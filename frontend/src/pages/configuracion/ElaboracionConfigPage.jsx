import React, { useState, useEffect } from 'react';
import {
  Container,
  Box,
  Typography,
  Card,
  CardContent,
  FormControlLabel,
  Checkbox,
  Button,
  Select,
  MenuItem,
  FormControl,
  InputLabel,
  Grid,
  Snackbar,
  Alert
} from '@mui/material';
import Iconify from '../../components/Iconify';
import axios from '../../utils/axios';

export default function ElaboracionConfigPage() {
  const [ejercicios, setEjercicios] = useState([]);
  const [selectedEjercicio, setSelectedEjercicio] = useState('');
  const [config, setConfig] = useState({
    habilitado: false,
    permitir_edicion: false,
  });
  const [loading, setLoading] = useState(false);
  const [snackbar, setSnackbar] = useState({ open: false, message: '', variant: 'success' });
  const enqueueSnackbar = (message, { variant }) => setSnackbar({ open: true, message, variant });

  useEffect(() => {
    fetchEjercicios();
  }, []);

  useEffect(() => {
    if (selectedEjercicio) {
      fetchConfig();
    }
  }, [selectedEjercicio]);

  const fetchEjercicios = async () => {
    try {
      const response = await axios.get('/catalogos/ejercicios');
      setEjercicios(response.data);
      if (response.data.length > 0) {
        setSelectedEjercicio(response.data[0].ejercicio_id);
      }
    } catch (error) {
      console.error('Error fetching ejercicios:', error);
      enqueueSnackbar('Error al cargar ejercicios', { variant: 'error' });
    }
  };

  const fetchConfig = async () => {
    try {
      setLoading(true);
      const response = await axios.get(`/configuracion/elaboracion/${selectedEjercicio}`);
      setConfig(response.data);
    } catch (error) {
      console.error('Error fetching config:', error);
      setConfig({ habilitado: false, permitir_edicion: false });
    } finally {
      setLoading(false);
    }
  };

  const handleSave = async () => {
    try {
      setLoading(true);
      await axios.put(`/configuracion/elaboracion/${selectedEjercicio}`, config);
      enqueueSnackbar('Configuración guardada correctamente', { variant: 'success' });
    } catch (error) {
      console.error('Error saving config:', error);
      enqueueSnackbar('Error al guardar la configuración', { variant: 'error' });
    } finally {
      setLoading(false);
    }
  };

  return (
    <Container maxWidth={false}>
      <Box sx={{ mb: 5 }}>
        <Typography variant="h4" sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
          <Iconify icon="mdi:cogs" width={32} height={32} />
          Configuración de Elaboración
        </Typography>
      </Box>

      <Card>
        <CardContent sx={{ p: 4 }}>
          <Grid container spacing={4}>
            <Grid size={{ xs: 12 }}>
              <Typography variant="h6" gutterBottom>Ejercicios</Typography>
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 4 }}>
                <FormControl sx={{ minWidth: 200 }}>
                  <InputLabel>Seleccione uno</InputLabel>
                  <Select
                    value={selectedEjercicio}
                    label="Seleccione uno"
                    onChange={(e) => setSelectedEjercicio(e.target.value)}
                  >
                    {ejercicios.map((ej) => (
                      <MenuItem key={ej.ejercicio_id} value={ej.ejercicio_id}>
                        {ej.ejercicio}
                      </MenuItem>
                    ))}
                  </Select>
                </FormControl>
                
                <FormControlLabel
                  control={
                    <Checkbox
                      checked={config.habilitado}
                      onChange={(e) => setConfig({ ...config, habilitado: e.target.checked })}
                      color="primary"
                    />
                  }
                  label="Habilitar elaboración de fichas POA"
                />
              </Box>
            </Grid>

            <Grid item xs={12} sx={{ mt: 2 }}>
              <Typography variant="h6" gutterBottom>Permisos</Typography>
              <FormControlLabel
                control={
                  <Checkbox
                    checked={config.permitir_edicion}
                    onChange={(e) => setConfig({ ...config, permitir_edicion: e.target.checked })}
                    color="primary"
                  />
                }
                label="Permitir edición sobre Elaboración de Fichas POA"
              />
            </Grid>
          </Grid>

          <Box sx={{ display: 'flex', justifyContent: 'flex-end', mt: 5 }}>
            <Button
              variant="contained"
              color="success"
              size="large"
              onClick={handleSave}
              disabled={!selectedEjercicio || loading}
            >
              Aplicar configuración
            </Button>
          </Box>
        </CardContent>
      </Card>

      <Snackbar open={snackbar.open} autoHideDuration={6000} onClose={() => setSnackbar({ ...snackbar, open: false })}>
        <Alert severity={snackbar.variant} onClose={() => setSnackbar({ ...snackbar, open: false })}>
          {snackbar.message}
        </Alert>
      </Snackbar>
    </Container>
  );
}
