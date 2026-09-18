import { useState, useEffect } from 'react';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';
import useAuth from '../../hooks/useAuth';
import { useNavigate, useLocation } from 'react-router-dom';

const cuadrante = (p, i) => {
  p = Number(p); i = Number(i);
  if (p > 5 && i > 5) return 'QI';
  if (p > 5 && i <= 5) return 'QII';
  if (p <= 5 && i <= 5) return 'QIII';
  return 'QIV';
};

export default function MapMarPage() {
  const [riesgos, setRiesgos] = useState([]);
  const [areas, setAreas] = useState([]);
  const [areaId, setAreaId] = useState('');
  const [loading, setLoading] = useState(true);
  
  const ejercicio = useGlobalStore((s) => s.ejercicio);
  const { hasRole, user } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();

  const isSuperAdmin = hasRole('Super Administrador') || hasRole('superadmin') || hasRole('Admin');
  const canValidate = isSuperAdmin || hasRole('Administrador') || hasRole('Validador');

  useEffect(() => {
    axios.get('/unidades-responsables').then(res => {
      const data = res.data.data || res.data;
      setAreas(data);
      if (data.length > 0) {
        if (location.state?.areaId) {
          setAreaId(location.state.areaId);
        } else {
          const firstId = String(data[0].unidad_responsable_gasto_id || data[0].id_unidad || data[0].id);
          setAreaId(firstId);
        }
      }
    });
  }, []);

  useEffect(() => {
    fetchRiesgos();
  }, [areaId, ejercicio]);

  const fetchRiesgos = async () => {
    if (!areaId) return;
    setLoading(true);
    try {
      const url = `/riesgos?ejercicio_id=${ejercicio}&area_id=${areaId}`;
      const res = await axios.get(url);
      setRiesgos(res.data.data || res.data || []);
    } finally {
      setLoading(false);
    }
  };

  const handleBatchValidate = async () => {
    if (!canValidate) {
      alert('Tu perfil no tiene permiso de validación para esta área.');
      return;
    }
    
    const pending = riesgos.filter(r => r.status !== 'Validado');
    if (pending.length === 0) {
      alert('Todos los datos del área ya están validados.');
      return;
    }
    
    const notReview = pending.filter(r => r.status !== 'En revisión');
    if (notReview.length > 0) {
      alert('Antes de la validación integral, todos los riesgos deben encontrarse "En revisión".');
      return;
    }
    
    if (!window.confirm(`Se validarán ${pending.length} riesgos del área. ¿Continuar?`)) return;
    
    try {
      const riskIds = pending.map(r => r.id);
      await axios.post('/riesgos/batch-validate', {
        area_id: areaId,
        ejercicio_id: ejercicio,
        risk_ids: riskIds
      });
      alert('Validación integral completada con éxito.');
      fetchRiesgos();
    } catch (err) {
      alert('Error en validación: ' + (err.response?.data?.message || err.message));
    }
  };

  const handleVerMapa = () => {
    if (!areaId) return;
    navigate(`/reportes/mapa/${areaId}`, { state: { fromMapMAR: true, areaId } });
  };

  const getFactorsText = (r) => {
    const arr = [];
    if (r.factores) arr.push(...r.factores.split(';').map(s => s.trim()).filter(Boolean));
    if (r.factores_internos) arr.push(...r.factores_internos.split(';').map(s => s.trim()).filter(Boolean));
    if (r.factores_externos) arr.push(...r.factores_externos.split(';').map(s => s.trim()).filter(Boolean));
    return [...new Set(arr)].join('; ') || '—';
  };

  const allValidated = riesgos.length > 0 && riesgos.every(r => r.status === 'Validado');

  return (
    <>
      <div className="page-head">
        <div>
          <h1>MAPA y MAR</h1>
          <p>Revisión integral y validación de la información del área.</p>
        </div>
      </div>

      <section className="panel" style={{ padding: '20px', marginBottom: '20px' }}>
        <div className="toolbar" style={{ display: 'flex', gap: '12px', flexWrap: 'wrap', alignItems: 'flex-end' }}>
          <label style={{ minWidth: '360px', flex: 1 }}>
            <b style={{ display: 'block', marginBottom: '5px' }}>Área / Unidad Responsable</b>
            <select className="input" value={areaId} onChange={e => setAreaId(e.target.value)}>
              {areas.length === 0 && <option value="">Sin áreas asignadas</option>}
              {areas.map((a, i) => (
                <option key={a.unidad_responsable_gasto_id || a.id_unidad || a.id || i} value={String(a.unidad_responsable_gasto_id || a.id_unidad || a.id)}>
                  {a.nombre || a.denominacion}
                </option>
              ))}
            </select>
          </label>
          <button className="btn" onClick={handleVerMapa}>Ver Mapa de Riesgos</button>
          <button className="btn" onClick={() => navigate(`/reportes/mar/${areaId}`, { state: { fromMapMAR: true, areaId } })}>Ver Reporte Institucional</button>
          <button className="btn primary" onClick={handleBatchValidate}>Validar datos del área</button>
        </div>

        {areaId && !loading && (
          <div className={`notice ${allValidated ? 'success' : ''}`} style={{ marginTop: '15px', backgroundColor: allValidated ? '#e6f4ea' : '#edf5fb', borderLeftColor: allValidated ? '#34a853' : '#2d75b8' }}>
            {allValidated ? 'Todos los riesgos del área están validados.' : 'La validación integral requiere que todos los riesgos del área queden validados.'}
          </div>
        )}
      </section>

      <section className="panel">
        {loading ? (
          <p>Cargando información del área...</p>
        ) : !areaId ? (
          <div className="empty">Seleccione un área para visualizar.</div>
        ) : (
          <table className="data-table" style={{ fontSize: '0.9rem' }}>
            <thead>
              <tr style={{ backgroundColor: '#1F4E79', color: '#fff' }}>
                <th style={{ color: '#fff' }}>ID</th>
                <th style={{ color: '#fff' }}>Objetivo</th>
                <th style={{ color: '#fff', width: '20%' }}>Riesgo</th>
                <th style={{ color: '#fff', width: '20%' }}>Factores</th>
                <th style={{ color: '#fff' }}>Controles</th>
                <th style={{ color: '#fff' }}>Indicadores</th>
                <th style={{ color: '#fff' }}>P</th>
                <th style={{ color: '#fff' }}>I</th>
                <th style={{ color: '#fff' }}>Cuadrante</th>
                <th style={{ color: '#fff' }}>Estatus</th>
              </tr>
            </thead>
            <tbody>
              {riesgos.map(r => {
                const ctrls = (r.controles || []).map(c => c.texto).filter(Boolean).join('; ') || '—';
                const inds = (r.indicadores || []).map(i => i.nombre).filter(Boolean).join('; ') || '—';
                const qd = cuadrante(r.probabilidad || 0, r.impacto || 0);

                return (
                  <tr key={r.id} style={{ transition: 'background-color 0.2s', ':hover': { backgroundColor: '#f1f5f9' } }}>
                    <td><b>{r.local_id}</b></td>
                    <td>{r.objetivo || '—'}</td>
                    <td>{r.riesgo}</td>
                    <td>{getFactorsText(r)}</td>
                    <td>{ctrls}</td>
                    <td>{inds}</td>
                    <td>{r.probabilidad}</td>
                    <td>{r.impacto}</td>
                    <td><span className={`badge ${qd.toLowerCase()}`}>{qd}</span></td>
                    <td><span className="status">{r.status}</span></td>
                  </tr>
                );
              })}
              {riesgos.length === 0 && (
                <tr><td colSpan="10" style={{ textAlign: 'center', color: '#6f8294', padding: '20px' }}>
                  No hay riesgos para mostrar.
                </td></tr>
              )}
            </tbody>
          </table>
        )}
      </section>
    </>
  );
}
