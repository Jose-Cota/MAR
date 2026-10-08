import React from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Typography,
  Button,
  Box,
} from '@mui/material';
import CheckCircleOutlineIcon from '@mui/icons-material/CheckCircleOutline';

const ConfirmValidateDialog = ({ open, onClose, onConfirm, title, message }) => {
  return (
    <Dialog 
      open={open} 
      onClose={onClose} 
      PaperProps={{
        sx: {
          borderRadius: 2,
          p: 2,
          minWidth: 400,
          textAlign: 'center',
        }
      }}
    >
      <Box display="flex" justifyContent="center" mt={2} mb={1}>
        <CheckCircleOutlineIcon sx={{ fontSize: 64, color: 'success.main' }} />
      </Box>
      <DialogTitle sx={{ fontWeight: 'bold', fontSize: '1.5rem', pb: 1 }}>
        {title || 'Confirmar Validación'}
      </DialogTitle>
      <DialogContent>
        <Typography color="text.secondary" sx={{ fontSize: '1.1rem' }}>
          {message || '¿Está seguro de que desea validar este registro?'}
        </Typography>
      </DialogContent>
      <DialogActions sx={{ justifyContent: 'center', p: 2, pt: 2, gap: 2 }}>
        <Button 
          onClick={onClose} 
          variant="outlined" 
          color="inherit" 
          sx={{ borderRadius: 2, px: 4, py: 1 }}
        >
          Cancelar
        </Button>
        <Button 
          onClick={() => {
            onConfirm();
            onClose();
          }} 
          variant="contained" 
          color="success" 
          disableElevation
          sx={{ borderRadius: 2, px: 4, py: 1 }}
        >
          Sí, Validar
        </Button>
      </DialogActions>
    </Dialog>
  );
};

export default ConfirmValidateDialog;
