import { useEffect, useState, Fragment } from 'react';
import './FichaDescriptiva.css';
import {
  Alert,
  Box,
  CircularProgress,
  Dialog,
  DialogTitle,
  DialogActions,
  DialogContent,
  IconButton,
  Button,
  Paper,
  Stack,
  FormControl,
  InputLabel,
  MenuItem,
  Select,
  Tabs,
  Tab,
  Tooltip,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Typography,
  Snackbar,
} from '@mui/material';
import axios from '../utils/axios';
import useAuth from '../hooks/useAuth';
import Iconify from './Iconify';
import ConfirmDeleteDialog from './ui/ConfirmDeleteDialog';
import MetaDescriptivaDialog from './MetaDescriptivaDialog';
import ActividadDescriptivaDialog from './ActividadDescriptivaDialog';
import IndicadorDialog from './IndicadorDialog';
import ProyectoBitacoraModal from './ProyectoBitacoraModal';
import BitacoraHistorialModal from './BitacoraHistorialModal';
import DatosProyectoDialog from './DatosProyectoDialog';
import ResponsablesDialog from './ResponsablesDialog';
import AutorizanteDialog from './AutorizanteDialog';

const MONTH_LABELS = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

function TabPanel({ children, value, index }) {
  return value === index ? <Box sx={{ pt: 1 }}>{children}</Box> : null;
}

function SectionCard({ title, children, action }) {
  return (
    <Box sx={{ border: '1px solid #0d2f66', borderRadius: 1, overflow: 'hidden', bgcolor: '#fff' }}>
      <Box sx={{ bgcolor: '#0d2f66', color: '#fff', px: 2, py: 0.25, display: 'flex', alignItems: 'center', justifyContent: action ? 'space-between' : 'center', minHeight: 28 }}>
        <Typography variant="subtitle2" sx={{ fontWeight: 700, letterSpacing: 0.3, fontSize: '0.8rem' }}>
          {title}
        </Typography>
        {action && action}
      </Box>
      <Box sx={{ p: 0 }}>{children}</Box>
    </Box>
  );
}

function FieldRow({ label, value }) {
  return (
    <Box
      sx={{
        display: 'grid',
        gridTemplateColumns: { xs: '1fr', md: '300px 1fr' },
        borderTop: '1px solid #0d2f66',
      }}
    >
      <Box
        sx={{
          bgcolor: '#0d2f66',
          color: '#fff',
          px: 1.5,
          py: 0.25,
          fontWeight: 700,
          display: 'flex',
          alignItems: 'center',
          fontSize: '0.75rem'
        }}
      >
        {label}
      </Box>
      <Box
        sx={{
          px: 1.5,
          py: 0.25,
          minHeight: 24,
          borderLeft: { md: '1px solid #0d2f66' },
          whiteSpace: 'pre-wrap',
          display: 'flex',
          alignItems: 'center',
          fontSize: '0.75rem'
        }}
      >
        {value || 'Sin información'}
      </Box>
    </Box>
  );
}

function TextBlock({ label, value, minHeight = 72 }) {
  return (
    <Box sx={{ border: '1px solid #0d2f66', borderRadius: 1, overflow: 'hidden', bgcolor: '#fff' }}>
      <Box sx={{ bgcolor: '#0d2f66', color: '#fff', px: 2, py: 0.25, textAlign: 'center' }}>
        <Typography variant="subtitle2" sx={{ fontWeight: 700, fontSize: '0.8rem' }}>
          {label}
        </Typography>
      </Box>
      <Box sx={{ px: 1.5, py: 0.75, minHeight, whiteSpace: 'pre-wrap', fontSize: '0.75rem' }}>
        {value || 'Sin información'}
      </Box>
    </Box>
  );
}

function formatNumber(value) {
  if (value === null || value === undefined || value === '') {
    return '0';
  }

  return new Intl.NumberFormat('es-MX', { maximumFractionDigits: 2 }).format(Number(value));
}

