import PropTypes from 'prop-types';
import { createContext, useEffect, useMemo, useState } from 'react';
import axios from '../utils/axios';

// ----------------------------------------------------------------------

const TOKEN_KEY = 'poa-token';

export const AuthContext = createContext({
  isInitialized: false,
  isAuthenticated: false,
  user: null,
  hasPermission: () => false,
  hasRole: () => false,
  login: async () => {},
  logout: async () => {},
});

AuthProvider.propTypes = {
  children: PropTypes.node,
};

export function AuthProvider({ children }) {
  const [isInitialized, setIsInitialized] = useState(false);
  const [user, setUser] = useState(null);

  useEffect(() => {
    const token = window.localStorage.getItem(TOKEN_KEY);
    if (!token) {
      setIsInitialized(true);
      return;
    }

    axios
      .get('/user')
      .then((response) => setUser(response.data))
      .catch(() => {
        window.localStorage.removeItem(TOKEN_KEY);
        setUser(null);
      })
      .finally(() => setIsInitialized(true));
  }, []);

  const login = async (email, password) => {
    const response = await axios.post('/login', { email, password });
    window.localStorage.setItem(TOKEN_KEY, response.data.token);
    setUser(response.data.user);
  };

  const logout = async () => {
    try {
      await axios.post('/logout');
    } finally {
      window.localStorage.removeItem(TOKEN_KEY);
      setUser(null);
    }
  };

  const hasPermission = (permission) => {
    if (!user || !user.permissions) return false;
    return user.permissions.includes(permission);
  };

  const hasRole = (role) => {
    if (!user) return false;
    if (user.roles && Array.isArray(user.roles)) {
      if (user.roles.includes(role)) return true;
      if (user.roles.some(r => r.name === role)) return true;
    }
    return user.role === role;
  };

  const value = useMemo(
    () => ({
      isInitialized,
      isAuthenticated: !!user,
      user,
      hasPermission,
      hasRole,
      login,
      logout,
    }),
    [isInitialized, user]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
