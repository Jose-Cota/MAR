import { useState, useEffect } from 'react';
import { styled } from '@mui/material/styles';
import { Alert, Box, Button, Card, Container, IconButton, InputAdornment, Stack, TextField, Typography } from '@mui/material';
import Logo from '../../components/Logo';
import Iconify from '../../components/Iconify';
import useAuth from '../../hooks/useAuth';

// ----------------------------------------------------------------------

const RootStyle = styled('div')(({ theme }) => ({
  display: 'flex',
  minHeight: '100vh',
  alignItems: 'center',
  justifyContent: 'center',
  backgroundColor: theme.palette.background.neutral,
}));

const CardStyle = styled(Card)(({ theme }) => ({
  width: '100%',
  maxWidth: 504,               // +20% respecto a 420
  padding: theme.spacing(3, 4), // reducido de (5,4) a (3,4)
}));

// ----------------------------------------------------------------------

export default function Login() {
  const { login } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [currentTime, setCurrentTime] = useState(new Date());

  useEffect(() => {
    const timer = setInterval(() => setCurrentTime(new Date()), 1000);
    return () => clearInterval(timer);
  }, []);

  const handleSubmit = async (event) => {
    event.preventDefault();
    setError('');
    setIsSubmitting(true);
    try {
      await login(email, password);
    } catch (err) {
      setError(
        err.response?.data?.errors?.email?.[0] ||
          err.response?.data?.message ||
          'No fue posible iniciar sesión. Intenta de nuevo.'
      );
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <RootStyle>
      <Container maxWidth="sm">
        <CardStyle>
          <Box sx={{ textAlign: 'center', mb: 2 }}>
            <Logo disabledLink sx={{ width: 90, mx: 'auto', mb: 1.5 }} />
            <Typography sx={{ color: 'text.secondary', mb: 1, fontSize: '1.15rem', fontWeight: 600 }}>
              Matriz de Administración de Riesgos 2027
            </Typography>
            <Typography variant="body2" sx={{ color: 'text.secondary' }}>
              Tribunal Electoral de la Ciudad de México
            </Typography>
          </Box>

          <Box component="form" onSubmit={handleSubmit}>
            <Stack spacing={1.5}>
              {error && <Alert severity="error">{error}</Alert>}

              <TextField
                name="email"
                label="Usuario o correo electrónico"
                type="text"
                fullWidth
                required
                value={email}
                onChange={(e) => setEmail(e.target.value)}
              />

              <TextField
                name="password"
                label="Contraseña"
                type={showPassword ? 'text' : 'password'}
                fullWidth
                required
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                slotProps={{
                  input: {
                    endAdornment: (
                      <InputAdornment position="end">
                        <IconButton onClick={() => setShowPassword((prev) => !prev)} edge="end">
                          <Iconify icon={showPassword ? 'eva:eye-fill' : 'eva:eye-off-fill'} />
                        </IconButton>
                      </InputAdornment>
                    ),
                  },
                }}
              />

              <Button fullWidth size="large" type="submit" variant="contained" disabled={isSubmitting}>
                {isSubmitting ? 'Ingresando…' : 'Iniciar sesión'}
              </Button>

              <Typography variant="caption" sx={{ display: 'block', textAlign: 'center', color: 'text.secondary' }}>
                Versión 1.0 · 23 sep 2026 · 00:30 hrs
              </Typography>
            </Stack>
          </Box>
        </CardStyle>
      </Container>
    </RootStyle>
  );
}
