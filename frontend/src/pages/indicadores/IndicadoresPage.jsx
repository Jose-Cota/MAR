import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';

export default function IndicadoresPage() {
  const [indicadores, setIndicadores] = useState([]);
  const [areas, setAreas] = useState([]);
  const [loading, setLoading] = useState(true);
  const ejercicio = useGlobalStore((s) => s.ejercicio);
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

      // Flatten: un row por indicador
      const rows = riesgos.flatMap(r =>
        (r.indicadores || []).map(i => ({ r, i }))
      );
      setIndicadores(rows);
    } finally {
      setLoading(false);
    }
  };

  const getAreaName = (areaId) => {
    const a = areas.find(x => String(x.id_unidad || x.id) === String(areaId));
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
                  <td><b>{r.local_id}</b></td>
                  <td>{i.nombre}</td>
                  <td>{i.tipo || '—'}</td>
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
