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

export default function ReportAnnexB() {
  const { areaId } = useParams();
  const [riesgos, setRiesgos] = useState([]);
  const [area, setArea] = useState(null);
  const ejercicio = useGlobalStore((s) => s.ejercicio);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function load() {
      try {
        const [resArea, resRiesgos] = await Promise.all([
          axios.get(`/unidades-responsables`),
          axios.get(`/riesgos?ejercicio_id=${ejercicio}&area_id=${areaId}`),
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

  if (loading) return <div style={{ padding: 20 }}>Cargando datos del Anexo B...</div>;

  return (
    <div style={{ fontFamily: 'Arial, sans-serif', padding: '28px', color: '#142b45' }}>
      <h1 style={{ fontSize: '20px', borderBottom: '3px solid #0b3a63', paddingBottom: '8px' }}>
        Anexo B – Cédula auxiliar de valoración
      </h1>
      <div style={{ padding: '10px', backgroundColor: '#eef4f8', margin: '10px 0', fontSize: '12px' }}>
        Generado automáticamente por el Sistema MAR. Área: <b>{area?.nombre || area?.denominacion || ''}</b>
      </div>
      
      <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: '11px', border: '1px solid #999', marginTop: '16px' }}>
        <thead>
          <tr>
            <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px' }}>Riesgo</th>
            <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px', width: '40px' }}>P</th>
            <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px', width: '40px' }}>I</th>
            <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px', width: '70px' }}>Cuadrante</th>
            <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px' }}>Justificación</th>
          </tr>
        </thead>
        <tbody>
          {riesgos.map(r => (
            <tr key={r.id}>
              <td style={{ border: '1px solid #999', padding: '6px' }}>
                <b>{r.local_id || r.id}</b> — {r.riesgo}
              </td>
              <td style={{ border: '1px solid #999', padding: '6px', textAlign: 'center' }}>{r.probabilidad || 0}</td>
              <td style={{ border: '1px solid #999', padding: '6px', textAlign: 'center' }}>{r.impacto || 0}</td>
              <td style={{ border: '1px solid #999', padding: '6px', textAlign: 'center' }}>
                {cuadrante(r.probabilidad || 0, r.impacto || 0)}
              </td>
              <td style={{ border: '1px solid #999', padding: '6px' }}>
                {r.justificacion_valoracion || 'Pendiente de validación'}
              </td>
            </tr>
          ))}
          {riesgos.length === 0 && (
            <tr>
              <td colSpan={5} style={{ border: '1px solid #999', padding: '6px', textAlign: 'center' }}>
                No hay riesgos capturados para esta área.
              </td>
            </tr>
          )}
        </tbody>
      </table>
    </div>
  );
}
