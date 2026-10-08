import { useState, useEffect } from 'react';

export default function SeguimientoTrimestralModal({ isOpen, onClose, onSave, activeRiesgo, activeQuarter, initialData, isReadOnly }) {
  const [formData, setFormData] = useState({
    m1_n: '', m1_d: '',
    m2_n: '', m2_d: '',
    m3_n: '', m3_d: '',
    notas: ''
  });

  useEffect(() => {
    if (initialData) {
      setFormData({
        m1_n: initialData.m1_n ?? '',
        m1_d: initialData.m1_d ?? '',
        m2_n: initialData.m2_n ?? '',
        m2_d: initialData.m2_d ?? '',
        m3_n: initialData.m3_n ?? '',
        m3_d: initialData.m3_d ?? '',
        notas: initialData.notas || ''
      });
    } else {
      setFormData({ m1_n: '', m1_d: '', m2_n: '', m2_d: '', m3_n: '', m3_d: '', notas: '' });
    }
  }, [initialData, isOpen]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSave = () => {
    onSave({
      trimestre: activeQuarter,
      m1_n: formData.m1_n === '' ? null : Number(formData.m1_n),
      m1_d: formData.m1_d === '' ? null : Number(formData.m1_d),
      m2_n: formData.m2_n === '' ? null : Number(formData.m2_n),
      m2_d: formData.m2_d === '' ? null : Number(formData.m2_d),
      m3_n: formData.m3_n === '' ? null : Number(formData.m3_n),
      m3_d: formData.m3_d === '' ? null : Number(formData.m3_d),
      notas: formData.notas
    });
  };

  if (!isOpen) return null;

  const MESES = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
  const baseIndex = (activeQuarter - 1) * 3;
  const meses = [MESES[baseIndex], MESES[baseIndex + 1], MESES[baseIndex + 2]];

  return (
    <div style={{
      position: 'fixed', inset: 0, background: 'rgba(15, 23, 42, 0.4)',
      zIndex: 99999, display: 'flex', alignItems: 'center', justifyContent: 'center',
      backdropFilter: 'blur(8px)', padding: '20px', animation: 'fadeIn 0.2s ease-out'
    }}>
      <style>{`
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .premium-input {
          width: 100%; padding: 8px 12px; fontSize: 0.95rem; border: 1px solid #cbd5e1; border-radius: 6px;
          background-color: #ffffff; color: #0f172a; transition: all 0.2s ease; outline: none;
        }
        .premium-input:focus {
          border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        .premium-btn {
          padding: 8px 20px; border-radius: 6px; font-weight: 600; font-size: 0.95rem; cursor: pointer; transition: all 0.2s ease; border: none; outline: none;
        }
        .premium-btn-cancel {
          background-color: #f1f5f9; color: #475569;
        }
        .premium-btn-cancel:hover {
          background-color: #e2e8f0; color: #1e293b;
        }
        .premium-btn-save {
          background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2), 0 2px 4px -1px rgba(37, 99, 235, 0.1);
        }
        .premium-btn-save:hover {
          background: linear-gradient(135deg, #1d4ed8, #1e40af); transform: translateY(-1px); box-shadow: 0 6px 8px -1px rgba(37, 99, 235, 0.3), 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        }
      `}</style>

      <div style={{
        background: '#ffffff', borderRadius: '16px', width: '100%', maxWidth: '700px',
        boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(15, 23, 42, 0.05)',
        display: 'flex', flexDirection: 'column', maxHeight: '90vh', overflow: 'hidden',
        animation: 'slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1)'
      }}>
        {/* Header */}
        <div style={{ 
          padding: '16px 24px', 
          background: 'linear-gradient(to right, #f8fafc, #f1f5f9)', 
          borderBottom: '1px solid #e2e8f0', 
          display: 'flex', justifyContent: 'space-between', alignItems: 'center' 
        }}>
          <h2 style={{ margin: 0, fontSize: '1.4rem', color: '#0f172a', fontWeight: '700', letterSpacing: '-0.025em', display: 'flex', alignItems: 'center', gap: '10px' }}>
            <span style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: '36px', height: '36px', borderRadius: '10px', background: '#eff6ff', color: '#3b82f6' }}>
              <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </span>
            Editar Trimestre {activeQuarter}
          </h2>
          <button onClick={onClose} style={{ background: '#e2e8f0', border: 'none', width: '32px', height: '32px', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '1.2rem', color: '#475569', cursor: 'pointer', transition: 'all 0.2s ease' }} onMouseOver={e => e.target.style.backgroundColor = '#cbd5e1'} onMouseOut={e => e.target.style.backgroundColor = '#e2e8f0'}>&times;</button>
        </div>

        {/* Body */}
        <div style={{ padding: '20px', overflowY: 'auto', backgroundColor: '#fcfcfc' }}>
          <div style={{ marginBottom: '12px', padding: '12px 16px', backgroundColor: '#f0f9ff', borderLeft: '4px solid #3b82f6', borderRadius: '0 8px 8px 0', fontSize: '0.9rem', color: '#0369a1', lineHeight: '1.4' }}>
            <strong style={{ color: '#075985', display: 'block', marginBottom: '2px', fontSize: '0.8rem', textTransform: 'uppercase', letterSpacing: '0.05em' }}>Riesgo seleccionado</strong> 
            {activeRiesgo?.riesgo}
          </div>

          <div style={{ marginBottom: '12px', padding: '12px 16px', backgroundColor: '#f0fdf4', borderLeft: '4px solid #22c55e', borderRadius: '0 8px 8px 0', fontSize: '0.9rem', color: '#166534', lineHeight: '1.4' }}>
            <strong style={{ display: 'block', marginBottom: '2px', fontSize: '0.8rem', textTransform: 'uppercase', letterSpacing: '0.05em' }}>Controles</strong> 
            {activeRiesgo?.controles?.length > 0 ? activeRiesgo.controles.map(c => c.texto || c.control || c.descripcion).filter(Boolean).join('; ') : '—'}
          </div>

          <div style={{ marginBottom: '20px', padding: '12px 16px', backgroundColor: '#fff7ed', borderLeft: '4px solid #f97316', borderRadius: '0 8px 8px 0', fontSize: '0.9rem', color: '#9a3412', lineHeight: '1.4' }}>
            <strong style={{ display: 'block', marginBottom: '2px', fontSize: '0.8rem', textTransform: 'uppercase', letterSpacing: '0.05em' }}>Indicadores / Fórmula</strong> 
            {(activeRiesgo?.indicadores || []).map(i => {
                let f = i.formula;
                if (!f || f === 'Resultado = (N / D) × 100') {
                  f = (i.numerador && i.denominador) ? `Resultado = (${i.numerador} / ${i.denominador}) × 100` : i.nombre || '';
                }
                return f;
            }).filter(Boolean).join('; ') || '—'}
          </div>

          <div style={{ display: 'flex', gap: '16px', marginBottom: '20px' }}>
            {[1, 2, 3].map((num, i) => (
              <div key={num} style={{ flex: 1, backgroundColor: '#ffffff', border: '1px solid #e2e8f0', padding: '16px', borderRadius: '8px', boxShadow: '0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03)', transition: 'transform 0.2s ease', cursor: 'default' }} onMouseOver={e => e.currentTarget.style.transform = 'translateY(-2px)'} onMouseOut={e => e.currentTarget.style.transform = 'translateY(0)'}>
                <div style={{ textAlign: 'center', fontWeight: '700', marginBottom: '12px', color: '#334155', fontSize: '1.05rem', letterSpacing: '0.05em', textTransform: 'uppercase', borderBottom: '2px solid #f1f5f9', paddingBottom: '8px' }}>
                  {meses[i]}
                </div>
                <div style={{ marginBottom: '12px' }}>
                  <label style={{ display: 'block', fontSize: '0.85rem', fontWeight: '600', color: '#64748b', marginBottom: '6px' }}>Numerador (N)</label>
                  <input
                    type="number"
                    className="premium-input"
                    name={`m${num}_n`}
                    value={formData[`m${num}_n`]}
                    onChange={handleChange}
                    readOnly={isReadOnly}
                    placeholder="0"
                  />
                </div>
                <div>
                  <label style={{ display: 'block', fontSize: '0.85rem', fontWeight: '600', color: '#64748b', marginBottom: '6px' }}>Denominador (D)</label>
                  <input
                    type="number"
                    className="premium-input"
                    name={`m${num}_d`}
                    value={formData[`m${num}_d`]}
                    onChange={handleChange}
                    readOnly={isReadOnly}
                    placeholder="0"
                  />
                </div>
              </div>
            ))}
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '0.95rem', fontWeight: '600', color: '#1e293b', marginBottom: '8px' }}>Notas u observaciones</label>
            <textarea
              className="premium-input"
              style={{ minHeight: '100px', resize: 'vertical' }}
              name="notas"
              value={formData.notas}
              onChange={handleChange}
              readOnly={isReadOnly}
              rows="3"
              placeholder="Agrega cualquier observación relevante sobre el seguimiento de este trimestre..."
            />
          </div>
        </div>

        {/* Footer */}
        <div style={{ padding: '20px 32px', borderTop: '1px solid #e2e8f0', display: 'flex', justifyContent: 'flex-end', gap: '16px', backgroundColor: '#f8fafc' }}>
          <button className="premium-btn premium-btn-cancel" onClick={onClose}>Cancelar</button>
          {!isReadOnly && <button className="premium-btn premium-btn-save" onClick={handleSave}>Guardar Seguimiento</button>}
        </div>
      </div>
    </div>
  );
}
