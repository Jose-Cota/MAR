import React, { useState, useEffect } from 'react';
import useAuth from '../../../hooks/useAuth';
import {
  Container,
  Box,
  Typography,
  Button,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  CircularProgress,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  MenuItem,
  Select,
  FormControl,
  InputLabel,
  Chip,
} from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import DomainIcon from '@mui/icons-material/Domain';
import axios from '../../../utils/axios';
import useGlobalStore from '../../../stores/useGlobalStore';
import ConfirmDeleteDialog from '../../../components/ui/ConfirmDeleteDialog';

const UnidadesResponsablesPage = () => {
  const { hasRole } = useAuth();
  const canManage = hasRole('Administrador');
  const ejercicio = useGlobalStore((state) => state.ejercicio);
  const [unidades, setUnidades] = useState([]);
  const [loading, setLoading] = useState(true);
  const [openModal, setOpenModal] = useState(false);
  const [deleteConfirmOpen, setDeleteConfirmOpen] = useState(false);
  const [deleteId, setDeleteId] = useState(null);
  const [formData, setFormData] = useState({
    unidad_responsable_gasto_id: null,
    numero: '',
    nombre: '',
    cerrada: 0,
  });

  const fetchUnidades = async () => {
    try {
      setLoading(true);
      const response = await axios.get('/unidades-responsables', {
        params: { ejercicio }
      });
      setUnidades(response.data);
    } catch (error) {
      console.error('Error fetching unidades responsables:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (ejercicio) {
      fetchUnidades();
    }
  }, [ejercicio]);

  const handleOpenModal = (unidad = null) => {
    if (unidad) {
      setFormData({
        unidad_responsable_gasto_id: unidad.unidad_responsable_gasto_id,
        numero: unidad.numero,
        nombre: unidad.nombre,
        cerrada: unidad.cerrada,
      });
    } else {
      setFormData({
        unidad_responsable_gasto_id: null,
        numero: '',
        nombre: '',
        cerrada: 0,
      });
    }
    setOpenModal(true);
  };

  const handleCloseModal = () => {
    setOpenModal(false);
  };

  const handleSave = async () => {
    try {
      if (formData.unidad_responsable_gasto_id) {
        await axios.put(`/unidades-responsables/${formData.unidad_responsable_gasto_id}`, formData);
      } else {
        await axios.post('/unidades-responsables', formData);
      }
      fetchUnidades();
      handleCloseModal();
    } catch (error) {
      console.error('Error saving unidad responsable:', error);
      alert('Error al guardar la unidad responsable: ' + (error.response?.data?.message || ''));
    }
  };

  const handleDeleteClick = (id) => {
    setDeleteId(id);
    setDeleteConfirmOpen(true);
  };

  const confirmDelete = async () => {
    try {
      await axios.delete(`/unidades-responsables/${deleteId}`);
      fetchUnidades();
    } catch (error) {
      console.error('Error deleting unidad responsable:', error);
      alert('Error al eliminar la unidad responsable');
    } finally {
      setDeleteConfirmOpen(false);
      setDeleteId(null);
    }
  };

  return (
    <Container maxWidth={false}>
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
        <Box display="flex" alignItems="center" gap={1}>
          <DomainIcon fontSize="large" color="primary" />
          <Typography variant="h4">
            {canManage ? 'Unidades Responsables' : 'Validar y enviar ficha POA'}
          </Typography>
        </Box>
        {canManage && (
          <Button
            variant="contained"
            color="primary"
            startIcon={<AddIcon />}
            onClick={() => handleOpenModal()}
          >
            Agregar Unidad Responsable
          </Button>
        )}
      </Box>

      {loading ? (
        <Box display="flex" justifyContent="center">
          <CircularProgress />
        </Box>
      ) : (
        <TableContainer component={Paper}>
          <Table size="small" sx={{ '& .MuiTableCell-root': { py: 0.5 } }}>
            <TableHead>
              <TableRow>
                <TableCell>Número</TableCell>
                <TableCell>Nombre</TableCell>
                <TableCell>Estado</TableCell>
                {canManage && <TableCell align="right" sx={{ width: '1%', whiteSpace: 'nowrap' }}>Acciones</TableCell>}
              </TableRow>
            </TableHead>
            <TableBody>
              {unidades.map((row) => (
                <TableRow key={row.unidad_responsable_gasto_id}>
                  <TableCell>{row.numero}</TableCell>
                  <TableCell>{row.nombre}</TableCell>
                  <TableCell>
                    {row.cerrada ? (
                      <Chip label="Cerrada" color="error" size="small" />
                    ) : (
                      <Chip label="Abierta" color="success" size="small" />
                    )}
                  </TableCell>
                  {canManage && (
                    <TableCell align="right" sx={{ whiteSpace: 'nowrap' }}>
                      <Button
                        color="primary"
                        onClick={() => handleOpenModal(row)}
                        sx={{ minWidth: 'auto', p: 1 }}
                      >
                        <EditIcon />
                      </Button>
                      <Button size="small" color="error" onClick={() => handleDeleteClick(row.unidad_responsable_gasto_id)}>
                        <DeleteIcon fontSize="small" />
                      </Button>
                    </TableCell>
                  )}
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </TableContainer>
      )}

      <Dialog open={openModal} onClose={handleCloseModal} maxWidth="sm" fullWidth>
        <DialogTitle>
          {formData.unidad_responsable_gasto_id ? 'Editar' : 'Agregar'} Unidad Responsable
        </DialogTitle>
        <DialogContent>
          <Box display="flex" flexDirection="column" gap={2} mt={2}>
            <TextField
              label="Número"
              value={formData.numero}
              onChange={(e) => setFormData({ ...formData, numero: e.target.value })}
              fullWidth
              disabled={!canManage}
            />
            <TextField
              label="Nombre"
              value={formData.nombre}
              onChange={(e) => setFormData({ ...formData, nombre: e.target.value })}
              fullWidth
              disabled={!canManage}
            />
            {/* The old system conditioned this field based on $this->session->userdata('validacion') == 1 */}
            {/* For now, we will show it for everyone in the new system since it's a general catalog. */}
            {formData.unidad_responsable_gasto_id && (
                <FormControl fullWidth>
                  <InputLabel>Estatus de Edición y Registro</InputLabel>
                  <Select
                    value={formData.cerrada}
                    label="Estatus de Edición y Registro"
                    onChange={(e) => setFormData({ ...formData, cerrada: e.target.value })}
                  >
                    <MenuItem value={0}>Abierta</MenuItem>
                    <MenuItem value={1}>Cerrada</MenuItem>
                  </Select>
                </FormControl>
            )}
          </Box>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCloseModal} color="inherit">
            Cancelar
          </Button>
          <Button onClick={handleSave} variant="contained" color="primary">
            {formData.unidad_responsable_gasto_id ? 'Actualizar' : 'Guardar'}
          </Button>
        </DialogActions>
      </Dialog>
      
      <ConfirmDeleteDialog 
        open={deleteConfirmOpen} 
        onClose={() => setDeleteConfirmOpen(false)} 
        onConfirm={confirmDelete} 
        message={canManage 
          ? "¿Está seguro de que desea eliminar esta Unidad Responsable? Esta acción no se puede deshacer." 
          : "¿Está seguro de que desea eliminar su ficha POA? Esta acción no se puede deshacer y borrará permanentemente sus datos."}
      />
    </Container>
  );
};

export default UnidadesResponsablesPage;
