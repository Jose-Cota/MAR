import { useState, useEffect } from 'react';
import axios from '../../utils/axios';
import CheckCircleOutlineIcon from '@mui/icons-material/CheckCircleOutline';
import ErrorOutlineIcon from '@mui/icons-material/ErrorOutline';
import HelpOutlineIcon from '@mui/icons-material/HelpOutline';
import WarningAmberIcon from '@mui/icons-material/WarningAmber';

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

export default function ConfiguracionPage() {
  const [activeTab, setActiveTab] = useState('impresion');
  const [settings, setSettings] = useState({
    print_date_type: 'actual', // actual, fija, ninguna
    print_fixed_date: '',
    mail_host: '',
    mail_port: '',
    mail_username: '',
    mail_password: '',
    mail_encryption: '',
    mail_from_address: '',
    mail_from_name: ''
  });
  const [loading, setLoading] = useState(false);
  const [dialog, setDialog] = useState({ open: false, type: 'success', title: '', message: '' });

  const showDialog = (config) => setDialog({ open: true, ...config });
  const closeDialog = () => setDialog(d => ({ ...d, open: false }));

  useEffect(() => {
    fetchSettings();
  }, []);

  const fetchSettings = async () => {
    try {
      const res = await axios.get('/settings');
      if (res.data.data) {
        setSettings(prev => ({ ...prev, ...res.data.data }));
      }
    } catch (e) {
      console.error(e);
      showDialog({ type: 'error', title: 'Error', message: 'Error al cargar la configuración.', onConfirm: closeDialog });
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setSettings(prev => ({ ...prev, [name]: value }));
  };

  const handleSave = async () => {
    setLoading(true);
    try {
      await axios.post('/settings', settings);
      showDialog({ type: 'success', title: 'Éxito', message: 'Configuración guardada correctamente.', onConfirm: closeDialog });
    } catch (e) {
      console.error(e);
      showDialog({ type: 'error', title: 'Error', message: 'Ocurrió un problema al guardar la configuración.', onConfirm: closeDialog });
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <Dialog
        open={dialog.open}
        type={dialog.type}
        title={dialog.title}
        message={dialog.message}
        onConfirm={dialog.onConfirm}
        onCancel={dialog.onCancel}
      />
      <div className="page-head">
        <div>
          <h1>Configuración del Sistema</h1>
          <p>Ajustes generales, impresión y correo electrónico.</p>
        </div>
        <div>
          <button className="btn primary" onClick={handleSave} disabled={loading}>
            {loading ? 'Guardando...' : 'Guardar Cambios'}
          </button>
        </div>
      </div>

      <div className="tabs" style={{ display: 'flex', gap: '20px', borderBottom: '1px solid #cbd5e1', marginBottom: '20px' }}>
        <button 
          onClick={() => setActiveTab('impresion')}
          style={{ 
            background: 'none', border: 'none', padding: '10px 15px', cursor: 'pointer',
            fontSize: '1rem', fontWeight: activeTab === 'impresion' ? 'bold' : 'normal',
            borderBottom: activeTab === 'impresion' ? '3px solid #1F4E79' : '3px solid transparent',
            color: activeTab === 'impresion' ? '#1F4E79' : '#64748b'
          }}
        >
          Impresión
        </button>
        <button 
          onClick={() => setActiveTab('mailing')}
          style={{ 
            background: 'none', border: 'none', padding: '10px 15px', cursor: 'pointer',
            fontSize: '1rem', fontWeight: activeTab === 'mailing' ? 'bold' : 'normal',
            borderBottom: activeTab === 'mailing' ? '3px solid #1F4E79' : '3px solid transparent',
            color: activeTab === 'mailing' ? '#1F4E79' : '#64748b'
          }}
        >
          Mailing
        </button>
      </div>

      <div className="card" style={{ padding: '24px' }}>
        {activeTab === 'impresion' && (
          <div style={{ maxWidth: '600px' }}>
            <fieldset style={{ border: '1px solid #e2e8f0', borderRadius: '8px', padding: '20px' }}>
              <legend style={{ fontWeight: 'bold', color: '#1F4E79', padding: '0 10px' }}>Fechas de Impresión</legend>
              <p style={{ fontSize: '0.9rem', color: '#64748b', marginBottom: '15px' }}>
                Esta configuración definirá la fecha que se muestre cuando se imprima el mapa y la MAR.
              </p>

              <div style={{ display: 'flex', flexDirection: 'column', gap: '15px' }}>
                <label style={{ display: 'flex', alignItems: 'center', gap: '10px', cursor: 'pointer' }}>
                  <input 
                    type="radio" 
                    name="print_date_type" 
                    value="actual" 
                    checked={settings.print_date_type === 'actual'} 
                    onChange={handleChange} 
                  />
                  <span>Fecha actual (Día de la impresión)</span>
                </label>
                
                <div style={{ display: 'flex', alignItems: 'center', gap: '15px' }}>
                  <label style={{ display: 'flex', alignItems: 'center', gap: '10px', cursor: 'pointer' }}>
                    <input 
                      type="radio" 
                      name="print_date_type" 
                      value="fija" 
                      checked={settings.print_date_type === 'fija'} 
                      onChange={handleChange} 
                    />
                    <span>Fecha Fija:</span>
                  </label>
                  {settings.print_date_type === 'fija' && (
                    <input 
                      type="date" 
                      className="input" 
                      name="print_fixed_date" 
                      value={settings.print_fixed_date || ''} 
                      onChange={handleChange} 
                    />
                  )}
                </div>

                <label style={{ display: 'flex', alignItems: 'center', gap: '10px', cursor: 'pointer' }}>
                  <input 
                    type="radio" 
                    name="print_date_type" 
                    value="ninguna" 
                    checked={settings.print_date_type === 'ninguna'} 
                    onChange={handleChange} 
                  />
                  <span>Sin fecha</span>
                </label>
              </div>
            </fieldset>
          </div>
        )}

        {activeTab === 'mailing' && (
          <div style={{ maxWidth: '600px', display: 'flex', flexDirection: 'column', gap: '15px' }}>
            <p style={{ fontSize: '0.9rem', color: '#64748b' }}>
              Configura los datos del servidor SMTP para el envío de correos electrónicos del sistema.
            </p>
            
            <label style={{ display: 'flex', flexDirection: 'column', gap: '5px', fontWeight: 'bold' }}>
              Servidor SMTP (Host):
              <input type="text" className="input" name="mail_host" value={settings.mail_host || ''} onChange={handleChange} placeholder="ej. smtp.gmail.com" />
            </label>
            
            <label style={{ display: 'flex', flexDirection: 'column', gap: '5px', fontWeight: 'bold' }}>
              Puerto:
              <input type="text" className="input" name="mail_port" value={settings.mail_port || ''} onChange={handleChange} placeholder="ej. 587" />
            </label>
            
            <label style={{ display: 'flex', flexDirection: 'column', gap: '5px', fontWeight: 'bold' }}>
              Usuario:
              <input type="text" className="input" name="mail_username" value={settings.mail_username || ''} onChange={handleChange} />
            </label>
            
            <label style={{ display: 'flex', flexDirection: 'column', gap: '5px', fontWeight: 'bold' }}>
              Contraseña:
              <input type="password" className="input" name="mail_password" value={settings.mail_password || ''} onChange={handleChange} />
            </label>
            
            <label style={{ display: 'flex', flexDirection: 'column', gap: '5px', fontWeight: 'bold' }}>
              Cifrado (Encryption):
              <select className="input" name="mail_encryption" value={settings.mail_encryption || ''} onChange={handleChange}>
                <option value="">Ninguno</option>
                <option value="tls">TLS</option>
                <option value="ssl">SSL</option>
              </select>
            </label>
            
            <label style={{ display: 'flex', flexDirection: 'column', gap: '5px', fontWeight: 'bold' }}>
              Dirección de remitente (From Address):
              <input type="email" className="input" name="mail_from_address" value={settings.mail_from_address || ''} onChange={handleChange} placeholder="ej. no-reply@tecdmx.org.mx" />
            </label>
            
            <label style={{ display: 'flex', flexDirection: 'column', gap: '5px', fontWeight: 'bold' }}>
              Nombre de remitente (From Name):
              <input type="text" className="input" name="mail_from_name" value={settings.mail_from_name || ''} onChange={handleChange} placeholder="ej. Sistema MAR TECDMX" />
            </label>
          </div>
        )}
      </div>
    </>
  );
}
