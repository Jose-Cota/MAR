import { useState, useEffect, useRef } from 'react';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';

const MESES = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

const cuadrante = (p, i) => {
  p = Number(p); i = Number(i);
  if (p > 5 && i > 5) return 'QI';
  if (p > 5 && i <= 5) return 'QII';
  if (p <= 5 && i <= 5) return 'QIII';
  return 'QIV';
};

export default function POAPage() {
  const [areas, setAreas] = useState([]);
  const [areaId, setAreaId] = useState('');
  const [fichas, setFichas] = useState([]);
  const [loading, setLoading] = useState(false);
  const ejercicio = useGlobalStore((s) => s.ejercicio);

  useEffect(() => {
    axios.get('/unidades-responsables').then(res => {
      const data = res.data.data || res.data;
      setAreas(data);
      if (data.length > 0) setAreaId(String(data[0].unidad_responsable_gasto_id || data[0].id_unidad || data[0].id));
    });
  }, []);

  useEffect(() => {
    if (!areaId) return;
    setLoading(true);
    axios.get(`/poa/fichas?ejercicio_id=${ejercicio}&area_id=${areaId}`)
      .then(res => setFichas(res.data.data || res.data || []))
      .catch(() => setFichas([]))
      .finally(() => setLoading(false));
  }, [areaId, ejercicio]);

  return (
    <>
      <div className="page-head">
        <div>
          <h1>POA y acciones sustantivas</h1>
          <p>Fuente programática del ejercicio {ejercicio} y trazabilidad hacia los riesgos.</p>
        </div>
        <select className="input" style={{ maxWidth: 320 }} value={areaId} onChange={e => setAreaId(e.target.value)}>
          {areas.map((a, i) => (
            <option key={a.unidad_responsable_gasto_id || a.id_unidad || a.id || i} value={String(a.unidad_responsable_gasto_id || a.id_unidad || a.id)}>
              {a.nombre || a.denominacion}
            </option>
          ))}
        </select>
      </div>

      {loading && <div className="notice">Cargando fichas POA…</div>}

      {!loading && fichas.length === 0 && (
        <div className="empty">Sin ficha POA precargada para este ejercicio y área.</div>
      )}

      {fichas.map(proyecto => (
        <section className="panel" key={proyecto.id}>
          <div className="panel-head">
            <h2>{proyecto.nombre}</h2>
            <span className="chip">Ficha {proyecto.id}</span>
          </div>
          <p><b>Objetivo del proyecto:</b> {proyecto.objetivo || '—'}</p>

          <details open>
            <summary><b>Metas ({(proyecto.metas || []).length})</b></summary>
            <table style={{ marginTop: 8 }}>
              <thead>
                <tr>
                  <th>Tipo</th><th>Meta</th><th>Unidad</th><th>Total anual</th>
                  <th>Programación mensual</th>
                </tr>
              </thead>
              <tbody>
                {(proyecto.metas || []).map((m, idx) => (
                  <tr key={idx}>
                    <td>{m.tipo || '—'}</td>
                    <td>{m.nombre}</td>
                    <td>{m.unidad || '—'}</td>
                    <td>{m.total_anual ?? '—'}</td>
                    <td>
                      {MESES.map((mes, mi) => (
                        <span className="chip" key={mes}>{mes}: {m[`mes_${mi + 1}`] ?? '—'}</span>
                      ))}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </details>

          <details style={{ marginTop: 10 }}>
            <summary><b>Indicadores ({(proyecto.indicadores || []).length})</b></summary>
            <table style={{ marginTop: 8 }}>
              <thead>
                <tr>
                  <th>Indicador</th><th>Alineación</th><th>Objetivo</th><th>Fórmula</th><th>Unidad / dimensión / frecuencia</th>
                </tr>
              </thead>
              <tbody>
                {(proyecto.indicadores || []).map((i, idx) => (
                  <tr key={idx}>
                    <td>{i.nombre || i.indicador || '—'}</td>
                    <td>{i.alineacion || i.goalAlignment || '—'}</td>
                    <td>{i.objetivo || '—'}</td>
                    <td>{i.formula || '—'}</td>
                    <td>{i.unidad || '—'} / {i.dimension || '—'} / {i.frecuencia || '—'}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </details>

          <details open style={{ marginTop: 10 }}>
            <summary><b>Acciones sustantivas ({(proyecto.acciones || proyecto.actividades || []).length})</b></summary>
            <table style={{ marginTop: 8 }}>
              <thead>
                <tr>
                  <th>#</th><th>Acción</th><th>Riesgos vinculados</th>
                </tr>
              </thead>
              <tbody>
                {(proyecto.acciones || proyecto.actividades || []).map((a, idx) => (
                  <tr key={idx}>
                    <td>{a.numero || idx + 1}</td>
                    <td>{a.descripcion || a.denominacion || a.texto}</td>
                    <td>
                      {(a.riesgos_vinculados || a.riesgos || []).length > 0
                        ? (a.riesgos_vinculados || a.riesgos).map(r => <span className="chip" key={r.id}>{r.local_id}</span>)
                        : <span className="muted">Sin riesgo</span>}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </details>
        </section>
      ))}
    </>
  );
}
