import { NavLink } from 'react-router-dom';
import Iconify from '../../../components/Iconify';
import useAuth from '../../../hooks/useAuth';

export default function NavbarVertical() {
  const { hasRole } = useAuth();
  
  // En v4.3, Super Administrador tiene acceso a los modulos institucionales y reportes.
  // "Administrador" ahora solo es a nivel area, por lo que no ve consolidados.
  const isSuperAdmin = hasRole('Super Administrador') || hasRole('superadmin') || hasRole('Superadmin') || hasRole('Admin');

  return (
    <div className="sidebar">
      <nav>
        <NavLink to="/" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
          <Iconify icon="ph:squares-four" sx={{ width: 22, height: 22 }} /> <span>Tablero general</span>
        </NavLink>
        <NavLink to="/poa" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
          <Iconify icon="ph:calendar-check" sx={{ width: 22, height: 22 }} /> <span>POA y Planeación</span>
        </NavLink>
        <NavLink to="/riesgos" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
          <Iconify icon="ph:warning" sx={{ width: 22, height: 22 }} /> <span>Riesgos de Área</span>
        </NavLink>
        <NavLink to="/factores" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
          <Iconify icon="ph:list-dashes" sx={{ width: 22, height: 22 }} /> <span>Factores</span>
        </NavLink>
        <NavLink to="/controles" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
          <Iconify icon="ph:shield-check" sx={{ width: 22, height: 22 }} /> <span>Controles e Indicadores</span>
        </NavLink>
        <NavLink to="/mapmar" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
          <Iconify icon="ph:map-trifold" sx={{ width: 22, height: 22 }} /> <span>Mapa de Riesgos (MAR)</span>
        </NavLink>

        {isSuperAdmin && (
          <>
            <hr style={{ border: 0, borderTop: '1px dashed rgba(255,255,255,0.2)', margin: '10px 0' }} />
            
            <NavLink to="/seguimiento" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`} style={{ color: '#f2c94c' }}>
              <Iconify icon="ph:chart-line-up" sx={{ width: 22, height: 22 }} /> <span>Seguimiento mensual</span>
            </NavLink>
            <NavLink to="/consolidacion" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`} style={{ color: '#f2c94c' }}>
              <Iconify icon="ph:globe" sx={{ width: 22, height: 22 }} /> <span>Consolidación Inst.</span>
            </NavLink>
            <NavLink to="/mapa-institucional" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`} style={{ color: '#f2c94c' }}>
              <Iconify icon="ph:globe-hemisphere-west" sx={{ width: 22, height: 22 }} /> <span>Mapa Institucional</span>
            </NavLink>
            <NavLink to="/reportes" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`} style={{ color: '#f2c94c' }}>
              <Iconify icon="ph:file-text" sx={{ width: 22, height: 22 }} /> <span>Reportes</span>
            </NavLink>
            <NavLink to="/admin/usuarios" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`} style={{ color: '#f2c94c' }}>
              <Iconify icon="ph:users" sx={{ width: 22, height: 22 }} /> <span>Usuarios y permisos</span>
            </NavLink>
          </>
        )}
      </nav>
      <div className="side-footer">
        MAR 2027<br />
        <small>Versión 4.3</small>
      </div>
    </div>
  );
}
