import { useState, useEffect } from 'react';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';

export default function ControlesPage() {
  const [controles, setControles] = useState([]);
  const [areas, setAreas] = useState([]);
  const [loading, setLoading] = useState(true);
  const ejercicio = useGlobalStore((s) => s.ejercicio);

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

      // Flatten: un row por control
      const rows = riesgos.flatMap(r =>
        (r.controles || []).map(c => ({ r, c }))
      );
      setControles(rows);
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
          <h1>Controles</h1>
          <p>Consulta transversal de controles y evidencia registrada en los riesgos.</p>
        </div>
      </div>
      <section className="panel">
        {loading ? (
          <p>Cargando…</p>
        ) : controles.length === 0 ? (
          <div className="empty">No hay controles registrados para este ejercicio.</div>
        ) : (
          <table>
            <thead>
              <tr>
                <th>Área</th>
                <th>Riesgo</th>
                <th>Control</th>
                <th>Evidencia</th>
                <th>Periodicidad</th>
                <th>Responsable</th>
              </tr>
            </thead>
            <tbody>
              {controles.map(({ r, c }, idx) => (
                <tr key={idx}>
                  <td>{getAreaName(r.area_id)}</td>
                  <td><b>{r.local_id}</b> · {r.riesgo}</td>
                  <td>{c.control || '—'}</td>
                  <td>{[c.evidencia_tipo, c.evidencia_referencia].filter(Boolean).join(' ') || '—'}</td>
                  <td>{c.evidencia_periodicidad || '—'}</td>
                  <td>{c.evidencia_responsable || '—'}</td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </section>
    </>
  );
}
