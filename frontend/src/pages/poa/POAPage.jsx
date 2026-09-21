import { useState, useEffect, useRef } from 'react';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';

const MESES = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

const cuadrante = (p, i) => {
  p = Number(p); i = Number(i);
  if (p > 5 && i > 5) return 'QI';
  if (p > 5 && i <= 5) return 'QII';
  if (p <= 5 && i <= 5) return 'QIII';
  return 'QIV';
};

import { FiChevronDown, FiChevronUp } from 'react-icons/fi';

export default function POAPage() {
  const [areas, setAreas] = useState([]);
  const [areaId, setAreaId] = useState('');
  const [fichas, setFichas] = useState([]);
  const [loading, setLoading] = useState(false);
  const [expandedURs, setExpandedURs] = useState({});
  const ejercicio = useGlobalStore((s) => s.ejercicio);

  useEffect(() => {
    axios.get('/unidades-responsables').then(res => {
      const data = res.data.data || res.data;
      setAreas(data);
      if (data.length > 0) {
        // En lugar de seleccionar la primera UR por defecto, seleccionamos "todas"
        setAreaId('todas');
      }
    });
  }, []);

  useEffect(() => {
    if (!areaId) return;
    setLoading(true);
    axios.get(`/poa/fichas?ejercicio_id=${ejercicio}&area_id=${areaId}`)
      .then(res => setFichas(res.data.data || res.data || []))
      .catch(() => setFichas([]))
      .finally(() => setLoading(false));
  }, [areaId, ejercicio]);

  return (
    <>
      <div className="page-head">
        <div>
          <h1>POA y acciones sustantivas</h1>
          <p>Fuente programática del ejercicio {ejercicio} y trazabilidad hacia los riesgos.</p>
        </div>
        <select className="input" style={{ maxWidth: 320 }} value={areaId} onChange={e => setAreaId(e.target.value)}>
          <option value="todas">Todas las áreas (Institucional)</option>
          {areas.map((a, i) => (
            <option key={a.unidad_responsable_gasto_id || a.id_unidad || a.id || i} value={String(a.unidad_responsable_gasto_id || a.id_unidad || a.id)}>
              {a.nombre || a.denominacion}
            </option>
          ))}
        </select>
      </div>

      {loading && <div className="notice">Cargando fichas POA…</div>}

      {!loading && fichas.length === 0 && (
        <div className="empty">Sin ficha POA precargada para este ejercicio y área.</div>
      )}

      {!loading && fichas.length > 0 && (() => {
        // Agrupar fichas por URG
        const gruposUR = {};
        fichas.forEach(proyecto => {
          const urg = String(proyecto.urg_id || areaId);
          if (!gruposUR[urg]) gruposUR[urg] = [];
          gruposUR[urg].push(proyecto);
        });

        return areas.map((area, i) => {
          const urgId = String(area.unidad_responsable_gasto_id || area.id_unidad || area.id);
          
          // Si hay un filtro específico y esta no es el área, la ignoramos
          if (areaId !== 'todas' && areaId !== urgId) return null;

          const areaName = area.nombre || area.denominacion || 'Área desconocida';
          const title = `POA ${ejercicio} - ${areaName}`;
          const proyectosUR = gruposUR[urgId] || [];

          // Extraer todas las actividades de todos los proyectos de esta UR
          const allActivities = [];
          const objetivos = new Set();
          proyectosUR.forEach(proyecto => {
            if (proyecto.objetivo) objetivos.add(proyecto.objetivo);
            const acts = proyecto.acciones || proyecto.actividades || [];
            acts.forEach(a => allActivities.push(a));
          });

          // Usar los objetivos de los proyectos como subtítulo
          const subtitle = Array.from(objetivos).join(' ') || '—';

          const isExpanded = !!expandedURs[urgId];
          const toggleExpanded = () => setExpandedURs(prev => ({ ...prev, [urgId]: !prev[urgId] }));

          return (
            <section className="panel" key={urgId || i} style={{ marginBottom: '24px' }}>
              <div className="panel-head" style={{ marginBottom: '8px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <h2 style={{ fontSize: '1.25rem', fontWeight: 600 }}>{title}</h2>
                <button 
                  type="button" 
                  className="btn btn-outline" 
                  style={{ 
                    padding: '6px', 
                    fontSize: '1.2rem',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    border: 'none',
                    background: 'transparent',
                    cursor: 'pointer'
                  }} 
                  onClick={toggleExpanded}
                  title={isExpanded ? 'Comprimir' : 'Expandir'}
                >
                  {isExpanded ? <FiChevronUp /> : <FiChevronDown />}
                </button>
              </div>
              
              <p style={{ color: '#666', marginBottom: '16px' }}>{subtitle}</p>

              {isExpanded && (
                <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                  <thead>
                    <tr>
                        <th style={{ width: '50px' }}>#</th>
                        <th>Acción sustantiva / alineación</th>
                        <th style={{ width: '200px' }}>Riesgos vinculados</th>
                      </tr>
                    </thead>
                    <tbody>
                      {allActivities.length > 0 ? (
                        allActivities.map((a, idx) => (
                          <tr key={idx}>
                            <td>{idx + 1}</td>
                            <td>{a.descripcion || a.denominacion || a.texto}</td>
                            <td>
                              <div style={{ display: 'flex', gap: '4px', flexWrap: 'wrap' }}>
                                {(a.riesgos_vinculados || a.riesgos || []).length > 0
                                  ? (a.riesgos_vinculados || a.riesgos).map(r => (
                                      <span className="chip" key={r.id}>{r.local_id}</span>
                                    ))
                                  : <span className="muted">Sin riesgo</span>}
                              </div>
                            </td>
                          </tr>
                        ))
                      ) : (
                        <tr>
                          <td colSpan="3" style={{ textAlign: 'center', padding: '16px', color: '#888' }}>
                            Sin actividades sustantivas registradas.
                          </td>
                        </tr>
                      )}
                    </tbody>
                  </table>
              )}
            </section>
          );
        });
      })()}
    </>
  );
}
