import React, { useState, useEffect } from 'react';
import { Box, Typography, CircularProgress, Card } from '@mui/material';
import axios from '../../../../utils/axios';
import useGlobalStore from '../../../../stores/useGlobalStore';
import ReactApexChart from 'react-apexcharts';

export default function GraficasProyectos() {
  const [chartData, setChartData] = useState({ series: [], labels: [] });
  const [loading, setLoading] = useState(true);
  const ejercicio = useGlobalStore((state) => state.ejercicio);

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true);
      try {
        const response = await axios.get('/elaboracion/grafica-proyectos', {
          params: { ejercicio },
        });
        
        const series = response.data.map(item => parseInt(item.value, 10));
        const labels = response.data.map(item => item.name);
        
        setChartData({ series, labels });
      } catch (error) {
        console.error('Error fetching chart data:', error);
      } finally {
        setLoading(false);
      }
    };

    if (ejercicio) {
      fetchData();
    }
  }, [ejercicio]);

  const options = {
    chart: {
      type: 'pie',
      animations: { enabled: true }
    },
    labels: chartData.labels,
    // Modern vibrant colors
    colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#546E7A', '#26a69a', '#D10CE8'],
    tooltip: {
      y: {
        formatter: function (val) {
          return val + " proyectos";
        }
      }
    },
    dataLabels: {
      enabled: true,
      formatter: function (val) {
        return val.toFixed(1) + "%";
      },
      style: {
        fontSize: '14px',
        fontFamily: 'Inter, sans-serif',
        fontWeight: 'bold',
        colors: ['#fff']
      },
      dropShadow: {
        enabled: true,
        top: 1,
        left: 1,
        blur: 1,
        color: '#000',
        opacity: 0.45
      }
    },
    legend: {
      show: true,
      position: 'right',
      fontSize: '14px',
      fontFamily: 'Inter, sans-serif',
      markers: {
        width: 12,
        height: 12,
        radius: 12,
      },
      itemMargin: {
        horizontal: 10,
        vertical: 5
      }
    },
    stroke: {
      show: true,
      colors: ['#ffffff'],
      width: 2,
    }
  };

  if (loading) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', p: 5 }}>
        <CircularProgress />
      </Box>
    );
  }

  if (chartData.series.length === 0) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', p: 5 }}>
        <Typography color="text.secondary">No hay proyectos registrados para este ejercicio.</Typography>
      </Box>
    );
  }

  return (
    <Card sx={{ p: 3, display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
      <Typography variant="h6" sx={{ mb: 3 }}>
        Proyectos por programa
      </Typography>
      
      <Box sx={{ width: '100%', maxWidth: 700 }}>
        <ReactApexChart options={options} series={chartData.series} type="pie" height={400} />
      </Box>
    </Card>
  );
}
