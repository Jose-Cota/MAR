import { useState } from 'react';
import {
  Dialog,
  DialogContent,
  Tabs,
  Tab,
  Box,
  Typography,
  IconButton
} from '@mui/material';
import Chart from 'react-apexcharts';
import Iconify from './Iconify';

function TabPanel(props) {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`simple-tabpanel-${index}`}
      aria-labelledby={`simple-tab-${index}`}
      {...other}
      style={{ height: '100%', minHeight: 400 }}
    >
      {value === index && (
        <Box sx={{ p: 3, height: '100%' }}>
          {children}
        </Box>
      )}
    </div>
  );
}

export default function GraficasModal({ open, onClose }) {
  const [tabIndex, setTabIndex] = useState(0);

  const handleTabChange = (event, newValue) => {
    setTabIndex(newValue);
  };

  // Datos de ejemplo basados en las imágenes proporcionadas
  const labels = ['JEL', 'JLDC', 'JLI', 'JLT', 'PP', 'JIAI', 'JIAT', 'AG', 'PES'];
  
  const pieOptions = {
    labels: labels,
    legend: { position: 'left', offsetX: -20, offsetY: 50 },
    title: { text: 'Pesos', align: 'center', style: { fontSize: '18px', fontWeight: 'bold' } },
    dataLabels: { enabled: false },
    plotOptions: { pie: { expandOnClick: false } },
    colors: ['#c43036', '#2b4759', '#63a3a6', '#d97e68', '#8ccfa8', '#6b9e7b', '#d2963e', '#bca39d', '#6e7075']
  };
  const pieSeries = [15, 12, 10, 10, 15, 10, 10, 10, 8];

  const barOptions = {
    chart: { type: 'bar', stacked: false },
    plotOptions: { bar: { columnWidth: '50%' } },
    xaxis: { categories: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'] },
    legend: { position: 'top' },
    dataLabels: { enabled: false },
    colors: ['#ebdcf2', '#d5bad8', '#ab89b8', '#865b96', '#66337a', '#3e065f', '#e6e6e6', '#cccccc', '#b3b3b3']
  };

  const barSeries = [
    { name: 'JEL', data: [1, 2, 32, 10, 22, 4, 0, 0, 0, 0, 0, 0] },
    { name: 'JLDC', data: [1, 3, 8, 2, 3, 5, 0, 0, 0, 0, 0, 0] },
    { name: 'JLI', data: [0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0] },
    { name: 'JLT', data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0] },
    { name: 'PP', data: [1, 0, 4, 0, 1, 1, 0, 0, 0, 0, 0, 0] },
    { name: 'JIAI', data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0] },
    { name: 'JIAT', data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0] },
    { name: 'AG', data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0] },
    { name: 'PES', data: [0, 2, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0] },
  ];

  return (
    <Dialog open={open} onClose={onClose} maxWidth="lg" fullWidth>
      <Box sx={{ borderBottom: 1, borderColor: 'divider', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <Tabs value={tabIndex} onChange={handleTabChange} sx={{ ml: 2 }}>
          <Tab label="Gráficas del avance" />
          <Tab label="Avances" />
        </Tabs>
        <IconButton onClick={onClose} sx={{ mr: 1 }}>
          <Iconify icon="mdi:close" />
        </IconButton>
      </Box>
      <DialogContent sx={{ p: 0 }}>
        <TabPanel value={tabIndex} index={0}>
          <Box display="flex" justifyContent="center">
            <Chart options={pieOptions} series={pieSeries} type="pie" width={600} />
          </Box>
        </TabPanel>
        <TabPanel value={tabIndex} index={1}>
          <Chart options={barOptions} series={barSeries} type="bar" height={500} />
        </TabPanel>
      </DialogContent>
    </Dialog>
  );
}
