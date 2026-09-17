import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';

const cuadrante = (p, i) => {
  p = Number(p); i = Number(i);
  if (p > 5 && i > 5) return 'QI';
  if (p > 5 && i <= 5) return 'QII';
  if (p <= 5 && i <= 5) return 'QIII';
  return 'QIV';
};

export default function ReportesPage() {
  const [areas, setAreas] = useState([]);
  const [areaId, setAreaId] = useState('');
  const ejercicio = useGlobalStore((s) => s.ejercicio);
  const navigate = useNavigate();

  useEffect(() => {
    axios.get('/unidades-responsables').then(res => {
      const data = res.data.data || res.data;
      setAreas(data);
      if (data.length > 0) setAreaId(String(data[0].unidad_responsable_gasto_id || data[0].id_unidad || data[0].id));
    });
  }, []);

  const handleExportExcel = async () => {
    try {
      const [resRiesgos] = await Promise.all([
        axios.get(`/riesgos?ejercicio_id=${ejercicio}&area_id=${areaId}`),
      ]);
      const riesgos = resRiesgos.data.data || resRiesgos.data;
      const area = areas.find(a => String(a.id_unidad || a.id) === String(areaId));

      const headers = ['No.', 'OBJETIVO', 'RIESGO', 'FACTORES DE RIESGO', 'CONTROLES', 'INDICADORES', 'PROBABILIDAD', 'IMPACTO', 'CUADRANTE'];
      const rows = riesgos.map(r => `<tr>
        <td>${r.local_id || r.id}</td>
        <td>${r.objetivo || ''}</td>
        <td>${r.riesgo || ''}</td>
        <td>${r.factores || ''}</td>
        <td>${(r.controles || []).map(c => c.control).join('; ')}</td>
        <td>${(r.indicadores || []).map(i => i.nombre).join('; ')}</td>
        <td>${r.probabilidad || 0}</td>
        <td>${r.impacto || 0}</td>
        <td>${cuadrante(r.probabilidad || 0, r.impacto || 0)}</td>
      </tr>`).join('');

      const html = `<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
      <head><meta charset="utf-8"><style>
        body{font-family:Arial}table{border-collapse:collapse}
        td,th{border:1px solid #b8c7d4;padding:6px;vertical-align:top}
        th{background:#1F4E78;color:white;font-weight:bold}
        .title{text-align:center;font-weight:bold;font-size:16px}
        .sub{text-align:center;font-size:13px}
      </style></head>
      <body><table>
        <tr><td colspan="9" class="title">TRIBUNAL ELECTORAL DE LA CIUDAD DE MÉXICO</td></tr>
        <tr><td colspan="9" class="sub">${area?.nombre || area?.denominacion || ''}</td></tr>
        <tr><td colspan="9" class="title">MATRIZ DE ADMINISTRACIÓN DE RIESGOS ${ejercicio}</td></tr>
        <tr>${headers.map(h => `<th>${h}</th>`).join('')}</tr>
        ${rows}
      </table></body></html>`;

      const blob = new Blob([html], { type: 'application/vnd.ms-excel;charset=utf-8' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `MAR_${areaId}_${ejercicio}.xls`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
    } catch (e) {
      alert('Error exportando Excel: ' + e.message);
    }
  };

  return (
    <>
      <div className="page-head">
        <div>
          <h1>Reportes y respaldos</h1>
          <p>Formatos oficiales y anexos auxiliares de la Guía v2.</p>
        </div>
      </div>
      <div className="notice" style={{ backgroundColor: '#eef4f8', padding: '16px', borderRadius: '8px', marginBottom: '24px', color: '#142b45', borderLeft: '4px solid #0b3a63' }}>
        <strong>Jerarquía documental:</strong> los Anexos A, B y C son instrumentos auxiliares y no sustituyen el Formato 1 – Mapa de Riesgos ni el Formato 2 – MAR.
      </div>
      <div className="grid-2">
        <section className="panel">
          <h2>Reportes por área</h2>
          <label style={{ fontWeight: 600 }}>Área
            <select className="input" style={{ width: '100%' }} value={areaId} onChange={e => setAreaId(e.target.value)}>
              {areas.map((a, i) => (
                <option key={a.unidad_responsable_gasto_id || a.id_unidad || a.id || i} value={String(a.unidad_responsable_gasto_id || a.id_unidad || a.id)}>
                  {a.nombre || a.denominacion}
                </option>
              ))}
            </select>
          </label>
          <div className="actions" style={{ marginTop: 14 }}>
            <button className="btn primary" onClick={() => navigate(`/reportes/mapa/${areaId}`)}>
              Formato 1 – Mapa / PDF
            </button>
            <button className="btn" onClick={() => navigate(`/reportes/mar/${areaId}`)}>
              Formato 2 – MAR
            </button>
            <button className="btn" onClick={handleExportExcel}>
              Excel
            </button>
          </div>
          
          <h2 style={{ marginTop: '24px' }}>Anexos auxiliares Guía v2</h2>
          <div className="actions" style={{ marginTop: 14 }}>
            <button className="btn" onClick={() => navigate(`/reportes/anexo-a/${areaId}`)}>
              Anexo A
            </button>
            <button className="btn" onClick={() => navigate(`/reportes/anexo-b/${areaId}`)}>
              Anexo B
            </button>
            <button className="btn" onClick={() => navigate(`/reportes/anexo-c/${areaId}`)}>
              Anexo C
            </button>
          </div>
        </section>
      </div>
    </>
  );
}
