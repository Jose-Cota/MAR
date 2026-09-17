import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  Typography,
  List,
  ListItem,
  ListItemIcon,
  ListItemText,
  Badge,
  Box,
  Divider,
} from '@mui/material';
import Iconify from '../../../components/Iconify';

const LABELS = {
  unidades_responsables_gastos: 'Unidades Responsables (URGs)',
  responsables_operativos: 'Responsables Operativos (ROs)',
  programas: 'Programas',
  subprogramas: 'Subprogramas',
  proyectos: 'Proyectos',
  metas: 'Metas',
  meses_metas_programadas: 'Programación Mensual de Metas',
  indicadores: 'Indicadores',
  actividades_sustantivas: 'Actividades Sustantivas',
  pei_proyecto_alineaciones: 'Alineaciones PEI',
  unidades_medidas: 'Unidades de Medida',
};

const ICONS = {
  unidades_responsables_gastos: 'mdi:domain',
  responsables_operativos: 'mdi:account-group',
  programas: 'mdi:clipboard-text',
  subprogramas: 'mdi:format-list-bulleted-type',
  proyectos: 'mdi:briefcase',
  metas: 'mdi:bullseye-arrow',
  meses_metas_programadas: 'mdi:calendar-month',
  indicadores: 'mdi:chart-line',
  actividades_sustantivas: 'mdi:format-list-checks',
  pei_proyecto_alineaciones: 'mdi:link-variant',
  unidades_medidas: 'mdi:scale-balance',
};

export default function ModoSummaryModal({ open, onClose, stats, type }) {
  if (!stats) return null;

  const isDelete = type === 'delete';
  const color = isDelete ? 'error' : 'success';
  const title = isDelete ? 'Resumen de Registros Eliminados' : 'Resumen de Registros Clonados';
  const bgHeader = isDelete ? '#d32f2f' : '#2e7d32';
  const headerIcon = isDelete ? 'mdi:delete-sweep' : 'mdi:content-copy';

  const items = Object.keys(stats).filter(key => LABELS[key]);

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth PaperProps={{ sx: { borderRadius: 2 } }}>
      <DialogTitle sx={{ bgcolor: bgHeader, color: '#fff', display: 'flex', alignItems: 'center', p: 2 }}>
        <Iconify icon={headerIcon} width={28} height={28} sx={{ mr: 2 }} />
        <Typography variant="h6" component="div">
          {title}
        </Typography>
      </DialogTitle>
      
      <DialogContent sx={{ mt: 2, p: 0 }}>
        <Box sx={{ px: 3, pt: 2, pb: 1 }}>
          <Typography variant="body2" color="text.secondary" gutterBottom>
            {isDelete 
              ? 'Se han eliminado de forma definitiva los siguientes registros asociados a la etapa:'
              : 'Se han copiado exitosamente los siguientes registros del ejercicio anterior:'}
          </Typography>
        </Box>
        
        <List sx={{ px: 2 }}>
          {items.map((key) => {
            const count = stats[key] || 0;
            return (
              <ListItem key={key} sx={{ py: 1, px: 2, '&:hover': { bgcolor: 'action.hover', borderRadius: 1 } }}>
                <ListItemIcon>
                  <Iconify icon={ICONS[key] || 'mdi:file'} width={24} height={24} color={count > 0 ? `${color}.main` : 'text.disabled'} />
                </ListItemIcon>
                <ListItemText 
                  primary={LABELS[key]} 
                  primaryTypographyProps={{ 
                    fontWeight: count > 0 ? 600 : 400,
                    color: count > 0 ? 'text.primary' : 'text.disabled'
                  }} 
                />
                <Badge 
                  badgeContent={count} 
                  color={count > 0 ? color : 'default'} 
                  showZero
                  sx={{ 
                    '& .MuiBadge-badge': { 
                      fontSize: '0.9rem',
                      height: 24,
                      minWidth: 24,
                      px: 1
                    } 
                  }}
                />
              </ListItem>
            );
          })}
        </List>
      </DialogContent>
      
      <Divider />
      
      <DialogActions sx={{ p: 2, bgcolor: 'background.neutral' }}>
        <Button onClick={onClose} variant="contained" color={color}>
          Aceptar
        </Button>
      </DialogActions>
    </Dialog>
  );
}
