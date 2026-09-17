import { useState, useEffect } from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
} from '@mui/material';
import useGlobalStore from '../stores/useGlobalStore';

export default function EjercicioModal() {
  const { isEjercicioModalOpen, closeEjercicioModal, ejercicio, setEjercicio } = useGlobalStore();
  const [selectedEjercicio, setSelectedEjercicio] = useState(ejercicio);

  // Sync state when modal opens
  useEffect(() => {
    if (isEjercicioModalOpen) {
      setSelectedEjercicio(ejercicio);
    }
  }, [isEjercicioModalOpen, ejercicio]);

  const handleClose = () => {
    closeEjercicioModal();
  };

  const handleSave = () => {
    setEjercicio(selectedEjercicio);
    closeEjercicioModal();
  };

  const currentYear = new Date().getFullYear();
  const years = [currentYear + 1, currentYear, currentYear - 1, currentYear - 2, currentYear - 3];

  return (
    <Dialog open={isEjercicioModalOpen} onClose={handleClose} maxWidth="xs" fullWidth>
      <DialogTitle>Seleccionar ejercicio...</DialogTitle>
      <DialogContent>
        <FormControl fullWidth sx={{ mt: 2 }}>
          <InputLabel id="ejercicio-select-label">Ejercicio</InputLabel>
          <Select
            labelId="ejercicio-select-label"
            id="ejercicio-select"
            value={selectedEjercicio}
            label="Ejercicio"
            onChange={(e) => setSelectedEjercicio(e.target.value)}
          >
            {years.map((year) => (
              <MenuItem key={year} value={year}>
                {year}
              </MenuItem>
            ))}
          </Select>
        </FormControl>
      </DialogContent>
      <DialogActions>
        <Button onClick={handleClose} color="inherit">
          Cancelar
        </Button>
        <Button onClick={handleSave} variant="contained">
          Guardar
        </Button>
      </DialogActions>
    </Dialog>
  );
}
