import { useState, useEffect } from 'react';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';
import useAuth from '../../hooks/useAuth';
import { Edit, Save, Close } from '@mui/icons-material';
import { IconButton, Tooltip, Chip, Button, TextField, Alert, Snackbar, Dialog, DialogTitle, DialogContent, DialogActions } from '@mui/material';

export default function ControlesPage() {
  const [riesgos, setRiesgos] = useState([]);
  const [areas, setAreas] = useState([]);
  const [areaId, setAreaId] = useState('');
  const [loading, setLoading] = useState(true);
  const [editingId, setEditingId] = useState(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
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
  const { hasRole } = useAuth();
  const isSuperAdmin = hasRole('Super Administrador') || hasRole('superadmin') || hasRole('Admin');
  const allowedAreaIds = areas.map(a => String(a.unidad_responsable_gasto_id || a.id_unidad || a.id));
  const filtrados = riesgos.filter(r => {
    return areaId ? String(r.area_id) === String(areaId) : (isSuperAdmin || allowedAreaIds.includes(String(r.area_id)));
  });
  
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
    const formulaText = ind.formula || (ind.numerador && ind.denominador ? `Resultado = (${ind.numerador} / ${ind.denominador}) × 100` : ind.nombre || '');
    setEditForm({
      controlsText,
      indicatorName: ind.nombre || '',
      formula: formulaText,
      numerator: ind.numerador || '',
      denominator: ind.denominador || '',
      periodicidad: ind.periodicidad || 'Trimestral',
    });
    setIsModalOpen(true);
  };

  const handleCancelEdit = () => {
    setIsModalOpen(false);
    setEditingId(null);
  };

  const handleSaveEdit = async () => {
    const r = riesgos.find(risk => risk.id === editingId);
    if (!r) return;
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
      setIsModalOpen(false);
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
      <div style={{ color: '#1e293b', fontSize: '0.9rem', lineHeight: '1.45' }}>
        {r.controles.map(c => c.texto || c.control).filter(Boolean).join('; ')}
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
      <div style={{ 
        padding: '12px 14px', 
        border: '1px solid #cbd5e1', 
        borderRadius: '6px', 
        backgroundColor: '#ffffff', 
        color: '#0f172a',
        fontSize: '0.9rem',
        lineHeight: 1.45
      }}>
        <b>{formulaText}</b>
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
          <div style={{ overflowX: 'auto', width: '100%' }}>
            <table className="data-table" style={{ width: '100%', tableLayout: 'fixed' }}>
              <thead>
                <tr style={{ backgroundColor: '#1F4E79', color: '#fff' }}>
                  <th style={{ color: '#fff', width: '6%', textAlign: 'center' }}>ID</th>
                  <th style={{ color: '#fff', width: '20%' }}>Riesgo</th>
                  <th style={{ color: '#fff', width: '26%' }}>Control</th>
                  <th style={{ color: '#fff', width: '28%' }}>Indicador / fórmula</th>
                  <th style={{ color: '#fff', width: '10%', textAlign: 'center' }}>Periodicidad</th>
                  <th style={{ color: '#fff', width: '10%', textAlign: 'center' }}>Acciones</th>
                </tr>
              </thead>
              <tbody>
                {filtrados.map((r) => {
                  return (
                    <tr key={r.id} style={{ verticalAlign: 'top', backgroundColor: 'transparent' }}>
                      <td style={{ paddingTop: '15px', textAlign: 'center' }}>
                        <span style={{ 
                          fontWeight: 700, 
                          color: '#1e293b', 
                          fontSize: '0.9rem'
                        }}>
                          {r.local_id}
                        </span>
                      </td>
                      <td style={{ paddingTop: '15px', color: '#1e293b', fontSize: '0.9rem', lineHeight: 1.5 }}>
                        {r.riesgo}
                      </td>
                      <td style={{ paddingTop: '15px' }}>
                        {renderControles(r)}
                      </td>
                      <td style={{ paddingTop: '15px' }}>
                        {renderIndicador(r)}
                      </td>
                      <td style={{ paddingTop: '15px', textAlign: 'center' }}>
                        <span style={{ color: '#1e293b', fontSize: '0.9rem' }}>
                          {getPeriodicidadText(r)}
                        </span>
                      </td>
                      <td style={{ paddingTop: '15px', textAlign: 'center' }}>
                        <div style={{ display: 'flex', justifyContent: 'center', gap: '4px' }}>
                          <Tooltip title="Editar control e indicador">
                            <IconButton size="small" color="primary" onClick={() => handleStartEdit(r)}>
                              <Edit fontSize="small" />
                            </IconButton>
                          </Tooltip>
                        </div>
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

      <Dialog open={isModalOpen} onClose={handleCancelEdit} maxWidth="md" fullWidth>
        <DialogTitle sx={{ fontWeight: 'bold', borderBottom: '1px solid #e2e8f0', color: '#1e293b', display: 'flex', alignItems: 'center', gap: '8px' }}>
          <Edit color="primary" /> Editar Control - Indicador
        </DialogTitle>
        <DialogContent sx={{ mt: 1, pb: 1 }}>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
            {/* Row 1: Control */}
            <div>
              <h4 style={{ color: '#0369a1', margin: '0 0 6px 0' }}>Control</h4>
              <label style={{ display: 'block', marginBottom: '2px', fontSize: '0.85rem', fontWeight: 600, color: '#475569' }}>
                Texto del Control (uno por línea):
              </label>
              <TextField
                multiline
                rows={5}
                fullWidth
                size="small"
                value={editForm.controlsText}
                onChange={e => setEditForm(prev => ({ ...prev, controlsText: e.target.value }))}
                placeholder="Escribe el control..."
                variant="outlined"
              />
            </div>
            
            {/* Row 2: Indicador and Periodicidad */}
            <div>
              <h4 style={{ color: '#0369a1', margin: '0 0 6px 0' }}>Indicador</h4>
              <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
                <div>
                  <label style={{ display: 'block', marginBottom: '2px', fontSize: '0.85rem', fontWeight: 600, color: '#475569' }}>
                    Fórmula / Indicador:
                  </label>
                  <TextField
                    multiline
                    rows={3}
                    fullWidth
                    size="small"
                    value={editForm.formula}
                    onChange={e => setEditForm(prev => ({ ...prev, formula: e.target.value }))}
                    placeholder="Escribe la fórmula o nombre del indicador..."
                    variant="outlined"
                  />
                </div>
                
                <div>
                  <label style={{ display: 'block', marginBottom: '2px', fontSize: '0.85rem', fontWeight: 600, color: '#475569' }}>
                    Periodicidad:
                  </label>
                  <TextField
                    select
                    size="small"
                    fullWidth
                    value={editForm.periodicidad}
                    onChange={e => setEditForm(prev => ({ ...prev, periodicidad: e.target.value }))}
                    SelectProps={{ native: true }}
                  >
                    <option value="Mensual">Mensual</option>
                    <option value="Bimestral">Bimestral</option>
                    <option value="Trimestral">Trimestral</option>
                    <option value="Cuatrimestral">Cuatrimestral</option>
                    <option value="Semestral">Semestral</option>
                    <option value="Anual">Anual</option>
                  </TextField>
                </div>
              </div>
            </div>
          </div>
        </DialogContent>
        <DialogActions sx={{ padding: '16px 24px', borderTop: '1px solid #e2e8f0' }}>
          <Button onClick={handleCancelEdit} variant="outlined" color="inherit" disabled={saving}>
            Cancelar
          </Button>
          <Button onClick={handleSaveEdit} variant="contained" color="primary" startIcon={<Save />} disabled={saving}>
            {saving ? 'Guardando...' : 'Guardar Cambios'}
          </Button>
        </DialogActions>
      </Dialog>
    </>
  );
}
