import { useState, useEffect, useCallback } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';

const cuadrante = (p, i) => {
  p = Number(p); i = Number(i);
  if (p > 5 && i > 5) return 'QI';
  if (p > 5 && i <= 5) return 'QII';
  if (p <= 5 && i <= 5) return 'QIII';
  return 'QIV';
};

const W = 790, H = 535, LEFT = 75, TOP = 26, W_MAP = 660, H_MAP = 430;
const mapX = (v) => LEFT + (Number(v) / 10) * W_MAP;
const mapY = (v) => TOP + H_MAP - (Number(v) / 10) * H_MAP;

export default function MapaInstitucionalPage() {
  const [activeTab, setActiveTab] = useState('consolidated');
  const [riesgosInstitucionales, setRiesgosInstitucionales] = useState([]);
  const [riesgosValidados, setRiesgosValidados] = useState([]);
  const [areas, setAreas] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedRisk, setSelectedRisk] = useState(null);
  const [drawerOpen, setDrawerOpen] = useState(false);

  const [searchParams] = useSearchParams();
  const initRiskoId = searchParams.get('riesgo_id');
  const navigate = useNavigate();
  const ejercicio = useGlobalStore((s) => s.ejercicio);

  const fetchData = useCallback(async () => {
    setLoading(true);
    try {
      const [resInst, resRiesgos, resAreas] = await Promise.all([
        axios.get(`/riesgos-institucionales?ejercicio_id=${ejercicio}`),
        axios.get(`/riesgos?ejercicio_id=${ejercicio}`),
        axios.get('/unidades-responsables'),
      ]);
      const instData = resInst.data.data || resInst.data;
      const riesgosData = (resRiesgos.data.data || resRiesgos.data).filter(r => r.estatus === 'Validado');
      const areasData = resAreas.data.data || resAreas.data;
      setRiesgosInstitucionales(instData);
      setRiesgosValidados(riesgosData);
      setAreas(areasData);

      if (initRiskoId) {
        const found = instData.find(r => String(r.id) === initRiskoId);
        if (found) { setSelectedRisk(found); setDrawerOpen(true); }
      }
    } catch (e) {
      console.error(e);
    }
    setLoading(false);
  }, [ejercicio, initRiskoId]);

  useEffect(() => { fetchData(); }, [fetchData]);

  const areaName = (id) => {
    const a = areas.find(x => String(x.unidad_responsable_gasto_id || x.id_unidad || x.id) === String(id));
    return a ? (a.nombre || a.denominacion) : (id || '—');
  };

  const linked = new Set(riesgosInstitucionales.flatMap(r => (r.fuentes || []).map(f => f.id)));
  const areasVinculadas = new Set(riesgosValidados.filter(r => linked.has(r.id)).map(r => r.area_id));

  // ── Mapa SVG ─────────────────────────────────────────────────────────────
  const renderMapSvg = (items, isSource = false) => {
    const gridLines = [];
    for (let n = 0; n <= 10; n++) {
      gridLines.push(
        <g key={`gx-${n}`}>
          <line x1={mapX(n)} y1={TOP} x2={mapX(n)} y2={TOP + H_MAP} className="gridline" />
          <text x={mapX(n)} y={TOP + H_MAP + 22} textAnchor="middle">{n}</text>
        </g>,
        <g key={`gy-${n}`}>
          <line x1={LEFT} y1={mapY(n)} x2={LEFT + W_MAP} y2={mapY(n)} className="gridline" />
          <text x={LEFT - 12} y={mapY(n) + 4} textAnchor="end">{n}</text>
        </g>
      );
    }
    return (
      <svg viewBox={`0 0 ${W} ${H}`} className="risk-map institutional-map-svg" aria-label="Mapa Institucional de Riesgos">
        <rect x={LEFT} y={TOP} width={W_MAP / 2} height={H_MAP / 2} className="q2fill" />
        <rect x={LEFT + W_MAP / 2} y={TOP} width={W_MAP / 2} height={H_MAP / 2} className="q1fill" />
        <rect x={LEFT} y={TOP + H_MAP / 2} width={W_MAP / 2} height={H_MAP / 2} className="q3fill" />
        <rect x={LEFT + W_MAP / 2} y={TOP + H_MAP / 2} width={W_MAP / 2} height={H_MAP / 2} className="q4fill" />
        {gridLines}
        <line x1={mapX(5)} y1={TOP} x2={mapX(5)} y2={TOP + H_MAP} className="midline" />
        <line x1={LEFT} y1={mapY(5)} x2={LEFT + W_MAP} y2={mapY(5)} className="midline" />
        {items.map((r) => {
          const p = Number(r.probabilidad) || 0;
          const imp = Number(r.impacto) || 0;
          const label = isSource
            ? String(r.local_id || r.id).replace(/^.*-R/, 'R')
            : String(r.folio || r.id).replace(/^RI-\d{4}-?/, 'RI');
          return (
            <g key={r.id} className="risk-dot inst-dot" tabIndex="0" role="button"
              onClick={() => { setSelectedRisk(r); setDrawerOpen(true); }}>
              <circle cx={mapX(imp)} cy={mapY(p)} r="17" />
              <text x={mapX(imp)} y={mapY(p) + 5} textAnchor="middle">{label}</text>
              <title>{r.folio || r.local_id || r.id} — {r.riesgo}</title>
            </g>
          );
        })}
        <text x={LEFT + W_MAP / 2} y={H - 8} textAnchor="middle" className="axislabel">GRADO DE IMPACTO</text>
        <text transform={`translate(20 ${TOP + H_MAP / 2}) rotate(-90)`} textAnchor="middle" className="axislabel">PROBABILIDAD DE OCURRENCIA</text>
      </svg>
    );
  };

  // ── Vista Consolidada ─────────────────────────────────────────────────────
  const renderConsolidated = () => (
    <>
      <section className="panel">
        <div className="panel-head" style={{ marginBottom: '16px' }}>
          <h2>Mapa Institucional consolidado</h2>
          <button className="btn" onClick={() => window.print()}>Imprimir / PDF</button>
        </div>
        {riesgosInstitucionales.length === 0
          ? <div className="empty">
              Aún no existen riesgos institucionales.{' '}
              <button className="btn" style={{ marginTop: '8px' }} onClick={() => navigate('/consolidacion')}>
                Valida riesgos de Área y créalos desde Consolidación.
              </button>
            </div>
          : renderMapSvg(riesgosInstitucionales)
        }
      </section>

      <section className="panel">
        <h2>MAR Institucional — riesgos del mapa</h2>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Riesgo Institucional</th>
              <th>Fuentes</th>
              <th>P</th>
              <th>I</th>
              <th>Cuadrante</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {riesgosInstitucionales.length === 0
              ? <tr><td colSpan="7" className="muted" style={{ textAlign: 'center' }}>Sin riesgos institucionales.</td></tr>
              : riesgosInstitucionales.map(r => (
                <tr key={r.id}>
                  <td><b>{r.folio}</b></td>
                  <td>{r.riesgo}</td>
                  <td>
                    {(r.fuentes || []).map(f => <span key={f.id} className="chip">{f.local_id || f.id}</span>)}
                  </td>
                  <td>{r.probabilidad}</td>
                  <td>{r.impacto}</td>
                  <td><span className={`badge ${cuadrante(r.probabilidad, r.impacto).toLowerCase()}`}>{cuadrante(r.probabilidad, r.impacto)}</span></td>
                  <td>
                    <button className="icon-btn" onClick={() => { setSelectedRisk(r); setDrawerOpen(true); }}>
                      Ver detalle
                    </button>
                  </td>
                </tr>
              ))
            }
          </tbody>
        </table>
      </section>

      {/* Sugerencias de agrupación */}
      <section className="panel">
        <div className="panel-head" style={{ marginBottom: '10px' }}>
          <div>
            <h2>Sugerencias de agrupación</h2>
            <p className="muted" style={{ margin: 0, fontSize: '13px' }}>
              Ayuda técnica por similitud textual. No crea ni modifica riesgos automáticamente.
            </p>
          </div>
          <button className="btn" onClick={() => navigate('/consolidacion')}>Abrir Consolidación</button>
        </div>
        {riesgosValidados.length < 2
          ? <div className="empty">No hay suficientes riesgos validados similares para sugerir agrupaciones.</div>
          : (
            <div className="suggestion-grid">
              {/* Agrupamos por área como sugerencia simple */}
              {Object.entries(
                riesgosValidados.reduce((acc, r) => {
                  const key = r.area_id;
                  if (!acc[key]) acc[key] = [];
                  acc[key].push(r);
                  return acc;
                }, {})
              ).filter(([, rs]) => rs.length > 1).slice(0, 6).map(([areaId, rs], i) => {
                const maxP = Math.max(...rs.map(r => Number(r.probabilidad) || 0));
                const maxI = Math.max(...rs.map(r => Number(r.impacto) || 0));
                return (
                  <article key={areaId} className="suggestion-card">
                    <div>
                      <span>Grupo {i + 1} — {areaName(areaId)}</span>
                      <b>{rs.length} riesgos</b>
                    </div>
                    <p style={{ margin: '6px 0' }}>
                      {rs.map(r => <span key={r.id} className="chip">{r.local_id || r.id}</span>)}
                    </p>
                    <small>Valoración sugerida: <b>P={maxP} / I={maxI} · {cuadrante(maxP, maxI)}</b></small>
                  </article>
                );
              })}
            </div>
          )
        }
      </section>
    </>
  );

  // ── Vista por Áreas ───────────────────────────────────────────────────────
  const renderSources = () => (
    <section className="panel">
      <h2>Vista por áreas — Riesgos validados</h2>
      {riesgosValidados.length === 0
        ? <div className="empty">No hay riesgos validados en este ejercicio.</div>
        : <>
            {renderMapSvg(riesgosValidados, true)}
            <table style={{ marginTop: '16px' }}>
              <thead>
                <tr>
                  <th>Área</th>
                  <th>ID</th>
                  <th>Riesgo</th>
                  <th>P/I</th>
                  <th>Cuadrante</th>
                  <th>Vinculado a RI</th>
                </tr>
              </thead>
              <tbody>
                {riesgosValidados.map(r => (
                  <tr key={r.id}>
                    <td>{areaName(r.area_id)}</td>
                    <td>{r.local_id}</td>
                    <td>{r.riesgo}</td>
                    <td>{r.probabilidad}/{r.impacto}</td>
                    <td><span className={`badge ${cuadrante(r.probabilidad, r.impacto).toLowerCase()}`}>{cuadrante(r.probabilidad, r.impacto)}</span></td>
                    <td>
                      {linked.has(r.id)
                        ? <span className="chip" style={{ background: '#d7ead2', color: '#2d7d46' }}>Sí</span>
                        : <span className="muted">—</span>
                      }
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </>
      }
    </section>
  );

  // ── Trazabilidad ──────────────────────────────────────────────────────────
  const renderTrace = () => {
    const rows = riesgosInstitucionales.flatMap(ir =>
      (ir.fuentes || []).map(f => ({ ir, f }))
    );
    return (
      <section className="panel">
        <h2>Trazabilidad institucional</h2>
        <table>
          <thead>
            <tr>
              <th>Riesgo institucional</th>
              <th>Área</th>
              <th>Riesgo fuente</th>
              <th>P/I fuente</th>
            </tr>
          </thead>
          <tbody>
            {rows.length === 0
              ? <tr><td colSpan="4" className="muted" style={{ textAlign: 'center' }}>Sin relaciones institucionales.</td></tr>
              : rows.map(({ ir, f }) => (
                <tr key={`${ir.id}-${f.id}`}>
                  <td>
                    <button className="icon-btn" onClick={() => { setSelectedRisk(ir); setDrawerOpen(true); }}>
                      {ir.folio}
                    </button>
                  </td>
                  <td>{areaName(f.area_id)}</td>
                  <td><b>{f.local_id || f.id}</b><br /><small>{f.riesgo}</small></td>
                  <td>{f.probabilidad}/{f.impacto}</td>
                </tr>
              ))
            }
          </tbody>
        </table>
      </section>
    );
  };

  // ── Drawer de detalle ─────────────────────────────────────────────────────
  const renderDrawer = () => {
    if (!selectedRisk || !drawerOpen) return null;
    const ir = selectedRisk;
    const src = ir.fuentes || [];
    const quad = cuadrante(ir.probabilidad, ir.impacto);
    const areaNames = [...new Set(src.map(f => areaName(f.area_id)))];

    return (
      <div className="drawer">
        <div className="drawer-head" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
          <div>
            <h2 style={{ margin: 0 }}>{ir.folio}</h2>
            <span className={`badge ${quad.toLowerCase()}`} style={{ marginTop: '6px', display: 'inline-block' }}>{quad}</span>
          </div>
          <button className="btn" onClick={() => setDrawerOpen(false)}>Cerrar</button>
        </div>

        <div className="detail-stack">
          <section>
            <b>Riesgo institucional</b>
            <p>{ir.riesgo}</p>
          </section>

          <section className="detail-grid">
            <div><span>Probabilidad</span><b>{ir.probabilidad}</b></div>
            <div><span>Impacto</span><b>{ir.impacto}</b></div>
            <div><span>Sugerencia técnica</span><b>{ir.probabilidad_sugerida}/{ir.impacto_sugerido}</b></div>
            <div><span>Estatus</span><b>{ir.estatus || 'Proyecto'}</b></div>
          </section>

          {ir.justificacion_valoracion && (
            <section>
              <b>Justificación de valoración</b>
              <p>{ir.justificacion_valoracion}</p>
            </section>
          )}

          <section>
            <b>Áreas involucradas</b>
            <div style={{ marginTop: '8px' }}>
              {areaNames.length > 0
                ? areaNames.map(a => <span key={a} className="chip" style={{ marginRight: '4px' }}>{a}</span>)
                : <span className="muted">Sin fuentes</span>
              }
            </div>
          </section>

          <section>
            <b>Riesgos fuente</b>
            <div className="source-list" style={{ marginTop: '8px' }}>
              {src.map(f => (
                <div key={f.id} className="source-link">
                  <b>{f.local_id || f.id}</b>
                  <span>{areaName(f.area_id)}</span>
                  <small>{f.riesgo}</small>
                </div>
              ))}
            </div>
          </section>

          <section>
            <b>Controles e indicadores de las fuentes</b>
            {src.map(f => (
              <div key={f.id} className="source-evidence">
                <b>{f.local_id || f.id}</b>
                <div><small>Controles: </small>{(f.controles || []).map(c => c.descripcion || c.nombre).join('; ') || '—'}</div>
                <div><small>Indicadores: </small>{(f.indicadores || []).map(i => i.nombre || i.indicador).join('; ') || '—'}</div>
              </div>
            ))}
          </section>
        </div>
      </div>
    );
  };

  return (
    <>
      <div className="page-head">
        <div>
          <h1>Mapa Institucional de Riesgos</h1>
          <p>MAR institucional, trazabilidad a las áreas y relaciones POA · {ejercicio}</p>
        </div>
        <button className="btn primary" onClick={() => navigate('/consolidacion')}>
          Consolidación institucional
        </button>
      </div>

      <div className="notice">
        <strong>Integridad de la fuente:</strong> este módulo no modifica los riesgos validados de las áreas. Cada RI conserva sus vínculos de origen.
      </div>

      {/* KPIs */}
      <div className="kpis institutional-kpis" style={{ gridTemplateColumns: 'repeat(4, minmax(150px, 1fr))' }}>
        <div className="kpi"><span>Riesgos institucionales</span><b>{riesgosInstitucionales.length}</b></div>
        <div className="kpi"><span>Riesgos validados</span><b>{riesgosValidados.length}</b></div>
        <div className="kpi"><span>Fuentes vinculadas</span><b>{linked.size}</b></div>
        <div className="kpi"><span>Áreas vinculadas</span><b>{areasVinculadas.size}</b></div>
      </div>

      {/* Tabs */}
      <div className="tabs">
        {[['consolidated', 'Vista consolidada'], ['sources', 'Vista por áreas'], ['trace', 'Trazabilidad']].map(([tab, label]) => (
          <button key={tab} className={`tab ${activeTab === tab ? 'active' : ''}`} onClick={() => setActiveTab(tab)}>
            {label}
          </button>
        ))}
      </div>

      {loading
        ? <div className="panel"><p className="muted" style={{ textAlign: 'center' }}>Cargando...</p></div>
        : activeTab === 'sources' ? renderSources()
        : activeTab === 'trace' ? renderTrace()
        : renderConsolidated()
      }

      {renderDrawer()}
    </>
  );
}
