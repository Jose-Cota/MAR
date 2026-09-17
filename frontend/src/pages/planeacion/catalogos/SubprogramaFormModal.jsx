import { useState, useEffect } from 'react';
import { Dialog, DialogTitle, DialogContent, DialogActions, Button, TextField, Stack, MenuItem } from '@mui/material';
import axios from '../../../utils/axios';
import useGlobalStore from '../../../stores/useGlobalStore';
import ValidationModal from '../../../components/ui/ValidationModal';

export default function SubprogramaFormModal({ open, onClose, subprograma, onSuccess }) {
  const ejercicio = useGlobalStore((state) => state.ejercicio);
  const isEdit = Boolean(subprograma);
  const [formData, setFormData] = useState({ programa_id: '', numero: '', nombre: '' });
  const [programas, setProgramas] = useState([]);
  const [loading, setLoading] = useState(false);
  const [validationErrors, setValidationErrors] = useState([]);
  const [errorModalOpen, setErrorModalOpen] = useState(false);

  useEffect(() => {
    if (open) {
      fetchProgramas();
      if (isEdit) {
        setFormData({ programa_id: subprograma.programa_id, numero: subprograma.numero, nombre: subprograma.nombre });
      } else {
        setFormData({ programa_id: '', numero: '', nombre: '' });
      }
      setValidationErrors([]);
    }
  }, [open, subprograma]);

  const fetchProgramas = async () => {
    try {
      const res = await axios.get(`/programas?ejercicio=${ejercicio}`);
      setProgramas(res.data);
    } catch (err) {
      console.error(err);
    }
  };

  const handleSubmit = async () => {
    const errors = [];
    if (!formData.programa_id) errors.push('Programa');
    if (!formData.numero) errors.push('Número');
    if (!formData.nombre) errors.push('Nombre del Subprograma');

    if (errors.length > 0) {
      setValidationErrors(errors);
      setErrorModalOpen(true);
      return;
    }

    setLoading(true);
    try {
      if (isEdit) {
        await axios.put(`/subprogramas/${subprograma.subprograma_id}`, formData);
      } else {
        await axios.post('/subprogramas', formData);
      }
      onSuccess();
    } catch (err) {
      setValidationErrors([err.response?.data?.message || 'Error al guardar el subprograma']);
      setErrorModalOpen(true);
    } finally {
      setLoading(false);
    }
  };

  return (
    <Dialog open={open} onClose={onClose} fullWidth maxWidth="sm">
      <DialogTitle>{isEdit ? 'Editar Subprograma' : 'Nuevo Subprograma'}</DialogTitle>
      <DialogContent dividers>
        <Stack spacing={3} sx={{ mt: 1 }}>
          <TextField
            required
            select
            label="Programa"
            value={formData.programa_id}
            onChange={(e) => setFormData({ ...formData, programa_id: e.target.value })}
            fullWidth
          >
            {programas.map((pg) => (
              <MenuItem key={pg.programa_id} value={pg.programa_id}>
                {pg.numero} - {pg.nombre}
              </MenuItem>
            ))}
          </TextField>
          <TextField
            required
            label="Número"
            value={formData.numero}
            onChange={(e) => setFormData({ ...formData, numero: e.target.value })}
            fullWidth
          />
          <TextField
            required
            label="Nombre del Subprograma"
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
