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
import WarningRoundedIcon from '@mui/icons-material/WarningRounded';

const ConfirmDeleteDialog = ({ open, onClose, onConfirm, title, message }) => {
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
        <WarningRoundedIcon sx={{ fontSize: 64, color: 'error.main' }} />
      </Box>
      <DialogTitle sx={{ fontWeight: 'bold', fontSize: '1.5rem', pb: 1 }}>
        {title || 'Confirmar Eliminación'}
      </DialogTitle>
      <DialogContent>
        <Typography color="text.secondary" sx={{ fontSize: '1.1rem' }}>
          {message || '¿Está seguro de que desea eliminar este registro? Esta acción no se puede deshacer.'}
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
          onClick={onConfirm} 
          variant="contained" 
          color="error" 
          disableElevation
          sx={{ borderRadius: 2, px: 4, py: 1 }}
        >
          Sí, Eliminar
        </Button>
      </DialogActions>
    </Dialog>
  );
};

export default ConfirmDeleteDialog;