export default function FichaDescriptivaModal({ open, onClose, proyectoId }) {
  const { hasRole } = useAuth();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [data, setData] = useState(null);
  const [selectedResponsableFicha, setSelectedResponsableFicha] = useState('');
  const [selectedJustificacion, setSelectedJustificacion] = useState('');
  const [selectedDescripcion, setSelectedDescripcion] = useState('');
  const [selectedObjetivo, setSelectedObjetivo] = useState('');
  const [selectedProyectoNombre, setSelectedProyectoNombre] = useState('');
  const [selectedLineaId, setSelectedLineaId] = useState('');
  const [selectedObjetivoId, setSelectedObjetivoId] = useState('');
  const [selectedPuestoResponsableFicha, setSelectedPuestoResponsableFicha] = useState('');
  const [selectedAutorizanteNombre, setSelectedAutorizanteNombre] = useState('');
  const [selectedAutorizantePuesto, setSelectedAutorizantePuesto] = useState('');
  const [savingChanges, setSavingChanges] = useState(false);
  const [metaDialogOpen, setMetaDialogOpen] = useState(false);
  const [editingMeta, setEditingMeta] = useState(null);
  
  const [datosProyectoDialogOpen, setDatosProyectoDialogOpen] = useState(false);
  const [responsablesDialogOpen, setResponsablesDialogOpen] = useState(false);
  const [autorizanteDialogOpen, setAutorizanteDialogOpen] = useState(false);
  const [deleteConfirmOpen, setDeleteConfirmOpen] = useState(false);
  const [deleteProjectConfirmOpen, setDeleteProjectConfirmOpen] = useState(false);
  const [deletingMeta, setDeletingMeta] = useState(null);
  const [deletingActividad, setDeletingActividad] = useState(null);
  const [historialOpen, setHistorialOpen] = useState(false);
  const [actividadDialogOpen, setActividadDialogOpen] = useState(false);
  const [editingActividad, setEditingActividad] = useState(null);
  const [indicadorDialogOpen, setIndicadorDialogOpen] = useState(false);
  const [editingIndicador, setEditingIndicador] = useState(null);
  const [selectedMetaForIndicador, setSelectedMetaForIndicador] = useState(null);
  const [metaPrincipalForIndicador, setMetaPrincipalForIndicador] = useState(null);
  const [tabValue, setTabValue] = useState(0);
  const [collapsedMetas, setCollapsedMetas] = useState(new Set());
  const [bitacoraOpen, setBitacoraOpen] = useState(false);
  const [bitacoraAction, setBitacoraAction] = useState('');
  const [alertaMaxima, setAlertaMaxima] = useState(false);
  const [alertaValidacionOpen, setAlertaValidacionOpen] = useState(false);
  const [validacionErrores, setValidacionErrores] = useState([]);
  const [validacionWarnings, setValidacionWarnings] = useState([]);

  const handleOpenBitacora = (action) => {
    setValidacionWarnings([]);
    
    if (action === 'enviar_validador') {
      const warnings = [];
      const metas = data?.metas || [];
      const comps = metas.filter(m => m.tipo !== 'principal').sort((a, b) => {
        return (Number(a.orden) || Number(a.id) || 0) - (Number(b.orden) || Number(b.id) || 0);
      });
      const principles = metas.filter(m => m.tipo === 'principal');

      comps.forEach((mc, idx) => {
        if (!mc.indicadores || mc.indicadores.length === 0) {
          warnings.push(`Falta capturar el indicador para la Meta Complementaria ${idx+1}.`);
        }
      });

      const acts = data?.actividades || [];
      if (acts.length === 0) {
        warnings.push('Falta capturar al menos una Actividad Sustantiva.');
      }

      if (principles.length > 0) {
        const sum = comps.reduce((acc, mc) => acc + (Number(mc.peso) || 0), 0);
        if (Math.round(sum * 100) / 100 !== 100) {
          warnings.push('La meta principal no suma exactamente 100%.');
        }
      } else {
        warnings.push('No hay Meta Principal registrada.');
      }

      if (warnings.length > 0) {
        setValidacionWarnings(warnings);
      }
    }

    if (action === 'enviar_dpyrf') {
      const errores = [];
      
      if (!selectedResponsableFicha || selectedResponsableFicha.trim() === '') {
        errores.push('Falta capturar el Nombre del Responsable de la ficha (sección 1. Identificación de responsables).');
      }
      if (!selectedPuestoResponsableFicha || selectedPuestoResponsableFicha.trim() === '') {
        errores.push('Falta capturar el Puesto del Responsable de la ficha (sección 1. Identificación de responsables).');
      }
      if (!selectedJustificacion || selectedJustificacion.trim() === '') {
        errores.push('Falta capturar el Objetivo de la UR (sección 1. Identificación de responsables).');
      }
      
      if (!selectedAutorizanteNombre || selectedAutorizanteNombre.trim() === '') {
        errores.push('Falta capturar el Nombre del Autorizante (sección 8. Autorizante).');
      }
      if (!selectedAutorizantePuesto || selectedAutorizantePuesto.trim() === '') {
        errores.push('Falta capturar el Puesto del Autorizante (sección 8. Autorizante).');
      }

      const metas = data?.metas || [];
      const principles = metas.filter(m => m.tipo === 'principal');
      const comps = metas.filter(m => m.tipo !== 'principal').sort((a, b) => {
        return (Number(a.orden) || Number(a.id) || 0) - (Number(b.orden) || Number(b.id) || 0);
      });
      const sum = comps.reduce((acc, mc) => acc + (Number(mc.peso) || 0), 0);

      if (principles.length === 0) {
        errores.push('Falta capturar la Meta Principal del proyecto.');
      }
      
      if (comps.length === 0) {
        errores.push('Falta capturar Metas Complementarias.');
      } else if (Math.round(sum * 100) / 100 !== 100) {
        errores.push('La suma de porcentajes de las Metas Complementarias debe ser exactamente 100%.');
      }

      comps.forEach((mc, idx) => {
        if (!mc.indicadores || mc.indicadores.length === 0) {
          errores.push(`Falta capturar el indicador para la Meta Complementaria ${idx+1}.`);
        }
      });

      const acts = data?.actividades || [];
      if (acts.length === 0) {
        errores.push('Falta capturar al menos una Actividad Sustantiva.');
      }

      if (errores.length > 0) {
        setValidacionErrores(errores);
        setAlertaValidacionOpen(true);
        return;
      }
    }

    setBitacoraAction(action);
    setBitacoraOpen(true);
  };

  const toggleCollapseMeta = (metaId) => {
    const idStr = String(metaId);
    setCollapsedMetas((prev) => {
      const newSet = new Set(prev);
      if (newSet.has(idStr)) {
        newSet.delete(idStr);
      } else {
        newSet.add(idStr);
      }
      return newSet;
    });
  };

  const fetchFicha = async (silent = false) => {
    if (!proyectoId) {
      return;
    }

    if (!silent) {
      setLoading(true);
      setData(null);
    }
    setError('');

    try {
      const ts = new Date().getTime();
      const response = await axios.get(`/proyectos/${proyectoId}/ficha-descriptiva?t=${ts}`);
        setData(response.data);
        
        if (response.data.proyecto) {
          setSelectedResponsableFicha(response.data.proyecto.responsable_ficha || '');
          setSelectedPuestoResponsableFicha(response.data.proyecto.puesto_responsable_ficha || '');
          setSelectedAutorizanteNombre(response.data.proyecto.autorizante_nombre || '');
          setSelectedAutorizantePuesto(response.data.proyecto.autorizante_puesto || '');
          setSelectedJustificacion(response.data.proyecto.justificacion || '');
        }
      setSelectedDescripcion(response.data?.proyecto?.descripcion || '');
      setSelectedProyectoNombre(response.data?.proyecto?.proyecto_nombre || '');
      setSelectedObjetivo(response.data?.proyecto?.objetivo || '');
      let fetchedLineaId = response.data?.pei?.alineacion?.linea_id?.toString() || '';
      let fetchedObjId = response.data?.pei?.alineacion?.objetivo_id?.toString() || '';
      
      const lineas = response.data?.pei?.lineas || [];
      
      // Auto-select if empty and there's only 1 option available
      if (!fetchedLineaId && lineas.length === 1) {
        fetchedLineaId = lineas[0].id.toString();
        if (lineas[0].objetivos && lineas[0].objetivos.length === 1) {
          fetchedObjId = lineas[0].objetivos[0].id.toString();
        }
      }

      setSelectedLineaId(fetchedLineaId);
      setSelectedObjetivoId(fetchedObjId);
    } catch (fetchError) {
      console.error('Error fetching ficha descriptiva:', fetchError);
      setError('No fue posible cargar la ficha descriptiva.');
    } finally {
      if (!silent) setLoading(false);
    }
  };

  useEffect(() => {
    if (!open || !proyectoId) {
      return;
    }

    fetchFicha();
    setTabValue(0);
  }, [open, proyectoId]);

  const proyecto = data?.proyecto;
  const responsableFichaOptions = data?.responsable_ficha_options || [];
  const peiPrograma = data?.pei?.programa;
  const lineaOptions = data?.pei?.lineas || [];
  const selectedLinea = lineaOptions.find((linea) => String(linea.id) === String(selectedLineaId));
  const objetivoOptions = selectedLinea?.objetivos || [];
  const metas = data?.metas || [];
  const actividades = data?.actividades || [];
  const unidadMedidas = data?.unidad_medidas || [];
  const optionValues = [
    ...responsableFichaOptions.map((item) => ({ label: item.label, puesto: item.puesto })),
  ];
  if (selectedResponsableFicha && !optionValues.find(o => o.label === selectedResponsableFicha)) {
    optionValues.push({ label: selectedResponsableFicha, puesto: selectedPuestoResponsableFicha });
  }

  const handleSaveChanges = async () => {
    if (!proyectoId || !selectedResponsableFicha) {
      return;
    }

    setSavingChanges(true);
    setError('');

    try {
      await axios.put(`/proyectos/${proyectoId}/responsable-ficha`, {
        responsable_ficha: selectedResponsableFicha,
        justificacion: selectedJustificacion,
        descripcion: selectedDescripcion,
        objetivo: selectedObjetivo,
        proyecto_nombre: selectedProyectoNombre,
      });

      setData((prev) => ({
        ...prev,
        proyecto: {
          ...prev.proyecto,
          responsable_ficha: selectedResponsableFicha,
          justificacion: selectedJustificacion,
          descripcion: selectedDescripcion,
          objetivo: selectedObjetivo,
          proyecto_nombre: selectedProyectoNombre,
        },
      }));
    } catch (saveError) {
      console.error('Error updating ficha descriptiva:', saveError);
      setError('Error al actualizar la ficha.');
    } finally {
      setSavingChanges(false);
    }
  };

  const handleSaveResponsables = async (datos) => {
    if (!proyectoId) return;
    try {
      await axios.put(`/proyectos/${proyectoId}/responsable-ficha`, {
        responsable_ficha: datos.responsable_ficha,
        puesto_responsable_ficha: datos.puesto_responsable_ficha,
        justificacion: selectedJustificacion,
        descripcion: selectedDescripcion,
        objetivo: datos.objetivo,
        proyecto_nombre: selectedProyectoNombre,
      });

      setSelectedResponsableFicha(datos.responsable_ficha);
      setSelectedPuestoResponsableFicha(datos.puesto_responsable_ficha);
      setSelectedObjetivo(datos.objetivo);
      setResponsablesDialogOpen(false);
      await fetchFicha(true);
    } catch (saveError) {
      console.error('Error updating responsables:', saveError);
      setError('Error al actualizar responsables.');
    }
  };

  const handleSaveAutorizante = async (datos) => {
    if (!proyectoId) return;
    try {
      await axios.put(`/proyectos/${proyectoId}/responsable-ficha`, {
        responsable_ficha: selectedResponsableFicha,
        puesto_responsable_ficha: selectedPuestoResponsableFicha,
        autorizante_nombre: datos.autorizante_nombre,
        autorizante_puesto: datos.autorizante_puesto,
        justificacion: selectedJustificacion,
        descripcion: selectedDescripcion,
        objetivo: selectedObjetivo,
        proyecto_nombre: selectedProyectoNombre,
      });

      setSelectedAutorizanteNombre(datos.autorizante_nombre);
      setSelectedAutorizantePuesto(datos.autorizante_puesto);
      setAutorizanteDialogOpen(false);
      await fetchFicha(true);
    } catch (err) {
      console.error(err);
      alert('Ocurrió un error al guardar los datos del autorizante.');
    }
  };

  const handleSaveDatosProyecto = async (datos) => {
    if (!proyectoId) return;
    try {
      await axios.put(`/proyectos/${proyectoId}/responsable-ficha`, {
        responsable_ficha: selectedResponsableFicha,
        justificacion: datos.justificacion,
        descripcion: datos.descripcion,
        objetivo: selectedObjetivo,
        proyecto_nombre: datos.proyecto_nombre,
      });

      await fetchFicha(true);
    } catch (err) {
      console.error(err);
      alert('Ocurrió un error al guardar los datos del proyecto.');
    }
  };

  const handleOpenAddMeta = () => {
    setEditingMeta({ id: null, tipo: 'principal' });
    setMetaDialogOpen(true);
  };

  const handleOpenAddMetaComplementaria = (meta) => {
    if (sumaPorcentajes >= 100) {
      setAlertaMaxima(true);
      return;
    }
    const metasOriginales = data?.metas || [];
    const complementarias = metasOriginales.filter(m => m.tipo !== 'principal');
    const maxOrden = complementarias.length > 0 ? Math.max(...complementarias.map((m, idx) => Number(m.orden) || (idx + 1))) : 0;
    setEditingMeta({ id: null, tipo: 'complementaria', meta_id: meta.id, orden: maxOrden + 1 });
    setMetaDialogOpen(true);
  };

  const handleOpenEditMeta = (meta, fallbackIndex) => {
    setEditingMeta({
      ...meta,
      orden: meta.orden || (fallbackIndex !== undefined ? fallbackIndex + 1 : '')
    });
    setMetaDialogOpen(true);
  };

  const handleRequestDeleteMeta = (meta) => {
    setDeletingMeta(meta);
    setDeleteConfirmOpen(true);
  };

  const handleSaveMeta = async (metaPayload) => {
    if (!proyectoId) {
      return;
    }

    try {
      const request = metaPayload.id
        ? axios.put(`/proyectos/${proyectoId}/metas/${metaPayload.id}`, metaPayload)
        : axios.post(`/proyectos/${proyectoId}/metas`, metaPayload);

      await request;
      await fetchFicha(true);
      setMetaDialogOpen(false);
    } catch (error) {
      console.error('Error saving meta:', error);
      alert('Ocurrió un error al guardar la meta. ' + (error.response?.data?.message || ''));
      throw error;
    }
  };

  const handleRequestDeleteProyecto = () => {
    setDeleteProjectConfirmOpen(true);
  };

  const confirmDeleteProyecto = async () => {
    setDeleteProjectConfirmOpen(false);
    setLoading(true);
    try {
      await axios.delete(`/proyectos/${proyectoId}`);
      onClose();
      // Need a way to refresh list in ProyectosPage, since onClose doesn't trigger refresh, 
      // but assuming the user will reload or a parent state handles it if we trigger a window.location.reload
      window.location.reload(); 
    } catch (err) {
      console.error(err);
      setError('No fue posible eliminar el proyecto.');
      setLoading(false);
    }
  };

  const confirmDeleteMeta = async () => {
    if (!proyectoId || !deletingMeta?.id) {
      return;
    }

    try {
      await axios.delete(`/proyectos/${proyectoId}/metas/${deletingMeta.id}`);
      await fetchFicha(true);
    } catch (deleteError) {
      console.error('Error deleting meta:', deleteError);
      setError('No fue posible eliminar la meta.');
    } finally {
      setDeleteConfirmOpen(false);
      setDeletingMeta(null);
    }
  };

  const handleSaveActividad = async (actividadPayload) => {
    if (!proyectoId) return;
    
    try {
      if (actividadPayload.id) {
        await axios.put(`/actividades-sustantivas/${actividadPayload.id}`, actividadPayload);
      } else {
        await axios.post(`/actividades-sustantivas`, { ...actividadPayload, proyecto_id: proyectoId });
      }
      setActividadDialogOpen(false);
      await fetchFicha(true);
    } catch (error) {
      console.error('Error saving actividad:', error);
      setError('No fue posible guardar la actividad.');
    }
  };

  const handleRequestDeleteActividad = (actividad) => {
    setDeletingMeta(null); // we can reuse the same dialog or make a new one, but let's reuse
    setDeletingActividad(actividad);
    setDeleteConfirmOpen(true);
  };

  const confirmDeleteActividad = async () => {
    if (!deletingActividad?.id) return;
    
    try {
      await axios.delete(`/actividades-sustantivas/${deletingActividad.id}`);
      await fetchFicha(true);
    } catch (error) {
      console.error('Error deleting actividad:', error);
      setError('No fue posible eliminar la actividad.');
    } finally {
      setDeleteConfirmOpen(false);
      setDeletingActividad(null);
    }
  };

  const handleOpenIndicador = (metaComp) => {
    if (metaComp) {
      setEditingIndicador(metaComp.indicadores?.[0] || null);
      setSelectedMetaForIndicador(metaComp);
      
      const mpId = metaComp.meta_padre_id || metaComp.parent_id || metaComp.meta_id;
      const mp = principales.find(m => m.id === mpId) || null;
      setMetaPrincipalForIndicador(mp);
    } else {
      setEditingIndicador(null);
      setSelectedMetaForIndicador(null);
      setMetaPrincipalForIndicador(null);
    }
    setIndicadorDialogOpen(true);
  };

  const handleSaveIndicador = async (indicadorPayload) => {
    try {
      if (indicadorPayload.indicador_id) {
        await axios.put(`/indicadores/${indicadorPayload.indicador_id}`, indicadorPayload);
      } else {
        await axios.post('/indicadores', indicadorPayload);
      }
      setIndicadorDialogOpen(false);
      await fetchFicha(true);
    } catch (error) {
      console.error('Error saving indicador:', error);
      setError('No fue posible guardar el indicador.');
    }
  };

  const metasOriginales = data?.metas || [];
  const orderedMetas = [];
  const principales = metasOriginales.filter(m => m.tipo === 'principal');
  const complementarias = metasOriginales.filter(m => m.tipo !== 'principal').sort((a, b) => {
    const ordenA = Number(a.orden) || Number(a.id) || 0;
    const ordenB = Number(b.orden) || Number(b.id) || 0;
    return ordenA - ordenB;
  });
  const sumaPorcentajes = complementarias.reduce((acc, mc) => acc + (Number(mc.peso) || 0), 0);

  principales.forEach(principal => {
    orderedMetas.push(principal);
    const susComplementarias = complementarias.filter(c => 
      c.meta_id === principal.id || c.parent_id === principal.id || c.meta_padre_id === principal.id
    );
    orderedMetas.push(...susComplementarias);
  });
  const assignedCompIds = new Set(orderedMetas.filter(m => m.tipo !== 'principal').map(m => m.id));
  orderedMetas.push(...complementarias.filter(c => !assignedCompIds.has(c.id)));


  if (!open) return null;

  return (
    <div className="ficha-descriptiva-container">
      <div className="fd-contenido">
        {loading ? (
          <Box sx={{ display: 'flex', justifyContent: 'center', py: 8 }}>
            <CircularProgress />
          </Box>
        ) : error ? (
          <Alert severity="error">{error}</Alert>
        ) : proyecto ? (
          <>
            <div className="fd-titulo-vista">
                <div>
                  <h2>Ficha descriptiva de proyecto</h2>
                  <p>Notas generales: <br/>
                  1) La ficha se encuentra precargada con la información de la ficha del ejercicio del 2026.<br/>
                  2) Los campos no editables(sombreados) son capturados por la DPyRF.<br/>
                  3) La versión final de la ficha con fecha de cierre, se generará cuando se cierre y envíe electrónicamente a la DPyRF.
                  </p>
                </div>
                <Button onClick={onClose} variant="outlined" color="inherit">Volver a Proyectos</Button>
              </div>

            {/* 1. Identificación de responsables */}
            <div className="fd-panel">
              <div className="fd-panel-cab">
                <h3>Identificación de responsables</h3>
                <IconButton size="small" onClick={() => setResponsablesDialogOpen(true)} color="success" title="Editar responsables">
                  <Iconify icon="mdi:pencil" width={20} />
                </IconButton>
              </div>
              <div className="fd-panel-cuerpo">
                <div className="fd-grid-form">
                  <div className="fd-campo">
                    <label><span className="fd-ref">1</span> Unidad Responsable (UR)</label>
                    <input type="text" readOnly value={`${proyecto.urg || ''} - ${proyecto.urg_nombre || ''}`} />
                    <span className="fd-ayuda">(Denominación de la unidad administrativa conforme al artículo 3 del Reglamento Interior del TECDMX.)</span>
                  </div>
                  <div className="fd-campo">
                    <label><span className="fd-ref">2</span> Responsable operativo</label>
                    <input type="text" readOnly value={`${proyecto.ro || ''} - ${proyecto.responsable_operativo || ''}`} />
                    <span className="fd-ayuda">(Solo se muestran los RO que corresponden a la UR seleccionada, conforme a la alineación vigente.)</span>
                  </div>
                  <div className="fd-campo" style={{ flex: 1 }}>
                    <div style={{ display: 'flex', gap: '16px' }}>
                      <div style={{ flex: 1 }}>
                        <label><span className="fd-ref">3</span> Responsable de la ficha</label>
                        <textarea 
                          readOnly 
                          value={selectedResponsableFicha || ''} 
                          rows={2}
                          style={{ backgroundColor: '#fff', color: 'var(--tinta)', width: '100%', resize: 'vertical', fontFamily: 'inherit', padding: '8px', border: '1px solid #ccc', borderRadius: '4px' }}
                        />
                      </div>
                      <div style={{ flex: 1 }}>
                        <label>Puesto</label>
                        <textarea 
                          readOnly 
                          value={selectedPuestoResponsableFicha || ''} 
                          rows={2}
                          style={{ backgroundColor: '#fff', color: 'var(--tinta)', width: '100%', resize: 'vertical', fontFamily: 'inherit', padding: '8px', border: '1px solid #ccc', borderRadius: '4px' }}
                        />
                      </div>
                    </div>
                    <span className="fd-ayuda" style={{ marginTop: '4px', display: 'block' }}>
                      (Se prellena con el Titular del área (Validador) de la UR; editable por personal con esa área permitida.)
                    </span>
                  </div>
                  <div className="fd-campo">
                    <label><span className="fd-ref">4</span> Objetivo de la UR</label>
                    <textarea 
                      value={selectedObjetivo}
                      readOnly
                      rows="3"
                      style={{ backgroundColor: '#fff', color: 'var(--tinta)', resize: 'vertical' }}
                    ></textarea>
                    <span className="fd-ayuda">(Finalidad general que persigue la UR de acuerdo con sus atribuciones legales y/o reglamentarias.)</span>
                  </div>
                </div>
              </div>
            </div>

            {/* 2. Estructura programática */}
            <div className="fd-panel">
              <div className="fd-panel-cab">
                <h3><span className="fd-ref">5</span> Estructura programática</h3>
                <small>La registra la DPRF</small>
              </div>
              <div className="fd-panel-cuerpo">
                <div className="fd-nota-seccion" style={{ marginBottom: '8px' }}>
                  Conjunto de claves numéricas que identifican el proyecto dentro de la estructura programática y permiten asociarle recursos. Este apartado será registrado directamente por la Dirección de Planeación y Recursos Financieros, seleccionando de los catálogos de UR, RO, Programas, Subprogramas y Proyectos. Los cinco niveles se seleccionan de catálogo y la cascada solo permite combinaciones existentes en la alineación vigente.
                </div>
                
                <div className="fd-clave-compuesta" style={{ marginBottom: '8px', padding: '6px 12px' }}>
                  Clave programática: <b className="num">{proyecto.urg}.{proyecto.ro}.{proyecto.pg}.{proyecto.sp}.{proyecto.py}</b> — {proyecto.proyecto_nombre}
                </div>

                <div className="fd-grid-form" style={{ gap: '8px' }}>
                  <div className="fd-campo">
                    <label>UR</label>
                    <input type="text" readOnly disabled value={`${proyecto.urg || ''} - ${proyecto.urg_nombre || ''}`} />
                  </div>
                  <div className="fd-campo">
                    <label>RO</label>
                    <input type="text" readOnly disabled value={`${proyecto.ro || ''} - ${proyecto.responsable_operativo || ''}`} />
                  </div>
                  <div className="fd-campo ancho">
                    <label>PG · Programa</label>
                    <input type="text" readOnly disabled value={`${proyecto.pg || ''} - ${proyecto.programa_nombre || ''}`} />
                  </div>
                  <div className="fd-campo ancho">
                    <label>SP · Subprograma</label>
                    <input type="text" readOnly disabled value={`${proyecto.sp || ''} - ${proyecto.subprograma_nombre || ''}`} />
                  </div>
                  <div className="fd-campo ancho">
                    <label>PY · Proyecto</label>
                    <textarea rows="2" readOnly disabled value={`${proyecto.py || ''} - ${proyecto.proyecto_nombre || ''}`}></textarea>
                  </div>
                </div>
              </div>
            </div>

            {/* 3. Datos del proyecto y alineación */}
            <div className="fd-panel">
              <div className="fd-panel-cab">
                <h3>Datos del proyecto y alineación estratégica</h3>
                <IconButton size="small" onClick={() => setDatosProyectoDialogOpen(true)} color="primary" title="Editar datos del proyecto">
                  <Iconify icon="mdi:pencil" width={20} />
                </IconButton>
              </div>
              <div className="fd-panel-cuerpo">
                <div className="fd-grid-form">
                  <div className="fd-campo ancho">
                    <label><span className="fd-ref">6</span> Proyecto</label>
                    <textarea 
                      rows="2"
                      value={selectedProyectoNombre} 
                      readOnly
                      style={{ backgroundColor: '#fff', color: 'var(--tinta)' }}
                    ></textarea>
                    <span className="fd-ayuda">(Se toma automáticamente del catálogo PY conforme a la clave programática seleccionada.)</span>
                  </div>
                  <div className="fd-campo ancho">
                    <label><span className="fd-ref">7</span> Descripción del proyecto</label>
                    <textarea 
                      rows="8"
                      value={selectedDescripcion} 
                      disabled
                    ></textarea>
                    <span className="fd-ayuda">(Explicación detallada del proyecto.)</span>
                  </div>
                  <div className="fd-campo ancho">
                    <label><span className="fd-ref">8</span> Objetivo del proyecto</label>
                    <textarea 
                      rows="8"
                      value={selectedJustificacion} 
                      disabled
                    ></textarea>
                    <span className="fd-ayuda">(Motivo que justifica su ejecución, especificando los beneficios o resultados a obtener.)</span>
                  </div>
                  {(() => {
                    const alineacionesAgrupadas = [];
                    if (data?.pei?.alineaciones?.length > 0) {
                      const grupos = {};
                      data.pei.alineaciones.forEach(al => {
                        if (!grupos[al.linea_id]) {
                          grupos[al.linea_id] = new Set();
                        }
                        grupos[al.linea_id].add(al.objetivo_id);
                      });
                      for (const lineaId in grupos) {
                        alineacionesAgrupadas.push({
                          linea_id: lineaId,
                          objetivos: Array.from(grupos[lineaId])
                        });
                      }
                    } else {
                      alineacionesAgrupadas.push({ linea_id: null, objetivos: [] });
                    }

                    return alineacionesAgrupadas.map((grupo, idx) => {
                      const linea = lineaOptions.find(l => l.id.toString() === grupo.linea_id?.toString());
                      const objetivosTxt = grupo.objetivos.map(objId => {
                        const obj = (linea?.objetivos || []).find(o => o.id.toString() === objId?.toString());
                        return obj ? obj.nombre : '';
                      }).filter(Boolean).join('\n\n');

                      return (
                        <Fragment key={idx}>
                          <div className="fd-campo">
                            <label><span className="fd-ref">9</span> Objetivo estratégico {alineacionesAgrupadas.length > 1 ? `(${idx + 1})` : ''}</label>
                            <textarea
                              rows="3"
                              value={linea ? `${linea.numero}. ${linea.nombre}` : ''}
                              disabled
                            />
                            <span className="fd-ayuda">(Objetivo estratégico al que se alinea el proyecto (PEI).)</span>
                          </div>
                          <div className="fd-campo">
                            <label><span className="fd-ref">10</span> Línea estratégica {alineacionesAgrupadas.length > 1 ? `(${idx + 1})` : ''}</label>
                            <textarea
                              rows="4"
                              value={objetivosTxt}
                              disabled
                            />
                            <span className="fd-ayuda">(Del Plan Estratégico Institucional.)</span>
                          </div>
                        </Fragment>
                      );
                    });
                  })()}
                </div>
              </div>
            </div>

            {/* 4. Meta principal */}
            {principales.map((metaPrin, idx) => (
              <div className="fd-panel" key={metaPrin.id}>
                <div className="fd-panel-cab">
                  <h3>Cuantificación de metas · Meta principal {principales.length > 1 ? (idx+1) : ''}</h3>
                  <div style={{ display: 'flex', gap: '4px' }}>
                    <IconButton size="small" onClick={() => handleOpenEditMeta(metaPrin)} color="primary" title="Editar">
                      <Iconify icon="mdi:pencil" width={20} />
                    </IconButton>
                  </div>
                </div>
                <div className="fd-panel-cuerpo">
                  <div className="fd-grid-form" style={{ marginBottom: '16px' }}>
                    <div className="fd-campo ancho">
                      <label><span className="fd-ref">11</span> Denominación de la meta principal</label>
                      <textarea 
                        rows="4"
                        value={metaPrin.nombre || ''} 
                        disabled
                        style={{ width: '100%', resize: 'vertical' }}
                      />
                      <span className="fd-ayuda">(Descripción concisa del resultado a lograr; su resultado es el acumulado de las metas complementarias.)</span>
                    </div>
                  </div>
                  <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <label style={{ fontSize: '12px', fontWeight: 600, margin: 0 }}><span className="fd-ref">12</span> Programación mensual (absoluta)</label>
                    <label style={{ fontSize: '12px', fontWeight: 600, margin: 0 }}><span className="fd-ref">13</span> Total anual</label>
                  </div>
                  <div className="fd-fila-meses" style={{ marginTop: '8px' }}>
                    {[1,2,3,4,5,6,7,8,9,10,11,12].map(m => (
                      <div className="fd-celda-mes" key={m}>
                        <span>{MONTH_LABELS[m-1]}</span>
                        <output>{formatNumber(sumaPorcentajes)}%</output>
                      </div>
                    ))}
                    <div className="fd-celda-mes total">
                      <span>TOTAL ANUAL</span>
                      <output>{formatNumber(sumaPorcentajes)}%</output>
                    </div>
                  </div>
                  <div className="fd-nota-seccion" style={{ marginTop: '12px', marginBottom: 0 }}>Se calcula de manera automática a partir de la programación de las metas complementarias.</div>
                </div>
              </div>
            ))}
            {principales.length === 0 && (
              <div className="fd-panel">
                <div className="fd-panel-cab">
                  <h3>Cuantificación de metas · Meta principal</h3>
                  <button className="fd-btn fd-btn-primario fd-btn-mini" onClick={handleOpenAddMeta}>＋ Agregar meta principal</button>
                </div>
                <div className="fd-panel-cuerpo">
                  <Alert severity="info">No hay meta principal registrada. Agrega una para comenzar.</Alert>
                </div>
              </div>
            )}

            {/* 5. Metas complementarias */}
            <div className="fd-panel">
              <div className="fd-panel-cab">
                <h3>Metas complementarias</h3>
                {principales.length > 0 && (
                  <button className="fd-btn fd-btn-secundario fd-btn-mini" onClick={() => handleOpenAddMetaComplementaria(principales[0])}>＋ Agregar meta complementaria</button>
                )}
              </div>
              <div className="fd-panel-cuerpo">
                <div className="fd-nota-seccion">Metas específicas que desagregan la meta principal por tipo de acción, producto o servicio <span className="fd-ref">15</span>. Cada una define su unidad de medida <span className="fd-ref">16</span>, si es Programable o No Programable <span className="fd-ref">17</span>, su programación mensual <span className="fd-ref">18</span> con total anual <span className="fd-ref">19</span>, y su valor (%) dentro de la meta principal <span className="fd-ref">20</span>.</div>
                
                {complementarias.length === 0 ? (
                   <Alert severity="info" sx={{mb: 2}}>No hay metas complementarias registradas.</Alert>
                ) : (
                  complementarias.map((metaComp, idx) => (
                    <div className="fd-meta-card" key={metaComp.id}>
                      <div className="fd-meta-card-cab" style={{ alignItems: 'flex-start' }}>
                        <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
                          <span style={{ fontSize: '9px', fontWeight: 700, color: 'var(--tinta-2)', textTransform: 'uppercase', marginBottom: '4px' }}>No. Meta</span>
                          <div className="fd-meta-num" style={{ margin: 0 }}>{idx + 1}</div>
                        </div>
                        <textarea className="denominacion" readOnly value={metaComp.nombre || ''} rows="3" style={{ resize: 'vertical' }}></textarea>
                        <div style={{ display: 'flex', flexDirection: 'column', gap: '4px' }}>
                          <IconButton size="small" onClick={() => handleOpenEditMeta(metaComp, idx)} color="primary" title="Editar">
                            <Iconify icon="mdi:pencil" width={20} />
                          </IconButton>
                          <IconButton size="small" onClick={() => handleRequestDeleteMeta(metaComp)} color="error" title="Quitar">
                            <Iconify icon="mdi:delete" width={20} />
                          </IconButton>
                        </div>
                      </div>
                      <div className="fd-meta-herr" style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '16px', alignItems: 'end' }}>
                        <div className="fd-campo" style={{ marginBottom: 0 }}>
                          <label style={{ fontSize: '12px', fontWeight: 600 }}>Unidad de medida</label>
                          <input type="text" readOnly value={metaComp.unidad_medida || 'Sin U.M.'} style={{ border: '1px solid var(--linea)', borderRadius: '8px', padding: '8px 10px', fontSize: '13px', background: 'var(--blanco)', width: '100%'}} />
                        </div>
                        <div className="fd-campo" style={{ marginBottom: 0 }}>
                          <label style={{ fontSize: '12px', fontWeight: 600 }}>Tipo</label>
                          <div className="fd-radio-tipo" style={{ marginTop: 0 }}>
                            <label style={{ background: metaComp.tmc === 1 ? 'var(--primario)' : 'var(--blanco)', color: metaComp.tmc === 1 ? '#fff' : 'inherit'}}>Programable</label>
                            <label style={{ background: metaComp.tmc !== 1 ? 'var(--primario)' : 'var(--blanco)', color: metaComp.tmc !== 1 ? '#fff' : 'inherit'}}>No Programable</label>
                          </div>
                        </div>
                        <div className="fd-campo" style={{ marginBottom: 0, alignItems: 'flex-end' }}>
                          <label style={{ fontSize: '12px', fontWeight: 600 }}>Valor (%)</label>
                          <input type="text" readOnly value={metaComp.valor_porcentaje || metaComp.peso || '0'} style={{ border: '1px solid var(--linea)', borderRadius: '8px', padding: '8px 10px', fontSize: '13px', background: 'var(--blanco)', width: '100px', textAlign: 'right'}} />
                        </div>
                      </div>
                      <div className="fd-fila-meses">
                        {[1,2,3,4,5,6,7,8,9,10,11,12].map(m => (
                          <div className="fd-celda-mes" key={m}>
                            <span>{MONTH_LABELS[m-1]}</span>
                            <output style={{ background: '#fff' }}>
                              {metaComp.tmc !== 1 && (!metaComp.meses?.[m] || metaComp.meses[m] == 0) ? 'NP' : formatNumber(metaComp.meses?.[m] || 0)}
                            </output>
                          </div>
                        ))}
                        <div className="fd-celda-mes total">
                          <span>TOTAL</span>
                          <output>{metaComp.tmc !== 1 ? 'NP' : formatNumber(metaComp.total_anual || 0)}</output>
                        </div>
                      </div>
                    </div>
                  ))
                )}
                
                <div className="fd-resumen-valores ok" style={{ display: 'none' }}>
                  <span>✓</span>
                  <span>Suma de valores: <b className="num">100.00%</b></span>
                  <span>La suma de los valores de las metas complementarias asciende a 100.00%.</span>
                </div>
              </div>
            </div>

            {/* 6. Indicadores */}
            <div className="fd-panel">
              <div className="fd-panel-cab">
                <h3>Indicadores</h3>
                <button className="fd-btn fd-btn-secundario fd-btn-mini" onClick={() => handleOpenIndicador(null)}>＋ Agregar indicador</button>
              </div>
              <div className="fd-panel-cuerpo">
                <div className="fd-nota-seccion">La alineación <span className="fd-ref">21</span>, la unidad de medida <span className="fd-ref">24</span> y el método de cálculo <span className="fd-ref">25</span> se toman automáticamente de la meta complementaria: Programable = Atendido / Programado · No Programable = Atendido / Recibido. La medición se efectúa mes a mes; la frecuencia <span className="fd-ref">27</span> es informativa.</div>
                
                {complementarias.length === 0 ? (
                  <Alert severity="info">No hay metas complementarias para asociar indicadores.</Alert>
                ) : (
                  complementarias.map((metaComp, idx) => {
                    const indicador = metaComp.indicadores?.[0] || null;
                    return (
                      <div className="fd-indicador-card" key={metaComp.id}>
                        <div className="fd-indicador-cab" style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', minHeight: '20px' }}>
                          <span style={{ fontWeight: 700, fontSize: '13px', color: 'var(--primario)' }}>
                            Indicador de la Meta Complementaria {idx + 1}
                          </span>
                          {indicador && (
                            <button className="fd-btn fd-btn-secundario fd-btn-mini" title="Editar indicador" onClick={() => handleOpenIndicador(metaComp)}>
                              ✎
                            </button>
                          )}
                        </div>

                        {indicador ? (
                          <div>

                            <div className="fd-grid-form" style={{ marginTop: '6px' }}>
                              <div className="fd-campo">
                                <label><span className="fd-ref">21</span> Alineación</label>
                                <textarea 
                                  readOnly 
                                  rows="3" 
                                  style={{ border: '1px solid var(--linea)', borderRadius: '8px', padding: '8px 10px', fontSize: '13px', background: 'var(--blanco)', width: '100%', resize: 'vertical', fontFamily: 'inherit' }}
                                  value={`Meta ${idx + 1} - ${metaComp.nombre || ''}`}
                                />
                              </div>
                              <div className="fd-campo">
                                <label><span className="fd-ref">22</span> Nombre del indicador</label>
                                <input type="text" readOnly value={indicador.nombre || ''} />
                              </div>
                              <div className="fd-campo">
                                <label><span className="fd-ref">23</span> Objetivo del indicador</label>
                                <input type="text" readOnly value={indicador.objetivo || indicador.definicion || ''} />
                              </div>
                              <div className="fd-campo">
                                <label><span className="fd-ref">24</span> Unidad de medida</label>
                                <input type="text" readOnly value={metaComp.unidad_medida || ''} />
                              </div>
                            </div>
                            


                            <div className="fd-grid-form" style={{ marginTop: '6px' }}>
                              <div className="fd-campo">
                                <label><span className="fd-ref">25</span> Método de cálculo</label>
                                <input type="text" readOnly value={indicador.metodo_calculo || (metaComp.programable === false ? 'Resultado = (Atendido/Recibido)*100' : 'Resultado = (Atendido/Programado)*100')} />
                              </div>
                              <div className="fd-campo">
                                <label><span className="fd-ref">26</span> Dimensión a medir</label>
                                <input type="text" readOnly value={indicador.dimension_nombre || indicador.dimension || 'Eficacia'} />
                              </div>
                              <div className="fd-campo">
                                <label><span className="fd-ref">27</span> Frecuencia de medición</label>
                                <input type="text" readOnly value={indicador.frecuencia_nombre || 'Mensual'} />
                              </div>
                            </div>
                          </div>
                        ) : (
                          <p style={{fontSize: '13px', color: 'var(--tinta-2)', marginTop: '8px'}}>No se ha definido el indicador para esta meta complementaria.</p>
                        )}
                      </div>
                    );
                  })
                )}
              </div>
            </div>

            {/* 7. Actividades sustantivas */}
            <div className="fd-panel">
              <div className="fd-panel-cab">
                <h3>Actividades sustantivas del proyecto</h3>
                <button className="fd-btn fd-btn-secundario fd-btn-mini" onClick={() => { setEditingActividad(null); setActividadDialogOpen(true); }}>＋ Agregar actividad</button>
              </div>
              <div className="fd-panel-cuerpo">
                <div className="fd-nota-seccion">Actividades principales cuya ejecución permite alcanzar las metas <span className="fd-ref">29</span>, presentadas en orden de prelación <span className="fd-ref">28</span>. Los recursos asociados <span className="fd-ref">30</span> se describen sin cuantificar.</div>
                
                {(!actividades || actividades.length === 0) ? (
                  <Alert severity="info">No hay actividades registradas.</Alert>
                ) : (
                  <>
                    {/* Encabezados de columna — solo se muestran una vez */}
                    <div className="fd-fila-actividad fd-fila-actividad-header">
                      <div className="fd-actividad-label" style={{ textAlign: 'center' }}><span className="fd-ref">28</span></div>
                      <div className="fd-actividad-label">Descripción de las Actividades <span className="fd-ref">29</span></div>
                      <div className="fd-actividad-label">Recursos Asociados <span className="fd-ref">30</span></div>
                      <div />
                      <div />
                    </div>

                    {actividades.map((act, idx) => (
                      <div className="fd-fila-actividad" key={act.id || idx}>
                        <div className="no-act">
                          {idx+1}
                        </div>
                        <div className="fd-actividad-campo">
                          <textarea readOnly value={act.descripcion || ''} rows={2} className="fd-actividad-textarea" />
                        </div>
                        <div className="fd-actividad-campo">
                          <textarea readOnly value={act.recursos_asociados || ''} rows={2} className="fd-actividad-textarea recursos" />
                        </div>
                        <button className="fd-quitar-cruz" onClick={() => { setEditingActividad(act); setActividadDialogOpen(true); }} title="Editar actividad">✎</button>
                        <button className="fd-quitar-cruz" onClick={() => handleRequestDeleteActividad(act)} title="Quitar actividad">×</button>
                      </div>
                    ))}
                  </>
                )}
              </div>
            </div>

            {/* 8. Autorizante (visible para validadores y capturadores) */}
            {(hasRole('Administrador') || hasRole('Administrador', 'web') || hasRole('Validador') || hasRole('Capturador')) && (
              <div className="fd-panel">
                <div className="fd-panel-cab">
                  <h3>Autorizante</h3>
                  <IconButton size="small" onClick={() => setAutorizanteDialogOpen(true)} color="success" title="Editar autorizante">
                    <Iconify icon="mdi:pencil" width={20} />
                  </IconButton>
                </div>
                <div className="fd-panel-cuerpo">
                  <div className="fd-grid-form">
                    <div className="fd-campo">
                      <label>Autoriza</label>
                      <textarea 
                        readOnly 
                        value={selectedAutorizanteNombre || ''} 
                        rows={2}
                        style={{ backgroundColor: '#fff', color: 'var(--tinta)', width: '100%', resize: 'vertical', fontFamily: 'inherit', padding: '8px', border: '1px solid #ccc', borderRadius: '4px' }}
                      />
                    </div>
                    <div className="fd-campo">
                      <label>Puesto</label>
                      <textarea 
                        readOnly 
                        value={selectedAutorizantePuesto || ''} 
                        rows={2}
                        style={{ backgroundColor: '#fff', color: 'var(--tinta)', width: '100%', resize: 'vertical', fontFamily: 'inherit', padding: '8px', border: '1px solid #ccc', borderRadius: '4px' }}
                      />
                    </div>
                  </div>
                </div>
              </div>
            )}
            <div className="fd-acciones-form">
              <button 
                className="fd-btn fd-btn-secundario" 
                onClick={() => setHistorialOpen(true)} 
                style={{ marginRight: 'auto', display: 'flex', alignItems: 'center', gap: '6px' }}
              >
                <Iconify icon="mdi:history" width={18} /> Bitácora
              </button>

              {hasRole('Validador') && (
                <button className="fd-btn fd-btn-secundario" onClick={() => handleOpenBitacora('regresar_capturador')} style={{ color: '#B3372E', borderColor: '#B3372E' }}>
                  Regresar a capturador
                </button>
              )}
              {hasRole('Capturador') && (
                <button className="fd-btn fd-btn-primario" onClick={() => handleOpenBitacora('enviar_validador')}>
                  Enviar al Validador
                </button>
              )}
              {hasRole('Validador') && (
                <button className="fd-btn fd-btn-primario" onClick={() => handleOpenBitacora('enviar_dpyrf')}>
                  Cerrar y enviar a DPyRF
                </button>
              )}
            </div>
          </>
        ) : null}
      </div>

      {/* Modales secundarios */}
      <MetaDescriptivaDialog open={metaDialogOpen} onClose={() => setMetaDialogOpen(false)} onSave={handleSaveMeta} unidadMedidas={unidadMedidas} meta={editingMeta} maxPermitido={100 - sumaPorcentajes} sumaPorcentajes={sumaPorcentajes} />
      <ActividadDescriptivaDialog open={actividadDialogOpen} onClose={() => setActividadDialogOpen(false)} onSave={handleSaveActividad} actividad={editingActividad} />
      <ConfirmDeleteDialog open={deleteConfirmOpen} onClose={() => { setDeleteConfirmOpen(false); setDeletingMeta(null); setDeletingActividad(null); }} onConfirm={deletingMeta ? confirmDeleteMeta : confirmDeleteActividad} message={`¿Está seguro de que desea eliminar ${deletingMeta ? `la meta "${deletingMeta.nombre}"` : 'esta actividad'}? Esta acción no se puede deshacer.`} />
      <ConfirmDeleteDialog open={deleteProjectConfirmOpen} onClose={() => setDeleteProjectConfirmOpen(false)} onConfirm={confirmDeleteProyecto} message={`¿Está seguro de que desea eliminar permanentemente este proyecto y todos sus registros asociados (Metas, Actividades e Indicadores)? Esta acción no se puede deshacer.`} />
      <IndicadorDialog open={indicadorDialogOpen} onClose={() => setIndicadorDialogOpen(false)} onSave={handleSaveIndicador} indicador={editingIndicador} metaComplementaria={selectedMetaForIndicador} metaPrincipal={metaPrincipalForIndicador} proyectoId={proyectoId} unidadMedidas={unidadMedidas} complementarias={complementarias} />
      <DatosProyectoDialog 
        open={datosProyectoDialogOpen} 
        onClose={() => setDatosProyectoDialogOpen(false)} 
        onSave={handleSaveDatosProyecto} 
        initialData={{
          proyecto_nombre: selectedProyectoNombre,
          descripcion: selectedDescripcion,
          justificacion: selectedJustificacion
        }} 
      />
      <ResponsablesDialog
        open={responsablesDialogOpen}
        onClose={() => setResponsablesDialogOpen(false)}
        onSave={handleSaveResponsables}
        initialData={{
          urg: `${proyecto?.urg || ''} - ${proyecto?.urg_nombre || ''}`,
          ro: `${proyecto?.ro || ''} - ${proyecto?.responsable_operativo || ''}`,
          responsable_ficha: selectedResponsableFicha,
          puesto_responsable_ficha: selectedPuestoResponsableFicha,
          objetivo: selectedObjetivo,
        }}
        responsableFichaOptions={optionValues}
        puestosOptions={data?.puestos_ur || []}
      />
      <AutorizanteDialog
        open={autorizanteDialogOpen}
        onClose={() => setAutorizanteDialogOpen(false)}
        onSave={handleSaveAutorizante}
        initialData={{
          autorizante_nombre: selectedAutorizanteNombre,
          autorizante_puesto: selectedAutorizantePuesto,
        }}
        empleadosUrOptions={data?.empleados_ur || []}
        puestosOptions={data?.puestos_ur || []}
      />
      
      <Dialog open={alertaValidacionOpen} onClose={() => setAlertaValidacionOpen(false)} maxWidth="sm" fullWidth PaperProps={{ sx: { borderRadius: 2 } }}>
        <DialogTitle sx={{ display: 'flex', alignItems: 'center', color: '#B3372E', fontWeight: 'bold' }}>
          <Iconify icon="mdi:alert-circle-outline" sx={{ mr: 1, width: 28, height: 28 }} />
          Información incompleta
        </DialogTitle>
        <DialogContent dividers>
          <Typography variant="body1" sx={{ color: 'text.secondary', mb: 2 }}>
            No es posible enviar la ficha a DPyRF. Por favor, asegúrate de completar la siguiente información antes de continuar:
          </Typography>
          <ul style={{ color: '#B3372E', paddingLeft: '20px', margin: 0, fontSize: '0.9rem' }}>
            {validacionErrores.map((err, i) => <li key={i} style={{ marginBottom: '8px' }}>{err}</li>)}
          </ul>
        </DialogContent>
        <DialogActions sx={{ px: 3, py: 2 }}>
          <Button onClick={() => setAlertaValidacionOpen(false)} variant="contained" sx={{ bgcolor: '#1F4E79' }}>
            Entendido
          </Button>
        </DialogActions>
      </Dialog>
      
      <ProyectoBitacoraModal 
        open={bitacoraOpen} 
        onClose={() => setBitacoraOpen(false)} 
        proyectoId={proyectoId} 
        action={bitacoraAction} 
        warnings={validacionWarnings}
        onSuccess={() => { setBitacoraOpen(false); onClose(); }} 
      />
      <BitacoraHistorialModal 
        open={historialOpen}
        onClose={() => setHistorialOpen(false)}
        proyectoId={proyectoId}
      />
      <Snackbar open={alertaMaxima} autoHideDuration={4000} onClose={() => setAlertaMaxima(false)} anchorOrigin={{ vertical: 'top', horizontal: 'center' }}>
        <Alert onClose={() => setAlertaMaxima(false)} severity="warning" sx={{ width: '100%', borderRadius: 2, boxShadow: 3 }}>
          No es posible agregar más metas complementarias porque ya se alcanzó el 100%.
        </Alert>
      </Snackbar>
    </div>
  );
}
