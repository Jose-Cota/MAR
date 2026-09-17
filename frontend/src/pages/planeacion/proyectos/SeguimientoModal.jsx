import { useEffect, useState } from 'react';
import '../../../components/FichaDescriptiva.css';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  IconButton,
  Box,
  Typography,
  CircularProgress,
  Alert,
  Snackbar,
  Button,
} from '@mui/material';
import Iconify from '../../../components/Iconify';
import axios from '../../../utils/axios';

const MONTH_LABELS = [
  { id: 1, label: 'ENE' },
  { id: 2, label: 'FEB' },
  { id: 3, label: 'MAR' },
  { id: 4, label: 'ABR' },
  { id: 5, label: 'MAY' },
  { id: 6, label: 'JUN' },
  { id: 7, label: 'JUL' },
  { id: 8, label: 'AGO' },
  { id: 9, label: 'SEP' },
  { id: 10, label: 'OCT' },
  { id: 11, label: 'NOV' },
  { id: 12, label: 'DIC' },
];

export default function SeguimientoModal({ open, onClose, proyectoId }) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [metas, setMetas] = useState([]);
  const [savingMetaId, setSavingMetaId] = useState(null);
  const [savingMonthId, setSavingMonthId] = useState(null);
  const [snackbarMessage, setSnackbarMessage] = useState('');

  const [inputValues, setInputValues] = useState({});

  const currentMonth = new Date().getMonth() + 1;

  useEffect(() => {
    if (open && proyectoId) {
      fetchMetas();
    } else {
      setMetas([]);
      setInputValues({});
    }
  }, [open, proyectoId]);

  const fetchMetas = async () => {
    setLoading(true);
    setError('');
    try {
      const response = await axios.get(`/seguimiento/metas/${proyectoId}`);
      setMetas(response.data);
      
      const newInputs = {};
      response.data.forEach(meta => {
        MONTH_LABELS.forEach(month => {
          const avance = meta.avances?.find(a => a.mes_id === month.id);
          newInputs[`${meta.meta_id}_${month.id}`] = avance ? avance.numero : '';
        });
      });
      setInputValues(newInputs);

    } catch (err) {
      console.error('Error fetching metas para seguimiento:', err);
      setError('No fue posible cargar las metas para el seguimiento.');
    } finally {
      setLoading(false);
    }
  };

  const handleInputChange = (metaId, monthId, value) => {
    setInputValues(prev => ({
      ...prev,
      [`${metaId}_${monthId}`]: value
    }));
  };

  const handleSaveAvance = async (metaId, monthId) => {
    const value = inputValues[`${metaId}_${monthId}`];
    const numericValue = value === '' ? 0 : Number(value);

    setSavingMetaId(metaId);
    setSavingMonthId(monthId);
    
    try {
      await axios.put('/seguimiento/avance', {
        meta_id: metaId,
        proyecto_id: proyectoId,
        mes_id: monthId,
        numero: numericValue,
        explicacion: ''
      });
      
      setSnackbarMessage('Avance guardado correctamente');
      await fetchMetas();
    } catch (err) {
      console.error('Error guardando avance:', err);
      const msg = err.response?.data?.message || 'Error al guardar el avance';
      alert(msg);
      
      const meta = metas.find(m => m.meta_id === metaId);
      const avance = meta?.avances?.find(a => a.mes_id === monthId);
      setInputValues(prev => ({
        ...prev,
        [`${metaId}_${monthId}`]: avance ? avance.numero : ''
      }));
    } finally {
      setSavingMetaId(null);
      setSavingMonthId(null);
    }
  };

  if (!open) return null;

  return (
    <div className="ficha-descriptiva-container">
      <div className="fd-contenido">
        
        <div className="fd-titulo-vista">
          <div>
            <h2>Seguimiento de Avances Mensuales</h2>
            <p>Captura los avances alcanzados correspondientes a cada mes. Los meses futuros se encuentran deshabilitados.</p>
          </div>
          <Button onClick={onClose} variant="outlined" color="inherit">Volver a Proyectos</Button>
        </div>

              {loading ? (
                <Box sx={{ display: 'flex', justifyContent: 'center', py: 8 }}>
                  <CircularProgress />
                </Box>
              ) : error ? (
                <Alert severity="error">{error}</Alert>
              ) : metas.length === 0 ? (
                <Alert severity="info">Este proyecto no tiene metas registradas.</Alert>
              ) : (
                <div className="fd-panel">
                  <div className="fd-panel-cab">
                    <h3>Listado de Metas</h3>
                  </div>
                  <div className="fd-panel-cuerpo">
                    {metas.map((meta, index) => (
                      <div className="fd-meta-card" key={meta.meta_id} style={{ marginBottom: '24px' }}>
                        <div className="fd-meta-card-cab">
                          <div className="fd-meta-num">{index + 1}</div>
                          <div style={{ flex: 1 }}>
                            <div style={{ fontSize: '14px', fontWeight: 600, color: '#1F4E79' }}>
                              {meta.tipo === 'principal' ? 'Meta Principal' : 'Meta Complementaria'}
                            </div>
                            <div style={{ fontSize: '13px', color: '#6E6560', marginTop: '2px' }}>
                              {meta.nombre}
                            </div>
                          </div>
                        </div>

                        <div className="fd-fila-meses" style={{ marginTop: '16px' }}>
                          <div className="fd-celda-mes" style={{ justifyContent: 'flex-end', paddingBottom: '4px' }}>
                            <span style={{ textAlign: 'right', paddingRight: '8px' }}>Concepto</span>
                          </div>
                          {MONTH_LABELS.map(month => (
                            <div className="fd-celda-mes" key={month.id}>
                              <span>{month.label}</span>
                            </div>
                          ))}
                        </div>

                        <div className="fd-fila-meses" style={{ marginTop: '4px' }}>
                          <div className="fd-celda-mes" style={{ justifyContent: 'center' }}>
                            <span style={{ textAlign: 'right', paddingRight: '8px', color: '#6E6560' }}>Programado</span>
                          </div>
                          {MONTH_LABELS.map(month => {
                            const programado = meta.programados?.find(p => p.mes_id === month.id);
                            return (
                              <div className="fd-celda-mes" key={`prog_${month.id}`}>
                                <output>{programado ? programado.numero : 0}</output>
                              </div>
                            );
                          })}
                        </div>

                        <div className="fd-fila-meses" style={{ marginTop: '8px' }}>
                          <div className="fd-celda-mes" style={{ justifyContent: 'center' }}>
                            <span style={{ textAlign: 'right', paddingRight: '8px', color: '#1F4E79' }}>Alcanzado</span>
                          </div>
                          {MONTH_LABELS.map(month => {
                            const isFuture = month.id > currentMonth;
                            const isSaving = savingMetaId === meta.meta_id && savingMonthId === month.id;
                            
                            return (
                              <div className="fd-celda-mes" key={`alc_${month.id}`}>
                                <input
                                  type="number"
                                  value={inputValues[`${meta.meta_id}_${month.id}`] ?? ''}
                                  onChange={(e) => handleInputChange(meta.meta_id, month.id, e.target.value)}
                                  onBlur={() => handleSaveAvance(meta.meta_id, month.id)}
                                  disabled={isFuture || isSaving}
                                  style={{
                                    borderColor: isSaving ? '#3E7CB1' : '',
                                    background: isFuture ? '#F4F6F8' : '#fff'
                                  }}
                                />
                              </div>
                            );
                          })}
                        </div>

                        <div className="fd-fila-meses" style={{ marginTop: '8px' }}>
                          <div className="fd-celda-mes" style={{ justifyContent: 'center' }}>
                            <span style={{ textAlign: 'right', paddingRight: '8px', color: '#6E6560' }}>Avance %</span>
                          </div>
                          {MONTH_LABELS.map(month => {
                            const avance = meta.avances?.find(a => a.mes_id === month.id);
                            return (
                              <div className="fd-celda-mes" key={`pct_${month.id}`}>
                                <div style={{ 
                                  fontSize: '11px', 
                                  fontWeight: 600, 
                                  textAlign: 'center', 
                                  color: avance?.porcentaje >= 100 ? '#2E7D5B' : '#6E6560' 
                                }}>
                                  {avance ? `${avance.porcentaje}%` : '-'}
                                </div>
                              </div>
                            );
                          })}
                        </div>
                        
                      </div>
                    ))}
                  </div>
                </div>
              )}
        </div>

      <Snackbar
        open={!!snackbarMessage}
        autoHideDuration={3000}
        onClose={() => setSnackbarMessage('')}
        message={snackbarMessage}
        anchorOrigin={{ vertical: 'bottom', horizontal: 'center' }}
      />
    </div>
  );
}
