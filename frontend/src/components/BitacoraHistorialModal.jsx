import React, { useState, useEffect } from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  Typography,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  CircularProgress,
  Box,
  IconButton,
  Chip
} from '@mui/material';
import Iconify from './Iconify';
import axios from '../utils/axios';

const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  
  // Format to CDMX time
  return new Intl.DateTimeFormat('es-MX', {
    timeZone: 'America/Mexico_City',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  }).format(date).replace(',', '');
};

export default function BitacoraHistorialModal({ open, onClose, proyectoId }) {
  const [historial, setHistorial] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (open && proyectoId) {
      fetchHistorial();
    }
  }, [open, proyectoId]);

  const fetchHistorial = async () => {
    setLoading(true);
    try {
      const res = await axios.get(`/proyectos/${proyectoId}/bitacora`);
      setHistorial(res.data);
    } catch (err) {
      console.error('Error al cargar la bitácora', err);
    } finally {
      setLoading(false);
    }
  };

  const actionLabels = {
    enviar_validador: 'Enviar al Validador',
    aprobar: 'Aprobar proyecto',
    rechazar: 'Rechazar proyecto',
    enviar_dpyrf: 'Cerrar y enviar a DPyRF',
    devolver_modificacion: 'Devolver para modificación'
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth PaperProps={{ sx: { borderRadius: 1, height: '80vh', maxHeight: '80vh', maxWidth: '1080px' } }}>
      <DialogTitle sx={{ p: 1.5, fontSize: '1rem', display: 'flex', alignItems: 'center', justifyContent: 'space-between', background: 'linear-gradient(135deg, #0a1e42 0%, #0d2f66 50%, #1e52a8 100%)', color: '#fff' }}>
        <Box display="flex" alignItems="center" gap={1}>
          <Iconify icon="mdi:history" width={22} />
          Bitácora de la ficha descriptiva...
        </Box>
        <IconButton onClick={onClose} size="small" sx={{ color: '#fff' }}>
          <Iconify icon="mdi:close" />
        </IconButton>
      </DialogTitle>
      
      <DialogContent sx={{ p: 3, backgroundColor: '#f8f9fa' }}>
        {loading ? (
          <Box display="flex" justifyContent="center" my={4}>
            <CircularProgress />
          </Box>
        ) : (
          <TableContainer component={Paper} elevation={0} sx={{ border: '1px solid #e0e0e0', borderRadius: 2 }}>
            <Table size="small" sx={{ minWidth: 650 }}>
              <TableHead sx={{ bgcolor: '#f0f4f8' }}>
                <TableRow>
                  <TableCell sx={{ fontWeight: 'bold', width: '50px', textAlign: 'center', color: '#1F4E79', fontSize: '0.7rem', py: 0.25, lineHeight: 1.2 }}>Consecutivo</TableCell>
                  <TableCell sx={{ fontWeight: 'bold', width: '180px', color: '#1F4E79', fontSize: '0.7rem', py: 0.25, lineHeight: 1.2 }}>Fecha y hora</TableCell>
                  <TableCell sx={{ fontWeight: 'bold', width: '200px', color: '#1F4E79', fontSize: '0.7rem', py: 0.25, lineHeight: 1.2 }}>Usuario</TableCell>
                  <TableCell sx={{ fontWeight: 'bold', color: '#1F4E79', fontSize: '0.7rem', py: 0.25, lineHeight: 1.2 }}>Mensaje</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {historial.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={4} align="center" sx={{ py: 3, color: 'text.secondary' }}>
                      No hay registros en la bitácora para este proyecto.
                    </TableCell>
                  </TableRow>
                ) : (
                  [...historial].sort((a, b) => new Date(b.created_at) - new Date(a.created_at)).map((item, idx) => (
                    <TableRow key={item.id || idx} hover sx={{ '&:last-child td, &:last-child th': { border: 0 } }}>
                      <TableCell align="center" sx={{ fontSize: '0.7rem', py: 0.25, lineHeight: 1.2 }}>{historial.length - idx}</TableCell>
                      <TableCell sx={{ fontSize: '0.7rem', py: 0.25, lineHeight: 1.2 }}>{item.created_at ? formatDate(item.created_at) : '-'}</TableCell>
                      <TableCell sx={{ fontSize: '0.7rem', py: 0.25, lineHeight: 1.2 }}>{item.usuario || 'Sistema'}</TableCell>
                      <TableCell sx={{ whiteSpace: 'pre-wrap', fontSize: '0.7rem', py: 0.25, lineHeight: 1.2 }}>
                        {item.accion && (
                          <Chip 
                            size="small" 
                            label={actionLabels[item.accion] || item.accion} 
                            sx={{ fontSize: '0.65rem', height: '18px', mr: 1, bgcolor: '#e3f2fd', color: '#1976d2' }} 
                          />
                        )}
                        {item.mensaje}
                      </TableCell>
                    </TableRow>
                  ))
                )}
              </TableBody>
            </Table>
          </TableContainer>
        )}
      </DialogContent>
      <DialogActions sx={{ p: 2, bgcolor: '#f8f9fa' }}>
        <Button onClick={onClose} variant="outlined" color="primary">
          Cerrar
        </Button>
      </DialogActions>
    </Dialog>
  );
}
