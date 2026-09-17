import { useState, useEffect } from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  TextField,
  MenuItem,
  Checkbox,
  Typography,
  Divider,
  Stack,
  Box,
  Snackbar,
  Alert,
} from '@mui/material';
import Iconify from '../../../components/Iconify';
import axios from '../../../utils/axios';
import { usuariosService } from '../../../services/usuariosService';
import ValidationModal from '../../../components/ui/ValidationModal';

const ROLES_CONFIG = {
  Administrador: { color: '#7B3FA0', bg: '#F3E8FF', border: '#C084FC' },
  Validador:     { color: '#1F4E79', bg: '#EFF6FF', border: '#93C5FD' },
  Capturador:    { color: '#2E7D5B', bg: '#F0FDF4', border: '#86EFAC' },
  Lector:        { color: '#92400E', bg: '#FFFBEB', border: '#FCD34D' },
};

const TODOS_LOS_ROLES = Object.keys(ROLES_CONFIG);

export default function UsuarioFormModal({ open, onClose, usuario, onSuccess }) {
  const isEdit = Boolean(usuario);
  const [unidades, setUnidades] = useState([]);
  const [responsables, setResponsables] = useState([]);

  const [formData, setFormData] = useState({
    nombre: '',
    apellido_paterno: '',
    apellido_materno: '',
    correo: '',
    area_ids: [],
    usuario: '',
    password: '',
    password_confirmation: '',
    role: 'Capturador',
  });
  const [rolesSeleccionados, setRolesSeleccionados] = useState(['Capturador']);
  const [responsablesSeleccionados, setResponsablesSeleccionados] = useState([]);

  const [validationErrors, setValidationErrors] = useState([]);
  const [errorModalOpen, setErrorModalOpen] = useState(false);
  const [apiErrorOpen, setApiErrorOpen] = useState(false);
  const [apiErrorMessage, setApiErrorMessage] = useState('');

  useEffect(() => {
    fetchUnidades();
  }, []);

  useEffect(() => {
    if (usuario && unidades.length > 0) {
      const rolPrincipal = usuario.role || 'Capturador';
      
      const mappedAreaIds = [];
      if (Array.isArray(usuario.unidades_responsables) && usuario.unidades_responsables.length > 0) {
        usuario.unidades_responsables.forEach(ur => {
          const latestUr = unidades.find(u => u.numero === ur.numero);
          if (latestUr && !mappedAreaIds.includes(latestUr.unidad_responsable_gasto_id)) {
            mappedAreaIds.push(latestUr.unidad_responsable_gasto_id);
          }
        });
      } else if (usuario.area_id) {
        const legacyUr = unidades.find(u => u.unidad_responsable_gasto_id === usuario.area_id);
        if (legacyUr && !mappedAreaIds.includes(legacyUr.unidad_responsable_gasto_id)) {
            mappedAreaIds.push(legacyUr.unidad_responsable_gasto_id);
        } else {
            mappedAreaIds.push(usuario.area_id);
        }
      }

      setFormData({
        nombre: usuario.nombre || '',
        apellido_paterno: usuario.apellido_paterno || '',
        apellido_materno: usuario.apellido_materno || '',
        correo: usuario.correo || '',
        area_ids: mappedAreaIds,
        usuario: usuario.usuario || '',
        password: '',
        password_confirmation: '',
        role: rolPrincipal,
      });

      const roles = Array.isArray(usuario.roles)
        ? usuario.roles.map(r => typeof r === 'string' ? r : r.name).filter(Boolean)
        : [rolPrincipal];
      setRolesSeleccionados(roles.length > 0 ? roles : [rolPrincipal]);
      
      if (mappedAreaIds.length > 0) {
        fetchResponsables(mappedAreaIds);
      } else {
        setResponsables([]);
      }

      const rosIniciales = Array.isArray(usuario.responsables_operativos)
        ? usuario.responsables_operativos.map(r => r.responsable_operativo_id ?? r)
        : (usuario.responsable_operativo_id ? [usuario.responsable_operativo_id] : []);
      setResponsablesSeleccionados(rosIniciales);
    } else if (!usuario) {
      setFormData(prev => ({ ...prev, area_ids: [] }));
      setRolesSeleccionados(['Capturador']);
      setResponsablesSeleccionados([]);
      setResponsables([]);
    }
  }, [usuario, unidades]);

  const fetchUnidades = async () => {
    try {
      const res = await axios.get('/unidades-responsables');
      setUnidades(res.data);
    } catch (err) {
      console.error('Error fetching unidades:', err);
    }
  };

  const fetchResponsables = async (urgIds) => {
    if (!urgIds || urgIds.length === 0) { setResponsables([]); return; }
    try {
      const params = { unidad_responsable_gasto_id: urgIds.join(',') };
      const res = await axios.get('/responsables-operativos', { params });
      setResponsables(Array.isArray(res.data) ? res.data : (res.data?.data || []));
    } catch (err) {
      console.error('Error fetching responsables:', err);
      setResponsables([]);
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
    if (name === 'area_ids') {
      setResponsablesSeleccionados([]);
      fetchResponsables(value);
    }
  };

  const handleToggleResponsable = (id) => {
    setResponsablesSeleccionados(prev =>
      prev.includes(id) ? prev.filter(r => r !== id) : [...prev, id]
    );
  };

  const handleToggleRol = (rol) => {
    setRolesSeleccionados(prev => {
      if (prev.includes(rol)) {
        if (prev.length === 1) return prev;
        return prev.filter(r => r !== rol);
      }
      return [...prev, rol];
    });
  };

  const handleSubmit = async () => {
    const errors = [];
    if (!formData.nombre.trim()) errors.push('Nombre(s)');
    if (!formData.apellido_paterno.trim()) errors.push('Apellido Paterno');
    if (!formData.area_ids || formData.area_ids.length === 0) errors.push('Unidad Responsable');
    if (!formData.usuario.trim()) errors.push('Nombre de Usuario');
    if (!isEdit && !formData.password) errors.push('Contraseña (obligatoria para nuevos usuarios)');
    if (formData.password && formData.password !== formData.password_confirmation) {
      errors.push('Las contraseñas no coinciden');
    }

    if (errors.length > 0) {
      setValidationErrors(errors);
      setErrorModalOpen(true);
      return;
    }

    try {
      const payload = {
        ...formData,
        roles_adicionales: rolesSeleccionados,
        responsables_seleccionados: responsablesSeleccionados,
      };
      if (isEdit) {
        await usuariosService.updateUsuario(usuario.usuario_poa_id, payload);
      } else {
        await usuariosService.createUsuario(payload);
      }
      if (onSuccess) onSuccess();
    } catch (err) {
      console.error('Error saving user:', err);
      
      let errorMsg = 'Error inesperado al guardar el usuario.';
      if (err.response?.data?.errors) {
        const errorObj = err.response.data.errors;
        errorMsg = Object.values(errorObj).flat().join(', ');
      } else if (err.response?.data?.message) {
        errorMsg = err.response.data.message;
      }
      
      setApiErrorMessage(errorMsg);
      setApiErrorOpen(true);
    }
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="lg" fullWidth>
      <DialogTitle sx={{ borderBottom: '1px solid', borderColor: 'divider', pb: 1.5, display: 'flex', alignItems: 'center', gap: 1 }}>
        <Iconify icon={isEdit ? 'mdi:account-edit-outline' : 'mdi:account-plus-outline'} width={22} sx={{ color: 'primary.main' }} />
        {isEdit ? 'Editar Usuario' : 'Nuevo Usuario'}
      </DialogTitle>

      <DialogContent sx={{ pt: 2.5 }}>
        <Stack spacing={3}>

          {/* Datos generales */}
          <Box>
            <SectionLabel>Datos generales</SectionLabel>
            <Stack spacing={2}>
              <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
                <TextField size="small" fullWidth label="Nombre(s)" name="nombre"
                  value={formData.nombre} onChange={handleChange} required />
                <TextField size="small" fullWidth label="Apellido Paterno" name="apellido_paterno"
                  value={formData.apellido_paterno} onChange={handleChange} required />
                <TextField size="small" fullWidth label="Apellido Materno" name="apellido_materno"
                  value={formData.apellido_materno} onChange={handleChange} />
              </Stack>
              
              <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
                <TextField size="small" sx={{ flex: 4 }} label="Correo electrónico" name="correo" type="email"
                  value={formData.correo} onChange={handleChange} />
                
                <TextField size="small" sx={{ flex: 6 }} select SelectProps={{ multiple: true }} label="Unidad Responsable (Área)"
                  name="area_ids" value={formData.area_ids} onChange={handleChange} required>
                  {unidades.map((u) => (
                    <MenuItem key={u.unidad_responsable_gasto_id} value={u.unidad_responsable_gasto_id}
                      sx={{ whiteSpace: 'normal', fontSize: '0.82rem' }}>
                      <Checkbox checked={formData.area_ids.indexOf(u.unidad_responsable_gasto_id) > -1} size="small" sx={{p:0, mr:1}} />
                      <Box component="span" sx={{ fontWeight: 700, mr: 0.8, color: 'primary.main' }}>
                        {u.numero}
                      </Box>
                      {u.nombre}
                    </MenuItem>
                  ))}
                </TextField>
              </Stack>

              {/* ROs en grid de 4 por renglón */}
              <Box
                component="fieldset"
                sx={{
                  border: '1px solid',
                  borderColor: formData.area_ids && formData.area_ids.length > 0 ? 'divider' : 'action.disabledBackground',
                  borderRadius: 1, p: 0, m: 0,
                  '& legend': {
                    px: 1, ml: 1,
                    fontSize: '0.68rem', fontWeight: 600,
                    color: formData.area_ids && formData.area_ids.length > 0 ? 'text.secondary' : 'action.disabled',
                    letterSpacing: '0.5px',
                  },
                }}
              >
                <legend>Áreas internas · Responsable Operativo</legend>
                {!formData.area_ids || formData.area_ids.length === 0 ? (
                  <Box sx={{ px: 2, py: 1.2, display: 'flex', alignItems: 'center', gap: 1 }}>
                    <Iconify icon="mdi:information-outline" width={14} sx={{ color: 'action.disabled' }} />
                    <Typography variant="caption" sx={{ color: 'action.disabled', fontStyle: 'italic' }}>
                      Selecciona una Unidad Responsable para ver sus áreas internas.
                    </Typography>
                  </Box>
                ) : responsables.length === 0 ? (
                  <Box sx={{ px: 2, py: 1.2 }}>
                    <Typography variant="caption" sx={{ color: 'text.secondary', fontStyle: 'italic' }}>
                      Esta UR no tiene áreas internas registradas.
                    </Typography>
                  </Box>
                ) : (
                  <Box sx={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)' }}>
                    {responsables.map((ro, idx) => {
                      const seleccionado = responsablesSeleccionados.includes(ro.responsable_operativo_id);
                      const col = idx % 4;
                      const row = Math.floor(idx / 4);
                      const totalRows = Math.ceil(responsables.length / 4);
                      const isLastRow = row === totalRows - 1;
                      const isLastCol = col === 3 || idx === responsables.length - 1;
                      return (
                        <Box
                          key={ro.responsable_operativo_id}
                          onClick={() => handleToggleResponsable(ro.responsable_operativo_id)}
                          sx={{
                            display: 'flex', alignItems: 'flex-start', gap: 0.5,
                            px: 1, py: 0.6, cursor: 'pointer',
                            bgcolor: seleccionado ? 'primary.lighter' : 'transparent',
                            borderRight: !isLastCol ? '1px solid' : 'none',
                            borderBottom: !isLastRow ? '1px solid' : 'none',
                            borderColor: 'divider',
                            transition: 'background .15s',
                            '&:hover': { bgcolor: 'action.hover' },
                            height: '100%',
                          }}
                        >
                          <Checkbox checked={seleccionado} size="small" sx={{ p: 0, pt: 0.3, flexShrink: 0 }} />
                          <Typography variant="body2" sx={{
                            fontSize: '0.7rem',
                            fontWeight: seleccionado ? 700 : 400,
                            color: seleccionado ? 'primary.main' : 'text.secondary',
                            userSelect: 'none',
                            lineHeight: 1.2,
                            pt: 0.4,
                          }}>
                            <Box component="span" sx={{ fontWeight: 700, mr: 0.4, color: 'text.disabled' }}>
                              {ro.urg_numero ? `${ro.urg_numero}.${ro.numero}` : ro.numero}
                            </Box>
                            {ro.nombre}
                          </Typography>
                        </Box>
                      );
                    })}
                  </Box>
                )}
              </Box>
            </Stack>
          </Box>

          {/* Credenciales */}
          <Box>
            <SectionLabel>Credenciales de acceso</SectionLabel>
            <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
              <TextField size="small" fullWidth label="Usuario (Login)" name="usuario"
                value={formData.usuario} onChange={handleChange} required />
              <TextField size="small" fullWidth type="password" name="password"
                label={isEdit ? "Contraseña (opcional)" : "Contraseña"}
                placeholder={isEdit ? "Dejar en blanco" : ""}
                value={formData.password} onChange={handleChange} required={!isEdit} />
              <TextField size="small" fullWidth type="password" name="password_confirmation"
                label="Confirmar contraseña"
                placeholder={isEdit ? "Dejar en blanco" : ""}
                value={formData.password_confirmation} onChange={handleChange} required={!isEdit || !!formData.password} />
            </Stack>
          </Box>

          {/* Roles */}
          <Box>
            <SectionLabel>Roles del usuario</SectionLabel>
            <Box
              component="fieldset"
              sx={{
                border: '1px solid', borderColor: 'divider',
                borderRadius: 1, p: 0, m: 0,
                '& legend': {
                  px: 1, ml: 1, fontSize: '0.68rem', fontWeight: 600,
                  color: 'text.secondary', letterSpacing: '0.5px',
                },
              }}
            >
              <legend>Selecciona uno o más roles</legend>
              <Box sx={{ display: 'flex' }}>
                {TODOS_LOS_ROLES.map((rol, idx) => {
                  const cfg = ROLES_CONFIG[rol];
                  const seleccionado = rolesSeleccionados.includes(rol);
                  return (
                    <Box
                      key={rol}
                      onClick={() => handleToggleRol(rol)}
                      sx={{
                        display: 'flex', alignItems: 'center', gap: 0.5,
                        px: 2, py: 1,
                        cursor: 'pointer',
                        bgcolor: seleccionado ? cfg.bg : 'transparent',
                        borderRight: idx < TODOS_LOS_ROLES.length - 1 ? '1px solid' : 'none',
                        borderColor: 'divider',
                        transition: 'background .15s',
                        '&:hover': { bgcolor: cfg.bg },
                        flex: '1 1 0',
                      }}
                    >
                      <Checkbox checked={seleccionado} size="small"
                        sx={{
                          p: 0.2,
                          color: cfg.border,
                          '&.Mui-checked': { color: cfg.color },
                        }}
                      />
                      <Typography variant="body2" sx={{
                        fontSize: '0.8rem',
                        fontWeight: seleccionado ? 700 : 400,
                        color: seleccionado ? cfg.color : 'text.secondary',
                        userSelect: 'none', whiteSpace: 'nowrap',
                      }}>
                        {rol}
                      </Typography>
                    </Box>
                  );
                })}
              </Box>
            </Box>
          </Box>

        </Stack>
      </DialogContent>

      <DialogActions sx={{ borderTop: '1px solid', borderColor: 'divider', px: 3, py: 2 }}>
        <Button onClick={onClose} color="inherit">Cancelar</Button>
        <Button onClick={handleSubmit} variant="contained">
          {isEdit ? 'Guardar cambios' : 'Crear usuario'}
        </Button>
      </DialogActions>

      <ValidationModal open={errorModalOpen} onClose={() => setErrorModalOpen(false)} errors={validationErrors} />

      <Snackbar open={apiErrorOpen} autoHideDuration={6000} onClose={() => setApiErrorOpen(false)} anchorOrigin={{ vertical: 'bottom', horizontal: 'center' }}>
        <Alert onClose={() => setApiErrorOpen(false)} severity="error" sx={{ width: '100%', boxShadow: 3 }}>
          {apiErrorMessage}
        </Alert>
      </Snackbar>
    </Dialog>
  );
}

function SectionLabel({ children }) {
  return (
    <>
      <Typography variant="overline" color="text.disabled"
        sx={{ fontSize: '0.68rem', letterSpacing: '0.8px' }}>
        {children}
      </Typography>
      <Divider sx={{ mb: 2, mt: 0.5 }} />
    </>
  );
}
