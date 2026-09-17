import { useState, useEffect } from 'react';
import { Dialog, DialogTitle, DialogContent, DialogActions, Button, TextField, Stack } from '@mui/material';
import axios from '../../../utils/axios';
import useGlobalStore from '../../../stores/useGlobalStore';
import ValidationModal from '../../../components/ui/ValidationModal';

export default function ProgramaFormModal({ open, onClose, programa, onSuccess }) {
  const ejercicio = useGlobalStore((state) => state.ejercicio);
  const isEdit = Boolean(programa);
  const [formData, setFormData] = useState({ numero: '', nombre: '' });
  const [loading, setLoading] = useState(false);
  const [validationErrors, setValidationErrors] = useState([]);
  const [errorModalOpen, setErrorModalOpen] = useState(false);

  useEffect(() => {
    if (open) {
      if (isEdit) {
        setFormData({ numero: programa.numero, nombre: programa.nombre });
      } else {
        setFormData({ numero: '', nombre: '' });
      }
      setValidationErrors([]);
    }
  }, [open, programa]);

  const handleSubmit = async () => {
    const errors = [];
    if (!formData.numero) errors.push('Número');
    if (!formData.nombre) errors.push('Nombre del Programa');

    if (errors.length > 0) {
      setValidationErrors(errors);
      setErrorModalOpen(true);
      return;
    }

    setLoading(true);
    try {
      if (isEdit) {
        await axios.put(`/programas/${programa.programa_id}`, formData);
      } else {
        await axios.post('/programas', { ...formData, ejercicio });
      }
      onSuccess();
    } catch (err) {
      setValidationErrors([err.response?.data?.message || 'Error al guardar el programa']);
      setErrorModalOpen(true);
    } finally {
      setLoading(false);
    }
  };

  return (
    <Dialog open={open} onClose={onClose} fullWidth maxWidth="sm">
      <DialogTitle>{isEdit ? 'Editar Programa' : 'Nuevo Programa'}</DialogTitle>
      <DialogContent dividers>
        <Stack spacing={3} sx={{ mt: 1 }}>
          <TextField
            required
            label="Número"
            value={formData.numero}
            onChange={(e) => setFormData({ ...formData, numero: e.target.value })}
            fullWidth
          />
          <TextField
            required
            label="Nombre del Programa"
            value={formData.nombre}
            onChange={(e) => setFormData({ ...formData, nombre: e.target.value })}
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
