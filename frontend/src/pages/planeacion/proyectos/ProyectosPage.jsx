import { useState, useEffect, useRef, useMemo } from 'react';
import {
  Card,
  Table,
  Stack,
  TableRow,
  TableBody,
  TableCell,
  Container,
  Typography,
  TableContainer,
  TablePagination,
  TextField,
  Box,
  IconButton,
  TableHead,
  TableSortLabel,
  Tooltip,
  Button,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions
} from '@mui/material';
import useAuth from '../../../hooks/useAuth';
import Iconify from '../../../components/Iconify';
import axios from '../../../utils/axios';
import useGlobalStore from '../../../stores/useGlobalStore';
import GraficasModal from '../../../components/GraficasModal';
import FichaDescriptivaModal from '../../../components/FichaDescriptivaModal';
import ModalAlinearProyecto from './ModalAlinearProyecto';
import NuevoProyectoModal from './NuevoProyectoModal';
import SeguimientoModal from './SeguimientoModal';
import ConfirmDestructiveDeleteDialog from '../../../components/ui/ConfirmDestructiveDeleteDialog';

const TABLE_HEAD = [
  { id: 'urg', label: 'URG', alignRight: false },
  { id: 'ro', label: 'RO', alignRight: false },
  { id: 'pg', label: 'PG', alignRight: false },
  { id: 'sp', label: 'SP', alignRight: false },
  { id: 'py', label: 'PY', alignRight: false },
  { id: 'denominacion', label: 'Denominación', alignRight: false },
  { id: 'estatus', label: 'Estatus', alignRight: false },
  { id: 'acciones', label: 'Acciones', alignRight: true },
];

const formatEstatus = (estatus) => {
  return estatus;
};

