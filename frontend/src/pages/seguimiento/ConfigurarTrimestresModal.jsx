import { useState, useEffect } from 'react';
import axios from '../../utils/axios';

export default function ConfigurarTrimestresModal({ isOpen, onClose, urId, areas, ejercicioId }) {
  const [loading, setLoading] = useState(false);
  const [trimestres, setTrimestres] = useState({
    t1_abierto: false,
    t2_abierto: false,
    t3_abierto: false,
    t4_abierto: false
  });

  const [selectedUrId, setSelectedUrId] = useState(urId || 'todas');

  useEffect(() => {
    if (isOpen) {
      setSelectedUrId(urId || 'todas');
    }
  }, [urId, isOpen]);

  useEffect(() => {
    if (isOpen && selectedUrId && ejercicioId) {
      if (selectedUrId === 'todas') {
        setTrimestres({ t1_abierto: false, t2_abierto: false, t3_abierto: false, t4_abierto: false });
        return;
      }
      setLoading(true);
      axios.get(`/configuracion-trimestres/${selectedUrId}/${ejercicioId}`)
        .then(res => {
          const isTrue = (val) => val === 1 || val === true || val === '1';
          setTrimestres({
            t1_abierto: isTrue(res.data.t1_abierto),
            t2_abierto: isTrue(res.data.t2_abierto),
            t3_abierto: isTrue(res.data.t3_abierto),
            t4_abierto: isTrue(res.data.t4_abierto)
          });
        })
        .catch(err => console.error(err))
        .finally(() => setLoading(false));
    }
  }, [isOpen, selectedUrId, ejercicioId]);

  const handleChange = (t) => {
    setTrimestres(prev => ({
      ...prev,
      [t]: !prev[t]
    }));
  };

  const handleSave = () => {
    setLoading(true);
    axios.put(`/configuracion-trimestres/${selectedUrId}/${ejercicioId}`, trimestres)
      .then(() => {
        onClose();
      })
      .catch(err => {
        console.error(err);
        alert('Error al guardar configuración');
      })
      .finally(() => setLoading(false));
  };

  if (!isOpen) return null;

  return (
    <div style={{
      position: 'fixed', inset: 0, background: 'rgba(15, 23, 42, 0.4)',
      zIndex: 99999, display: 'flex', alignItems: 'center', justifyContent: 'center',
      backdropFilter: 'blur(8px)', padding: '20px', animation: 'fadeIn 0.2s ease-out'
    }}>
      <style>{`
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .cfg-checkbox-container {
          display: flex; align-items: center; justify-content: center; gap: 8px;
          background-color: #ffffff; padding: 12px; border-radius: 8px;
          border: 1px solid #e2e8f0; cursor: pointer; transition: all 0.2s ease;
        }
        .cfg-checkbox-container:hover {
          border-color: #3b82f6; background-color: #f8fafc;
        }
        .cfg-checkbox {
          width: 18px; height: 18px; cursor: pointer;
        }
      `}</style>

      <div style={{
        background: '#ffffff', borderRadius: '16px', width: '100%', maxWidth: '500px',
        boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(15, 23, 42, 0.05)',
        display: 'flex', flexDirection: 'column', overflow: 'hidden',
        animation: 'slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1)'
      }}>
        {/* Header */}
        <div style={{ 
          padding: '20px 24px', 
          background: 'linear-gradient(to right, #f8fafc, #f1f5f9)', 
          borderBottom: '1px solid #e2e8f0', 
          display: 'flex', justifyContent: 'space-between', alignItems: 'center' 
        }}>
          <h2 style={{ margin: 0, fontSize: '1.2rem', color: '#0f172a', fontWeight: '700', display: 'flex', alignItems: 'center', gap: '8px' }}>
            <span style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: '32px', height: '32px', borderRadius: '8px', background: '#eff6ff', color: '#3b82f6' }}>
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </span>
            Configurar trimestres
          </h2>
          <button onClick={onClose} style={{ background: '#e2e8f0', border: 'none', width: '28px', height: '28px', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#475569', cursor: 'pointer' }}>&times;</button>
        </div>

        {/* Body */}
        <div style={{ padding: '24px', backgroundColor: '#fcfcfc' }}>
          {loading ? (
            <div style={{ textAlign: 'center', padding: '20px', color: '#64748b' }}>Cargando...</div>
          ) : (
            <>
              <div style={{ marginBottom: '24px', padding: '12px 16px', backgroundColor: '#f0f9ff', borderLeft: '4px solid #3b82f6', borderRadius: '0 8px 8px 0', fontSize: '0.9rem', color: '#0369a1' }}>
                <strong style={{ display: 'block', marginBottom: '8px', fontSize: '0.8rem', textTransform: 'uppercase', color: '#075985' }}>Unidad Responsable a configurar</strong> 
                <select 
                  style={{ width: '100%', padding: '8px', borderRadius: '4px', border: '1px solid #bae6fd', backgroundColor: '#ffffff', color: '#0f172a', fontSize: '0.9rem' }}
                  value={selectedUrId}
                  onChange={(e) => setSelectedUrId(e.target.value)}
                >
                  <option value="todas">-- Todas las Unidades Responsables --</option>
                  {areas && areas.map((a, idx) => (
                    <option key={a.unidad_responsable_gasto_id || a.id_unidad || a.id || idx} value={a.unidad_responsable_gasto_id || a.id_unidad || a.id}>
                      {a.nombre || a.denominacion}
                    </option>
                  ))}
                </select>
              </div>

              <div style={{ marginBottom: '12px', fontSize: '0.9rem', fontWeight: '600', color: '#334155' }}>
                Configuración por trimestre:
              </div>
              
              <div style={{ display: 'flex', gap: '12px' }}>
                {[1, 2, 3, 4].map(num => (
                  <label key={num} className="cfg-checkbox-container" style={{ flex: 1, flexDirection: 'column', gap: '8px', padding: '16px 8px' }}>
                    <input
                      type="checkbox"
                      className="cfg-checkbox"
                      checked={trimestres[`t${num}_abierto`]}
                      onChange={() => handleChange(`t${num}_abierto`)}
                    />
                    <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
                      <span style={{ fontWeight: '800', color: '#1e293b', fontSize: '1.1rem' }}>T{num}</span>
                      <span style={{ fontSize: '0.8rem', color: trimestres[`t${num}_abierto`] ? '#059669' : '#64748b', marginTop: '4px', fontWeight: '700', textTransform: 'uppercase', letterSpacing: '0.5px' }}>
                        {trimestres[`t${num}_abierto`] ? 'Abierto' : 'Cerrado'}
                      </span>
                    </div>
                  </label>
                ))}
              </div>
            </>
          )}
        </div>

        {/* Footer */}
        <div style={{ padding: '16px 24px', borderTop: '1px solid #e2e8f0', display: 'flex', justifyContent: 'flex-end', gap: '12px', backgroundColor: '#f8fafc' }}>
          <button onClick={onClose} style={{ padding: '8px 16px', borderRadius: '6px', background: '#f1f5f9', border: 'none', cursor: 'pointer', fontWeight: '600', color: '#475569' }}>Cancelar</button>
          <button onClick={handleSave} disabled={loading} style={{ padding: '8px 16px', borderRadius: '6px', background: 'linear-gradient(135deg, #2563eb, #1d4ed8)', border: 'none', cursor: 'pointer', fontWeight: '600', color: 'white' }}>
            {loading ? 'Guardando...' : 'Guardar Configuración'}
          </button>
        </div>
      </div>
    </div>
  );
}
