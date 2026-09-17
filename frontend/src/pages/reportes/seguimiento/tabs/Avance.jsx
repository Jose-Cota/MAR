import React, { useState, useEffect } from 'react';
import {
  Box,
  Typography,
  List,
  ListItem,
  ListItemButton,
  ListItemText,
  ListItemIcon,
  Collapse,
  CircularProgress,
  Grid
} from '@mui/material';
import { AddBoxOutlined, IndeterminateCheckBoxOutlined, RemoveOutlined, ArticleOutlined, EditOutlined } from '@mui/icons-material';
import { IconButton, Tooltip } from '@mui/material';
import CapturaAvanceModal from '../CapturaAvanceModal';
import axios from '../../../../utils/axios';
import useGlobalStore from '../../../../stores/useGlobalStore';

const sidebarOptions = [
  'Consolidado a la última fecha de corte (fin de mes)',
  'Avance mensual y acumulado',
  'Avance trimestral y acumulado',
  'Matriz de metas programadas y alcanzadas',
  'Avance trimestral y acumulado de indicadores',
  'Avance trimestral de implementación de las LAPDHDF'
];

const mesesNombres = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

// Reusable Tree Node Component
const TreeNode = ({ label, childrenData, onExpand, isExpanded, isLoading, level = 0, isLeaf = false }) => {
  return (
    <React.Fragment>
      <ListItemButton onClick={isLeaf ? undefined : onExpand} sx={{ pl: level * 4, py: 0.5, borderLeft: level > 0 ? '1px dotted #ccc' : 'none', ml: level > 0 ? 2 : 0 }}>
        <ListItemIcon sx={{ minWidth: 28 }}>
          {isLoading ? (
            <CircularProgress size={16} />
          ) : isLeaf ? (
            <RemoveOutlined fontSize="small" color="action" />
          ) : isExpanded ? (
            <IndeterminateCheckBoxOutlined fontSize="small" color="action" />
          ) : (
            <AddBoxOutlined fontSize="small" color="action" />
          )}
        </ListItemIcon>
        <ListItemText primary={label} primaryTypographyProps={{ variant: 'body2', color: 'text.secondary', sx: { fontSize: '0.85rem' } }} />
      </ListItemButton>
      {!isLeaf && (
        <Collapse in={isExpanded} timeout="auto" unmountOnExit>
          <List component="div" disablePadding>
            {childrenData}
          </List>
        </Collapse>
      )}
    </React.Fragment>
  );
};

