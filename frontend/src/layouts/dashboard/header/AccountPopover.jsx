import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Avatar, Divider, IconButton, ListItemIcon, Menu, MenuItem } from '@mui/material';
import Iconify from '../../../components/Iconify';
import useAuth from '../../../hooks/useAuth';
import ProfileModal from './ProfileModal';

// ----------------------------------------------------------------------

export default function AccountPopover() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [anchorEl, setAnchorEl] = useState(null);
  const [profileOpen, setProfileOpen] = useState(false);

  const handleLogout = async () => {
    setAnchorEl(null);
    await logout();
    navigate('/login', { replace: true });
  };

  const handleProfileOpen = () => {
    setAnchorEl(null);
    setProfileOpen(true);
  };

  return (
    <>
      <IconButton onClick={(e) => setAnchorEl(e.currentTarget)} sx={{ p: 0 }}>
        <Avatar sx={{ bgcolor: 'primary.main', width: 48, height: 48 }}>
          <Iconify icon="eva:person-fill" width={28} height={28} />
        </Avatar>
      </IconButton>

      <Menu
        anchorEl={anchorEl}
        open={!!anchorEl}
        onClose={() => setAnchorEl(null)}
        anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
        transformOrigin={{ vertical: 'top', horizontal: 'right' }}
        PaperProps={{ sx: { mt: 1.5, width: 220 } }}
      >
        <MenuItem onClick={handleProfileOpen} sx={{ m: 1 }}>
          <ListItemIcon>
            <Iconify icon="eva:person-outline" width={24} height={24} />
          </ListItemIcon>
          Perfil
        </MenuItem>

        <Divider sx={{ borderStyle: 'dashed' }} />

        <MenuItem onClick={handleLogout} sx={{ m: 1, color: 'error.main' }}>
          <ListItemIcon>
            <Iconify icon="eva:log-out-outline" sx={{ color: 'error.main' }} width={24} height={24} />
          </ListItemIcon>
          Cerrar sesión
        </MenuItem>
      </Menu>

      <ProfileModal open={profileOpen} onClose={() => setProfileOpen(false)} />
    </>
  );
}
