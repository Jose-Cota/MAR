import { useState, useEffect } from 'react';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';
import useAuth from '../../hooks/useAuth';
import { Edit, Save, Close } from '@mui/icons-material';
import { IconButton, Tooltip } from '@mui/material';

export default function FactoresPage() {
  const [riesgos, setRiesgos] = useState([]);
  const [areas, setAreas] = useState([]);
  const [areaId, setAreaId] = useState('');
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);
  
  const [editingId, setEditingId] = useState(null);
  const [editFactorText, setEditFactorText] = useState('');
  
  const ejercicio = useGlobalStore((s) => s.ejercicio);
  const { hasRole } = useAuth();
  
  const isSuperAdmin = hasRole('Super Administrador') || hasRole('superadmin') || hasRole('Admin');

  useEffect(() => {
    axios.get('/unidades-responsables').then(res => {
      const data = res.data.data || res.data;
      setAreas(data);
      if (data.length > 0) {
        const firstId = String(data[0].unidad_responsable_gasto_id || data[0].id_unidad || data[0].id);
        setAreaId(firstId);
      }
    });
  }, []);

  useEffect(() => {
    fetchRiesgos();
  }, [areaId, ejercicio]);

  const fetchRiesgos = async () => {
    setLoading(true);
    try {
      const url = areaId ? `/riesgos?ejercicio_id=${ejercicio}&area_id=${areaId}` : `/riesgos?ejercicio_id=${ejercicio}`;
      const res = await axios.get(url);
      setRiesgos(res.data.data || res.data || []);
    } finally {
      setLoading(false);
    }
  };

  const handleSave = async (id) => {
    try {
      await axios.put(`/riesgos/${id}`, { factores_internos: editFactorText });
      setEditingId(null);
      fetchRiesgos();
    } catch (err) {
      alert('Error al guardar: ' + (err.response?.data?.message || err.message));
    }
  };

  const allowedAreaIds = areas.map(a => String(a.unidad_responsable_gasto_id || a.id_unidad || a.id));
  const filtrados = riesgos.filter(r => {
    const matchesArea = areaId ? String(r.area_id) === String(areaId) : (isSuperAdmin || allowedAreaIds.includes(String(r.area_id)));
    const matchesSearch = !search || (r.local_id + ' ' + r.riesgo + ' ' + (r.factores_internos || '')).toLowerCase().includes(search.toLowerCase());
    return matchesArea && matchesSearch;
  });

  return (
    <>
      <div className="page-head">
        <div>
          <h1>Factores</h1>
          <p>Factores de riesgo asociados a cada riesgo del Formato 2.</p>
        </div>
      </div>

      <section className="panel" style={{ padding: '20px', marginBottom: '20px' }}>
        <div className="form-grid" style={{ gridTemplateColumns: '1fr 2fr', gap: '20px' }}>
          <div>
            <label style={{ display: 'block', marginBottom: '5px', fontWeight: 600 }}>Área / Unidad Responsable</label>
            <select className="input" style={{ width: '100%' }} value={areaId} onChange={e => setAreaId(e.target.value)}>
              <option value="">Todas las áreas asignadas</option>
              {areas.map((a, i) => (
                <option key={a.unidad_responsable_gasto_id || a.id_unidad || a.id || i} value={String(a.unidad_responsable_gasto_id || a.id_unidad || a.id)}>
                  {a.nombre || a.denominacion}
                </option>
              ))}
            </select>
          </div>
          <div>
            <label style={{ display: 'block', marginBottom: '5px', fontWeight: 600 }}>Buscar</label>
            <input className="input" style={{ width: '100%' }} placeholder="ID, riesgo o factor..." value={search} onChange={e => setSearch(e.target.value)} />
          </div>
        </div>
      </section>

      <section className="panel">
        {loading ? (
          <p style={{ padding: '20px' }}>Cargando factores...</p>
        ) : (
          <table className="data-table">
            <thead>
              <tr style={{ backgroundColor: '#1F4E79', color: '#fff' }}>
                <th style={{ color: '#fff', width: '80px' }}>ID</th>
                <th style={{ color: '#fff', width: '40%' }}>Riesgo</th>
                <th style={{ color: '#fff' }}>Factores de riesgo</th>
                <th style={{ color: '#fff', width: '100px', textAlign: 'center' }}>Acciones</th>
              </tr>
            </thead>
            <tbody>
              {filtrados.map(r => (
                  <tr key={r.id} style={{ transition: 'background-color 0.2s', verticalAlign: 'top' }}>
                    <td style={{ paddingTop: '15px' }}><b>{r.local_id}</b></td>
                    <td style={{ paddingTop: '15px' }}>{r.riesgo}</td>
                    <td style={{ paddingTop: '15px' }}>
                      <div style={{ whiteSpace: 'pre-wrap', color: r.factores_internos ? 'inherit' : '#64748b', backgroundColor: r.factores_internos ? 'transparent' : '#f8fafc', padding: r.factores_internos ? '0' : '8px', borderRadius: '4px' }}>
                        {r.factores_internos || 'Sin factores registrados'}
                      </div>
                    </td>
                    <td style={{ paddingTop: '15px' }}>
                      <div style={{ display: 'flex', justifyContent: 'center' }}>
                        <Tooltip title="Editar">
                          <IconButton size="small" color="primary" onClick={() => { setEditingId(r.id); setEditFactorText(r.factores_internos || ''); }}>
                            <Edit fontSize="small" />
                          </IconButton>
                        </Tooltip>
                      </div>
                    </td>
                  </tr>
              ))}
              {filtrados.length === 0 && (
                <tr><td colSpan="4" style={{ textAlign: 'center', color: '#6f8294', padding: '20px' }}>
                  No hay factores para mostrar.
                </td></tr>
              )}
            </tbody>
          </table>
        )}
      </section>

      {/* Modal de edición */}
      {editingId && (
        <div style={{ position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, backgroundColor: 'rgba(15, 23, 42, 0.7)', zIndex: 2000, display: 'flex', alignItems: 'center', justifyContent: 'center', backdropFilter: 'blur(4px)' }}>
          <div style={{ backgroundColor: '#fff', borderRadius: '12px', width: '600px', maxWidth: '90%', padding: '24px', boxShadow: '0 20px 25px -5px rgba(0, 0, 0, 0.1)' }}>
            <h3 style={{ margin: '0 0 16px', color: '#0f172a', fontSize: '1.2rem', display: 'flex', alignItems: 'center', gap: '8px' }}>
              <Edit color="primary" /> Editar factor de riesgo
            </h3>
            <div style={{ marginBottom: '24px' }}>
              <textarea
                className="input"
                style={{ width: '100%', minHeight: '120px', resize: 'vertical' }}
                value={editFactorText}
                onChange={(e) => setEditFactorText(e.target.value)}
              />
            </div>
            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '12px' }}>
              <button className="btn" onClick={() => setEditingId(null)}>Cancelar</button>
              <button className="btn primary" onClick={() => handleSave(editingId)}>Guardar</button>
            </div>
          </div>
        </div>
      )}
    </>
  );
}
