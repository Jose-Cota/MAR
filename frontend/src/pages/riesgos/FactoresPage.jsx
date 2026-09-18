import { useState, useEffect } from 'react';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';
import useAuth from '../../hooks/useAuth';

export default function FactoresPage() {
  const [riesgos, setRiesgos] = useState([]);
  const [areas, setAreas] = useState([]);
  const [areaId, setAreaId] = useState('');
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);
  
  const ejercicio = useGlobalStore((s) => s.ejercicio);
  const { hasRole } = useAuth();
  
  const isSuperAdmin = hasRole('Super Administrador') || hasRole('superadmin') || hasRole('Admin');

  useEffect(() => {
    // Si no es super admin, las areas serian solo las asignadas (el backend ya deberia filtrar si es necesario, 
    // pero aqui asumiremos que /unidades-responsables retorna las correctas para el usuario)
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

  const getFactors = (r) => {
    const arr = [];
    if (r.factores) arr.push(...r.factores.split(';').map(s => s.trim()).filter(Boolean));
    if (r.factores_internos) arr.push(...r.factores_internos.split(';').map(s => s.trim()).filter(Boolean));
    if (r.factores_externos) arr.push(...r.factores_externos.split(';').map(s => s.trim()).filter(Boolean));
    // deduplicate
    return [...new Set(arr)];
  };

  const filtrados = riesgos.map(r => ({
    ...r, 
    factorList: getFactors(r)
  })).filter(r => 
    !search || 
    (r.local_id + ' ' + r.riesgo + ' ' + r.factorList.join(' ')).toLowerCase().includes(search.toLowerCase())
  );

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
          <p>Cargando factores...</p>
        ) : (
          <table className="data-table">
            <thead>
              <tr style={{ backgroundColor: '#1F4E79', color: '#fff' }}>
                <th style={{ color: '#fff', width: '80px' }}>ID</th>
                <th style={{ color: '#fff', width: '40%' }}>Riesgo</th>
                <th style={{ color: '#fff' }}>Factores de riesgo</th>
              </tr>
            </thead>
            <tbody>
              {filtrados.map(r => (
                <tr key={r.id} style={{ transition: 'background-color 0.2s' }}>
                  <td><b>{r.local_id}</b></td>
                  <td>{r.riesgo}</td>
                  <td>
                    {r.factorList.length > 0 ? (
                      <div style={{ display: 'flex', flexDirection: 'column', gap: '6px' }}>
                        {r.factorList.map((f, i) => (
                          <div key={i} style={{ backgroundColor: '#f8fafc', padding: '8px 12px', borderRadius: '6px', border: '1px solid #e2e8f0', fontSize: '0.9rem' }}>
                            <b>{i + 1}.</b> {f}
                          </div>
                        ))}
                      </div>
                    ) : (
                      <span className="muted">—</span>
                    )}
                  </td>
                </tr>
              ))}
              {filtrados.length === 0 && (
                <tr><td colSpan="3" style={{ textAlign: 'center', color: '#6f8294', padding: '20px' }}>
                  No hay factores para mostrar.
                </td></tr>
              )}
            </tbody>
          </table>
        )}
      </section>
    </>
  );
}
