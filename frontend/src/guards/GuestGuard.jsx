import PropTypes from 'prop-types';
import { Navigate } from 'react-router-dom';
import useAuth from '../hooks/useAuth';

// ----------------------------------------------------------------------

GuestGuard.propTypes = {
  children: PropTypes.node,
};

export default function GuestGuard({ children }) {
  const { isInitialized, isAuthenticated } = useAuth();

  if (isInitialized && isAuthenticated) {
    return <Navigate to="/" replace />;
  }

  return children;
}
