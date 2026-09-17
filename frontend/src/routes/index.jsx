import { Routes, Route } from 'react-router-dom';
import DashboardLayout from '../layouts/dashboard/DashboardLayout';
import Dashboard from '../pages/Dashboard';
import PlaceholderPage from '../pages/PlaceholderPage';
import Login from '../pages/auth/Login';
import AuthGuard from '../guards/AuthGuard';
import GuestGuard from '../guards/GuestGuard';
import RoleGuard from '../guards/RoleGuard';

// ----------------------------------------------------------------------

import ProyectosPage from '../pages/planeacion/proyectos/ProyectosPage';
import ResponsablesOperativosPage from '../pages/planeacion/catalogos/ResponsablesOperativosPage';
import ProgramasPage from '../pages/planeacion/catalogos/ProgramasPage';
import SubprogramasPage from '../pages/planeacion/catalogos/SubprogramasPage';
import UnidadesMedidaPage from '../pages/planeacion/catalogos/UnidadesMedidaPage';
import UnidadesResponsablesPage from '../pages/planeacion/catalogos/UnidadesResponsablesPage';
import SeguimientoPage from '../pages/reportes/seguimiento/SeguimientoPage';
import TableroAdministrativoPage from '../pages/planeacion/TableroAdministrativoPage';
import ElaboracionPage from '../pages/reportes/elaboracion/ElaboracionPage';
import ElaboracionConfigPage from '../pages/configuracion/ElaboracionConfigPage';
import SeguimientoConfigPage from '../pages/configuracion/SeguimientoConfigPage';
import AnteproyectoConfigPage from '../pages/configuracion/AnteproyectoConfigPage';
import UsuariosPage from '../pages/admin/usuarios/UsuariosPage';
import RolesPage from '../pages/admin/roles/RolesPage';
import MailingConfigPage from '../pages/configuracion/MailingConfigPage';
import ModosPage from '../pages/admin/catalogos/ModosPage';
import DocumentosPage from '../pages/DocumentosPage';

import RiesgosPage from '../pages/riesgos/RiesgosPage';
import RiesgoEditorPage from '../pages/riesgos/RiesgoEditorPage';
import POAPage from '../pages/poa/POAPage';
import ReportsPage from '../pages/reports/ReportsPage';
import ReportMap from '../pages/reports/ReportMap';
import ReportMAR from '../pages/reports/ReportMAR';
import ReportAnnexA from '../pages/reports/ReportAnnexA';
import ReportAnnexB from '../pages/reports/ReportAnnexB';
import ReportAnnexC from '../pages/reports/ReportAnnexC';
import ControlesPage from '../pages/controles/ControlesPage';
import IndicadoresPage from '../pages/indicadores/IndicadoresPage';
import MARSeguimientoPage from '../pages/seguimiento/SeguimientoPage';

const MODULE_ROUTES = [
  { path: '/planeacion/metas', title: 'Metas e indicadores' },
  { path: '/reportes/avance-programatico', title: 'Avance programático' },
  { path: '/reportes/exportar', title: 'Exportar / imprimir' },
];

export default function AppRoutes() {
  return (
    <Routes>
      <Route
        path="/login"
        element={
          <GuestGuard>
            <Login />
          </GuestGuard>
        }
      />

      <Route
        element={
          <AuthGuard>
            <DashboardLayout />
          </AuthGuard>
        }
      >
        <Route path="/" element={<Dashboard />} />
        <Route path="documentos" element={<DocumentosPage />} />
        
        <Route path="planeacion/proyectos" element={<RoleGuard requiredPermission="proyectos"><ProyectosPage /></RoleGuard>} />
        <Route path="planeacion/unidades-responsables" element={<RoleGuard requiredPermission="Unidades administrativas"><UnidadesResponsablesPage /></RoleGuard>} />
        <Route path="planeacion/responsables-operativos" element={<RoleGuard requiredPermission="Responsables operativos"><ResponsablesOperativosPage /></RoleGuard>} />
        <Route path="planeacion/programas" element={<RoleGuard requiredPermission="Programas"><ProgramasPage /></RoleGuard>} />
        <Route path="planeacion/subprogramas" element={<RoleGuard requiredPermission="subprogramas"><SubprogramasPage /></RoleGuard>} />
        <Route path="planeacion/unidades-medida" element={<RoleGuard requiredPermission="Unidades de medida"><UnidadesMedidaPage /></RoleGuard>} />
        <Route path="planeacion/tablero" element={<RoleGuard requiredPermission="Tablero de administracion"><TableroAdministrativoPage /></RoleGuard>} />

        <Route path="reportes/elaboracion" element={<RoleGuard requiredPermission="Reportes de Elaboracion"><ElaboracionPage /></RoleGuard>} />
        <Route path="reportes/seguimiento" element={<RoleGuard requiredPermission="Reportes de seguimiento"><SeguimientoPage /></RoleGuard>} />

        <Route path="configuracion/elaboracion" element={<RoleGuard requiredPermission="Configuracion Elaboracion"><ElaboracionConfigPage /></RoleGuard>} />
        <Route path="configuracion/seguimiento" element={<RoleGuard requiredPermission="Configuracion Seguimiento"><SeguimientoConfigPage /></RoleGuard>} />
        <Route path="configuracion/anteproyecto" element={<RoleGuard requiredPermission="Configuracion Anteproyecto"><AnteproyectoConfigPage /></RoleGuard>} />
        <Route path="configuracion/mailing" element={<RoleGuard requiredPermission="Administrador"><MailingConfigPage /></RoleGuard>} />

        <Route path="admin/catalogos/modos" element={<RoleGuard requiredPermission="Etapas"><ModosPage /></RoleGuard>} />
        <Route path="admin/usuarios" element={<RoleGuard requiredPermission="Usuarios y accesos"><UsuariosPage /></RoleGuard>} />
        <Route path="admin/roles" element={<RoleGuard requiredPermission="Roles y permisos"><RolesPage /></RoleGuard>} />

        {/* MAR Routes */}
        <Route path="riesgos" element={<RiesgosPage />} />
        <Route path="riesgos/nuevo" element={<RiesgoEditorPage />} />
        <Route path="riesgos/:id" element={<RiesgoEditorPage />} />
        
        <Route path="poa" element={<POAPage />} />
        
        <Route path="reportes" element={<ReportsPage />} />
        <Route path="reportes/mapa/:areaId" element={<ReportMap />} />
        <Route path="reportes/mar/:areaId" element={<ReportMAR />} />
        <Route path="reportes/anexo-a/:areaId" element={<ReportAnnexA />} />
        <Route path="reportes/anexo-b/:areaId" element={<ReportAnnexB />} />
        <Route path="reportes/anexo-c/:areaId" element={<ReportAnnexC />} />
        <Route path="controles" element={<ControlesPage />} />
        <Route path="indicadores" element={<IndicadoresPage />} />
        <Route path="seguimiento" element={<MARSeguimientoPage />} />

        {MODULE_ROUTES.map((route) => (
          <Route key={route.path} path={route.path} element={<PlaceholderPage title={route.title} />} />
        ))}
      </Route>
    </Routes>
  );
}
