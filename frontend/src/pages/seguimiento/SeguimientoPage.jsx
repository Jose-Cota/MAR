import { useState, useEffect } from 'react';
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

const trimestreLabel = (q) => {
  const ranges = { 1: 'Ene–Mar', 2: 'Abr–Jun', 3: 'Jul–Sep', 4: 'Oct–Dic' };
  return ranges[q] || `T${q}`;
};

export default function SeguimientoPage() {
  const [areas, setAreas] = useState([]);
  const [areaId, setAreaId] = useState('');
  const [riesgos, setRiesgos] = useState([]);
  const [seguimiento, setSeguimiento] = useState({}); // { riesgoId_mes: valor }
  const [evaluaciones, setEvaluaciones] = useState({}); // { riesgoId_trimestre: { probabilidad, impacto, obs } }
  const [loading, setLoading] = useState(false);
  const ejercicio = useGlobalStore((s) => s.ejercicio);

  useEffect(() => {
    axios.get('/unidades-responsables').then(res => {
      const data = res.data.data || res.data;
      setAreas(data);
      if (data.length > 0) setAreaId(String(data[0].unidad_responsable_gasto_id || data[0].id_unidad || data[0].id));
    });
  }, []);

  useEffect(() => {
    if (!areaId) return;
    setLoading(true);
    axios.get(`/riesgos?ejercicio_id=${ejercicio}&area_id=${areaId}`)
      .then(res => {
        const data = res.data.data || res.data;
        setRiesgos(data);
        // Pre-populate seguimiento from existing data
        const seg = {};
        const eval_ = {};
        data.forEach(r => {
          (r.seguimiento_mensual || []).forEach(sm => {
            seg[`${r.id}_${sm.mes}`] = sm.valor ?? '';
          });
          (r.seguimiento_trimestral || []).forEach(st => {
            eval_[`${r.id}_${st.trimestre}`] = { probabilidad: st.probabilidad, impacto: st.impacto, obs: st.observacion || '' };
          });
        });
        setSeguimiento(seg);
        setEvaluaciones(eval_);
      })
      .catch(() => setRiesgos([]))
      .finally(() => setLoading(false));
  }, [areaId, ejercicio]);

  const handleMesChange = async (riesgoId, mes, valor) => {
    const key = `${riesgoId}_${mes}`;
    setSeguimiento(prev => ({ ...prev, [key]: valor }));
    try {
      await axios.post('/riesgos/seguimiento-mensual', {
        riesgo_id: riesgoId,
        ejercicio_id: ejercicio,
        mes,
        valor: valor === '' ? null : Number(valor),
      });
    } catch { /* ignore */ }
  };

  const handleRegistrarEvaluacion = async (r, q) => {
    const currentEval = evaluaciones[`${r.id}_${q}`];
    const pVal = prompt(`T${q} — Probabilidad de seguimiento (0-10):`, currentEval?.probabilidad ?? r.probabilidad);
    if (pVal === null) return;
    const iVal = prompt(`T${q} — Impacto de seguimiento (0-10):`, currentEval?.impacto ?? r.impacto);
    if (iVal === null) return;
    const obs = prompt('Observaciones del trimestre (opcional):', currentEval?.obs || '') || '';

    const evalData = { probabilidad: Number(pVal), impacto: Number(iVal), obs };
    setEvaluaciones(prev => ({ ...prev, [`${r.id}_${q}`]: evalData }));

    try {
      await axios.post('/riesgos/seguimiento-trimestral', {
        riesgo_id: r.id,
        ejercicio_id: ejercicio,
        trimestre: q,
        probabilidad: Number(pVal),
        impacto: Number(iVal),
        observacion: obs,
      });
    } catch { /* ignore */ }
  };

  const getTrimestrePromedio = (riesgoId, q) => {
    const meses = [1,2,3].map(m => (q-1)*3 + m);
    const vals = meses
      .map(m => seguimiento[`${riesgoId}_${m}`])
      .filter(v => v !== '' && v !== undefined && !isNaN(Number(v)))
      .map(Number);
    if (!vals.length) return '—';
    return (vals.reduce((a, b) => a + b, 0) / vals.length).toFixed(1) + '%';
  };

  return (
    <>
      <div className="page-head">
        <div>
          <h1>Seguimiento POA–MAR</h1>
          <p>Indicadores mensuales y evaluación trimestral del riesgo.</p>
        </div>
        <select className="input" style={{ maxWidth: 320 }} value={areaId} onChange={e => setAreaId(e.target.value)}>
          {areas.map((a, i) => (
            <option key={a.unidad_responsable_gasto_id || a.id_unidad || a.id || i} value={String(a.unidad_responsable_gasto_id || a.id_unidad || a.id)}>
              {a.nombre || a.denominacion}
            </option>
          ))}
        </select>
      </div>

      {loading && <div className="notice">Cargando…</div>}

      {!loading && riesgos.length === 0 && (
        <div className="empty">Sin riesgos registrados para esta área.</div>
      )}

      {riesgos.map(r => (
        <section className="panel follow-card" key={r.id}>
          <div className="follow-title">
            <div><b>{r.local_id}</b> {r.riesgo}</div>
            <span className={`badge ${cuadrante(r.probabilidad, r.impacto).toLowerCase()}`}>
              {cuadrante(r.probabilidad, r.impacto)}
            </span>
          </div>

          {/* Captura mensual */}
          <div className="month-grid" style={{ marginTop: 12 }}>
            {MESES.map((mes, mi) => {
              const mesNum = mi + 1;
              const key = `${r.id}_${mesNum}`;
              return (
                <label key={mes}>
                  {mes}
                  <input
                    className="input month-val"
                    type="number"
                    step="0.01"
                    placeholder="%"
                    value={seguimiento[key] ?? ''}
                    onChange={e => handleMesChange(r.id, mesNum, e.target.value)}
                  />
                </label>
              );
            })}
          </div>

          {/* Evaluaciones trimestrales */}
          <div className="quarters">
            {[1, 2, 3, 4].map(q => {
              const eval_ = evaluaciones[`${r.id}_${q}`];
              const prom = getTrimestrePromedio(r.id, q);
              return (
                <div className="quarter" key={q}>
                  <b>T{q}</b>
                  <span>{trimestreLabel(q)}</span>
                  <small>Promedio POA: {prom}</small>
                  <div>P/I seguimiento: {eval_ ? `${eval_.probabilidad}/${eval_.impacto}` : 'sin capturar'}</div>
                  {eval_?.obs && <small style={{ color: '#6f8294' }}>Obs: {eval_.obs}</small>}
                  <button className="icon-btn qreview" onClick={() => handleRegistrarEvaluacion(r, q)}>
                    Registrar evaluación
                  </button>
                </div>
              );
            })}
          </div>
        </section>
      ))}
    </>
  );
}
