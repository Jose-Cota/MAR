import { useState, useEffect } from 'react';
import {
  Container,
  Typography,
  Card,
  CardContent,
  Grid,
  TextField,
  Button,
  Stack,
  MenuItem,
  Alert,
  Box,
  Divider,
  CircularProgress
} from '@mui/material';
import Iconify from '../../components/Iconify';
import axios from '../../utils/axios';

export default function MailingConfigPage() {
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [testing, setTesting] = useState(false);
  const [message, setMessage] = useState({ type: '', text: '' });
  
  const [formData, setFormData] = useState({
    host: '',
    port: 587,
    username: '',
    password: '',
    encryption: 'tls',
    from_address: '',
    from_name: ''
  });

  const [testEmail, setTestEmail] = useState('');

  useEffect(() => {
    fetchConfig();
  }, []);

  const fetchConfig = async () => {
    try {
      const response = await axios.get('/configuracion/mailing');
      if (response.data) {
        setFormData({
          host: response.data.host || '',
          port: response.data.port || 587,
          username: response.data.username || '',
          password: '', // do not populate password for security
          encryption: response.data.encryption || 'tls',
          from_address: response.data.from_address || '',
          from_name: response.data.from_name || ''
        });
      }
    } catch (error) {
      console.error('Error fetching mailing config:', error);
      setMessage({ type: 'error', text: 'Error al cargar la configuración.' });
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    setMessage({ type: '', text: '' });
    
    try {
      await axios.put('/configuracion/mailing', formData);
      setMessage({ type: 'success', text: 'Configuración guardada exitosamente.' });
      // Clear password field after save
      setFormData(prev => ({ ...prev, password: '' }));
    } catch (error) {
      setMessage({ type: 'error', text: error.response?.data?.message || 'Error al guardar la configuración.' });
    } finally {
      setSaving(false);
    }
  };

  const handleTestConnection = async () => {
    if (!testEmail) {
      setMessage({ type: 'error', text: 'Ingresa un correo para la prueba.' });
      return;
    }
    setTesting(true);
    setMessage({ type: 'info', text: 'Enviando correo de prueba...' });
    
    try {
      const response = await axios.post('/configuracion/mailing/test', { email: testEmail });
      setMessage({ type: 'success', text: response.data.message || 'Correo enviado con éxito.' });
    } catch (error) {
      setMessage({ type: 'error', text: error.response?.data?.message || 'Error al enviar el correo de prueba.' });
    } finally {
      setTesting(false);
    }
  };

  if (loading) return <Box sx={{ p: 5, textAlign: 'center' }}><CircularProgress /></Box>;

  return (
    <Container maxWidth="md">
      <Typography variant="h4" sx={{ mb: 4, color: '#143352', fontWeight: 700 }}>
        Configuración de Mailing (Correos)
      </Typography>

      {message.text && (
        <Alert severity={message.type === 'info' ? 'info' : message.type} sx={{ mb: 3 }}>
          {message.text}
        </Alert>
      )}

      <Card sx={{ mb: 4, borderRadius: 2, boxShadow: '0 4px 14px rgba(43,37,35,.05)' }}>
        <CardContent sx={{ p: 4 }}>
          <form onSubmit={handleSubmit}>
            <Grid container spacing={3}>
              <Grid item xs={12}>
                <Typography variant="subtitle1" sx={{ fontWeight: 600, color: '#1F4E79' }}>
                  Servidor SMTP
                </Typography>
                <Divider sx={{ my: 1 }} />
              </Grid>

              <Grid item xs={12} sm={8}>
                <TextField
                  fullWidth
                  label="Host SMTP"
                  name="host"
                  value={formData.host}
                  onChange={handleChange}
                  placeholder="ej. smtp.office365.com"
                  required
                />
              </Grid>
              <Grid item xs={12} sm={4}>
                <TextField
                  fullWidth
                  label="Puerto"
                  name="port"
                  type="number"
                  value={formData.port}
                  onChange={handleChange}
                  required
                />
              </Grid>

              <Grid item xs={12} sm={6}>
                <TextField
                  fullWidth
                  select
                  label="Cifrado (Encryption)"
                  name="encryption"
                  value={formData.encryption}
                  onChange={handleChange}
                >
                  <MenuItem value="tls">TLS</MenuItem>
                  <MenuItem value="ssl">SSL</MenuItem>
                  <MenuItem value="">Ninguno</MenuItem>
                </TextField>
              </Grid>

              <Grid item xs={12}>
                <Typography variant="subtitle1" sx={{ fontWeight: 600, color: '#1F4E79', mt: 2 }}>
                  Autenticación
                </Typography>
                <Divider sx={{ my: 1 }} />
              </Grid>

              <Grid item xs={12} sm={6}>
                <TextField
                  fullWidth
                  label="Usuario (Correo)"
                  name="username"
                  value={formData.username}
                  onChange={handleChange}
                  required
                />
              </Grid>
              <Grid item xs={12} sm={6}>
                <TextField
                  fullWidth
                  label="Contraseña"
                  name="password"
                  type="password"
                  value={formData.password}
                  onChange={handleChange}
                  placeholder="Dejar en blanco para no cambiar"
                />
              </Grid>

              <Grid item xs={12}>
                <Typography variant="subtitle1" sx={{ fontWeight: 600, color: '#1F4E79', mt: 2 }}>
                  Remitente (From)
                </Typography>
                <Divider sx={{ my: 1 }} />
              </Grid>

              <Grid item xs={12} sm={6}>
                <TextField
                  fullWidth
                  label="Dirección de Remitente"
                  name="from_address"
                  type="email"
                  value={formData.from_address}
                  onChange={handleChange}
                  required
                />
              </Grid>
              <Grid item xs={12} sm={6}>
                <TextField
                  fullWidth
                  label="Nombre de Remitente"
                  name="from_name"
                  value={formData.from_name}
                  onChange={handleChange}
                  required
                />
              </Grid>

              <Grid item xs={12}>
                <Stack direction="row" justifyContent="flex-end" sx={{ mt: 2 }}>
                  <Button
                    type="submit"
                    variant="contained"
                    disabled={saving}
                    startIcon={<Iconify icon="mdi:content-save" />}
                    sx={{ bgcolor: '#1F4E79', '&:hover': { bgcolor: '#143352' } }}
                  >
                    Guardar Configuración
                  </Button>
                </Stack>
              </Grid>
            </Grid>
          </form>
        </CardContent>
      </Card>

      <Card sx={{ borderRadius: 2, boxShadow: '0 4px 14px rgba(43,37,35,.05)' }}>
        <CardContent sx={{ p: 4 }}>
          <Typography variant="subtitle1" sx={{ fontWeight: 600, color: '#B57A16', mb: 2 }}>
            Probar Conexión
          </Typography>
          <Typography variant="body2" sx={{ color: 'text.secondary', mb: 3 }}>
            Antes de probar la conexión, asegúrate de haber guardado la configuración. Se enviará un correo de prueba a la dirección que especifiques.
          </Typography>

          <Stack direction={{ xs: 'column', sm: 'row' }} spacing={2} alignItems="center">
            <TextField
              fullWidth
              label="Correo destinatario de prueba"
              type="email"
              value={testEmail}
              onChange={(e) => setTestEmail(e.target.value)}
            />
            <Button
              variant="outlined"
              onClick={handleTestConnection}
              disabled={testing || !testEmail}
              sx={{ minWidth: 200, height: 56, color: '#B57A16', borderColor: '#B57A16', '&:hover': { borderColor: '#8E6011', bgcolor: 'rgba(181, 122, 22, 0.04)' } }}
              startIcon={<Iconify icon="mdi:send-check" />}
            >
              {testing ? 'Enviando...' : 'Enviar Prueba'}
            </Button>
          </Stack>
        </CardContent>
      </Card>
    </Container>
  );
}
