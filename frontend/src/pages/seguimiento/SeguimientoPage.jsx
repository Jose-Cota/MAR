import { useState, useEffect } from 'react';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';
import useAuth from '../../hooks/useAuth';
import EvaluacionTrimestralModal from './EvaluacionTrimestralModal';

const MESES = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

const cuadrante = (p, i) => {
  p = Number(p); i = Number(i);
  if (p > 5 && i > 5) return 'QI';
  if (p > 5 && i <= 5) return 'QII';
  if (p <= 5 && i <= 5) return 'QIII';
  return 'QIV';
};

const trimestreLabel = (q, y) => {
  if (y === 2026 && q <= 3) return 'Integración inicial 2026';
  return 'Seguimiento ordinario';
};

export default function SeguimientoPage() {
  const [areas, setAreas] = useState([]);
  const [areaId, setAreaId] = useState('');
  const [riesgos, setRiesgos] = useState([]);
  const [seguimientos, setSeguimientos] = useState({}); // { riesgoId_mes: { numerador, denominador, valor } }
  const [evaluaciones, setEvaluaciones] = useState({}); // { riesgoId_trimestre: { ...data } }
  const [loading, setLoading] = useState(false);
  
  const [modalOpen, setModalOpen] = useState(false);
  const [activeRiesgo, setActiveRiesgo] = useState(null);
  const [activeQuarter, setActiveQuarter] = useState(null);

  const ejercicio = useGlobalStore((s) => s.ejercicio);
  const user = useAuth().user;

  useEffect(() => {
    axios.get('/unidades-responsables').then(res => {
      const data = res.data.data || res.data;
      setAreas(data);
      if (data.length > 0) setAreaId(String(data[0].unidad_responsable_gasto_id || data[0].id_unidad || data[0].id));
    });
  }, []);

  useEffect(() => {
    if (!areaId || !ejercicio) return;
    setLoading(true);
    axios.get(`/riesgos?ejercicio_id=${ejercicio}&area_id=${areaId}`)
      .then(res => {
        const data = res.data.data || res.data;
        setRiesgos(data);
        const seg = {};
        const eval_ = {};
        data.forEach(r => {
          (r.seguimientos_mensuales || []).forEach(sm => {
            seg[`${r.id}_${sm.mes}`] = {
              numerador: sm.numerador ?? '',
              denominador: sm.denominador ?? '',
              valor: sm.valor ?? ''
            };
          });
          (r.evaluaciones_trimestrales || []).forEach(st => {
            eval_[`${r.id}_${st.trimestre}`] = st;
          });
        });
        setSeguimientos(seg);
        setEvaluaciones(eval_);
      })
      .catch(() => setRiesgos([]))
      .finally(() => setLoading(false));
  }, [areaId, ejercicio]);

  const handleNDChange = async (riesgoId, mes, field, rawValue) => {
    const key = `${riesgoId}_${mes}`;
    const value = rawValue === '' ? '' : Number(rawValue);

    setSeguimientos(prev => {
      const current = prev[key] || { numerador: '', denominador: '', valor: '' };
      const updated = { ...current, [field]: value };
      
      let finalValor = '';
      if (updated.numerador !== '' && updated.denominador !== '' && Number(updated.denominador) > 0) {
        finalValor = (Number(updated.numerador) / Number(updated.denominador)) * 100;
      }
      updated.valor = finalValor;

      // Make API call inside state updater logic or use a timeout to avoid blocking.
      // We do it asynchronously here:
      axios.post('/riesgos/seguimiento-mensual', {
        riesgo_id: riesgoId,
        ejercicio_id: ejercicio,
        mes,
        numerador: updated.numerador === '' ? null : updated.numerador,
        denominador: updated.denominador === '' ? null : updated.denominador,
        valor: updated.valor === '' ? null : updated.valor,
      }).catch(err => console.error(err));

      return { ...prev, [key]: updated };
    });
  };

  const openEvaluacionModal = (r, q) => {
    setActiveRiesgo(r);
    setActiveQuarter(q);
    setModalOpen(true);
  };

  const handleSaveEvaluacion = async (data) => {
    const { trimestre } = data;
    const key = `${activeRiesgo.id}_${trimestre}`;
    
    // Add missing defaults
    if (!data.responsable) data.responsable = user?.name || '';
    if (!data.etiqueta) data.etiqueta = trimestreLabel(trimestre, Number(ejercicio));

    setEvaluaciones(prev => ({ ...prev, [key]: data }));
    setModalOpen(false);

    try {
      await axios.post('/riesgos/evaluacion-trimestral', {
        riesgo_id: activeRiesgo.id,
        ejercicio_id: ejercicio,
        ...data,
      });
    } catch (e) {
      console.error(e);
      alert('Error guardando la evaluación trimestral.');
    }
  };

  const getTrimestrePromedio = (riesgoId, q) => {
    const meses = [1,2,3].map(m => (q-1)*3 + m);
    const vals = meses
      .map(m => seguimientos[`${riesgoId}_${m}`]?.valor)
      .filter(v => v !== '' && v !== null && v !== undefined && !isNaN(Number(v)))
      .map(Number);
    if (!vals.length) return '—';
    return (vals.reduce((a, b) => a + b, 0) / vals.length).toFixed(1) + '%';
  };

  const escapeHtml = (text) => text; // React escapes by default

  return (
    <>
      <div className="page-head">
        <div>
          <h1>Seguimiento POA–MAR</h1>
          <p>Capture numerador y denominador; el resultado porcentual se calcula automáticamente.</p>
        </div>
        <select className="input" style={{ maxWidth: 320 }} value={areaId} onChange={e => setAreaId(e.target.value)}>
          {areas.map((a, i) => (
            <option key={a.unidad_responsable_gasto_id || a.id_unidad || a.id || i} value={String(a.unidad_responsable_gasto_id || a.id_unidad || a.id)}>
              {a.nombre || a.denominacion}
            </option>
          ))}
        </select>
      </div>

      {Number(ejercicio) === 2026 && (
        <div style={{
          padding: '16px 20px', 
          backgroundColor: '#f0f5fa', 
          borderLeft: '4px solid #0b3a63', 
          borderRadius: '4px',
          color: '#142b45',
          marginBottom: '20px'
        }}>
          <strong>Implementación 2026:</strong> T1, T2 y T3 son integración inicial retrospectiva enero–septiembre; T4 es seguimiento ordinario.
        </div>
      )}

      {loading && <div className="notice">Cargando…</div>}

      {!loading && riesgos.length === 0 && (
        <div className="empty">Sin riesgos registrados para esta área.</div>
      )}

      {riesgos.map(r => {
        const i = (r.indicadores || [])[0] || {};
        const isIncidenciaOrDirect = i.unidad === 'Incidencia' || (!i.numerador && !i.denominador);
        const formulaDisplay = isIncidenciaOrDirect 
          ? (i.formula || i.nombre || 'Resultado') 
          : 'Resultado = (N / D) × 100';

        return (
          <section className="panel follow-card" key={r.id}>
            <div className="follow-title">
              <div><b>{r.local_id}</b> {escapeHtml(r.riesgo)}</div>
              <span className={`badge ${cuadrante(r.probabilidad, r.impacto).toLowerCase()}`}>
                {cuadrante(r.probabilidad, r.impacto)}
              </span>
            </div>

            <div style={{ marginBottom: '24px' }}>
              <div style={{ 
                display: 'inline-block', 
                backgroundColor: '#f1f5f9', 
                padding: '8px 16px', 
                borderRadius: '8px', 
                fontWeight: 'bold', 
                color: '#0b3a63',
                marginBottom: '16px',
                border: '1px solid #e2e8f0'
              }}>
                {formulaDisplay}
              </div>
              {(i.numerador || i.denominador) && (
                <div style={{ display: 'flex', flexDirection: 'column', gap: '10px', color: '#142b45', fontSize: '1rem' }}>
                  <div style={{ display: 'flex', alignItems: 'flex-start', gap: '12px' }}>
                    <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
                      <b style={{ color: '#0b3a63' }}>N</b>
                      <b style={{ color: '#0b3a63' }}>=</b>
                    </div>
                    <span style={{ paddingTop: '2px' }}>{i.numerador || 'Numerador'}</span>
                  </div>
                  {i.denominador && (
                    <div style={{ display: 'flex', alignItems: 'flex-start', gap: '12px' }}>
                      <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
                        <b style={{ color: '#0b3a63' }}>D</b>
                        <b style={{ color: '#0b3a63' }}>=</b>
                      </div>
                      <span style={{ paddingTop: '2px' }}>{i.denominador}</span>
                    </div>
                  )}
                </div>
              )}
            </div>

            {/* Captura mensual */}
            <div className="month-grid">
              {MESES.map((mes, mi) => {
                const mesNum = mi + 1;
                const key = `${r.id}_${mesNum}`;
                const segData = seguimientos[key] || {};
                
                return (
                  <label key={mes}>
                    {mes}
                    <small>N</small>
                    <input
                      className="input v40Numerator"
                      type="number"
                      min="0"
                      step="0.01"
                      value={segData.numerador ?? ''}
                      onChange={e => handleNDChange(r.id, mesNum, 'numerador', e.target.value)}
                    />
                    <small>D</small>
                    <input
                      className="input v40Denominator"
                      type="number"
                      min="0"
                      step="0.01"
                      value={segData.denominador ?? ''}
                      onChange={e => handleNDChange(r.id, mesNum, 'denominador', e.target.value)}
                    />
                    <output className="v40Result">
                      {segData.valor !== '' && segData.valor !== undefined && segData.valor !== null && !isNaN(Number(segData.valor))
                        ? Number(segData.valor).toFixed(1) + '%'
                        : '—'}
                    </output>
                  </label>
                );
              })}
            </div>

          </section>
        );
      })}

    </>
  );
}
