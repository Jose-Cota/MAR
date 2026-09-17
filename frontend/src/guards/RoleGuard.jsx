import PropTypes from 'prop-types';
import { Navigate } from 'react-router-dom';
import useAuth from '../hooks/useAuth';

export default function RoleGuard({ allowedRoles, requiredPermission, children }) {
  const { user, hasPermission } = useAuth();

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  if (requiredPermission) {
    if (!hasPermission(requiredPermission)) {
      return <Navigate to="/" replace />;
    }
  } else if (allowedRoles) {
    if (!allowedRoles.includes(user.role)) {
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
