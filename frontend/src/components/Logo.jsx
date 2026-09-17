import PropTypes from 'prop-types';
import { Link as RouterLink } from 'react-router-dom';
import { Box } from '@mui/material';
import logoTecdmx from '../assets/logo_tecdmx.png';

// ----------------------------------------------------------------------

Logo.propTypes = {
  disabledLink: PropTypes.bool,
  sx: PropTypes.object,
};

export default function Logo({ disabledLink = false, sx }) {
  const logo = (
    <Box sx={{ width: 100, ...sx }}>
      <img src={logoTecdmx} alt="TECDMX" style={{ width: '100%', height: 'auto' }} />
    </Box>
  );

  if (disabledLink) {
    return logo;
  }

  return <RouterLink to="/">{logo}</RouterLink>;
}
