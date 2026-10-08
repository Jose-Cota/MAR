import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';
import CloseIcon from '@mui/icons-material/Close';
import PrintIcon from '@mui/icons-material/Print';
import VisibilityIcon from '@mui/icons-material/Visibility';

export default function ReportMARInstitucional({ asModal, onCloseModal }) {
  const navigate = useNavigate();
  const [riesgos, setRiesgos] = useState([]);
  const [showModal, setShowModal] = useState(asModal);
  const ejercicio = useGlobalStore((state) => state.ejercicio);

  useEffect(() => {
    fetchData();
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

  useEffect(() => {
    const originalTitle = document.title;
    document.title = '\u200B'; // Zero-width space so Chrome prints nothing in the header
    return () => {
      document.title = originalTitle;
    };
  }, []);

  const renderTableBody = () => (
    <tbody>
      {riesgos.map(r => (
        <tr key={r.id}>
          <td style={{borderBottom: '1px solid #eee', padding: '8px'}}>{r.folio || r.id}</td>
          <td style={{borderBottom: '1px solid #eee', padding: '8px'}}>{r.objetivo || '—'}</td>
          <td style={{borderBottom: '1px solid #eee', padding: '8px'}}>{r.riesgo}</td>
          <td style={{borderBottom: '1px solid #eee', padding: '8px'}}>
            {r.factores || '—'}
          </td>
          <td style={{borderBottom: '1px solid #eee', padding: '8px'}}>
            {(r.fuentes || []).map(f => (
              f.controles && f.controles.length > 0 
                ? f.controles.map(c => c.texto || c.control || c.descripcion).filter(Boolean).join('; ')
                : ''
            )).filter(Boolean).join(' | ') || '—'}
          </td>
          <td style={{borderBottom: '1px solid #eee', padding: '8px'}}>
            {(r.fuentes || []).map(f => (
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
      {riesgos.length === 0 && (
        <tr>
          <td colSpan="6" style={{textAlign: 'center', padding: '20px'}}>No hay riesgos institucionales validados en este ejercicio.</td>
        </tr>
      )}
    </tbody>
  );

  return (
    <>
      <style>
        {`
          @media print {
            @page { size: letter landscape; margin: 5mm; }
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
          <section className="report-sheet landscape">
            <div className="report-head">
              <div>
                <b>TRIBUNAL ELECTORAL DE LA CIUDAD DE MÉXICO</b>
                <span>INSTITUCIONAL</span>
                <strong>MATRIZ DE ADMINISTRACIÓN DE RIESGOS {ejercicio}</strong>
              </div>
            </div>
            
            <div className="table-responsive">
              <table className="mar-table">
                <thead>
                  <tr>
                    <th style={{width: '6%'}}>No.</th>
                    <th style={{width: '18%'}}>Objetivo Institucional</th>
                    <th style={{width: '26%'}}>Riesgo Institucional</th>
                    <th style={{width: '18%'}}>Factores de Riesgo (Fuentes)</th>
                    <th style={{width: '16%'}}>Controles (Fuentes)</th>
                    <th style={{width: '16%'}}>Indicadores (Fuentes)</th>
                  </tr>
                </thead>
                {renderTableBody()}
              </table>
            </div>
          </section>
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
            
            <section className="report-sheet landscape print-area" style={{position: 'relative', border: '1px solid #ccc', padding: '40px', minHeight: '8.5in', backgroundColor: 'white', margin: '0 auto'}}>
              <div className="report-head" style={{display: 'flex', alignItems: 'center', gap: '20px', borderBottom: '5px solid var(--blue)', paddingBottom: '10px', marginBottom: '20px'}}>
                <img src="/logo_tecdmx.png" alt="TECDMX" style={{width: '160px', height: 'auto', objectFit: 'contain'}} />
                <div style={{flex: 1, textAlign: 'center'}}>
                  <b style={{display: 'block', fontSize: '1.2rem', marginBottom: '5px'}}>TRIBUNAL ELECTORAL DE LA CIUDAD DE MÉXICO</b>
                  <span style={{display: 'block', fontSize: '1.1rem', marginBottom: '5px', fontWeight: 'bold', color: '#444'}}>INSTITUCIONAL</span>
                  <strong style={{display: 'block', fontSize: '1.2rem'}}>MATRIZ DE ADMINISTRACIÓN DE RIESGOS {ejercicio}</strong>
                </div>
              </div>
              
              <div className="table-responsive">
                <table className="mar-table">
                  <thead>
                    <tr>
                      <th style={{width: '6%'}}>No.</th>
                      <th style={{width: '18%'}}>Objetivo Institucional</th>
                      <th style={{width: '26%'}}>Riesgo Institucional</th>
                      <th style={{width: '18%'}}>Factores de Riesgo (Fuentes)</th>
                      <th style={{width: '16%'}}>Controles (Fuentes)</th>
                      <th style={{width: '16%'}}>Indicadores (Fuentes)</th>
                    </tr>
                  </thead>
                  {renderTableBody()}
                </table>
              </div>

              <div style={{marginTop: '80px', display: 'flex', justifyContent: 'space-around', textAlign: 'center', pageBreakInside: 'avoid', position: 'relative', zIndex: 20}}>
                <div>
                  <div style={{borderBottom: '1px solid black', width: '250px', height: '40px', margin: '0 auto 10px'}}></div>
                  <b>Magistrado Presidente</b>
                </div>
                <div>
                  <div style={{borderBottom: '1px solid black', width: '250px', height: '40px', margin: '0 auto 10px'}}></div>
                  <b>Contralor Interno</b>
                </div>
              </div>
              
              <p style={{textAlign: 'center', marginTop: '20px', fontSize: '13px', color: '#556b7c'}}>
                Fecha de emisión: {new Date().toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' })}
              </p>

            </section>
          </div>
        </div>
      )}
    </>
  );
}
