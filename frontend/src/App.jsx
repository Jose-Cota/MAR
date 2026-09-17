import { BrowserRouter } from 'react-router-dom';
import { SettingsProvider } from './contexts/SettingsContext';
import { AuthProvider } from './contexts/AuthContext';
import ThemeProvider from './theme';
import AppRoutes from './routes';

// ----------------------------------------------------------------------

export default function App() {
  return (
    <SettingsProvider>
      <ThemeProvider>
        <BrowserRouter>
          <AuthProvider>
            <AppRoutes />
          </AuthProvider>
        </BrowserRouter>
      </ThemeProvider>
    </SettingsProvider>
  );
}
