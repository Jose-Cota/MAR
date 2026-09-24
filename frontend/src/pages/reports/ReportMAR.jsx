import { useState, useEffect } from 'react';
import { useParams, useNavigate, useLocation } from 'react-router-dom';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';
import CloseIcon from '@mui/icons-material/Close';
import PrintIcon from '@mui/icons-material/Print';
import logoTecdmx from '../../assets/logo_tecdmx.png';
import VisibilityIcon from '@mui/icons-material/Visibility';

export default function ReportMAR() {
  const { areaId } = useParams();
  const navigate = useNavigate();
  const { state } = useLocation();
  const [riesgos, setRiesgos] = useState([]);
  const [area, setArea] = useState(null);
  const [showModal, setShowModal] = useState(false);
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
      setArea(areasList.find(a => (a.unidad_responsable_gasto_id || a.id_unidad || a.id) == areaId));
    } catch (e) {
      console.error(e);
    }
  };

  const isAllValidated = riesgos.length > 0 && riesgos.every(r => r.status === 'Validado');
  const isCaptura = riesgos.some(r => !r.status || r.status.toLowerCase().includes('captura'));
  
  const lastValidationDate = isAllValidated 
    ? new Date(Math.max(...riesgos.map(r => new Date(r.updated_at || r.created_at || Date.now())))).toLocaleDateString('es-MX') 
    : '';

  const renderTableBody = () => (
    <tbody>
      {riesgos.map(r => (
        <tr key={r.id}>
          <td style={{borderBottom: '1px solid #eee', padding: '8px'}}>{r.local_id || r.id}</td>
          <td style={{borderBottom: '1px solid #eee', padding: '8px'}}>{r.objetivo}</td>
          <td style={{borderBottom: '1px solid #eee', padding: '8px'}}>{r.riesgo}</td>
          <td style={{borderBottom: '1px solid #eee', padding: '8px'}}>
            {[r.factores_internos, r.factores_externos].filter(Boolean).length > 0 
              ? [r.factores_internos, r.factores_externos].filter(Boolean).join('; ') 
              : (r.factores || '')}
          </td>
          <td style={{borderBottom: '1px solid #eee', padding: '8px'}}>
            {r.controles && r.controles.length > 0
              ? r.controles.map(c => c.texto || c.control || c.descripcion).filter(Boolean).join('; ')
              : ''}
          </td>
          <td style={{borderBottom: '1px solid #eee', padding: '8px'}}>
            {(r.indicadores || []).map((i, idx) => {
              const formulaText = i.formula || (i.numerador && i.denominador ? `Resultado = (${i.numerador} / ${i.denominador}) × 100` : '');
              return (
                <div key={idx} style={{ marginBottom: '8px' }}>
                  {i.nombre && <strong style={{ display: 'block', marginBottom: '2px' }}>{i.nombre}</strong>}
                  {formulaText && <span>{formulaText}</span>}
                </div>
              );
            })}
            {(!r.indicadores || r.indicadores.length === 0) && r.indicador && (
              <div style={{ marginBottom: '8px' }}>
                {r.indicador.nombre && <strong style={{ display: 'block', marginBottom: '2px' }}>{r.indicador.nombre}</strong>}
                <span>{r.indicador.formula || (r.indicador.numerador && r.indicador.denominador ? `Resultado = (${r.indicador.numerador} / ${r.indicador.denominador}) × 100` : '')}</span>
              </div>
            )}
          </td>
        </tr>
      ))}
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

      <div className={`main-content ${showModal ? 'no-print' : ''}`}>
        <div className="page-head no-print">
          <div>
            <h1>MAR imprimible</h1>
            <p>{area?.nombre || area?.denominacion} · {ejercicio}</p>
          </div>
          <div>
            {state?.fromMapMAR ? (
              <button className="btn" onClick={() => navigate('/mapmar', { state: { areaId: state.areaId } })}>Volver a MAPA y MAR</button>
            ) : (
              <button className="btn" onClick={() => navigate('/reportes')}>Volver</button>
            )}{' '}
            <button className="btn primary" onClick={() => setShowModal(true)}>Imprimir / PDF</button>
          </div>
        </div>
        
        {/* Vista normal en pantalla si no está el modal abierto */}
        {!showModal && (
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
              {renderTableBody()}
            </table>
          </section>
        )}
      </div>

      {showModal && (
        <div className="modal-overlay" style={{position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, background: 'rgba(0,0,0,0.6)', zIndex: 9999, padding: '40px 20px', overflowY: 'auto'}}>
          <div className="modal-content" style={{background: 'white', maxWidth: '1200px', margin: '0 auto', width: '100%', borderRadius: '8px', padding: '30px', position: 'relative'}}>
            <div className="no-print" style={{display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px'}}>
              <h2 style={{margin: 0, display: 'flex', alignItems: 'center', gap: '8px', color: '#17324d'}}>
                <VisibilityIcon /> Vista preliminar: MAR Imprimible
              </h2>
              <div style={{display: 'flex', gap: '10px'}}>
                <button className="icon-btn" onClick={() => window.print()} title="Confirmar Impresión" style={{ padding: '8px', fontSize: '20px' }}>
                  <PrintIcon fontSize="inherit" />
                </button>
                <button className="icon-btn" onClick={() => setShowModal(false)} title="Cerrar vista preliminar" style={{ padding: '8px', fontSize: '20px', background: '#ffebee', color: '#c62828' }}>
                  <CloseIcon fontSize="inherit" />
                </button>
              </div>
            </div>
            
            <section className="report-sheet landscape print-area" style={{position: 'relative', border: '1px solid #ccc', padding: '40px', minHeight: '8.5in', backgroundColor: 'white', margin: '0 auto'}}>
              {isCaptura && (
                <div style={{
                  position: 'absolute', top: '40%', left: '50%', transform: 'translate(-50%, -50%) rotate(-45deg)',
                  fontSize: '8rem', color: 'rgba(255, 0, 0, 0.12)', zIndex: 10, pointerEvents: 'none', whiteSpace: 'nowrap', fontWeight: 'bold'
                }}>
                  EN CAPTURA
                </div>
              )}
              
              <div className="report-head" style={{display: 'flex', alignItems: 'center', gap: '20px', borderBottom: '5px solid var(--blue)', paddingBottom: '10px', marginBottom: '20px'}}>
                <img src={logoTecdmx} alt="TECDMX" style={{width: '160px', height: 'auto', objectFit: 'contain'}} />
                <div style={{flex: 1, textAlign: 'center'}}>
                  <b style={{display: 'block', fontSize: '1.2rem', marginBottom: '5px'}}>TRIBUNAL ELECTORAL DE LA CIUDAD DE MÉXICO</b>
                  <span style={{display: 'block', fontSize: '1.1rem', marginBottom: '5px', fontWeight: 'bold', color: '#444'}}>{area?.nombre || area?.denominacion || area?.area || ''}</span>
                  <strong style={{display: 'block', fontSize: '1.2rem'}}>MATRIZ DE ADMINISTRACIÓN DE RIESGOS {ejercicio}</strong>
                </div>
              </div>
              
              <table style={{width: '100%', borderCollapse: 'collapse', marginTop: '20px', position: 'relative', zIndex: 20}} className="mar-table">
                <thead>
                  <tr>
                    <th style={{borderBottom: '2px solid #ccc', padding: '8px', textAlign: 'left', width: '5%'}}>No.</th>
                    <th style={{borderBottom: '2px solid #ccc', padding: '8px', textAlign: 'left', width: '20%'}}>OBJETIVO</th>
                    <th style={{borderBottom: '2px solid #ccc', padding: '8px', textAlign: 'left', width: '20%'}}>RIESGO</th>
                    <th style={{borderBottom: '2px solid #ccc', padding: '8px', textAlign: 'left', width: '20%'}}>FACTORES DE RIESGO</th>
                    <th style={{borderBottom: '2px solid #ccc', padding: '8px', textAlign: 'left', width: '20%'}}>CONTROLES</th>
                    <th style={{borderBottom: '2px solid #ccc', padding: '8px', textAlign: 'left', width: '15%'}}>INDICADORES</th>
                  </tr>
                </thead>
                {renderTableBody()}
              </table>

              {isAllValidated && (
                <div style={{marginTop: '80px', display: 'flex', justifyContent: 'space-around', textAlign: 'center', pageBreakInside: 'avoid', position: 'relative', zIndex: 20}}>
                  <div>
                    <div style={{borderBottom: '1px solid black', width: '250px', height: '40px', margin: '0 auto 10px'}}></div>
                    <b>Titular de la Unidad Responsable</b>
                  </div>
                  <div>
                    <div style={{borderBottom: '1px solid black', width: '250px', height: '40px', margin: '0 auto 10px'}}></div>
                    <b>Responsable Operativo</b>
                  </div>
                </div>
              )}
              {isAllValidated && lastValidationDate && (
                <p style={{textAlign: 'center', marginTop: '20px', fontSize: '13px', color: '#556b7c'}}>
                  Fecha de validación: {lastValidationDate}
                </p>
              )}
            </section>
          </div>
        </div>
      )}
    </>
  );
}
