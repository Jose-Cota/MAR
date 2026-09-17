import { useState, useEffect } from 'react';
import {
  Box,
  Card,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Typography,
  IconButton,
  Chip,
  Button,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  DialogContentText,
  Snackbar,
  Alert
} from '@mui/material';
import Iconify from '../../../components/Iconify';
import useGlobalStore from '../../../stores/useGlobalStore';
import axios from '../../../utils/axios';
import ModoFormPanel from './ModoFormPanel';
import ModoSummaryModal from './ModoSummaryModal';

const TYPE_LABELS = {
  elaboracion_proyectos: 'Elaboración',
  seguimiento_proyectos: 'Seguimiento',
  anteproyecto: 'Anteproyecto',
};

export default function ModosPage() {
  const ejercicio = useGlobalStore((state) => state.ejercicio);
  const [modos, setModos] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [selectedModo, setSelectedModo] = useState(null);
  const [confirmDeleteId, setConfirmDeleteId] = useState(null);
  const [snackbarMessage, setSnackbarMessage] = useState('');
  const [errorMsg, setErrorMsg] = useState('');
  const [showErrorModal, setShowErrorModal] = useState(false);
  const [summaryStats, setSummaryStats] = useState(null);
  const [summaryType, setSummaryType] = useState(null);

  useEffect(() => {
    fetchModos();
  }, []);

  const fetchModos = async () => {
    setLoading(true);
    try {
      const response = await axios.get(`/modos`);
      setModos(response.data);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleEdit = (modo) => {
    setSelectedModo(modo);
    setIsModalOpen(true);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setSelectedModo(null);
  };

  const handleSuccess = (stats) => {
    fetchModos();
    handleCloseModal();
    if (stats) {
      setSummaryStats(stats);
      setSummaryType('create');
    }
  };

  const handleDeleteClick = (id) => {
    setConfirmDeleteId(id);
  };

  const handleConfirmDelete = async () => {
    if (!confirmDeleteId) return;
    try {
      const response = await axios.delete(`/modos/${confirmDeleteId}`);
      if (response.data.stats) {
        setSummaryStats(response.data.stats);
        setSummaryType('delete');
      } else {
        setSnackbarMessage('Etapa eliminada correctamente, incluyendo todos sus registros asociados.');
      }
      fetchModos();
    } catch (err) {
      console.error(err);
      const msg = err.response?.data?.message || err.message || 'Error al eliminar la etapa.';
      setErrorMsg(msg);
      setShowErrorModal(true);
    } finally {
      setConfirmDeleteId(null);
    }
  };

  return (
    <Box>
      <Dialog
        open={Boolean(confirmDeleteId)}
        onClose={() => setConfirmDeleteId(null)}
        PaperProps={{ sx: { borderRadius: 2 } }}
      >
        <DialogTitle sx={{ backgroundColor: '#fee2e2', color: '#991b1b', display: 'flex', alignItems: 'center', gap: 1 }}>
          <Iconify icon="mdi:alert-circle-outline" width={24} />
          Confirmar Eliminación
        </DialogTitle>
        <DialogContent sx={{ pt: 3 }}>
          <DialogContentText>
            ¿Estás seguro de que deseas eliminar esta etapa? 
            <br/><br/>
            <strong>Advertencia:</strong> Esta acción eliminará permanentemente la configuración de la etapa y, si es una etapa de Elaboración, borrará todos los registros y catálogos (Proyectos, Metas, URGs, etc.) asociados a este ejercicio.
          </DialogContentText>
        </DialogContent>
        <DialogActions sx={{ p: 2, pt: 0 }}>
          <Button onClick={() => setConfirmDeleteId(null)} color="inherit">
            Cancelar
          </Button>
          <Button onClick={handleConfirmDelete} variant="contained" color="error">
            Eliminar Todo
          </Button>
        </DialogActions>
      </Dialog>

      <ModoFormPanel
        open={isModalOpen}
        onClose={handleCloseModal}
        modo={selectedModo}
        onSuccess={handleSuccess}
      />

      <ModoSummaryModal
        open={Boolean(summaryStats)}
        onClose={() => setSummaryStats(null)}
        stats={summaryStats}
        type={summaryType}
      />
      <Box>
      <Box sx={{ mb: 4, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <Box>
          <Typography variant="h4">Configuración de Etapas</Typography>
          <Typography variant="body2" color="textSecondary">
            Administra la vigencia y estatus de las diferentes etapas.
          </Typography>
        </Box>
        <Button
          variant="contained"
          startIcon={<Iconify icon="mdi:plus" />}
          onClick={() => {
            setSelectedModo(null);
            setIsModalOpen(true);
          }}
        >
          Agregar Etapa
        </Button>
      </Box>

      <Card>
        <TableContainer>
          <Table>
            <TableHead>
              <TableRow>
                <TableCell>Etapa</TableCell>
                <TableCell>Ejercicio</TableCell>
                <TableCell>Estado</TableCell>
                <TableCell>Fecha Inicio</TableCell>
                <TableCell>Fecha Fin</TableCell>
                <TableCell align="right">Acciones</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {loading ? (
                <TableRow>
                  <TableCell colSpan={6} align="center" sx={{ py: 3 }}>
                    Cargando...
                  </TableCell>
                </TableRow>
              ) : modos.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={6} align="center" sx={{ py: 3 }}>
                    No hay etapas configuradas.
                  </TableCell>
                </TableRow>
              ) : (
                modos.map((modo) => (
                  <TableRow key={modo.operacion_ejercicio_id} hover>
                    <TableCell sx={{ fontWeight: 'bold' }}>
                      {TYPE_LABELS[modo.tipo] || modo.tipo}
                    </TableCell>
                    <TableCell>{modo.ejercicio}</TableCell>
                    <TableCell>
                      <Chip
                        label={modo.habilitado === 'si' ? 'Habilitado' : 'Deshabilitado'}
                        color={modo.habilitado === 'si' ? 'success' : 'default'}
                        size="small"
                      />
                    </TableCell>
                    <TableCell>{modo.fecha_inicio || 'No definida'}</TableCell>
                    <TableCell>{modo.fecha_fin || 'No definida'}</TableCell>
                    <TableCell align="right">
                      <IconButton color="primary" onClick={() => handleEdit(modo)}>
                        <Iconify icon="mdi:pencil" />
                      </IconButton>
                      <IconButton color="error" onClick={() => handleDeleteClick(modo.operacion_ejercicio_id)}>
                        <Iconify icon="mdi:delete" />
                      </IconButton>
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </TableContainer>
      </Card>

      <Snackbar 
        open={Boolean(snackbarMessage)} 
        autoHideDuration={6000} 
        onClose={() => setSnackbarMessage('')}
        anchorOrigin={{ vertical: 'bottom', horizontal: 'center' }}
      >
        <Alert onClose={() => setSnackbarMessage('')} severity="success" sx={{ width: '100%', borderRadius: 2, fontWeight: 'bold' }}>
          {snackbarMessage}
        </Alert>
      </Snackbar>

      <Dialog 
        open={showErrorModal} 
        onClose={() => setShowErrorModal(false)}
        PaperProps={{ sx: { borderRadius: 3, p: 2, textAlign: 'center', maxWidth: 400 } }}
      >
        <DialogTitle sx={{ pt: 3, pb: 1 }}>
          <Iconify icon="mdi:alert-decagram" sx={{ color: '#d32f2f', width: 64, height: 64, mb: 2 }} />
          <Typography variant="h5" color="error.main" fontWeight="bold">
            No se pudo eliminar
          </Typography>
        </DialogTitle>
        <DialogContent>
          <Typography variant="body1" sx={{ color: 'text.secondary', mt: 1 }}>
            {errorMsg}
          </Typography>
        </DialogContent>
        <DialogActions sx={{ justifyContent: 'center', pb: 2 }}>
          <Button 
            variant="contained" 
            color="error" 
            onClick={() => setShowErrorModal(false)}
            sx={{ borderRadius: 2, px: 4, py: 1 }}
          >
            Entendido
          </Button>
        </DialogActions>
      </Dialog>

      </Box>
    </Box>
  );
}
