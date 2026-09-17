import React from 'react';
import { Dialog, DialogTitle, DialogContent, DialogActions, Button, Typography, Box } from '@mui/material';
import Iconify from '../Iconify';

export default function ValidationModal({ open, onClose, errors = [] }) {
  return (
    <Dialog open={open} onClose={onClose} maxWidth="xs" fullWidth PaperProps={{ sx: { borderRadius: 2 } }}>
      <DialogTitle sx={{ display: 'flex', alignItems: 'center', gap: 1, color: 'error.main', pb: 1 }}>
        <Iconify icon="mdi:alert-circle" width={24} />
        Información incompleta
      </DialogTitle>
      <DialogContent>
        <Typography variant="body2" sx={{ mb: 2 }}>
          Para guardar, es necesario capturar los siguientes campos obligatorios:
        </Typography>
        <Box component="ul" sx={{ pl: 3, m: 0 }}>
          {errors.map((err, i) => (
            <Typography component="li" key={i} variant="body2" color="error.main" sx={{ fontWeight: 500, mb: 0.5 }}>
              {err}
            </Typography>
          ))}
        </Box>
      </DialogContent>
      <DialogActions sx={{ p: 2, pt: 1 }}>
        <Button onClick={onClose} variant="contained" color="error" fullWidth>
          Entendido
        </Button>
      </DialogActions>
    </Dialog>
  );
}
