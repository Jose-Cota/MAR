import { useState, useEffect } from 'react';

export default function EvaluacionTrimestralModal({ open, onClose, onSave, q, initialData, riesgo }) {
  const [probabilidad, setProbabilidad] = useState(0);
  const [impacto, setImpacto] = useState(0);
  const [etiqueta, setEtiqueta] = useState('');
  const [evidenciaControl, setEvidenciaControl] = useState('');
  const [incidencia, setIncidencia] = useState('');
  const [accionMitigacion, setAccionMitigacion] = useState('');
  const [estatus, setEstatus] = useState('En seguimiento');
  const [responsable, setResponsable] = useState('');

  useEffect(() => {
    if (open) {
      setProbabilidad(initialData?.probabilidad ?? riesgo?.probabilidad ?? 0);
      setImpacto(initialData?.impacto ?? riesgo?.impacto ?? 0);
      setEtiqueta(initialData?.etiqueta ?? '');
      setEvidenciaControl(initialData?.evidencia_control ?? '');
      setIncidencia(initialData?.incidencia ?? '');
      setAccionMitigacion(initialData?.accion_mitigacion ?? '');
      setEstatus(initialData?.estatus ?? 'En seguimiento');
      setResponsable(initialData?.responsable ?? '');
    }
  }, [open, initialData, riesgo]);

  if (!open) return null;

  const handleSubmit = (e) => {
    e.preventDefault();
    onSave({
      trimestre: q,
      probabilidad: Number(probabilidad),
      impacto: Number(impacto),
      etiqueta,
      evidencia_control: evidenciaControl,
      incidencia,
      accion_mitigacion: accionMitigacion,
      estatus,
      responsable,
    });
  };

  return (
    <div style={{
      display: 'flex',
      position: 'fixed',
      top: 0, left: 0, right: 0, bottom: 0,
      backgroundColor: 'rgba(11, 58, 99, 0.4)',
      alignItems: 'center',
      justifyContent: 'center',
      zIndex: 1000,
      backdropFilter: 'blur(3px)'
    }}>
      <div style={{
        width: '600px',
        maxWidth: '90%',
        backgroundColor: '#fff',
        borderRadius: '12px',
        boxShadow: '0 8px 30px rgba(0,0,0,0.12)',
        display: 'flex',
        flexDirection: 'column',
        overflow: 'hidden'
      }}>
        <div style={{
          padding: '20px 24px',
          borderBottom: '1px solid #eef4f8',
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          backgroundColor: '#fafbfc'
        }}>
          <h2 style={{ margin: 0, fontSize: '1.25rem', color: '#0b3a63' }}>Evaluación Trimestral (T{q})</h2>
          <button type="button" onClick={onClose} style={{
            background: 'transparent',
            border: 'none',
            fontSize: '1.5rem',
            cursor: 'pointer',
            color: '#8898a9'
          }}>&times;</button>
        </div>
        
        <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', flex: 1 }}>
          <div style={{ padding: '24px', display: 'flex', flexDirection: 'column', gap: '16px', overflowY: 'auto', maxHeight: '70vh' }}>
            
            <div style={{ display: 'flex', gap: '16px' }}>
              <div style={{ flex: 1 }}>
                <label style={{ display: 'block', marginBottom: '6px', fontWeight: 'bold', color: '#142b45', fontSize: '0.9rem' }}>Probabilidad actual (0-10) <span style={{color: 'red'}}>*</span></label>
                <input 
                  type="number" 
                  className="input" 
                  min="0" 
                  max="10" 
                  value={probabilidad} 
                  onChange={e => setProbabilidad(e.target.value)} 
                  required
                  style={{ width: '100%', padding: '10px', borderRadius: '6px', border: '1px solid #d8e0e8' }}
                />
              </div>
              <div style={{ flex: 1 }}>
                <label style={{ display: 'block', marginBottom: '6px', fontWeight: 'bold', color: '#142b45', fontSize: '0.9rem' }}>Impacto actual (0-10) <span style={{color: 'red'}}>*</span></label>
                <input 
                  type="number" 
                  className="input" 
                  min="0" 
                  max="10" 
                  value={impacto} 
                  onChange={e => setImpacto(e.target.value)} 
                  required
                  style={{ width: '100%', padding: '10px', borderRadius: '6px', border: '1px solid #d8e0e8' }}
                />
              </div>
            </div>

            <div>
              <label style={{ display: 'block', marginBottom: '6px', fontWeight: 'bold', color: '#142b45', fontSize: '0.9rem' }}>Etiqueta (opcional)</label>
              <input 
                type="text" 
                className="input" 
                value={etiqueta} 
                onChange={e => setEtiqueta(e.target.value)}
                placeholder="Ej. Integración inicial 2026"
                style={{ width: '100%', padding: '10px', borderRadius: '6px', border: '1px solid #d8e0e8' }}
              />
            </div>

            <div>
              <label style={{ display: 'block', marginBottom: '6px', fontWeight: 'bold', color: '#142b45', fontSize: '0.9rem' }}>Control/evidencia revisada (opcional)</label>
              <textarea 
                className="input" 
                value={evidenciaControl} 
                onChange={e => setEvidenciaControl(e.target.value)}
                rows="2"
                style={{ width: '100%', padding: '10px', borderRadius: '6px', border: '1px solid #d8e0e8', resize: 'vertical' }}
              ></textarea>
            </div>

            <div>
              <label style={{ display: 'block', marginBottom: '6px', fontWeight: 'bold', color: '#142b45', fontSize: '0.9rem' }}>Incidencia o materialización (opcional)</label>
              <textarea 
                className="input" 
                value={incidencia} 
                onChange={e => setIncidencia(e.target.value)}
                rows="2"
                style={{ width: '100%', padding: '10px', borderRadius: '6px', border: '1px solid #d8e0e8', resize: 'vertical' }}
              ></textarea>
            </div>

            <div>
              <label style={{ display: 'block', marginBottom: '6px', fontWeight: 'bold', color: '#142b45', fontSize: '0.9rem' }}>Acción de mitigación / seguimiento (opcional)</label>
              <textarea 
                className="input" 
                value={accionMitigacion} 
                onChange={e => setAccionMitigacion(e.target.value)}
                rows="2"
                style={{ width: '100%', padding: '10px', borderRadius: '6px', border: '1px solid #d8e0e8', resize: 'vertical' }}
              ></textarea>
            </div>

            <div style={{ display: 'flex', gap: '16px' }}>
              <div style={{ flex: 1 }}>
                <label style={{ display: 'block', marginBottom: '6px', fontWeight: 'bold', color: '#142b45', fontSize: '0.9rem' }}>Estatus del seguimiento <span style={{color: 'red'}}>*</span></label>
                <input 
                  type="text" 
                  className="input" 
                  value={estatus} 
                  onChange={e => setEstatus(e.target.value)}
                  required
                  style={{ width: '100%', padding: '10px', borderRadius: '6px', border: '1px solid #d8e0e8' }}
                />
              </div>
              <div style={{ flex: 1 }}>
                <label style={{ display: 'block', marginBottom: '6px', fontWeight: 'bold', color: '#142b45', fontSize: '0.9rem' }}>Responsable <span style={{color: 'red'}}>*</span></label>
                <input 
                  type="text" 
                  className="input" 
                  value={responsable} 
                  onChange={e => setResponsable(e.target.value)}
                  required
                  style={{ width: '100%', padding: '10px', borderRadius: '6px', border: '1px solid #d8e0e8' }}
                />
              </div>
            </div>

          </div>
          <div style={{
            padding: '16px 24px',
            borderTop: '1px solid #eef4f8',
            display: 'flex',
            justifyContent: 'flex-end',
            gap: '12px',
            backgroundColor: '#fafbfc'
          }}>
            <button type="button" onClick={onClose} style={{
              padding: '8px 16px', borderRadius: '6px', border: '1px solid #d8e0e8', background: '#fff', cursor: 'pointer', fontWeight: 'bold', color: '#5b6773'
            }}>Cancelar</button>
            <button type="submit" style={{
              padding: '8px 16px', borderRadius: '6px', border: 'none', background: '#0b3a63', color: '#fff', cursor: 'pointer', fontWeight: 'bold'
            }}>Guardar Evaluación</button>
          </div>
        </form>
      </div>
    </div>
  );
}
