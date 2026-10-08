import { useState, useEffect } from 'react';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';
import useAuth from '../../hooks/useAuth';
import SeguimientoTrimestralModal from './SeguimientoTrimestralModal';
import EditIcon from '@mui/icons-material/Edit';
import CheckCircleOutlineIcon from '@mui/icons-material/CheckCircleOutline';
import SettingsIcon from '@mui/icons-material/Settings';
import ConfigurarTrimestresModal from './ConfigurarTrimestresModal';
import ConfirmValidateDialog from '../../components/ui/ConfirmValidateDialog';
import AlertDialog from '../../components/ui/AlertDialog';

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
  const [trimestresConfig, setTrimestresConfig] = useState({ t1_abierto: false, t2_abierto: false, t3_abierto: false, t4_abierto: false });
  const [loading, setLoading] = useState(false);
  
  const [modalOpen, setModalOpen] = useState(false);
  const [configModalOpen, setConfigModalOpen] = useState(false);
  const [validateConfirmData, setValidateConfirmData] = useState(null);
  const [alertData, setAlertData] = useState({ open: false, title: '', message: '', type: 'warning' });
  const [activeRiesgo, setActiveRiesgo] = useState(null);
  const [activeQuarter, setActiveQuarter] = useState(null);
  const [initialModalData, setInitialModalData] = useState(null);

  const ejercicio = useGlobalStore((s) => s.ejercicio);
  const user = useAuth().user;
  
  const hasRole = (role) => {
    if (!user) return false;
    if (user.role === 'Administrador') return true;
    return user.role === role;
  };

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
          (r.seguimiento_trimestral || []).forEach(st => {
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

  const openEvaluacionModal = (r, q, existingData) => {
    setActiveRiesgo(r);
    setActiveQuarter(q);
    setInitialModalData(existingData);
    setModalOpen(true);
  };

  const handleSaveEvaluacion = async (data) => {
    const key = `${activeRiesgo.id}_${data.trimestre}`;
    setModalOpen(false);

    try {
      const res = await axios.post('/seguimiento-trimestral', {
        riesgo_id: activeRiesgo.id,
        ejercicio_id: ejercicio,
        ...data,
      });
      setEvaluaciones(prev => ({ ...prev, [key]: res.data }));
      fetchRiesgos();
    } catch (e) {
      console.error(e);
      setAlertData({ open: true, title: 'Error', message: 'Error guardando el seguimiento trimestral.', type: 'error' });
    }
  };

  const fetchRiesgos = async () => {
    if (!areaId || !ejercicio) return;
    setLoading(true);
    try {
      const [resRiesgos, resConfig] = await Promise.all([
        axios.get(`/riesgos?ejercicio_id=${ejercicio}&area_id=${areaId}`),
        axios.get(`/configuracion-trimestres/${areaId}/${ejercicio}`)
      ]);
      const data = resRiesgos.data.data || resRiesgos.data;
      setRiesgos(data);
      const eval_ = {};
      data.forEach(r => {
        (r.seguimiento_trimestral || []).forEach(st => {
          eval_[`${r.id}_${st.trimestre}`] = st;
        });
      });
      setEvaluaciones(eval_);
      const isTrue = (val) => val === 1 || val === true || val === '1';
      setTrimestresConfig({
        t1_abierto: isTrue(resConfig.data.t1_abierto),
        t2_abierto: isTrue(resConfig.data.t2_abierto),
        t3_abierto: isTrue(resConfig.data.t3_abierto),
        t4_abierto: isTrue(resConfig.data.t4_abierto)
      });
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  const handleValidarTrimestre = async (r, q, evalData) => {
    if (!evalData || !evalData.id) {
      setAlertData({ open: true, title: 'Captura incompleta', message: 'Debes capturar datos primero antes de poder validar.', type: 'warning' });
      return;
    }

    const missingFields = [];
    const meses = MESES.slice((q - 1) * 3, q * 3);
    
    if (evalData.m1_n === null || evalData.m1_n === '') missingFields.push(`Numerador de ${meses[0]}`);
    if (evalData.m1_d === null || evalData.m1_d === '') missingFields.push(`Denominador de ${meses[0]}`);
    if (evalData.m2_n === null || evalData.m2_n === '') missingFields.push(`Numerador de ${meses[1]}`);
    if (evalData.m2_d === null || evalData.m2_d === '') missingFields.push(`Denominador de ${meses[1]}`);
    if (evalData.m3_n === null || evalData.m3_n === '') missingFields.push(`Numerador de ${meses[2]}`);
    if (evalData.m3_d === null || evalData.m3_d === '') missingFields.push(`Denominador de ${meses[2]}`);
    
    if (missingFields.length > 0) {
      setAlertData({ 
        open: true, 
        title: 'Faltan datos', 
        message: `Para validar el trimestre, primero debes capturar los siguientes campos faltantes: ${missingFields.join(', ')}.`, 
        type: 'warning' 
      });
      return;
    }

    setValidateConfirmData({ r, q, stId: evalData.id });
  };

  const confirmValidar = async () => {
    if (!validateConfirmData) return;
    const { r, q, stId } = validateConfirmData;
    setLoading(true);
    try {
      const res = await axios.put(`/seguimiento-trimestral/${stId}/validar`);
      setEvaluaciones(prev => ({ ...prev, [`${r.id}_${q}`]: res.data }));
    } catch (e) {
      console.error(e);
      setAlertData({ open: true, title: 'Error', message: 'Error validando el trimestre.', type: 'error' });
    } finally {
      setLoading(false);
      setValidateConfirmData(null);
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
          <h1 style={{ fontSize: '1.25rem' }}>Seguimiento Trimestral de la MAR</h1>
          <p>Capture numerador y denominador; el resultado porcentual se calcula automáticamente.</p>
        </div>
        <div style={{ display: 'flex', gap: '12px', alignItems: 'center' }}>
          {hasRole('Administrador') && (
            <button
              onClick={() => setConfigModalOpen(true)}
              style={{ display: 'flex', alignItems: 'center', gap: '6px', padding: '8px 12px', borderRadius: '6px', border: '1px solid #cbd5e1', background: '#fff', color: '#475569', cursor: 'pointer', fontSize: '0.85rem', fontWeight: 'bold' }}
            >
              <SettingsIcon fontSize="small" />
              Configurar
            </button>
          )}
          <select className="input" style={{ maxWidth: 320 }} value={areaId} onChange={e => setAreaId(e.target.value)}>
            {areas.map((a, i) => (
              <option key={a.unidad_responsable_gasto_id || a.id_unidad || a.id || i} value={String(a.unidad_responsable_gasto_id || a.id_unidad || a.id)}>
                {a.nombre || a.denominacion}
              </option>
            ))}
          </select>
        </div>
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
        return (
          <section className="panel" key={r.id} style={{ width: '100%', marginBottom: '16px', padding: '16px 20px' }}>
            <div style={{ marginBottom: '12px', lineHeight: '1.4', fontSize: '0.9rem', color: '#334155' }}>
              <div style={{ marginBottom: '4px' }}>
                <b style={{ color: '#0f172a' }}>{r.local_id || r.id}.</b> {escapeHtml(r.riesgo)}
              </div>
              <div style={{ marginBottom: '4px' }}>
                <b style={{ color: '#0f172a' }}>Controles.</b> {r.controles && r.controles.length > 0 ? r.controles.map(c => c.texto || c.control || c.descripcion).filter(Boolean).join('; ') : '—'}
              </div>
              <div>
                <b style={{ color: '#0f172a' }}>Indicadores.</b> {
                  (r.indicadores || []).map((i) => {
                    let formulaText = i.formula;
                    if (!formulaText || formulaText === 'Resultado = (N / D) × 100') {
                      formulaText = (i.numerador && i.denominador) 
                        ? `Resultado = (${i.numerador} / ${i.denominador}) × 100` 
                        : i.nombre || '';
                    }
                    return formulaText;
                  }).filter(Boolean).join('; ') || '—'
                }
              </div>
            </div>

            <div style={{ display: 'flex', gap: '16px', overflowX: 'auto', paddingBottom: '4px' }}>
              {[1, 2, 3, 4].map(trimestre => {
                const mesesSubset = MESES.slice((trimestre - 1) * 3, trimestre * 3);
                const evalData = evaluaciones[`${r.id}_${trimestre}`] || null;
                const isValidado = evalData?.estatus === 'Validado';
                const isAbierto = trimestresConfig[`t${trimestre}_abierto`] && !isValidado;
                
                // Forzamos temporalmente a true para que cualquier rol pueda verlos y probar
                const canEdit = true;
                const canValidate = true;

                return (
                  <div key={trimestre} style={{ position: 'relative', flex: 1, minWidth: '220px', border: `1px solid ${isValidado ? '#86efac' : '#cbd5e1'}`, padding: '16px 10px 12px 10px', borderRadius: '6px', margin: 0, backgroundColor: isValidado ? '#f0fdf4' : '#f8fafc', marginTop: '12px' }}>
                    <div style={{ position: 'absolute', top: '-11px', left: '8px', fontWeight: 'bold', color: isValidado ? '#166534' : '#1e293b', fontSize: '0.9rem', padding: '0 6px', backgroundColor: isValidado ? '#f0fdf4' : '#f8fafc' }}>
                      Trimestre {trimestre}
                    </div>

                    <div style={{ position: 'absolute', top: '-13px', right: '12px', display: 'flex', gap: '2px', backgroundColor: isValidado ? '#f0fdf4' : '#f8fafc', padding: '2px 4px', borderRadius: '4px', alignItems: 'center' }}>
                      {canEdit && (
                        <button
                          title="Editar"
                          onClick={() => openEvaluacionModal(r, trimestre, evalData)}
                          disabled={!isAbierto}
                          style={{ background: 'none', border: 'none', cursor: isAbierto ? 'pointer' : 'not-allowed', color: isAbierto ? '#3b82f6' : '#94a3b8', padding: 0, margin: 0, display: 'flex' }}
                        >
                          <EditIcon fontSize="small" style={{ fontSize: '1.1rem' }} />
                        </button>
                      )}
                      {canValidate && !isValidado && (
                        <button
                          title="Validar"
                          onClick={() => handleValidarTrimestre(r, trimestre, evalData)}
                          disabled={!trimestresConfig[`t${trimestre}_abierto`]}
                          style={{ background: 'none', border: 'none', cursor: trimestresConfig[`t${trimestre}_abierto`] ? 'pointer' : 'not-allowed', color: trimestresConfig[`t${trimestre}_abierto`] ? '#10b981' : '#94a3b8', padding: 0, margin: 0, display: 'flex' }}
                        >
                          <CheckCircleOutlineIcon fontSize="small" style={{ fontSize: '1.1rem' }} />
                        </button>
                      )}
                    </div>
                    <div style={{ display: 'flex', gap: '8px' }}>
                      {mesesSubset.map((mes, index) => {
                        const num = index + 1;
                        let nVal = evalData ? evalData[`m${num}_n`] : '';
                        let dVal = evalData ? evalData[`m${num}_d`] : '';
                        let rVal = '';
                        if (nVal !== null && dVal !== null && Number(dVal) > 0) {
                           rVal = ((Number(nVal) / Number(dVal)) * 100).toFixed(1) + '%';
                        }

                        return (
                          <div key={mes} style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: '4px' }}>
                            <div style={{ fontWeight: '600', textAlign: 'center', color: '#475569', fontSize: '0.8rem', textTransform: 'uppercase' }}>{mes}</div>
                            <input
                              className="input"
                              type="text"
                              placeholder="N"
                              disabled={!trimestresConfig[`t${trimestre}_abierto`]}
                              readOnly
                              style={{ width: '100%', padding: '2px 4px', textAlign: 'center', fontSize: '0.75rem', backgroundColor: !trimestresConfig[`t${trimestre}_abierto`] ? '#f1f5f9' : '#ffffff', cursor: !trimestresConfig[`t${trimestre}_abierto`] ? 'not-allowed' : 'default', minHeight: '22px' }}
                              value={nVal ?? ''}
                            />
                            <input
                              className="input"
                              type="text"
                              placeholder="D"
                              disabled={!trimestresConfig[`t${trimestre}_abierto`]}
                              readOnly
                              style={{ width: '100%', padding: '2px 4px', textAlign: 'center', fontSize: '0.75rem', backgroundColor: !trimestresConfig[`t${trimestre}_abierto`] ? '#f1f5f9' : '#ffffff', cursor: !trimestresConfig[`t${trimestre}_abierto`] ? 'not-allowed' : 'default', minHeight: '22px' }}
                              value={dVal ?? ''}
                            />
                            <output style={{ textAlign: 'center', backgroundColor: isValidado ? '#dcfce7' : '#e2e8f0', padding: '2px 4px', borderRadius: '4px', fontSize: '0.75rem', fontWeight: 'bold', color: isValidado ? '#166534' : '#0f172a', minHeight: '22px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                              {rVal || '—'}
                            </output>
                          </div>
                        );
                      })}
                    </div>
                  </div>
                );
              })}
            </div>
          </section>
        );
      })}

      <SeguimientoTrimestralModal
        isOpen={modalOpen}
        onClose={() => setModalOpen(false)}
        activeRiesgo={activeRiesgo}
        activeQuarter={activeQuarter}
        initialData={initialModalData}
        onSave={handleSaveEvaluacion}
        isReadOnly={false}
      />

      <ConfigurarTrimestresModal
        isOpen={configModalOpen}
        onClose={() => { setConfigModalOpen(false); fetchRiesgos(); }}
        urId={areaId}
        areas={areas}
        ejercicioId={ejercicio}
      />

      <ConfirmValidateDialog
        open={Boolean(validateConfirmData)}
        onClose={() => setValidateConfirmData(null)}
        onConfirm={confirmValidar}
        title="Validar Trimestre"
        message={`¿Estás seguro de que deseas validar los datos capturados para este trimestre? Una vez validado, ya no podrá ser editado por el capturador.`}
      />

      <AlertDialog 
        open={alertData.open}
        onClose={() => setAlertData(prev => ({ ...prev, open: false }))}
        title={alertData.title}
        message={alertData.message}
        type={alertData.type}
      />
    </>
  );
}
