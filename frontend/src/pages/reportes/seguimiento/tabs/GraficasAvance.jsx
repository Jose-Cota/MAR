import React, { useState, useEffect } from 'react';
import { Box, Typography, FormControl, Select, MenuItem, CircularProgress } from '@mui/material';
import Chart from 'react-apexcharts';
import axios from '../../../../utils/axios';
import useGlobalStore from '../../../../stores/useGlobalStore';

const mesesNombres = [
  'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
  'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

export default function GraficasAvance() {
  const [mes, setMes] = useState('Enero');
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(false);
  const ejercicio = useGlobalStore((state) => state.ejercicio);

  useEffect(() => {
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
    if (ejercicio) {
      fetchAvances();
    }
  }, [ejercicio, mes]);

  const handleMesChange = (event) => {
    setMes(event.target.value);
  };

  const chartOptions = {
    chart: {
      type: 'bar',
      toolbar: { show: false }
    },
    plotOptions: {
      bar: {
        horizontal: true,
        barHeight: '15%',
        dataLabels: {
          position: 'right'
        }
      }
    },
    colors: ['#4b227c'], // Solid purple gradient color from the screenshot
    dataLabels: {
      enabled: true,
      textAnchor: 'start',
      style: {
        colors: ['#333'],
        fontSize: '12px',
        fontWeight: 'bold',
      },
      formatter: function (val, opt) {
        return val + "%";
      },
      offsetX: 0,
      background: {
        enabled: true,
        color: '#fff',
        foreColor: '#000',
        padding: 4,
        borderRadius: 2,
        borderWidth: 1,
        borderColor: '#ccc',
        opacity: 1,
        dropShadow: {
          enabled: true,
          top: 1,
          left: 1,
          blur: 1,
          color: '#000',
          opacity: 0.2
        }
      }
    },
    xaxis: {
      categories: data.map(item => item.clave),
      max: 100,
      labels: {
        show: false
      },
      axisBorder: {
        show: false
      },
      axisTicks: {
        show: false
      }
    },
    yaxis: {
      labels: {
        style: {
          colors: '#666',
          fontSize: '11px',
          fontFamily: 'monospace'
        }
      }
    },
    grid: {
      xaxis: {
        lines: { show: true }
      },
      yaxis: {
        lines: { show: false }
      }
    }
  };

  const chartSeries = [
    {
      name: 'Avance',
      data: data.map(item => Math.round(item.avance))
    }
  ];

  return (
    <Box sx={{ p: 2 }}>
      {/* Selector de Mes */}
      <Box sx={{ maxWidth: 300, mb: 4 }}>
        <FormControl fullWidth size="small">
          <Select value={mes} onChange={handleMesChange}>
            {mesesNombres.map((m) => (
              <MenuItem key={m} value={m}>{m}</MenuItem>
            ))}
          </Select>
        </FormControl>
      </Box>

      {/* Título de la Gráfica */}
      <Box sx={{ textAlign: 'center', mb: 3 }}>
        <Typography variant="h5" sx={{ color: '#444', mb: 1 }}>Avance de Proyectos</Typography>
        <Typography variant="body2" sx={{ color: 'text.secondary' }}>Enero - {mes}</Typography>
      </Box>

      {/* CSS Overrides para forzar el fondo blanco en las etiquetas de ApexCharts */}
      <style>
        {`
          .apexcharts-data-labels rect {
            fill: #ffffff !important;
          }
          .apexcharts-data-labels text {
            fill: #000000 !important;
          }
        `}
      </style>

      {/* Gráfica */}
      {loading ? (
        <Box sx={{ display: 'flex', justifyContent: 'center', p: 5 }}>
          <CircularProgress />
        </Box>
      ) : data.length === 0 ? (
        <Typography align="center" color="text.secondary">No hay proyectos para graficar.</Typography>
      ) : (
        <Box sx={{ 
          bgcolor: 'white', 
          p: 2, 
          borderRadius: 1, 
          border: '1px solid #f0f0f0',
          minHeight: data.length * 40 + 100 // Dynamic height based on number of items
        }}>
          <Chart 
            options={chartOptions} 
            series={chartSeries} 
            type="bar" 
            height={Math.max(400, data.length * 40 + 50)} 
          />
        </Box>
      )}
    </Box>
  );
}
