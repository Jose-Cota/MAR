import React, { useState, useEffect } from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  TextField,
  MenuItem,
  Box,
  Typography,
  Chip,
  Alert,
} from '@mui/material';
import axios from '../utils/axios';
import Iconify from './Iconify';
import ValidationModal from './ui/ValidationModal';

export default function IndicadorDialog({ open, onClose, onSave, indicador = null, metaComplementaria = null, metaPrincipal = null, proyectoId, unidadMedidas = [], complementarias = [] }) {
  const [formData, setFormData] = useState({
    nombre: '',
    definicion: '',
    metodo_calculo: '',
    unidad_medida_id: '',
    dimension_id: '',
    dimension_id: '',
    frecuencia_id: '',
    selectedMetaId: ''
  });
  
  const [dimensiones, setDimensiones] = useState([]);
  const [frecuencias, setFrecuencias] = useState([]);
  const [validationErrors, setValidationErrors] = useState([]);
  const [errorModalOpen, setErrorModalOpen] = useState(false);

  useEffect(() => {
    if (open) {
      setValidationErrors([]);
      setErrorModalOpen(false);
      if (indicador) {
        setFormData({
          nombre: indicador.nombre || '',
          definicion: indicador.definicion || '',
          metodo_calculo: indicador.metodo_calculo || '',
          unidad_medida_id: indicador.unidad_medida_id || '',
          dimension_id: indicador.dimension_id || '',
          frecuencia_id: indicador.frecuencia_id || '',
          selectedMetaId: metaComplementaria ? metaComplementaria.id : (indicador.id_metac || indicador.meta_id || '')
        });
      } else {
        setFormData({
          nombre: '',
          definicion: '',
          metodo_calculo: '',
          unidad_medida_id: '',
          dimension_id: '',
          frecuencia_id: '',
          selectedMetaId: metaComplementaria ? metaComplementaria.id : ''
        });
      }
      
      // Load catalogs
      axios.get('/dimensiones').then(res => setDimensiones(Array.isArray(res.data) ? res.data : (res.data?.data || []))).catch(() => setDimensiones([]));
      axios.get('/frecuencias').then(res => setFrecuencias(Array.isArray(res.data) ? res.data : (res.data?.data || []))).catch(() => setFrecuencias([]));
    }
  }, [open, indicador, metaComplementaria, complementarias]);

  useEffect(() => {
    if (open) {
      const targetMetaId = metaComplementaria ? metaComplementaria.id : formData.selectedMetaId;
      const targetMeta = metaComplementaria || complementarias.find(c => String(c.id) === String(targetMetaId));
      if (targetMeta) {
        setFormData(prev => {
          const newData = { ...prev };
          if (!indicador || prev.selectedMetaId !== (indicador.id_metac || indicador.meta_id)) {
            newData.metodo_calculo = targetMeta.tmc !== 1 
              ? 'Resultado = (Atendido/Recibido)*100' 
              : 'Resultado = (Atendido/Programado)*100';
          }
          newData.unidad_medida_id = targetMeta.unidad_medida_id || '';
          return newData;
        });
      }
    }
  }, [formData.selectedMetaId, metaComplementaria, complementarias, open, indicador]);

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSave = () => {
    const errors = [];
    if (!formData.selectedMetaId) errors.push('Alineación');
    if (!(formData.nombre || '').trim()) errors.push('Nombre del Indicador');
    if (!(formData.definicion || '').trim()) errors.push('Objetivo del Indicador');
    if (!(formData.metodo_calculo || '').trim()) errors.push('Método de cálculo');
    if (!formData.unidad_medida_id) errors.push('Unidad de medida');
    if (!formData.dimension_id) errors.push('Dimensión a medir');
    if (!formData.frecuencia_id) errors.push('Frecuencia de medición');

    if (errors.length > 0) {
      setValidationErrors(errors);
      setErrorModalOpen(true);
      return;
    }
    
    setValidationErrors([]);

    const targetMetaId = metaComplementaria ? metaComplementaria.id : formData.selectedMetaId;
    const targetMeta = metaComplementaria || complementarias.find(c => String(c.id) === String(targetMetaId));
    const targetMetaPrincipalId = metaPrincipal ? metaPrincipal.id : (targetMeta?.meta_padre_id || targetMeta?.parent_id || targetMeta?.meta_id);

    onSave({
      ...indicador,
      ...formData,
      meta_id: targetMetaId,
      proyecto_id: proyectoId,
      id_metap: targetMetaPrincipalId,
      id_metac: targetMetaId
    });
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth PaperProps={{ sx: { maxWidth: 860, borderRadius: 1 } }}>
      <DialogTitle sx={{ p: 1.5, fontSize: '1rem', display: 'flex', alignItems: 'center', gap: 1, background: 'linear-gradient(135deg, #0a1e42 0%, #0d2f66 50%, #1e52a8 100%)', color: '#fff' }}>
        <Iconify icon={indicador ? 'mdi:pencil-circle' : 'mdi:chart-box-outline'} width={20} />
        {indicador ? 'Editar Indicador...' : 'Nuevo Indicador...'}
      </DialogTitle>
      <DialogContent sx={{ p: 3 }}>
        <Box display="flex" flexDirection="column" gap={3} mt={2}>
          <Box>
            <Typography variant="body2" color="textSecondary" gutterBottom>Alineación</Typography>
            <TextField
              required
              select
              name="selectedMetaId"
              value={formData.selectedMetaId}
              onChange={handleChange}
              fullWidth
              size="small"
              disabled={!!metaComplementaria || !!indicador}
              SelectProps={{
                sx: { 
                  '& .MuiSelect-select': { 
                    whiteSpace: 'normal',
                    paddingTop: '8px',
                    paddingBottom: '8px',
                    minHeight: 'auto'
                  }
                },
                renderValue: (selected) => {
                  const meta = complementarias.find(c => String(c.id) === String(selected));
                  if (!meta) return '';
                  const idx = complementarias.findIndex(c => String(c.id) === String(selected));
                  return (
                    <Typography 
                      sx={{ 
                        fontSize: '0.875rem', 
                        display: '-webkit-box', 
                        WebkitLineClamp: 3, 
                        WebkitBoxOrient: 'vertical', 
                        overflow: 'hidden',
                        lineHeight: 1.25
                      }}
                    >
                      Meta {meta.orden || idx + 1} - {meta.nombre}
                    </Typography>
                  );
                }
              }}
            >
              {complementarias
                .filter(c => {
                  if (indicador && (String(c.id) === String(formData.selectedMetaId) || String(c.id) === String(indicador.meta_id || indicador.id_metac))) return true;
                  return !c.indicadores || c.indicadores.length === 0;
                })
                .map((c) => {
                  const originalIdx = complementarias.findIndex(orig => String(orig.id) === String(c.id));
                  return (
                    <MenuItem key={c.id} value={c.id} sx={{ py: 1, borderBottom: '1px solid #eee' }}>
                      <Typography 
                        sx={{ 
                          fontSize: '0.8rem', 
                          whiteSpace: 'normal', 
                          display: '-webkit-box', 
                          WebkitLineClamp: 3, 
                          WebkitBoxOrient: 'vertical', 
                          overflow: 'hidden',
                          lineHeight: 1.25
                        }}
                      >
                        Meta {c.orden || originalIdx + 1} - {c.nombre}
                      </Typography>
                    </MenuItem>
                  );
              })}
            </TextField>
          </Box>
          <TextField
            required
            label="Nombre del Indicador"
            name="nombre"
            value={formData.nombre}
            onChange={handleChange}
            fullWidth
            size="small"
          />
          <TextField
            required
            label="Objetivo del Indicador"
            name="definicion"
            value={formData.definicion}
            onChange={handleChange}
            fullWidth
            multiline
            rows={2}
            size="small"
          />
          <Box display="grid" gridTemplateColumns="1fr 1fr" gap={2}>
            <TextField
              required
              select
              label="Unidad de medida"
              name="unidad_medida_id"
              value={formData.unidad_medida_id}
              onChange={handleChange}
              fullWidth
              size="small"
              disabled
            >
              {unidadMedidas.map(u => (
                <MenuItem key={u.unidad_medida_id || u.id} value={u.unidad_medida_id || u.id}>
                  {u.nombre}
                </MenuItem>
              ))}
            </TextField>
            <TextField
              required
              label="Método de cálculo"
              name="metodo_calculo"
              value={formData.metodo_calculo}
              fullWidth
              size="small"
              disabled
            />
          </Box>
          <Box display="grid" gridTemplateColumns="1fr 1fr" gap={2}>
            <TextField
              required
              select
              label="Dimensión a medir"
              name="dimension_id"
              value={formData.dimension_id}
              onChange={handleChange}
              fullWidth
              size="small"
            >
              {dimensiones.map(d => (
                <MenuItem key={d.dimension_id} value={d.dimension_id}>
                  {d.nombre}
                </MenuItem>
              ))}
            </TextField>
            <TextField
              required
              select
              label="Frecuencia de medición"
              name="frecuencia_id"
              value={formData.frecuencia_id}
              onChange={handleChange}
              fullWidth
              size="small"
            >
              {frecuencias.map(f => (
                <MenuItem key={f.frecuencia_id} value={f.frecuencia_id}>
                  {f.nombre}
                </MenuItem>
              ))}
            </TextField>
          </Box>
        </Box>
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose} color="inherit">Cancelar</Button>
        <Button onClick={handleSave} variant="contained" color="primary">Guardar</Button>
      </DialogActions>

      <ValidationModal open={errorModalOpen} onClose={() => setErrorModalOpen(false)} errors={validationErrors} />
    </Dialog>
  );
}
