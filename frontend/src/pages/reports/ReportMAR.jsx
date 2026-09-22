import { useState, useEffect } from 'react';
import { useParams, useNavigate, useLocation } from 'react-router-dom';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';

export default function ReportMAR() {
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

  return (
    <>
      <div className="page-head no-print">
        <h1>MAR imprimible</h1>
        <div>
          {state?.fromMapMAR ? (
            <button className="btn" onClick={() => navigate('/mapmar', { state: { areaId: state.areaId } })}>Volver a MAPA y MAR</button>
          ) : (
            <button className="btn" onClick={() => navigate('/reportes')}>Volver</button>
          )}{' '}
          <button className="btn primary" onClick={() => window.print()}>Imprimir / PDF</button>
        </div>
      </div>
      <section className="report-sheet landscape">
        <div className="report-head">
          <div>
            <b>TRIBUNAL ELECTORAL DE LA CIUDAD DE MÉXICO</b>
            <span>{area?.nombre || area?.denominacion}</span>
            <strong>MATRIZ DE ADMINISTRACIÓN DE RIESGOS {ejercicio}</strong>
          </div>
        </div>
        <table className="mar-table">
          <thead>
            <tr>
              <th>No.</th>
              <th>OBJETIVO</th>
              <th>RIESGO</th>
              <th>FACTORES DE RIESGO</th>
              <th>CONTROLES</th>
              <th>INDICADORES</th>
            </tr>
          </thead>
          <tbody>
            {riesgos.map(r => (
              <tr key={r.id}>
                <td>{r.local_id || r.id}</td>
                <td>{r.objetivo}</td>
                <td>{r.riesgo}</td>
                <td>{[r.factores_internos, r.factores_externos, r.factores].filter(Boolean).join('; ')}</td>
                <td>
                  {(r.controles || []).map((c, idx) => (
                    <div key={idx} style={{ marginBottom: '4px' }}>• {c.texto || c.control || c.descripcion}</div>
                  ))}
                </td>
                <td>
                  {(r.indicadores || []).map((i, idx) => (
                    <div key={idx} style={{ marginBottom: '4px' }}>• {i.nombre || i.indicador || i.formula}</div>
                  ))}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </section>
    </>
  );
}
