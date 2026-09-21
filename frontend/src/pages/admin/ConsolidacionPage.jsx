import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';

const cuadrante = (p, i) => {
  p = Number(p); i = Number(i);
  if (p > 5 && i > 5) return 'QI';
  if (p > 5 && i <= 5) return 'QII';
  if (p <= 5 && i <= 5) return 'QIII';
  return 'QIV';
};

const getQuadClass = (q) => q.toLowerCase();

export default function ConsolidacionPage() {
  const [riesgosBase, setRiesgosBase] = useState([]);
  const [riesgosInstitucionales, setRiesgosInstitucionales] = useState([]);
  const [areas, setAreas] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedIds, setSelectedIds] = useState([]);
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [newIR, setNewIR] = useState({ objective: '', risk: '', justification: '', probability: 0, impact: 0 });
  const [suggested, setSuggested] = useState({ probability: 0, impact: 0, quadrant: '' });
  const [sourceRisks, setSourceRisks] = useState([]);

  const ejercicio = useGlobalStore((s) => s.ejercicio);
  const navigate = useNavigate();

  useEffect(() => {
    fetchData();
  }, [ejercicio]);

  const fetchData = async () => {
    setLoading(true);
    try {
      const [resBase, resInst, resAreas] = await Promise.all([
        axios.get(`/riesgos?ejercicio_id=${ejercicio}`),
        axios.get(`/riesgos-institucionales?ejercicio_id=${ejercicio}`),
        axios.get('/unidades-responsables')
      ]);
      
      const allRiesgos = resBase.data.data || resBase.data;
      setRiesgosBase(allRiesgos.filter(r => r.estatus === 'Validado'));
      setRiesgosInstitucionales(resInst.data.data || resInst.data);
      setAreas(resAreas.data.data || resAreas.data);
    } catch (e) {
      console.error(e);
    }
    setLoading(false);
  };

  const areaName = (id) => {
    const a = areas.find(x => String(x.unidad_responsable_gasto_id || x.id_unidad || x.id) === String(id));
    return a ? (a.nombre || a.denominacion) : id;
  };

  const handleCheckbox = (id) => {
    setSelectedIds(prev => prev.includes(id) ? prev.filter(x => x !== id) : [...prev, id]);
  };

  const handleOpenDrawer = () => {
    if (selectedIds.length === 0) {
      alert('Selecciona uno o más riesgos validados.');
      return;
    }
    const src = riesgosBase.filter(r => selectedIds.includes(r.id));
    setSourceRisks(src);

    const maxP = Math.max(...src.map(r => r.probabilidad || 0), 0);
    const maxI = Math.max(...src.map(r => r.impacto || 0), 0);
    setSuggested({
      probability: maxP,
      impact: maxI,
      quadrant: cuadrante(maxP, maxI)
    });

    setNewIR({
      objective: src[0]?.objetivo || '',
      risk: src[0]?.riesgo || '',
      justification: '',
      probability: maxP,
      impact: maxI
    });
    
    setDrawerOpen(true);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if ((newIR.probability !== suggested.probability || newIR.impact !== suggested.impact) && !newIR.justification.trim()) {
      alert('Cuando la valoración institucional difiere de la sugerida, registra una justificación.');
      return;
    }

    try {
      await axios.post('/riesgos-institucionales', {
        ejercicio_id: ejercicio,
        objetivo: newIR.objective,
        riesgo: newIR.risk,
        probabilidad_sugerida: suggested.probability,
        impacto_sugerido: suggested.impact,
        probabilidad: newIR.probability,
        impacto: newIR.impact,
        justificacion_valoracion: newIR.justification,
        sourceRiskIds: selectedIds,
        factores: sourceRisks.map(x => x.factores).filter(Boolean).join('; ')
      });
      setDrawerOpen(false);
      setSelectedIds([]);
      fetchData();
    } catch (err) {
      console.error(err);
      alert('Error guardando el riesgo institucional');
    }
  };

  return (
    <>
      <div className="page-head">
        <div>
          <h1>Consolidación institucional</h1>
          <p>Base automática inalterada y proyecto de MAR Institucional.</p>
        </div>
        <div className="head-actions">
          <button className="btn" onClick={() => navigate('/mapa-institucional')}>Mapa Institucional</button>
          <button className="btn primary" onClick={handleOpenDrawer}>Crear riesgo institucional a partir de…</button>
        </div>
      </div>
      <div className="notice" style={{ backgroundColor: '#eef4f8', padding: '16px', borderRadius: '8px', marginBottom: '24px', color: '#142b45', borderLeft: '4px solid #0b3a63' }}>
        <strong>Regla de trazabilidad:</strong> los riesgos fuente validados no se modifican desde esta sección. La valoración sugerida usa el mayor valor de probabilidad y de impacto de las fuentes.
      </div>

      <section className="panel" style={{ padding: '24px' }}>
        <h2 style={{ fontSize: '1.25rem', marginBottom: '20px', color: '#17324d' }}>Base Consolidada Automática</h2>
        <div style={{ overflowX: 'auto' }}>
          <table>
            <thead>
              <tr>
                <th></th>
                <th>Área</th>
                <th>ID</th>
                <th>Riesgo validado</th>
                <th>P/I</th>
                <th>Cuadrante</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr><td colSpan="6" className="muted" style={{ textAlign: 'center', padding: '20px' }}>Cargando...</td></tr>
              ) : riesgosBase.length > 0 ? (
                riesgosBase.map(r => (
                  <tr key={r.id}>
                    <td>
                      <input 
                        type="checkbox" 
                        checked={selectedIds.includes(r.id)}
                        onChange={() => handleCheckbox(r.id)} 
                      />
                    </td>
                    <td>{areaName(r.area_id)}</td>
                    <td>{r.local_id || r.id}</td>
                    <td>{r.riesgo}</td>
                    <td>{r.probabilidad}/{r.impacto}</td>
                    <td>
                      <span className={`badge ${cuadrante(r.probabilidad, r.impacto).toLowerCase()}`}>
                        {cuadrante(r.probabilidad, r.impacto)}
                      </span>
                    </td>
                  </tr>
                ))
              ) : (
                <tr><td colSpan="6" className="muted" style={{ textAlign: 'center', padding: '20px' }}>No hay riesgos validados en este ejercicio.</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </section>

      <section className="panel" style={{ padding: '24px' }}>
        <h2 style={{ fontSize: '1.25rem', marginBottom: '20px', color: '#17324d' }}>Proyecto de MAR Institucional</h2>
        <div style={{ overflowX: 'auto' }}>
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Riesgo institucional</th>
                <th>Fuentes</th>
                <th>Valor sugerido</th>
                <th>P/I institucional</th>
                <th>Justificación</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr><td colSpan="7" className="muted" style={{ textAlign: 'center', padding: '20px' }}>Cargando...</td></tr>
              ) : riesgosInstitucionales.length > 0 ? (
                riesgosInstitucionales.map(r => {
                  return (
                    <tr key={r.id}>
                      <td><b>{r.folio}</b></td>
                      <td>{r.riesgo}</td>
                      <td>
                        <div style={{ display: 'flex', gap: '4px', flexWrap: 'wrap' }}>
                          {(r.fuentes || []).map(f => (
                            <span key={f.id} className="chip">{f.local_id || f.id}</span>
                          ))}
                        </div>
                      </td>
                      <td>{r.probabilidad_sugerida}/{r.impacto_sugerido} · {cuadrante(r.probabilidad_sugerida, r.impacto_sugerido)}</td>
                      <td>
                        {r.probabilidad}/{r.impacto} · 
                        <span className={`badge ${cuadrante(r.probabilidad, r.impacto).toLowerCase()}`} style={{ marginLeft: '4px' }}>
                          {cuadrante(r.probabilidad, r.impacto)}
                        </span>
                      </td>
                      <td>
                        {r.justificacion_valoracion 
                          || ((r.probabilidad === r.probabilidad_sugerida && r.impacto === r.impacto_sugerido) 
                              ? <span className="muted">Coincide con sugerencia técnica</span> 
                              : <span className="muted">Pendiente de justificar</span>)}
                      </td>
                      <td>
                        <button className="icon-btn map-ir" onClick={() => navigate(`/mapa-institucional?riesgo_id=${r.id}`)}>
                          Ver mapa
                        </button>
                      </td>
                    </tr>
                  )
                })
              ) : (
                <tr><td colSpan="7" className="muted" style={{ textAlign: 'center', padding: '20px' }}>Aún no se han creado riesgos institucionales.</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </section>

      {/* Drawer */}
      <div className={`drawer ${drawerOpen ? '' : 'hidden'}`} style={{ backgroundColor: '#fff', borderLeft: '1px solid #ddd' }}>
        <div className="drawer-head">
          <h2>Nuevo riesgo institucional</h2>
        </div>
        <form className="form-grid" onSubmit={handleSubmit}>
          <div className="wide notice" style={{ backgroundColor: '#eef4f8', padding: '16px', borderRadius: '8px', color: '#142b45', borderLeft: '4px solid #0b3a63' }}>
            <div style={{ marginBottom: '8px' }}>
              <b>Fuentes seleccionadas:</b>
              <div style={{ display: 'flex', gap: '4px', flexWrap: 'wrap', marginTop: '4px' }}>
                {sourceRisks.map(r => (
                  <span key={r.id} className="chip">{r.local_id} · {areaName(r.area_id)}</span>
                ))}
              </div>
            </div>
            <div>
              <b>Valoración técnica sugerida:</b> P={suggested.probability}, I={suggested.impact}, {suggested.quadrant}. Esta sugerencia no sustituye la valoración institucional.
            </div>
          </div>
          
          <label className="wide">
            Objetivo institucional
            <textarea className="input" value={newIR.objective} onChange={e => setNewIR(p => ({ ...p, objective: e.target.value }))}></textarea>
          </label>
          <label className="wide">
            Riesgo institucional
            <textarea className="input" value={newIR.risk} required onChange={e => setNewIR(p => ({ ...p, risk: e.target.value }))}></textarea>
          </label>
          <label>
            Probabilidad
            <input className="input" type="number" min="0" max="10" step="1" value={newIR.probability} onChange={e => setNewIR(p => ({ ...p, probability: Number(e.target.value) }))} />
          </label>
          <label>
            Impacto
            <input className="input" type="number" min="0" max="10" step="1" value={newIR.impact} onChange={e => setNewIR(p => ({ ...p, impact: Number(e.target.value) }))} />
          </label>
          <label className="wide">
            Justificación de la valoración institucional <span className="muted" style={{ fontWeight: 'normal', fontSize: '0.85em' }}>(obligatoria si difiere de la sugerida)</span>
            <textarea className="input" value={newIR.justification} onChange={e => setNewIR(p => ({ ...p, justification: e.target.value }))} placeholder="Explique por qué la valoración institucional difiere de la sugerida."></textarea>
          </label>
          <div className="wide actions" style={{ marginTop: '16px' }}>
            <button className="btn primary" type="submit">Crear riesgo institucional</button>
            <button className="btn" type="button" onClick={() => setDrawerOpen(false)}>Cancelar</button>
          </div>
        </form>
      </div>
    </>
  );
}
