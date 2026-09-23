import { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import { FiChevronDown, FiChevronUp } from 'react-icons/fi';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';
import useAuth from '../../hooks/useAuth';
import { Edit, Delete, Visibility } from '@mui/icons-material';
import { IconButton, Tooltip, Popover, Snackbar, Alert } from '@mui/material';
import Iconify from '../../components/Iconify';

const cuadrante = (p, i) => {
  p = Number(p); i = Number(i);
  if (p > 5 && i > 5) return 'QI';
  if (p > 5 && i <= 5) return 'QII';
  if (p <= 5 && i <= 5) return 'QIII';
  return 'QIV';
};

const ESTADOS = ['Borrador', 'En revisión', 'Devuelto con observaciones', 'Validado'];

const proyectosDeRiesgo = (r) => {
  const map = new Map();
  (r?.proyectos || []).forEach(p => {
    if (p?.proyecto_id) map.set(String(p.proyecto_id), p);
  });
  (r?.actividades || []).forEach(a => {
    const py = a?.proyecto;
    if (py?.proyecto_id) map.set(String(py.proyecto_id), py);
  });
  return Array.from(map.values());
};

const proyectoLabel = (p) => {
  const clave = [p?.urg_num, p?.ro_num, p?.pg_num, p?.sp_num, p?.py_num ?? p?.numero]
    .filter(v => v !== null && v !== undefined && v !== '')
    .join('-');
  const nombre = p?.nombre || '';
  return clave ? `${clave} ${nombre}`.trim() : (nombre || p?.clave || '');
};

const proyectosParaMostrar = (r) => {
  const linked = proyectosDeRiesgo(r);
  return linked.length ? linked : (r?.proyectos_area || []);
};

const actividadesParaMostrar = (r) => {
  const linked = r?.actividades || [];
  return linked.length ? linked : (r?.actividades_area || []);
};

export default function RiesgosPage() {
  const [riesgos, setRiesgos] = useState([]);
  const [areas, setAreas] = useState([]);
  const [areaId, setAreaId] = useState('');
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [editorOpen, setEditorOpen] = useState(false);
  const [editRiesgo, setEditRiesgo] = useState(null);
  const [editorProyectoId, setEditorProyectoId] = useState('');
  const [editorProyectos, setEditorProyectos] = useState([]);
  const [editorAreaId, setEditorAreaId] = useState('');
  const [fichaRapidaOpen, setFichaRapidaOpen] = useState(false);
  const [selectedRiesgo, setSelectedRiesgo] = useState(null);
  const [actividades, setActividades] = useState([]);
  const [formData, setFormData] = useState({});
  const [fichaAnchor, setFichaAnchor] = useState(null);
  const [fichaRisk, setFichaRisk] = useState(null);
  const [expandedURs, setExpandedURs] = useState({});
  const [errorMsg, setErrorMsg] = useState('');
  const [snackbarOpen, setSnackbarOpen] = useState(false);
  const [deleteConfirmOpen, setDeleteConfirmOpen] = useState(false);
  const [riskToDelete, setRiskToDelete] = useState(null);

  const showError = (msg) => {
    setErrorMsg(msg);
    setSnackbarOpen(true);
  };
  
  const openFicha = (event, r) => {
    setFichaAnchor(event.currentTarget);
    setFichaRisk(r);
  };
  const closeFicha = () => {
    setFichaAnchor(null);
    setFichaRisk(null);
  };
  const navigate = useNavigate();
  const ejercicio = useGlobalStore((s) => s.ejercicio);
  const { user } = useAuth();

  useEffect(() => {
    axios.get('/unidades-responsables').then(res => {
      let data = res.data.data || res.data;
      data = [...data].sort((a, b) => (a.nombre || a.denominacion || '').localeCompare(b.nombre || b.denominacion || ''));
      setAreas(data);
      if (data.length > 0) {
        const firstId = String(data[0].unidad_responsable_gasto_id || data[0].id_unidad || data[0].id);
        setAreaId(firstId);
      }
    });
  }, []);

  useEffect(() => {
    fetchRiesgos();
    fetchActividades();
  }, [areaId, ejercicio]);

  const fetchRiesgos = async () => {
    setLoading(true);
    try {
      // Fetch all if areaId is empty ("Todas las áreas asignadas")
      const url = areaId ? `/riesgos?ejercicio_id=${ejercicio}&area_id=${areaId}` : `/riesgos?ejercicio_id=${ejercicio}`;
      const res = await axios.get(url);
      setRiesgos(res.data.data || res.data || []);
    } finally {
      setLoading(false);
    }
  };

  const fetchActividades = async () => {
    try {
      const url = areaId ? `/actividades-sustantivas?area_id=${areaId}` : `/actividades-sustantivas`;
      const res = await axios.get(url);
      setActividades(res.data.data || res.data || []);
    } catch { setActividades([]); }
  };

  const openEditor = (r = null) => {
    setEditRiesgo(r);
    const linkedActs = r?.actividades || [];
    const linkedActIds = linkedActs.map(a => String(a.id ?? a.accion_sustantiva_id)).filter(Boolean);
    const linkedProyectoId = linkedActs.find(a => a.proyecto_id)?.proyecto_id;
    const riesgoProyectos = proyectosDeRiesgo(r).map(p => ({
      id: p.proyecto_id,
      nombre: p.nombre || '',
      clave: proyectoLabel(p),
    }));
    setFormData(r ? {
      local_id: r.local_id,
      objetivo: r.objetivo || '',
      efectos_consecuencias: r.efectos_consecuencias || '',
      riesgo: r.riesgo,
      factores: r.factores || '',
      factores_internos: r.factores_internos || '',
      factores_externos: r.factores_externos || '',
      control: (r.controles || [])[0]?.texto || '',
      control_estado: (r.controles || [])[0]?.estado_validacion || 'Propuesto',
      ev_tipo: (r.controles || [])[0]?.evidencia_tipo || '',
      ev_ref: (r.controles || [])[0]?.evidencia_referencia || '',
      ev_periodo: (r.controles || [])[0]?.evidencia_periodicidad || '',
      ev_resp: (r.controles || [])[0]?.evidencia_responsable || '',
      indicador: (r.indicadores || [])[0]?.nombre || '',
      probabilidad: r.probabilidad || 2,
      impacto: r.impacto || 8,
      actividades: linkedActIds,
    } : {
      local_id: '',
      objetivo: '', efectos_consecuencias: '', riesgo: '',
      factores: '', factores_internos: '', factores_externos: '',
      control: '', control_estado: 'Propuesto',
      ev_tipo: '', ev_ref: '', ev_periodo: '', ev_resp: '',
      indicador: '', probabilidad: 2, impacto: 8, actividades: [],
    });
    setEditorAreaId(r ? r.area_id : areaId);
    setEditorProyectos(riesgoProyectos);
    setEditorProyectoId(linkedProyectoId ? String(linkedProyectoId) : (riesgoProyectos[0] ? String(riesgoProyectos[0].id) : ''));
    setEditorOpen(true);
  };

  const handleProyectoSelect = (proyectoId) => {
    setEditorProyectoId(proyectoId);
    if (!editRiesgo && proyectoId) {
      // Calculate how many risks belong to this project
      const projectActivitiesIds = actividades.filter(a => String(a.proyecto_id) === proyectoId).map(a => String(a.id));
      let count = 0;
      riesgos.forEach(r => {
        const rActIds = (r.actividades || []).map(a => String(a.id || a));
        if (rActIds.some(id => projectActivitiesIds.includes(id))) {
          count++;
        }
      });
      setFormData(prev => ({ ...prev, local_id: `R${count + 1}` }));
    } else if (!editRiesgo) {
      setFormData(prev => ({ ...prev, local_id: '' }));
    }
  };

  const handleField = (key, val) => setFormData(prev => ({ ...prev, [key]: val }));

  const handleActividadToggle = (id) => {
    const idStr = String(id);
    setFormData(prev => ({
      ...prev,
      actividades: prev.actividades.includes(idStr)
        ? prev.actividades.filter(a => a !== idStr)
        : [...prev.actividades, idStr],
    }));
  };

  const handleActividadSeleccion = (id) => {
    setFormData(prev => ({ ...prev, actividades: id ? [String(id)] : [] }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!formData.objetivo || formData.objetivo.trim() === '') {
      showError('Debe ingresar un objetivo.');
      return;
    }
    if (formData.probabilidad === '' || formData.probabilidad === null || formData.probabilidad === undefined) {
      showError('Debe ingresar una probabilidad.');
      return;
    }
    if (formData.impacto === '' || formData.impacto === null || formData.impacto === undefined) {
      showError('Debe ingresar un impacto.');
      return;
    }
    const payload = {
      area_id: editorAreaId || areaId,
      ejercicio_id: ejercicio,
      local_id: formData.local_id,
      objetivo: formData.objetivo,
      efectos_consecuencias: formData.efectos_consecuencias,
      riesgo: formData.riesgo,
      factores: formData.factores,
      factores_internos: formData.factores_internos,
      factores_externos: formData.factores_externos,
      probabilidad: Number(formData.probabilidad),
      impacto: Number(formData.impacto),
      status: editRiesgo?.status || 'Borrador',
      controles: formData.control ? [{
        texto: formData.control,
        estado_validacion: formData.control_estado || 'Propuesto',
        evidencia_tipo: formData.ev_tipo,
        evidencia_referencia: formData.ev_ref,
        evidencia_periodicidad: formData.ev_periodo,
        evidencia_responsable: formData.ev_resp,
      }] : [],
      indicadores: formData.indicador ? [{ nombre: formData.indicador, tipo: 'Riesgo', periodicidad: 'Trimestral' }] : [],
      actividades: formData.actividades,
    };
    try {
      if (editRiesgo) {
        await axios.put(`/riesgos/${editRiesgo.id}`, payload);
      } else {
        await axios.post('/riesgos', payload);
      }
      setEditorOpen(false);
      fetchRiesgos();
    } catch (err) {
      showError('Error al guardar: ' + (err.response?.data?.error || err.response?.data?.message || err.message));
    }
  };

  const handleTransicion = async (riesgo, accion) => {
    const statusMap = {
      submit: 'En revisión',
      validate: 'Validado',
      reopen: 'Borrador',
    };
    let obs = '';
    if (accion === 'return') {
      obs = prompt('Observación para devolución:');
      if (!obs) return;
    }
    try {
      await axios.put(`/riesgos/${riesgo.id}`, {
        ...riesgo,
        status: accion === 'return' ? 'Devuelto con observaciones' : statusMap[accion],
        last_observation: obs || riesgo.last_observation,
      });
      fetchRiesgos();
    } catch (err) {
      showError('Error: ' + (err.response?.data?.message || err.message));
    }
  };

  const confirmDelete = (id) => {
    setRiskToDelete(id);
    setDeleteConfirmOpen(true);
  };

  const handleDelete = async () => {
    setDeleteConfirmOpen(false);
    if (!riskToDelete) return;
    try {
      await axios.delete(`/riesgos/${riskToDelete}`);
      fetchRiesgos();
    } catch (err) {
      showError('Error al eliminar: ' + (err.response?.data?.message || err.message));
    }
  };

  const filtrados = riesgos.filter(r =>
    (!search || (r.riesgo + ' ' + (r.objetivo || '') + ' ' + (r.local_id || '')).toLowerCase().includes(search.toLowerCase()))
  );

  const canCapture = true; // simplify: all users can capture
  const canValidate = user?.role === 'Administrador';
  const canAdmin = user?.role === 'Administrador';

  return (
    <>
      <div className="page-head">
        <div>
          <h1>Objetivos y Riesgos</h1>
          <p>Edición directa de objetivo, riesgo y valoración.</p>
        </div>
        <div className="head-actions">
          {canCapture && (
            <button className="btn primary" onClick={() => openEditor()}>+ Agregar riesgo</button>
          )}
        </div>
      </div>

      <section className="panel" style={{ padding: '20px', marginBottom: '20px' }}>
        <div className="form-grid" style={{ gridTemplateColumns: '1fr 2fr', gap: '20px' }}>
          <div>
            <label style={{ display: 'block', marginBottom: '5px', fontWeight: 600 }}>Área / Unidad Responsable</label>
            <select className="input" style={{ width: '100%' }} value={areaId} onChange={e => setAreaId(e.target.value)}>
              {areas.map((a, i) => (
                <option key={a.unidad_responsable_gasto_id || a.id_unidad || a.id || i} value={String(a.unidad_responsable_gasto_id || a.id_unidad || a.id)}>
                  {a.nombre || a.denominacion}
                </option>
              ))}
            </select>
          </div>
          <div>
            <label style={{ display: 'block', marginBottom: '5px', fontWeight: 600 }}>Buscar</label>
            <input className="input" style={{ width: '100%' }} placeholder="Clave, objetivo o riesgo..." value={search} onChange={e => setSearch(e.target.value)} />
          </div>
        </div>
      </section>



      {loading ? (
        <p>Cargando…</p>
      ) : (
        areas.map((area, i) => {
          const urgId = String(area.unidad_responsable_gasto_id || area.id_unidad || area.id);
          
          if (areaId && areaId !== urgId) return null;

          const areaName = area.nombre || area.denominacion || 'Área desconocida';
          const title = `${areaName}`;
          
          // Filtrar los riesgos correspondientes a esta UR
          const riesgosUR = filtrados.filter(r => String(r.area_id) === urgId);

          const isExpanded = expandedURs[urgId] !== false;
          const toggleExpanded = () => setExpandedURs(prev => ({ ...prev, [urgId]: isExpanded ? false : true }));

          return (
            <section className="panel" key={urgId || i} style={{ marginBottom: '24px' }}>
              <div className="panel-head" style={{ marginBottom: '8px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <h2 style={{ fontSize: '1.25rem', fontWeight: 600 }}>
                  {title}
                  <span style={{ fontWeight: 'normal', color: '#666', marginLeft: '8px', fontSize: '1rem' }}>
                    {riesgosUR.length} riesgo(s)
                  </span>
                </h2>
                <button 
                  type="button" 
                  className="btn btn-outline" 
                  style={{ 
                    padding: '6px', 
                    fontSize: '1.2rem',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    border: 'none',
                    background: 'transparent',
                    cursor: 'pointer'
                  }} 
                  onClick={toggleExpanded}
                  title={isExpanded ? 'Comprimir' : 'Expandir'}
                >
                  {isExpanded ? <FiChevronUp /> : <FiChevronDown />}
                </button>
              </div>

              {isExpanded && (
                <div style={{ overflowX: 'auto', width: '100%' }}>
                  <table className="data-table" style={{ width: '100%', tableLayout: 'fixed', borderCollapse: 'collapse', marginTop: '16px' }}>
                    <thead>
                      <tr style={{ backgroundColor: '#1F4E79', color: '#fff' }}>
                        <th style={{ color: '#fff', width: '6%', textAlign: 'center' }}>ID</th>
                        <th style={{ color: '#fff', width: '30%' }}>Objetivo</th>
                        <th style={{ color: '#fff', width: '28%' }}>Riesgo</th>
                        <th style={{ color: '#fff', width: '4%', textAlign: 'center' }}>P</th>
                        <th style={{ color: '#fff', width: '4%', textAlign: 'center' }}>I</th>
                        <th style={{ color: '#fff', width: '8%', textAlign: 'center' }}>Cuadrante</th>
                        <th style={{ color: '#fff', width: '8%', textAlign: 'center' }}>Estatus</th>
                        <th style={{ color: '#fff', width: '12%', textAlign: 'center' }}>Acciones</th>
                      </tr>
                    </thead>
                  <tbody>
                    {riesgosUR.length > 0 ? (
                      riesgosUR.map(r => (
                        <tr 
                          key={r.id}
                          onClick={(e) => openFicha(e, r)}
                          style={{ transition: 'background-color 0.2s', cursor: 'pointer', ':hover': { backgroundColor: '#f1f5f9' } }}
                        >
                          <td><b>{r.local_id}</b></td>
                          <td style={{ maxWidth: '300px' }}>{r.objetivo || '—'}</td>
                          <td style={{ maxWidth: '300px' }}>{r.riesgo}</td>
                          <td>{r.probabilidad}</td>
                          <td>{r.impacto}</td>
                          <td>{cuadrante(r.probabilidad, r.impacto)}</td>
                          <td>{r.status}</td>
                          <td style={{ width: '120px', verticalAlign: 'middle', textAlign: 'center' }}>
                            <div style={{ display: 'flex', justifyContent: 'center', gap: '4px' }}>
                              <Tooltip title="Editar">
                                <IconButton size="small" color="primary" onClick={() => openEditor(r)}>
                                  <Edit fontSize="small" />
                                </IconButton>
                              </Tooltip>

                              <Tooltip title="Eliminar">
                                <IconButton size="small" color="error" onClick={() => confirmDelete(r.id)}>
                                  <Delete fontSize="small" />
                                </IconButton>
                              </Tooltip>
                            </div>
                          </td>
                        </tr>
                      ))
                    ) : (
                      <tr>
                        <td colSpan="8" style={{ textAlign: 'center', color: '#6f8294', padding: '20px' }}>
                          Sin riesgos encontrados en esta unidad.
                        </td>
                      </tr>
                    )}
                  </tbody>
                </table>
                </div>
              )}
            </section>
          );
        })
      )}

      <Popover
        id="click-popover"
        open={Boolean(fichaAnchor)}
        anchorEl={fichaAnchor}
        anchorOrigin={{
          vertical: 'bottom',
          horizontal: 'left',
        }}
        transformOrigin={{
          vertical: 'top',
          horizontal: 'left',
        }}
        onClose={closeFicha}
        disableScrollLock
        PaperProps={{
          elevation: 4,
          sx: { borderRadius: '12px', mt: 1, p: 2, maxWidth: 500, minWidth: 350, border: '1px solid #e2e8f0', boxShadow: '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)' }
        }}
      >
        {fichaRisk && (
          <div>
            <h3 style={{ margin: '0 0 12px 0', fontSize: '1.1rem', color: '#1F4E79' }}>Ficha rápida · {fichaRisk.local_id}</h3>
            
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px', marginBottom: '8px' }}>
              <div>
                <span style={{ fontSize: '0.8rem', color: '#64748b', fontWeight: 600 }}>Estado</span>
                <div style={{ fontSize: '0.9rem' }}>{fichaRisk.status}</div>
              </div>
              <div>
                <span style={{ fontSize: '0.8rem', color: '#64748b', fontWeight: 600 }}>P / I</span>
                <div style={{ fontSize: '0.9rem' }}>{fichaRisk.probabilidad} / {fichaRisk.impacto}</div>
              </div>
            </div>

            <div style={{ marginBottom: '8px' }}>
              <span style={{ fontSize: '0.8rem', color: '#64748b', fontWeight: 600 }}>Objetivo</span>
              <div style={{ fontSize: '0.9rem' }}>{fichaRisk.objetivo || '—'}</div>
            </div>

            <div style={{ marginBottom: '8px' }}>
              <span style={{ fontSize: '0.8rem', color: '#64748b', fontWeight: 600 }}>Riesgo</span>
              <div style={{ fontSize: '0.9rem', fontWeight: 500, color: '#0f172a' }}>{fichaRisk.riesgo || '—'}</div>
            </div>

            <div>
              <span style={{ fontSize: '0.8rem', color: '#64748b', fontWeight: 600 }}>Factores</span>
              <div style={{ fontSize: '0.9rem' }}>
                {[fichaRisk.factores_internos, fichaRisk.factores_externos].filter(Boolean).length > 0
                  ? [fichaRisk.factores_internos, fichaRisk.factores_externos].filter(Boolean).join('; ')
                  : (fichaRisk.factores || '—')}
              </div>
            </div>
          </div>
        )}
      </Popover>

      {/* Premium Modal Editor */}
      {editorOpen && (
        <div style={{ position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, backgroundColor: 'rgba(15, 23, 42, 0.7)', zIndex: 1000, display: 'flex', alignItems: 'center', justifyContent: 'center', backdropFilter: 'blur(4px)' }}>
          <div style={{ backgroundColor: '#fff', borderRadius: '12px', width: '70%', maxHeight: '90vh', display: 'flex', flexDirection: 'column', boxShadow: '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)', overflow: 'hidden' }}>
            <div style={{ padding: '20px 24px', borderBottom: '1px solid #e2e8f0', backgroundColor: '#f8fafc', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <h2 style={{ margin: 0, fontSize: '1.25rem', color: '#0f172a', fontWeight: '600', display: 'flex', alignItems: 'center', gap: '8px' }}>
                <Edit fontSize="medium" color="primary" /> {editRiesgo ? `Editar ${editRiesgo.local_id}` : 'Nuevo riesgo'}
              </h2>
              <button type="button" onClick={() => setEditorOpen(false)} style={{ background: 'transparent', border: 'none', fontSize: '1.5rem', cursor: 'pointer', color: '#64748b', lineHeight: 1, padding: '4px' }}>&times;</button>
            </div>
            
            <form id="riesgo-form" onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', flex: 1, overflow: 'hidden' }}>
              <div style={{ padding: '24px', overflowY: 'auto', flex: 1 }} className="form-grid">
            
            {(() => {
              const uniqueProyectosMap = new Map();
              actividades.filter(a => !editorAreaId || String(a.area_id) === String(editorAreaId)).forEach(a => {
                if (a.proyecto_id !== undefined && a.proyecto_id !== null && !uniqueProyectosMap.has(String(a.proyecto_id))) {
                  uniqueProyectosMap.set(String(a.proyecto_id), {
                    id: a.proyecto_id,
                    nombre: a.proyecto_nombre,
                    clave: [a.urg_num, a.ro_num, a.pg_num, a.sp_num, a.py_num].filter(Boolean).join('-')
                  });
                }
              });
              editorProyectos.forEach(p => {
                if (p?.id !== undefined && p?.id !== null) {
                  uniqueProyectosMap.set(String(p.id), p);
                }
              });
              const uniqueProyectos = Array.from(uniqueProyectosMap.values());

              const actividadesDelProyecto = actividades.filter(a => !editorProyectoId || String(a.proyecto_id) === editorProyectoId);

              const actividadIdSeleccionada = formData.actividades?.length
                ? String(formData.actividades.find(id => actividadesDelProyecto.some(a => String(a.id) === String(id))) ?? '')
                : '';

              return (
                <div className="wide" style={{ marginBottom: '12px' }}>
                  <div style={{ display: 'grid', gridTemplateColumns: '120px 1fr', gap: '16px', alignItems: 'end', marginBottom: '16px' }}>
                    <label style={{ display: 'block', fontWeight: 'bold' }}>
                      ID local
                      <input 
                        className="input" 
                        style={{ width: '100%', marginTop: 5, backgroundColor: '#f1f5f9', color: '#64748b', fontWeight: 'bold' }} 
                        value={formData.local_id} 
                        disabled 
                      />
                    </label>
                    <label style={{ display: 'block', fontWeight: 'bold' }}>
                      UR
                      <select 
                        className="input" 
                        style={{ width: '100%', marginTop: 5, fontWeight: 'normal', backgroundColor: (areaId || editRiesgo) ? '#f1f5f9' : '#fff' }}
                        value={editorAreaId} 
                        onChange={e => setEditorAreaId(e.target.value)}
                        disabled={!!areaId || !!editRiesgo}
                      >
                        <option value="">Seleccione UR...</option>
                        {areas.map((a, i) => (
                          <option key={a.unidad_responsable_gasto_id || a.id_unidad || a.id || i} value={String(a.unidad_responsable_gasto_id || a.id_unidad || a.id)}>
                            {a.nombre || a.denominacion}
                          </option>
                        ))}
                      </select>
                    </label>
                  </div>

                  {!editRiesgo && (
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px', marginBottom: '12px' }}>
                    <label style={{ display: 'block', fontWeight: 'bold' }}>
                      Proyecto
                      <select 
                        className="input" 
                        style={{ width: '100%', marginTop: 5, fontWeight: 'normal' }}
                        value={editorProyectoId} 
                        onChange={e => handleProyectoSelect(e.target.value)}
                      >
                        <option value="">Seleccione un proyecto...</option>
                        {uniqueProyectos.map(p => (
                          <option key={p.id} value={String(p.id)}>
                            {p.clave} {p.nombre}
                          </option>
                        ))}
                      </select>
                    </label>
                    <label style={{ display: 'block', fontWeight: 'bold' }}>
                      Actividad Sustantiva
                      <select 
                        className="input" 
                        style={{ width: '100%', marginTop: 5, fontWeight: 'normal' }}
                        value={actividadIdSeleccionada} 
                        onChange={e => handleActividadSeleccion(e.target.value)}
                      >
                        <option value="">Seleccione una actividad...</option>
                        {actividadesDelProyecto.map(a => (
                          <option key={String(a.id)} value={String(a.id)}>
                            {[a.numero, a.descripcion || a.denominacion || 'Actividad'].filter(Boolean).join(' · ')}
                          </option>
                        ))}
                      </select>
                    </label>
                    </div>
                  )}

                  {!editRiesgo && (
                    <fieldset style={{ border: '1px solid #cbd5e1', borderRadius: '8px', padding: '16px', backgroundColor: '#f8fafc', marginBottom: '4px' }}>
                      <legend style={{ fontWeight: '600', color: '#1e293b', padding: '0 8px', fontSize: '0.9rem' }}>Acciones sustantivas POA vinculadas</legend>
                      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px', maxHeight: '180px', overflowY: 'auto', paddingRight: '8px' }}>
                        {actividadesDelProyecto.map(a => (
                          <label className="check" key={a.id} style={{ display: 'flex', alignItems: 'flex-start', gap: '8px', fontSize: '0.85rem', backgroundColor: '#ffffff', padding: '10px', borderRadius: '6px', border: '1px solid #e2e8f0', cursor: 'pointer', transition: 'all 0.2s', margin: 0, boxShadow: '0 1px 2px 0 rgba(0, 0, 0, 0.05)' }}>
                            <input type="checkbox"
                              style={{ marginTop: '2px', accentColor: '#0f172a', width: '16px', height: '16px', cursor: 'pointer' }}
                              checked={formData.actividades?.includes(String(a.id))}
                              onChange={() => handleActividadToggle(a.id)} />
                            <span style={{ lineHeight: 1.3, color: '#334155', flex: 1 }}>{a.descripcion || a.denominacion || 'Actividad sin nombre'}</span>
                          </label>
                        ))}
                      </div>
                    </fieldset>
                  )}
                  {!editRiesgo && (
                    <p style={{ margin: '8px 0 0 0', fontSize: '0.8rem', color: '#64748b' }}>Si no selecciona alguna acción, al guardar se vincularán automáticamente las acciones sustantivas del área para el ejercicio {ejercicio}.</p>
                  )}
                </div>
              );
            })()}
            <label className="wide">Riesgo *
              <textarea className="input" required value={formData.riesgo} onChange={e => handleField('riesgo', e.target.value)} />
            </label>
            <label>Objetivo *
              <textarea className="input" required value={formData.objetivo} onChange={e => handleField('objetivo', e.target.value)} />
            </label>
            <div className="form-grid" style={{ gridTemplateColumns: '1fr 1fr 1fr', gap: '12px', marginTop: '12px' }}>
              <label>Probabilidad (0–10) *
                <input type="number" min={0} max={10} step={1} className="input" required value={formData.probabilidad} onChange={e => handleField('probabilidad', e.target.value)} />
              </label>
              <label>Impacto (0–10) *
                <input type="number" min={0} max={10} step={1} className="input" required value={formData.impacto} onChange={e => handleField('impacto', e.target.value)} />
              </label>
              <label>Cuadrante
                <input 
                  type="text" 
                  className="input" 
                  value={cuadrante(formData.probabilidad || 0, formData.impacto || 0)} 
                  disabled 
                  style={{ backgroundColor: '#f1f5f9', color: '#64748b', fontWeight: 'bold' }}
                />
              </label>
            </div>



              </div>
            
              <div style={{ padding: '16px 24px', borderTop: '1px solid #e2e8f0', backgroundColor: '#f8fafc', display: 'flex', justifyContent: 'flex-end', gap: '12px' }}>
                <button className="btn" type="button" onClick={() => setEditorOpen(false)}>Cancelar</button>
                <button className="btn primary" type="submit">Guardar riesgo</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Ficha Rápida Modal */}
      {fichaRapidaOpen && selectedRiesgo && (
        <div style={{ position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1000, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
          <div style={{ backgroundColor: '#fff', borderRadius: '8px', width: '800px', maxWidth: '90%', maxHeight: '90vh', overflowY: 'auto', boxShadow: '0 4px 20px rgba(0,0,0,0.15)' }}>
            <div style={{ padding: '20px', borderBottom: '1px solid #eee' }}>
              <h2 style={{ margin: 0, fontSize: '1.2rem', color: '#1F4E79' }}>Ficha rápida · {selectedRiesgo.local_id}</h2>
            </div>
            
            <div style={{ padding: '20px' }}>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px', marginBottom: '20px' }}>
                <div>
                  <div style={{ fontSize: '0.7rem', fontWeight: 'bold', color: '#777', textTransform: 'uppercase', marginBottom: '4px' }}>Área</div>
                  <div>{selectedRiesgo.area?.nombre || selectedRiesgo.area?.denominacion || areas.find(a => String(a.id_unidad || a.id) === String(selectedRiesgo.area_id))?.nombre || areas.find(a => String(a.id_unidad || a.id) === String(selectedRiesgo.area_id))?.denominacion || '—'}</div>
                </div>
                <div>
                  <div style={{ fontSize: '0.7rem', fontWeight: 'bold', color: '#777', textTransform: 'uppercase', marginBottom: '4px' }}>Estado</div>
                  <div>{selectedRiesgo.status}</div>
                </div>
              </div>

              <div style={{ marginBottom: '20px' }}>
                <div style={{ fontSize: '0.7rem', fontWeight: 'bold', color: '#777', textTransform: 'uppercase', marginBottom: '4px' }}>Objetivo</div>
                <div>{selectedRiesgo.objetivo || '—'}</div>
              </div>

              <div style={{ marginBottom: '20px' }}>
                <div style={{ fontSize: '0.7rem', fontWeight: 'bold', color: '#777', textTransform: 'uppercase', marginBottom: '4px' }}>Efectos / consecuencias</div>
                <div>{selectedRiesgo.efectos_consecuencias || '—'}</div>
              </div>

              <div style={{ marginBottom: '20px' }}>
                <div style={{ fontSize: '0.7rem', fontWeight: 'bold', color: '#777', textTransform: 'uppercase', marginBottom: '4px' }}>Riesgo</div>
                <div style={{ fontWeight: 'bold' }}>{selectedRiesgo.riesgo}</div>
              </div>

              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px', marginBottom: '20px' }}>
                <div>
                  <div style={{ fontSize: '0.7rem', fontWeight: 'bold', color: '#777', textTransform: 'uppercase', marginBottom: '4px' }}>Probabilidad / Impacto</div>
                  <div>{selectedRiesgo.probabilidad} / {selectedRiesgo.impacto}</div>
                </div>
                <div>
                  <div style={{ fontSize: '0.7rem', fontWeight: 'bold', color: '#777', textTransform: 'uppercase', marginBottom: '4px' }}>Responsable</div>
                  <div>—</div>
                </div>
              </div>

              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px', marginBottom: '20px' }}>
                <div>
                  <div style={{ fontSize: '0.7rem', fontWeight: 'bold', color: '#777', textTransform: 'uppercase', marginBottom: '4px' }}>Factores internos</div>
                  <div>{selectedRiesgo.factores_internos || '—'}</div>
                </div>
                <div>
                  <div style={{ fontSize: '0.7rem', fontWeight: 'bold', color: '#777', textTransform: 'uppercase', marginBottom: '4px' }}>Factores externos</div>
                  <div>{selectedRiesgo.factores_externos || '—'}</div>
                </div>
              </div>

              <div style={{ marginBottom: '20px' }}>
                <div style={{ fontSize: '0.7rem', fontWeight: 'bold', color: '#777', textTransform: 'uppercase', marginBottom: '4px' }}>Controles</div>
                <div>{selectedRiesgo.controles?.map(c => c.texto).join('; ') || '—'}</div>
              </div>

              <div style={{ marginBottom: '20px' }}>
                <div style={{ fontSize: '0.7rem', fontWeight: 'bold', color: '#777', textTransform: 'uppercase', marginBottom: '4px' }}>Acciones sustantivas POA vinculadas</div>
                <div>
                  {selectedRiesgo.actividades?.length > 0 
                    ? selectedRiesgo.actividades.map(a => `${a.numero || ''} ${a.descripcion || a.denominacion || a.texto}`).join(' - ')
                    : '—'}
                </div>
              </div>
            </div>

            <div style={{ padding: '15px 20px', borderTop: '1px solid #eee', display: 'flex', justifyContent: 'center', gap: '15px', backgroundColor: '#f9f9f9', borderBottomLeftRadius: '8px', borderBottomRightRadius: '8px' }}>
              <button className="btn" onClick={() => setFichaRapidaOpen(false)}>Cerrar</button>
              <button className="btn primary" onClick={() => { setFichaRapidaOpen(false); openEditor(selectedRiesgo); }}>Abrir ficha completa</button>
            </div>
          </div>
        </div>
      )}

      {/* Modal de Error Estilizado */}
      {snackbarOpen && (
        <div style={{ position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, backgroundColor: 'rgba(15, 23, 42, 0.7)', zIndex: 2000, display: 'flex', alignItems: 'center', justifyContent: 'center', backdropFilter: 'blur(4px)' }}>
          <div style={{ backgroundColor: '#fff', borderRadius: '12px', width: '400px', maxWidth: '90%', padding: '24px', boxShadow: '0 20px 25px -5px rgba(0, 0, 0, 0.1)' }}>
            <div style={{ display: 'flex', alignItems: 'center', marginBottom: '16px', color: '#d32f2f' }}>
              <Iconify icon="ph:x-circle-fill" sx={{ width: 32, height: 32, marginRight: '12px' }} />
              <h3 style={{ margin: 0, fontSize: '1.25rem' }}>Error</h3>
            </div>
            <p style={{ margin: '0 0 24px 0', color: '#475569', fontSize: '1rem', lineHeight: 1.5 }}>
              {errorMsg}
            </p>
            <div style={{ display: 'flex', justifyContent: 'flex-end' }}>
              <button className="btn primary" onClick={() => setSnackbarOpen(false)}>Aceptar</button>
            </div>
          </div>
        </div>
      )}

      {/* Modal de confirmación de eliminación */}
      {deleteConfirmOpen && (
        <div style={{ position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, backgroundColor: 'rgba(15, 23, 42, 0.7)', zIndex: 2000, display: 'flex', alignItems: 'center', justifyContent: 'center', backdropFilter: 'blur(4px)' }}>
          <div style={{ backgroundColor: '#fff', borderRadius: '12px', width: '400px', maxWidth: '90%', padding: '24px', boxShadow: '0 20px 25px -5px rgba(0, 0, 0, 0.1)' }}>
            <h3 style={{ margin: '0 0 16px', color: '#0f172a', fontSize: '1.2rem', display: 'flex', alignItems: 'center', gap: '8px' }}>
              <Delete color="error" /> Confirmar baja
            </h3>
            <p style={{ margin: '0 0 24px', color: '#475569', fontSize: '1rem' }}>
              ¿Estás seguro de que deseas dar de baja este riesgo?
            </p>
            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '12px' }}>
              <button className="btn" onClick={() => setDeleteConfirmOpen(false)}>Cancelar</button>
              <button className="btn" style={{ backgroundColor: '#ef4444', borderColor: '#ef4444', color: '#fff' }} onClick={handleDelete}>Sí, dar de baja</button>
            </div>
          </div>
        </div>
      )}
    </>
  );
}
