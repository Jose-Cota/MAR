import { NavLink } from 'react-router-dom';
import Iconify from '../../../components/Iconify';
import useAuth from '../../../hooks/useAuth';
import axios from '../../../utils/axios';

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

        {/* Solo Super Administrador: gestión de Usuarios y BD */}
        {isSuperAdmin && (
          <>
            <hr style={{ border: 'none', borderTop: '1px solid rgba(255,255,255,0.15)', margin: '8px 4px' }} />
            <NavLink to="/admin/usuarios" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
              <Iconify icon="ph:users" sx={{ width: 22, height: 22 }} /> <span>Usuarios y permisos</span>
            </NavLink>
            <button 
              onClick={async () => {
                try {
                  const response = await axios.get('/export-db', { responseType: 'blob' });
                  const blob = new Blob([response.data], { type: 'application/json' });
                  const url = window.URL.createObjectURL(blob);
                  const a = document.createElement('a');
                  a.href = url;
                  a.download = `Respaldo_MAR_TECDMX_${new Date().toISOString().split('T')[0]}.json`;
                  document.body.appendChild(a);
                  a.click();
                  a.remove();
                  window.URL.revokeObjectURL(url);
                } catch (error) {
                  console.error('Error:', error);
                  alert('Hubo un error al exportar la base de datos.');
                }
              }}
              style={{
                background: 'none', border: 'none', cursor: 'pointer', textAlign: 'left',
                fontFamily: 'inherit', fontSize: 'inherit', color: 'inherit'
              }}
              className="nav-link"
            >
              <Iconify icon="ph:database-export" sx={{ width: 22, height: 22 }} /> <span>Importar BD</span>
            </button>
          </>
        )}
      </nav>
      <div className="side-footer">
        MAR 2027<br />
        <small>Versión 1.2 · 25 sep 2026 · 10:44 hrs</small>
      </div>
    </div>
  );
}
