import { useState, useEffect } from 'react';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';
import useAuth from '../../hooks/useAuth';
import { useNavigate, useLocation } from 'react-router-dom';
import CheckCircleOutlineIcon from '@mui/icons-material/CheckCircleOutline';
import ErrorOutlineIcon from '@mui/icons-material/ErrorOutline';
import HelpOutlineIcon from '@mui/icons-material/HelpOutline';
import WarningAmberIcon from '@mui/icons-material/WarningAmber';

// ── Componente de modal reutilizable ────────────────────────────────────────
function Dialog({ open, type = 'confirm', title, message, onConfirm, onCancel, confirmLabel = 'Confirmar', cancelLabel = 'Cancelar' }) {
  if (!open) return null;

  const palette = {
    confirm: { bg: '#1a73e8', icon: <HelpOutlineIcon sx={{ fontSize: 48, color: '#1a73e8' }} />, bar: '#1a73e8' },
    success: { bg: '#34a853', icon: <CheckCircleOutlineIcon sx={{ fontSize: 48, color: '#34a853' }} />, bar: '#34a853' },
    warning: { bg: '#f9ab00', icon: <WarningAmberIcon sx={{ fontSize: 48, color: '#f9ab00' }} />, bar: '#f9ab00' },
    error:   { bg: '#d93025', icon: <ErrorOutlineIcon sx={{ fontSize: 48, color: '#d93025' }} />, bar: '#d93025' },
  };
  const p = palette[type] || palette.confirm;
  const isInfo = type === 'success' || type === 'error';

  return (
    <div style={{
      position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.5)',
      zIndex: 99999, display: 'flex', alignItems: 'center', justifyContent: 'center',
      backdropFilter: 'blur(3px)', padding: '20px'
    }}>
      <div style={{
        background: '#fff', borderRadius: '16px', maxWidth: '440px', width: '100%',
        boxShadow: '0 24px 64px rgba(0,0,0,0.22)', overflow: 'hidden',
        animation: 'dialogIn 0.18s ease'
      }}>
        <style>{`@keyframes dialogIn { from { opacity:0; transform:scale(0.92); } to { opacity:1; transform:scale(1); } }`}</style>
        {/* Barra de color superior */}
        <div style={{ height: '5px', background: p.bar }} />

        <div style={{ padding: '32px 28px 24px', textAlign: 'center' }}>
          <div style={{ marginBottom: '16px' }}>{p.icon}</div>
          <h3 style={{ margin: '0 0 10px', fontSize: '1.2rem', color: '#17324d', fontWeight: 700 }}>{title}</h3>
          <p style={{ margin: '0 0 28px', fontSize: '0.97rem', color: '#5b6773', lineHeight: 1.5 }}>{message}</p>

          <div style={{ display: 'flex', gap: '12px', justifyContent: 'center' }}>
            {!isInfo && (
              <button onClick={onCancel} style={{
                padding: '10px 24px', borderRadius: '8px', border: '1.5px solid #d8e0e8',
                background: '#f4f6f8', color: '#5b6773', fontWeight: 600, cursor: 'pointer',
                fontSize: '0.95rem', transition: 'background 0.15s'
              }}>{cancelLabel}</button>
            )}
            <button onClick={onConfirm} style={{
              padding: '10px 28px', borderRadius: '8px', border: 'none',
              background: p.bg, color: '#fff', fontWeight: 700, cursor: 'pointer',
              fontSize: '0.95rem', boxShadow: `0 4px 14px ${p.bg}55`, transition: 'opacity 0.15s'
            }}>{isInfo ? 'Aceptar' : confirmLabel}</button>
          </div>
        </div>
      </div>
    </div>
  );
}

// ── Cuadrante helper ─────────────────────────────────────────────────────────
const cuadrante = (p, i) => {
  p = Number(p); i = Number(i);
  if (p > 5 && i > 5) return 'QI';
  if (p > 5 && i <= 5) return 'QII';
  if (p <= 5 && i <= 5) return 'QIII';
  return 'QIV';
};

