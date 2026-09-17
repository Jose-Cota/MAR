import { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';

const cuadrante = (p, i) => {
  p = Number(p); i = Number(i);
  if (p > 5 && i > 5) return 'QI';
  if (p > 5 && i <= 5) return 'QII';
  if (p <= 5 && i <= 5) return 'QIII';
  return 'QIV';
};

export default function ReportAnnexC() {
  const { areaId } = useParams();
  const [riesgos, setRiesgos] = useState([]);
  const [reviews, setReviews] = useState([]); // This will be fetched from backend later
  const [area, setArea] = useState(null);
  const ejercicio = useGlobalStore((s) => s.ejercicio);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function load() {
      try {
        const [resArea, resRiesgos] = await Promise.all([
          axios.get(`/unidades-responsables`),
          axios.get(`/riesgos?ejercicio_id=${ejercicio}&area_id=${areaId}`),
          // axios.get(`/quarterly-reviews?ejercicio_id=${ejercicio}&area_id=${areaId}`) -> TODO
        ]);
        
        const dataArea = resArea.data.data || resArea.data;
        const found = dataArea.find(a => String(a.unidad_responsable_gasto_id || a.id_unidad || a.id) === String(areaId));
        setArea(found);

        const dataRiesgos = resRiesgos.data.data || resRiesgos.data;
        setRiesgos(dataRiesgos);

        setTimeout(() => window.print(), 500);
      } catch (err) {
        console.error(err);
      } finally {
        setLoading(false);
      }
    }
    load();
  }, [areaId, ejercicio]);

  if (loading) return <div style={{ padding: 20 }}>Cargando datos del Anexo C...</div>;

  // Generate 4 rows per risk (T1, T2, T3, T4)
  const rows = riesgos.flatMap(r => [1, 2, 3, 4].map(q => {
    const rev = reviews.find(x => String(x.riesgo_id) === String(r.id) && x.trimestre === q);
    return { r, q, rev };
  }));

  return (
    <div style={{ fontFamily: 'Arial, sans-serif', padding: '28px', color: '#142b45' }}>
      <h1 style={{ fontSize: '20px', borderBottom: '3px solid #0b3a63', paddingBottom: '8px' }}>
        Anexo C – Cédula de seguimiento trimestral de riesgos
      </h1>
      
      {ejercicio === 2026 && (
        <div style={{ padding: '10px', backgroundColor: '#eef4f8', margin: '10px 0', fontSize: '12px' }}>
          <b>Implementación 2026:</b> T1–T3 integración inicial retrospectiva enero–septiembre; T4 seguimiento ordinario.
        </div>
      )}

      <div style={{ marginBottom: '16px', fontSize: '14px' }}>
        Área: <b>{area?.nombre || area?.denominacion || ''}</b>
      </div>
      
      <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: '11px', border: '1px solid #999' }}>
        <thead>
          <tr>
            <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px' }}>Riesgo</th>
            <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px' }}>Trimestre</th>
            <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px' }}>P/I inicial</th>
            <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px' }}>P/I actual</th>
            <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px' }}>Cuadrante</th>
            <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px' }}>Control/evidencia</th>
            <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px' }}>Incidencia</th>
            <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px' }}>Acción</th>
            <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px' }}>Estatus</th>
            <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px' }}>Responsable/fecha</th>
          </tr>
        </thead>
        <tbody>
          {rows.map(({ r, q, rev }, idx) => (
            <tr key={`${r.id}-${q}`}>
              <td style={{ border: '1px solid #999', padding: '6px' }}>{r.local_id || r.id}</td>
              <td style={{ border: '1px solid #999', padding: '6px' }}>
                T{q} {ejercicio === 2026 && q <= 3 ? 'Integración inicial' : 'Ordinario'}
              </td>
              <td style={{ border: '1px solid #999', padding: '6px', textAlign: 'center' }}>
                {r.probabilidad_inicial ?? r.probabilidad}/{r.impacto_inicial ?? r.impacto}
              </td>
              <td style={{ border: '1px solid #999', padding: '6px', textAlign: 'center' }}>
                {rev ? `${rev.probabilidad}/${rev.impacto}` : '—'}
              </td>
              <td style={{ border: '1px solid #999', padding: '6px', textAlign: 'center' }}>
                {rev ? cuadrante(rev.probabilidad, rev.impacto) : '—'}
              </td>
              <td style={{ border: '1px solid #999', padding: '6px' }}>{rev?.evidencia_control || '—'}</td>
              <td style={{ border: '1px solid #999', padding: '6px' }}>{rev?.incidencia || '—'}</td>
              <td style={{ border: '1px solid #999', padding: '6px' }}>{rev?.accion || '—'}</td>
              <td style={{ border: '1px solid #999', padding: '6px' }}>{rev?.estatus || '—'}</td>
              <td style={{ border: '1px solid #999', padding: '6px' }}>
                {rev?.responsable || '—'} {rev?.fecha ? new Date(rev.fecha).toLocaleDateString('es-MX') : ''}
              </td>
            </tr>
          ))}
          {rows.length === 0 && (
            <tr>
              <td colSpan={10} style={{ border: '1px solid #999', padding: '6px', textAlign: 'center' }}>
                No hay riesgos capturados para esta área.
              </td>
            </tr>
          )}
        </tbody>
      </table>
    </div>
  );
}
