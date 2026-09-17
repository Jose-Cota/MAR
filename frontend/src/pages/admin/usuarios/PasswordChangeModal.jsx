import { useState } from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  TextField,
  Alert
} from '@mui/material';
import { usuariosService } from '../../../services/usuariosService';

export default function PasswordChangeModal({ open, onClose, usuario, onSuccess }) {
  const [formData, setFormData] = useState({
    password: '',
    password_confirmation: '',
  });

  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async () => {
    setError('');
    setSuccess('');
    
    if (formData.password !== formData.password_confirmation) {
      setError('Las contraseñas no coinciden.');
      return;
    }
    
    if (formData.password.length < 6) {
      setError('La contraseña debe tener al menos 6 caracteres.');
      return;
    }

    try {
      await usuariosService.updatePassword(usuario.usuario_poa_id, formData);
      setSuccess('Contraseña actualizada con éxito.');
      setTimeout(() => {
        if (onSuccess) onSuccess();
      }, 1500);
    } catch (err) {
      console.error('Error changing password:', err);
      setError(err.response?.data?.message || 'Error al cambiar la contraseña.');
    }
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
      <DialogTitle>Cambiar Contraseña - {usuario?.usuario}</DialogTitle>
      <DialogContent dividers>
        {error && <Alert severity="error" sx={{ mb: 3 }}>{error}</Alert>}
        {success && <Alert severity="success" sx={{ mb: 3 }}>{success}</Alert>}
        
        <TextField
          fullWidth
          type="password"
          label="Nueva Contraseña"
          name="password"
          value={formData.password}
          onChange={handleChange}
          required
          sx={{ mb: 2, mt: 1 }}
        />
        <TextField
          fullWidth
          type="password"
          label="Confirmar Contraseña"
          name="password_confirmation"
          value={formData.password_confirmation}
          onChange={handleChange}
          required
        />
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose} color="inherit">
          Cerrar
        </Button>
        <Button onClick={handleSubmit} variant="contained" color="warning">
          Actualizar Contraseña
        </Button>
      </DialogActions>
    </Dialog>
  );
}
