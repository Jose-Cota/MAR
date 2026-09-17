import { useState, useEffect } from 'react';
import {
  Container,
  Typography,
  Card,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Checkbox,
  Stack,
  Button,
  Alert,
  Snackbar,
  Box
} from '@mui/material';
import Iconify from '../../../components/Iconify';
import { rolesService } from '../../../services/rolesService';

export default function RolesPage() {
  const [roles, setRoles] = useState([]);
  const [permissions, setPermissions] = useState([]);
  const [rolePermissions, setRolePermissions] = useState({}); // { roleId: [permName, permName] }
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [snackbar, setSnackbar] = useState({ open: false, message: '', severity: 'success' });

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    setLoading(true);
    try {
      const data = await rolesService.getRolesAndPermissions();
      // data: { roles: [...], permissions: [...] }
      setRoles(data.roles);
      setPermissions(data.permissions);
      
      const mapping = {};
      data.roles.forEach(role => {
        mapping[role.id] = role.permissions.map(p => p.name);
      });
      setRolePermissions(mapping);
    } catch (error) {
      console.error('Error fetching roles:', error);
      showSnackbar('Error al cargar roles y permisos', 'error');
    } finally {
      setLoading(false);
    }
  };

  const handleToggle = (roleId, permissionName) => {
    setRolePermissions(prev => {
      const current = prev[roleId] || [];
      const updated = current.includes(permissionName)
        ? current.filter(p => p !== permissionName)
        : [...current, permissionName];
      return { ...prev, [roleId]: updated };
    });
  };

  const handleSave = async () => {
    setSaving(true);
    try {
      // Guardar secuencialmente (o en Promise.all)
      await Promise.all(
        roles.map(role => 
          rolesService.updateRolePermissions(role.id, rolePermissions[role.id] || [])
        )
      );
      showSnackbar('Permisos guardados correctamente');
      fetchData();
    } catch (error) {
      console.error('Error saving permissions:', error);
      showSnackbar('Error al guardar los permisos', 'error');
    } finally {
      setSaving(false);
    }
  };

  const showSnackbar = (message, severity = 'success') => {
    setSnackbar({ open: true, message, severity });
  };

  const handleCloseSnackbar = () => {
    setSnackbar({ ...snackbar, open: false });
  };

  return (
    <Container maxWidth={false}>
      <Stack direction="row" alignItems="center" justifyContent="space-between" mb={5}>
        <Typography variant="h4" gutterBottom sx={{ display: 'flex', alignItems: 'center' }}>
          <Iconify icon="mdi:shield-key-outline" sx={{ mr: 2, width: 32, height: 32 }} />
          Roles y Permisos
        </Typography>
        <Button
          variant="contained"
          startIcon={<Iconify icon="mdi:content-save" />}
          onClick={handleSave}
          disabled={loading || saving}
        >
          Guardar Cambios
        </Button>
      </Stack>

      <Card>
        <TableContainer sx={{ minWidth: 800 }}>
          <Table size="small">
            <TableHead sx={{ backgroundColor: '#f4f6f8' }}>
              <TableRow>
                <TableCell>Permiso / Módulo</TableCell>
                {roles && roles.map((role) => (
                  <TableCell key={role.id} align="center">
                    <Typography variant="subtitle2">{role.name}</Typography>
                  </TableCell>
                ))}
              </TableRow>
            </TableHead>
            <TableBody>
              {loading ? (
                <TableRow>
                  <TableCell colSpan={(roles?.length || 0) + 1} align="center" sx={{ py: 3 }}>
                    Cargando...
                  </TableCell>
                </TableRow>
              ) : (
                permissions.map((perm) => (
                  <TableRow hover key={perm.id}>
                    <TableCell sx={{ fontWeight: 'bold', py: 0.5 }}>
                      {formatPermissionName(perm.name)}
                    </TableCell>
                    {roles.map((role) => (
                      <TableCell key={`${role.id}-${perm.id}`} align="center" sx={{ py: 0.5 }}>
                        <Checkbox 
                          size="small"
                          checked={(rolePermissions[role.id] || []).includes(perm.name)}
                          onChange={() => handleToggle(role.id, perm.name)}
                          disabled={role.name === 'Administrador'} // Admin siempre tiene todo (opcional)
                        />
                      </TableCell>
                    ))}
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </TableContainer>
      </Card>

      <Snackbar 
        open={snackbar.open} 
        autoHideDuration={6000} 
        onClose={handleCloseSnackbar}
        anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
      >
        <Alert onClose={handleCloseSnackbar} severity={snackbar.severity} sx={{ width: '100%' }}>
          {snackbar.message}
        </Alert>
      </Snackbar>
    </Container>
  );
}

function formatPermissionName(name) {
  // Convert 'ver_tablero' to 'Ver Tablero'
  return name.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
}