// ── Página principal ─────────────────────────────────────────────────────────
export default function MapMarPage() {
  const [riesgos, setRiesgos] = useState([]);
  const [areas, setAreas] = useState([]);
  const [areaId, setAreaId] = useState('');
  const [loading, setLoading] = useState(true);

  // Estado del dialog
  const [dialog, setDialog] = useState({ open: false, type: 'confirm', title: '', message: '', onConfirm: null });

  const ejercicio = useGlobalStore((s) => s.ejercicio);
  const { hasRole } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();

  const isSuperAdmin = hasRole('Super Administrador') || hasRole('superadmin') || hasRole('Admin');
  const canValidate = hasRole('Validador');

  // Helper para mostrar diálogos
  const showDialog = (config) => setDialog({ open: true, ...config });
  const closeDialog = () => setDialog(d => ({ ...d, open: false }));

  useEffect(() => {
    axios.get('/unidades-responsables').then(res => {
      const data = res.data.data || res.data;
      setAreas(data);
      if (data.length > 0) {
        if (location.state?.areaId) {
          setAreaId(location.state.areaId);
        } else {
          const firstId = String(data[0].unidad_responsable_gasto_id || data[0].id_unidad || data[0].id);
          setAreaId(firstId);
        }
      }
    });
  }, []);

  useEffect(() => {
    fetchRiesgos();
  }, [areaId, ejercicio]);

  const fetchRiesgos = async () => {
    if (!areaId) return;
    setLoading(true);
    try {
      const res = await axios.get(`/riesgos?ejercicio_id=${ejercicio}&area_id=${areaId}`);
      setRiesgos(res.data.data || res.data || []);
    } finally {
      setLoading(false);
    }
  };

  // ── Validar ───────────────────────────────────────────────────────────────
  const handleBatchValidate = () => {
    if (!canValidate) {
      showDialog({ type: 'error', title: 'Sin permiso', message: 'Tu perfil no tiene permiso de validación para esta área.', onConfirm: closeDialog });
      return;
    }
    const pending = riesgos.filter(r => r.status !== 'Validado');
    if (pending.length === 0) {
      showDialog({ type: 'success', title: 'Ya validados', message: 'Todos los datos del área ya están validados.', onConfirm: closeDialog });
      return;
    }
    const notCaptura = pending.filter(r => r.status !== 'Captura');
    if (notCaptura.length > 0) {
      showDialog({ type: 'warning', title: 'Estatus incorrecto', message: 'Antes de la validación integral, todos los riesgos deben encontrarse en estatus de "Captura".', onConfirm: closeDialog });
      return;
    }

    // Confirmación antes de ejecutar
    showDialog({
      type: 'confirm',
      title: 'Confirmar validación',
      message: `Se validarán ${pending.length} riesgo${pending.length > 1 ? 's' : ''} del área. Esta acción cambiará su estatus a "Validado". ¿Deseas continuar?`,
      confirmLabel: 'Sí, Validar',
      cancelLabel: 'Cancelar',
      onCancel: closeDialog,
      onConfirm: async () => {
        closeDialog();
        try {
          const riskIds = pending.map(r => r.id);
          await axios.post('/riesgos/batch-validate', { area_id: areaId, ejercicio_id: ejercicio, risk_ids: riskIds });
          fetchRiesgos();
          showDialog({ type: 'success', title: '¡Validación completada!', message: `${pending.length} riesgo${pending.length > 1 ? 's han' : ' ha'} sido validado${pending.length > 1 ? 's' : ''} correctamente.`, onConfirm: closeDialog });
        } catch (err) {
          showDialog({ type: 'error', title: 'Error en validación', message: err.response?.data?.message || err.message, onConfirm: closeDialog });
        }
      }
    });
  };

  // ── Des-validar ───────────────────────────────────────────────────────────
  const handleBatchUnvalidate = () => {
    if (!isSuperAdmin) {
      showDialog({ type: 'error', title: 'Sin permiso', message: 'Tu perfil no tiene permiso para des-validar.', onConfirm: closeDialog });
      return;
    }
    const validated = riesgos.filter(r => r.status === 'Validado');
    if (validated.length === 0) {
      showDialog({ type: 'warning', title: 'Sin riesgos validados', message: 'No hay riesgos validados en esta área.', onConfirm: closeDialog });
      return;
    }

    // Confirmación antes de ejecutar
    showDialog({
      type: 'warning',
      title: 'Confirmar des-validación',
      message: `Se regresarán ${validated.length} riesgo${validated.length > 1 ? 's' : ''} al estatus de "Captura". Esta acción revertirá la validación. ¿Deseas continuar?`,
      confirmLabel: 'Sí, Des-validar',
      cancelLabel: 'Cancelar',
      onCancel: closeDialog,
      onConfirm: async () => {
        closeDialog();
        try {
          const riskIds = validated.map(r => r.id);
          await axios.post('/riesgos/batch-unvalidate', { area_id: areaId, ejercicio_id: ejercicio, risk_ids: riskIds });
          fetchRiesgos();
          showDialog({ type: 'success', title: '¡Des-validación completada!', message: `${validated.length} riesgo${validated.length > 1 ? 's han' : ' ha'} sido regresado${validated.length > 1 ? 's' : ''} a "Captura" correctamente.`, onConfirm: closeDialog });
        } catch (err) {
          showDialog({ type: 'error', title: 'Error al des-validar', message: err.response?.data?.message || err.message, onConfirm: closeDialog });
        }
      }
    });
  };

  const handleVerMapa = () => {
    if (!areaId) return;
    navigate(`/reportes/mapa/${areaId}`, { state: { fromMapMAR: true, areaId } });
  };

  const allValidated = riesgos.length > 0 && riesgos.every(r => r.status === 'Validado');

  return (
    <>
      {/* Modal de diálogo estilizado */}
      <Dialog
        open={dialog.open}
        type={dialog.type}
        title={dialog.title}
        message={dialog.message}
        onConfirm={dialog.onConfirm}
        onCancel={dialog.onCancel}
        confirmLabel={dialog.confirmLabel}
        cancelLabel={dialog.cancelLabel}
      />

      <div className="page-head">
        <div>
          <h1>MAPA y MAR</h1>
          <p>Revisión integral y validación de la información del área.</p>
        </div>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(400px, 1fr))', gap: '20px' }}>
        <section className="panel" style={{ padding: '24px' }}>
          <h2 style={{ fontSize: '1.25rem', marginBottom: '20px', color: '#17324d' }}>Mapa y Matriz de Administración de Riesgos</h2>
          <label style={{ display: 'block', marginBottom: '20px' }}>
            <b style={{ display: 'block', marginBottom: '8px', color: '#5b6773', fontSize: '0.9rem' }}>Área / Unidad Responsable</b>
            <select className="input" value={areaId} onChange={e => setAreaId(e.target.value)} style={{ width: '100%', padding: '10px', borderRadius: '6px', border: '1px solid #d8e0e8' }}>
              {areas.length === 0 && <option value="">Sin áreas asignadas</option>}
              {areas.map((a, i) => (
                <option key={a.unidad_responsable_gasto_id || a.id_unidad || a.id || i} value={String(a.unidad_responsable_gasto_id || a.id_unidad || a.id)}>
                  {a.nombre || a.denominacion}
                </option>
              ))}
            </select>
          </label>
          <div style={{ display: 'flex', gap: '12px', marginBottom: '15px' }}>
            <button className="btn primary" onClick={handleVerMapa} disabled={!areaId}>Mapa / PDF</button>
            <button className="btn" onClick={() => navigate(`/reportes/mar/${areaId}`, { state: { fromMapMAR: true, areaId } })} disabled={!areaId}>MAR imprimible</button>
          </div>
          <p style={{ color: '#8898a9', fontSize: '0.9rem', margin: 0 }}>Estas vistas utilizan el mismo formato disponible en Reportes.</p>
        </section>

        <section className="panel" style={{ padding: '24px' }}>
          <h2 style={{ fontSize: '1.25rem', marginBottom: '20px', color: '#17324d' }}>Validación del área</h2>

          <div style={{ marginBottom: '20px', padding: '15px', borderRadius: '8px', backgroundColor: allValidated ? '#e6f4ea' : '#edf5fb', borderLeft: `4px solid ${allValidated ? '#34a853' : '#2d75b8'}` }}>
            <p style={{ margin: 0, color: allValidated ? '#137333' : '#17324d' }}>
              {allValidated ? 'Todos los riesgos del área están validados.' : 'Para validar integralmente, todos los riesgos deben encontrarse en estatus de "Captura".'}
            </p>
          </div>

          <div style={{ display: 'flex', gap: '10px' }}>
            {canValidate && (
              <button className="btn primary" onClick={handleBatchValidate}>Validar datos del área</button>
            )}
            {isSuperAdmin && (
              <button className="btn" onClick={handleBatchUnvalidate} style={{ backgroundColor: '#fde7e9', color: '#c62828', borderColor: '#f8bbd0' }}>Des-Validar</button>
            )}
          </div>
        </section>
      </div>
    </>
  );
}