export default function Avance() {
  const [selectedOption, setSelectedOption] = useState(0);
  const ejercicio = useGlobalStore((state) => state.ejercicio);

  // Data states
  const [programas, setProgramas] = useState([]);
  const [expandedProgramas, setExpandedProgramas] = useState({});
  const [subprogramasCache, setSubprogramasCache] = useState({});
  
  const [expandedSubprogramas, setExpandedSubprogramas] = useState({});
  const [proyectosCache, setProyectosCache] = useState({});

  const [expandedProyectos, setExpandedProyectos] = useState({});
  const [metasCache, setMetasCache] = useState({});

  const [loadingInitial, setLoadingInitial] = useState(false);
  const [modalOpen, setModalOpen] = useState(false);
  const [selectedMeta, setSelectedMeta] = useState(null);
  const [selectedPyId, setSelectedPyId] = useState(null);

  useEffect(() => {
    const fetchProgramas = async () => {
      setLoadingInitial(true);
      try {
        const response = await axios.get('/programas', { params: { ejercicio } });
        setProgramas(response.data.data || response.data);
      } catch (error) {
        console.error('Error fetching programas', error);
      } finally {
        setLoadingInitial(false);
      }
    };
    if (ejercicio) {
      fetchProgramas();
      // Reset caches on year change
      setExpandedProgramas({});
      setSubprogramasCache({});
      setExpandedSubprogramas({});
      setProyectosCache({});
      setExpandedProyectos({});
      setMetasCache({});
    }
  }, [ejercicio]);

  const handleTogglePrograma = async (progId) => {
    const isCurrentlyExpanded = expandedProgramas[progId];
    setExpandedProgramas(prev => ({ ...prev, [progId]: !isCurrentlyExpanded }));

    // If expanding and we don't have the data yet, fetch it
    if (!isCurrentlyExpanded && !subprogramasCache[progId]) {
      try {
        const response = await axios.get('/elaboracion/subprogramas', { params: { programa_id: progId } });
        setSubprogramasCache(prev => ({ ...prev, [progId]: response.data }));
      } catch (error) {
        console.error('Error fetching subprogramas', error);
      }
    }
  };

  const handleToggleSubprograma = async (subpId) => {
    const isCurrentlyExpanded = expandedSubprogramas[subpId];
    setExpandedSubprogramas(prev => ({ ...prev, [subpId]: !isCurrentlyExpanded }));

    if (!isCurrentlyExpanded && !proyectosCache[subpId]) {
      try {
        const response = await axios.get('/elaboracion/proyectos', { params: { subprograma_id: subpId } });
        setProyectosCache(prev => ({ ...prev, [subpId]: response.data }));
      } catch (error) {
        console.error('Error fetching proyectos', error);
      }
    }
  };

  const handleToggleProyecto = async (pyId) => {
    const isCurrentlyExpanded = expandedProyectos[pyId];
    setExpandedProyectos(prev => ({ ...prev, [pyId]: !isCurrentlyExpanded }));

    if (!isCurrentlyExpanded && !metasCache[pyId]) {
      try {
        const response = await axios.get('/reportes/seguimiento/detalle-metas', { params: { proyecto_id: pyId } });
        setMetasCache(prev => ({ ...prev, [pyId]: response.data }));
      } catch (error) {
        console.error('Error fetching metas', error);
      }
    }
  };

  const renderMetasTable = (metas, pyId) => {
    if (!metas || metas.length === 0) return null;
    
    // Agrupar metas por tipo
    const principales = metas.filter(m => m.tipo === 'principal');
    const complementarias = metas.filter(m => m.tipo === 'complementaria');

    const renderMetaGroup = (title, metaList) => {
      if (metaList.length === 0) return null;
      return (
        <Box sx={{ ml: 8, mt: 1, mb: 3 }}>
          <Typography variant="body2" sx={{ fontWeight: 'bold', mb: 1 }}>{title}</Typography>
          {metaList.map(meta => (
            <Box key={meta.meta_id} sx={{ ml: 2, mb: 3 }}>
              <Typography variant="body2" sx={{ color: 'text.secondary', display: 'flex', alignItems: 'center' }}>
                <RemoveOutlined fontSize="small" sx={{ mr: 1 }} />
                {meta.meta}
                <Tooltip title="Capturar Avance">
                  <IconButton size="small" onClick={() => { setSelectedMeta(meta); setSelectedPyId(pyId); setModalOpen(true); }} sx={{ ml: 1 }}>
                    <EditOutlined fontSize="small" color="primary" />
                  </IconButton>
                </Tooltip>
              </Typography>
              <Typography variant="body2" sx={{ color: 'text.secondary', ml: 4, mb: 1 }}>
                Unidad de medida: {meta.unidad_medida}
              </Typography>
              
              <Box sx={{ overflowX: 'auto', ml: 4 }}>
                <table style={{ width: '100%', fontSize: '0.8rem', color: '#666', borderCollapse: 'collapse', textAlign: 'center' }}>
                  <thead>
                    <tr>
                      <th style={{ textAlign: 'left', minWidth: 200, padding: 4 }}></th>
                      {mesesNombres.map(mes => <th key={mes} style={{ padding: 4 }}>{mes}</th>)}
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td style={{ textAlign: 'left', padding: 4 }}>Programado</td>
                      {mesesNombres.map((m, i) => <td key={`p-${i}`}>{meta.meses[i + 1]?.programado || 0}</td>)}
                    </tr>
                    <tr>
                      <td style={{ textAlign: 'left', padding: 4 }}>Alcanzado</td>
                      {mesesNombres.map((m, i) => <td key={`a-${i}`}>{meta.meses[i + 1]?.alcanzado || 0}</td>)}
                    </tr>
                    <tr>
                      <td style={{ textAlign: 'left', padding: 4 }}>Porcentaje de avance respecto del mes</td>
                      {mesesNombres.map((m, i) => <td key={`pm-${i}`}>{meta.meses[i + 1]?.porcentaje_mes || '0.0'}%</td>)}
                    </tr>
                    <tr>
                      <td style={{ textAlign: 'left', padding: 4 }}>Porcentaje de avance acumulado</td>
                      {mesesNombres.map((m, i) => <td key={`pa-${i}`}>{meta.meses[i + 1]?.porcentaje_acumulado || '0.0'}%</td>)}
                    </tr>
                  </tbody>
                </table>
              </Box>
            </Box>
          ))}
        </Box>
      );
    };

    return (
      <React.Fragment>
        {renderMetaGroup('Meta Principal', principales)}
        {renderMetaGroup('Metas Complementarias', complementarias)}
      </React.Fragment>
    );
  };

  return (
    <>
    <Grid container spacing={3}>
      {/* Left Sidebar Menu */}
      <Grid size={{ xs: 12, md: 4, lg: 3 }}>
        <List sx={{ 
          pt: 0,
          maxHeight: 'calc(100vh - 200px)',
          overflowY: 'auto',
          pr: 1,
          '&::-webkit-scrollbar': {
            width: '6px',
          },
          '&::-webkit-scrollbar-track': {
            background: '#f1f1f1',
            borderRadius: '8px',
          },
          '&::-webkit-scrollbar-thumb': {
            backgroundColor: '#bcc0c4',
            borderRadius: '8px',
            '&:hover': {
              backgroundColor: '#9fa3a7',
            }
          }
        }}>
          {sidebarOptions.map((text, index) => (
            <ListItem key={index} disablePadding sx={{ mb: 1 }}>
              <ListItemButton 
                selected={selectedOption === index}
                onClick={() => setSelectedOption(index)}
                sx={{
                  borderRadius: 1,
                  backgroundColor: selectedOption === index ? '#3f51b5' : 'transparent',
                  color: selectedOption === index ? '#fff' : 'text.primary',
                  '&.Mui-selected': {
                    backgroundColor: '#3f51b5',
                    color: '#fff',
                    '&:hover': {
                      backgroundColor: '#303f9f',
                    }
                  }
                }}
              >
                <ListItemText primary={text} primaryTypographyProps={{ variant: 'body2' }} />
              </ListItemButton>
            </ListItem>
          ))}
        </List>
      </Grid>

      {/* Right Content Area */}
      <Grid size={{ xs: 12, md: 8, lg: 9 }}>
        <Box sx={{ p: 2, bgcolor: '#f9f9f9', borderRadius: 1, minHeight: 400 }}>
          <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
            Periodo: Enero - Diciembre
          </Typography>

          <List component="nav" disablePadding sx={{ bgcolor: 'white', border: '1px solid #eee', borderRadius: 1 }}>
            {/* Root Node */}
            <TreeNode 
              label={`Programa Operativo Anual ${ejercicio}`}
              isExpanded={true}
              level={0}
              childrenData={
                loadingInitial ? (
                  <Box sx={{ pl: 6, py: 2 }}><CircularProgress size={20} /></Box>
                ) : programas.length === 0 ? (
                  <Typography variant="body2" sx={{ pl: 6, color: 'text.secondary' }}>No hay programas registrados</Typography>
                ) : (
                  programas.map(prog => (
                    <TreeNode
                      key={prog.programa_id}
                      label={`${prog.numero} - ${prog.nombre}`}
                      isExpanded={!!expandedProgramas[prog.programa_id]}
                      onExpand={() => handleTogglePrograma(prog.programa_id)}
                      level={1}
                      isLoading={expandedProgramas[prog.programa_id] && !subprogramasCache[prog.programa_id]}
                      childrenData={
                        subprogramasCache[prog.programa_id]?.map(subp => (
                          <TreeNode
                            key={subp.subprograma_id}
                            label={`${subp.numero} - ${subp.nombre}`}
                            isExpanded={!!expandedSubprogramas[subp.subprograma_id]}
                            onExpand={() => handleToggleSubprograma(subp.subprograma_id)}
                            level={2}
                            isLoading={expandedSubprogramas[subp.subprograma_id] && !proyectosCache[subp.subprograma_id]}
                            childrenData={
                              proyectosCache[subp.subprograma_id]?.map(py => (
                                <TreeNode
                                  key={py.proyecto_id}
                                  label={`${py.numero} - ${py.nombre}`}
                                  isExpanded={!!expandedProyectos[py.proyecto_id]}
                                  onExpand={() => handleToggleProyecto(py.proyecto_id)}
                                  level={3}
                                  isLoading={expandedProyectos[py.proyecto_id] && !metasCache[py.proyecto_id]}
                                  childrenData={renderMetasTable(metasCache[py.proyecto_id], py.proyecto_id)}
                                />
                              )) || []
                            }
                          />
                        )) || []
                      }
                    />
                  ))
                )
              }
            />
          </List>
        </Box>
      </Grid>
    </Grid>
      <CapturaAvanceModal 
        open={modalOpen}
        onClose={() => setModalOpen(false)}
        meta={selectedMeta}
        proyectoId={selectedPyId}
        onSuccess={() => {
            setModalOpen(false);
            // Invalidate metasCache for this project so it re-fetches
            setMetasCache(prev => ({ ...prev, [selectedPyId]: null }));
            handleToggleProyecto(selectedPyId); // Toggle off
            setTimeout(() => handleToggleProyecto(selectedPyId), 100); // Toggle on to re-fetch
        }}
      />
    </>
  );
}
