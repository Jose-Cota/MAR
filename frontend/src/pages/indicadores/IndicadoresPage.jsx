import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';
import useAuth from '../../hooks/useAuth';

export default function IndicadoresPage() {
  const [indicadores, setIndicadores] = useState([]);
  const [areas, setAreas] = useState([]);
  const [loading, setLoading] = useState(true);
  const ejercicio = useGlobalStore((s) => s.ejercicio);
  const { hasRole } = useAuth();
  const isSuperAdmin = hasRole('Super Administrador') || hasRole('superadmin') || hasRole('Admin');
  const navigate = useNavigate();

  useEffect(() => {
    fetchData();
  }, [ejercicio]);

  const fetchData = async () => {
    setLoading(true);
    try {
      const [resRiesgos, resAreas] = await Promise.all([
        axios.get(`/riesgos?ejercicio_id=${ejercicio}`),
        axios.get('/unidades-responsables'),
      ]);
      const riesgos = resRiesgos.data.data || resRiesgos.data;
      const areasData = resAreas.data.data || resAreas.data;
      setAreas(areasData);

      const allowedAreaIds = areasData.map(a => String(a.unidad_responsable_gasto_id || a.id_unidad || a.id));
      const filteredRiesgos = riesgos.filter(r => isSuperAdmin || allowedAreaIds.includes(String(r.area_id)));

      // Flatten: un row por indicador
      const rows = filteredRiesgos.flatMap(r =>
        (r.indicadores || []).map(i => ({ r, i }))
      );
      setIndicadores(rows);
    } finally {
      setLoading(false);
    }
  };

  const getAreaName = (areaId) => {
    const a = areas.find(x => String(x.unidad_responsable_gasto_id || x.id_unidad || x.id) === String(areaId));
    return a?.nombre || a?.denominacion || areaId;
  };

  return (
    <>
      <div className="page-head">
        <div>
          <h1>Indicadores</h1>
          <p>Indicadores de riesgo y su vínculo con el seguimiento mensual/trimestral.</p>
        </div>
        <button className="btn primary" onClick={() => navigate('/seguimiento')}>
          Capturar seguimiento
        </button>
      </div>
      <section className="panel">
        {loading ? (
          <p>Cargando…</p>
        ) : indicadores.length === 0 ? (
          <div className="empty">No hay indicadores registrados para este ejercicio.</div>
        ) : (
          <table>
            <thead>
              <tr>
                <th>Área</th>
                <th>Riesgo</th>
                <th>Indicador</th>
                <th>Tipo</th>
                <th>Periodicidad</th>
              </tr>
            </thead>
            <tbody>
              {indicadores.map(({ r, i }, idx) => (
                <tr key={idx}>
                  <td>{getAreaName(r.area_id)}</td>
                  <td>{r.local_id}</td>
                  <td>{i.nombre}</td>
                  <td>{i.tipo || 'Riesgo'}</td>
                  <td>{i.periodicidad || '—'}</td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </section>
    </>
  );
}
