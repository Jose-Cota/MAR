import { useState, useEffect } from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  TextField,
  MenuItem,
  Alert,
  Stack,
  Box,
  Typography,
  Card,
  IconButton,
  LinearProgress,
  DialogContentText,
} from '@mui/material';
import axios from '../../../utils/axios';
import useGlobalStore from '../../../stores/useGlobalStore';
import Iconify from '../../../components/Iconify';
import ValidationModal from '../../../components/ui/ValidationModal';

const TYPE_LABELS = {
  elaboracion_proyectos: 'Elaboración',
  seguimiento_proyectos: 'Seguimiento',
  anteproyecto: 'Anteproyecto',
};

export default function ModoFormPanel({ open, onClose, modo, onSuccess }) {
  const globalEjercicio = useGlobalStore((state) => state.ejercicio);
  const isEdit = Boolean(modo);

  const [formData, setFormData] = useState({
    tipo: 'elaboracion_proyectos',
    ejercicio: [2025, 2026, 2027].includes(Number(globalEjercicio)) ? globalEjercicio : 2026,
    habilitado: 'no',
    fecha_inicio: '',
    fecha_fin: '',
  });

  const [unidades, setUnidades] = useState([]);
  const [selectedURs, setSelectedURs] = useState([]);
  const [prorrogas, setProrrogas] = useState({});
  const [ejerciciosList, setEjerciciosList] = useState([]);

  const [error, setError] = useState('');
  const [showErrorModal, setShowErrorModal] = useState(false);
  const [validationErrors, setValidationErrors] = useState([]);
  const [validationModalOpen, setValidationModalOpen] = useState(false);
  const [showConfirmClone, setShowConfirmClone] = useState(false);
  const [loading, setLoading] = useState(false);
  const [loadingUnidades, setLoadingUnidades] = useState(false);
  const [draggedUrId, setDraggedUrId] = useState(null);

  useEffect(() => {
    fetchEjercicios();
  }, []);

  const fetchEjercicios = async () => {
    try {
      const res = await axios.get('/catalogos/ejercicios');
      setEjerciciosList(res.data);
    } catch (err) {
      console.error('Error fetching ejercicios', err);
    }
  };

  useEffect(() => {
    if (open) {
      if (isEdit) {
        fetchModoData(modo.operacion_ejercicio_id);
      } else {
        setFormData({
          tipo: 'elaboracion_proyectos',
          ejercicio: [2025, 2026, 2027].includes(Number(globalEjercicio)) ? globalEjercicio : 2026,
          habilitado: 'no',
          fecha_inicio: '',
          fecha_fin: '',
        });
        setUnidades([]);
        setSelectedURs([]);
        setProrrogas({});
        setError('');
        setLoading(false);
        setShowConfirmClone(false);
      }
    }
  }, [open, modo]);

  const handleEjercicioChange = async (e) => {
    const newEjercicio = e.target.value;
    setFormData(prev => ({ ...prev, ejercicio: newEjercicio }));
    
    if (isEdit) {
      setLoadingUnidades(true);
      try {
        const res = await axios.get(`/modos?ejercicio=${newEjercicio}`);
        const foundModo = res.data.find(m => m.tipo === modo.tipo);
        
        if (foundModo) {
          fetchModoData(foundModo.operacion_ejercicio_id);
        } else {
          setFormData(prev => ({
            ...prev,
            habilitado: 'no',
            fecha_inicio: '',
            fecha_fin: ''
          }));
          setUnidades([]);
          setSelectedURs([]);
          setProrrogas({}); // new mode
          setError(`No existe configuración de ${TYPE_LABELS[modo.tipo]} para el año ${newEjercicio}.`);
        }
      } catch (err) {
        console.error(err);
      } finally {
        setLoadingUnidades(false);
      }
    }
  };

  const fetchModoData = async (modoId) => {
    setLoadingUnidades(true);
    setError('');
    try {
      const res = await axios.get(`/modos/${modoId}`);
      const data = res.data;
      
      setFormData({
        tipo: data.modo.tipo,
        ejercicio: data.ejercicio,
        habilitado: data.modo.habilitado || 'no',
        fecha_inicio: data.modo.fecha_inicio || '',
        fecha_fin: data.modo.fecha_fin || '',
      });

      setUnidades(data.unidades || []);
      const selected = data.unidades
        .filter(u => u.habilitado === 'si')
        .map(u => u.unidad_responsable_gasto_id);
      setSelectedURs(selected);
      const prMap = {};
      data.unidades.filter(u => u.habilitado === 'si').forEach(u => {
        prMap[u.unidad_responsable_gasto_id] = u.fecha_prorroga || '';
      });
      setProrrogas(prMap);

    } catch (err) {
      console.error(err);
      setError('No se pudo cargar la configuración de la etapa.');
    } finally {
      setLoadingUnidades(false);
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
    if (name === 'habilitado' && value === 'no') {
      setSelectedURs([]);
    }
  };

  const handleSubmit = async () => {
    setError('');
    
    if (formData.fecha_inicio && formData.fecha_fin) {
      if (new Date(formData.fecha_inicio) > new Date(formData.fecha_fin)) {
        setValidationErrors(['La fecha de fin debe ser posterior a la fecha de inicio.']);
        setValidationModalOpen(true);
        return;
      }
    }
    if (!isEdit && formData.tipo === 'elaboracion_proyectos' && !showConfirmClone) {
      setShowConfirmClone(true);
      return;
    }

    executeSubmit();
  };

  const executeSubmit = async () => {
    setShowConfirmClone(false);
    setLoading(true);
    try {
        let stats = null;
        if (isEdit) {
          const res = await axios.get(`/modos?ejercicio=${formData.ejercicio}`);
          const currentModo = res.data.find(m => m.tipo === formData.tipo);
          
          if (!currentModo) {
              throw new Error('La etapa no existe para este ejercicio. Cree una nueva primero.');
          }

          await axios.put(`/modos/${currentModo.operacion_ejercicio_id}`, {
            ...formData,
            unidades: selectedURs.map(id => ({
              id,
              fecha_prorroga: prorrogas[id] || null
            }))
          });
        } else {
          const res = await axios.post(`/modos`, {
            ejercicio: formData.ejercicio,
            tipo: formData.tipo,
            fecha_inicio: formData.fecha_inicio,
            fecha_fin: formData.fecha_fin
          });
          stats = res.data?.stats;
        }
        
        if (onSuccess) onSuccess(stats);
    } catch (err) {
      console.error(err);
      const errorMsg = err.response?.data?.message || err.message || 'Error al guardar la etapa.';
      setError(errorMsg);
      if (err.response?.status === 400 || err.response?.status === 500) {
        setShowErrorModal(true);
      }
    } finally {
      setLoading(false);
    }
  };

  // Drag and Drop Logic
  const handleDragStart = (e, id) => {
    setDraggedUrId(id);
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', id);
    e.target.style.opacity = '0.5';
  };

  const handleDragEnd = (e) => {
    e.target.style.opacity = '1';
    setDraggedUrId(null);
  };

  const handleDragOver = (e) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
  };

  const handleProrrogaChange = (id, value) => {
    setProrrogas(prev => ({ ...prev, [id]: value }));
  };

  const handleDropToEnabled = (e) => {
    e.preventDefault();
    if (draggedUrId && !selectedURs.includes(draggedUrId)) {
      setSelectedURs([...selectedURs, draggedUrId]);
    }
  };

  const handleDropToDisabled = (e) => {
    e.preventDefault();
    if (draggedUrId && selectedURs.includes(draggedUrId)) {
      setSelectedURs(selectedURs.filter(id => id !== draggedUrId));
    }
  };

  const habilitarTodos = () => {
    setSelectedURs(unidades.map(u => u.unidad_responsable_gasto_id));
  };

  const deshabilitarTodos = () => {
    setSelectedURs([]);
  };

  const enabledURGs = unidades.filter(u => selectedURs.includes(u.unidad_responsable_gasto_id));
  const disabledURGs = unidades.filter(u => !selectedURs.includes(u.unidad_responsable_gasto_id));

  return (
    <Dialog open={open} onClose={onClose} maxWidth="lg" fullWidth PaperProps={{ sx: { borderRadius: 2 } }}>
      <DialogTitle sx={{ bgcolor: '#1F4E79', color: '#fff', display: 'flex', justifyContent: 'space-between', alignItems: 'center', p: 2 }}>
        <Typography variant="h6" component="div">
          {isEdit ? `Configuración de Etapa: ${TYPE_LABELS[formData.tipo] || formData.tipo}` : 'Agregar Nueva Etapa'}
        </Typography>
        <IconButton onClick={onClose} sx={{ color: '#fff' }} size="small">
          <Iconify icon="mdi:close" />
        </IconButton>
      </DialogTitle>
      {loading && (
        <Box sx={{ width: '100%', position: 'absolute', top: 64, zIndex: 9999 }}>
          <LinearProgress color="info" />
          <Typography variant="caption" sx={{ display: 'block', textAlign: 'center', bgcolor: 'rgba(255,255,255,0.9)', color: '#1F4E79', fontWeight: 'bold', py: 0.5 }}>
            {!isEdit && formData.tipo === 'elaboracion_proyectos' ? 'Procesando y clonando información, por favor espere...' : 'Guardando configuración...'}
          </Typography>
        </Box>
      )}
      <DialogContent sx={{ p: 3, bgcolor: '#F6F4F0' }}>
        {error && <Alert severity="error" sx={{ mb: 2, mt: 1 }}>{error}</Alert>}
        
        <Card sx={{ p: 2, mb: 3, borderRadius: 2, boxShadow: '0 2px 8px rgba(0,0,0,0.05)' }}>
          <Stack direction={{ xs: 'column', sm: 'row' }} spacing={2}>
            {!isEdit && (
              <TextField select fullWidth label="Tipo de Etapa" name="tipo" value={formData.tipo} onChange={handleChange} size="small">
                {Object.entries(TYPE_LABELS).map(([key, label]) => (
                  <MenuItem key={key} value={key}>{label}</MenuItem>
                ))}
              </TextField>
            )}
            <TextField select fullWidth label="Ejercicio" name="ejercicio" value={formData.ejercicio} onChange={handleEjercicioChange} size="small">
              {(isEdit ? ejerciciosList.map(ej => Number(ej.ejercicio)) : [2025, 2026, 2027])
                .map(year => (
                <MenuItem key={year} value={year}>{year}</MenuItem>
              ))}
            </TextField>
            <TextField select fullWidth label="Estado Global de la Etapa" name="habilitado" value={formData.habilitado} onChange={handleChange} size="small">
              <MenuItem value="si">Habilitado</MenuItem>
              <MenuItem value="no">Deshabilitado</MenuItem>
            </TextField>
          </Stack>
          <Stack direction={{ xs: 'column', sm: 'row' }} spacing={2} sx={{ mt: 2 }}>
            <TextField type="date" fullWidth label="Fecha de Inicio" name="fecha_inicio" value={formData.fecha_inicio} onChange={handleChange} InputLabelProps={{ shrink: true }} size="small" />
            <TextField type="date" fullWidth label="Fecha de Fin" name="fecha_fin" value={formData.fecha_fin} onChange={handleChange} InputLabelProps={{ shrink: true }} size="small" />
          </Stack>
        </Card>

        {isEdit && (
          <Box>
            <Typography variant="h6" sx={{ fontFamily: '"Bitter", serif', color: '#143352', mb: 1 }}>
              Unidades Responsables de Gasto (Accesos)
            </Typography>
            <Typography variant="body2" sx={{ color: '#6E6560', mb: 2 }}>
              Arrastra las tarjetas de izquierda a derecha para habilitar o deshabilitar el acceso al módulo seleccionado.
            </Typography>
            
            <Stack direction="row" spacing={3} sx={{ height: 400 }}>
              {/* Columna Deshabilitados */}
              <Box 
                onDragOver={handleDragOver} 
                onDrop={handleDropToDisabled}
                sx={{ 
                  flex: 1, 
                  bgcolor: '#fff', 
                  borderRadius: 2, 
                  border: '2px dashed #E0DDD7', 
                  display: 'flex', 
                  flexDirection: 'column',
                  overflow: 'hidden',
                  transition: 'background-color 0.2s',
                  '&:hover': { bgcolor: '#FBFAF7' }
                }}
              >
                <Box sx={{ p: 2, bgcolor: '#f4f6f8', borderBottom: '1px solid #E0DDD7', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <Typography fontWeight={600} color="text.secondary">
                    Inactivas ({disabledURGs.length})
                  </Typography>
                  <Button size="small" variant="outlined" color="inherit" onClick={deshabilitarTodos} disabled={selectedURs.length === 0}>
                    Quitar todas
                  </Button>
                </Box>
                <Box sx={{ p: 2, overflowY: 'auto', flex: 1, display: 'flex', flexWrap: 'wrap', gap: 1, alignContent: 'flex-start' }}>
                  {loadingUnidades ? <Typography>Cargando...</Typography> : disabledURGs.map(ur => (
                    <Box
                      key={ur.unidad_responsable_gasto_id}
                      draggable
                      onDragStart={(e) => handleDragStart(e, ur.unidad_responsable_gasto_id)}
                      onDragEnd={handleDragEnd}
                      sx={{
                        p: 1.5,
                        bgcolor: '#fff',
                        border: '1px solid #e0e0e0',
                        borderRadius: 1.5,
                        cursor: 'grab',
                        display: 'flex',
                        alignItems: 'center',
                        gap: 1,
                        width: '100%',
                        boxShadow: '0 1px 3px rgba(0,0,0,0.05)',
                        '&:active': { cursor: 'grabbing' }
                      }}
                    >
                      <Iconify icon="mdi:drag-vertical" sx={{ color: '#bdbdbd' }} />
                      <Typography variant="body2" fontWeight={500} noWrap title={`${ur.numero} ${ur.nombre}`}>
                        {ur.numero} {ur.nombre}
                      </Typography>
                    </Box>
                  ))}
                  {!loadingUnidades && disabledURGs.length === 0 && (
                    <Typography variant="body2" color="text.disabled" sx={{ width: '100%', textAlign: 'center', mt: 4 }}>
                      Todas las unidades están habilitadas.
                    </Typography>
                  )}
                </Box>
              </Box>

              {/* Controles del centro */}
              <Box sx={{ display: 'flex', flexDirection: 'column', justifyContent: 'center', gap: 2 }}>
                <Iconify icon="mdi:swap-horizontal" width={32} sx={{ color: '#ccc' }} />
              </Box>

              {/* Columna Habilitados */}
              <Box 
                onDragOver={handleDragOver} 
                onDrop={handleDropToEnabled}
                sx={{ 
                  flex: 1, 
                  bgcolor: '#E8EFF6', 
                  borderRadius: 2, 
                  border: '2px dashed #3E7CB1', 
                  display: 'flex', 
                  flexDirection: 'column',
                  overflow: 'hidden',
                  transition: 'background-color 0.2s',
                  '&:hover': { bgcolor: '#dbe7f2' }
                }}
              >
                <Box sx={{ p: 2, bgcolor: '#1F4E79', color: '#fff', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <Typography fontWeight={600}>
                    Habilitadas ({enabledURGs.length})
                  </Typography>
                  <Button size="small" variant="contained" sx={{ bgcolor: '#3E7CB1', '&:hover': { bgcolor: '#2c5f8a' } }} onClick={habilitarTodos} disabled={selectedURs.length === unidades.length}>
                    Agregar todas
                  </Button>
                </Box>
                <Box sx={{ p: 2, overflowY: 'auto', flex: 1, display: 'flex', flexWrap: 'wrap', gap: 1, alignContent: 'flex-start' }}>
                  {loadingUnidades ? <Typography>Cargando...</Typography> : enabledURGs.map(ur => (
                    <Box
                      key={ur.unidad_responsable_gasto_id}
                      draggable
                      onDragStart={(e) => handleDragStart(e, ur.unidad_responsable_gasto_id)}
                      onDragEnd={handleDragEnd}
                      sx={{
                        p: 1.5,
                        bgcolor: '#fff',
                        border: '1px solid #3E7CB1',
                        borderLeft: '4px solid #3E7CB1',
                        borderRadius: 1.5,
                        cursor: 'grab',
                        display: 'flex',
                        alignItems: 'center',
                        gap: 1,
                        width: '100%',
                        boxShadow: '0 2px 5px rgba(31,78,121,0.1)',
                        '&:active': { cursor: 'grabbing' }
                      }}
                    >
                      <Iconify icon="mdi:drag-vertical" sx={{ color: '#3E7CB1' }} />
                      <Typography variant="body2" fontWeight={600} color="#1F4E79" noWrap title={`${ur.numero} ${ur.nombre}`} sx={{ flex: 1 }}>
                        {ur.numero} {ur.nombre}
                      </Typography>
                      <TextField 
                        type="datetime-local" 
                        size="small" 
                        InputLabelProps={{ shrink: true }}
                        value={prorrogas[ur.unidad_responsable_gasto_id] || ''}
                        onChange={(e) => handleProrrogaChange(ur.unidad_responsable_gasto_id, e.target.value)}
                        sx={{ width: 180, bgcolor: '#fff' }}
                        title="Fecha de prórroga"
                      />
                    </Box>
                  ))}
                  {!loadingUnidades && enabledURGs.length === 0 && (
                    <Typography variant="body2" sx={{ color: '#1F4E79', opacity: 0.7, width: '100%', textAlign: 'center', mt: 4 }}>
                      Arrastra unidades aquí para habilitarlas.
                    </Typography>
                  )}
                </Box>
              </Box>
            </Stack>
          </Box>
        )}
      </DialogContent>
      <DialogActions sx={{ bgcolor: '#F6F4F0', p: 2, borderTop: '1px solid #e0e0e0' }}>
        <Button onClick={onClose} color="inherit" disabled={loading} sx={{ fontWeight: 600 }}>
          Cancelar
        </Button>
        <Button onClick={handleSubmit} variant="contained" sx={{ bgcolor: '#1F4E79', fontWeight: 600 }} disabled={loading}>
          {isEdit ? 'Guardar Configuración' : 'Crear Etapa'}
        </Button>
      </DialogActions>

      {/* Styled Error Modal */}
      <Dialog 
        open={showErrorModal} 
        onClose={() => setShowErrorModal(false)}
        PaperProps={{ sx: { borderRadius: 3, p: 2, textAlign: 'center', maxWidth: 400 } }}
      >
        <DialogTitle sx={{ pt: 3, pb: 1 }}>
          <Iconify icon="mdi:alert-decagram" sx={{ color: '#d32f2f', width: 64, height: 64, mb: 2 }} />
          <Typography variant="h5" color="error.main" fontWeight="bold">
            Acción Bloqueada
          </Typography>
        </DialogTitle>
        <DialogContent>
          <Typography variant="body1" sx={{ color: 'text.secondary', mt: 1 }}>
            {error}
          </Typography>
        </DialogContent>
        <DialogActions sx={{ justifyContent: 'center', pb: 2 }}>
          <Button 
            variant="contained" 
            color="error" 
            onClick={() => setShowErrorModal(false)}
            sx={{ borderRadius: 2, px: 4, py: 1 }}
          >
            Entendido
          </Button>
        </DialogActions>
      </Dialog>

      {/* Styled Confirm Clone Modal */}
      <Dialog 
        open={showConfirmClone} 
        onClose={() => setShowConfirmClone(false)}
        PaperProps={{ sx: { borderRadius: 3, p: 1, maxWidth: 450 } }}
      >
        <DialogTitle sx={{ pt: 2, pb: 1, display: 'flex', alignItems: 'center', gap: 1, color: '#1F4E79' }}>
          <Iconify icon="mdi:content-copy" width={28} />
          <Typography variant="h6" fontWeight="bold">
            Clonar Información
          </Typography>
        </DialogTitle>
        <DialogContent>
          <DialogContentText sx={{ mt: 1, color: 'text.primary' }}>
            Estás a punto de crear una etapa de <strong>Elaboración</strong>. Esto iniciará el proceso automático que clonará toda la información del año anterior, incluyendo:
            <br /><br />
            Unidades de Medida, el catálogo PEI (Líneas y Objetivos Estratégicos), Programas, Subprogramas, Unidades Responsables (URG), Responsables Operativos, Proyectos, Metas, Indicadores y Actividades.
            <br /><br />
            Este proceso puede tardar unos segundos. ¿Deseas continuar?
          </DialogContentText>
        </DialogContent>
        <DialogActions sx={{ pb: 2, px: 3 }}>
          <Button 
            onClick={() => setShowConfirmClone(false)} 
            color="inherit"
            sx={{ fontWeight: 'bold' }}
          >
            Cancelar
          </Button>
          <Button 
            variant="contained" 
            onClick={executeSubmit}
            sx={{ bgcolor: '#1F4E79', fontWeight: 'bold' }}
          >
            Sí, continuar y clonar
          </Button>
        </DialogActions>
      </Dialog>

      <ValidationModal open={validationModalOpen} onClose={() => setValidationModalOpen(false)} errors={validationErrors} />
    </Dialog>
  );
}
