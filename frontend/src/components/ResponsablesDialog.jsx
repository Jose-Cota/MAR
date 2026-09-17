import { useEffect, useState } from 'react';
import {
  Box,
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  FormControl,
  InputLabel,
  MenuItem,
  Select,
  TextField,
  Stack,
  Autocomplete,
} from '@mui/material';
import Iconify from './Iconify';
import ValidationModal from './ui/ValidationModal';

export default function ResponsablesDialog({
  open,
  onClose,
  onSave,
  initialData,
  responsableFichaOptions,
  puestosOptions,
}) {
  const [form, setForm] = useState({
    urg: '',
    ro: '',
    responsable_ficha: '',
    puesto_responsable_ficha: '',
    objetivo: '',
  });
  const [saving, setSaving] = useState(false);
  const [validationErrors, setValidationErrors] = useState([]);
  const [errorModalOpen, setErrorModalOpen] = useState(false);

  useEffect(() => {
    if (open && initialData) {
      setForm({
        urg: initialData.urg || '',
        ro: initialData.ro || '',
        responsable_ficha: initialData.responsable_ficha || '',
        puesto_responsable_ficha: initialData.puesto_responsable_ficha || '',
        objetivo: initialData.objetivo || '',
      });
    }
  }, [open, initialData]);

  const handleChange = (field, value) => {
    setForm((prev) => ({ ...prev, [field]: value }));
  };

  const handleSubmit = async () => {
    const errors = [];
    if (!form.responsable_ficha.trim()) errors.push('Responsable de la ficha');
    if (!form.puesto_responsable_ficha.trim()) errors.push('Puesto');
    if (!form.objetivo.trim()) errors.push('Objetivo de la UR');

    if (errors.length > 0) {
      setValidationErrors(errors);
      setErrorModalOpen(true);
      return;
    }

    setSaving(true);
    try {
      await onSave({
        responsable_ficha: form.responsable_ficha.trim(),
        puesto_responsable_ficha: form.puesto_responsable_ficha.trim(),
        objetivo: form.objetivo.trim(),
      });
      onClose();
    } finally {
      setSaving(false);
    }
  };

  const uniquePuestos = puestosOptions || [];

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth PaperProps={{ sx: { borderRadius: 1, overflow: 'hidden' } }}>
      <DialogTitle sx={{ p: 1.5, fontSize: '1rem', display: 'flex', alignItems: 'center', gap: 1, background: 'linear-gradient(135deg, #0a1e42 0%, #0d2f66 50%, #1e52a8 100%)', color: '#fff' }}>
        <Iconify icon="mdi:pencil-circle" width={20} />
        Identificación de responsables
      </DialogTitle>
      <DialogContent sx={{ p: 2, pt: 4 }}>
        <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3, mt: 2 }}>
          <TextField
            label="Unidad Responsable (UR)"
            value={form.urg}
            disabled
            fullWidth
            size="small"
            sx={{ backgroundColor: '#f5f5f5' }}
            InputProps={{ sx: { fontSize: '0.8rem', color: '#666' } }}
            InputLabelProps={{ sx: { fontSize: '0.8rem' } }}
          />
          <TextField
            label="Responsable operativo"
            value={form.ro}
            disabled
            fullWidth
            size="small"
            sx={{ backgroundColor: '#f5f5f5' }}
            InputProps={{ sx: { fontSize: '0.8rem', color: '#666' } }}
            InputLabelProps={{ sx: { fontSize: '0.8rem' } }}
          />
          
          <Box>
            <Stack direction="row" spacing={2} sx={{ alignItems: 'flex-start' }}>
              <TextField
                required
                label="Responsable de la ficha"
                value={form.responsable_ficha}
                onChange={(e) => handleChange('responsable_ficha', e.target.value)}
                fullWidth
                size="small"
                multiline
                rows={2}
                inputProps={{ style: { resize: 'vertical' } }}
                sx={{ flex: 5 }}
                InputProps={{ sx: { fontSize: '0.8rem' } }}
                InputLabelProps={{ sx: { fontSize: '0.8rem', '& .MuiInputLabel-asterisk': { color: 'red' } } }}
              />

              <TextField
                required
                label="Puesto"
                value={form.puesto_responsable_ficha}
                onChange={(e) => handleChange('puesto_responsable_ficha', e.target.value)}
                fullWidth
                size="small"
                multiline
                rows={2}
                inputProps={{ style: { resize: 'vertical' } }}
                sx={{ flex: 5 }}
                InputProps={{ sx: { fontSize: '0.8rem' } }}
                InputLabelProps={{ sx: { fontSize: '0.8rem', '& .MuiInputLabel-asterisk': { color: 'red' } } }}
              />
            </Stack>
          </Box>

          <TextField
            required
            label="Objetivo de la UR"
            value={form.objetivo}
            onChange={(e) => handleChange('objetivo', e.target.value)}
            fullWidth
            multiline
            rows={3}
            size="small"
            InputProps={{ sx: { fontSize: '0.8rem' } }}
            InputLabelProps={{ sx: { fontSize: '0.8rem', '& .MuiInputLabel-asterisk': { color: 'red' } } }}
            helperText="(Finalidad general que persigue la UR de acuerdo con sus atribuciones legales y/o reglamentarias.)"
            FormHelperTextProps={{ sx: { fontSize: '0.7rem' } }}
          />
        </Box>
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
