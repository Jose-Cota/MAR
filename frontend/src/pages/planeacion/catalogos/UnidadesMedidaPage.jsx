import React, { useState, useEffect } from 'react';
import {
  Box,
  Card,
  Table,
  Button,
  TableRow,
  TableBody,
  TableCell,
  Container,
  Typography,
  TableContainer,
  TablePagination,
  TableHead,
  CircularProgress,
  IconButton,
  Tooltip,
  TextField,
  Checkbox,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Snackbar,
  Alert
} from '@mui/material';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import AddIcon from '@mui/icons-material/Add';
import axios from '../../../utils/axios';
import useGlobalStore from '../../../stores/useGlobalStore';
import ConfirmDeleteDialog from '../../../components/ui/ConfirmDeleteDialog';
import UnidadMedidaFormModal from './UnidadMedidaFormModal';
import Iconify from '../../../components/Iconify';

import MigrarUnidadesModal from './MigrarUnidadesModal';

export default function UnidadesMedidaPage() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(10);
  const [deleteConfirmOpen, setDeleteConfirmOpen] = useState(false);
  const [deleteId, setDeleteId] = useState(null);
  const [modalOpen, setModalOpen] = useState(false);
  const [migrarModalOpen, setMigrarModalOpen] = useState(false);
  const [selectedItem, setSelectedItem] = useState(null);
  const [filterNombre, setFilterNombre] = useState('');
  const [filterDescripcion, setFilterDescripcion] = useState('');
  const [filterUtilizado, setFilterUtilizado] = useState('');
  
  const [selected, setSelected] = useState([]);
  const [batchDeleteConfirmOpen, setBatchDeleteConfirmOpen] = useState(false);
  const [snackbar, setSnackbar] = useState({ open: false, message: '', variant: 'success' });

  const enqueueSnackbar = (message, variant = 'success') => setSnackbar({ open: true, message, variant });

  const ejercicio = useGlobalStore((state) => state.ejercicio);

  useEffect(() => {
    fetchData();
    setSelected([]);
  }, [ejercicio]);

  const fetchData = async () => {
    setLoading(true);
    try {
      const response = await axios.get('/unidades-medida', {
        params: { ejercicio }
      });
      setData(response.data);
    } catch (error) {
      console.error("Error fetching data:", error);
    } finally {
      setLoading(false);
    }
  };

  const handleChangePage = (event, newPage) => {
    setPage(newPage);
  };

  const handleChangeRowsPerPage = (event) => {
    setRowsPerPage(parseInt(event.target.value, 10));
    setPage(0);
  };

  const handleDeleteClick = (id) => {
    setDeleteId(id);
    setDeleteConfirmOpen(true);
  };

  const confirmDelete = async () => {
    try {
      await axios.delete(`/unidades-medida/${deleteId}`);
      enqueueSnackbar('Unidad de medida eliminada correctamente', 'success');
      fetchData();
    } catch (error) {
      console.error('Error deleting UM:', error);
      enqueueSnackbar('Error al eliminar la Unidad de Medida', 'error');
    } finally {
      setDeleteConfirmOpen(false);
      setDeleteId(null);
    }
  };

  const confirmBatchDelete = async () => {
    try {
      const response = await axios.post(`/unidades-medida/batch-delete`, { ids: selected });
      enqueueSnackbar(`${response.data.message}. Se borraron ${response.data.deleted} registros. ${response.data.skipped} registros se omitieron por estar en uso.`, 'success');
      fetchData();
      setSelected([]);
    } catch (error) {
      console.error('Error deleting batch UM:', error);
      enqueueSnackbar('Error al eliminar las Unidades de Medida', 'error');
    } finally {
      setBatchDeleteConfirmOpen(false);
    }
  };

  const filteredData = data.filter(row => {
    const matchesNombre = filterNombre === '' || (row.nombre || '').toLowerCase().includes(filterNombre.toLowerCase());
    const matchesDesc = filterDescripcion === '' || (row.descripcion || '').toLowerCase().includes(filterDescripcion.toLowerCase());
    const matchesUtil = filterUtilizado === '' || (filterUtilizado === 'Si' ? row.utilizado == 1 : row.utilizado == 0);
    return matchesNombre && matchesDesc && matchesUtil;
  });

  const handleSelectAll = (event) => {
    if (event.target.checked) {
      const newSelected = filteredData.map((n) => n.unidad_medida_id);
      setSelected(newSelected);
      return;
    }
    setSelected([]);
  };

  const handleSelect = (event, id) => {
    const selectedIndex = selected.indexOf(id);
    let newSelected = [];

    if (selectedIndex === -1) {
      newSelected = newSelected.concat(selected, id);
    } else if (selectedIndex === 0) {
      newSelected = newSelected.concat(selected.slice(1));
    } else if (selectedIndex === selected.length - 1) {
      newSelected = newSelected.concat(selected.slice(0, -1));
    } else if (selectedIndex > 0) {
      newSelected = newSelected.concat(
        selected.slice(0, selectedIndex),
        selected.slice(selectedIndex + 1)
      );
    }

    setSelected(newSelected);
  };

  return (
    <Container maxWidth="xl">
      <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 5 }}>
        <Typography variant="h4" gutterBottom sx={{ display: 'flex', alignItems: 'center' }}>
          <Iconify icon="mdi:ruler" sx={{ mr: 2, width: 32, height: 32 }} />
          Unidades de Medida
        </Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => { setSelectedItem(null); setModalOpen(true); }}>
          Agregar Unidad Medida
        </Button>
      </Box>

      <Card>
        {loading ? (
          <Box sx={{ display: 'flex', justifyContent: 'center', p: 5 }}>
            <CircularProgress />
          </Box>
        ) : (
          <>
            <Box sx={{ p: 2, display: 'flex', gap: 2, alignItems: 'center' }}>
              <TextField
                label="Buscar por Nombre"
                variant="outlined"
                size="small"
                value={filterNombre}
                onChange={(e) => { setFilterNombre(e.target.value); setPage(0); }}
              />
              <TextField
                label="Buscar por Descripción"
                variant="outlined"
                size="small"
                value={filterDescripcion}
                onChange={(e) => { setFilterDescripcion(e.target.value); setPage(0); }}
              />
              <FormControl size="small" sx={{ minWidth: 120 }}>
                <InputLabel>Utilizado</InputLabel>
                <Select
                  value={filterUtilizado}
                  label="Utilizado"
                  onChange={(e) => { setFilterUtilizado(e.target.value); setPage(0); }}
                >
                  <MenuItem value=""><em>Todos</em></MenuItem>
                  <MenuItem value="Si">Sí</MenuItem>
                  <MenuItem value="No">No</MenuItem>
                </Select>
              </FormControl>
              <Box sx={{ ml: 'auto', display: 'flex', gap: 1 }}>
                <Button
                  variant="contained"
                  color="primary"
                  onClick={() => setMigrarModalOpen(true)}
                  disabled={selected.length === 0}
                >
                  Migrar Registros
                </Button>
                <Button 
                  variant="contained" 
                  color="error" 
                  startIcon={<DeleteIcon />} 
                  onClick={() => setBatchDeleteConfirmOpen(true)}
                  disabled={selected.length === 0}
                >
                  Eliminar seleccionados {selected.length > 0 ? `(${selected.length})` : ''}
                </Button>
              </Box>
            </Box>
            <TableContainer>
              <Table size="small">
                <TableHead>
                  <TableRow>
                    <TableCell padding="checkbox">
                      <Checkbox
                        indeterminate={selected.length > 0 && selected.length < filteredData.length}
                        checked={filteredData.length > 0 && selected.length === filteredData.length}
                        onChange={handleSelectAll}
                      />
                    </TableCell>
                    <TableCell>Número</TableCell>
                    <TableCell>Nombre</TableCell>
                    <TableCell>Descripción</TableCell>
                    <TableCell align="right" sx={{ width: '1%', whiteSpace: 'nowrap' }}>Acciones</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {filteredData
                    .slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage)
                    .map((row) => {
                      const isItemSelected = selected.indexOf(row.unidad_medida_id) !== -1;
                      return (
                      <TableRow hover key={row.unidad_medida_id} selected={isItemSelected}>
                        <TableCell padding="checkbox">
                          <Checkbox
                            checked={isItemSelected}
                            onChange={(event) => handleSelect(event, row.unidad_medida_id)}
                          />
                        </TableCell>
                        <TableCell>{row.numero}</TableCell>
                        <TableCell>{row.nombre}</TableCell>
                        <TableCell>{row.descripcion}</TableCell>
                        <TableCell align="right" sx={{ whiteSpace: 'nowrap' }}>
                          <Tooltip title="Editar">
                            <IconButton onClick={() => { setSelectedItem(row); setModalOpen(true); }}>
                              <EditIcon />
                            </IconButton>
                          </Tooltip>
                          <Tooltip title={row.utilizado == 1 ? 'En uso (no se puede eliminar)' : 'Eliminar'}>
                            <span>
                              <IconButton color="error" disabled={row.utilizado == 1} onClick={() => handleDeleteClick(row.unidad_medida_id)}>
                                <DeleteIcon />
                              </IconButton>
                            </span>
                          </Tooltip>
                        </TableCell>
                      </TableRow>
                    )})}
                  {filteredData.length === 0 && (
                    <TableRow>
                      <TableCell colSpan={5} align="center">
                        No se encontraron registros
                      </TableCell>
                    </TableRow>
                  )}
                </TableBody>
              </Table>
            </TableContainer>

            <TablePagination
              rowsPerPageOptions={[10, 50, 200]}
              component="div"
              count={filteredData.length}
              rowsPerPage={rowsPerPage}
              page={page}
              onPageChange={handleChangePage}
              onRowsPerPageChange={handleChangeRowsPerPage}
              labelRowsPerPage="Registros por página:"
            />
          </>
        )}
      </Card>

      <ConfirmDeleteDialog 
        open={batchDeleteConfirmOpen} 
        onClose={() => setBatchDeleteConfirmOpen(false)} 
        onConfirm={confirmBatchDelete} 
        message={`Se van a borrar ${selected.length} registros y no hay forma de recuperarlos. ¿Continuar?`}
      />
      <ConfirmDeleteDialog 
        open={deleteConfirmOpen} 
        onClose={() => setDeleteConfirmOpen(false)} 
        onConfirm={confirmDelete} 
        message="¿Está seguro de que desea eliminar esta Unidad de Medida? Esta acción no se puede deshacer."
      />
      <UnidadMedidaFormModal 
        open={modalOpen} 
        onClose={() => setModalOpen(false)} 
        unidad={selectedItem} 
        onSuccess={() => { 
          setModalOpen(false); 
          enqueueSnackbar(selectedItem ? 'Unidad actualizada correctamente' : 'Unidad creada correctamente', 'success');
          fetchData(); 
        }} 
      />
      <Snackbar open={snackbar.open} autoHideDuration={6000} onClose={() => setSnackbar({ ...snackbar, open: false })} anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}>
        <Alert onClose={() => setSnackbar({ ...snackbar, open: false })} severity={snackbar.variant} sx={{ width: '100%' }}>
          {snackbar.message}
        </Alert>
      </Snackbar>

      <MigrarUnidadesModal
        open={migrarModalOpen}
        onClose={() => setMigrarModalOpen(false)}
        ejercicio={ejercicio}
        selected={selected}
        enqueueSnackbar={enqueueSnackbar}
        onMigrateSuccess={() => {
          setSelected([]);
          fetchData();
        }}
      />
    </Container>
  );
}
