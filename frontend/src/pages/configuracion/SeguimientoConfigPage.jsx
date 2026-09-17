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
  Radio,
  RadioGroup,
  Divider,
  Snackbar,
  Alert,
  Stack
} from '@mui/material';
import Iconify from '../../components/Iconify';
import axios from '../../utils/axios';

const MONTHS = [
  { id: 1, name: 'Enero', short: 'Ene' },
  { id: 2, name: 'Febrero', short: 'Feb' },
  { id: 3, name: 'Marzo', short: 'Mar' },
  { id: 4, name: 'Abril', short: 'Abr' },
  { id: 5, name: 'Mayo', short: 'May' },
  { id: 6, name: 'Junio', short: 'Jun' },
  { id: 7, name: 'Julio', short: 'Jul' },
  { id: 8, name: 'Agosto', short: 'Ago' },
  { id: 9, name: 'Septiembre', short: 'Sep' },
  { id: 10, name: 'Octubre', short: 'Oct' },
  { id: 11, name: 'Noviembre', short: 'Nov' },
  { id: 12, name: 'Diciembre', short: 'Dic' },
];

export default function SeguimientoConfigPage() {
  const [ejercicios, setEjercicios] = useState([]);
  const [selectedEjercicio, setSelectedEjercicio] = useState('');
  const [config, setConfig] = useState({
    habilitado: false,
    tipo_captura_seguimiento: 'global',
    ultimo_mes_visible: 'enero',
    ultimo_mes_consulta: 'enero',
    meses_habilitados: {}
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
      const response = await axios.get(`/configuracion/seguimiento/${selectedEjercicio}`);
      setConfig(response.data);
    } catch (error) {
      console.error('Error fetching config:', error);
      setConfig({ 
        habilitado: false, 
        tipo_captura_seguimiento: 'global',
        ultimo_mes_visible: 'enero',
        ultimo_mes_consulta: 'enero',
        meses_habilitados: {}
      });
    } finally {
      setLoading(false);
    }
  };

  const handleSave = async () => {
    try {
      setLoading(true);
      await axios.put(`/configuracion/seguimiento/${selectedEjercicio}`, config);
      enqueueSnackbar('Configuración guardada correctamente', { variant: 'success' });
    } catch (error) {
      console.error('Error saving config:', error);
      enqueueSnackbar('Error al guardar la configuración', { variant: 'error' });
    } finally {
      setLoading(false);
    }
  };

  const handleMonthChange = (mesId, checked) => {
    setConfig(prev => ({
      ...prev,
      meses_habilitados: {
        ...prev.meses_habilitados,
        [mesId]: checked
      }
    }));
  };

  return (
    <Container maxWidth={false}>
      <Box sx={{ mb: 3 }}>
        <Typography variant="h4" sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
          <Iconify icon="mdi:chart-timeline" width={32} height={32} />
          Permisos sobre el seguimiento de fichas POA
        </Typography>
      </Box>

      <Card>
        <CardContent sx={{ p: 3 }}>
          <Stack spacing={1}>
            
            {/* Ejercicio */}
            <Box sx={{ p: 2, bgcolor: '#f4f6f8', borderRadius: 1 }}>
              <Grid container alignItems="center">
                <Grid size={{ xs: 12, sm: 6 }}>
                  <Typography sx={{ color: 'text.primary', fontWeight: 'bold' }}>Ejercicio</Typography>
                </Grid>
                <Grid size={{ xs: 12, sm: 6 }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                    <FormControl sx={{ minWidth: 150 }}>
                      <Select
                        value={selectedEjercicio}
                        onChange={(e) => setSelectedEjercicio(e.target.value)}
                        size="small"
                        sx={{ bgcolor: 'white' }}
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
                          size="small"
                        />
                      }
                      label={<Typography sx={{ color: 'text.secondary' }}>Habilitar seguimiento POA</Typography>}
                    />
                  </Box>
                </Grid>
              </Grid>
            </Box>

            {/* Captura global / especifica */}
            <Box sx={{ p: 2, bgcolor: 'transparent', borderRadius: 1 }}>
              <Grid container alignItems="center">
                <Grid size={{ xs: 12, sm: 6 }}>
                  <Typography sx={{ color: 'text.primary', fontWeight: 'bold' }}>Permisos sobre la captura de metas principales y complementarias</Typography>
                </Grid>
                <Grid size={{ xs: 12, sm: 6 }}>
                  <RadioGroup
                    row
                    value={config.tipo_captura_seguimiento}
                    onChange={(e) => setConfig({ ...config, tipo_captura_seguimiento: e.target.value })}
                    sx={{ color: 'text.secondary' }}
                  >
                    <FormControlLabel value="global" control={<Radio size="small" />} label="Captura Global" />
                    <FormControlLabel value="especifica" control={<Radio size="small" />} label="Captura Específica" />
                  </RadioGroup>
                </Grid>
              </Grid>
            </Box>

            {/* Meses habilitados */}
            <Box sx={{ p: 2, bgcolor: '#f4f6f8', borderRadius: 1 }}>
              <Grid container alignItems="flex-start">
                <Grid size={{ xs: 12, sm: 6 }}>
                  <Typography sx={{ color: 'text.primary', fontWeight: 'bold', mt: 1 }}>
                    Control de meses habilitados para la captura del seguimiento físico de metas
                  </Typography>
                </Grid>
                <Grid size={{ xs: 12, sm: 6 }}>
                  <Grid container>
                    {[0, 1, 2].map((colIndex) => (
                      <Grid item xs={4} key={`col_${colIndex}`}>
                        <Box sx={{ display: 'flex', flexDirection: 'column' }}>
                          {[0, 3, 6, 9].map((offset) => {
                            const mes = MONTHS[offset + colIndex];
                            return (
                              <FormControlLabel
                                key={mes.id}
                                control={
                                  <Checkbox
                                    size="small"
                                    checked={!!config.meses_habilitados[mes.id]}
                                    onChange={(e) => handleMonthChange(mes.id, e.target.checked)}
                                  />
                                }
                                label={<Typography sx={{ color: 'text.secondary' }}>{mes.short}</Typography>}
                              />
                            );
                          })}
                        </Box>
                      </Grid>
                    ))}
                  </Grid>
                </Grid>
              </Grid>
            </Box>

            {/* Derechos humanos */}
            <Box sx={{ p: 2, bgcolor: 'transparent', borderRadius: 1 }}>
              <Grid container alignItems="center">
                <Grid size={{ xs: 12, sm: 6 }}>
                  <Typography sx={{ color: 'text.primary', fontWeight: 'bold' }}>
                    Control de meses habilitados para la captura del seguimiento al programa de Derechos Humanos
                  </Typography>
                </Grid>
                <Grid size={{ xs: 12, sm: 6 }}>
                  <FormControl sx={{ minWidth: 250 }}>
                    <Select
                      value=""
                      displayEmpty
                      size="small"
                      sx={{ color: 'text.secondary', bgcolor: 'white' }}
                    >
                      <MenuItem value="" disabled>Nothing selected</MenuItem>
                    </Select>
                  </FormControl>
                </Grid>
              </Grid>
            </Box>

            {/* Ultimo mes visible reportes */}
            <Box sx={{ p: 2, bgcolor: '#f4f6f8', borderRadius: 1 }}>
              <Grid container alignItems="center">
                <Grid size={{ xs: 12, sm: 6 }}>
                  <Typography sx={{ color: 'text.primary', fontWeight: 'bold' }}>
                    Último mes visible en la consulta de reportes al seguimiento físico de metas
                  </Typography>
                </Grid>
                <Grid size={{ xs: 12, sm: 6 }}>
                  <FormControl sx={{ minWidth: 250 }}>
                    <Select
                      value={config.ultimo_mes_visible}
                      onChange={(e) => setConfig({ ...config, ultimo_mes_visible: e.target.value })}
                      size="small"
                      displayEmpty
                      sx={{ bgcolor: 'white' }}
                    >
                      <MenuItem value="" disabled>- Seleccione uno -</MenuItem>
                      {MONTHS.map((mes) => (
                        <MenuItem key={`vis_${mes.id}`} value={mes.name.toLowerCase()}>
                          {mes.name}
                        </MenuItem>
                      ))}
                    </Select>
                  </FormControl>
                </Grid>
              </Grid>
            </Box>

            {/* Ultimo mes visible consulta integral */}
            <Box sx={{ p: 2, bgcolor: 'transparent', borderRadius: 1 }}>
              <Grid container alignItems="center">
                <Grid size={{ xs: 12, sm: 6 }}>
                  <Typography sx={{ color: 'text.primary', fontWeight: 'bold' }}>
                    Último mes visible para la consulta integral
                  </Typography>
                </Grid>
                <Grid size={{ xs: 12, sm: 6 }}>
                  <FormControl sx={{ minWidth: 250 }}>
                    <Select
                      value={config.ultimo_mes_consulta}
                      onChange={(e) => setConfig({ ...config, ultimo_mes_consulta: e.target.value })}
                      size="small"
                      displayEmpty
                      sx={{ bgcolor: 'white' }}
                    >
                      <MenuItem value="" disabled>- Seleccione uno -</MenuItem>
                      {MONTHS.map((mes) => (
                        <MenuItem key={`con_${mes.id}`} value={mes.name.toLowerCase()}>
                          {mes.name}
                        </MenuItem>
                      ))}
                    </Select>
                  </FormControl>
                </Grid>
              </Grid>
            </Box>

          </Stack>

          <Box sx={{ display: 'flex', justifyContent: 'flex-end', mt: 3, px: 2 }}>
            <Button
              variant="contained"
              color="success"
              size="large"
              onClick={handleSave}
              disabled={!selectedEjercicio || loading}
              sx={{ boxShadow: 2 }}
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
