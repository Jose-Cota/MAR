import React, { useState, useEffect } from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  Table,
  TableHead,
  TableRow,
  TableCell,
  TableBody,
  IconButton,
  Tooltip,
  Typography,
  Box,
  CircularProgress
} from '@mui/material';
import VisibilityIcon from '@mui/icons-material/Visibility';
import SwapHorizIcon from '@mui/icons-material/SwapHoriz';
import PublishIcon from '@mui/icons-material/Publish';
import axios from '../../../utils/axios';

export default function MigrarUnidadesModal({ open, onClose, ejercicio, selected, enqueueSnackbar, onMigrateSuccess }) {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(false);
  
  // State for visualizing details
  const [viewDetail, setViewDetail] = useState(null);
  
  // State for confirmation dialog
  const [confirmMigrate, setConfirmMigrate] = useState(null);

  useEffect(() => {
    if (open && ejercicio && selected?.length > 0) {
      fetchDuplicates();
      setViewDetail(null);
    } else if (open && (!selected || selected.length === 0)) {
      setData([]);
    }
  }, [open, ejercicio, selected]);

  const fetchDuplicates = async () => {
    setLoading(true);
    try {
      const res = await axios.get('/unidades-medida/duplicates', { 
        params: { ejercicio, ids: selected.join(',') } 
      });
      setData(res.data);
    } catch (error) {
      console.error(error);
      enqueueSnackbar('Error al cargar unidades duplicadas', 'error');
    } finally {
      setLoading(false);
    }
  };

  const truncate = (str, n) => {
    if (!str) return '';
    return str.length > n ? str.slice(0, n - 1) + '...' : str;
  };

  const handleInvert = (index) => {
    const newData = [...data];
    const row = newData[index];
    
    // Swap original and duplicado manually in state
    const tempId = row.original_id;
    const tempNombre = row.original_nombre;
    const tempDesc = row.original_descripcion;
    
    row.original_id = row.duplicado_id;
    row.original_nombre = row.duplicado_nombre;
    row.original_descripcion = row.duplicado_descripcion;
    
    row.duplicado_id = tempId;
    row.duplicado_nombre = tempNombre;
    row.duplicado_descripcion = tempDesc;
    
    setData(newData);
  };

  const handleMigrateClick = (row) => {
    setConfirmMigrate(row);
  };

  const executeMigrate = async () => {
    if (!confirmMigrate) return;
    try {
      await axios.post('/unidades-medida/migrate', {
        original_id: confirmMigrate.original_id,
        duplicado_id: confirmMigrate.duplicado_id
      });
      enqueueSnackbar('Migración exitosa', 'success');
      setConfirmMigrate(null);
      onMigrateSuccess();
      fetchDuplicates(); // reload list
    } catch (error) {
      console.error(error);
      enqueueSnackbar('Error al migrar', 'error');
      setConfirmMigrate(null);
    }
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="lg" fullWidth>
      <DialogTitle sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <span>Migrar Registros Duplicados</span>
      </DialogTitle>
      
      <DialogContent dividers>
        {viewDetail && (
          <Box sx={{ mb: 3, p: 2, bgcolor: '#f5f5f5', borderRadius: 1, position: 'relative' }}>
            <Button size="small" onClick={() => setViewDetail(null)} sx={{ position: 'absolute', top: 8, right: 8 }}>Cerrar detalle</Button>
            <Typography variant="subtitle2" color="primary">Detalle de Comparación</Typography>
            <Box sx={{ display: 'flex', gap: 2, mt: 1 }}>
              <Box sx={{ flex: 1 }}>
                <Typography variant="body2" fontWeight={600}>Original ({viewDetail.original_nombre}):</Typography>
                <Typography variant="body2">{viewDetail.original_descripcion || 'Sin descripción'}</Typography>
              </Box>
              <Box sx={{ flex: 1 }}>
                <Typography variant="body2" fontWeight={600}>Duplicado ({viewDetail.duplicado_nombre}):</Typography>
                <Typography variant="body2">{viewDetail.duplicado_descripcion || 'Sin descripción'}</Typography>
              </Box>
            </Box>
          </Box>
        )}

        <Typography variant="body2" sx={{ mb: 2 }}>
          La siguiente lista muestra unidades de medida que tienen nombres similares. Puedes invertir la dirección de la migración si lo consideras necesario. La acción de migrar moverá todas las metas e indicadores hacia el registro "Original" dejando desocupado el "Duplicado".
        </Typography>

        {loading ? (
          <Box sx={{ display: 'flex', justifyContent: 'center', p: 4 }}>
            <CircularProgress />
          </Box>
        ) : data.length === 0 ? (
          <Typography textAlign="center" sx={{ py: 3 }} color="textSecondary">No se encontraron duplicados pendientes en este ejercicio.</Typography>
        ) : (
          <Table size="small">
            <TableHead>
              <TableRow>
                <TableCell sx={{ fontSize: '13px' }}>Nombre Original</TableCell>
                <TableCell sx={{ fontSize: '13px' }}>Descripción del original</TableCell>
                <TableCell sx={{ fontSize: '13px' }}>Nombre del Duplicado</TableCell>
                <TableCell sx={{ fontSize: '13px' }}>Descripción del Duplicado</TableCell>
                <TableCell align="center" sx={{ fontSize: '13px' }}>Registros a migrar</TableCell>
                <TableCell align="center" sx={{ fontSize: '13px' }}>Acciones</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {data.map((row, idx) => (
                <TableRow key={idx}>
                  <TableCell sx={{ color: 'var(--primario)', fontWeight: 500, fontSize: '13px' }}>{row.original_nombre}</TableCell>
                  <TableCell sx={{ fontSize: '13px' }}>{row.original_descripcion}</TableCell>
                  <TableCell sx={{ color: 'error.main', fontSize: '13px' }}>{row.duplicado_nombre}</TableCell>
                  <TableCell sx={{ fontSize: '13px' }}>{row.duplicado_descripcion}</TableCell>
                  <TableCell align="center" sx={{ fontSize: '13px', fontWeight: 600 }}>{row.duplicado_usos}</TableCell>
                  <TableCell align="center" sx={{ whiteSpace: 'nowrap' }}>
                    <Tooltip title="Visualizar Detalle">
                      <IconButton size="small" color="info" onClick={() => setViewDetail(row)}>
                        <VisibilityIcon fontSize="small" />
                      </IconButton>
                    </Tooltip>
                    <Tooltip title="Invertir (Cambiar dirección)">
                      <IconButton size="small" color="warning" onClick={() => handleInvert(idx)}>
                        <SwapHorizIcon fontSize="small" />
                      </IconButton>
                    </Tooltip>
                    <Tooltip title="Migrar">
                      <IconButton size="small" color="primary" onClick={() => handleMigrateClick(row)}>
                        <PublishIcon fontSize="small" />
                      </IconButton>
                    </Tooltip>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        )}
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose}>Cerrar</Button>
      </DialogActions>

      {/* Confirm Migration Dialog */}
      <Dialog open={Boolean(confirmMigrate)} onClose={() => setConfirmMigrate(null)} maxWidth="sm" fullWidth>
        <DialogTitle sx={{ color: 'primary.main', fontWeight: 600 }}>Confirmar Migración</DialogTitle>
        <DialogContent dividers>
          {confirmMigrate && (
            <Box>
              <Typography variant="body1" gutterBottom>
                Se migrarán <strong>{confirmMigrate.duplicado_usos || 0}</strong> registros (metas, indicadores, etc.) que actualmente utilizan la unidad:
              </Typography>
              <Typography variant="body1" sx={{ color: 'error.main', fontWeight: 'bold', mb: 2, textAlign: 'center' }}>
                {confirmMigrate.duplicado_nombre}
              </Typography>
              <Typography variant="body1" gutterBottom>
                Y serán reasignados para apuntar a la unidad original:
              </Typography>
              <Typography variant="body1" sx={{ color: 'success.main', fontWeight: 'bold', mb: 2, textAlign: 'center' }}>
                {confirmMigrate.original_nombre}
              </Typography>
              <Typography variant="body2" color="textSecondary" sx={{ mt: 2, p: 2, bgcolor: '#fff3cd', color: '#856404', borderRadius: 1 }}>
                <strong>Nota:</strong> Esta acción modificará permanentemente las referencias en la base de datos. El registro duplicado original no será eliminado, pero quedará desocupado.
              </Typography>
            </Box>
          )}
        </DialogContent>
        <DialogActions sx={{ p: 2, px: 3 }}>
          <Button onClick={() => setConfirmMigrate(null)} color="inherit" variant="outlined">Cancelar</Button>
          <Button onClick={executeMigrate} color="primary" variant="contained" startIcon={<PublishIcon />}>Confirmar Migración</Button>
        </DialogActions>
      </Dialog>
    </Dialog>
  );
}
