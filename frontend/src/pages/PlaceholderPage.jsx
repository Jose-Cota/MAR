import PropTypes from 'prop-types';
import { Box, Card, Typography } from '@mui/material';
import Iconify from '../components/Iconify';

// ----------------------------------------------------------------------

PlaceholderPage.propTypes = {
  title: PropTypes.string.isRequired,
};

export default function PlaceholderPage({ title }) {
  return (
    <Box 
      sx={{ 
        display: 'flex', 
        flexDirection: 'column', 
        alignItems: 'center', 
        justifyContent: 'center', 
        minHeight: '60vh',
        textAlign: 'center'
      }}
    >
      <Iconify 
        icon="mdi:crane" 
        sx={{ width: 80, height: 80, color: 'text.secondary', opacity: 0.5, mb: 3 }} 
      />
      <Typography variant="h3" sx={{ color: 'text.primary', mb: 1, fontWeight: 600 }}>
        {title}
      </Typography>
      <Typography variant="h6" sx={{ color: 'text.secondary', fontWeight: 400 }}>
        Página en construcción
      </Typography>
      <Typography variant="body2" sx={{ color: 'text.disabled', mt: 2, maxWidth: 400 }}>
        Estamos trabajando para tener este módulo disponible pronto.
      </Typography>
    </Box>
  );
}
