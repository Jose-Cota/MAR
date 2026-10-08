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
import WarningAmberIcon from '@mui/icons-material/WarningAmber';
import ErrorOutlineIcon from '@mui/icons-material/ErrorOutline';
import InfoOutlinedIcon from '@mui/icons-material/InfoOutlined';

const AlertDialog = ({ open, onClose, title, message, type = 'warning' }) => {
  const getIcon = () => {
    switch(type) {
      case 'error': return <ErrorOutlineIcon sx={{ fontSize: 64, color: 'error.main' }} />;
      case 'info': return <InfoOutlinedIcon sx={{ fontSize: 64, color: 'info.main' }} />;
      case 'warning':
      default: return <WarningAmberIcon sx={{ fontSize: 64, color: 'warning.main' }} />;
    }
  };

  const getButtonColor = () => {
    switch(type) {
      case 'error': return 'error';
      case 'info': return 'info';
      case 'warning':
      default: return 'warning';
    }
  };

  return (
    <Dialog 
      open={open} 
      onClose={onClose} 
      PaperProps={{
        sx: {
          borderRadius: 2,
          p: 2,
          minWidth: 350,
          textAlign: 'center',
        }
      }}
    >
      <Box display="flex" justifyContent="center" mt={2} mb={1}>
        {getIcon()}
      </Box>
      <DialogTitle sx={{ fontWeight: 'bold', fontSize: '1.4rem', pb: 1 }}>
        {title || 'Aviso'}
      </DialogTitle>
      <DialogContent>
        <Typography color="text.secondary" sx={{ fontSize: '1.05rem' }}>
          {message}
        </Typography>
      </DialogContent>
      <DialogActions sx={{ justifyContent: 'center', p: 2, pt: 1 }}>
        <Button 
          onClick={onClose} 
          variant="contained" 
          color={getButtonColor()} 
          disableElevation
          sx={{ borderRadius: 2, px: 4, py: 1, color: 'white' }}
        >
          Entendido
        </Button>
      </DialogActions>
    </Dialog>
  );
};

export default AlertDialog;
