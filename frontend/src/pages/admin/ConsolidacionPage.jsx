import { useState, useEffect, useMemo, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';
import useAuth from '../../hooks/useAuth';

const cuadrante = (p, i) => {
  p = Number(p); i = Number(i);
  if (p > 5 && i > 5) return 'QI';
  if (p > 5 && i <= 5) return 'QII';
  if (p <= 5 && i <= 5) return 'QIII';
  return 'QIV';
};

export default function ConsolidacionPage() {
  const { hasRole } = useAuth();
  const [riesgosBase, setRiesgosBase] = useState([]);
  const [riesgosInstitucionales, setRiesgosInstitucionales] = useState([]);
  const [areas, setAreas] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedArea, setSelectedArea] = useState('Todas');
  const [selectedIds, setSelectedIds] = useState([]);
  const [savingMapping, setSavingMapping] = useState({});

  // Drawer modal para "Generar riesgos institucionales"
  const [generatorOpen, setGeneratorOpen] = useState(false);
  const [newIR, setNewIR] = useState({ objective: '', risk: '', probability: 0, impact: 0 });
  const [suggested, setSuggested] = useState({ probability: 0, impact: 0, quadrant: '' });
  const [generating, setGenerating] = useState(false);
  const [notification, setNotification] = useState(null);

  const ejercicio = useGlobalStore((s) => s.ejercicio);
  const navigate = useNavigate();

  const showNotification = useCallback((type, text) => {
    setNotification({ type, text });
    setTimeout(() => setNotification(null), 4000);
  }, []);

  const fetchData = useCallback(async () => {
    setLoading(true);
    try {
      const [resBase, resInst, resAreas] = await Promise.all([
        axios.get(`/riesgos?ejercicio_id=${ejercicio}`),
        axios.get(`/riesgos-institucionales?ejercicio_id=${ejercicio}`),
        axios.get('/unidades-responsables')
      ]);

      const allRiesgos = resBase.data.data || resBase.data || [];
      const instList = resInst.data.data || resInst.data || [];
      const areaList = resAreas.data.data || resAreas.data || [];

      // Mapear cada riesgo para identificar qué RI tiene asociado
      const mapping = {};
      instList.forEach(ri => {
        (ri.fuentes || []).forEach(f => {
          mapping[f.id] = ri.id;
        });
      });

      const riesgosConRI = allRiesgos
        .filter(r => r.status === 'Validado' || r.estatus === 'Validado')
        .map(r => {
          const directRI = r.riesgos_institucionales?.[0]?.id;
          const mappedRI = mapping[r.id];
          return {
            ...r,
            assigned_ri_id: directRI || mappedRI || ''
          };
        });

      setRiesgosBase(riesgosConRI);
      setRiesgosInstitucionales(instList);
      setAreas(areaList);
    } catch (e) {
      console.error(e);
      showNotification('error', 'Error al cargar los riesgos consolidados.');
    } finally {
      setLoading(false);
    }
  }, [ejercicio, showNotification]);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  const getAreaName = (id) => {
    const a = areas.find(x => String(x.unidad_responsable_gasto_id || x.id_unidad || x.id) === String(id));
    return a ? (a.nombre || a.denominacion) : `Área ${id}`;
  };

  // Filtrado por área
  const filteredRiesgos = useMemo(() => {
    if (selectedArea === 'Todas') {
      return riesgosBase;
    }
    return riesgosBase.filter(r => String(r.area_id) === String(selectedArea));
  }, [riesgosBase, selectedArea]);

  // Selección individual
  const handleToggle = (id) => {
    setSelectedIds(prev => prev.includes(id) ? prev.filter(x => x !== id) : [...prev, id]);
  };

  // Seleccionar / deseleccionar todos los visibles
  const isAllSelected = filteredRiesgos.length > 0 && filteredRiesgos.every(r => selectedIds.includes(r.id));
  const handleSelectAll = () => {
    if (isAllSelected) {
      const filteredSet = new Set(filteredRiesgos.map(r => r.id));
      setSelectedIds(prev => prev.filter(id => !filteredSet.has(id)));
    } else {
      const currentSelected = new Set(selectedIds);
      filteredRiesgos.forEach(r => currentSelected.add(r.id));
      setSelectedIds(Array.from(currentSelected));
    }
  };

  // Asignar o desvincular un RI desde el dropdown de la columna RI
  const handleAssignRI = async (riesgoId, riId) => {
    setSavingMapping(prev => ({ ...prev, [riesgoId]: true }));
    try {
      await axios.post('/riesgos-institucionales/assign-fuente', {
        riesgo_id: riesgoId,
        riesgo_institucional_id: riId ? Number(riId) : null
      });

      setRiesgosBase(prev => prev.map(r => r.id === riesgoId ? { ...r, assigned_ri_id: riId ? Number(riId) : '' } : r));
      showNotification('success', 'Vinculación de RI actualizada.');
    } catch (e) {
      console.error(e);
      showNotification('error', 'Error al actualizar la vinculación del riesgo institucional.');
    } finally {
      setSavingMapping(prev => ({ ...prev, [riesgoId]: false }));
    }
  };

  // Abrir generador de Riesgo Institucional a partir de los seleccionados
  const handleOpenGenerator = () => {
    if (selectedIds.length === 0) return;

    const selectedRisks = riesgosBase.filter(r => selectedIds.includes(r.id));
    const avgP = Math.round(selectedRisks.reduce((acc, r) => acc + (r.probabilidad || 0), 0) / selectedRisks.length);
    const avgI = Math.round(selectedRisks.reduce((acc, r) => acc + (r.impacto || 0), 0) / selectedRisks.length);
    const quad = cuadrante(avgP, avgI);

    setSuggested({ probability: avgP, impact: avgI, quadrant: quad });
    setNewIR({
      objective: '',
      risk: selectedRisks.length === 1 ? selectedRisks[0].riesgo : '',
      probability: avgP,
      impact: avgI
    });
    setGeneratorOpen(true);
  };

  const handleCreateInstitutionalRisk = async (e) => {
    e.preventDefault();
    if (!newIR.risk.trim()) {
      alert('Por favor ingrese la redacción del riesgo institucional.');
      return;
    }

    setGenerating(true);
    try {
      const payload = {
        ejercicio_id: ejercicio,
        objetivo: newIR.objective,
        riesgo: newIR.risk,
        probabilidad_sugerida: suggested.probability,
        impacto_sugerido: suggested.impact,
        probabilidad: newIR.probability,
        impacto: newIR.impact,
        sourceRiskIds: selectedIds
      };

      const res = await axios.post('/riesgos-institucionales', payload);
      const createdRI = res.data.data;

      // Actualizar los riesgos seleccionados con el nuevo RI
      if (createdRI && createdRI.id) {
        setRiesgosBase(prev => prev.map(r => selectedIds.includes(r.id) ? { ...r, assigned_ri_id: createdRI.id } : r));
      }

      setGeneratorOpen(false);
      setSelectedIds([]);
      showNotification('success', `Riesgo Institucional ${createdRI.folio || ''} generado exitosamente.`);
      fetchData();
    } catch (err) {
      console.error(err);
      showNotification('error', 'Ocurrió un error al generar el riesgo institucional.');
    } finally {
      setGenerating(false);
    }
  };

  return (
    <div style={{ width: '100%', margin: '0 auto', paddingBottom: '40px' }}>
      {/* Toast Notification */}
      {notification && (
        <div style={{
          position: 'fixed',
          top: '20px',
          right: '20px',
          zIndex: 999999,
          padding: '12px 20px',
          borderRadius: '10px',
          background: notification.type === 'error' ? '#ef4444' : '#10b981',
          color: '#fff',
          fontWeight: '600',
          boxShadow: '0 10px 25px rgba(0,0,0,0.18)',
          display: 'flex',
          alignItems: 'center',
          gap: '10px'
        }}>
          <span>{notification.text}</span>
        </div>
      )}

      {/* Header */}
      <div style={{ marginBottom: '24px' }}>
        <h1 style={{ margin: '0 0 6px 0', fontSize: '1.65rem', fontWeight: '800', color: '#17324d' }}>
          Consolidación de riesgos
        </h1>
        <p style={{ margin: 0, fontSize: '0.95rem', color: '#64748b' }}>
          Riesgos de UR relacionados a los riesgos institucionales.
        </p>
      </div>

      {/* Toolbar / Filtros */}
      <div style={{
        display: 'flex',
        justifyContent: 'flex-end',
        alignItems: 'center',
        gap: '20px',
        marginBottom: '18px',
        flexWrap: 'wrap'
      }}>
        {/* Selector de Área */}
        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
          <label style={{ fontSize: '0.9rem', fontWeight: '600', color: '#334155' }}>Área</label>
          <select
            value={selectedArea}
            onChange={(e) => setSelectedArea(e.target.value)}
            style={{
              padding: '8px 14px',
              borderRadius: '8px',
              border: '1px solid #cbd5e1',
              backgroundColor: '#fff',
              fontSize: '0.9rem',
              color: '#1e293b',
              minWidth: '220px',
              cursor: 'pointer',
              outline: 'none'
            }}
          >
            <option value="Todas">Todas</option>
            {areas.map(a => {
              const aId = String(a.unidad_responsable_gasto_id || a.id_unidad || a.id);
              return (
                <option key={aId} value={aId}>
                  {a.nombre || a.denominacion}
                </option>
              );
            })}
          </select>
        </div>

        {/* Checkbox Todos */}
        <label style={{
          display: 'flex',
          alignItems: 'center',
          gap: '8px',
          cursor: 'pointer',
          fontSize: '0.9rem',
          color: '#334155',
          userSelect: 'none'
        }}>
          <input
            type="checkbox"
            checked={isAllSelected}
            onChange={handleSelectAll}
            style={{ width: '17px', height: '17px', cursor: 'pointer', accentColor: '#1F4E79' }}
          />
          <span>Todos ({filteredRiesgos.length})</span>
        </label>

      </div>

      {/* Tabla de Riesgos Consolidados */}
      <div style={{
        background: '#fff',
        borderRadius: '10px',
        border: '1px solid #e2e8f0',
        overflow: 'hidden',
        boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.05)'
      }}>
        <div style={{ overflowX: 'auto' }}>
          <table style={{ width: '100%', borderCollapse: 'collapse', textAlign: 'left', fontSize: '0.9rem' }}>
            <thead>
              <tr style={{ backgroundColor: '#17324d', color: '#ffffff' }}>
                <th style={{ width: '4%', padding: '12px 14px', textAlign: 'center' }}>
                  <input
                    type="checkbox"
                    checked={isAllSelected}
                    onChange={handleSelectAll}
                    style={{ width: '16px', height: '16px', cursor: 'pointer', accentColor: '#38bdf8' }}
                  />
                </th>
                <th style={{ width: '20%', padding: '12px 14px', fontWeight: '700' }}>Área</th>
                <th style={{ width: '10%', padding: '12px 14px', fontWeight: '700' }}>ID</th>
                <th style={{ width: '30%', padding: '12px 14px', fontWeight: '700' }}>Riesgo validado</th>
                <th style={{ width: '10%', padding: '12px 14px', fontWeight: '700', textAlign: 'center' }}>P/I</th>
                <th style={{ width: '10%', padding: '12px 14px', fontWeight: '700', textAlign: 'center' }}>Cuadrante</th>
                <th style={{ width: '30%', padding: '12px 14px', fontWeight: '700' }}>RI</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr>
                  <td colSpan="7" style={{ textAlign: 'center', padding: '36px', color: '#64748b' }}>
                    Cargando base consolidada...
                  </td>
                </tr>
              ) : filteredRiesgos.length === 0 ? (
                <tr>
                  <td colSpan="7" style={{ textAlign: 'center', padding: '36px', color: '#64748b' }}>
                    No se encontraron riesgos validados para el área seleccionada.
                  </td>
                </tr>
              ) : (
                filteredRiesgos.map((r, index) => {
                  const isChecked = selectedIds.includes(r.id);
                  const quad = cuadrante(r.probabilidad, r.impacto);
                  return (
                    <tr
                      key={r.id}
                      style={{
                        backgroundColor: isChecked ? '#f0f9ff' : index % 2 === 0 ? '#ffffff' : '#f8fafc',
                        borderBottom: '1px solid #e2e8f0',
                        transition: 'background-color 0.15s'
                      }}
                    >
                      <td style={{ padding: '12px 14px', textAlign: 'center' }}>
                        <input
                          type="checkbox"
                          checked={isChecked}
                          onChange={() => handleToggle(r.id)}
                          style={{ width: '16px', height: '16px', cursor: 'pointer', accentColor: '#1F4E79' }}
                        />
                      </td>
                      <td style={{ padding: '12px 14px', color: '#334155', fontWeight: '500' }}>
                        {getAreaName(r.area_id)}
                      </td>
                      <td style={{ padding: '12px 14px', color: '#1e293b', fontWeight: '600' }}>
                        {r.local_id ? `R${r.local_id}` : `R${r.id}`}
                      </td>
                      <td style={{ padding: '12px 14px', color: '#1e293b', lineHeight: '1.45' }}>
                        {r.riesgo}
                      </td>
                      <td style={{ padding: '12px 14px', textAlign: 'center', color: '#475569', fontWeight: '600' }}>
                        {r.probabilidad}/{r.impacto}
                      </td>
                      <td style={{ padding: '12px 14px', textAlign: 'center' }}>
                        <span style={{
                          padding: '4px 10px',
                          borderRadius: '12px',
                          fontSize: '0.8rem',
                          fontWeight: '700',
                          backgroundColor: quad === 'QI' ? '#fee2e2' : quad === 'QII' ? '#fef08a' : quad === 'QIII' ? '#dcfce7' : '#e0e7ff',
                          color: quad === 'QI' ? '#b91c1c' : quad === 'QII' ? '#a16207' : quad === 'QIII' ? '#15803d' : '#3730a3'
                        }}>
                          {quad}
                        </span>
                      </td>
                      <td style={{ padding: '10px 14px' }}>
                        <select
                          value={r.assigned_ri_id || ''}
                          disabled={savingMapping[r.id]}
                          onChange={(e) => handleAssignRI(r.id, e.target.value)}
                          style={{
                            width: '100%',
                            padding: '6px 10px',
                            borderRadius: '6px',
                            border: '1px solid #cbd5e1',
                            fontSize: '0.85rem',
                            backgroundColor: r.assigned_ri_id ? '#f0fdf4' : '#fff',
                            color: r.assigned_ri_id ? '#166534' : '#64748b',
                            fontWeight: r.assigned_ri_id ? '600' : 'normal',
                            cursor: 'pointer',
                            outline: 'none'
                          }}
                        >
                          <option value="">-- Sin asignar --</option>
                          {riesgosInstitucionales.map(ri => (
                            <option key={ri.id} value={ri.id}>
                              {ri.folio || `RI-${ri.id}`} {ri.riesgo}
                            </option>
                          ))}
                        </select>
                      </td>
                    </tr>
                  );
                })
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal / Drawer para Generar Riesgo Institucional */}
      {generatorOpen && (
        <div style={{
          position: 'fixed',
          inset: 0,
          backgroundColor: 'rgba(15, 23, 42, 0.65)',
          zIndex: 99999,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          backdropFilter: 'blur(6px)',
          padding: '20px'
        }}>
          <div style={{
            backgroundColor: '#ffffff',
            borderRadius: '20px',
            maxWidth: '840px',
            width: '100%',
            maxHeight: '90vh',
            display: 'flex',
            flexDirection: 'column',
            boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.25)',
            overflow: 'hidden'
          }}>
            {/* Header del Modal */}
            <div style={{
              padding: '18px 24px',
              borderBottom: '1px solid #e2e8f0',
              display: 'flex',
              justifyContent: 'space-between',
              alignItems: 'center',
              backgroundColor: '#f8fafc'
            }}>
              <div>
                <h3 style={{ margin: 0, fontSize: '1.25rem', fontWeight: '800', color: '#17324d' }}>
                  Generar Riesgo Institucional
                </h3>
                <p style={{ margin: '3px 0 0 0', fontSize: '0.85rem', color: '#64748b' }}>
                  Consolidando {selectedIds.length} riesgo(s) fuente seleccionados
                </p>
              </div>
              <button
                onClick={() => setGeneratorOpen(false)}
                style={{
                  background: '#f1f5f9',
                  border: 'none',
                  width: '32px',
                  height: '32px',
                  borderRadius: '50%',
                  fontSize: '1.2rem',
                  cursor: 'pointer',
                  color: '#64748b',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center'
                }}
              >
                &times;
              </button>
            </div>

            {/* Contenido del Modal */}
            <form onSubmit={handleCreateInstitutionalRisk} style={{ display: 'flex', flexDirection: 'column', overflowY: 'auto' }}>
              <div style={{ padding: '24px', display: 'flex', flexDirection: 'column', gap: '20px' }}>
                {/* Métricas sugeridas */}
                <div style={{
                  display: 'flex',
                  gap: '16px',
                  backgroundColor: '#f0fdf4',
                  padding: '16px 20px',
                  borderRadius: '12px',
                  border: '1px solid #bbf7d0',
                  alignItems: 'center'
                }}>
                  <div style={{ flex: 1, borderRight: '1px dashed #86efac', textAlign: 'center' }}>
                    <div style={{ color: '#166534', fontSize: '0.75rem', fontWeight: '700', textTransform: 'uppercase' }}>Probabilidad Sugerida</div>
                    <div style={{ fontSize: '1.8rem', fontWeight: '800', color: '#14532d' }}>{suggested.probability}</div>
                  </div>
                  <div style={{ flex: 1, borderRight: '1px dashed #86efac', textAlign: 'center' }}>
                    <div style={{ color: '#166534', fontSize: '0.75rem', fontWeight: '700', textTransform: 'uppercase' }}>Impacto Sugerido</div>
                    <div style={{ fontSize: '1.8rem', fontWeight: '800', color: '#14532d' }}>{suggested.impact}</div>
                  </div>
                  <div style={{ flex: 1, textAlign: 'center' }}>
                    <div style={{ color: '#166534', fontSize: '0.75rem', fontWeight: '700', textTransform: 'uppercase', marginBottom: '4px' }}>Cuadrante Resultante</div>
                    <span style={{
                      display: 'inline-block',
                      padding: '4px 14px',
                      borderRadius: '20px',
                      fontWeight: '800',
                      fontSize: '1rem',
                      backgroundColor: suggested.quadrant === 'QI' ? '#fee2e2' : suggested.quadrant === 'QII' ? '#fef08a' : suggested.quadrant === 'QIII' ? '#dcfce7' : '#e0e7ff',
                      color: suggested.quadrant === 'QI' ? '#b91c1c' : suggested.quadrant === 'QII' ? '#a16207' : suggested.quadrant === 'QIII' ? '#15803d' : '#3730a3'
                    }}>
                      {suggested.quadrant}
                    </span>
                  </div>
                </div>

                {/* Formulario */}
                <div>
                  <label style={{ display: 'block', marginBottom: '6px', fontWeight: '700', color: '#334155', fontSize: '0.9rem' }}>
                    Objetivo Institucional (Opcional):
                  </label>
                  <input
                    type="text"
                    value={newIR.objective}
                    onChange={(e) => setNewIR(p => ({ ...p, objective: e.target.value }))}
                    placeholder="Objetivo estratégico asociado..."
                    style={{
                      width: '100%',
                      padding: '10px 14px',
                      borderRadius: '8px',
                      border: '1px solid #cbd5e1',
                      fontSize: '0.95rem',
                      outline: 'none'
                    }}
                  />
                </div>

                <div>
                  <label style={{ display: 'block', marginBottom: '6px', fontWeight: '700', color: '#334155', fontSize: '0.9rem' }}>
                    Redacción del Riesgo Institucional <span style={{ color: '#ef4444' }}>*</span>:
                  </label>
                  <textarea
                    rows={4}
                    required
                    value={newIR.risk}
                    onChange={(e) => setNewIR(p => ({ ...p, risk: e.target.value }))}
                    placeholder="Redacte el riesgo a nivel institucional que consolida las áreas seleccionadas..."
                    style={{
                      width: '100%',
                      padding: '12px 14px',
                      borderRadius: '8px',
                      border: '1px solid #cbd5e1',
                      fontSize: '0.95rem',
                      fontFamily: 'inherit',
                      outline: 'none',
                      resize: 'vertical'
                    }}
                  />
                </div>

                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                  <div>
                    <label style={{ display: 'block', marginBottom: '6px', fontWeight: '700', color: '#334155', fontSize: '0.9rem' }}>
                      Probabilidad final (1-10):
                    </label>
                    <input
                      type="number"
                      min="1"
                      max="10"
                      value={newIR.probability}
                      onChange={(e) => setNewIR(p => ({ ...p, probability: Number(e.target.value) }))}
                      style={{
                        width: '100%',
                        padding: '10px 14px',
                        borderRadius: '8px',
                        border: '1px solid #cbd5e1',
                        fontSize: '0.95rem',
                        outline: 'none'
                      }}
                    />
                  </div>
                  <div>
                    <label style={{ display: 'block', marginBottom: '6px', fontWeight: '700', color: '#334155', fontSize: '0.9rem' }}>
                      Impacto final (1-10):
                    </label>
                    <input
                      type="number"
                      min="1"
                      max="10"
                      value={newIR.impact}
                      onChange={(e) => setNewIR(p => ({ ...p, impact: Number(e.target.value) }))}
                      style={{
                        width: '100%',
                        padding: '10px 14px',
                        borderRadius: '8px',
                        border: '1px solid #cbd5e1',
                        fontSize: '0.95rem',
                        outline: 'none'
                      }}
                    />
                  </div>
                </div>

                {/* Lista rápida de riesgos vinculados */}
                <div style={{ backgroundColor: '#f8fafc', padding: '14px', borderRadius: '10px', border: '1px solid #e2e8f0' }}>
                  <div style={{ fontSize: '0.85rem', fontWeight: '700', color: '#475569', marginBottom: '8px' }}>
                    Riesgos que quedarán vinculados a este RI ({selectedIds.length}):
                  </div>
                  <div style={{ display: 'flex', flexDirection: 'column', gap: '6px', maxHeight: '140px', overflowY: 'auto' }}>
                    {riesgosBase.filter(r => selectedIds.includes(r.id)).map(r => (
                      <div key={r.id} style={{ fontSize: '0.82rem', color: '#334155', display: 'flex', gap: '6px' }}>
                        <span style={{ fontWeight: '700', color: '#1F4E79' }}>[{getAreaName(r.area_id)}]</span>
                        <span><b>{r.local_id ? `R${r.local_id}` : `R${r.id}`}</b>: {r.riesgo}</span>
                      </div>
                    ))}
                  </div>
                </div>
              </div>

              {/* Botones */}
              <div style={{
                padding: '16px 24px',
                borderTop: '1px solid #e2e8f0',
                display: 'flex',
                justifyContent: 'flex-end',
                gap: '12px',
                backgroundColor: '#f8fafc'
              }}>
                <button
                  type="button"
                  onClick={() => setGeneratorOpen(false)}
                  style={{
                    padding: '10px 20px',
                    borderRadius: '8px',
                    border: '1px solid #cbd5e1',
                    background: '#fff',
                    color: '#475569',
                    fontWeight: '600',
                    cursor: 'pointer'
                  }}
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  disabled={generating}
                  style={{
                    padding: '10px 24px',
                    borderRadius: '8px',
                    border: 'none',
                    background: '#1F4E79',
                    color: '#fff',
                    fontWeight: '700',
                    cursor: generating ? 'not-allowed' : 'pointer',
                    boxShadow: '0 4px 10px rgba(31, 78, 121, 0.3)'
                  }}
                >
                  {generating ? 'Generando...' : 'Crear y Vincular Riesgo Institucional'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
