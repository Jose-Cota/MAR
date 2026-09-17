import { useState, useEffect } from 'react';
import { Dialog, DialogTitle, DialogContent, DialogActions, Button, TextField, Stack, MenuItem } from '@mui/material';
import axios from '../../../utils/axios';
import useGlobalStore from '../../../stores/useGlobalStore';
import ValidationModal from '../../../components/ui/ValidationModal';

export default function ResponsableOperativoFormModal({ open, onClose, responsable, onSuccess }) {
  const ejercicio = useGlobalStore((state) => state.ejercicio);
  const isEdit = Boolean(responsable);
  const [formData, setFormData] = useState({ unidad_responsable_gasto_id: '', numero: '', nombre: '' });
  const [urgs, setUrgs] = useState([]);
  const [loading, setLoading] = useState(false);
  const [validationErrors, setValidationErrors] = useState([]);
  const [errorModalOpen, setErrorModalOpen] = useState(false);

  useEffect(() => {
    if (open) {
      fetchURGs();
      if (isEdit) {
        setFormData({ 
          unidad_responsable_gasto_id: responsable.unidad_responsable_gasto_id || '', 
          numero: responsable.ronum || responsable.numero || '', 
          nombre: responsable.ronom || responsable.nombre || '' 
        });
      } else {
        setFormData({ unidad_responsable_gasto_id: '', numero: '', nombre: '' });
      }
      setValidationErrors([]);
    }
  }, [open, responsable]);

  const fetchURGs = async () => {
    try {
      const res = await axios.get(`/unidades-responsables?ejercicio=${ejercicio}`);
      setUrgs(res.data);
    } catch (err) {
      console.error(err);
    }
  };

  const handleSubmit = async () => {
    const errors = [];
    if (!formData.unidad_responsable_gasto_id) errors.push('Unidad Responsable (URG)');
    if (!formData.numero) errors.push('Número');
    if (!formData.nombre) errors.push('Nombre');

    if (errors.length > 0) {
      setValidationErrors(errors);
      setErrorModalOpen(true);
      return;
    }

    setLoading(true);
    try {
      if (isEdit) {
        await axios.put(`/responsables-operativos/${responsable.responsable_operativo_id}`, formData);
      } else {
        await axios.post('/responsables-operativos', formData);
      }
      onSuccess();
    } catch (err) {
      setValidationErrors([err.response?.data?.message || 'Error al guardar el Responsable Operativo']);
      setErrorModalOpen(true);
    } finally {
      setLoading(false);
    }
  };

  return (
    <Dialog open={open} onClose={onClose} fullWidth maxWidth="sm">
      <DialogTitle>{isEdit ? 'Editar Responsable Operativo' : 'Nuevo Responsable Operativo'}</DialogTitle>
      <DialogContent dividers>
        <Stack spacing={3} sx={{ mt: 1 }}>
          <TextField
            required
            select
            label="Unidad Responsable (URG)"
            value={formData.unidad_responsable_gasto_id}
            onChange={(e) => setFormData({ ...formData, unidad_responsable_gasto_id: e.target.value })}
            fullWidth
          >
            {urgs.map((urg) => (
              <MenuItem key={urg.unidad_responsable_gasto_id} value={urg.unidad_responsable_gasto_id}>
                {urg.numero} - {urg.nombre}
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
            label="Nombre"
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
