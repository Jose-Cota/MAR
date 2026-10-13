import PropTypes from 'prop-types';
import { Navigate } from 'react-router-dom';
import useAuth from '../hooks/useAuth';

export default function RoleGuard({ allowedRoles, requiredPermission, children }) {
  const { user, hasPermission, hasRole } = useAuth();

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  // Administrador y Super Administrador tienen acceso total
  const isSuperAdmin = hasRole('Super Administrador') || hasRole('superadmin') || hasRole('Superadmin');
  const isAdmin = isSuperAdmin || hasRole('Administrador') || hasRole('admin') || hasRole('Admin');

  if (isAdmin) {
    return <>{children}</>;
  }

  if (requiredPermission) {
    if (!hasPermission(requiredPermission) && !hasRole(requiredPermission)) {
      return <Navigate to="/" replace />;
    }
  } else if (allowedRoles) {
    const hasAnyRole = allowedRoles.some((r) => hasRole(r) || (user.role && user.role === r));
    if (!hasAnyRole) {
      // Si no tiene el rol, mandarlo a dashboard
      return <Navigate to="/" replace />;
    }
  }

  return <>{children}</>;
}

RoleGuard.propTypes = {
  allowedRoles: PropTypes.arrayOf(PropTypes.string),
  requiredPermission: PropTypes.string,
  children: PropTypes.node,
};
