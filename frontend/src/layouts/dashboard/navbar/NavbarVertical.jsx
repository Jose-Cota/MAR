import { NavLink } from 'react-router-dom';
import Iconify from '../../../components/Iconify';
import useAuth from '../../../hooks/useAuth';

/**
 * Roles del sistema MAR:
 * - Super Administrador: Ve todo (incluyendo Usuarios)
 * - Administrador: Todo excepto Usuarios
 * - Capturista: Todo excepto Usuarios
 * - Validador: Todo excepto Usuarios
 */
export default function NavbarVertical() {
  const { hasRole } = useAuth();

  const isSuperAdmin = hasRole('Super Administrador') || hasRole('superadmin') || hasRole('Superadmin');
  const isAdminOrSuperAdmin = isSuperAdmin || hasRole('Administrador') || hasRole('admin') || hasRole('Admin');

  return (
    <div className="sidebar">
      <nav>
        {/* Módulos visibles para todos los roles */}
        <NavLink to="/" end className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
          <Iconify icon="ph:house" sx={{ width: 22, height: 22 }} /> <span>Inicio</span>
        </NavLink>
        <NavLink to="/poa" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
          <Iconify icon="ph:list-dashes" sx={{ width: 22, height: 22 }} /> <span>POA</span>
        </NavLink>
        <NavLink to="/riesgos" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
          <Iconify icon="ph:squares-four" sx={{ width: 22, height: 22 }} /> <span>Objetivos y Riesgos</span>
        </NavLink>
        <NavLink to="/factores" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
          <Iconify icon="ph:warning" sx={{ width: 22, height: 22 }} /> <span>Factores</span>
        </NavLink>
        <NavLink to="/controles" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
          <Iconify icon="ph:app-window" sx={{ width: 22, height: 22 }} /> <span>Controles e Indicadores</span>
        </NavLink>
        <NavLink to="/mapmar" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
          <Iconify icon="ph:grid-four" sx={{ width: 22, height: 22 }} /> <span>MAPA y MAR</span>
        </NavLink>
        {isAdminOrSuperAdmin && (
          <>
            <NavLink to="/indicadores" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
              <Iconify icon="ph:chart-bar" sx={{ width: 22, height: 22 }} /> <span>Indicadores</span>
            </NavLink>
            <NavLink to="/seguimiento" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
              <Iconify icon="ph:arrow-up-right" sx={{ width: 22, height: 22 }} /> <span>Seguimiento</span>
            </NavLink>
            <NavLink to="/reportes" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
              <Iconify icon="ph:list" sx={{ width: 22, height: 22 }} /> <span>Reportes</span>
            </NavLink>
            <NavLink to="/consolidacion" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
              <Iconify icon="ph:diamond" sx={{ width: 22, height: 22 }} /> <span>Consolidación</span>
            </NavLink>
            <NavLink to="/mapa-institucional" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
              <Iconify icon="ph:map-trifold" sx={{ width: 22, height: 22 }} /> <span>Mapa Institucional</span>
            </NavLink>
          </>
        )}

        {/* Solo Super Administrador: gestión de Usuarios */}
        {isSuperAdmin && (
          <>
            <hr style={{ border: 'none', borderTop: '1px solid rgba(255,255,255,0.15)', margin: '8px 4px' }} />
            <NavLink to="/admin/usuarios" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
              <Iconify icon="ph:users" sx={{ width: 22, height: 22 }} /> <span>Usuarios y permisos</span>
            </NavLink>
          </>
        )}
      </nav>
      <div className="side-footer">
        MAR 2027<br />
        <small>Versión 1.0  22 sep 2026  1733 hrs</small>
      </div>
    </div>
  );
}
