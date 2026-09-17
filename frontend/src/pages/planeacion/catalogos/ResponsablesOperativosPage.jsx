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
  Snackbar,
  Alert
} from '@mui/material';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import AddIcon from '@mui/icons-material/Add';
import axios from '../../../utils/axios';
import useGlobalStore from '../../../stores/useGlobalStore';
import ConfirmDeleteDialog from '../../../components/ui/ConfirmDeleteDialog';
import Iconify from '../../../components/Iconify';
import ResponsableOperativoFormModal from './ResponsableOperativoFormModal';

export default function ResponsablesOperativosPage() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(50);
  const [deleteConfirmOpen, setDeleteConfirmOpen] = useState(false);
  const [deleteId, setDeleteId] = useState(null);
  const [modalOpen, setModalOpen] = useState(false);
  const [selectedItem, setSelectedItem] = useState(null);
  const [errorMessage, setErrorMessage] = useState('');
  
  const ejercicio = useGlobalStore((state) => state.ejercicio);

  useEffect(() => {
    fetchData();
  }, [ejercicio]);

  const fetchData = async () => {
    setLoading(true);
    try {
      const response = await axios.get('/responsables-operativos', {
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
      await axios.delete(`/responsables-operativos/${deleteId}`);
      fetchData();
    } catch (error) {
      console.error('Error deleting RO:', error);
      if (error.response && error.response.data && error.response.data.message) {
        setErrorMessage(error.response.data.message);
      } else {
        setErrorMessage('Error al eliminar el Responsable Operativo');
      }
    } finally {
      setDeleteConfirmOpen(false);
      setDeleteId(null);
    }
  };

  return (
    <Container maxWidth={false}>
      <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 5 }}>
        <Typography variant="h4" sx={{ display: 'flex', alignItems: 'center', gap: 1 }} gutterBottom>
          <Iconify icon="mdi:account-tie-outline" width={32} height={32} />
          Responsables Operativos
        </Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => { setSelectedItem(null); setModalOpen(true); }}>
          Nuevo Responsable
        </Button>
      </Box>

      <Card>
        {loading ? (
          <Box sx={{ display: 'flex', justifyContent: 'center', p: 5 }}>
            <CircularProgress />
          </Box>
        ) : (
          <>
            <TableContainer>
              <Table size="small">
                <TableHead>
                  <TableRow>
                    <TableCell>UR</TableCell>
                    <TableCell>Número</TableCell>
                    <TableCell>Nombre del Responsable</TableCell>
                    <TableCell align="right">Acciones</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {data
                    .slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage)
                    .map((row) => (
                      <TableRow hover key={row.responsable_operativo_id}>
                        <TableCell>{row.urg_numero}</TableCell>
                        <TableCell>{row.numero}</TableCell>
                        <TableCell>{row.nombre}</TableCell>
                        <TableCell align="right">
                          <Tooltip title="Editar">
                            <IconButton color="primary" onClick={() => { setSelectedItem(row); setModalOpen(true); }}>
                              <EditIcon />
                            </IconButton>
                          </Tooltip>
                          <Tooltip title="Eliminar">
                            <IconButton color="error" onClick={() => handleDeleteClick(row.responsable_operativo_id)}>
                              <DeleteIcon />
                            </IconButton>
                          </Tooltip>
                        </TableCell>
                      </TableRow>
                  ))}
                  {data.length === 0 && (
                    <TableRow>
                      <TableCell colSpan={4} align="center">
                        No se encontraron registros para el ejercicio {ejercicio}
                      </TableCell>
                    </TableRow>
                  )}
                </TableBody>
              </Table>
            </TableContainer>

            <TablePagination
              rowsPerPageOptions={[5, 10, 25, 50, 100]}
              component="div"
              count={data.length}
              rowsPerPage={rowsPerPage}
              page={page}
              onPageChange={handleChangePage}
              onRowsPerPageChange={handleChangeRowsPerPage}
              labelRowsPerPage="Filas por página"
            />
          </>
        )}
      </Card>

      <ConfirmDeleteDialog 
        open={deleteConfirmOpen} 
        onClose={() => setDeleteConfirmOpen(false)} 
        onConfirm={confirmDelete} 
        message={'¿Está seguro de que desea eliminar este Responsable Operativo? Esta acción no se puede deshacer.'}
      />
      <ResponsableOperativoFormModal 
        open={modalOpen} 
        onClose={() => setModalOpen(false)} 
        responsable={selectedItem} 
        onSuccess={() => { setModalOpen(false); fetchData(); }} 
      />
      <Snackbar 
        open={!!errorMessage} 
        autoHideDuration={5000} 
        onClose={() => setErrorMessage('')} 
        anchorOrigin={{ vertical: 'top', horizontal: 'center' }}
      >
        <Alert onClose={() => setErrorMessage('')} severity="error" sx={{ width: '100%', borderRadius: 2, boxShadow: 3 }}>
          {errorMessage}
        </Alert>
      </Snackbar>
    </Container>
  );
}
