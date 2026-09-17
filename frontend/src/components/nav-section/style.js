import { alpha, styled } from '@mui/material/styles';
import { ListItemText, ListItemButton, ListItemIcon } from '@mui/material';
import { ICON } from '../../config';

// ----------------------------------------------------------------------

export const ListItemStyle = styled(ListItemButton, {
  shouldForwardProp: (prop) => prop !== 'activeRoot' && prop !== 'activeSub' && prop !== 'subItem',
})(({ activeRoot, activeSub, subItem, theme }) => ({
  ...theme.typography.body2,
  position: 'relative',
  height: 28, // Highly compact
  fontSize: '0.75rem', // 12px
  paddingLeft: theme.spacing(1.5),
  paddingRight: theme.spacing(1),
  marginBottom: 0,
  color: theme.palette.text.secondary,
  borderRadius: theme.shape.borderRadius,
  ...(activeRoot && {
    ...theme.typography.subtitle2,
    fontSize: '0.75rem',
    color: theme.palette.primary.main,
    backgroundColor: alpha(theme.palette.primary.main, theme.palette.action.selectedOpacity ?? 0.12),
  }),
  ...(activeSub && {
    ...theme.typography.subtitle2,
    fontSize: '0.75rem',
    color: theme.palette.text.primary,
  }),
  ...(subItem && {
    height: 24, // Highly compact sub item
    fontSize: '0.7rem',
    paddingLeft: theme.spacing(5),
  }),
}));

export const ListItemTextStyle = styled(ListItemText, {
  shouldForwardProp: (prop) => prop !== 'isCollapse',
})(({ isCollapse, theme }) => ({
  whiteSpace: 'nowrap',
  transition: theme.transitions.create(['width', 'opacity'], {
    duration: theme.transitions.duration.shorter,
  }),
  ...(isCollapse && {
    width: 0,
    opacity: 0,
  }),
}));

export const ListItemIconStyle = styled(ListItemIcon)({
  width: 20,
  height: 20,
  minWidth: 28,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  '& svg': { width: '100%', height: '100%' },
});
