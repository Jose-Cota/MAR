import React, { useState, useEffect } from 'react';
import {
  Container,
  Box,
  Typography,
  Tabs,
  Tab,
  Card,
} from '@mui/material';
import useGlobalStore from '../../../stores/useGlobalStore';
import Iconify from '../../../components/Iconify';

// Pestañas (Componentes hijos que crearemos)
import GraficasProyectos from './tabs/GraficasProyectos';
import AperturaProgramatica from './tabs/AperturaProgramatica';
import FichasPoa from './tabs/FichasPoa';

function CustomTabPanel(props) {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`simple-tabpanel-${index}`}
      aria-labelledby={`simple-tab-${index}`}
      {...other}
    >
      {value === index && (
        <Box sx={{ p: 3 }}>
          {children}
        </Box>
      )}
    </div>
  );
}

function a11yProps(index) {
  return {
    id: `simple-tab-${index}`,
    'aria-controls': `simple-tabpanel-${index}`,
  };
}

export default function ElaboracionPage() {
  const [value, setValue] = useState(0);
  const ejercicio = useGlobalStore((state) => state.ejercicio);

  const handleChange = (event, newValue) => {
    setValue(newValue);
  };

  return (
    <Container maxWidth={false}>
      <Box sx={{ mb: 5 }}>
        <Typography variant="h4" sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
          <Iconify icon="mdi:file-document-edit-outline" width={32} height={32} />
          Elaboración
        </Typography>
      </Box>

      <Card>
        <Box sx={{ borderBottom: 1, borderColor: 'divider' }}>
          <Tabs value={value} onChange={handleChange} aria-label="Elaboracion Tabs">
            <Tab label="Gráficas de Proyectos" {...a11yProps(0)} />
            <Tab label="Apertura Programática" {...a11yProps(1)} />
            <Tab label="Fichas POA" {...a11yProps(2)} />
          </Tabs>
        </Box>
        <CustomTabPanel value={value} index={0}>
          <GraficasProyectos />
        </CustomTabPanel>
        <CustomTabPanel value={value} index={1}>
          <AperturaProgramatica />
        </CustomTabPanel>
        <CustomTabPanel value={value} index={2}>
          <FichasPoa />
        </CustomTabPanel>
      </Card>
    </Container>
  );
}
