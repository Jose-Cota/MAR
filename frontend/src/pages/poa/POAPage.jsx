import { useState, useEffect } from 'react';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';
import { FiChevronDown, FiChevronUp } from 'react-icons/fi';

export default function POAPage() {
  const [areas, setAreas]           = useState([]);
  const [areaId, setAreaId]         = useState('todas');
  const [fichas, setFichas]         = useState([]);
  const [loading, setLoading]       = useState(false);
  const [expandedURs, setExpandedURs] = useState({});
  const ejercicio = useGlobalStore((s) => s.ejercicio);

  // Cargar áreas (URGs)
  useEffect(() => {
    axios.get('/unidades-responsables').then(res => {
      const data = res.data.data || res.data;
      setAreas(data);
    });
  }, []);

  // Cargar fichas al cambiar área o ejercicio
  useEffect(() => {
    if (!areaId) return;
    setLoading(true);
    // Enviamos el areaId real al backend — ahora filtra correctamente por nombre de URG
    axios.get(`/poa/fichas?ejercicio_id=${ejercicio}&area_id=${areaId}`)
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
      // Agrupar por urg_id numérico (IDs internos del POA, aunque sean 564+)
      const grupos = {};
      fichas.forEach(p => {
        const key = String(p.urg_id || 'sin-area');
        if (!grupos[key]) grupos[key] = [];
        grupos[key].push(p);
      });
      return Object.entries(grupos).map(([urgId, proyectos]) => ({
        key: urgId,
        titulo: `POA ${ejercicio}`,
        proyectos,
      }));
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
          <p>Fuente programática del ejercicio {ejercicio} y trazabilidad hacia los riesgos.</p>
        </div>
        <select
          className="input"
          style={{ maxWidth: 320 }}
          value={areaId}
          onChange={e => setAreaId(e.target.value)}
        >
          <option value="todas">Todas las áreas (Institucional)</option>
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

      {!loading && fichas.length > 0 && (
        <div className="notice" style={{ marginBottom: 16 }}>
          <b>{fichas.length}</b> proyectos · <b>{totalActividades}</b> actividades sustantivas cargadas
        </div>
      )}

      {!loading && panels.map(({ key, titulo, proyectos }) => {
        // Extraer todas las actividades de los proyectos del panel
        const allActivities = [];
        const objetivos = new Set();
        proyectos.forEach(proyecto => {
          if (proyecto.objetivo) objetivos.add(proyecto.objetivo);
          (proyecto.acciones || proyecto.actividades || []).forEach(a => allActivities.push(a));
        });

        const subtitle = Array.from(objetivos).slice(0, 2).join(' | ') || '—';
        const isExpanded = !!expandedURs[key];
        const toggle = () => setExpandedURs(prev => ({ ...prev, [key]: !prev[key] }));

        return (
          <section className="panel" key={key} style={{ marginBottom: '24px' }}>
            <div className="panel-head" style={{ marginBottom: '8px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <div>
                <h2 style={{ fontSize: '1.1rem', fontWeight: 600, margin: 0 }}>{titulo}</h2>
                <span style={{ fontSize: '0.82rem', color: '#6f8294' }}>
                  {proyectos.length} proyecto(s) · {allActivities.length} actividad(es)
                </span>
              </div>
              <button
                type="button"
                style={{ padding: '6px', fontSize: '1.2rem', display: 'flex', alignItems: 'center', border: 'none', background: 'transparent', cursor: 'pointer' }}
                onClick={toggle}
                title={isExpanded ? 'Comprimir' : 'Expandir'}
              >
                {isExpanded ? <FiChevronUp /> : <FiChevronDown />}
              </button>
            </div>

            {isExpanded && (
              <>
                {proyectos.map((proyecto, pi) => {
                  const acts = proyecto.acciones || proyecto.actividades || [];
                  return (
                    <div key={proyecto.id || pi} style={{ marginBottom: 16 }}>
                      <p style={{ margin: '0 0 6px', fontWeight: 600, fontSize: '0.9rem', color: '#17324d' }}>
                        📋 {proyecto.nombre || proyecto.proyecto || `Proyecto ${proyecto.id}`}
                      </p>
                      {acts.length > 0 ? (
                        <table style={{ width: '100%', borderCollapse: 'collapse', marginBottom: 8 }}>
                          <thead>
                            <tr>
                              <th style={{ width: '40px' }}>#</th>
                              <th>Acción sustantiva</th>
                              <th style={{ width: '180px' }}>Riesgos vinculados</th>
                            </tr>
                          </thead>
                          <tbody>
                            {acts.map((a, idx) => {
                              const riesgos = a.riesgos_vinculados || a.riesgos || [];
                              const total = riesgos.length;
                              return (
                                <tr key={idx}>
                                  <td>{idx + 1}</td>
                                  <td>{a.descripcion || a.denominacion || a.texto || '—'}</td>
                                  <td style={{ textAlign: 'center' }}>
                                    {total > 0 ? (
                                      <span style={{
                                        display: 'inline-block',
                                        background: '#1f4e78',
                                        color: '#fff',
                                        borderRadius: '12px',
                                        padding: '2px 10px',
                                        fontWeight: 700,
                                        fontSize: '13px',
                                        minWidth: '28px'
                                      }} title={riesgos.map(r => r.local_id).join(', ')}>
                                        {total}
                                      </span>
                                    ) : (
                                      <span style={{ color: '#aaa', fontSize: '12px' }}>—</span>
                                    )}
                                  </td>
                                </tr>
                              );
                            })}
                          </tbody>
                        </table>
                      ) : (
                        <p style={{ color: '#888', fontSize: '0.85rem', margin: '0 0 8px' }}>Sin actividades sustantivas registradas.</p>
                      )}
                    </div>
                  );
                })}
              </>
            )}
          </section>
        );
      })}
    </>
  );
}
