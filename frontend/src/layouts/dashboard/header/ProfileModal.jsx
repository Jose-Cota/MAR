import { useState, useEffect } from 'react';
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
import useAuth from '../../../hooks/useAuth';
import axios from '../../../utils/axios';
import ChangePasswordModal from './ChangePasswordModal';

export default function ProfileModal({ open, onClose }) {
  const { user } = useAuth();
  
  const [formData, setFormData] = useState({
    nombre: '',
    apellido: '',
    correo: '',
  });

  const [passwordModalOpen, setPasswordModalOpen] = useState(false);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (user && open) {
      setFormData({
        nombre: user.nombre || '',
        apellido: user.apellido_paterno || '',
        correo: user.email || '',
      });
    }
  }, [user, open]);

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSave = async () => {
    try {
      setLoading(true);
      await axios.put('/user/profile', formData);
      // To properly reflect changes, we would ideally reload the user session
      // or window.location.reload() since AuthContext only fetches on init.
      // But for simplicity in UX, we can just reload the window.
      window.location.reload();
    } catch (error) {
      console.error('Error saving profile:', error);
      alert('Error al guardar el perfil: ' + (error.response?.data?.message || ''));
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
        <DialogTitle sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <Typography variant="h6">Perfil de usuario...</Typography>
        </DialogTitle>
        <DialogContent>
          <Box display="grid" gridTemplateColumns={{ xs: '1fr', sm: '1fr 1fr' }} gap={2} mt={2}>
            <Box>
              <TextField
                label="Nombre"
                name="nombre"
                value={formData.nombre}
                onChange={handleChange}
                fullWidth
                required
                helperText="*Campo obligatorio"
                FormHelperTextProps={{ sx: { margin: 0, marginTop: 0.5, fontSize: '0.75rem' } }}
              />
            </Box>
            <Box>
              <TextField
                label="Apellido"
                name="apellido"
                value={formData.apellido}
                onChange={handleChange}
                fullWidth
                required
                helperText="*Campo obligatorio"
                FormHelperTextProps={{ sx: { margin: 0, marginTop: 0.5, fontSize: '0.75rem' } }}
              />
            </Box>
            <Box gridColumn={{ xs: '1fr', sm: 'span 2' }}>
              <TextField
                label="Correo"
                name="correo"
                value={formData.correo}
                onChange={handleChange}
                fullWidth
                required
                helperText="*Campo obligatorio"
                FormHelperTextProps={{ sx: { margin: 0, marginTop: 0.5, fontSize: '0.75rem' } }}
              />
            </Box>
          </Box>
        </DialogContent>
        <DialogActions sx={{ justifyContent: 'space-between', px: 3, pb: 2 }}>
          <Button
            variant="contained"
            sx={{ bgcolor: '#ffc107', color: 'black', '&:hover': { bgcolor: '#e0a800' } }}
            onClick={() => setPasswordModalOpen(true)}
          >
            Cambiar Contraseña
          </Button>
          <Button
            variant="contained"
            color="success"
            onClick={handleSave}
            disabled={loading}
          >
            Guardar
          </Button>
        </DialogActions>
      </Dialog>

      <ChangePasswordModal open={passwordModalOpen} onClose={() => setPasswordModalOpen(false)} />
    </>
  );
}
