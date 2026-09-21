import { useState, useEffect } from 'react';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';
import { Edit, Delete } from '@mui/icons-material';
import { IconButton, Tooltip } from '@mui/material';

export default function ControlesPage() {
  const [riesgos, setRiesgos] = useState([]);
  const [areas, setAreas] = useState([]);
  const [areaId, setAreaId] = useState('');
  const [loading, setLoading] = useState(true);
  const ejercicio = useGlobalStore((s) => s.ejercicio);

  useEffect(() => {
    axios.get('/unidades-responsables').then(res => {
      const data = res.data.data || res.data;
      setAreas(data);
      if (data.length > 0) {
        const firstId = String(data[0].unidad_responsable_gasto_id || data[0].id_unidad || data[0].id);
        setAreaId(firstId);
      }
    });
  }, []);

  useEffect(() => {
    fetchData();
  }, [areaId, ejercicio]);

  const fetchData = async () => {
    setLoading(true);
    try {
      const url = areaId ? `/riesgos?ejercicio_id=${ejercicio}&area_id=${areaId}` : `/riesgos?ejercicio_id=${ejercicio}`;
      const res = await axios.get(url);
      setRiesgos(res.data.data || res.data || []);
    } finally {
      setLoading(false);
    }
  };

  const getControlText = (r) => {
    if (!r.controles || r.controles.length === 0) return '—';
    return r.controles.map(c => c.control || c.texto).filter(Boolean).join('; ');
  };

  const getIndicadorText = (r) => {
    if (!r.indicadores || r.indicadores.length === 0) return '—';
    const ind = r.indicadores[0];
    return (
      <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
        <div style={{ padding: '12px', border: '1px solid #e2e8f0', borderRadius: '6px', backgroundColor: '#f8fafc', color: '#0f172a' }}>
          <b>{ind.formula || ind.nombre || '—'}</b>
        </div>
        <div style={{ fontSize: '0.8rem', color: '#64748b' }}>
          Unidad: {ind.unidad || '—'} · Periodicidad: {ind.periodicidad || '—'} · Sentido: {ind.sentido || '—'}
        </div>
      </div>
    );
  };

  const getPeriodicidadText = (r) => {
    if (!r.indicadores || r.indicadores.length === 0) return '—';
    return r.indicadores[0].periodicidad || '—';
  };

  return (
    <>
      <div className="page-head">
        <div>
          <h1>Controles e Indicadores</h1>
          <p>Indicadores MAR con numerador, denominador y fórmula de cálculo.</p>
        </div>
      </div>

      <section className="panel" style={{ padding: '20px', marginBottom: '20px' }}>
        <div className="form-grid" style={{ gridTemplateColumns: '1fr 2fr', gap: '20px' }}>
          <div>
            <label style={{ display: 'block', marginBottom: '5px', fontWeight: 600 }}>Área / Unidad Responsable</label>
            <select className="input" style={{ width: '100%' }} value={areaId} onChange={e => setAreaId(e.target.value)}>
              <option value="">Todas las áreas asignadas</option>
              {areas.map((a, i) => (
                <option key={a.unidad_responsable_gasto_id || a.id_unidad || a.id || i} value={String(a.unidad_responsable_gasto_id || a.id_unidad || a.id)}>
                  {a.nombre || a.denominacion}
                </option>
              ))}
            </select>
          </div>
        </div>
      </section>

      <section className="panel">
        {loading ? (
          <p style={{ padding: '20px' }}>Cargando…</p>
        ) : riesgos.length === 0 ? (
          <div className="empty" style={{ padding: '20px' }}>No hay riesgos registrados para esta área y ejercicio.</div>
        ) : (
          <table className="data-table">
            <thead>
              <tr style={{ backgroundColor: '#1F4E79', color: '#fff' }}>
                <th style={{ color: '#fff', width: '80px' }}>ID</th>
                <th style={{ color: '#fff', width: '25%' }}>Riesgo</th>
                <th style={{ color: '#fff', width: '25%' }}>Control</th>
                <th style={{ color: '#fff' }}>Indicador / fórmula</th>
                <th style={{ color: '#fff', width: '120px' }}>Periodicidad</th>
                <th style={{ color: '#fff', width: '100px', textAlign: 'center' }}>Acciones</th>
              </tr>
            </thead>
            <tbody>
              {riesgos.map((r, idx) => (
                <tr key={idx} style={{ verticalAlign: 'top' }}>
                  <td style={{ paddingTop: '15px' }}><b>{r.local_id}</b></td>
                  <td style={{ paddingTop: '15px' }}>{r.riesgo}</td>
                  <td style={{ paddingTop: '15px' }}>{getControlText(r)}</td>
                  <td style={{ paddingTop: '15px' }}>{getIndicadorText(r)}</td>
                  <td style={{ paddingTop: '15px' }}>{getPeriodicidadText(r)}</td>
                  <td style={{ paddingTop: '15px' }}>
                    <div style={{ display: 'flex', justifyContent: 'center', gap: '4px' }}>
                      <Tooltip title="Editar">
                        <IconButton size="small" color="primary">
                          <Edit fontSize="small" />
                        </IconButton>
                      </Tooltip>
                      <Tooltip title="Eliminar">
                        <IconButton size="small" color="error">
                          <Delete fontSize="small" />
                        </IconButton>
                      </Tooltip>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </section>
    </>
  );
}
