import { useState, useEffect } from 'react';
import { useParams, useNavigate, useLocation } from 'react-router-dom';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';

const quadrantFor = (p, i) => {
  p = Number(p); i = Number(i);
  if (p > 5 && i > 5) return 'QI';
  if (p > 5 && i <= 5) return 'QII';
  if (p <= 5 && i <= 5) return 'QIII';
  return 'QIV';
};

export default function ReportMap() {
  const { areaId } = useParams();
  const navigate = useNavigate();
  const { state } = useLocation();
  const [riesgos, setRiesgos] = useState([]);
  const [area, setArea] = useState(null);
  const ejercicio = useGlobalStore((state) => state.ejercicio);

  useEffect(() => {
    fetchData();
  }, [areaId, ejercicio]);

  const fetchData = async () => {
    try {
      const [resRiesgos, resAreas] = await Promise.all([
        axios.get(`/riesgos?ejercicio_id=${ejercicio}&area_id=${areaId}`),
        axios.get('/unidades-responsables')
      ]);
      setRiesgos(resRiesgos.data.data || resRiesgos.data);
      const areasList = resAreas.data.data || resAreas.data;
      setArea(areasList.find(a => (a.id || a.id_unidad) == areaId));
    } catch (e) {
      console.error(e);
    }
  };

  const renderSvg = () => {
    const W = 760, H = 520, left = 68, top = 24, w = 650, h = 430;
    const x = v => left + (v / 10) * w;
    const y = v => top + h - (v / 10) * h;

    const grid = [];
    for (let n = 0; n <= 10; n++) {
      grid.push(<line key={`vx${n}`} x1={x(n)} y1={top} x2={x(n)} y2={top + h} className="gridline" />);
      grid.push(<text key={`tx${n}`} x={x(n)} y={top + h + 20} textAnchor="middle">{n}</text>);
      grid.push(<line key={`hy${n}`} x1={left} y1={y(n)} x2={left + w} y2={y(n)} className="gridline" />);
      grid.push(<text key={`ty${n}`} x={left - 14} y={y(n) + 4} textAnchor="end">{n}</text>);
    }

    const dots = riesgos.map(r => {
      const p = r.probabilidad || 0;
      const i = r.impacto || 0;
      const local = r.local_id || `R${r.id}`;
      return (
        <g className="risk-dot" key={r.id}>
          <circle cx={x(i)} cy={y(p)} r="15" />
          <text x={x(i)} y={y(p) + 5} textAnchor="middle">{local.replace('R', '')}</text>
          <title>{local} — {r.riesgo}</title>
        </g>
      );
    });

    return (
      <svg viewBox={`0 0 ${W} ${H}`} className="risk-map" role="img" aria-label="Mapa de riesgos 0 a 10">
        <rect x={left} y={top} width={w / 2} height={h / 2} className="q2fill" />
        <rect x={left + w / 2} y={top} width={w / 2} height={h / 2} className="q1fill" />
        <rect x={left} y={top + h / 2} width={w / 2} height={h / 2} className="q3fill" />
        <rect x={left + w / 2} y={top + h / 2} width={w / 2} height={h / 2} className="q4fill" />
        {grid}
        <line x1={x(5)} y1={top} x2={x(5)} y2={top + h} className="midline" />
        <line x1={left} y1={y(5)} x2={left + w} y2={y(5)} className="midline" />
        {dots}
        <text x={left + w / 2} y={H - 8} textAnchor="middle" className="axislabel">GRADO DE IMPACTO</text>
        <text transform={`translate(18 ${top + h / 2}) rotate(-90)`} textAnchor="middle" className="axislabel">PROBABILIDAD DE OCURRENCIA</text>
      </svg>
    );
  };

  return (
    <>
      <div className="page-head">
        <div>
          <h1>Mapa de Riesgos</h1>
          <p>{area?.nombre || area?.denominacion} · {ejercicio}</p>
        </div>
        <div>
          {state?.fromMapMAR ? (
            <button className="btn" onClick={() => navigate('/mapmar', { state: { areaId: state.areaId } })}>Volver a MAPA y MAR</button>
          ) : (
            <button className="btn" onClick={() => navigate('/reportes')}>Volver</button>
          )}{' '}
          <button className="btn primary" onClick={() => window.print()}>Imprimir / PDF</button>
        </div>
      </div>
      <section className="report-sheet">
        <div className="report-head">
          <div>
            <b>TRIBUNAL ELECTORAL DE LA CIUDAD DE MÉXICO</b>
            <span>{area?.nombre || area?.denominacion}</span>
            <strong>MAPA DE RIESGOS {ejercicio}</strong>
          </div>
        </div>
        {renderSvg()}
        <table>
          <thead>
            <tr>
              <th>No.</th>
              <th>Riesgo</th>
              <th>Probabilidad</th>
              <th>Impacto</th>
              <th>Cuadrante</th>
            </tr>
          </thead>
          <tbody>
            {riesgos.map(r => (
              <tr key={r.id}>
                <td>{r.local_id || r.id}</td>
                <td>{r.riesgo}</td>
                <td>{r.probabilidad || 0}</td>
                <td>{r.impacto || 0}</td>
                <td>{quadrantFor(r.probabilidad || 0, r.impacto || 0)}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </section>
    </>
  );
}
