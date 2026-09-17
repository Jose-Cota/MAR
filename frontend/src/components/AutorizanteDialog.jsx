import { useEffect, useState } from 'react';
import {
  Box,
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  TextField,
  Stack,
} from '@mui/material';
import Iconify from './Iconify';
import ValidationModal from './ui/ValidationModal';

export default function AutorizanteDialog({
  open,
  onClose,
  onSave,
  initialData,
  empleadosUrOptions,
  puestosOptions,
}) {
  const [form, setForm] = useState({
    autorizante_nombre: '',
    autorizante_puesto: '',
  });
  const [saving, setSaving] = useState(false);
  const [validationErrors, setValidationErrors] = useState([]);
  const [errorModalOpen, setErrorModalOpen] = useState(false);

  useEffect(() => {
    if (open && initialData) {
      setForm({
        autorizante_nombre: initialData.autorizante_nombre || '',
        autorizante_puesto: initialData.autorizante_puesto || '',
      });
    }
  }, [open, initialData]);

  const handleChange = (field, value) => {
    setForm((prev) => {
      const newForm = { ...prev, [field]: value };
      return newForm;
    });
  };

  const handleSubmit = async () => {
    const errors = [];
    if (!form.autorizante_nombre || !form.autorizante_nombre.trim()) errors.push('Autoriza (Nombre del autorizante)');
    if (!form.autorizante_puesto || !form.autorizante_puesto.trim()) errors.push('Puesto del autorizante');

    if (errors.length > 0) {
      setValidationErrors(errors);
      setErrorModalOpen(true);
      return;
    }

    setSaving(true);
    try {
      await onSave({
        autorizante_nombre: form.autorizante_nombre.trim(),
        autorizante_puesto: form.autorizante_puesto.trim(),
      });
      onClose();
    } finally {
      setSaving(false);
    }
  };

  const uniquePuestos = puestosOptions || [];

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth PaperProps={{ sx: { borderRadius: 1, overflow: 'hidden' } }}>
      <DialogTitle sx={{ p: 1.5, fontSize: '1rem', display: 'flex', alignItems: 'center', gap: 1, background: 'linear-gradient(135deg, #0a1e42 0%, #0d2f66 50%, #1e52a8 100%)', color: '#fff' }}>
        <Iconify icon="mdi:pencil-circle" width={20} />
        Autorizante
      </DialogTitle>
      <DialogContent sx={{ p: 3 }}>
        <Stack direction="column" spacing={3} sx={{ mt: 2 }}>
          <TextField
            required
            label="Autoriza"
            value={form.autorizante_nombre || ''}
            onChange={(e) => handleChange('autorizante_nombre', e.target.value)}
            fullWidth
            size="small"
            multiline
            rows={2}
            inputProps={{ style: { resize: 'vertical' } }}
            InputProps={{ sx: { fontSize: '0.875rem' } }}
            InputLabelProps={{ sx: { fontSize: '0.875rem', '& .MuiInputLabel-asterisk': { color: 'red' } } }}
          />

          <TextField
            required
            label="Puesto"
            value={form.autorizante_puesto || ''}
            onChange={(e) => handleChange('autorizante_puesto', e.target.value)}
            fullWidth
            size="small"
            multiline
            rows={2}
            inputProps={{ style: { resize: 'vertical' } }}
            InputProps={{ sx: { fontSize: '0.875rem' } }}
            InputLabelProps={{ sx: { fontSize: '0.875rem', '& .MuiInputLabel-asterisk': { color: 'red' } } }}
          />
        </Stack>
      </DialogContent>
      <DialogActions sx={{ px: 1.5, pb: 1.5, pt: 0 }}>
        <Button onClick={onClose} color="inherit" size="small" startIcon={<Iconify icon="mdi:close" />}>
          Cancelar
        </Button>
        <Button onClick={handleSubmit} color="primary" variant="contained" size="small" disabled={saving} startIcon={<Iconify icon="mdi:content-save" />}>
          {saving ? 'Guardando...' : 'Guardar'}
        </Button>
      </DialogActions>
      <ValidationModal open={errorModalOpen} onClose={() => setErrorModalOpen(false)} errors={validationErrors} />
    </Dialog>
  );
}
