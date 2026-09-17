import { useState } from 'react';
import PropTypes from 'prop-types';
import { NavLink as RouterLink, useLocation } from 'react-router-dom';
import { List, ListSubheader, Collapse, Divider } from '@mui/material';
import Iconify from '../Iconify';
import { ListItemStyle, ListItemTextStyle, ListItemIconStyle } from './style';
import useGlobalStore from '../../stores/useGlobalStore';

// ----------------------------------------------------------------------

function NavItem({ item, isCollapse }) {
  const { pathname } = useLocation();
  const { title, path, icon, children, action, type } = item;
  const active = path ? pathname === path || pathname.startsWith(`${path}/`) : false;
  const [open, setOpen] = useState(active);

  if (type === 'divider') {
    return <Divider sx={{ my: 1, borderStyle: 'dashed' }} />;
  }

  const handleClick = (e) => {
    if (action) {
      // Si la acción era el modal de ejercicio, ya no hace nada.
      return;
    } else if (children) {
      setOpen((prev) => !prev);
    }
  };

  if (children) {
    return (
      <>
        <ListItemStyle activeRoot={open} onClick={handleClick}>
          {icon && <ListItemIconStyle><Iconify icon={icon} /></ListItemIconStyle>}
          <ListItemTextStyle disableTypography primary={title} isCollapse={isCollapse} />
          {!isCollapse && (
            <Iconify
              icon={open ? 'eva:arrow-ios-downward-fill' : 'eva:arrow-ios-forward-fill'}
              sx={{ width: 16, height: 16, ml: 1 }}
            />
          )}
        </ListItemStyle>
        {!isCollapse && (
          <Collapse in={open} timeout="auto" unmountOnExit>
            <List component="div" disablePadding>
              {children.map((child) => {
                if (child.action) {
                  return (
                    <ListItemStyle
                      key={child.title}
                      onClick={(e) => {
                        if (child.action) {
                          // Acción no usada actualmente
                        }
                      }}
                      sx={{ cursor: 'pointer' }}
                      subItem
                    >
                      <ListItemTextStyle disableTypography primary={child.title} />
                    </ListItemStyle>
                  );
                }

                const childActive = pathname === child.path;
                return (
                  <ListItemStyle
                    key={child.title}
                    component={RouterLink}
                    to={child.path}
                    subItem
                    activeSub={childActive}
                    onClick={() => window.dispatchEvent(new CustomEvent('nav-link-clicked', { detail: child.path }))}
                  >
                    <ListItemTextStyle disableTypography primary={child.title} />
                  </ListItemStyle>
                );
              })}
            </List>
          </Collapse>
        )}
      </>
    );
  }

  if (action) {
    return (
      <ListItemStyle onClick={handleClick} activeRoot={false} sx={{ cursor: 'pointer' }}>
        {icon && <ListItemIconStyle><Iconify icon={icon} /></ListItemIconStyle>}
        <ListItemTextStyle disableTypography primary={title} isCollapse={isCollapse} />
      </ListItemStyle>
    );
  }

  return (
    <ListItemStyle 
      component={RouterLink} 
      to={path} 
      activeRoot={active}
      onClick={() => window.dispatchEvent(new CustomEvent('nav-link-clicked', { detail: path }))}
    >
      {icon && <ListItemIconStyle><Iconify icon={icon} /></ListItemIconStyle>}
      <ListItemTextStyle disableTypography primary={title} isCollapse={isCollapse} />
    </ListItemStyle>
  );
}

NavItem.propTypes = {
  item: PropTypes.object.isRequired,
  isCollapse: PropTypes.bool,
};

// ----------------------------------------------------------------------

NavSectionVertical.propTypes = {
  navConfig: PropTypes.array.isRequired,
  isCollapse: PropTypes.bool,
};

export default function NavSectionVertical({ navConfig, isCollapse = false }) {
  return (
    <List disablePadding sx={{ px: 1 }}>
      {navConfig.map((section) => (
        <List
          key={section.subheader}
          disablePadding
          subheader={
            !isCollapse && (
              <ListSubheader
                disableSticky
                sx={{
                  px: 1,
                  mt: 0.5,
                  mb: 0,
                  pb: 0,
                  lineHeight: 1.2,
                  fontSize: 10,
                  fontWeight: 700,
                  letterSpacing: 0.5,
                  textTransform: 'uppercase',
                  color: 'text.disabled',
                }}
              >
                {section.subheader}
              </ListSubheader>
            )
          }
        >
          {section.items.map((item) => (
            <NavItem key={item.title} item={item} isCollapse={isCollapse} />
          ))}
        </List>
      ))}
    </List>
  );
}
