import { useState, useEffect } from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  TextField,
  CircularProgress,
  IconButton,
  Typography,
  Alert,
  Collapse
} from '@mui/material';
import Iconify from './Iconify';
import axios from '../utils/axios';

export default function ProyectoBitacoraModal({ open, onClose, proyectoId, action, warnings = [], onSuccess }) {
  const [mensaje, setMensaje] = useState(() => {
    if (warnings.length > 0) {
      return `Se envió al validador con las siguientes observaciones automáticas:\n- ${warnings.join('\n- ')}\n\n`;
    }
    return '';
  });

  // Si abren el modal de nuevo, actualizar el mensaje por defecto
  useEffect(() => {
    if (open) {
      if (warnings.length > 0) {
        setMensaje(`Se envió al validador con las siguientes observaciones automáticas:\n- ${warnings.join('\n- ')}\n\n`);
      } else {
        setMensaje('');
      }
      setError('');
    }
  }, [open, warnings]);

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [successModalOpen, setSuccessModalOpen] = useState(false);

  const actionLabels = {
    enviar_validador: 'Enviar al Validador',
    regresar_capturador: 'Regresar a Capturador',
    enviar_dpyrf: 'Cerrar y enviar a DPyRF'
  };

  const isEnviar = action === 'enviar_validador' || action === 'enviar_dpyrf';

  const handleSave = async () => {
    if (!mensaje.trim()) {
      setError('Por favor ingresa un mensaje o nota antes de continuar.');
      return;
    }
    
    setLoading(true);
    setError('');
    
    try {
      await axios.post(`/proyectos/${proyectoId}/bitacora`, {
        accion: action,
        mensaje: mensaje
      });
      
      setMensaje('');
      setSuccessModalOpen(true);
    } catch (err) {
      console.error('Error guardando nota de bitácora:', err);
      setError(err.response?.data?.message || 'Error al guardar la nota de bitácora.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Dialog open={open} onClose={loading ? undefined : onClose} maxWidth="sm" fullWidth>
      <DialogTitle sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', pb: 1 }}>
        <Typography variant="h6" component="div" sx={{ fontWeight: 'bold', display: 'flex', alignItems: 'center' }}>
          <Iconify icon="mdi:file-send-outline" sx={{ mr: 1, color: '#1F4E79' }} /> Enviar ficha descriptiva...
        </Typography>
        <IconButton onClick={onClose} disabled={loading} size="small">
          <Iconify icon="mdi:close" />
        </IconButton>
      </DialogTitle>
      <DialogContent dividers>
        <Collapse in={!!error} sx={{ mb: 2 }}>
          <Alert severity="error" onClose={() => setError('')} sx={{ borderRadius: 1, '& .MuiAlert-message': { width: '100%' } }}>
            {error}
          </Alert>
        </Collapse>
        
        {warnings.length > 0 && (
          <Alert severity="warning" sx={{ mb: 2, borderRadius: 1 }}>
            <strong>Existen observaciones:</strong>
            <ul style={{ margin: '4px 0 0', paddingLeft: '20px' }}>
              {warnings.map((w, i) => <li key={i}>{w}</li>)}
            </ul>
          </Alert>
        )}

        <Typography variant="body2" sx={{ mb: 2, color: 'text.secondary' }}>
          Estás a punto de <strong>{actionLabels[action]}</strong>. Por favor, deja un comentario o nota para mantener el registro en la bitácora del proyecto.
        </Typography>
        
        <TextField
          autoFocus
          margin="dense"
          label="Mensaje o nota"
          type="text"
          fullWidth
          multiline
          rows={6}
          variant="outlined"
          value={mensaje}
          onChange={(e) => setMensaje(e.target.value)}
          disabled={loading}
        />
      </DialogContent>
      <DialogActions sx={{ px: 3, py: 2 }}>
        <Button onClick={onClose} disabled={loading} color="inherit">
          Cancelar
        </Button>
        <Button 
          onClick={handleSave} 
          variant="contained" 
          disabled={loading}
          sx={{ 
            bgcolor: isEnviar ? '#1F4E79' : '#B3372E',
            '&:hover': {
              bgcolor: isEnviar ? '#143352' : '#8c2923'
            }
          }}
          startIcon={loading && <CircularProgress size={16} color="inherit" />}
        >
          Enviar
        </Button>
      </DialogActions>

      {/* Success Message Modal */}
      <Dialog open={successModalOpen} maxWidth="xs" fullWidth PaperProps={{ sx: { borderRadius: 1 } }}>
        <DialogTitle sx={{ p: 1.5, fontSize: '1rem', display: 'flex', alignItems: 'center', gap: 1, background: 'linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%)', color: '#fff' }}>
          <Iconify icon="mdi:check-circle-outline" width={20} />
          Envío exitoso
        </DialogTitle>
        <DialogContent sx={{ p: 3, textAlign: 'center' }}>
          <Iconify icon="mdi:check-decagram" width={60} color="#2e7d32" sx={{ mb: 2, mt: 1 }} />
          <Typography variant="h6" sx={{ mb: 1, fontWeight: 'bold' }}>
            ¡Operación realizada!
          </Typography>
          <Typography variant="body2" color="text.secondary">
            Se ha completado el envío y se registró la nota en la bitácora correctamente. 
            Ahora serás redirigido al módulo de proyectos.
          </Typography>
        </DialogContent>
        <DialogActions sx={{ justifyContent: 'center', pb: 3 }}>
          <Button onClick={() => { setSuccessModalOpen(false); onSuccess(); }} variant="contained" color="success" size="large" sx={{ px: 4 }}>
            Continuar
          </Button>
        </DialogActions>
      </Dialog>
    </Dialog>
  );
}
