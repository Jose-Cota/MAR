import React, { useState, useEffect } from 'react';
import {
  Box,
  Typography,
  Card,
  Grid,
  MenuItem,
  Select,
  InputLabel,
  FormControl,
  Button,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  CircularProgress
} from '@mui/material';
import FileDownloadIcon from '@mui/icons-material/FileDownload';
import axios from '../../../../utils/axios';
import useGlobalStore from '../../../../stores/useGlobalStore';

export default function AperturaProgramatica() {
  const [loading, setLoading] = useState(false);
  const [loadingData, setLoadingData] = useState(false);
  
  // Catalogs for dropdowns
  const [programas, setProgramas] = useState([]);
  const [subprogramas, setSubprogramas] = useState([]);
  const [proyectos, setProyectos] = useState([]);
  const [tiposMeta] = useState(['principal', 'complementaria']);
  const [metas, setMetas] = useState([]);

  // Selected values
  const [programaId, setProgramaId] = useState('');
  const [subprogramaId, setSubprogramaId] = useState('');
  const [proyectoId, setProyectoId] = useState('');
  const [tipoMeta, setTipoMeta] = useState('');
  const [metaId, setMetaId] = useState('');
  const [unidadMedida, setUnidadMedida] = useState('');

  // Table data
  const [mesesData, setMesesData] = useState(null);

  const ejercicio = useGlobalStore((state) => state.ejercicio);

  useEffect(() => {
    // Fetch initial programs
    const fetchProgramas = async () => {
      setLoading(true);
      try {
        const response = await axios.get('/programas', {
          params: { ejercicio }
        });
        setProgramas(response.data.data || response.data);
      } catch (error) {
        console.error('Error fetching programas', error);
      } finally {
        setLoading(false);
      }
    };
    if (ejercicio) {
      fetchProgramas();
    } else {
      setProgramas([]);
    }
  }, [ejercicio]);

  useEffect(() => {
    // Fetch metas whenever project or type changes
    const fetchMetas = async () => {
      try {
        const response = await axios.get('/elaboracion/metas-proyecto', {
          params: { proyecto_id: proyectoId, tipo: tipoMeta }
        });
        setMetas(response.data);
      } catch (error) {
        console.error('Error fetching metas', error);
      }
    };

    if (proyectoId && tipoMeta) {
      fetchMetas();
    } else {
      setMetas([]);
      setMetaId('');
      setUnidadMedida('');
    }
  }, [proyectoId, tipoMeta]);

  useEffect(() => {
    // Fetch table data whenever a meta or exercise changes
    const fetchApertura = async () => {
      if (!metaId) {
        setMesesData(null);
        return;
      }
      setLoadingData(true);
      try {
        const response = await axios.get('/elaboracion/apertura-programatica', {
          params: {
            ejercicio,
            meta_id: metaId
          }
        });
        setMesesData(response.data);
        
        // Find selected meta to set unidad de medida
        const metaSelected = metas.find(m => m.meta_id === metaId);
        if (metaSelected) {
          setUnidadMedida(metaSelected.unidad_medida);
        }
      } catch (error) {
        console.error('Error fetching apertura programatica', error);
      } finally {
        setLoadingData(false);
      }
    };

    if (ejercicio) {
      fetchApertura();
    }
  }, [ejercicio, metaId, metas]);

  // Handlers for cascades
  const handleProgramaChange = async (e) => {
    const pId = e.target.value;
    setProgramaId(pId);
    setSubprogramaId('');
    setProyectoId('');
    setTipoMeta('');
    setMetaId('');
    setUnidadMedida('');
    if (pId) {
      try {
        const response = await axios.get('/elaboracion/subprogramas', {
          params: { programa_id: pId }
        });
        setSubprogramas(response.data);
      } catch (error) {
        console.error('Error fetching subprogramas', error);
      }
    } else {
      setSubprogramas([]);
    }
  };

  const handleSubprogramaChange = async (e) => {
    const sId = e.target.value;
    setSubprogramaId(sId);
    setProyectoId('');
    setTipoMeta('');
    setMetaId('');
    setUnidadMedida('');
    if (sId) {
      try {
        const response = await axios.get('/elaboracion/proyectos', {
          params: { subprograma_id: sId }
        });
        setProyectos(response.data);
      } catch (error) {
        console.error('Error fetching proyectos', error);
      }
    } else {
      setProyectos([]);
    }
  };

  const handleProyectoChange = (e) => {
    setProyectoId(e.target.value);
    setTipoMeta('');
    setMetaId('');
    setUnidadMedida('');
  };

  const handleTipoMetaChange = (e) => {
    setTipoMeta(e.target.value);
    setMetaId('');
    setUnidadMedida('');
  };

  const handleMetaChange = (e) => {
    setMetaId(e.target.value);
  };

  const handleExportExcel = async () => {
    try {
      const response = await axios.get('/elaboracion/apertura-programatica/exportar', {
        params: { ejercicio, meta_id: metaId },
        responseType: 'blob', // Important for downloading files
      });
      
      // In a real scenario, trigger download
      alert('La exportación de Excel está en construcción.');
    } catch (error) {
      console.error('Error exporting Excel', error);
      alert('Error al generar Excel');
    }
  };

  const monthNames = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
  ];

  return (
    <Box>
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 3 }}>
        <Typography variant="h6">Reportes predeterminados</Typography>
        <Button 
          variant="contained" 
          color="success" 
          startIcon={<FileDownloadIcon />}
          onClick={handleExportExcel}
        >
          Generar Excel
        </Button>
      </Box>

      <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
        Apertura Programática
      </Typography>

      <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 2, mb: 4 }}>
        <Box sx={{ width: { xs: '100%', md: '48%' } }}>
          <FormControl fullWidth>
            <InputLabel id="programa-label">Programa</InputLabel>
            <Select
              labelId="programa-label"
              value={programaId}
              label="Programa"
              onChange={handleProgramaChange}
            >
              <MenuItem value=""><em>Todos los programas</em></MenuItem>
              {programas.map((prog) => (
                <MenuItem key={prog.programa_id} value={prog.programa_id}>
                  {prog.numero} - {prog.nombre}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
        </Box>
        
        <Box sx={{ width: { xs: '100%', md: '48%' } }}>
          <FormControl fullWidth disabled={!programaId}>
            <InputLabel id="subprograma-label">Subprograma</InputLabel>
            <Select
              labelId="subprograma-label"
              value={subprogramaId}
              label="Subprograma"
              onChange={handleSubprogramaChange}
            >
              <MenuItem value=""><em>Todos los subprogramas</em></MenuItem>
              {subprogramas.map((subp) => (
                <MenuItem key={subp.subprograma_id} value={subp.subprograma_id}>
                  {subp.numero} - {subp.nombre}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
        </Box>

        <Box sx={{ width: { xs: '100%', md: '48%' } }}>
          <FormControl fullWidth disabled={!subprogramaId}>
            <InputLabel id="proyecto-label">Proyecto</InputLabel>
            <Select
              labelId="proyecto-label"
              value={proyectoId}
              label="Proyecto"
              onChange={handleProyectoChange}
            >
              <MenuItem value=""><em>Selecciona un proyecto</em></MenuItem>
              {proyectos.map((proy) => (
                <MenuItem key={proy.proyecto_id} value={proy.proyecto_id}>
                  {proy.numero} - {proy.nombre}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
        </Box>
        
        <Box sx={{ width: { xs: '100%', md: '48%' } }}>
          <FormControl fullWidth disabled={!proyectoId}>
            <InputLabel id="tipo-meta-label">Tipo de Meta</InputLabel>
            <Select
              labelId="tipo-meta-label"
              value={tipoMeta}
              label="Tipo de Meta"
              onChange={handleTipoMetaChange}
            >
              <MenuItem value=""><em>Selecciona un tipo</em></MenuItem>
              {tiposMeta.map((tipo) => (
                <MenuItem key={tipo} value={tipo} sx={{ textTransform: 'capitalize' }}>
                  {tipo}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
        </Box>

        <Box sx={{ width: '100%' }}>
          <FormControl fullWidth disabled={!tipoMeta || metas.length === 0}>
            <InputLabel id="meta-label">Meta</InputLabel>
            <Select
              labelId="meta-label"
              value={metaId}
              label="Meta"
              onChange={handleMetaChange}
            >
              <MenuItem value=""><em>Selecciona una meta</em></MenuItem>
              {metas.map((m) => (
                <MenuItem key={m.meta_id} value={m.meta_id}>
                  {m.numero}. {m.meta}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
        </Box>
      </Box>

      <Typography variant="h6" sx={{ mb: 1 }}>
        Periodo: Enero - Diciembre {ejercicio}
      </Typography>
      
      {unidadMedida && (
        <Typography variant="subtitle1" sx={{ mb: 2, fontWeight: 'medium' }}>
          Unidad Medida: {unidadMedida}
        </Typography>
      )}

      {loadingData ? (
        <Box sx={{ display: 'flex', justifyContent: 'center', p: 3 }}>
          <CircularProgress />
        </Box>
      ) : mesesData ? (
        <TableContainer component={Paper} variant="outlined">
          <Table size="small">
            <TableHead sx={{ bgcolor: 'background.neutral' }}>
              <TableRow>
                <TableCell></TableCell>
                {monthNames.map((mes, idx) => (
                  <TableCell key={idx} align="center" sx={{ fontWeight: 'bold' }}>
                    {mes}
                  </TableCell>
                ))}
                <TableCell align="center" sx={{ fontWeight: 'bold' }}>Total</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              <TableRow>
                <TableCell sx={{ fontWeight: 'bold' }}>Programado</TableCell>
                {Object.keys(mesesData.meses).map((mesKey) => (
                  <TableCell key={mesKey} align="center">
                    {mesesData.meses[mesKey]}
                  </TableCell>
                ))}
                <TableCell align="center" sx={{ fontWeight: 'bold' }}>
                  {mesesData.total}
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </TableContainer>
      ) : (
        <Typography color="text.secondary">No hay datos para mostrar.</Typography>
      )}
    </Box>
  );
}
