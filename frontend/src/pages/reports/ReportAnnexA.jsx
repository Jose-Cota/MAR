import { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';

export default function ReportAnnexA() {
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

        // Auto print after rendering
        setTimeout(() => window.print(), 500);
      } catch (err) {
        console.error(err);
      } finally {
        setLoading(false);
      }
    }
    load();
  }, [areaId, ejercicio]);

  if (loading) return <div style={{ padding: 20 }}>Cargando datos del Anexo A...</div>;

  return (
    <div style={{ fontFamily: 'Arial, sans-serif', padding: '28px', color: '#142b45' }}>
      <h1 style={{ fontSize: '20px', borderBottom: '3px solid #0b3a63', paddingBottom: '8px' }}>
        Anexo A – Cédula auxiliar para identificación y alineación del riesgo
      </h1>
      <div style={{ padding: '10px', backgroundColor: '#eef4f8', margin: '10px 0', fontSize: '12px' }}>
        Instrumento auxiliar. No sustituye los Formatos 1 y 2 de los Lineamientos.
      </div>
      
      {riesgos.map((r, index) => (
        <div key={r.id} style={{ marginBottom: '32px', pageBreakInside: 'avoid' }}>
          <h2 style={{ fontSize: '16px', color: '#0b3a63' }}>{r.local_id || `R${index + 1}`}</h2>
          <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: '11px', border: '1px solid #999' }}>
            <tbody>
              <tr>
                <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px', textAlign: 'left', width: '25%' }}>
                  UR / RO / Área
                </th>
                <td style={{ border: '1px solid #999', padding: '6px' }}>
                  {area?.nombre || area?.denominacion || '—'}
                </td>
              </tr>
              <tr>
                <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px', textAlign: 'left' }}>
                  Ficha / Proyecto POA
                </th>
                <td style={{ border: '1px solid #999', padding: '6px' }}>
                  {/* TODO: Mostrar proyecto del POA asociado si lo hay, por ahora lo sacamos del riesgo si existe */}
                  {r.proyecto_id ? `Proyecto ID: ${r.proyecto_id}` : '—'}
                </td>
              </tr>
              <tr>
                <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px', textAlign: 'left' }}>
                  Acciones sustantivas
                </th>
                <td style={{ border: '1px solid #999', padding: '6px' }}>
                  {(r.actividades_sustantivas || []).length > 0
                    ? r.actividades_sustantivas.map(a => a.actividad).join(', ')
                    : '—'}
                </td>
              </tr>
              <tr>
                <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px', textAlign: 'left' }}>
                  Objetivo
                </th>
                <td style={{ border: '1px solid #999', padding: '6px' }}>
                  {r.objetivo || '—'}
                </td>
              </tr>
              <tr>
                <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px', textAlign: 'left' }}>
                  ¿Qué podría impedir el objetivo?
                </th>
                <td style={{ border: '1px solid #999', padding: '6px' }}>
                  {r.riesgo || '—'}
                </td>
              </tr>
              <tr>
                <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px', textAlign: 'left' }}>
                  Factores
                </th>
                <td style={{ border: '1px solid #999', padding: '6px' }}>
                  {r.factores || '—'}
                </td>
              </tr>
              <tr>
                <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px', textAlign: 'left' }}>
                  Controles / evidencia
                </th>
                <td style={{ border: '1px solid #999', padding: '6px' }}>
                  {(r.controles || []).length > 0 ? (
                    r.controles.map((c, i) => (
                      <div key={i}>{c.control} — {c.estado_validacion || 'Propuesto'}</div>
                    ))
                  ) : '—'}
                </td>
              </tr>
              <tr>
                <th style={{ background: '#0b3a63', color: '#fff', border: '1px solid #999', padding: '6px', textAlign: 'left' }}>
                  Responsable de validación
                </th>
                <td style={{ border: '1px solid #999', padding: '6px' }}>
                  {r.responsable_validacion || 'Pendiente'}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      ))}

      {riesgos.length === 0 && (
        <p style={{ marginTop: '20px' }}>No hay riesgos capturados para esta área.</p>
      )}
    </div>
  );
}
