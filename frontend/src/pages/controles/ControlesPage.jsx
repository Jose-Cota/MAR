import { useState, useEffect } from 'react';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';

export default function ControlesPage() {
  const [controles, setControles] = useState([]);
  const [areas, setAreas] = useState([]);
  const [loading, setLoading] = useState(true);
  const ejercicio = useGlobalStore((s) => s.ejercicio);

  useEffect(() => {
    fetchData();
  }, [ejercicio]);

  const fetchData = async () => {
    setLoading(true);
    try {
      const [resRiesgos, resAreas] = await Promise.all([
        axios.get(`/riesgos?ejercicio_id=${ejercicio}`),
        axios.get('/unidades-responsables'),
      ]);
      const riesgos = resRiesgos.data.data || resRiesgos.data;
      const areasData = resAreas.data.data || resAreas.data;
      setAreas(areasData);

      // Flatten: un row por control
      const rows = riesgos.flatMap(r =>
        (r.controles || []).map(c => ({ r, c }))
      );
      setControles(rows);
    } finally {
      setLoading(false);
    }
  };

  const getAreaName = (areaId) => {
    const a = areas.find(x => String(x.id_unidad || x.id) === String(areaId));
    return a?.nombre || a?.denominacion || areaId;
  };

  const toggleControlValidation = async (riesgoId, controlId, currentState, evType, evRef) => {
    const isCurrentlyValidated = currentState === 'Validado por el área';
    if (!isCurrentlyValidated && !evType && !evRef) {
      alert('Para validar el control registra primero evidencia o referencia verificable en la edición del riesgo.');
      return;
    }
    
    const newState = isCurrentlyValidated ? 'Propuesto – pendiente de validación' : 'Validado por el área';
    try {
      await axios.put(`/riesgos/${riesgoId}/controles/${controlId}/validar`, { estado_validacion: newState });
      // Reload or optimistic update
      setControles(prev => prev.map(item => {
        if (item.c.id === controlId) {
          return { ...item, c: { ...item.c, estado_validacion: newState } };
        }
        return item;
      }));
    } catch (err) {
      alert('Error al cambiar el estado del control: ' + (err.response?.data?.message || err.message));
    }
  };

  return (
    <>
      <div className="page-head">
        <div>
          <h1>Controles</h1>
          <p>Consulta transversal de controles y evidencia registrada en los riesgos.</p>
        </div>
      </div>
      <section className="panel">
        {loading ? (
          <p>Cargando…</p>
        ) : controles.length === 0 ? (
          <div className="empty">No hay controles registrados para este ejercicio.</div>
        ) : (
          <table>
            <thead>
              <tr>
                <th>Área</th>
                <th>Riesgo</th>
                <th>Control</th>
                <th>Estado</th>
                <th>Evidencia</th>
                <th>Periodicidad</th>
                <th>Responsable</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody>
              {controles.map(({ r, c }, idx) => (
                <tr key={idx}>
                  <td>{getAreaName(r.area_id)}</td>
                  <td><b>{r.local_id}</b> · {r.riesgo}</td>
                  <td>{c.control || '—'}</td>
                  <td>
                    <span className={`status-badge ${c.estado_validacion === 'Validado por el área' ? 'success' : 'warning'}`} style={{ padding: '4px 8px', borderRadius: '12px', fontSize: '11px', background: c.estado_validacion === 'Validado por el área' ? '#dff0e4' : '#fff3cd', color: c.estado_validacion === 'Validado por el área' ? '#216338' : '#7a5b00', border: `1px solid ${c.estado_validacion === 'Validado por el área' ? '#a8d3b4' : '#ead38a'}` }}>
                      {c.estado_validacion || 'Propuesto – pendiente de validación'}
                    </span>
                  </td>
                  <td>{[c.evidencia_tipo, c.evidencia_referencia].filter(Boolean).join(' ') || '—'}</td>
                  <td>{c.evidencia_periodicidad || '—'}</td>
                  <td>{c.evidencia_responsable || '—'}</td>
                  <td>
                    <button 
                      className="icon-btn" 
                      style={{ padding: '6px 12px', borderRadius: '4px', border: '1px solid #cbd9e5', background: '#fff', cursor: 'pointer' }}
                      onClick={() => toggleControlValidation(r.id, c.id, c.estado_validacion, c.evidencia_tipo, c.evidencia_referencia)}
                    >
                      {c.estado_validacion === 'Validado por el área' ? 'Marcar propuesto' : 'Validar control'}
                    </button>
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
