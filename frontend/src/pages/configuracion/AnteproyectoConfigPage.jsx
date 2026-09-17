import React, { useState, useEffect } from 'react';
import {
  Container,
  Box,
  Typography,
  Card,
  CardContent,
  Button,
  Select,
  MenuItem,
  FormControl,
  Snackbar,
  Alert
} from '@mui/material';
import Iconify from '../../components/Iconify';
import axios from '../../utils/axios';

export default function AnteproyectoConfigPage() {
  const [ejercicios, setEjercicios] = useState([]);
  const [selectedEjercicio, setSelectedEjercicio] = useState('');
  const [loading, setLoading] = useState(false);
  const [snackbar, setSnackbar] = useState({ open: false, message: '', variant: 'success' });
  const enqueueSnackbar = (message, { variant }) => setSnackbar({ open: true, message, variant });

  useEffect(() => {
    fetchEjercicios();
  }, []);

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

  const handleGenerate = async () => {
    if(!window.confirm('¿Estás seguro de que deseas generar el Anteproyecto para el año siguiente? Se copiarán todos los catálogos y se configurará la apertura programática.')){
        return;
    }
    
    try {
      setLoading(true);
      const res = await axios.post(`/configuracion/anteproyecto/${selectedEjercicio}`);
      enqueueSnackbar(res.data.message || 'Anteproyecto generado exitosamente', { variant: 'success' });
      // Refetch ejercicios so the new one appears in the dropdown
      fetchEjercicios();
    } catch (error) {
      console.error('Error generando anteproyecto:', error);
      enqueueSnackbar(error.response?.data?.message || 'Error al generar el anteproyecto', { variant: 'error' });
    } finally {
      setLoading(false);
    }
  };

  return (
    <Container maxWidth={false}>
      <Box sx={{ mb: 5 }}>
        <Typography variant="h4" sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
          <Iconify icon="mdi:file-document-outline" width={32} height={32} />
          Configuración de Anteproyecto
        </Typography>
      </Box>

      <Card>
        <CardContent sx={{ p: 4, display: 'flex', justifyContent: 'center', alignItems: 'center', minHeight: 300 }}>
          
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 4, bgcolor: '#f4f6f8', p: 4, borderRadius: 2 }}>
            <FormControl sx={{ minWidth: 200, bgcolor: 'background.paper' }}>
              <Select
                value={selectedEjercicio}
                onChange={(e) => setSelectedEjercicio(e.target.value)}
                displayEmpty
              >
                <MenuItem value="" disabled>- Seleccione uno -</MenuItem>
                {ejercicios.map((ej) => (
                  <MenuItem key={ej.ejercicio_id} value={ej.ejercicio_id}>
                    {ej.ejercicio}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>

            <Button
              variant="outlined"
              color="success"
              onClick={handleGenerate}
              disabled={!selectedEjercicio || loading}
              sx={{ px: 4, py: 1.5 }}
            >
              Generar
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