export default function ProyectosPage() {
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(50);
  const [search, setSearch] = useState('');
  const [estatusFilter, setEstatusFilter] = useState('Todos');
  const [order, setOrder] = useState('asc');
  const [orderBy, setOrderBy] = useState('');
  const [proyectos, setProyectos] = useState([]);
  const [loading, setLoading] = useState(false);
  const [graficasOpen, setGraficasOpen] = useState(false);
  const [fichaDescriptivaOpen, setFichaDescriptivaOpen] = useState(false);
  const [nuevoProyectoOpen, setNuevoProyectoOpen] = useState(false);
  const [seguimientoOpen, setSeguimientoOpen] = useState(false);
  const [selectedProyectoId, setSelectedProyectoId] = useState(null);
  const [snackbarOpen, setSnackbarOpen] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState('');
  const [pdfModalOpen, setPdfModalOpen] = useState(false);
  const [selectedPdfUrl, setSelectedPdfUrl] = useState('');
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [proyectoToDelete, setProyectoToDelete] = useState(null);
  const [pdfWarningOpen, setPdfWarningOpen] = useState(false);
  
  const [verificarModalOpen, setVerificarModalOpen] = useState(false);
  const [proyectoToVerificar, setProyectoToVerificar] = useState(null);
  
  const [cambiarEstatusModalOpen, setCambiarEstatusModalOpen] = useState(false);
  const [proyectoToCambiarEstatus, setProyectoToCambiarEstatus] = useState(null);
  const [nuevoEstatus, setNuevoEstatus] = useState('');

  const [alinearModalOpen, setAlinearModalOpen] = useState(false);
  const [proyectoToAlinear, setProyectoToAlinear] = useState(null);

  
  const ejercicio = useGlobalStore((state) => state.ejercicio);
  const etapasActivas = useGlobalStore((state) => state.etapasActivas);
  const isSeguimientoActive = etapasActivas.includes('seguimiento_proyectos');
  
  const { hasRole, user } = useAuth();
  const canCreate = hasRole('Administrador');
  console.log("Debug canCreate:", canCreate, "Role:", user?.role, "Roles:", user?.roles);
  
  const uniqueEstatusOptions = useMemo(() => {
    const statuses = new Set(proyectos.map(p => formatEstatus(p.estatus)).filter(Boolean));
    return ['Todos', ...Array.from(statuses)];
  }, [proyectos]);
  
  const handleNavClickRef = useRef();

  useEffect(() => {
    handleNavClickRef.current = (e) => {
      if (e.detail === '/planeacion/proyectos') {
        setFichaDescriptivaOpen(false);
        setSeguimientoOpen(false);
        setNuevoProyectoOpen(false);
        setGraficasOpen(false);
        fetchProyectos();
      }
    };
  });

  useEffect(() => {
    fetchProyectos();
  }, [ejercicio]);

  useEffect(() => {
    const handler = (e) => {
      if (handleNavClickRef.current) {
        handleNavClickRef.current(e);
      }
    };
    window.addEventListener('nav-link-clicked', handler);
    return () => window.removeEventListener('nav-link-clicked', handler);
  }, []);

  const fetchProyectos = async () => {
    setLoading(true);
    try {
      const response = await axios.get('/proyectos', {
        params: { ejercicio },
      });
      setProyectos(response.data);
    } catch (error) {
      console.error('Error fetching proyectos:', error);
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

  const handleSearch = (event) => {
    setSearch(event.target.value);
  };

  const handleOpenFichaDescriptiva = (proyectoId, estatus) => {
    const formattedEstatus = formatEstatus(estatus);
    const isAdmin = hasRole('Administrador') || hasRole('Administrador', 'web');
    const isCapturador = hasRole('Capturador');
    const isValidador = hasRole('Validador');

    let canOpen = false;

    const cleanEstatus = formattedEstatus ? formattedEstatus.trim().toLowerCase() : '';

    if (isAdmin) {
      canOpen = true;
    } else if (cleanEstatus === 'captura' && isCapturador) {
      canOpen = true;
    } else if ((cleanEstatus === 'validación' || cleanEstatus.includes('validaci')) && isValidador) {
      canOpen = true;
    }

    if (!canOpen) {
      let allowedEstatus = [];
      if (isCapturador) allowedEstatus.push('"Captura"');
      if (isValidador) allowedEstatus.push('"Validación"');
      
      const allowedText = allowedEstatus.length > 0 
        ? `Solo puedes editar proyectos en estatus ${allowedEstatus.join(' o ')}.` 
        : 'No tienes permisos para editar esta ficha.';
        
      setSnackbarMessage(`La Ficha Descriptiva no puede ser abierta porque se encuentra en estatus "${formattedEstatus}". ${allowedText}`);
      setSnackbarOpen(true);
      return;
    }

    setSelectedProyectoId(proyectoId);
    setFichaDescriptivaOpen(true);
  };

  const handlePrintPdf = () => {
    if (selectedPdfUrl) {
      const cleanUrl = selectedPdfUrl.split('#')[0];
      const iframe = document.createElement('iframe');
      iframe.style.position = 'absolute';
      iframe.style.width = '0px';
      iframe.style.height = '0px';
      iframe.style.border = 'none';
      iframe.src = cleanUrl;
      document.body.appendChild(iframe);
      iframe.onload = () => {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        setTimeout(() => {
          document.body.removeChild(iframe);
        }, 3000); // Increased timeout to ensure print dialog can open fully
      };
    }
  };

  const handleOpenSeguimiento = (id) => {
    setSelectedProyectoId(id);
    setSeguimientoOpen(true);
  };

  const handleOpenPdf = async (id) => {
    try {
      setLoading(true);
      
      // Validar si los campos de responsables están completos
      const descResponse = await axios.get(`/proyectos/${id}/ficha-descriptiva`);
      const data = descResponse.data;
      const proyecto = data.proyecto;
      
      const hasResponsable = proyecto.responsable_ficha && proyecto.puesto_responsable_ficha;
      const hasAutoriza = proyecto.autorizante_nombre && proyecto.autorizante_puesto;
      
      const isVerificado = proyecto.estatus && typeof proyecto.estatus === 'string' && proyecto.estatus.trim().toUpperCase() === 'VERIFICADO';
      
      if (isVerificado && (!hasResponsable || !hasAutoriza)) {
        setPdfWarningOpen(true);
        setLoading(false);
        return;
      }

      const response = await axios.get(`/proyectos/${id}/ficha-descriptiva/pdf`, { responseType: 'blob' });
      const blob = new Blob([response.data], { type: 'application/pdf' });
      const url = window.URL.createObjectURL(blob);
      setSelectedPdfUrl(`${url}#toolbar=0&navpanes=0&scrollbar=0`);
      setPdfModalOpen(true);
    } catch (error) {
      console.error('Error al generar la vista preliminar:', error);
      setSnackbarMessage('Error al cargar la vista preliminar.');
      setSnackbarOpen(true);
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteClick = (id) => {
    setProyectoToDelete(id);
    setDeleteModalOpen(true);
  };

  const confirmDelete = async () => {
    try {
      await axios.delete(`/proyectos/${proyectoToDelete}`);
      setSnackbarMessage('Proyecto eliminado correctamente.');
      setSnackbarOpen(true);
      fetchProyectos();
    } catch (error) {
      console.error('Error deleting proyecto:', error);
      setSnackbarMessage(error.response?.data?.message || 'Error al eliminar el proyecto.');
      setSnackbarOpen(true);
    } finally {
      setDeleteModalOpen(false);
      setProyectoToDelete(null);
    }
  };

  const handleVerificarClick = (id) => {
    setProyectoToVerificar(id);
    setVerificarModalOpen(true);
  };

  const confirmVerificar = async () => {
    try {
      await axios.put(`/proyectos/${proyectoToVerificar}/verificar`);
      setSnackbarMessage('Proyecto verificado correctamente.');
      setSnackbarOpen(true);
      fetchProyectos();
    } catch (error) {
      console.error('Error verificando proyecto:', error);
      setSnackbarMessage(error.response?.data?.message || 'Error al verificar el proyecto.');
      setSnackbarOpen(true);
    } finally {
      setVerificarModalOpen(false);
      setProyectoToVerificar(null);
    }
  };

  const handleCambiarEstatusClick = (id) => {
    setProyectoToCambiarEstatus(id);
    setNuevoEstatus('');
    setCambiarEstatusModalOpen(true);
  };

  const confirmCambiarEstatus = async () => {
    if (!nuevoEstatus) {
      setSnackbarMessage('Selecciona un estatus.');
      setSnackbarOpen(true);
      return;
    }
    try {
      await axios.put(`/proyectos/${proyectoToCambiarEstatus}/cambiar-estatus`, { estatus: nuevoEstatus });
      setSnackbarMessage('Estatus cambiado correctamente.');
      setSnackbarOpen(true);
      fetchProyectos();
    } catch (error) {
      console.error('Error cambiando estatus:', error);
      setSnackbarMessage(error.response?.data?.message || 'Error al cambiar estatus.');
      setSnackbarOpen(true);
    } finally {
      setCambiarEstatusModalOpen(false);
      setProyectoToCambiarEstatus(null);
      setNuevoEstatus('');
    }
  };

  const handleAlinearClick = (id) => {
    setProyectoToAlinear(id);
    setAlinearModalOpen(true);
  };


  const filteredProyectos = proyectos.filter((proyecto) => {
    const matchesSearch = Object.values(proyecto).some((value) =>
      value ? value.toString().toLowerCase().includes(search.toLowerCase()) : false
    );
    const matchesEstatus = estatusFilter === 'Todos' || formatEstatus(proyecto.estatus) === estatusFilter;
    return matchesSearch && matchesEstatus;
  });

  const handleRequestSort = (property) => {
    const isAsc = orderBy === property && order === 'asc';
    setOrder(isAsc ? 'desc' : 'asc');
    setOrderBy(property);
  };

  const sortedProyectos = [...filteredProyectos].sort((a, b) => {
    if (!orderBy) return 0;
    
    const isNumericColumn = ['urg', 'ro', 'pg', 'sp', 'py'].includes(orderBy);
    let valA = a[orderBy];
    let valB = b[orderBy];

    if (isNumericColumn) {
      const numA = parseInt(valA, 10);
      const numB = parseInt(valB, 10);
      if (!isNaN(numA) && !isNaN(numB)) {
        if (numA < numB) return order === 'asc' ? -1 : 1;
        if (numA > numB) return order === 'asc' ? 1 : -1;
        return 0;
      }
    }

    valA = (valA || '').toString().toLowerCase();
    valB = (valB || '').toString().toLowerCase();

    if (valA < valB) return order === 'asc' ? -1 : 1;
    if (valA > valB) return order === 'asc' ? 1 : -1;
    return 0;
  });

  const handleDownloadAvanceMensual = async (proyectoId) => {
    try {
      const response = await axios.get(`/reportes/excel/avance-mensual/${proyectoId}`, {
        responseType: 'blob',
      });
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `Avance_Mensual_Proyecto_${proyectoId}.xlsx`);
      document.body.appendChild(link);
      link.click();
      link.remove();
    } catch (error) {
      console.error('Error downloading Excel:', error);
      alert('Error al descargar el archivo Excel.');
    }
  };

  const isDetailView = fichaDescriptivaOpen || seguimientoOpen;

  return (
    <Container 
      maxWidth={false} 
      disableGutters={isDetailView} 
      sx={isDetailView ? { p: '0 !important', m: '0 !important', bgcolor: '#F6F4F0', minHeight: '100%' } : {}}
    >
      {!isDetailView ? (
        <>
          <Stack direction="row" alignItems="center" justifyContent="space-between" mb={5}>
        <Typography variant="h4" gutterBottom sx={{ display: 'flex', alignItems: 'center' }}>
          <Iconify icon="mdi:format-list-bulleted" sx={{ mr: 2, width: 32, height: 32 }} />
          Proyectos
        </Typography>
        {canCreate && (
          <Button
            variant="contained"
            startIcon={<Iconify icon="mdi:plus" />}
            onClick={() => setNuevoProyectoOpen(true)}
          >
            Agregar proyecto
          </Button>
        )}
      </Stack>

      <Card>
        <Box sx={{ p: 3, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <Box display="flex" alignItems="center">
            <Typography variant="body2" sx={{ mr: 1 }}>Mostrar</Typography>
            <TextField
              select
              size="small"
              value={rowsPerPage}
              onChange={handleChangeRowsPerPage}
              SelectProps={{ native: true }}
              sx={{ width: 70, mr: 1 }}
            >
              {[10, 25, 50].map((option) => (
                <option key={option} value={option}>
                  {option}
                </option>
              ))}
            </TextField>
            <Typography variant="body2">registros</Typography>
          </Box>
          <Box display="flex" alignItems="center">
            <Typography variant="body2" sx={{ mr: 1 }}>Buscar:</Typography>
            <TextField size="small" value={search} onChange={handleSearch} sx={{ mr: 2 }} />
            
            <Typography variant="body2" sx={{ mr: 1 }}>Estatus:</Typography>
            <TextField
              select
              size="small"
              value={estatusFilter}
              onChange={(e) => setEstatusFilter(e.target.value)}
              SelectProps={{ native: true }}
              sx={{ width: 140 }}
            >
              {uniqueEstatusOptions.map((option) => (
                <option key={option} value={option}>
                  {option}
                </option>
              ))}
            </TextField>
          </Box>
        </Box>

        <TableContainer sx={{ minWidth: 800 }}>
          <Table>
            <TableHead sx={{ backgroundColor: '#f4f6f8' }}>
              <TableRow>
                {TABLE_HEAD.map((headCell) => (
                  <TableCell
                    key={headCell.id}
                    align={headCell.alignRight ? 'right' : 'left'}
                    sx={{ fontSize: '0.8125rem' }} // Reduce 1 point from standard 0.875rem
                  >
                    {['urg', 'ro', 'pg', 'sp', 'py'].includes(headCell.id) ? (
                      <TableSortLabel
                        active={orderBy === headCell.id}
                        direction={orderBy === headCell.id ? order : 'asc'}
                        onClick={() => handleRequestSort(headCell.id)}
                      >
                        {headCell.label}
                      </TableSortLabel>
                    ) : (
                      headCell.label
                    )}
                  </TableCell>
                ))}
              </TableRow>
            </TableHead>
            <TableBody>
              {loading ? (
                <TableRow>
                  <TableCell colSpan={8} align="center" sx={{ fontSize: '0.8125rem' }}>
                    Cargando...
                  </TableCell>
                </TableRow>
              ) : sortedProyectos.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={8} align="center" sx={{ fontSize: '0.8125rem' }}>
                    No se encontraron registros
                  </TableCell>
                </TableRow>
              ) : (
                sortedProyectos
                  .slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage)
                  .map((row) => (
                    <TableRow hover key={row.proyecto_id} tabIndex={-1}>
                      <TableCell align="left" sx={{ fontSize: '0.8125rem' }}>{row.urg}</TableCell>
                      <TableCell align="left" sx={{ fontSize: '0.8125rem' }}>{row.ro}</TableCell>
                      <TableCell align="left" sx={{ fontSize: '0.8125rem' }}>{row.pg}</TableCell>
                      <TableCell align="left" sx={{ fontSize: '0.8125rem' }}>{row.sp}</TableCell>
                      <TableCell align="left" sx={{ fontSize: '0.8125rem' }}>{row.py}</TableCell>
                      <TableCell align="left" sx={{ fontSize: '0.8125rem' }}>{row.denominacion}</TableCell>
                      <TableCell align="left" sx={{ fontSize: '0.8125rem', textTransform: 'capitalize' }}>
                        {formatEstatus(row.estatus)}
                      </TableCell>
                      <TableCell align="right" sx={{ whiteSpace: 'nowrap', fontSize: '0.8125rem' }}>
                        <Tooltip title="Ficha Descriptiva">
                          <IconButton
                            color="secondary"
                            size="small"
                            onClick={() => handleOpenFichaDescriptiva(row.proyecto_id, row.estatus)}
                          >
                            <Iconify icon="mdi:file-document-outline" width={20} height={20} />
                          </IconButton>
                        </Tooltip>
                        <Tooltip title="Vista Preliminar">
                          <IconButton color="error" size="small" onClick={() => handleOpenPdf(row.proyecto_id)}>
                            <Iconify icon="mdi:file-pdf-box" width={20} height={20} />
                          </IconButton>
                        </Tooltip>
                        {hasRole('Administrador') && row.estatus === 'Cerrada' && (
                          <Tooltip title="Verificar Proyecto">
                            <IconButton color="success" size="small" onClick={() => handleVerificarClick(row.proyecto_id)}>
                              <Iconify icon="mdi:check-decagram" width={20} height={20} />
                            </IconButton>
                          </Tooltip>
                        )}
                        {hasRole('Administrador') && (
                          <Tooltip title="Cambiar de Estatus">
                            <IconButton color="warning" size="small" onClick={() => handleCambiarEstatusClick(row.proyecto_id)}>
                              <Iconify icon="mdi:swap-horizontal" width={20} height={20} />
                            </IconButton>
                          </Tooltip>
                        )}
                        {hasRole('Administrador') && (
                          <Tooltip title="Alinear proyecto">
                            <IconButton color="primary" size="small" onClick={() => handleAlinearClick(row.proyecto_id)}>
                              <Iconify icon="mdi:bullseye-arrow" width={20} height={20} />
                            </IconButton>
                          </Tooltip>
                        )}
                        {isSeguimientoActive && (
                          <Tooltip title="Seguimiento">
                            <IconButton color="info" size="small" onClick={() => handleOpenSeguimiento(row.proyecto_id)}>
                              <Iconify icon="mdi:chart-timeline" width={20} height={20} />
                            </IconButton>
                          </Tooltip>
                        )}
                        {hasRole('Administrador') && (
                          <Tooltip title="Eliminar Proyecto">
                            <IconButton color="error" size="small" onClick={() => handleDeleteClick(row.proyecto_id)}>
                              <Iconify icon="mdi:delete" width={20} height={20} />
                            </IconButton>
                          </Tooltip>
                        )}
                      </TableCell>
                    </TableRow>
                  ))
              )}
            </TableBody>
          </Table>
        </TableContainer>

        <TablePagination
          rowsPerPageOptions={[]} // hidden because we have custom selector
          component="div"
          count={sortedProyectos.length}
          rowsPerPage={rowsPerPage}
          page={page}
          onPageChange={handleChangePage}
          onRowsPerPageChange={handleChangeRowsPerPage}
          labelDisplayedRows={({ from, to, count }) => `Mostrando registros del ${from} al ${to} de un total de ${count} registros`}
        />
      </Card>
      
      <ConfirmDestructiveDeleteDialog
        open={deleteModalOpen}
        onClose={() => setDeleteModalOpen(false)}
        onConfirm={confirmDelete}
        title="Eliminar Proyecto Permanentemente"
        message="¿Estás completamente seguro de que deseas eliminar este proyecto/ficha descriptiva? Esta acción borrará TODO el historial: metas, bitácoras, acciones sustantivas y cualquier otro dato asociado."
      />
        </>
      ) : fichaDescriptivaOpen ? (
        <FichaDescriptivaModal
          open={fichaDescriptivaOpen}
          onClose={() => {
            setFichaDescriptivaOpen(false);
            fetchProyectos();
          }}
          proyectoId={selectedProyectoId}
        />
      ) : (
        <SeguimientoModal 
          open={seguimientoOpen} 
          onClose={() => {
            setSeguimientoOpen(false);
            fetchProyectos();
          }} 
          proyectoId={selectedProyectoId} 
        />
      )}

      <GraficasModal open={graficasOpen} onClose={() => setGraficasOpen(false)} />
      <NuevoProyectoModal
        open={nuevoProyectoOpen}
        onClose={() => setNuevoProyectoOpen(false)}
        onSuccess={() => {
          fetchProyectos();
          setNuevoProyectoOpen(false);
        }}
      />
      <Dialog
        open={snackbarOpen}
        onClose={() => setSnackbarOpen(false)}
        maxWidth="xs"
        fullWidth
        PaperProps={{
          sx: {
            borderRadius: 2,
            boxShadow: '0 8px 24px rgba(0,0,0,0.15)',
          }
        }}
      >
        <DialogTitle sx={{ display: 'flex', alignItems: 'center', color: snackbarMessage.includes('correctamente') ? '#2e7d32' : '#B3372E', fontWeight: 'bold' }}>
          <Iconify icon={snackbarMessage.includes('correctamente') ? "mdi:check-circle-outline" : "mdi:alert-circle-outline"} sx={{ mr: 1, width: 28, height: 28 }} />
          {snackbarMessage.includes('correctamente') || snackbarMessage.includes('exitosamente') ? 'Éxito' : 'Aviso del Sistema'}
        </DialogTitle>
        <DialogContent dividers>
          <Typography variant="body1" sx={{ color: 'text.secondary', mt: 1 }}>
            {snackbarMessage}
          </Typography>
        </DialogContent>
        <DialogActions sx={{ p: 2 }}>
          <Button 
            onClick={() => setSnackbarOpen(false)} 
            variant="contained" 
            sx={{ bgcolor: '#0d2f66', '&:hover': { bgcolor: '#0a244d' } }}
          >
            Entendido
          </Button>
        </DialogActions>
      </Dialog>

      <Dialog 
        open={pdfModalOpen} 
        onClose={() => setPdfModalOpen(false)} 
        maxWidth="lg" 
        fullWidth 
        PaperProps={{ sx: { maxWidth: '90vw', height: '90vh' } }}
      >
        <DialogTitle sx={{ m: 0, p: 2, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          Vista Preliminar
          <Box>
            <Tooltip title="Imprimir">
              <IconButton onClick={handlePrintPdf} sx={{ mr: 1, color: 'primary.main' }}>
                <Iconify icon="mdi:printer" />
              </IconButton>
            </Tooltip>
            <IconButton
              onClick={() => setPdfModalOpen(false)}
              sx={{ color: (theme) => theme.palette.grey[500] }}
            >
              <Iconify icon="eva:close-fill" />
            </IconButton>
          </Box>
        </DialogTitle>
        <DialogContent dividers sx={{ p: 0, overflow: 'hidden' }}>
          {selectedPdfUrl && (
            <iframe 
              src={selectedPdfUrl} 
              width="100%" 
              height="100%" 
              style={{ border: 'none' }}
              title="Vista Preliminar PDF"
            />
          )}
        </DialogContent>
      </Dialog>

      <Dialog
        open={verificarModalOpen}
        onClose={() => setVerificarModalOpen(false)}
        maxWidth="sm"
        fullWidth
        PaperProps={{ sx: { borderRadius: 2 } }}
      >
        <DialogTitle sx={{ backgroundColor: '#2e7d32', color: 'white' }}>Verificar Proyecto</DialogTitle>
        <DialogContent sx={{ mt: 2 }}>
          <Typography>
            ¿Estás seguro de que deseas cambiar el estatus de este proyecto a <strong>Verificado</strong>? <br/><br/>
            Esto indicará que el proyecto está completo y listo para firma, enviando un correo a los responsables.
          </Typography>
        </DialogContent>
        <DialogActions sx={{ p: 2, backgroundColor: '#f5f5f5' }}>
          <Button onClick={() => setVerificarModalOpen(false)} color="inherit" variant="outlined">Cancelar</Button>
          <Button onClick={confirmVerificar} color="success" variant="contained">Confirmar Verificación</Button>
        </DialogActions>
      </Dialog>

      <Dialog
        open={cambiarEstatusModalOpen}
        onClose={() => setCambiarEstatusModalOpen(false)}
        maxWidth="sm"
        fullWidth
        PaperProps={{ sx: { borderRadius: 2 } }}
      >
        <DialogTitle sx={{ backgroundColor: '#ed6c02', color: 'white' }}>Cambiar de estatus</DialogTitle>
        <DialogContent sx={{ mt: 2, pb: 1 }}>
          <Typography sx={{ mb: 2 }}>
            Selecciona el nuevo estatus para este proyecto.
          </Typography>
          <TextField
            select
            fullWidth
            label="Estatus"
            value={nuevoEstatus}
            onChange={(e) => setNuevoEstatus(e.target.value)}
            SelectProps={{ native: true }}
          >
            <option value="" disabled>Selecciona una opción</option>
            {['Captura', 'Validacion', 'Verificado', 'Cerrada'].map((estatus) => (
              <option key={estatus} value={estatus}>{estatus}</option>
            ))}
          </TextField>
        </DialogContent>
        <DialogActions sx={{ p: 2, backgroundColor: '#f5f5f5' }}>
          <Button onClick={() => setCambiarEstatusModalOpen(false)} color="inherit" variant="outlined">Cancelar</Button>
          <Button onClick={confirmCambiarEstatus} color="warning" variant="contained">Guardar</Button>
        </DialogActions>
      </Dialog>

      {/* Warning Modal for PDF */}
      <Dialog open={pdfWarningOpen} onClose={() => setPdfWarningOpen(false)} maxWidth="xs" fullWidth>
        <Box sx={{ p: 3, textAlign: 'center' }}>
          <Iconify icon="mdi:alert-circle-outline" sx={{ width: 60, height: 60, color: 'warning.main', mb: 2 }} />
          <Typography variant="h6" gutterBottom>
            Información Incompleta
          </Typography>
          <Typography variant="body2" color="text.secondary">
            Para generar el archivo PDF de la Ficha Descriptiva, asegúrate de haber capturado la información de <strong>quien elabora</strong> y <strong>quien autoriza</strong>, así como sus <strong>cargos/puestos</strong> correspondientes.
          </Typography>
        </Box>
        <DialogActions sx={{ justifyContent: 'center', pb: 3 }}>
          <Button variant="contained" onClick={() => setPdfWarningOpen(false)}>
            Entendido
          </Button>
        </DialogActions>
      </Dialog>

      <ModalAlinearProyecto 
        open={alinearModalOpen}
        onClose={() => {
          setAlinearModalOpen(false);
          setProyectoToAlinear(null);
        }}
        proyectoId={proyectoToAlinear}
        onSaved={() => {
          setSnackbarMessage('Alineación guardada exitosamente.');
          setSnackbarOpen(true);
        }}
      />
    </Container>
  );
}
