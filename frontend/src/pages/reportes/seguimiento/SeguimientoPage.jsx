import { useState, useEffect } from 'react';
import {
  Container,
  Typography,
  Box,
  Tabs,
  Tab,
  Card,
  FormControl,
  Select,
  MenuItem,
  IconButton,
  Tooltip,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  LinearProgress,
  Button
} from '@mui/material';
import Iconify from '../../../components/Iconify';
import axios from '../../../utils/axios';
import ImprimirAvancesModal from '../../../components/ImprimirAvancesModal';
import useGlobalStore from '../../../stores/useGlobalStore';
import Avance from './tabs/Avance';
import GraficasAvance from './tabs/GraficasAvance';

function TabPanel(props) {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`seguimiento-tabpanel-${index}`}
      aria-labelledby={`seguimiento-tab-${index}`}
      {...other}
      style={{ minHeight: 400 }}
    >
      {value === index && (
        <Box sx={{ p: 3 }}>
          {children}
        </Box>
      )}
    </div>
  );
}

export default function SeguimientoPage() {
  const [tabIndex, setTabIndex] = useState(2);
  const [mes, setMes] = useState('Enero');
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(false);
  const [imprimirOpen, setImprimirOpen] = useState(false);
  const ejercicio = useGlobalStore((state) => state.ejercicio);

  useEffect(() => {
    fetchAvances();
  }, [ejercicio, mes]);

  const fetchAvances = async () => {
    setLoading(true);
    try {
      const response = await axios.get('/reportes/seguimiento/avances', {
        params: { ejercicio, mes },
      });
      setData(response.data);
    } catch (error) {
      console.error('Error fetching avances:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleTabChange = (event, newValue) => {
    setTabIndex(newValue);
  };

  
  const handleDownloadExcel = async () => {
    try {
      const response = await axios.get('/reportes/excel/avance-trimestral', {
        params: { ejercicio },
        responseType: 'blob', // Important for file downloads
      });
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `Avance_Trimestral_POA_${ejercicio}.xlsx`);
      document.body.appendChild(link);
      link.click();
      link.remove();
    } catch (error) {
      console.error('Error downloading Excel:', error);
      alert('Error al descargar el archivo Excel.');
    }
  };

  const handleMesChange = (event) => {
    setMes(event.target.value);
  };

  const meses = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
  ];

  // Group data by UR
  const groupedData = data.reduce((acc, curr) => {
    const urKey = `${curr.urg_numero} ${curr.urg_nombre}`;
    if (!acc[urKey]) acc[urKey] = [];
    acc[urKey].push(curr);
    return acc;
  }, {});

  return (
    <Container maxWidth={false}>
      <Box sx={{ mb: 5, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        <Box sx={{ display: 'flex', alignItems: 'center' }}>
          <Iconify icon="mdi:chart-timeline-variant" sx={{ mr: 2, width: 32, height: 32 }} />
          <Typography variant="h4">Seguimiento</Typography>
        </Box>
        <Button 
          variant="contained" 
          color="success" 
          startIcon={<Iconify icon="mdi:microsoft-excel" />}
          onClick={handleDownloadExcel}
        >
          Exportar Trimestral
        </Button>
      </Box>

      <Card>
        <Box sx={{ borderBottom: 1, borderColor: 'divider' }}>
          <Tabs value={tabIndex} onChange={handleTabChange} sx={{ px: 2, bgcolor: 'background.neutral' }}>
            <Tab label="Gráficas del Avance" />
            <Tab label="Avance" />
            <Tab label="Gráficas x UR y PY" />
          </Tabs>
        </Box>

        <TabPanel value={tabIndex} index={0}>
          <GraficasAvance />
        </TabPanel>

        <TabPanel value={tabIndex} index={1}>
          <Avance />
        </TabPanel>

        <TabPanel value={tabIndex} index={2}>
          <Box sx={{ maxWidth: 300, mb: 3 }}>
            <FormControl fullWidth size="small">
              <Select value={mes} onChange={handleMesChange}>
                {meses.map((m) => (
                  <MenuItem key={m} value={m}>{m}</MenuItem>
                ))}
              </Select>
            </FormControl>
          </Box>
          
          <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', mb: 3, position: 'relative' }}>
            <Box sx={{ textAlign: 'center' }}>
              <Typography variant="h5" sx={{ fontWeight: 'normal', color: '#4a4a4a', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                Avance de Proyectos por Unidad Responsable
                <Tooltip title="Imprimir avances">
                  <IconButton onClick={() => setImprimirOpen(true)} sx={{ ml: 1, color: 'text.secondary' }}>
                    <Iconify icon="mdi:printer" />
                  </IconButton>
                </Tooltip>
              </Typography>
              <Typography variant="body2" sx={{ color: 'text.secondary' }}>
                Enero - {mes}
              </Typography>
            </Box>
          </Box>

          <Box sx={{ mt: 2 }}>
            {loading ? (
              <Box sx={{ display: 'flex', height: '100%', justifyContent: 'center', alignItems: 'center' }}>
                <Typography variant="h6" color="textSecondary">Cargando datos reales...</Typography>
              </Box>
            ) : data.length === 0 ? (
              <Box sx={{ display: 'flex', height: '100%', justifyContent: 'center', alignItems: 'center' }}>
                <Box sx={{ textAlign: 'center', p: 5, bgcolor: 'background.neutral', borderRadius: 2, width: '100%', maxWidth: 500 }}>
                  <Iconify icon="mdi:folder-search-outline" sx={{ width: 64, height: 64, color: 'text.disabled', mb: 2 }} />
                  <Typography variant="h6" paragraph>Sin proyectos registrados</Typography>
                  <Typography variant="body2" sx={{ color: 'text.secondary' }}>
                    No se encontraron proyectos para el ejercicio fiscal actual. 
                    Por favor, cambie de ejercicio o registre nuevos proyectos para generar las gráficas.
                  </Typography>
                </Box>
              </Box>
            ) : (
              <TableContainer sx={{ border: 'none', borderRadius: 2, overflow: 'hidden' }}>
                <Table sx={{ border: 'none', '& td, & th': { border: 'none', py: 0.75 } }} size="small">
                  <TableHead>
                    <TableRow sx={{ backgroundColor: '#37474f' }}>
                      <TableCell align="center" sx={{ width: '18%', fontWeight: 'bold', color: '#eceff1', fontSize: '0.8rem', letterSpacing: '0.05em', textTransform: 'uppercase' }}>UR</TableCell>
                      <TableCell align="center" sx={{ width: '76%', fontWeight: 'bold', color: '#eceff1', fontSize: '0.8rem', letterSpacing: '0.05em', textTransform: 'uppercase' }}>Avance del Proyecto</TableCell>
                      <TableCell align="center" sx={{ width: '6%', fontWeight: 'bold', color: '#eceff1', fontSize: '0.8rem', letterSpacing: '0.05em', textTransform: 'uppercase' }}>%</TableCell>
                    </TableRow>
                  </TableHead>
                  <TableBody>
                    {Object.entries(groupedData).map(([urName, proyectos], index) => (
                      <TableRow 
                        key={index} 
                        sx={{ 
                          verticalAlign: 'middle',
                          backgroundColor: index % 2 === 0 ? '#ffffff' : '#e0e0e0',
                          '&:hover': { backgroundColor: index % 2 === 0 ? '#f0f0f0' : '#d6d6d6' },
                        }}
                      >
                        <TableCell align="center" sx={{ color: '#37474f', fontWeight: 600, fontSize: '0.75rem' }}>
                          {urName}
                        </TableCell>
                        <TableCell>
                          {proyectos.map((row, pyIndex) => (
                            <Box key={pyIndex} sx={{ mb: pyIndex < proyectos.length - 1 ? 0.75 : 0, position: 'relative', borderRadius: 1, overflow: 'hidden', backgroundColor: '#e3f2fd' }}>
                              <Box sx={{
                                position: 'absolute',
                                top: 0, left: 0, bottom: 0,
                                width: `${row.avance}%`,
                                backgroundColor: '#0d47a1',
                                borderRadius: 1,
                              }} />
                              <Typography sx={{
                                position: 'relative',
                                zIndex: 1,
                                px: 1.5,
                                py: 0.6,
                                fontSize: '0.72rem',
                                fontWeight: 600,
                                fontFamily: 'monospace',
                                lineHeight: 1.4,
                                color: '#fff',
                                textShadow: '0 0 4px rgba(0,0,0,0.6)',
                                whiteSpace: 'normal',
                              }}>
                                {row.clave} {row.nombre}
                              </Typography>
                            </Box>
                          ))}
                        </TableCell>
                        <TableCell align="center">
                          {proyectos.map((row, pyIndex) => (
                            <Typography key={pyIndex} sx={{ fontSize: '0.65rem', fontWeight: 'bold', color: 'text.secondary', mb: pyIndex < proyectos.length - 1 ? 0.75 : 0, display: 'block' }}>
                              {`${Math.round(row.avance)}%`}
                            </Typography>
                          ))}
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </TableContainer>
            )}
          </Box>
        </TabPanel>
      </Card>

      <ImprimirAvancesModal 
        open={imprimirOpen} 
        onClose={() => setImprimirOpen(false)} 
        mes={mes} 
        groupedData={groupedData}
        ejercicio={ejercicio}
      />
    </Container>
  );
}
