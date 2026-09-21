import { useState, useEffect } from 'react';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';
import { Edit, Save, Close } from '@mui/icons-material';
import { IconButton, Tooltip, Chip, Button, TextField, Alert, Snackbar } from '@mui/material';

export default function ControlesPage() {
  const [riesgos, setRiesgos] = useState([]);
  const [areas, setAreas] = useState([]);
  const [areaId, setAreaId] = useState('');
  const [loading, setLoading] = useState(true);
  const [editingId, setEditingId] = useState(null);
  const [editForm, setEditForm] = useState({
    controlsText: '',
    indicatorName: '',
    formula: '',
    numerator: '',
    denominator: '',
    periodicidad: 'Trimestral',
  });
  const [saving, setSaving] = useState(false);
  const [notification, setNotification] = useState({ open: false, message: '', severity: 'success' });
  const ejercicio = useGlobalStore((s) => s.ejercicio);

  useEffect(() => {
    axios.get('/unidades-responsables').then(res => {
      const data = res.data.data || res.data;
      setAreas(data);
      if (data.length > 0) {
        // Look for Secretaría General first if available, else first area
        const secGen = data.find(a => (a.nombre || a.denominacion || '').toLowerCase().includes('secretar') && (a.nombre || a.denominacion || '').toLowerCase().includes('general'));
        const target = secGen || data[0];
        const initialId = String(target.unidad_responsable_gasto_id || target.id_unidad || target.id);
        setAreaId(initialId);
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
    } catch (err) {
      console.error('Error fetching riesgos:', err);
    } finally {
      setLoading(false);
    }
  };

  const getIndicadorObj = (r) => {
    if (Array.isArray(r.indicadores) && r.indicadores.length > 0) return r.indicadores[0];
    if (r.indicador) return r.indicador;
    return null;
  };

  const handleStartEdit = (r) => {
    setEditingId(r.id);
    const controlsText = (r.controles || []).map(c => c.texto || c.control || '').filter(Boolean).join('\n');
    const ind = getIndicadorObj(r) || {};
    setEditForm({
      controlsText,
      indicatorName: ind.nombre || '',
      formula: ind.formula || '',
      numerator: ind.numerador || '',
      denominator: ind.denominador || '',
      periodicidad: ind.periodicidad || 'Trimestral',
    });
  };

  const handleCancelEdit = () => {
    setEditingId(null);
  };

  const handleSaveEdit = async (r) => {
    setSaving(true);
    try {
      const currentInd = getIndicadorObj(r) || {};
      
      const newControls = editForm.controlsText
        .split('\n')
        .map(t => t.trim())
        .filter(Boolean)
        .map((texto, idx) => ({
          texto,
          estado_validacion: r.controles?.[idx]?.estado_validacion || 'Propuesto – pendiente de validación',
          evidencia_tipo: r.controles?.[idx]?.evidencia_tipo || '',
          evidencia_referencia: r.controles?.[idx]?.evidencia_referencia || '',
          evidencia_responsable: r.controles?.[idx]?.evidencia_responsable || '',
          evidencia_periodicidad: r.controles?.[idx]?.evidencia_periodicidad || '',
        }));

      const num = editForm.numerator.trim();
      const den = editForm.denominator.trim();
      let formula = editForm.formula.trim();
      if (!formula) {
        formula = (num && den)
          ? `Resultado = (${num} / ${den}) × 100`
          : currentInd.formula || 'Resultado = (N / D) × 100';
      }

      const newIndicators = [
        {
          nombre: editForm.indicatorName.trim() || currentInd.nombre || 'Indicador de Riesgo',
          formula,
          numerador: num,
          denominador: den,
          unidad: currentInd.unidad || 'Porcentaje',
          periodicidad: editForm.periodicidad || currentInd.periodicidad || 'Trimestral',
          sentido: currentInd.sentido || 'Ascendente',
        }
      ];

      const payload = {
        ...r,
        controles: newControls,
        indicadores: newIndicators,
      };

      await axios.put(`/riesgos/${r.id}`, payload);
      setNotification({ open: true, message: 'Control e indicador actualizados correctamente.', severity: 'success' });
      setEditingId(null);
      await fetchData();
    } catch (error) {
      console.error('Error saving risk details:', error);
      setNotification({ open: true, message: 'Error al guardar los cambios.', severity: 'error' });
    } finally {
      setSaving(false);
    }
  };

  const renderControles = (r) => {
    if (!r.controles || r.controles.length === 0) {
      return <span style={{ color: '#94a3b8', fontStyle: 'italic' }}>Sin controles registrados</span>;
    }
    return (
      <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
        {r.controles.map((c, idx) => (
          <div key={idx} style={{ 
            backgroundColor: '#f8fafc', 
            border: '1px solid #e2e8f0', 
            borderRadius: '6px', 
            padding: '10px 12px',
            boxShadow: '0 1px 2px rgba(0,0,0,0.03)'
          }}>
            <div style={{ color: '#1e293b', fontSize: '0.88rem', lineHeight: '1.45', fontWeight: 500 }}>
              {c.texto || c.control || '—'}
            </div>
            <div style={{ marginTop: '6px', display: 'flex', flexWrap: 'wrap', gap: '6px', alignItems: 'center' }}>
              <Chip 
                label={c.estado_validacion || 'Propuesto – pendiente de validación'} 
                size="small" 
                variant="outlined"
                color={c.estado_validacion?.toLowerCase().includes('validado') ? 'success' : 'default'}
                sx={{ fontSize: '0.72rem', height: '22px' }}
              />
              {c.evidencia_responsable && (
                <span style={{ fontSize: '0.74rem', color: '#64748b' }}>
                  <b>Resp:</b> {c.evidencia_responsable}
                </span>
              )}
            </div>
          </div>
        ))}
      </div>
    );
  };

  const renderIndicador = (r) => {
    const ind = getIndicadorObj(r);
    if (!ind) {
      return <span style={{ color: '#94a3b8', fontStyle: 'italic' }}>Sin indicadores</span>;
    }

    const formulaText = ind.formula || (ind.numerador && ind.denominador ? `Resultado = (${ind.numerador} / ${ind.denominador}) × 100` : ind.nombre || '—');

    return (
      <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
        {ind.nombre && (
          <div style={{ fontWeight: 600, color: '#0f172a', fontSize: '0.88rem', lineHeight: 1.4 }}>
            {ind.nombre}
          </div>
        )}
        <div style={{ 
          padding: '10px 12px', 
          border: '1px solid #cbd5e1', 
          borderRadius: '6px', 
          backgroundColor: '#f1f5f9', 
          color: '#1e3a8a',
          fontSize: '0.84rem',
          fontFamily: 'monospace, monospace',
          wordBreak: 'break-word',
          lineHeight: 1.45
        }}>
          <b>{formulaText}</b>
        </div>
        {(ind.numerador || ind.denominador) && (
          <div style={{ 
            backgroundColor: '#ffffff', 
            border: '1px solid #e2e8f0', 
            borderRadius: '6px', 
            padding: '8px 10px',
            display: 'flex',
            flexDirection: 'column',
            gap: '4px',
            fontSize: '0.8rem'
          }}>
            {ind.numerador && (
              <div style={{ color: '#334155', lineHeight: 1.4 }}>
                <span style={{ fontWeight: 700, color: '#0369a1' }}>Numerador (N): </span>
                {ind.numerador}
              </div>
            )}
            {ind.denominador && (
              <div style={{ color: '#334155', lineHeight: 1.4 }}>
                <span style={{ fontWeight: 700, color: '#0369a1' }}>Denominador (D): </span>
                {ind.denominador}
              </div>
            )}
          </div>
        )}
        <div style={{ fontSize: '0.76rem', color: '#64748b' }}>
          Unidad: <b>{ind.unidad || 'Porcentaje'}</b> · Sentido: <b>{ind.sentido || 'Ascendente'}</b>
        </div>
      </div>
    );
  };

  const getPeriodicidadText = (r) => {
    const ind = getIndicadorObj(r);
    return ind?.periodicidad || 'Trimestral';
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
                  {a.numero ? `${a.numero} - ` : ''}{a.nombre || a.denominacion}
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
          <div style={{ overflowX: 'auto' }}>
            <table className="data-table" style={{ width: '100%', minWidth: '1100px' }}>
              <thead>
                <tr style={{ backgroundColor: '#1F4E79', color: '#fff' }}>
                  <th style={{ color: '#fff', width: '70px', textAlign: 'center' }}>ID</th>
                  <th style={{ color: '#fff', width: '20%' }}>Riesgo</th>
                  <th style={{ color: '#fff', width: '28%' }}>Control</th>
                  <th style={{ color: '#fff', width: '32%' }}>Indicador / fórmula</th>
                  <th style={{ color: '#fff', width: '110px', textAlign: 'center' }}>Periodicidad</th>
                  <th style={{ color: '#fff', width: '90px', textAlign: 'center' }}>Acciones</th>
                </tr>
              </thead>
              <tbody>
                {riesgos.map((r) => {
                  const isEditing = editingId === r.id;
                  return (
                    <tr key={r.id} style={{ verticalAlign: 'top', backgroundColor: isEditing ? '#f8fafc' : 'transparent' }}>
                      <td style={{ paddingTop: '15px', textAlign: 'center' }}>
                        <span style={{ 
                          fontWeight: 700, 
                          color: '#1F4E79', 
                          backgroundColor: '#e0f2fe', 
                          padding: '3px 8px', 
                          borderRadius: '4px',
                          fontSize: '0.85rem'
                        }}>
                          {r.local_id}
                        </span>
                      </td>
                      <td style={{ paddingTop: '15px', color: '#1e293b', fontSize: '0.9rem', lineHeight: 1.5 }}>
                        <b>{r.riesgo}</b>
                      </td>
                      <td style={{ paddingTop: '15px' }}>
                        {isEditing ? (
                          <div>
                            <label style={{ display: 'block', marginBottom: '4px', fontSize: '0.78rem', fontWeight: 600, color: '#475569' }}>
                              Texto del Control (uno por línea):
                            </label>
                            <TextField
                              multiline
                              rows={4}
                              fullWidth
                              size="small"
                              value={editForm.controlsText}
                              onChange={e => setEditForm(prev => ({ ...prev, controlsText: e.target.value }))}
                              placeholder="Escribe el control..."
                            />
                          </div>
                        ) : (
                          renderControles(r)
                        )}
                      </td>
                      <td style={{ paddingTop: '15px' }}>
                        {isEditing ? (
                          <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                            <div>
                              <label style={{ display: 'block', marginBottom: '2px', fontSize: '0.78rem', fontWeight: 600, color: '#475569' }}>
                                Nombre del Indicador:
                              </label>
                              <TextField
                                fullWidth
                                size="small"
                                value={editForm.indicatorName}
                                onChange={e => setEditForm(prev => ({ ...prev, indicatorName: e.target.value }))}
                              />
                            </div>
                            <div>
                              <label style={{ display: 'block', marginBottom: '2px', fontSize: '0.78rem', fontWeight: 600, color: '#475569' }}>
                                Fórmula de Cálculo:
                              </label>
                              <TextField
                                fullWidth
                                size="small"
                                value={editForm.formula}
                                onChange={e => setEditForm(prev => ({ ...prev, formula: e.target.value }))}
                                placeholder="Resultado = (N / D) × 100"
                              />
                            </div>
                            <div>
                              <label style={{ display: 'block', marginBottom: '2px', fontSize: '0.78rem', fontWeight: 600, color: '#0369a1' }}>
                                Numerador (N):
                              </label>
                              <TextField
                                multiline
                                rows={2}
                                fullWidth
                                size="small"
                                value={editForm.numerator}
                                onChange={e => setEditForm(prev => ({ ...prev, numerator: e.target.value }))}
                              />
                            </div>
                            <div>
                              <label style={{ display: 'block', marginBottom: '2px', fontSize: '0.78rem', fontWeight: 600, color: '#0369a1' }}>
                                Denominador (D):
                              </label>
                              <TextField
                                multiline
                                rows={2}
                                fullWidth
                                size="small"
                                value={editForm.denominator}
                                onChange={e => setEditForm(prev => ({ ...prev, denominator: e.target.value }))}
                              />
                            </div>
                          </div>
                        ) : (
                          renderIndicador(r)
                        )}
                      </td>
                      <td style={{ paddingTop: '15px', textAlign: 'center' }}>
                        {isEditing ? (
                          <TextField
                            size="small"
                            value={editForm.periodicidad}
                            onChange={e => setEditForm(prev => ({ ...prev, periodicidad: e.target.value }))}
                            sx={{ width: '100px' }}
                          />
                        ) : (
                          <Chip 
                            label={getPeriodicidadText(r)} 
                            size="small"
                            sx={{ backgroundColor: '#e2e8f0', color: '#1e293b', fontWeight: 600, fontSize: '0.78rem' }}
                          />
                        )}
                      </td>
                      <td style={{ paddingTop: '15px', textAlign: 'center' }}>
                        {isEditing ? (
                          <div style={{ display: 'flex', flexDirection: 'column', gap: '6px', alignItems: 'center' }}>
                            <Button 
                              variant="contained" 
                              size="small" 
                              color="primary" 
                              startIcon={<Save fontSize="small" />}
                              onClick={() => handleSaveEdit(r)}
                              disabled={saving}
                              sx={{ textTransform: 'none', minWidth: '90px' }}
                            >
                              Guardar
                            </Button>
                            <Button 
                              variant="outlined" 
                              size="small" 
                              color="inherit" 
                              startIcon={<Close fontSize="small" />}
                              onClick={handleCancelEdit}
                              disabled={saving}
                              sx={{ textTransform: 'none', minWidth: '90px' }}
                            >
                              Cancelar
                            </Button>
                          </div>
                        ) : (
                          <div style={{ display: 'flex', justifyContent: 'center', gap: '4px' }}>
                            <Tooltip title="Editar control e indicador">
                              <IconButton size="small" color="primary" onClick={() => handleStartEdit(r)}>
                                <Edit fontSize="small" />
                              </IconButton>
                            </Tooltip>
                          </div>
                        )}
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}
      </section>

      <Snackbar
        open={notification.open}
        autoHideDuration={4000}
        onClose={() => setNotification(prev => ({ ...prev, open: false }))}
        anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
      >
        <Alert 
          onClose={() => setNotification(prev => ({ ...prev, open: false }))} 
          severity={notification.severity} 
          variant="filled"
          sx={{ width: '100%', boxShadow: 3 }}
        >
          {notification.message}
        </Alert>
      </Snackbar>
    </>
  );
}
