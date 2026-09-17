import React, { useState, useEffect } from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  FormGroup,
  FormControlLabel,
  Checkbox,
  Box,
  Typography,
  CircularProgress,
  Alert,
  Divider
} from '@mui/material';
import axios from '../../../utils/axios';

export default function ModalAlinearProyecto({ open, onClose, proyectoId, onSaved }) {
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  
  const [peiData, setPeiData] = useState(null);
  
  // Arreglo de pei_linea_estrategica_id seleccionados
  const [selectedLineasIds, setSelectedLineasIds] = useState([]);
  
  // Arreglo de pei_objetivo_estrategico_id seleccionados
  const [selectedObjetivosIds, setSelectedObjetivosIds] = useState([]);

  useEffect(() => {
    if (open && proyectoId) {
      fetchPeiData();
    } else {
      resetState();
    }
  }, [open, proyectoId]);

  const resetState = () => {
    setPeiData(null);
    setSelectedLineasIds([]);
    setSelectedObjetivosIds([]);
    setError('');
  };

  const fetchPeiData = async () => {
    setLoading(true);
    setError('');
    try {
      const response = await axios.get(`/proyectos/${proyectoId}/ficha-descriptiva`);
      const pei = response.data.pei;
      setPeiData(pei);
      
      if (pei && pei.alineaciones && pei.alineaciones.length > 0) {
        // Encontrar todos los linea_id (Objetivos Estratégicos) únicos de las alineaciones guardadas
        const lineasIds = [...new Set(pei.alineaciones.map(al => al.linea_id))];
        setSelectedLineasIds(lineasIds);
        
        // Encontrar todos los objetivo_id (Líneas Estratégicas) de las alineaciones guardadas
        const objIds = pei.alineaciones.map(al => al.objetivo_id);
        setSelectedObjetivosIds(objIds);
      } else {
        setSelectedLineasIds([]);
        setSelectedObjetivosIds([]);
      }
    } catch (err) {
      console.error("Error cargando PEI del proyecto", err);
      setError('Error al cargar la información del proyecto.');
    } finally {
      setLoading(false);
    }
  };

  const handleLineaChange = (id) => {
    setSelectedLineasIds(prev => {
      const isSelected = prev.includes(id);
      if (isSelected) {
        // Si deseleccionamos el Objetivo Estratégico, también debemos deseleccionar sus Líneas Estratégicas asociadas
        const activeLinea = peiData?.lineas?.find(l => l.id === id);
        const objIdsToRemove = activeLinea ? activeLinea.objetivos.map(o => o.id) : [];
        setSelectedObjetivosIds(prevObjs => prevObjs.filter(objId => !objIdsToRemove.includes(objId)));
        
        return prev.filter(item => item !== id);
      } else {
        return [...prev, id];
      }
    });
  };

  const handleObjetivoChange = (id) => {
    setSelectedObjetivosIds(prev => {
      if (prev.includes(id)) {
        return prev.filter(item => item !== id);
      } else {
        return [...prev, id];
      }
    });
  };

  const handleSave = async () => {
    if (selectedLineasIds.length === 0) {
      setError('Debes seleccionar al menos un Objetivo Estratégico.');
      return;
    }
    if (selectedObjetivosIds.length === 0) {
      setError('Debes seleccionar al menos una Línea Estratégica.');
      return;
    }

    setSaving(true);
    setError('');
    try {
      // El backend ahora solo requiere los objetivos_estrategicos, ya que puede deducir la linea de la BD
      await axios.put(`/proyectos/${proyectoId}/alineacion-pei`, {
        objetivos_estrategicos: selectedObjetivosIds
      });
      if (onSaved) onSaved();
      onClose();
    } catch (err) {
      setError(err.response?.data?.message || 'Error al guardar la alineación.');
    } finally {
      setSaving(false);
    }
  };

  // Obtener las Líneas Estratégicas (UI) para los Objetivos Estratégicos (UI) seleccionados
  const activeLineas = peiData?.lineas?.filter(l => selectedLineasIds.includes(l.id)) || [];

  return (
    <Dialog open={open} onClose={onClose} fullWidth maxWidth="md">
      <DialogTitle sx={{ backgroundColor: '#2065D1', color: 'white' }}>Alinear Proyecto</DialogTitle>
      <DialogContent sx={{ mt: 2 }}>
        {loading ? (
          <Box display="flex" justifyContent="center" my={4}>
            <CircularProgress />
          </Box>
        ) : !peiData?.programa ? (
          <Alert severity="warning">No existe un Programa Institucional (PEI) para el ejercicio de este proyecto.</Alert>
        ) : (
          <Box sx={{ mt: 1 }}>
            {error && <Alert severity="error" sx={{ mb: 2 }}>{error}</Alert>}
            
            <Typography variant="h6" sx={{ mb: 2, color: '#212B36' }}>
              1. Selecciona los Objetivos Estratégicos
            </Typography>
            
            <Box sx={{ border: '1px solid #e0e0e0', borderRadius: 1, p: 2, bgcolor: '#f9fafb', mb: 4 }}>
              <FormGroup>
                {peiData?.lineas?.map((linea) => (
                  <FormControlLabel
                    key={linea.id}
                    control={
                      <Checkbox 
                        checked={selectedLineasIds.includes(linea.id)} 
                        onChange={() => handleLineaChange(linea.id)}
                        color="primary"
                      />
                    }
                    label={
                      <Typography variant="body1" sx={{ lineHeight: 1.4, mb: 1, fontWeight: selectedLineasIds.includes(linea.id) ? 'bold' : 'normal' }}>
                        {linea.nombre}
                      </Typography>
                    }
                    sx={{ alignItems: 'flex-start', mb: 1 }}
                  />
                ))}
                {(!peiData?.lineas || peiData.lineas.length === 0) && (
                  <Typography variant="body2" color="textSecondary">
                    No hay objetivos estratégicos disponibles.
                  </Typography>
                )}
              </FormGroup>
            </Box>

            {selectedLineasIds.length > 0 && (
              <>
                <Typography variant="h6" sx={{ mb: 2, color: '#212B36' }}>
                  2. Selecciona las Líneas Estratégicas
                </Typography>
                <Box sx={{ border: '1px solid #e0e0e0', borderRadius: 1, p: 2, bgcolor: '#f9fafb' }}>
                  {activeLineas.map((linea, index) => (
                    <Box key={linea.id} sx={{ mb: index !== activeLineas.length - 1 ? 3 : 0 }}>
                      <Typography variant="subtitle1" sx={{ color: '#2065D1', fontWeight: 'bold', mb: 1 }}>
                        {linea.nombre}
                      </Typography>
                      <Divider sx={{ mb: 2 }} />
                      <FormGroup sx={{ pl: 2 }}>
                        {linea.objetivos.map((obj) => (
                          <FormControlLabel
                            key={obj.id}
                            control={
                              <Checkbox 
                                checked={selectedObjetivosIds.includes(obj.id)} 
                                onChange={() => handleObjetivoChange(obj.id)}
                                color="secondary"
                              />
                            }
                            label={
                              <Typography variant="body2" sx={{ lineHeight: 1.4, mb: 1 }}>
                                {obj.nombre}
                              </Typography>
                            }
                            sx={{ alignItems: 'flex-start', mb: 1 }}
                          />
                        ))}
                        {linea.objetivos.length === 0 && (
                          <Typography variant="body2" color="textSecondary">
                            No hay líneas estratégicas configuradas para este objetivo.
                          </Typography>
                        )}
                      </FormGroup>
                    </Box>
                  ))}
                </Box>
              </>
            )}
          </Box>
        )}
      </DialogContent>
      <DialogActions sx={{ p: 3 }}>
        <Button onClick={onClose} color="inherit" variant="outlined">Cancelar</Button>
        <Button 
          onClick={handleSave} 
          color="primary" 
          variant="contained" 
          disabled={loading || saving || !peiData?.programa}
        >
          {saving ? 'Guardando...' : 'Guardar Alineación'}
        </Button>
      </DialogActions>
    </Dialog>
  );
}
