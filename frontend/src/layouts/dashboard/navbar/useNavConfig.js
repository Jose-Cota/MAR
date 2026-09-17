import useAuth from '../../../hooks/useAuth';
import useGlobalStore from '../../../stores/useGlobalStore';

export default function useNavConfig() {
  const { hasPermission, hasRole } = useAuth();
  const isAdmin = hasRole('Administrador');
  
  const etapasActivas = useGlobalStore((state) => state.etapasActivas) || [];
  const elaboracionActiva = etapasActivas.includes('elaboracion_proyectos');
  const seguimientoActivo = etapasActivas.includes('seguimiento_proyectos');

  const navConfig = [
    {
      subheader: 'Tablero',
      hidden: !hasPermission('Dashboard') && !isAdmin,
      items: [
        { title: 'Tablero General', path: '/', icon: 'mdi:view-dashboard-outline' },
      ],
    },
    {
      subheader: 'Planeación',
      hidden: (!elaboracionActiva && !seguimientoActivo) && !isAdmin,
      items: [
        { title: 'Proyectos', path: '/planeacion/proyectos', icon: 'mdi:format-list-bulleted', hidden: !hasPermission('proyectos') && !isAdmin },
        { 
          title: hasPermission('Unidades administrativas') ? 'Unidades Administrativas' : 'Unidad Responsable', 
          path: '/planeacion/unidades-responsables', 
          icon: 'mdi:office-building-outline',
          hidden: !hasPermission('Unidades administrativas') && !isAdmin // If they have neither, it's hidden. Wait! Capturador needs to see it! I assigned 'Unidades administrativas' to Capturador.
        },
        { title: 'Responsables operativos', path: '/planeacion/responsables-operativos', icon: 'mdi:account-tie-outline', hidden: !hasPermission('Responsables operativos') && !isAdmin },
        { title: 'Programas', path: '/planeacion/programas', icon: 'mdi:clipboard-text-outline', hidden: !hasPermission('Programas') && !isAdmin },
        { title: 'Subprogramas', path: '/planeacion/subprogramas', icon: 'mdi:file-tree', hidden: !hasPermission('subprogramas') && !isAdmin },
        { title: 'Unidades de medida', path: '/planeacion/unidades-medida', icon: 'mdi:ruler', hidden: !hasPermission('Unidades de medida') && !isAdmin },
        { title: 'Tablero de Administración', path: '/planeacion/tablero', icon: 'mdi:view-dashboard-outline', hidden: !hasPermission('Tablero de administracion') && !isAdmin },
      ],
    },
    {
      subheader: 'Reportes',
      items: [
        { title: 'Elaboración', path: '/reportes/elaboracion', icon: 'mdi:file-document-edit-outline', hidden: (!elaboracionActiva && !isAdmin) || (!hasPermission('Reportes de Elaboracion') && !isAdmin) },
        { title: 'Seguimiento', path: '/reportes/seguimiento', icon: 'mdi:chart-timeline-variant', hidden: (!seguimientoActivo && !isAdmin) || (!hasPermission('Reportes de seguimiento') && !isAdmin) },
      ],
    },
    {
      subheader: 'Configuración',
      hidden: false,
      items: [
        { title: 'Elaboración', path: '/configuracion/elaboracion', icon: 'mdi:cogs', hidden: !hasPermission('Configuracion Elaboracion') && !isAdmin },
        { title: 'Seguimiento', path: '/configuracion/seguimiento', icon: 'mdi:chart-timeline', hidden: !hasPermission('Configuracion Seguimiento') && !isAdmin },
        { title: 'Anteproyecto', path: '/configuracion/anteproyecto', icon: 'mdi:file-document-outline', hidden: !hasPermission('Configuracion Anteproyecto') && !isAdmin },
        { title: 'Mailing', path: '/configuracion/mailing', icon: 'mdi:email-outline', hidden: !isAdmin },
      ],
    },
    {
      subheader: 'Administración',
      hidden: false,
      items: [
        {
          title: 'Catálogos',
          path: '/admin/catalogos',
          icon: 'mdi:folder-cog-outline',
          hidden: !hasPermission('Etapas') && !isAdmin,
          children: [
            { title: 'Etapas', path: '/admin/catalogos/modos', hidden: !hasPermission('Etapas') && !isAdmin },
          ],
        },
        { title: 'Usuarios y accesos', path: '/admin/usuarios', icon: 'mdi:account-group-outline', hidden: !hasPermission('Usuarios y accesos') && !isAdmin },
        { title: 'Roles y permisos', path: '/admin/roles', icon: 'mdi:shield-key-outline', hidden: !hasPermission('Roles y permisos') && !isAdmin },
      ],
    },
  ];

  // Filter out hidden subheaders and items
  return navConfig
    .filter(group => !group.hidden)
    .map(group => ({
      ...group,
      items: group.items.filter(item => !item.hidden)
    }))
    .filter(group => group.items.length > 0);
}
