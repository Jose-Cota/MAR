import { useState } from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  Button,
  Box,
  Typography,
} from '@mui/material';
import axios from '../../../utils/axios';

export default function ChangePasswordModal({ open, onClose }) {
  const [formData, setFormData] = useState({
    password: '',
    password_confirmation: '',
  });

  const [loading, setLoading] = useState(false);

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSave = async () => {
    if (formData.password !== formData.password_confirmation) {
      alert('Las contraseñas no coinciden');
      return;
    }

    try {
      setLoading(true);
      await axios.put('/user/password', formData);
      alert('Contraseña actualizada correctamente');
      setFormData({ password: '', password_confirmation: '' });
      onClose();
    } catch (error) {
      console.error('Error changing password:', error);
      alert('Error al cambiar contraseña: ' + (error.response?.data?.message || ''));
    } finally {
      setLoading(false);
    }
  };

  const handleClose = () => {
    setFormData({ password: '', password_confirmation: '' });
    onClose();
  };

  return (
    <Dialog open={open} onClose={handleClose} maxWidth="sm" fullWidth>
      <DialogTitle sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <Typography variant="h6">Cambio de contraseña</Typography>
      </DialogTitle>
      <DialogContent>
        <Box display="grid" gridTemplateColumns={{ xs: '1fr', sm: '1fr 1fr' }} gap={2} mt={2}>
          <Box>
            <TextField
              label="Contraseña"
              name="password"
              type="password"
              value={formData.password}
              onChange={handleChange}
              fullWidth
              required
              helperText="*Campo obligatorio"
              FormHelperTextProps={{ sx: { margin: 0, marginTop: 0.5, fontSize: '0.75rem' } }}
            />
          </Box>
          <Box>
            <TextField
              label="Confirmar Contraseña"
              name="password_confirmation"
              type="password"
              value={formData.password_confirmation}
              onChange={handleChange}
              fullWidth
              required
              helperText="*Campo obligatorio"
              FormHelperTextProps={{ sx: { margin: 0, marginTop: 0.5, fontSize: '0.75rem' } }}
            />
          </Box>
        </Box>
      </DialogContent>
      <DialogActions sx={{ px: 3, pb: 2 }}>
        <Button
          variant="contained"
          sx={{ bgcolor: '#6c757d', color: 'white', '&:hover': { bgcolor: '#5a6268' } }}
          onClick={handleClose}
        >
          Close
        </Button>
        <Button
          variant="contained"
          sx={{ bgcolor: '#8e5ea2', color: 'white', '&:hover': { bgcolor: '#774c8b' } }}
          onClick={handleSave}
          disabled={loading || !formData.password || !formData.password_confirmation}
        >
          Cambiar contraseña
        </Button>
      </DialogActions>
    </Dialog>
  );
}
