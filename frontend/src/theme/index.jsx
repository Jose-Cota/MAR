import PropTypes from 'prop-types';
import { useMemo } from 'react';
import { CssBaseline } from '@mui/material';
import { createTheme, ThemeProvider as MUIThemeProvider, StyledEngineProvider } from '@mui/material/styles';
import useSettings from '../hooks/useSettings';
import palette from './palette';
import typography from './typography';
import breakpoints from './breakpoints';
import shadows, { customShadows } from './shadows';

// ----------------------------------------------------------------------

ThemeProvider.propTypes = {
  children: PropTypes.node,
};

export default function ThemeProvider({ children }) {
  const { themeMode } = useSettings();
  const isLight = themeMode === 'light';

  const themeOptions = useMemo(
    () => ({
      palette: isLight ? palette.light : palette.dark,
      typography,
      breakpoints,
      shape: { borderRadius: 8 },
      shadows: isLight ? shadows.light : shadows.dark,
      customShadows: isLight ? customShadows.light : customShadows.dark,
      components: {
        MuiCard: {
          styleOverrides: {
            root: ({ theme }) => ({
              boxShadow: theme.customShadows.card,
              borderRadius: theme.shape.borderRadius * 2,
            }),
          },
        },
        MuiButton: {
          styleOverrides: {
            root: { borderRadius: 8 },
          },
        },
        MuiListItemButton: {
          styleOverrides: {
            root: { borderRadius: 8 },
          },
        },
        MuiCssBaseline: {
          styleOverrides: {
            '*::-webkit-scrollbar': {
              width: '8px',
              height: '8px',
            },
            '*::-webkit-scrollbar-track': {
              background: 'transparent',
            },
            '*::-webkit-scrollbar-thumb': {
              background: 'rgba(145, 158, 171, 0.32)',
              borderRadius: '10px',
            },
            '*::-webkit-scrollbar-thumb:hover': {
              background: 'rgba(145, 158, 171, 0.56)',
            },
          },
        },
      },
    }),
    [isLight]
  );

  const theme = createTheme(themeOptions);

  return (
    <StyledEngineProvider injectFirst>
      <MUIThemeProvider theme={theme}>
        <CssBaseline />
        {children}
      </MUIThemeProvider>
    </StyledEngineProvider>
  );
}
