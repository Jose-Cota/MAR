import React, { useState } from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Typography,
  Button,
  Box,
  TextField,
  Alert
} from '@mui/material';
import WarningRoundedIcon from '@mui/icons-material/WarningRounded';

const ConfirmDestructiveDeleteDialog = ({ open, onClose, onConfirm, title, message, warningDetails }) => {
  const [confirmText, setConfirmText] = useState('');

  const handleClose = () => {
    setConfirmText('');
    onClose();
  };

  const handleConfirm = () => {
    if (confirmText === 'ELIMINAR') {
      onConfirm();
      setConfirmText('');
    }
  };

  return (
    <Dialog 
      open={open} 
      onClose={handleClose} 
      PaperProps={{
        sx: {
          borderRadius: 2,
          p: 2,
          minWidth: 400,
          maxWidth: 500,
          textAlign: 'center',
        }
      }}
    >
      <Box display="flex" justifyContent="center" mt={2} mb={1}>
        <WarningRoundedIcon sx={{ fontSize: 64, color: 'error.main' }} />
      </Box>
      <DialogTitle sx={{ fontWeight: 'bold', fontSize: '1.5rem', pb: 1, color: 'error.main' }}>
        {title || "¡ADVERTENCIA DE SEGURIDAD!"}
      </DialogTitle>
      
      <DialogContent sx={{ pb: 1 }}>
        <Typography variant="body1" sx={{ mb: 2, fontWeight: 500 }}>
          {message}
        </Typography>

        {warningDetails && (
          <Alert severity="error" sx={{ mb: 3, textAlign: 'left', borderRadius: 2 }}>
            {warningDetails}
          </Alert>
        )}
        
        <Typography variant="body2" sx={{ mb: 1, color: 'text.secondary', fontWeight: 'bold' }}>
          Para proceder, escribe la palabra "ELIMINAR" en mayúsculas:
        </Typography>
        
        <TextField
          fullWidth
          size="small"
          placeholder="Escribe ELIMINAR aquí"
          value={confirmText}
          onChange={(e) => setConfirmText(e.target.value)}
          inputProps={{ style: { textAlign: 'center', letterSpacing: '2px', fontWeight: 'bold' } }}
          autoComplete="off"
        />
      </DialogContent>
      
      <DialogActions sx={{ justifyContent: 'center', pt: 2, gap: 2 }}>
        <Button 
          onClick={handleClose} 
          variant="outlined" 
          color="inherit" 
          sx={{ borderRadius: 2, px: 3 }}
        >
          Cancelar
        </Button>
        <Button 
          onClick={handleConfirm} 
          variant="contained" 
          color="error" 
          disabled={confirmText !== 'ELIMINAR'}
          sx={{ borderRadius: 2, px: 3, boxShadow: 2 }}
        >
          Borrar Permanentemente
        </Button>
      </DialogActions>
    </Dialog>
  );
};

export default ConfirmDestructiveDeleteDialog;
