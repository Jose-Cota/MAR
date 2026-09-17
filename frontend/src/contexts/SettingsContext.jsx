import PropTypes from 'prop-types';
import { createContext, useEffect, useMemo, useState } from 'react';

// ----------------------------------------------------------------------

const STORAGE_KEY = 'poa-settings';

const initialState = {
  themeMode: 'light',
  isCollapse: false,
};

export const SettingsContext = createContext({
  ...initialState,
  onToggleMode: () => {},
  onToggleCollapse: () => {},
});

function readStoredSettings() {
  try {
    const raw = window.localStorage.getItem(STORAGE_KEY);
    return raw ? { ...initialState, ...JSON.parse(raw) } : initialState;
  } catch {
    return initialState;
  }
}

SettingsProvider.propTypes = {
  children: PropTypes.node,
};

export function SettingsProvider({ children }) {
  const [settings, setSettings] = useState(readStoredSettings);

  useEffect(() => {
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(settings));
  }, [settings]);

  const value = useMemo(
    () => ({
      ...settings,
      onToggleMode: () =>
        setSettings((prev) => ({ ...prev, themeMode: prev.themeMode === 'light' ? 'dark' : 'light' })),
      onToggleCollapse: () => setSettings((prev) => ({ ...prev, isCollapse: !prev.isCollapse })),
    }),
    [settings]
  );

  return <SettingsContext.Provider value={value}>{children}</SettingsContext.Provider>;
}
