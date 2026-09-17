import { useState, useEffect } from 'react';
import { Dialog, DialogTitle, DialogContent, DialogActions, Button, TextField, Stack, FormControlLabel, Checkbox, Box } from '@mui/material';
import axios from '../../../utils/axios';
import useGlobalStore from '../../../stores/useGlobalStore';
import ValidationModal from '../../../components/ui/ValidationModal';
import Iconify from '../../../components/Iconify';

export default function UnidadMedidaFormModal({ open, onClose, unidad, onSuccess }) {
  const ejercicio = useGlobalStore((state) => state.ejercicio);
  const isEdit = Boolean(unidad);
  const [formData, setFormData] = useState({ numero: '', nombre: '', descripcion: '', porcentajes: false });
  const [loading, setLoading] = useState(false);
  const [validationErrors, setValidationErrors] = useState([]);
  const [errorModalOpen, setErrorModalOpen] = useState(false);

  useEffect(() => {
    if (open) {
      if (isEdit) {
        setFormData({ 
          numero: unidad.numero, 
          nombre: unidad.nombre,
          descripcion: unidad.descripcion || '',
          porcentajes: Boolean(unidad.porcentajes)
        });
      } else {
        setFormData({ numero: '', nombre: '', descripcion: '', porcentajes: false });
      }
      setValidationErrors([]);
    }
  }, [open, unidad]);

  const handleSubmit = async () => {
    const errors = [];
    if (isEdit && !formData.numero) errors.push('Número / Clave');
    if (!formData.nombre) errors.push('Nombre');

    if (errors.length > 0) {
      setValidationErrors(errors);
      setErrorModalOpen(true);
      return;
    }

    setLoading(true);
    try {
      if (isEdit) {
        await axios.put(`/unidades-medida/${unidad.unidad_medida_id}`, formData);
      } else {
        await axios.post('/unidades-medida', { ...formData, ejercicio });
      }
      onSuccess();
    } catch (err) {
      setValidationErrors([err.response?.data?.message || 'Error al guardar la unidad de medida']);
      setErrorModalOpen(true);
    } finally {
      setLoading(false);
    }
  };

  return (
    <Dialog open={open} onClose={onClose} fullWidth maxWidth="sm">
      <DialogTitle sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
        <Iconify icon="mdi:ruler" width={24} height={24} />
        {isEdit ? 'Editar Unidad de Medida' : 'Nueva Unidad de Medida'}
      </DialogTitle>
      <DialogContent dividers>
        <Stack spacing={3} sx={{ mt: 1 }}>
          {isEdit && (
            <TextField
              required
              label="Número / Clave"
              value={formData.numero}
              onChange={(e) => setFormData({ ...formData, numero: e.target.value })}
              fullWidth
            />
          )}
          <TextField
            required
            label="Nombre"
            value={formData.nombre}
            onChange={(e) => setFormData({ ...formData, nombre: e.target.value })}
            fullWidth
          />
          <TextField
            label="Descripción"
            value={formData.descripcion}
            onChange={(e) => setFormData({ ...formData, descripcion: e.target.value })}
            fullWidth
            multiline
            rows={3}
          />
        </Stack>
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose} color="inherit" disabled={loading}>Cancelar</Button>
        <Button onClick={handleSubmit} variant="contained" disabled={loading}>Guardar</Button>
      </DialogActions>
      <ValidationModal open={errorModalOpen} onClose={() => setErrorModalOpen(false)} errors={validationErrors} />
    </Dialog>
  );
}
