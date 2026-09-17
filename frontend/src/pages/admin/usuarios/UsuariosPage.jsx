import { useState, useEffect } from 'react';
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
  Chip,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
} from '@mui/material';
import Iconify from '../../../components/Iconify';
import { usuariosService } from '../../../services/usuariosService';
import UsuarioFormModal from './UsuarioFormModal';
import PasswordChangeModal from './PasswordChangeModal';

const TABLE_HEAD = [
  { id: 'nombre', label: 'Nombre', alignRight: false, width: '15%' },
  { id: 'usuario', label: 'Usuario', alignRight: false, width: '12%' },
  { id: 'area_nombre', label: 'Área / URG', alignRight: false, width: '20%' },
  { id: 'ro_nombre', label: 'RO', alignRight: false, width: '25%' },
  { id: 'role', label: 'Rol', alignRight: false, width: '10%' },
  { id: 'activo', label: 'Estado', alignRight: false, width: '8%' },
  { id: 'acciones', label: 'Acciones', alignRight: true, width: '10%' },
];

export default function UsuariosPage() {
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(10);
  const [search, setSearch] = useState('');
  const [usuarios, setUsuarios] = useState([]);
  const [loading, setLoading] = useState(false);
  
  const [formOpen, setFormOpen] = useState(false);
  const [passwordOpen, setPasswordOpen] = useState(false);
  const [selectedUsuario, setSelectedUsuario] = useState(null);
  
  const [deleteOpen, setDeleteOpen] = useState(false);
  const [usuarioToDelete, setUsuarioToDelete] = useState(null);

  useEffect(() => {
    fetchUsuarios();
  }, []);

  const fetchUsuarios = async () => {
    setLoading(true);
    try {
      const data = await usuariosService.getUsuarios();
      setUsuarios(data);
    } catch (error) {
      console.error('Error fetching usuarios:', error);
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

  const handleOpenForm = (usuario = null) => {
    setSelectedUsuario(usuario);
    setFormOpen(true);
  };

  const handleOpenPassword = (usuario) => {
    setSelectedUsuario(usuario);
    setPasswordOpen(true);
  };

  const handleToggleActive = async (id) => {
    if (window.confirm('¿Está seguro de cambiar el estado de este usuario?')) {
      try {
        await usuariosService.toggleActive(id);
        fetchUsuarios();
      } catch (error) {
        console.error('Error toggling active status:', error);
      }
    }
  };

  const handleOpenDelete = (usuario) => {
    setUsuarioToDelete(usuario);
    setDeleteOpen(true);
  };

  const handleConfirmDelete = async () => {
    if (!usuarioToDelete) return;
    try {
      await usuariosService.forceDelete(usuarioToDelete.usuario_poa_id);
      fetchUsuarios();
    } catch (error) {
      console.error('Error deleting user:', error);
    } finally {
      setDeleteOpen(false);
      setUsuarioToDelete(null);
    }
  };

  const filteredUsuarios = usuarios.filter((usuario) => {
    const searchString = search.toLowerCase();
    return (
      (usuario.nombre && usuario.nombre.toLowerCase().includes(searchString)) ||
      (usuario.apellido_paterno && usuario.apellido_paterno.toLowerCase().includes(searchString)) ||
      (usuario.apellido_materno && usuario.apellido_materno.toLowerCase().includes(searchString)) ||
      (usuario.usuario && usuario.usuario.toLowerCase().includes(searchString)) ||
      (usuario.area_nombre && usuario.area_nombre.toLowerCase().includes(searchString)) ||
      (usuario.unidades_responsables && usuario.unidades_responsables.some(ur => ur.numero.toLowerCase().includes(searchString) || ur.nombre.toLowerCase().includes(searchString))) ||
      (usuario.responsables_operativos && usuario.responsables_operativos.some(ro => ro.numero.toLowerCase().includes(searchString) || ro.nombre.toLowerCase().includes(searchString)))
    );
  });

  return (
    <Container maxWidth={false}>
      <Stack direction="row" alignItems="center" justifyContent="space-between" mb={5}>
        <Typography variant="h4" gutterBottom sx={{ display: 'flex', alignItems: 'center' }}>
          <Iconify icon="mdi:account-group-outline" sx={{ mr: 2, width: 32, height: 32 }} />
          Usuarios y Permisos
        </Typography>
        <Button
          variant="contained"
          startIcon={<Iconify icon="mdi:plus" />}
          onClick={() => handleOpenForm()}
        >
          Nuevo Usuario
        </Button>
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
            <TextField size="small" value={search} onChange={handleSearch} />
          </Box>
        </Box>

        <TableContainer sx={{ width: '100%', overflowX: 'auto' }}>
          <Table size="small" sx={{ tableLayout: 'fixed', minWidth: 800 }}>
            <TableHead sx={{ backgroundColor: '#f4f6f8' }}>
              <TableRow>
                {TABLE_HEAD.map((headCell) => (
                  <TableCell
                    key={headCell.id}
                    align={headCell.alignRight ? 'right' : 'left'}
                    sx={{ width: headCell.width }}
                  >
                    <TableSortLabel hideSortIcon>
                      {headCell.label}
                    </TableSortLabel>
                  </TableCell>
                ))}
              </TableRow>
            </TableHead>
            <TableBody>
              {loading ? (
                <TableRow>
                  <TableCell colSpan={6} align="center">
                    Cargando...
                  </TableCell>
                </TableRow>
              ) : filteredUsuarios.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={6} align="center">
                    No se encontraron registros
                  </TableCell>
                </TableRow>
              ) : (
                filteredUsuarios
                  .slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage)
                  .map((row) => (
                    <TableRow hover key={row.usuario_poa_id} tabIndex={-1}>
                      <TableCell align="left">
                        {row.nombre} {row.apellido_paterno} {row.apellido_materno}
                      </TableCell>
                      <TableCell align="left">{row.usuario}</TableCell>
                      <TableCell align="left" sx={{ wordWrap: 'break-word', fontSize: '0.75rem' }}>
                        {row.unidades_responsables?.length > 0 ? (
                          <Stack direction="column" spacing={0.5} sx={{ maxHeight: 120, overflowY: 'auto' }}>
                            {row.unidades_responsables.map((ur) => (
                              <Typography key={ur.unidad_responsable_gasto_id} variant="body2" sx={{ fontSize: '0.75rem', lineHeight: 1.2 }}>
                                {ur.numero} - {ur.nombre}
                              </Typography>
                            ))}
                          </Stack>
                        ) : (
                          `${row.area_numero || ''} - ${row.area_nombre || ''}`
                        )}
                      </TableCell>
                      <TableCell align="left" sx={{ wordWrap: 'break-word', fontSize: '0.75rem' }}>
                        {row.responsables_operativos?.length > 0 ? (
                          <Stack direction="column" spacing={0.5} sx={{ maxHeight: 120, overflowY: 'auto' }}>
                            {row.responsables_operativos.map((ro) => (
                              <Typography key={ro.responsable_operativo_id} variant="body2" sx={{ fontSize: '0.75rem', lineHeight: 1.2 }}>
                                {ro.urg_numero ? ro.urg_numero + '.' : ''}{ro.numero} - {ro.nombre}
                              </Typography>
                            ))}
                          </Stack>
                        ) : (
                          <Typography variant="body2" color="text.secondary" sx={{ fontStyle: 'italic', fontSize: '0.7rem' }}>Sin RO</Typography>
                        )}
                      </TableCell>
                      <TableCell align="left">
                        <Stack direction="row" spacing={0.5} flexWrap="wrap">
                          {row.roles?.map((rolName) => (
                            <Chip key={rolName} label={rolName} size="small" variant="outlined" color="primary" sx={{ fontSize: '0.7rem', height: 20 }} />
                          ))}
                        </Stack>
                      </TableCell>
                      <TableCell align="left">
                        <Chip 
                          label={row.activo === 1 ? 'Activo' : 'Inactivo'} 
                          color={row.activo === 1 ? 'success' : 'error'} 
                          size="small" 
                          sx={{ fontSize: '0.7rem', height: 20 }}
                        />
                      </TableCell>
                      <TableCell align="right" sx={{ whiteSpace: 'nowrap' }}>
                        <Tooltip title="Editar">
                          <IconButton color="primary" size="small" onClick={() => handleOpenForm(row)}>
                            <Iconify icon="mdi:pencil-outline" width={20} height={20} />
                          </IconButton>
                        </Tooltip>
                        <Tooltip title="Cambiar Contraseña">
                          <IconButton color="warning" size="small" onClick={() => handleOpenPassword(row)}>
                            <Iconify icon="mdi:lock-reset" width={20} height={20} />
                          </IconButton>
                        </Tooltip>
                        <Tooltip title={row.activo === 1 ? "Dar de baja" : "Reactivar"}>
                          <IconButton 
                            color={row.activo === 1 ? "warning" : "success"} 
                            size="small" 
                            onClick={() => handleToggleActive(row.usuario_poa_id)}
                          >
                            <Iconify icon={row.activo === 1 ? "mdi:account-off-outline" : "mdi:account-check-outline"} width={20} height={20} />
                          </IconButton>
                        </Tooltip>
                        <Tooltip title="Eliminar definitivamente">
                          <IconButton color="error" size="small" onClick={() => handleOpenDelete(row)}>
                            <Iconify icon="mdi:trash-can-outline" width={20} height={20} />
                          </IconButton>
                        </Tooltip>
                      </TableCell>
                    </TableRow>
                  ))
              )}
            </TableBody>
          </Table>
        </TableContainer>

        <TablePagination
          rowsPerPageOptions={[]}
          component="div"
          count={filteredUsuarios.length}
          rowsPerPage={rowsPerPage}
          page={page}
          onPageChange={handleChangePage}
          onRowsPerPageChange={handleChangeRowsPerPage}
          labelDisplayedRows={({ from, to, count }) => `Mostrando registros del ${from} al ${to} de un total de ${count} registros`}
        />
      </Card>

      {formOpen && (
        <UsuarioFormModal
          open={formOpen}
          onClose={() => setFormOpen(false)}
          usuario={selectedUsuario}
          onSuccess={() => {
            fetchUsuarios();
            setFormOpen(false);
          }}
        />
      )}

      {passwordOpen && (
        <PasswordChangeModal
          open={passwordOpen}
          onClose={() => setPasswordOpen(false)}
          usuario={selectedUsuario}
          onSuccess={() => {
            setPasswordOpen(false);
          }}
        />
      )}

      {/* Modal de Confirmación para Eliminar */}
      <Dialog open={deleteOpen} onClose={() => setDeleteOpen(false)} maxWidth="xs" fullWidth>
        <DialogTitle sx={{ display: 'flex', alignItems: 'center', gap: 1, color: 'error.main' }}>
          <Iconify icon="mdi:alert-circle" width={24} height={24} />
          Eliminar Usuario
        </DialogTitle>
        <DialogContent>
          <Typography variant="body1" sx={{ mt: 1 }}>
            ¿Estás seguro de que deseas eliminar permanentemente al usuario <strong>{usuarioToDelete?.usuario}</strong>?
          </Typography>
          <Typography variant="body2" color="text.secondary" sx={{ mt: 2 }}>
            Esta acción no se puede deshacer y eliminará todos sus permisos y asignaciones.
          </Typography>
        </DialogContent>
        <DialogActions sx={{ px: 3, pb: 3 }}>
          <Button onClick={() => setDeleteOpen(false)} color="inherit">
            Cancelar
          </Button>
          <Button onClick={handleConfirmDelete} variant="contained" color="error">
            Sí, eliminar
          </Button>
        </DialogActions>
      </Dialog>
    </Container>
  );
}
