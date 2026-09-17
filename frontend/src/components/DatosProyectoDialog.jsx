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
} from '@mui/material';
import Iconify from './Iconify';

export default function DatosProyectoDialog({
  open,
  onClose,
  onSave,
  initialData,
}) {
  const [form, setForm] = useState({
    proyecto_nombre: '',
    descripcion: '',
    justificacion: '',
  });
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (open && initialData) {
      setForm({
        proyecto_nombre: initialData.proyecto_nombre || '',
        descripcion: initialData.descripcion || '',
        justificacion: initialData.justificacion || '',
      });
    }
  }, [open, initialData]);

  const handleChange = (field, value) => {
    setForm((prev) => ({ ...prev, [field]: value }));
  };

  const handleSubmit = async () => {
    setSaving(true);
    try {
      await onSave({
        proyecto_nombre: form.proyecto_nombre.trim(),
        descripcion: form.descripcion.trim(),
        justificacion: form.justificacion.trim(),
      });
      onClose();
    } finally {
      setSaving(false);
    }
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth PaperProps={{ sx: { borderRadius: 1, overflow: 'hidden', width: '90%', maxWidth: 864 } }}>
      <DialogTitle sx={{ p: 1.5, fontSize: '1rem', display: 'flex', alignItems: 'center', gap: 1, background: 'linear-gradient(135deg, #0a1e42 0%, #0d2f66 50%, #1e52a8 100%)', color: '#fff' }}>
        <Iconify icon="mdi:pencil-circle" width={20} />
        Editar datos del proyecto y alineación estratégica...
      </DialogTitle>
      <DialogContent sx={{ p: 3 }}>
        <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3, mt: 3 }}>
          <TextField
            label="Proyecto"
            value={form.proyecto_nombre}
            onChange={(e) => handleChange('proyecto_nombre', e.target.value)}
            fullWidth
            size="small"
            InputProps={{ sx: { fontSize: '0.8rem' } }}
            InputLabelProps={{ sx: { fontSize: '0.8rem' } }}
            helperText="(Se toma automáticamente del catálogo PY conforme a la clave programática seleccionada.)"
            FormHelperTextProps={{ sx: { fontSize: '0.7rem' } }}
          />
          <TextField
            label="Descripción del proyecto"
            value={form.descripcion}
            onChange={(e) => handleChange('descripcion', e.target.value)}
            fullWidth
            multiline
            rows={8}
            size="small"
            InputProps={{ sx: { fontSize: '0.8rem' } }}
            InputLabelProps={{ sx: { fontSize: '0.8rem' } }}
            helperText="(Explicación detallada del proyecto.)"
            FormHelperTextProps={{ sx: { fontSize: '0.7rem' } }}
          />
          <TextField
            label="Objetivo del proyecto"
            value={form.justificacion}
            onChange={(e) => handleChange('justificacion', e.target.value)}
            fullWidth
            multiline
            rows={8}
            size="small"
            InputProps={{ sx: { fontSize: '0.8rem' } }}
            InputLabelProps={{ sx: { fontSize: '0.8rem' } }}
            helperText="(Motivo que justifica su ejecución, especificando los beneficios o resultados a obtener.)"
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
    </Dialog>
  );
}
