import { useState, useEffect } from 'react';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';
import Iconify from '../../components/Iconify';
import logoTecdmx from '../../assets/logo_tecdmx.png';

const cuadrante = (p, i) => {
  p = Number(p); i = Number(i);
  if (p > 5 && i > 5) return 'QI';
  if (p > 5 && i <= 5) return 'QII';
  if (p <= 5 && i <= 5) return 'QIII';
  return 'QIV';
};

export default function RiesgosInstitucionalesPage() {
  const [riesgosBase, setRiesgosBase] = useState([]);
  const [riesgosInstitucionales, setRiesgosInstitucionales] = useState([]);
  const [areas, setAreas] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedIds, setSelectedIds] = useState([]);
  const [selectedUR, setSelectedUR] = useState('ALL');
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [editId, setEditId] = useState(null);
  const [originalData, setOriginalData] = useState({ risk: '', selectedIds: [] });
  const [confirmDialog, setConfirmDialog] = useState({ open: false, type: '', action: null, title: '', message: '' });
  const [newIR, setNewIR] = useState({ objective: '', risk: '', factores: '', control: '', indicador: '', probability: 0, impact: 0 });
  const [suggested, setSuggested] = useState({ probability: 0, impact: 0, quadrant: '' });
  const [fuentesModalData, setFuentesModalData] = useState(null);
  const [previewModalOpen, setPreviewModalOpen] = useState(false);

  const ejercicio = useGlobalStore((s) => s.ejercicio);

  useEffect(() => {
    fetchData();
  }, [ejercicio]);

  const fetchData = async () => {
    setLoading(true);
    try {
      const [resBase, resInst, resAreas] = await Promise.all([
        axios.get(`/riesgos?ejercicio_id=${ejercicio}`),
        axios.get(`/riesgos-institucionales?ejercicio_id=${ejercicio}`),
        axios.get('/unidades-responsables')
      ]);
      
      const allRiesgos = resBase.data.data || resBase.data;
      setRiesgosBase(allRiesgos.filter(r => r.status === 'Validado' || r.estatus === 'Validado'));
      setRiesgosInstitucionales(resInst.data.data || resInst.data);
      setAreas(resAreas.data.data || resAreas.data);
    } catch (e) {
      console.error(e);
    }
    setLoading(false);
  };

  const areaName = (id) => {
    const a = areas.find(x => String(x.unidad_responsable_gasto_id || x.id_unidad || x.id) === String(id));
    return a ? (a.nombre || a.denominacion) : id;
  };

  const handleCheckbox = (id) => {
    setSelectedIds(prev => prev.includes(id) ? prev.filter(x => x !== id) : [...prev, id]);
  };

  const handleOpenDrawer = (r = null) => {
    setSuggested({ probability: 0, impact: 0, quadrant: '' });
    if (r && r.id) {
      setEditId(r.id);
      setNewIR({
        objective: r.objetivo || '',
        risk: r.riesgo || '',
        factores: r.factores || '',
        control: r.control || '',
        indicador: r.indicador || '',
        probability: r.probabilidad || 0,
        impact: r.impacto || 0
      });
      const ids = (r.fuentes || []).map(f => f.id);
      setSelectedIds(ids);
      setOriginalData({ risk: r.riesgo || '', selectedIds: ids });
    } else {
      setEditId(null);
      setNewIR({
        objective: '',
        risk: '',
        factores: '',
        control: '',
        indicador: '',
        probability: 0,
        impact: 0
      });
      setSelectedIds([]);
      setOriginalData({ risk: '', selectedIds: [] });
    }
    setSelectedUR('ALL');
    setDrawerOpen(true);
  };

  useEffect(() => {
    if (selectedIds.length > 0) {
      const selected = riesgosBase.filter(r => selectedIds.includes(r.id));
      const avgP = Math.round(selected.reduce((acc, r) => acc + (r.probabilidad || 0), 0) / selected.length);
      const avgI = Math.round(selected.reduce((acc, r) => acc + (r.impacto || 0), 0) / selected.length);
      setSuggested({
        probability: avgP,
        impact: avgI,
        quadrant: cuadrante(avgP, avgI)
      });
      setNewIR(prev => ({ ...prev, probability: avgP, impact: avgI }));
    } else {
      setSuggested({ probability: 0, impact: 0, quadrant: '' });
      setNewIR(prev => ({ ...prev, probability: 0, impact: 0 }));
    }
  }, [selectedIds, riesgosBase]);

  const riesgosPorArea = areas.map(a => {
    const aId = String(a.unidad_responsable_gasto_id || a.id_unidad || a.id);
    return {
      area: a,
      riesgos: riesgosBase.filter(r => String(r.area_id) === aId && originalData.selectedIds.includes(r.id))
    };
  });

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (selectedIds.length === 0) {
      alert('Debe seleccionar al menos un Riesgo de Unidad Responsable.');
      return;
    }

    try {
      const payload = {
        ejercicio_id: ejercicio,
        objetivo: newIR.objective,
        riesgo: newIR.risk,
        control: newIR.control,
        indicador: newIR.indicador,
        probabilidad_sugerida: suggested.probability,
        impacto_sugerido: suggested.impact,
        probabilidad: newIR.probability,
        impacto: newIR.impact,
        sourceRiskIds: selectedIds,
        factores: newIR.factores || riesgosBase.filter(r => selectedIds.includes(r.id)).map(x => x.factores).filter(Boolean).join('; ')
      };

      if (editId) {
        await axios.put(`/riesgos-institucionales/${editId}`, payload);
      } else {
        await axios.post('/riesgos-institucionales', payload);
      }

      setDrawerOpen(false);
      setSelectedIds([]);
      setEditId(null);
      fetchData();
    } catch (err) {
      console.error(err);
      alert('Error guardando el riesgo institucional');
    }
  };

  const handleDelete = (r) => {
    setConfirmDialog({
      open: true,
      type: 'delete',
      title: 'Eliminar Riesgo Institucional',
      message: '¿Está seguro de eliminar este riesgo? Las relaciones con los Riesgos de UR (RUR) quedarán liberadas automáticamente.',
      action: async () => {
        try {
          await axios.delete(`/riesgos-institucionales/${r.id}`);
          setConfirmDialog(p => ({ ...p, open: false }));
          fetchData();
        } catch (e) {
          alert('Error eliminando el riesgo');
        }
      }
    });
  };

  const handleCancelClick = () => {
    const isChanged = newIR.risk !== originalData.risk || JSON.stringify([...selectedIds].sort()) !== JSON.stringify([...originalData.selectedIds].sort());
    if (isChanged) {
      setConfirmDialog({
        open: true,
        type: 'cancel',
        title: 'Cambios sin guardar',
        message: '¿Está seguro de que desea salir? Se perderán los cambios que no haya guardado.',
        action: () => {
          setConfirmDialog(p => ({ ...p, open: false }));
          setDrawerOpen(false);
        }
      });
    } else {
      setDrawerOpen(false);
    }
  };

  return (
    <>
      <div className="page-head">
        <div>
          <h1>Riesgos Institucionales</h1>
          <p>Proyecto de MAR Institucional y seguimiento de riesgos consolidados.</p>
        </div>
        <div className="head-actions">
        </div>
      </div>
      <div className="notice" style={{ backgroundColor: '#eef4f8', padding: '16px', borderRadius: '8px', marginBottom: '24px', color: '#142b45', borderLeft: '4px solid #0b3a63' }}>
        <strong>Regla de trazabilidad:</strong> los riesgos institucionales vinculan riesgos de UR. El impacto y la probabilidad sugeridos se calculan como el promedio de los riesgos fuente seleccionados.
      </div>

      <section className="panel" style={{ padding: '24px' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
          <h2 style={{ fontSize: '1.25rem', margin: 0, color: '#17324d' }}>MAR Institucional</h2>
          <button
            type="button"
            className="icon-btn"
            onClick={() => setPreviewModalOpen(true)}
            title="Vista preliminar para impresión"
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              gap: '6px',
              padding: '6px 14px',
              borderRadius: '6px',
              background: '#f8fafc',
              border: '1px solid #cbd5e1',
              color: '#1e293b',
              cursor: 'pointer',
              fontWeight: 600,
              fontSize: '0.875rem',
              transition: 'all 0.2s',
            }}
          >
            <Iconify icon="ph:printer" sx={{ width: 18, height: 18 }} />
            <span>Imprimir</span>
          </button>
        </div>
        <div style={{ overflowX: 'auto' }}>
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Riesgo institucional</th>
                <th>Fuentes</th>
                <th>P/I institucional</th>
                <th>Cuadrante</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr><td colSpan="6" className="muted" style={{ textAlign: 'center', padding: '20px' }}>Cargando...</td></tr>
              ) : riesgosInstitucionales.length > 0 ? (
                riesgosInstitucionales.map(r => {
                  return (
                    <tr key={r.id}>
                      <td><b>{r.folio}</b></td>
                      <td style={{ textAlign: 'justify', lineHeight: '1.45' }}>{r.riesgo}</td>
                      <td style={{ textAlign: 'justify' }}>
                        <div style={{ fontSize: '0.85rem', color: '#0f172a', lineHeight: '1.4', textAlign: 'justify' }}>
                          {Object.entries(
                            (r.fuentes || []).reduce((acc, f) => {
                              if (!acc[f.area_id]) acc[f.area_id] = [];
                              acc[f.area_id].push(f);
                              return acc;
                            }, {})
                          ).map(([aId, fs], idx, arr) => {
                            const idsStr = fs.map(f => f.local_id ? (String(f.local_id).startsWith('R') ? f.local_id : `R${f.local_id}`) : `R${f.id}`);
                            let formattedIds = idsStr[0] || '';
                            if (idsStr.length > 1) {
                               const last = idsStr.pop();
                               formattedIds = idsStr.join(', ') + ' y ' + last;
                            }
                            return (
                              <span key={aId}>
                                <a 
                                  href="#!" 
                                  onClick={(e) => { e.preventDefault(); setFuentesModalData({ areaName: areaName(aId), fuentes: fs, riFolio: r.folio }); }} 
                                  style={{ textDecoration: 'none', color: 'inherit' }}
                                >
                                  <strong>{areaName(aId)}:</strong> {formattedIds}
                                </a>
                                {idx < arr.length - 1 && <span style={{ margin: '0 6px', color: '#cbd5e1' }}>|</span>}
                              </span>
                            );
                          })}
                        </div>
                      </td>
                      <td>{r.probabilidad} / {r.impacto}</td>
                      <td>
                        <span className={`badge ${cuadrante(r.probabilidad, r.impacto).toLowerCase()}`}>
                          {cuadrante(r.probabilidad, r.impacto)}
                        </span>
                      </td>
                      <td>
                        <div style={{ display: 'flex', gap: '8px', alignItems: 'center' }}>
                          <button className="icon-btn" title="Editar" onClick={() => handleOpenDrawer(r)} style={{ background: 'none', border: 'none', cursor: 'pointer', color: '#3b82f6', padding: '4px' }}>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                          </button>
                          
                          {r.estatus === 'Validado' && (
                            <span title="Validado" style={{ color: '#10b981', padding: '4px', display: 'flex' }}>
                              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"></path></svg>
                            </span>
                          )}

                          <button className="icon-btn" title="Eliminar" onClick={() => handleDelete(r)} style={{ background: 'none', border: 'none', cursor: 'pointer', color: '#ef4444', padding: '4px' }}>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                  )
                })
              ) : (
                <tr><td colSpan="6" className="muted" style={{ textAlign: 'center', padding: '20px' }}>Aún no se han creado riesgos institucionales.</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </section>

      {/* Drawer Derecho */}
      {drawerOpen && (
        <div style={{
          position: 'fixed', inset: 0, background: 'rgba(15, 23, 42, 0.4)',
          zIndex: 99999, display: 'flex', justifyContent: 'flex-end',
          backdropFilter: 'blur(4px)'
        }}>
          <div style={{
            background: '#ffffff', width: '715px', maxWidth: '100%',
            height: '100%', display: 'flex', flexDirection: 'column',
            boxShadow: '-10px 0 25px rgba(0, 0, 0, 0.15)', overflow: 'hidden',
            animation: 'slideInRight 0.3s cubic-bezier(0.16, 1, 0.3, 1)'
          }}>
            <style>{`
              @keyframes slideInRight { 
                from { transform: translateX(100%); } 
                to { transform: translateX(0); } 
              }
              .ur-card { transition: all 0.2s ease; border: 1px solid #e2e8f0; }
              .ur-card:hover { border-color: #cbd5e1; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); transform: translateY(-1px); }
              .rur-label { transition: background 0.15s; border-radius: 8px; padding: 8px 10px; }
              .rur-label:hover { background-color: #f1f5f9; }
              .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
              .custom-scroll::-webkit-scrollbar-track { background: transparent; }
              .custom-scroll::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
              .custom-scroll::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }
            `}</style>
            
            {/* Header Compacto */}
            <div style={{ padding: '10px 16px', borderBottom: '1px solid #e2e8f0', display: 'flex', justifyContent: 'space-between', alignItems: 'center', background: '#f8fafc' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                <div style={{ width: '32px', height: '32px', borderRadius: '8px', background: '#e0f2fe', color: '#0284c7', display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: 'inset 0 2px 4px rgba(255,255,255,0.5)' }}>
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                </div>
                <div>
                  <h2 style={{ margin: 0, fontSize: '1.1rem', color: '#0f172a', fontWeight: '800' }}>Editar Riesgo Institucional</h2>
                  <p style={{ margin: '0', fontSize: '0.8rem', color: '#64748b' }}>Consolide los riesgos de las Unidades Responsables</p>
                </div>
              </div>
              <button onClick={() => setDrawerOpen(false)} style={{ background: 'transparent', border: 'none', width: '28px', height: '28px', borderRadius: '4px', fontSize: '1.2rem', cursor: 'pointer', color: '#64748b', display: 'flex', alignItems: 'center', justifyContent: 'center', transition: 'all 0.2s' }} onMouseOver={e => { e.currentTarget.style.background = '#e2e8f0'; e.currentTarget.style.color = '#0f172a'; }} onMouseOut={e => { e.currentTarget.style.background = 'transparent'; e.currentTarget.style.color = '#64748b'; }}>&times;</button>
            </div>
            
            {/* Body */}
            <div className="custom-scroll" style={{ overflowY: 'auto', padding: '16px', flex: 1, display: 'flex', flexDirection: 'column', gap: '16px' }}>
              <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', height: '100%' }}>
                
                <div style={{ display: 'flex', flexDirection: 'column', gap: '12px', flex: 1 }}>
                  {/* Campos añadidos */}
                  <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                    <div>
                      <label style={{ display: 'block', marginBottom: '2px', fontWeight: '600', color: '#475569', fontSize: '0.85rem' }}>Riesgo Institucional</label>
                      <textarea value={newIR.risk} onChange={e => setNewIR(p => ({ ...p, risk: e.target.value }))} rows="1" style={{ width: '100%', padding: '4px 8px', borderRadius: '6px', border: '1px solid #cbd5e1', fontSize: '0.9rem', outline: 'none', resize: 'vertical' }}></textarea>
                    </div>
                    <div>
                      <label style={{ display: 'block', marginBottom: '2px', fontWeight: '600', color: '#475569', fontSize: '0.85rem' }}>Objetivo</label>
                      <textarea value={newIR.objective} onChange={e => setNewIR(p => ({ ...p, objective: e.target.value }))} rows="1" style={{ width: '100%', padding: '4px 8px', borderRadius: '6px', border: '1px solid #cbd5e1', fontSize: '0.9rem', outline: 'none', resize: 'vertical' }}></textarea>
                    </div>
                    <div>
                      <label style={{ display: 'block', marginBottom: '2px', fontWeight: '600', color: '#475569', fontSize: '0.85rem' }}>Factores de riesgo</label>
                      <textarea value={newIR.factores || ''} onChange={e => setNewIR(p => ({ ...p, factores: e.target.value }))} rows="1" style={{ width: '100%', padding: '4px 8px', borderRadius: '6px', border: '1px solid #cbd5e1', fontSize: '0.9rem', outline: 'none', resize: 'vertical' }}></textarea>
                    </div>
                    <div>
                      <label style={{ display: 'block', marginBottom: '2px', fontWeight: '600', color: '#475569', fontSize: '0.85rem' }}>Control</label>
                      <textarea value={newIR.control || ''} onChange={e => setNewIR(p => ({ ...p, control: e.target.value }))} rows="1" style={{ width: '100%', padding: '4px 8px', borderRadius: '6px', border: '1px solid #cbd5e1', fontSize: '0.9rem', outline: 'none', resize: 'vertical' }}></textarea>
                    </div>
                    <div>
                      <label style={{ display: 'block', marginBottom: '2px', fontWeight: '600', color: '#475569', fontSize: '0.85rem' }}>Indicador</label>
                      <textarea value={newIR.indicador || ''} onChange={e => setNewIR(p => ({ ...p, indicador: e.target.value }))} rows="1" style={{ width: '100%', padding: '4px 8px', borderRadius: '6px', border: '1px solid #cbd5e1', fontSize: '0.9rem', outline: 'none', resize: 'vertical' }}></textarea>
                    </div>
                  </div>

                  {/* Resumen Promedios Compacto */}
                  <div style={{ display: 'flex', gap: '8px', backgroundColor: '#f0fdf4', padding: '6px 12px', borderRadius: '8px', border: '1px solid #bbf7d0', alignItems: 'center' }}>
                    <div style={{ flex: 1, borderRight: '1px dashed #86efac', display: 'flex', alignItems: 'center', gap: '6px' }}>
                      <span style={{ color: '#166534', fontSize: '0.7rem', fontWeight: '700', textTransform: 'uppercase' }}>Prob.</span>
                      <span style={{ fontSize: '1.1rem', fontWeight: '800', color: '#14532d' }}>{newIR.probability || 0}</span>
                    </div>
                    <div style={{ flex: 1, borderRight: '1px dashed #86efac', paddingLeft: '8px', display: 'flex', alignItems: 'center', gap: '6px' }}>
                      <span style={{ color: '#166534', fontSize: '0.7rem', fontWeight: '700', textTransform: 'uppercase' }}>Impacto</span>
                      <span style={{ fontSize: '1.1rem', fontWeight: '800', color: '#14532d' }}>{newIR.impact || 0}</span>
                    </div>
                    <div style={{ flex: 1.2, paddingLeft: '8px', display: 'flex', alignItems: 'center', gap: '6px' }}>
                      <span style={{ color: '#166534', fontSize: '0.7rem', fontWeight: '700', textTransform: 'uppercase' }}>Cuadrante</span>
                      {suggested.quadrant ? (
                        <span style={{ padding: '2px 8px', borderRadius: '12px', fontWeight: '800', fontSize: '0.85rem',
                          backgroundColor: suggested.quadrant === 'QI' ? '#fee2e2' : suggested.quadrant === 'QII' ? '#fef08a' : suggested.quadrant === 'QIII' ? '#dcfce7' : '#e0e7ff',
                          color: suggested.quadrant === 'QI' ? '#b91c1c' : suggested.quadrant === 'QII' ? '#a16207' : suggested.quadrant === 'QIII' ? '#15803d' : '#4338ca'
                        }}>
                          {suggested.quadrant}
                        </span>
                      ) : <span style={{ color: '#94a3b8', fontSize: '1rem', fontWeight: '800' }}>-</span>}
                    </div>
                  </div>

                  <div style={{ backgroundColor: '#f8fafc', padding: '16px', borderRadius: '16px', border: '1px solid #e2e8f0', display: 'flex', flexDirection: 'column', flex: 1 }}>
                    <div className="custom-scroll" style={{ display: 'flex', flexDirection: 'column', gap: '8px', overflowY: 'auto', flex: 1, minHeight: '150px', paddingRight: '8px' }}>
                      {riesgosPorArea.filter(g => g.riesgos.length > 0).map(g => (
                        <div key={g.area.id || g.area.id_unidad} style={{ marginBottom: '16px' }}>
                          <div style={{ fontSize: '0.9rem', fontWeight: 'bold', color: '#334155', paddingBottom: '6px', borderBottom: '1px dashed #cbd5e1', marginBottom: '10px' }}>
                            {g.area.nombre || g.area.denominacion}
                          </div>
                          <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                            {g.riesgos.map(r => (
                              <label key={r.id} className="rur-label" style={{ display: 'flex', alignItems: 'flex-start', gap: '10px', cursor: 'pointer', fontSize: '0.9rem', color: '#475569', margin: 0, backgroundColor: '#ffffff', border: '1px solid #e2e8f0', borderRadius: '10px', padding: '10px 12px', transition: 'all 0.2s', boxShadow: '0 1px 2px rgba(0,0,0,0.02)' }}>
                                <input 
                                  type="checkbox" 
                                  checked={selectedIds.includes(r.id)} 
                                  onChange={() => handleCheckbox(r.id)} 
                                  style={{ marginTop: '3px', accentColor: '#2563eb', width: '18px', height: '18px', cursor: 'pointer', flexShrink: 0 }}
                                />
                                <span style={{ lineHeight: '1.4' }}><b style={{ color: '#1e293b' }}>{r.local_id || r.id}</b>: {r.riesgo} <span style={{ color: '#94a3b8', display: 'inline-block', marginLeft: '6px', fontSize: '0.8rem' }}>(P:{r.probabilidad} I:{r.impacto})</span></span>
                              </label>
                            ))}
                          </div>
                        </div>
                      ))}
                      {riesgosPorArea.filter(g => g.riesgos.length > 0).length === 0 && (
                        <div style={{ textAlign: 'center', color: '#94a3b8', marginTop: '24px', fontSize: '0.95rem' }}>No hay riesgos ligados a este riesgo institucional.</div>
                      )}
                    </div>
                    
                    <div style={{ marginTop: '16px', paddingTop: '16px', borderTop: '1px solid #e2e8f0', fontSize: '0.9rem', color: '#64748b', display: 'flex', alignItems: 'center', gap: '10px' }}>
                      <div style={{ width: '10px', height: '10px', borderRadius: '50%', backgroundColor: selectedIds.length > 0 ? '#10b981' : '#cbd5e1' }}></div>
                      <b>{selectedIds.length}</b> riesgo(s) seleccionado(s) en total.
                    </div>
                  </div>
                </div>

                {/* Footer del Drawer */}
                <div style={{ marginTop: 'auto', display: 'flex', justifyContent: 'flex-end', gap: '12px', paddingTop: '20px', borderTop: '1px solid #e2e8f0' }}>
                  <button type="button" onClick={handleCancelClick} style={{ padding: '12px 24px', borderRadius: '10px', background: '#f8fafc', color: '#475569', fontWeight: '600', border: '1px solid #cbd5e1', cursor: 'pointer', transition: 'all 0.2s', fontSize: '0.95rem' }} onMouseOver={e => { e.currentTarget.style.background = '#f1f5f9'; e.currentTarget.style.borderColor = '#94a3b8'; }} onMouseOut={e => { e.currentTarget.style.background = '#f8fafc'; e.currentTarget.style.borderColor = '#cbd5e1'; }}>
                    Cancelar
                  </button>
                  <button type="submit" disabled={!newIR.risk || !newIR.risk.trim()} style={{ padding: '12px 32px', borderRadius: '10px', background: (!newIR.risk || !newIR.risk.trim()) ? '#94a3b8' : '#2563eb', color: '#ffffff', fontWeight: '600', border: 'none', cursor: (!newIR.risk || !newIR.risk.trim()) ? 'not-allowed' : 'pointer', transition: 'all 0.2s', boxShadow: (!newIR.risk || !newIR.risk.trim()) ? 'none' : '0 4px 6px -1px rgba(37, 99, 235, 0.4)', fontSize: '0.95rem' }} onMouseOver={e => { if (newIR.risk && newIR.risk.trim()) e.currentTarget.style.transform = 'translateY(-1px)'; }} onMouseOut={e => { e.currentTarget.style.transform = 'translateY(0)'; }}>
                    Guardar Cambios
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      )}

      {/* Confirm Dialog */}
      {confirmDialog.open && (
        <div style={{
          position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, zIndex: 10000,
          background: 'rgba(15, 23, 42, 0.4)', display: 'flex', alignItems: 'center', justifyContent: 'center', backdropFilter: 'blur(4px)', padding: '20px'
        }}>
          <div style={{
            background: '#ffffff', borderRadius: '20px', maxWidth: '450px', width: '100%',
            boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.25)', overflow: 'hidden', padding: '32px', textAlign: 'center',
            animation: 'modalSlideUp 0.2s cubic-bezier(0.16, 1, 0.3, 1)'
          }}>
            <div style={{ 
              width: '64px', height: '64px', borderRadius: '50%', margin: '0 auto 20px auto', display: 'flex', alignItems: 'center', justifyContent: 'center',
              backgroundColor: confirmDialog.type === 'delete' ? '#fee2e2' : confirmDialog.type === 'cancel' ? '#fef3c7' : '#d1fae5',
              color: confirmDialog.type === 'delete' ? '#ef4444' : confirmDialog.type === 'cancel' ? '#f59e0b' : '#10b981'
            }}>
              {confirmDialog.type === 'delete' ? (
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
              ) : confirmDialog.type === 'cancel' ? (
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
              ) : (
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
              )}
            </div>
            <h3 style={{ margin: '0 0 12px 0', fontSize: '1.4rem', color: '#0f172a', fontWeight: '800' }}>{confirmDialog.title}</h3>
            <p style={{ margin: '0 0 32px 0', fontSize: '1rem', color: '#64748b', lineHeight: '1.5' }}>{confirmDialog.message}</p>
            
            <div style={{ display: 'flex', gap: '16px', justifyContent: 'center' }}>
              <button onClick={() => setConfirmDialog(p => ({ ...p, open: false }))} style={{ flex: 1, padding: '12px', borderRadius: '12px', background: '#f8fafc', color: '#475569', fontWeight: '600', border: '1px solid #cbd5e1', cursor: 'pointer', transition: 'all 0.2s', fontSize: '1rem' }} onMouseOver={e => { e.currentTarget.style.background = '#f1f5f9'; e.currentTarget.style.borderColor = '#94a3b8'; }} onMouseOut={e => { e.currentTarget.style.background = '#f8fafc'; e.currentTarget.style.borderColor = '#cbd5e1'; }}>
                {confirmDialog.type === 'cancel' ? 'No, continuar editando' : 'Cancelar'}
              </button>
              <button onClick={confirmDialog.action} style={{ flex: 1, padding: '12px', borderRadius: '12px', background: confirmDialog.type === 'delete' ? '#ef4444' : confirmDialog.type === 'cancel' ? '#f59e0b' : '#10b981', color: '#ffffff', fontWeight: '600', border: 'none', cursor: 'pointer', transition: 'all 0.2s', boxShadow: confirmDialog.type === 'delete' ? '0 10px 15px -3px rgba(239, 68, 68, 0.4)' : confirmDialog.type === 'cancel' ? '0 10px 15px -3px rgba(245, 158, 11, 0.4)' : '0 10px 15px -3px rgba(16, 185, 129, 0.4)', fontSize: '1rem' }} onMouseOver={e => { e.currentTarget.style.transform = 'translateY(-2px)'; }} onMouseOut={e => { e.currentTarget.style.transform = 'translateY(0)'; }}>
                {confirmDialog.type === 'delete' ? 'Sí, eliminar' : confirmDialog.type === 'cancel' ? 'Sí, salir' : 'Sí, validar'}
              </button>
            </div>
          </div>
        </div>
      )}

      {fuentesModalData && (
        <div style={{
          position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, zIndex: 100000,
          background: 'rgba(15, 23, 42, 0.4)', display: 'flex', alignItems: 'center', justifyContent: 'center', backdropFilter: 'blur(4px)', padding: '20px'
        }}>
          <div style={{
            background: '#ffffff', borderRadius: '20px', maxWidth: '600px', width: '100%', maxHeight: '80vh',
            boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.25)', overflow: 'hidden', display: 'flex', flexDirection: 'column',
            animation: 'modalSlideUp 0.2s cubic-bezier(0.16, 1, 0.3, 1)'
          }}>
            <div style={{ padding: '20px 24px', borderBottom: '1px solid #e2e8f0', background: 'linear-gradient(to right, #f8fafc, #f1f5f9)', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <h3 style={{ margin: 0, fontSize: '1.25rem', color: '#0f172a', fontWeight: '700' }}>
                Riesgos de {fuentesModalData.areaName}
              </h3>
              <button onClick={() => setFuentesModalData(null)} style={{ background: 'none', border: 'none', cursor: 'pointer', fontSize: '1.5rem', color: '#64748b', lineHeight: 1 }}>&times;</button>
            </div>
            
            <div style={{ padding: '24px', overflowY: 'auto' }}>
              <div style={{ marginBottom: '16px', fontSize: '0.9rem', color: '#64748b' }}>
                Riesgos fuente asociados al riesgo institucional <strong>{fuentesModalData.riFolio}</strong>.
              </div>
              <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                {fuentesModalData.fuentes.map(r => {
                  const idStr = r.local_id ? (String(r.local_id).startsWith('R') ? r.local_id : `R${r.local_id}`) : `R${r.id}`;
                  return (
                    <div key={r.id} style={{ padding: '16px', background: '#f8fafc', borderRadius: '12px', border: '1px solid #e2e8f0' }}>
                      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '8px' }}>
                        <strong style={{ fontSize: '1rem', color: '#1e293b' }}>{idStr}</strong>
                        <span style={{ fontSize: '0.8rem', padding: '2px 8px', background: '#e0e7ff', color: '#3730a3', borderRadius: '12px', fontWeight: '600' }}>
                          P:{r.probabilidad} / I:{r.impacto}
                        </span>
                      </div>
                      <div style={{ fontSize: '0.95rem', color: '#334155', lineHeight: '1.5' }}>
                        {r.riesgo}
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>
            
            <div style={{ padding: '16px 24px', borderTop: '1px solid #e2e8f0', display: 'flex', justifyContent: 'flex-end', background: '#f8fafc' }}>
              <button onClick={() => setFuentesModalData(null)} style={{ padding: '10px 20px', borderRadius: '8px', background: '#2563eb', color: '#ffffff', fontWeight: '600', border: 'none', cursor: 'pointer', fontSize: '0.95rem' }}>
                Cerrar
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Modal Vista Preliminar (sin columna Acciones) */}
      {previewModalOpen && (
        <div
          className="modal-overlay preview-modal-overlay"
          style={{
            position: 'fixed',
            inset: 0,
            background: 'rgba(15, 23, 42, 0.6)',
            backdropFilter: 'blur(4px)',
            zIndex: 999999,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            padding: '24px',
            overflowY: 'auto',
          }}
          onClick={(e) => {
            if (e.target === e.currentTarget) setPreviewModalOpen(false);
          }}
        >
          <div
            className="preview-modal-box"
            style={{
              background: '#ffffff',
              borderRadius: '16px',
              maxWidth: '1100px',
              width: '100%',
              maxHeight: '90vh',
              display: 'flex',
              flexDirection: 'column',
              boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.25)',
              overflow: 'hidden',
            }}
          >
            {/* Header del modal */}
            <div
              className="no-print"
              style={{
                padding: '16px 24px',
                borderBottom: '1px solid #e2e8f0',
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                background: '#f8fafc',
              }}
            >
              <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                <Iconify icon="ph:eye" sx={{ width: 22, height: 22, color: '#1e293b' }} />
                <h3 style={{ margin: 0, fontSize: '1.15rem', fontWeight: 600, color: '#0f172a' }}>
                  Vista preliminar
                </h3>
              </div>
              <div style={{ display: 'flex', gap: '10px', alignItems: 'center' }}>
                <button
                  type="button"
                  onClick={() => window.print()}
                  style={{
                    display: 'inline-flex',
                    alignItems: 'center',
                    gap: '6px',
                    padding: '8px 16px',
                    borderRadius: '8px',
                    background: '#2563eb',
                    color: '#ffffff',
                    border: 'none',
                    fontWeight: 600,
                    cursor: 'pointer',
                    fontSize: '0.9rem',
                  }}
                  title="Imprimir"
                >
                  <Iconify icon="ph:printer" sx={{ width: 18, height: 18 }} />
                  <span>Imprimir</span>
                </button>
                <button
                  type="button"
                  onClick={() => setPreviewModalOpen(false)}
                  style={{
                    display: 'inline-flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    width: '36px',
                    height: '36px',
                    borderRadius: '8px',
                    background: '#ffffff',
                    border: '1px solid #cbd5e1',
                    color: '#64748b',
                    cursor: 'pointer',
                  }}
                  title="Cerrar"
                >
                  <Iconify icon="ph:x" sx={{ width: 20, height: 20 }} />
                </button>
              </div>
            </div>

            {/* Hoja imprimible */}
            <div
              id="vista-preliminar-mar-print"
              style={{
                padding: '30px 1cm',
                overflowY: 'auto',
                flex: 1,
                background: '#ffffff',
              }}
            >
              <style>{`
                @media print {
                  /* 1. Ocultar todos los elementos de la interfaz excepto la vista preliminar */
                  .topbar, .sidebar, .page-head, .notice, section.panel, .drawer, .side-footer, nav, aside, header, .no-print {
                    display: none !important;
                  }

                  /* 2. Resetear body y html para paginación normal sin scroll ni overflow */
                  html, body {
                    margin: 0 !important;
                    padding: 0 !important;
                    background: #ffffff !important;
                    height: auto !important;
                    min-height: auto !important;
                    overflow: visible !important;
                  }

                  /* 3. Modal overlay a flujo estático en impresión */
                  .preview-modal-overlay {
                    position: static !important;
                    display: block !important;
                    background: transparent !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    overflow: visible !important;
                    width: 100% !important;
                    height: auto !important;
                    inset: auto !important;
                    z-index: auto !important;
                    backdrop-filter: none !important;
                  }

                  /* 4. Caja del modal sin sombras, bordes ni límites de altura */
                  .preview-modal-box {
                    position: static !important;
                    display: block !important;
                    box-shadow: none !important;
                    border: none !important;
                    border-radius: 0 !important;
                    max-width: 100% !important;
                    width: 100% !important;
                    height: auto !important;
                    max-height: none !important;
                    overflow: visible !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    background: #ffffff !important;
                  }

                  /* 5. Contenedor de impresión */
                  #vista-preliminar-mar-print {
                    position: static !important;
                    display: block !important;
                    width: 100% !important;
                    height: auto !important;
                    max-height: none !important;
                    overflow: visible !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    background: #ffffff !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                  }

                  /* 6. Tabla continua con encabezados repetidos en cada página */
                  .print-table {
                    width: 100% !important;
                    border-collapse: collapse !important;
                    display: table !important;
                  }

                  .print-table thead {
                    display: table-header-group !important;
                  }

                  .print-table tbody {
                    display: table-row-group !important;
                  }

                  /* 7. Evitar que un renglón se corte: se pasa completo a la siguiente página */
                  .print-table tr {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                    page-break-after: auto !important;
                    break-after: auto !important;
                  }

                  .print-table td, .print-table th {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                  }

                  .no-print {
                    display: none !important;
                  }

                  @page {
                    size: landscape;
                    margin: 1cm;
                  }

                  .print-table thead tr:last-child th {
                    background-color: #0b3a63 !important;
                    color: #ffffff !important;
                    font-weight: bold !important;
                    text-align: center !important;
                    vertical-align: middle !important;
                  }
                }
              `}</style>

              {/* Tabla continua: encabezado del reporte y de la tabla en thead (se repite en cada página) */}
              <div style={{ overflowX: 'auto' }}>
                <table className="print-table" style={{ width: '100%', borderCollapse: 'collapse', fontSize: '0.9rem' }}>
                  <thead>
                    {/* Fila 1: Encabezado del reporte (se repite en todas las hojas automáticamente) */}
                    <tr>
                      <th
                        colSpan="5"
                        style={{
                          padding: '0 0 14px 0',
                          border: 'none',
                          background: '#ffffff',
                          fontWeight: 'normal',
                        }}
                      >
                        <div
                          className="report-header-print"
                          style={{
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'space-between',
                            borderBottom: '3px solid #0b3a63',
                            paddingBottom: '12px',
                          }}
                        >
                          <div style={{ display: 'flex', alignItems: 'center', gap: '18px' }}>
                            <img src={logoTecdmx} alt="TECDMX" style={{ width: '135px', height: 'auto', objectFit: 'contain' }} />
                            <div>
                              <h2 style={{ margin: '0 0 3px 0', fontSize: '1.05rem', color: '#0b3a63', fontWeight: 'bold', textTransform: 'uppercase', letterSpacing: '0.5px' }}>
                                Tribunal Electoral de la Ciudad de México
                              </h2>
                              <h3 style={{ margin: '0 0 3px 0', fontSize: '1rem', color: '#1e293b', fontWeight: 700 }}>
                                MAR Institucional — Ejercicio {ejercicio}
                              </h3>
                              <p style={{ margin: 0, fontSize: '0.825rem', color: '#64748b' }}>
                                Trazabilidad de MAR Institucional con Riesgos fuente por UR
                              </p>
                            </div>
                          </div>
                          <div style={{ fontSize: '0.8rem', color: '#64748b', textAlign: 'right', minWidth: '130px' }}>
                            Fecha de emisión:<br />
                            <strong style={{ color: '#1e293b' }}>{new Date().toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' })}</strong>
                          </div>
                        </div>
                      </th>
                    </tr>

                    {/* Fila 2: Encabezado de la tabla (se repite en todas las hojas automáticamente) */}
                    <tr style={{ background: '#0b3a63', borderBottom: '2px solid #092e55' }}>
                      <th style={{ padding: '10px 12px', textAlign: 'center', verticalAlign: 'middle', width: '10%', color: '#ffffff', fontWeight: 'bold' }}>ID</th>
                      <th style={{ padding: '10px 12px', textAlign: 'center', verticalAlign: 'middle', width: '42%', color: '#ffffff', fontWeight: 'bold' }}>Riesgo institucional</th>
                      <th style={{ padding: '10px 12px', textAlign: 'center', verticalAlign: 'middle', width: '30%', color: '#ffffff', fontWeight: 'bold' }}>Fuentes</th>
                      <th style={{ padding: '10px 12px', textAlign: 'center', verticalAlign: 'middle', width: '10%', color: '#ffffff', fontWeight: 'bold' }}>P/I institucional</th>
                      <th style={{ padding: '10px 12px', textAlign: 'center', verticalAlign: 'middle', width: '8%', color: '#ffffff', fontWeight: 'bold' }}>Cuadrante</th>
                    </tr>
                  </thead>

                  <tbody>
                    {riesgosInstitucionales.length > 0 ? (
                      riesgosInstitucionales.map((r) => (
                        <tr
                          key={r.id}
                          style={{
                            borderBottom: '1px solid #e2e8f0',
                            pageBreakInside: 'avoid',
                            breakInside: 'avoid',
                          }}
                        >
                          <td style={{ padding: '12px', fontWeight: 'bold', verticalAlign: 'top', whiteSpace: 'nowrap' }}>
                            {r.folio}
                          </td>
                          <td style={{ padding: '12px', verticalAlign: 'top', textAlign: 'justify', lineHeight: '1.5' }}>
                            {r.riesgo}
                          </td>
                          <td style={{ padding: '12px', verticalAlign: 'top', fontSize: '0.85rem', textAlign: 'justify', lineHeight: '1.5' }}>
                            {Object.entries(
                              (r.fuentes || []).reduce((acc, f) => {
                                if (!acc[f.area_id]) acc[f.area_id] = [];
                                acc[f.area_id].push(f);
                                return acc;
                              }, {})
                            ).map(([aId, fs], idx, arr) => {
                              const idsStr = fs.map((f) =>
                                f.local_id
                                  ? String(f.local_id).startsWith('R')
                                    ? f.local_id
                                    : `R${f.local_id}`
                                  : `R${f.id}`
                              );
                              let formattedIds = idsStr[0] || '';
                              if (idsStr.length > 1) {
                                const last = idsStr.pop();
                                formattedIds = idsStr.join(', ') + ' y ' + last;
                              }
                              return (
                                <span key={aId}>
                                  <strong>{areaName(aId)}:</strong> {formattedIds}
                                  {idx < arr.length - 1 && <span style={{ margin: '0 6px', color: '#cbd5e1' }}>|</span>}
                                </span>
                              );
                            })}
                          </td>
                          <td style={{ padding: '12px', textAlign: 'center', verticalAlign: 'top', whiteSpace: 'nowrap' }}>
                            {r.probabilidad} / {r.impacto}
                          </td>
                          <td style={{ padding: '12px', textAlign: 'center', verticalAlign: 'top' }}>
                            <span className={`badge ${cuadrante(r.probabilidad, r.impacto).toLowerCase()}`}>
                              {cuadrante(r.probabilidad, r.impacto)}
                            </span>
                          </td>
                        </tr>
                      ))
                    ) : (
                      <tr>
                        <td colSpan="5" style={{ textAlign: 'center', padding: '40px', color: '#64748b' }}>
                          Aún no se han creado riesgos institucionales.
                        </td>
                      </tr>
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      )}
    </>
  );
}
