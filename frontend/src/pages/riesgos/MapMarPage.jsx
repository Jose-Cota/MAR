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

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(400px, 1fr))', gap: '20px' }}>
        <section className="panel" style={{ padding: '24px' }}>
          <h2 style={{ fontSize: '1.25rem', marginBottom: '20px', color: '#17324d' }}>Mapa y Matriz de Administración de Riesgos</h2>
          <label style={{ display: 'block', marginBottom: '20px' }}>
            <b style={{ display: 'block', marginBottom: '8px', color: '#5b6773', fontSize: '0.9rem' }}>Área / Unidad Responsable</b>
            <select className="input" value={areaId} onChange={e => setAreaId(e.target.value)} style={{ width: '100%', padding: '10px', borderRadius: '6px', border: '1px solid #d8e0e8' }}>
              {areas.length === 0 && <option value="">Sin áreas asignadas</option>}
              {areas.map((a, i) => (
                <option key={a.unidad_responsable_gasto_id || a.id_unidad || a.id || i} value={String(a.unidad_responsable_gasto_id || a.id_unidad || a.id)}>
                  {a.nombre || a.denominacion}
                </option>
              ))}
            </select>
          </label>
          <div style={{ display: 'flex', gap: '12px', marginBottom: '15px' }}>
            <button className="btn primary" onClick={handleVerMapa}>Mapa / PDF</button>
            <button className="btn" onClick={() => navigate(`/reportes/mar/${areaId}`, { state: { fromMapMAR: true, areaId } })}>MAR imprimible</button>
          </div>
          <p style={{ color: '#8898a9', fontSize: '0.9rem', margin: 0 }}>Estas vistas utilizan el mismo formato disponible en Reportes.</p>
        </section>

        <section className="panel" style={{ padding: '24px' }}>
          <h2 style={{ fontSize: '1.25rem', marginBottom: '20px', color: '#17324d' }}>Validación del área</h2>
          
          <div className={`notice ${allValidated ? 'success' : ''}`} style={{ marginBottom: '20px', padding: '15px', borderRadius: '8px', backgroundColor: allValidated ? '#e6f4ea' : '#edf5fb', borderLeft: `4px solid ${allValidated ? '#34a853' : '#2d75b8'}` }}>
            <p style={{ margin: 0, color: allValidated ? '#137333' : '#17324d' }}>
              {allValidated ? 'Todos los riesgos del área están validados.' : 'Para validar integralmente, todos los riesgos deben encontrarse En revisión.'}
            </p>
          </div>
          
          <button className="btn primary" onClick={handleBatchValidate}>Validar datos del área</button>
        </section>
      </div>
    </>
  );
}
