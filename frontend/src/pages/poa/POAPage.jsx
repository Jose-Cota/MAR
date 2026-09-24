import { useState, useEffect } from 'react';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';
import { FiChevronDown, FiChevronUp } from 'react-icons/fi';

export default function POAPage() {
  const [areas, setAreas]           = useState([]);
  const [areaId, setAreaId]         = useState('');
  const [fichas, setFichas]         = useState([]);
  const [loading, setLoading]       = useState(false);
  const [expandedURs, setExpandedURs] = useState({});
  const ejercicio = useGlobalStore((s) => s.ejercicio);

  // Cargar áreas (URGs)
  useEffect(() => {
    axios.get('/unidades-responsables').then(res => {
      const data = res.data.data || res.data;
      setAreas(data);
      // Seleccionar la primera área por defecto
      if (data.length > 0) {
        const firstId = String(data[0].unidad_responsable_gasto_id || data[0].id_unidad || data[0].id);
        setAreaId(firstId);
      }
    });
  }, []);

  // Cargar fichas al cambiar área o ejercicio
  useEffect(() => {
    if (!areaId) return;
    setLoading(true);
    // Enviamos el areaId real al backend — ahora filtra correctamente por nombre de URG
    axios.get(`/poa/fichas?ejercicio=${ejercicio}&area_id=${areaId}`)
      .then(res => setFichas(res.data.data || res.data || []))
      .catch(() => setFichas([]))
      .finally(() => setLoading(false));
  }, [areaId, ejercicio]);

  /**
   * Agrupa proyectos por nombre de área.
   * Intenta hacer coincidir la URG (por nombre) con el responsable_operativo
   * del proyecto (campo ro_nombre), ya que los IDs históricos no coinciden.
   */
  const getProyectosPorArea = (areaNombre) => {
    if (!areaNombre) return fichas; // "todas"
    const lower = areaNombre.toLowerCase().trim();
    return fichas.filter(p => {
      // Intento 1: comparar por ro_nombre (si viene del backend)
      if (p.ro_nombre && p.ro_nombre.toLowerCase().includes(lower)) return true;
      // Intento 2: comparar por nombre del proyecto
      if (p.nombre && p.nombre.toLowerCase().includes(lower)) return true;
      return false;
    });
  };

  const getAreaActual = () => areas.find(a =>
    String(a.unidad_responsable_gasto_id || a.id_unidad || a.id) === String(areaId)
  );

  // Construir lista de "panels" a mostrar
  const getPanels = () => {
    if (areaId === 'todas') {
      // Agrupar por responsable_operativo_id para que cada área tenga su propio panel (urg_id a veces se comparte)
      const grupos = {};
      fichas.forEach(p => {
        const key = String(p.responsable_operativo_id || p.urg_id || 'sin-area');
        if (!grupos[key]) grupos[key] = [];
        grupos[key].push(p);
      });
      return Object.entries(grupos).map(([urgId, proyectos]) => {
        const roNombre = proyectos.find(p => p.ro_nombre)?.ro_nombre || `Área ${urgId}`;
        return {
          key: urgId,
          titulo: `POA ${ejercicio} - ${roNombre}`,
          proyectos,
        };
      });
    } else {
      // Área específica: mostrar TODOS los proyectos retornados (el backend ya filtró o no pudo)
      const area = getAreaActual();
      const areaNombre = area ? (area.nombre || area.denominacion) : '';
      return [{
        key: areaId,
        titulo: `POA ${ejercicio} - ${areaNombre}`,
        proyectos: fichas,
      }];
    }
  };

  const panels = !loading ? getPanels() : [];
  const totalActividades = fichas.reduce((sum, p) => sum + (p.acciones?.length || 0), 0);

  return (
    <>
      <div className="page-head">
        <div>
          <h1>POA y acciones sustantivas</h1>
          <p>Trazabilidad de proyectos y acciones hacia los riesgos.</p>
        </div>
        <select
          className="input"
          style={{ maxWidth: 320 }}
          value={areaId}
          onChange={e => setAreaId(e.target.value)}
        >
          {areas.map((a, i) => (
            <option
              key={a.unidad_responsable_gasto_id || a.id_unidad || a.id || i}
              value={String(a.unidad_responsable_gasto_id || a.id_unidad || a.id)}
            >
              {a.nombre || a.denominacion}
            </option>
          ))}
        </select>
      </div>

      {loading && <div className="notice">Cargando fichas POA…</div>}

      {!loading && fichas.length === 0 && (
        <div className="empty">Sin ficha POA precargada para este ejercicio.</div>
      )}

      {!loading && areaId !== 'todas' && getAreaActual() && fichas.length > 0 && (
        <>
          {fichas.map((proyecto, pIdx) => {
            const actividades = (proyecto.acciones || proyecto.actividades || []);
            
            if (actividades.length === 0) return null;
            
            return (
              <section key={proyecto.id || pIdx} className="panel" style={{ marginBottom: '24px' }}>
                <h2 style={{ fontSize: '1.25rem', margin: '0 0 8px' }}>
                  {proyecto.nombre || `Alineación técnica POA ${ejercicio} - Proyecto ${pIdx + 1}`}
                </h2>
                
                <p style={{ margin: '0 0 16px', color: '#555', lineHeight: 1.5 }}>
                  {actividades
                    .map(a => a.descripcion || a.denominacion || a.texto)
                    .filter(Boolean)
                    .join('; ')}
                </p>

                
                <table style={{ width: '100%', borderCollapse: 'collapse', marginBottom: 8 }}>
                    <thead>
                      <tr>
                        <th style={{ width: '40px' }}>#</th>
                        <th>Acción sustantiva / alineación</th>
                        <th style={{ width: '270px', textAlign: 'center' }}>Riesgos vinculados</th>
                      </tr>
                    </thead>
                    <tbody>
                      {actividades.map((a, idx) => {
                        const riesgos = a.riesgos_vinculados || a.riesgos || [];
                        return (
                          <tr key={idx}>
                            <td>{a.numero || idx + 1}</td>
                            <td>{a.descripcion || a.denominacion || a.texto || '—'}</td>
                            <td style={{ textAlign: 'center' }}>
                              {riesgos.length > 0 ? (
                                riesgos.map((r, ri) => {
                                  const rawId = String(r.local_id || r.id);
                                  const shortId = rawId.includes('-') ? rawId.split('-').pop() : rawId;
                                  const displayId = shortId.startsWith('R') ? shortId : `R${shortId}`;
                                  return (
                                    <span key={ri} className="chip" style={{ marginRight: '4px' }}>
                                      {displayId}
                                    </span>
                                  );
                                })
                              ) : (
                                <span className="muted" style={{ fontSize: '12px' }}>Sin riesgo</span>
                              )}
                            </td>
                          </tr>
                        );
                      })}
                    </tbody>
                  </table>
              </section>
            );
          })}
        </>
      )}

      {!loading && areaId === 'todas' && getPanels().map((grupo, idx) => {
        const areaFichas = grupo.proyectos;
        const actividadesTotales = areaFichas.flatMap(p => p.acciones || p.actividades || []);
        
        if (actividadesTotales.length === 0) return null;
        
        return (
          <section className="panel" key={grupo.key || idx} style={{ marginBottom: '24px' }}>
            <h2 style={{ fontSize: '1.25rem', margin: '0 0 8px' }}>
              {grupo.titulo}
            </h2>
            
            <p style={{ margin: '0 0 16px', color: '#555', lineHeight: 1.5 }}>
              {actividadesTotales
                .map(a => a.descripcion || a.denominacion || a.texto)
                .filter(Boolean)
                .join('; ')}
            </p>
            
            <table style={{ width: '100%', borderCollapse: 'collapse', marginBottom: 8 }}>
                <thead>
                  <tr>
                    <th style={{ width: '40px' }}>#</th>
                    <th>Acción sustantiva / alineación</th>
                    <th style={{ width: '180px', textAlign: 'center' }}>Riesgos vinculados</th>
                  </tr>
                </thead>
                <tbody>
                  {(() => {
                    return actividadesTotales.map((a, idxAct) => {
                      const riesgos = a.riesgos_vinculados || a.riesgos || [];
                      return (
                        <tr key={idxAct}>
                          <td>{a.numero || idxAct + 1}</td>
                          <td>{a.descripcion || a.denominacion || a.texto || '—'}</td>
                          <td style={{ textAlign: 'center' }}>
                            {riesgos.length > 0 ? (
                              riesgos.map((r, ri) => {
                                const rawId = String(r.local_id || r.id);
                                const shortId = rawId.includes('-') ? rawId.split('-').pop() : rawId;
                                const displayId = shortId.startsWith('R') ? shortId : `R${shortId}`;
                                return (
                                  <span key={ri} className="chip" style={{ marginRight: '4px' }}>
                                    {displayId}
                                  </span>
                                );
                              })
                            ) : (
                              <span className="muted" style={{ fontSize: '12px' }}>Sin riesgo</span>
                            )}
                          </td>
                        </tr>
                      );
                    });
                  })()}
                </tbody>
              </table>
          </section>
        );
      })}
    </>
  );
}
