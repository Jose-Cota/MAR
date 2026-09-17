import React, { useState, useEffect } from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  TextField,
  Box,
  Typography,
  Stack
} from '@mui/material';
import ValidationModal from './ui/ValidationModal';

export default function ActividadDescriptivaDialog({ open, onClose, onSave, actividad }) {
  const [descripcion, setDescripcion] = useState('');
  const [recursosAsociados, setRecursosAsociados] = useState('');
  const [validationErrors, setValidationErrors] = useState([]);
  const [errorModalOpen, setErrorModalOpen] = useState(false);

  useEffect(() => {
    if (open) {
      if (actividad) {
        setDescripcion(actividad.descripcion || '');
        setRecursosAsociados(actividad.recursos_asociados || '');
      } else {
        setDescripcion('');
        setRecursosAsociados('');
      }
      setValidationErrors([]);
    }
  }, [open, actividad]);

  const handleSave = () => {
    const errs = [];
    if (!descripcion.trim()) errs.push('Descripción de las Actividades');
    
    if (errs.length > 0) {
      setValidationErrors(errs);
      setErrorModalOpen(true);
      return;
    }

    onSave({
      id: actividad?.id,
      descripcion: descripcion.trim(),
      recursos_asociados: recursosAsociados.trim()
    });
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
      <DialogTitle sx={{ m: 0, p: 2, bgcolor: '#0d2f66', color: '#fff' }}>
        <Typography variant="subtitle1" fontWeight="bold">
          {actividad ? 'Editar Actividad' : 'Nueva Actividad'}
        </Typography>
      </DialogTitle>
      <DialogContent sx={{ p: 2, mt: 1 }}>
        <Stack spacing={2} sx={{ mt: 1 }}>
          <TextField
            label="Descripción de las Actividades"
            multiline
            rows={3}
            fullWidth
            size="small"
            value={descripcion}
            onChange={(e) => setDescripcion(e.target.value)}
            required
          />
          <TextField
            label="Recursos Asociados"
            multiline
            rows={3}
            fullWidth
            size="small"
            value={recursosAsociados}
            onChange={(e) => setRecursosAsociados(e.target.value)}
          />
        </Stack>
      </DialogContent>
      <DialogActions sx={{ p: 2, borderTop: '1px solid #e0e0e0' }}>
        <Button onClick={onClose} color="inherit" size="small">Cancelar</Button>
        <Button onClick={handleSave} variant="contained" color="primary" size="small">
          Guardar
        </Button>
      </DialogActions>
      <ValidationModal open={errorModalOpen} onClose={() => setErrorModalOpen(false)} errors={validationErrors} />
    </Dialog>
  );
}
