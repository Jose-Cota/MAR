import { useState } from 'react';
import { NavLink } from 'react-router-dom';
import { Snackbar, Alert } from '@mui/material';
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
  const [isExporting, setIsExporting] = useState(false);
  const [feedback, setFeedback] = useState({ open: false, message: '', severity: 'info' });

  const isSuperAdmin = hasRole('Super Administrador') || hasRole('superadmin') || hasRole('Superadmin');
  const isAdminOrSuperAdmin = isSuperAdmin || hasRole('Administrador') || hasRole('admin') || hasRole('Admin');

  const handleExportDb = async () => {
    if (isExporting) return;
    setIsExporting(true);
    setFeedback({
      open: true,
      message: 'Generando respaldo completo de la base de datos (estructura y datos)...',
      severity: 'info',
    });
    try {
      const response = await axios.get('/export-db', { responseType: 'blob' });
      const blob = new Blob([response.data], { type: 'application/json' });
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `Respaldo_MAR_TECDMX_${new Date().toISOString().split('T')[0]}.json`;
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);

      setFeedback({
        open: true,
        message: 'Base de datos exportada exitosamente a JSON.',
        severity: 'success',
      });
    } catch {
      setFeedback({
        open: true,
        message: 'Hubo un error al exportar la base de datos.',
        severity: 'error',
      });
    } finally {
      setIsExporting(false);
    }
  };

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
        {isAdminOrSuperAdmin && (
          <>
            <NavLink to="/consolidacion" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
              <Iconify icon="ph:diamond" sx={{ width: 22, height: 22 }} /> <span>Consolidación</span>
            </NavLink>
            <NavLink to="/riesgos-institucionales" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
              <Iconify icon="ph:shield-check" sx={{ width: 22, height: 22 }} /> <span>Riesgos institucionales</span>
            </NavLink>
          </>
        )}
        <NavLink to="/mapmar" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
          <Iconify icon="ph:grid-four" sx={{ width: 22, height: 22 }} /> <span>MAPA y MAR</span>
        </NavLink>
        {isAdminOrSuperAdmin && (
          <>
            <hr style={{ border: 'none', borderTop: '1px solid rgba(255,255,255,0.15)', margin: '8px 4px' }} />
            <NavLink to="/seguimiento" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
              <Iconify icon="ph:arrow-up-right" sx={{ width: 22, height: 22 }} /> <span>Seguimiento</span>
            </NavLink>
            <NavLink to="/admin/configuracion" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
              <Iconify icon="ph:gear" sx={{ width: 22, height: 22 }} /> <span>Configuración</span>
            </NavLink>
            {/* <NavLink to="/reportes" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
              <Iconify icon="ph:chart-bar" sx={{ width: 22, height: 22 }} /> <span>Reportes</span>
            </NavLink> */}
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
              type="button"
              onClick={handleExportDb}
              disabled={isExporting}
              style={{
                background: 'none',
                border: 'none',
                cursor: isExporting ? 'wait' : 'pointer',
                textAlign: 'left',
                fontFamily: 'inherit',
                fontSize: 'inherit',
                color: 'inherit',
                width: '100%',
                opacity: isExporting ? 0.7 : 1,
              }}
              className="nav-link"
              title="Exportar base de datos completa a JSON"
            >
              <Iconify icon={isExporting ? 'eos-icons:loading' : 'ph:database'} sx={{ width: 22, height: 22 }} />
              <span>{isExporting ? 'Exportando BD...' : 'Exportar BD'}</span>
            </button>
          </>
        )}
      </nav>

      <Snackbar
        open={feedback.open}
        autoHideDuration={5000}
        onClose={() => setFeedback((prev) => ({ ...prev, open: false }))}
        anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
      >
        <Alert
          onClose={() => setFeedback((prev) => ({ ...prev, open: false }))}
          severity={feedback.severity}
          variant="filled"
          sx={{ width: '100%', boxShadow: 3 }}
        >
          {feedback.message}
        </Alert>
      </Snackbar>
      <div className="side-footer">
        Sistema de Administracion de Riesgos - TECDMX 2026<br />
        <small>Versión 1.2 · 14 oct 2026 · 12:45 hrs</small>
      </div>
    </div>
  );
}
