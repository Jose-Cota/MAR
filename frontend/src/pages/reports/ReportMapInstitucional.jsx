import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';
import CloseIcon from '@mui/icons-material/Close';
import PrintIcon from '@mui/icons-material/Print';
import VisibilityIcon from '@mui/icons-material/Visibility';

const quadrantFor = (p, i) => {
  p = Number(p); i = Number(i);
  if (p > 5 && i > 5) return 'QI';
  if (p > 5 && i <= 5) return 'QII';
  if (p <= 5 && i <= 5) return 'QIII';
  return 'QIV';
};

export default function ReportMapInstitucional({ asModal, onCloseModal }) {
  const navigate = useNavigate();
  const [riesgos, setRiesgos] = useState([]);
  const [showModal, setShowModal] = useState(asModal);
  const ejercicio = useGlobalStore((state) => state.ejercicio);
  const settings = useGlobalStore((state) => state.settings);
  const fetchSettings = useGlobalStore((state) => state.fetchSettings);

  useEffect(() => {
    fetchData();
    fetchSettings();
  }, [ejercicio]);

  const getPrintDate = () => {
    if (settings?.print_date_type === 'ninguna') return '';
    if (settings?.print_date_type === 'fija' && settings?.print_fixed_date) {
      const d = new Date(settings.print_fixed_date + 'T12:00:00Z');
      return d.toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric', timeZone: 'UTC' });
    }
    return new Date().toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' });
  };

  const fetchData = async () => {
    try {
      // Filtrar riesgos institucionales validados (o todos si así se requiere, usaremos todos los validados o simplemente los RI)
      const resRiesgos = await axios.get(`/riesgos-institucionales?ejercicio_id=${ejercicio}`);
      const data = resRiesgos.data.data || resRiesgos.data;
      setRiesgos(data);
    } catch (e) {
      console.error(e);
    }
  };

  useEffect(() => {
    const originalTitle = document.title;
    document.title = '\u200B'; // Zero-width space so Chrome prints nothing in the header
    return () => {
      document.title = originalTitle;
    };
  }, []);

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
      const local = r.folio || `RI-${r.id}`;
      return (
        <g className="risk-dot" key={r.id}>
          <circle cx={x(i)} cy={y(p)} r="15" />
          <text x={x(i)} y={y(p) + 5} textAnchor="middle" style={{fontSize: '11px'}}>{local.replace('RI-', 'R')}</text>
          <title>{local} — {r.riesgo}</title>
        </g>
      );
    });

    return (
      <svg viewBox={`0 0 ${W} ${H}`} className="risk-map" role="img" aria-label="Mapa de riesgos Institucional">
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
      <style>
        {`
          @media print {
            @page { size: letter portrait; margin: 5mm; }
            .modal-overlay { position: static !important; padding: 0 !important; background: transparent !important; }
            .modal-content { padding: 0 !important; border: none !important; max-width: 100% !important; }
            .print-area { padding: 0 !important; border: none !important; box-shadow: none !important; width: 100% !important; max-width: 100% !important; min-height: auto !important; margin: 0 !important; }
            body { overflow: visible !important; }
            .main-content { display: none !important; }
            .mar-table { font-size: 10px !important; }
          }
          @media screen {
            .print-area {
              width: 100%;
              max-width: 8.5in;
              margin: 0 auto;
              box-shadow: 0 0 10px rgba(0,0,0,0.15);
            }
          }
        `}
      </style>

      {!asModal && (
        <div className={`main-content ${showModal ? 'no-print' : ''}`}>
          <div className="page-head no-print">
            <div>
              <h1>Mapa de Riesgos Institucional</h1>
              <p>Institucional · {ejercicio}</p>
            </div>
            <div>
              <button className="btn" onClick={() => navigate('/mapmar')}>Volver</button>
              {' '}
              <button className="btn primary" onClick={() => setShowModal(true)}>Imprimir / PDF</button>
            </div>
          </div>
        
        {!showModal && (
          <section className="report-sheet">
            <div className="report-head">
              <div>
                <b>TRIBUNAL ELECTORAL DE LA CIUDAD DE MÉXICO</b>
                <span>INSTITUCIONAL</span>
                <strong>MAPA DE RIESGOS {ejercicio}</strong>
              </div>
            </div>
            {renderSvg()}
            <table className="mar-table">
              <thead>
                <tr>
                  <th>No.</th>
                  <th>Riesgo Institucional</th>
                  <th>Probabilidad</th>
                  <th>Impacto</th>
                  <th>Cuadrante</th>
                </tr>
              </thead>
              <tbody>
                {riesgos.map(r => (
                  <tr key={r.id}>
                    <td>{r.folio || r.id}</td>
                    <td>{r.riesgo}</td>
                    <td>{r.probabilidad || 0}</td>
                    <td>{r.impacto || 0}</td>
                    <td>{quadrantFor(r.probabilidad || 0, r.impacto || 0)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </section>
        )}
        </div>
      )}

      {showModal && (
        <div className="modal-overlay" style={{position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, background: 'rgba(0,0,0,0.6)', zIndex: 9999, padding: '40px 20px', overflowY: 'auto'}}>
          <div className="modal-content" style={{background: 'white', maxWidth: '900px', margin: '0 auto', width: '100%', borderRadius: '8px', padding: '30px', position: 'relative'}}>
            <div className="no-print" style={{display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px'}}>
              <h2 style={{margin: 0, display: 'flex', alignItems: 'center', gap: '8px', color: '#17324d'}}>
                <VisibilityIcon /> Vista preliminar: Mapa de Riesgos Institucional
              </h2>
              <div style={{display: 'flex', gap: '10px'}}>
                <button className="icon-btn" onClick={() => window.print()} title="Confirmar Impresión" style={{ padding: '8px', fontSize: '20px' }}>
                  <PrintIcon fontSize="inherit" />
                </button>
                <button className="icon-btn" onClick={() => { setShowModal(false); if(onCloseModal) onCloseModal(); }} title="Cerrar vista preliminar" style={{ padding: '8px', fontSize: '20px', background: '#ffebee', color: '#c62828' }}>
                  <CloseIcon fontSize="inherit" />
                </button>
              </div>
            </div>
            
            <section className="report-sheet print-area" style={{position: 'relative', border: '1px solid #ccc', padding: '40px', minHeight: '11in', backgroundColor: 'white', margin: '0 auto'}}>
              <div className="report-head">
                <div>
                  <b>TRIBUNAL ELECTORAL DE LA CIUDAD DE MÉXICO</b>
                  <span>INSTITUCIONAL</span>
                  <strong>MAPA DE RIESGOS {ejercicio}</strong>
                </div>
              </div>

              {renderSvg()}

              <table className="mar-table">
                <thead>
                  <tr>
                    <th>No.</th>
                    <th>Riesgo Institucional</th>
                    <th>Probabilidad</th>
                    <th>Impacto</th>
                    <th>Cuadrante</th>
                  </tr>
                </thead>
                <tbody>
                  {riesgos.map(r => (
                    <tr key={r.id}>
                      <td>{r.folio || r.id}</td>
                      <td>{r.riesgo}</td>
                      <td>{r.probabilidad || 0}</td>
                      <td>{r.impacto || 0}</td>
                      <td>{quadrantFor(r.probabilidad || 0, r.impacto || 0)}</td>
                    </tr>
                  ))}
                  {riesgos.length === 0 && (
                    <tr>
                      <td colSpan="5" style={{textAlign: 'center'}}>No hay riesgos institucionales validados en este ejercicio.</td>
                    </tr>
                  )}
                </tbody>
              </table>
              <p style={{textAlign: 'center', marginTop: '20px', fontSize: '13px', color: '#556b7c'}}>
                {getPrintDate() && `Fecha de emisión: ${getPrintDate()}`}
              </p>

            </section>
          </div>
        </div>
      )}
    </>
  );
}
