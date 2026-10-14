import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';
import CloseIcon from '@mui/icons-material/Close';
import PrintIcon from '@mui/icons-material/Print';
import VisibilityIcon from '@mui/icons-material/Visibility';
import logoTecdmx from '../../assets/logo_tecdmx.png';

export default function ReportMARInstitucional({ asModal, onCloseModal }) {
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

  const fetchData = async () => {
    try {
      const resRiesgos = await axios.get(`/riesgos-institucionales?ejercicio_id=${ejercicio}`);
      const data = resRiesgos.data.data || resRiesgos.data;
      setRiesgos(data);
    } catch (e) {
      console.error(e);
    }
  };

  const getPrintDate = () => {
    if (settings?.print_date_type === 'ninguna') return '';
    if (settings?.print_date_type === 'fija' && settings?.print_fixed_date) {
      const d = new Date(settings.print_fixed_date + 'T12:00:00Z');
      return d.toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric', timeZone: 'UTC' });
    }
    return new Date().toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' });
  };

  useEffect(() => {
    const originalTitle = document.title;
    document.title = '\u200B'; // Zero-width space so Chrome prints nothing in the header
    return () => {
      document.title = originalTitle;
    };
  }, []);

  const renderTable = () => (
    <section 
      className="report-sheet landscape print-area"
      style={{
        position: 'relative',
        border: '1px solid #ccc',
        padding: '30px 1cm',
        backgroundColor: 'white',
        margin: '0 auto',
        boxSizing: 'border-box',
      }}
    >
      <div className="table-responsive" style={{ overflow: 'visible' }}>
        <table className="mar-table" style={{ width: '100%', borderCollapse: 'collapse', display: 'table' }}>
          <thead>
            {/* Encabezado del reporte: se repite automáticamente en cada hoja */}
            <tr>
              <th colSpan="6" style={{ border: 'none', background: '#ffffff', padding: '0 0 14px 0', fontWeight: 'normal' }}>
                <div className="report-head" style={{ display: 'flex', alignItems: 'center', gap: '20px', borderBottom: '4px solid var(--blue)', paddingBottom: '10px', marginBottom: '8px' }}>
                  <img src={logoTecdmx} alt="TECDMX" style={{ width: '140px', height: 'auto', objectFit: 'contain' }} />
                  <div style={{ flex: 1, textAlign: 'center' }}>
                    <b style={{ display: 'block', fontSize: '1.15rem', marginBottom: '3px', color: '#092e55' }}>TRIBUNAL ELECTORAL DE LA CIUDAD DE MÉXICO</b>
                    <span style={{ display: 'block', fontSize: '1rem', marginBottom: '3px', fontWeight: 'bold', color: '#1f4e78' }}>INSTITUCIONAL</span>
                    <strong style={{ display: 'block', fontSize: '1.15rem', color: '#17324d' }}>MATRIZ DE ADMINISTRACIÓN DE RIESGOS {ejercicio}</strong>
                  </div>
                  <div style={{ fontSize: '0.8rem', color: '#64748b', textAlign: 'right', minWidth: '130px' }}>
                    {getPrintDate() && (
                      <>
                        Fecha de emisión:<br />
                        <strong style={{ color: '#17324d' }}>{getPrintDate()}</strong>
                      </>
                    )}
                  </div>
                </div>
              </th>
            </tr>
            {/* Encabezado de columnas: se repite automáticamente en cada hoja */}
            <tr style={{ background: '#0b3a63' }}>
              <th style={{ width: '8%', whiteSpace: 'nowrap', textAlign: 'center', verticalAlign: 'middle', color: '#ffffff', fontWeight: 'bold' }}>No.</th>
              <th style={{ width: '18%', textAlign: 'center', verticalAlign: 'middle', color: '#ffffff', fontWeight: 'bold' }}>Objetivo Institucional</th>
              <th style={{ width: '26%', textAlign: 'center', verticalAlign: 'middle', color: '#ffffff', fontWeight: 'bold' }}>Riesgo Institucional</th>
              <th style={{ width: '16%', textAlign: 'center', verticalAlign: 'middle', color: '#ffffff', fontWeight: 'bold' }}>Factores de Riesgo</th>
              <th style={{ width: '16%', textAlign: 'center', verticalAlign: 'middle', color: '#ffffff', fontWeight: 'bold' }}>Controles</th>
              <th style={{ width: '16%', textAlign: 'center', verticalAlign: 'middle', color: '#ffffff', fontWeight: 'bold' }}>Indicadores</th>
            </tr>
          </thead>
          <tbody>
            {riesgos.map((r) => (
              <tr key={r.id} style={{ pageBreakInside: 'avoid', breakInside: 'avoid' }}>
                <td style={{ borderBottom: '1px solid #eee', padding: '10px 8px', whiteSpace: 'nowrap', fontWeight: 'bold', verticalAlign: 'top' }}>{r.folio || r.id}</td>
                <td style={{ borderBottom: '1px solid #eee', padding: '10px 8px', textAlign: 'justify', verticalAlign: 'top', lineHeight: '1.45' }}>{r.objetivo || '—'}</td>
                <td style={{ borderBottom: '1px solid #eee', padding: '10px 8px', textAlign: 'justify', verticalAlign: 'top', lineHeight: '1.45' }}>{r.riesgo}</td>
                <td style={{ borderBottom: '1px solid #eee', padding: '10px 8px', textAlign: 'justify', verticalAlign: 'top', lineHeight: '1.45' }}>
                  {r.factores || '—'}
                </td>
                <td style={{ borderBottom: '1px solid #eee', padding: '10px 8px', textAlign: 'justify', verticalAlign: 'top', lineHeight: '1.45' }}>
                  {r.control || (r.fuentes || []).map(f => (
                    f.controles && f.controles.length > 0 
                      ? f.controles.map(c => c.texto || c.control || c.descripcion).filter(Boolean).join('; ')
                      : ''
                  )).filter(Boolean).join(' | ') || '—'}
                </td>
                <td style={{ borderBottom: '1px solid #eee', padding: '10px 8px', textAlign: 'justify', verticalAlign: 'top', lineHeight: '1.45' }}>
                  {r.indicador || (r.fuentes || []).map(f => (
                    f.indicadores && f.indicadores.length > 0
                      ? f.indicadores.map(i => {
                          let formulaText = i.formula;
                          if (!formulaText || formulaText === 'Resultado = (N / D) × 100') {
                            formulaText = (i.numerador && i.denominador) 
                              ? `Resultado = (${i.numerador} / ${i.denominador}) × 100` 
                              : i.nombre || '';
                          }
                          return formulaText;
                        }).filter(Boolean).join('; ')
                      : ''
                  )).filter(Boolean).join(' | ') || '—'}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Firmas al final del reporte */}
      <div style={{ marginTop: '45px', pageBreakInside: 'avoid', breakInside: 'avoid' }}>
        <div style={{ display: 'flex', justifyContent: 'space-around', textAlign: 'center', pageBreakInside: 'avoid' }}>
          <div>
            <div style={{ borderBottom: '1px solid black', width: '250px', height: '35px', margin: '0 auto 10px' }}></div>
            <b>Secretario Técnico del CARCI</b>
          </div>
          <div>
            <div style={{ borderBottom: '1px solid black', width: '250px', height: '35px', margin: '0 auto 10px' }}></div>
            <b>Presidente del CARCI</b>
          </div>
        </div>
      </div>
    </section>
  );

  return (
    <>
      <style>
        {`
          @media print {
            @page { size: letter landscape; margin: 1cm; }
            .modal-overlay { position: static !important; padding: 0 !important; background: transparent !important; }
            .modal-content { padding: 0 !important; border: none !important; max-width: 100% !important; box-shadow: none !important; }
            .print-area { padding: 0 !important; border: none !important; box-shadow: none !important; width: 100% !important; max-width: 100% !important; margin: 0 !important; }
            .mar-table { width: 100% !important; border-collapse: collapse !important; display: table !important; }
            .mar-table thead { display: table-header-group !important; }
            .mar-table thead tr:last-child th {
              background-color: #0b3a63 !important;
              color: #ffffff !important;
              font-weight: bold !important;
              text-align: center !important;
              vertical-align: middle !important;
              -webkit-print-color-adjust: exact !important;
              print-color-adjust: exact !important;
            }
            .mar-table tbody { display: table-row-group !important; }
            .mar-table tr { page-break-inside: avoid !important; break-inside: avoid !important; }
            .mar-table td, .mar-table th { page-break-inside: avoid !important; break-inside: avoid !important; }
            body { overflow: visible !important; }
            .main-content { display: none !important; }
            .mar-table { font-size: 11px !important; }
          }
          @media screen {
            .print-area {
              width: 100%;
              margin: 0 auto;
            }
          }
        `}
      </style>

      {!asModal && (
        <div className={`main-content ${showModal ? 'no-print' : ''}`}>
          <div className="page-head no-print">
            <div>
              <h1>MAR Institucional imprimible</h1>
              <p>Institucional · {ejercicio}</p>
            </div>
            <div>
              <button className="btn" onClick={() => navigate('/mapmar')}>Volver</button>
              {' '}
              <button className="btn primary" onClick={() => setShowModal(true)}>Imprimir / PDF</button>
            </div>
          </div>
          
          {!showModal && (
            <div>
              {riesgos.length > 0 ? (
                renderTable()
              ) : (
                <section className="report-sheet landscape" style={{textAlign: 'center', padding: '40px'}}>
                  No hay riesgos institucionales validados en este ejercicio.
                </section>
              )}
            </div>
          )}
        </div>
      )}

      {showModal && (
        <div className="modal-overlay" style={{position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, background: 'rgba(0,0,0,0.6)', zIndex: 9999, padding: '40px 20px', overflowY: 'auto'}}>
          <div className="modal-content" style={{background: 'white', maxWidth: '1200px', margin: '0 auto', width: '100%', borderRadius: '8px', padding: '30px', position: 'relative'}}>
            <div className="no-print" style={{display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px'}}>
              <h2 style={{margin: 0, display: 'flex', alignItems: 'center', gap: '8px', color: '#17324d'}}>
                <VisibilityIcon /> Vista preliminar: MAR Institucional
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
            
            <div className="print-area">
              {riesgos.length > 0 ? (
                renderTable()
              ) : (
                <section className="report-sheet landscape" style={{textAlign: 'center', padding: '40px'}}>
                  No hay riesgos institucionales validados en este ejercicio.
                </section>
              )}
            </div>
          </div>
        </div>
      )}
    </>
  );
}
