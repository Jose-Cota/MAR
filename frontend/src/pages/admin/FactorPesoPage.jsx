import { useState, useEffect, useMemo, useCallback } from 'react';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';
import Iconify from '../../components/Iconify';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  TextField,
  Snackbar,
  Alert,
  Chip,
  Tooltip
} from '@mui/material';

export default function FactorPesoPage() {
  const globalEjercicio = useGlobalStore((s) => s.ejercicio);
  const [ejercicio, setEjercicio] = useState(globalEjercicio || 2026);
  const [factores, setFactores] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [filterPeso, setFilterPeso] = useState('todos');

  // Estado para modal de edición
  const [editItem, setEditItem] = useState(null);
  const [editValor, setEditValor] = useState(1);
  const [saving, setSaving] = useState(false);

  // Estado para modal de confirmación de recálculo
  const [recalcDialogOpen, setRecalcDialogOpen] = useState(false);
  const [recalculating, setRecalculating] = useState(false);

  // Notificaciones estilizadas
  const [snackbar, setSnackbar] = useState({ open: false, message: '', severity: 'info' });

  const notify = useCallback((message, severity = 'success') => {
    setSnackbar({ open: true, message, severity });
  }, []);

  const fetchFactores = useCallback(async () => {
    setLoading(true);
    try {
      const res = await axios.get(`/factor-peso?ejercicio=${ejercicio}`);
      setFactores(res.data.data || []);
    } catch (err) {
      console.error(err);
      notify('Error al cargar los factores de peso.', 'error');
    } finally {
      setLoading(false);
    }
  }, [ejercicio, notify]);

  useEffect(() => {
    fetchFactores();
  }, [fetchFactores]);

  // Actualizar ejercicio local si cambia el global
  useEffect(() => {
    if (globalEjercicio && (globalEjercicio === 2026 || globalEjercicio === 2027)) {
      setEjercicio(globalEjercicio);
    }
  }, [globalEjercicio]);

  const handleOpenEdit = (item) => {
    setEditItem(item);
    setEditValor(item.factor_peso);
  };

  const handleSaveEdit = async () => {
    if (!editItem) return;
    const num = Number(editValor);
    if (isNaN(num) || num < 0) {
      notify('Ingrese un factor de peso válido (mayor o igual a 0).', 'warning');
      return;
    }

    setSaving(true);
    try {
      await axios.put(`/factor-peso/${editItem.id}`, { factor_peso: num });
      notify(`Factor de peso de "${editItem.ur}" actualizado a ${num}. Puntajes recalculados.`);
      setEditItem(null);
      fetchFactores();
    } catch (err) {
      console.error(err);
      notify('Error al guardar el factor de peso.', 'error');
    } finally {
      setSaving(false);
    }
  };

  const handleRecalcularGlobal = async () => {
    setRecalculating(true);
    try {
      const res = await axios.post('/factor-peso/recalcular', { ejercicio });
      notify(res.data.message || 'Puntajes recalculados con éxito.', 'success');
      setRecalcDialogOpen(false);
      fetchFactores();
    } catch (err) {
      console.error(err);
      notify('Error al recalcular puntajes en la base de datos.', 'error');
    } finally {
      setRecalculating(false);
    }
  };

  // Filtrado de URs
  const filteredFactores = useMemo(() => {
    return factores.filter((f) => {
      const matchesSearch = f.ur.toLowerCase().includes(searchTerm.toLowerCase());
      const matchesFilter =
        filterPeso === 'todos' ||
        (filterPeso === 'peso2' && Number(f.factor_peso) === 2) ||
        (filterPeso === 'peso1' && Number(f.factor_peso) === 1) ||
        (filterPeso === 'otro' && Number(f.factor_peso) !== 1 && Number(f.factor_peso) !== 2);
      return matchesSearch && matchesFilter;
    });
  }, [factores, searchTerm, filterPeso]);

  // Métricas
  const totalURs = factores.length;
  const countPeso2 = factores.filter((f) => Number(f.factor_peso) === 2).length;
  const countPeso1 = factores.filter((f) => Number(f.factor_peso) === 1).length;

  return (
    <div className="content">
      {/* Encabezado */}
      <div className="page-head">
        <div>
          <h1>Catálogo de Factores de Peso</h1>
          <p>
            Configuración y ponderación asignada a cada Unidad Responsable por ejercicio fiscal (2026 / 2027).
          </p>
        </div>
        <div className="head-actions">
          <button
            type="button"
            className="btn ghost"
            onClick={() => setRecalcDialogOpen(true)}
            style={{ display: 'flex', alignItems: 'center', gap: 6 }}
          >
            <Iconify icon="ph:arrows-clockwise" width={18} height={18} />
            <span>Recalcular Puntajes</span>
          </button>
        </div>
      </div>

      {/* Tarjetas informativas / KPIs */}
      <div className="kpis" style={{ gridTemplateColumns: 'repeat(3, 1fr)' }}>
        <div className="kpi">
          <span>Total de Unidades Responsables</span>
          <b>{totalURs}</b>
        </div>
        <div className="kpi" style={{ borderLeft: '4px solid #1f4e78' }}>
          <span>URs con Factor de Peso 2</span>
          <b style={{ color: '#1f4e78' }}>{countPeso2}</b>
        </div>
        <div className="kpi" style={{ borderLeft: '4px solid #6f8294' }}>
          <span>URs con Factor de Peso 1</span>
          <b style={{ color: '#6f8294' }}>{countPeso1}</b>
        </div>
      </div>

      {/* Nota descriptiva */}
      <div className="notice" style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
        <Iconify icon="ph:info" width={24} height={24} style={{ color: 'var(--blue2)', flexShrink: 0 }} />
        <div style={{ fontSize: 13 }}>
          <strong>Cálculo del Puntaje de Riesgo:</strong> Cada riesgo individual de las UR se evalúa como:{' '}
          <code style={{ background: '#e0ecf7', padding: '2px 6px', borderRadius: 4, fontWeight: 700 }}>
            Puntaje = Impacto × Probabilidad × Factor de Peso de la UR
          </code>
          . Al modificar el factor de peso de una UR, se actualizan de forma automática los puntajes de sus riesgos.
        </div>
      </div>

      {/* Panel principal */}
      <div className="panel">
        <div className="panel-head" style={{ marginBottom: 16 }}>
          <h2>Unidades Responsables y Factores ({ejercicio})</h2>
          <div style={{ display: 'flex', gap: 10, alignItems: 'center' }}>
            <span style={{ fontSize: 13, fontWeight: 600, color: 'var(--muted)' }}>Ejercicio:</span>
            <select
              className="input small"
              value={ejercicio}
              onChange={(e) => setEjercicio(Number(e.target.value))}
              style={{ fontWeight: 700, padding: '6px 12px' }}
            >
              <option value={2026}>Ejercicio 2026</option>
              <option value={2027}>Ejercicio 2027</option>
            </select>
          </div>
        </div>

        {/* Barra de herramientas */}
        <div className="toolbar" style={{ display: 'flex', gap: 12, flexWrap: 'wrap', marginBottom: 16 }}>
          <div style={{ position: 'relative', flex: '1 1 300px' }}>
            <input
              type="text"
              className="input"
              placeholder="Buscar Unidad Responsable..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              style={{ paddingLeft: 34 }}
            />
            <Iconify
              icon="ph:magnifying-glass"
              width={18}
              height={18}
              style={{ position: 'absolute', left: 10, top: '50%', transform: 'translateY(-50%)', color: 'var(--muted)' }}
            />
          </div>

          <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
            <span style={{ fontSize: 13, color: 'var(--muted)' }}>Filtrar por:</span>
            <select
              className="input"
              value={filterPeso}
              onChange={(e) => setFilterPeso(e.target.value)}
              style={{ width: 'auto' }}
            >
              <option value="todos">Todos los factores</option>
              <option value="peso2">Factor de peso 2</option>
              <option value="peso1">Factor de peso 1</option>
              <option value="otro">Otros valores</option>
            </select>
          </div>
        </div>

        {/* Tabla */}
        {loading ? (
          <div style={{ textAlign: 'center', padding: '40px 0', color: 'var(--muted)' }}>
            <Iconify icon="ph:spinner-gap" width={32} height={32} className="spin" />
            <p>Cargando catálogo de factores de peso...</p>
          </div>
        ) : filteredFactores.length === 0 ? (
          <div className="empty">
            <p>No se encontraron Unidades Responsables que coincidan con la búsqueda.</p>
          </div>
        ) : (
          <table>
            <thead>
              <tr>
                <th style={{ width: 60, textAlign: 'center' }}>No.</th>
                <th>Unidad Responsable</th>
                <th style={{ width: 140, textAlign: 'center' }}>Ejercicio</th>
                <th style={{ width: 180, textAlign: 'center' }}>Factor de Peso</th>
                <th style={{ width: 120, textAlign: 'center' }}>Acciones</th>
              </tr>
            </thead>
            <tbody>
              {filteredFactores.map((item, idx) => {
                const pesoNum = Number(item.factor_peso);
                const isPeso2 = pesoNum === 2;
                return (
                  <tr key={item.id}>
                    <td style={{ textAlign: 'center', fontWeight: 600, color: 'var(--muted)' }}>
                      {idx + 1}
                    </td>
                    <td>
                      <div style={{ fontWeight: 600, color: 'var(--navy)' }}>{item.ur}</div>
                      {item.ur_id && (
                        <small style={{ color: 'var(--muted)' }}>Área ID: {item.ur_id}</small>
                      )}
                    </td>
                    <td style={{ textAlign: 'center', fontWeight: 600 }}>
                      <span className="chip">{item.ejercicio}</span>
                    </td>
                    <td style={{ textAlign: 'center' }}>
                      <Chip
                        label={`Factor: ${item.factor_peso}`}
                        size="small"
                        sx={{
                          fontWeight: 700,
                          fontSize: '12px',
                          backgroundColor: isPeso2 ? '#e1effa' : '#f0f3f6',
                          color: isPeso2 ? '#104f85' : '#475569',
                          border: `1px solid ${isPeso2 ? '#a5cceb' : '#cbd5e1'}`
                        }}
                      />
                    </td>
                    <td style={{ textAlign: 'center' }}>
                      <Tooltip title="Modificar Factor de Peso">
                        <button
                          type="button"
                          className="icon-btn"
                          onClick={() => handleOpenEdit(item)}
                          style={{
                            padding: '6px 12px',
                            display: 'inline-flex',
                            alignItems: 'center',
                            gap: 4
                          }}
                        >
                          <Iconify icon="ph:pencil-simple" width={16} height={16} />
                          <span>Editar</span>
                        </button>
                      </Tooltip>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        )}
      </div>

      {/* Modal de Edición Estilizado */}
      <Dialog
        open={Boolean(editItem)}
        onClose={() => !saving && setEditItem(null)}
        maxWidth="xs"
        fullWidth
        PaperProps={{
          sx: { borderRadius: 2, padding: 1 }
        }}
      >
        <DialogTitle sx={{ fontWeight: 700, color: 'var(--navy)', pb: 1 }}>
          Modificar Factor de Peso
        </DialogTitle>
        <DialogContent sx={{ pt: 1 }}>
          <p style={{ margin: '0 0 12px', fontSize: 13, color: 'var(--muted)' }}>
            Unidad Responsable: <strong style={{ color: 'var(--navy)' }}>{editItem?.ur}</strong>
            <br />
            Ejercicio: <strong>{editItem?.ejercicio}</strong>
          </p>

          <TextField
            label="Factor de Peso"
            type="number"
            inputProps={{ step: 0.5, min: 0, max: 10 }}
            value={editValor}
            onChange={(e) => setEditValor(e.target.value)}
            fullWidth
            size="small"
            helperText="Selecciona o escribe el factor de ponderación (por defecto 1 o 2)."
            sx={{ mt: 1 }}
          />

          <div style={{ display: 'flex', gap: 8, marginTop: 12 }}>
            <Button
              variant={Number(editValor) === 1 ? 'contained' : 'outlined'}
              size="small"
              onClick={() => setEditValor(1)}
              sx={{ flex: 1 }}
            >
              Factor 1 (Estándar)
            </Button>
            <Button
              variant={Number(editValor) === 2 ? 'contained' : 'outlined'}
              size="small"
              onClick={() => setEditValor(2)}
              sx={{ flex: 1 }}
            >
              Factor 2 (Prioritario)
            </Button>
          </div>
        </DialogContent>
        <DialogActions sx={{ px: 3, pb: 2 }}>
          <Button
            onClick={() => setEditItem(null)}
            disabled={saving}
            color="inherit"
            sx={{ fontWeight: 600 }}
          >
            Cancelar
          </Button>
          <Button
            onClick={handleSaveEdit}
            disabled={saving}
            variant="contained"
            sx={{ fontWeight: 600, backgroundColor: 'var(--blue)' }}
          >
            {saving ? 'Guardando...' : 'Guardar y Recalcular'}
          </Button>
        </DialogActions>
      </Dialog>

      {/* Modal de Confirmación para Recálculo Global */}
      <Dialog
        open={recalcDialogOpen}
        onClose={() => !recalculating && setRecalcDialogOpen(false)}
        maxWidth="xs"
        fullWidth
        PaperProps={{
          sx: { borderRadius: 2, padding: 1 }
        }}
      >
        <DialogTitle sx={{ fontWeight: 700, color: 'var(--navy)', pb: 1 }}>
          Recálculo Global de Puntajes
        </DialogTitle>
        <DialogContent sx={{ pt: 1 }}>
          <p style={{ fontSize: 14, color: 'var(--text)', margin: 0 }}>
            Se recalcularán los puntajes de todos los riesgos institucionales y de las URs para el ejercicio{' '}
            <strong>{ejercicio}</strong> utilizando la fórmula:
          </p>
          <div
            style={{
              background: '#f1f5f9',
              padding: '10px 14px',
              borderRadius: 6,
              margin: '12px 0',
              fontFamily: 'monospace',
              fontSize: 13,
              fontWeight: 600,
              color: '#1e293b'
            }}
          >
            Puntaje = Impacto × Probabilidad × Factor de Peso
          </div>
          <p style={{ fontSize: 12, color: 'var(--muted)', margin: 0 }}>
            ¿Deseas continuar con el proceso de actualización en la base de datos?
          </p>
        </DialogContent>
        <DialogActions sx={{ px: 3, pb: 2 }}>
          <Button
            onClick={() => setRecalcDialogOpen(false)}
            disabled={recalculating}
            color="inherit"
            sx={{ fontWeight: 600 }}
          >
            Cancelar
          </Button>
          <Button
            onClick={handleRecalcularGlobal}
            disabled={recalculating}
            variant="contained"
            sx={{ fontWeight: 600, backgroundColor: 'var(--blue)' }}
          >
            {recalculating ? 'Procesando...' : 'Confirmar Recálculo'}
          </Button>
        </DialogActions>
      </Dialog>

      {/* Notificación estilizada (Snackbar) */}
      <Snackbar
        open={snackbar.open}
        autoHideDuration={4000}
        onClose={() => setSnackbar((s) => ({ ...s, open: false }))}
        anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
      >
        <Alert
          onClose={() => setSnackbar((s) => ({ ...s, open: false }))}
          severity={snackbar.severity}
          variant="filled"
          sx={{ width: '100%', fontWeight: 600 }}
        >
          {snackbar.message}
        </Alert>
      </Snackbar>
    </div>
  );
}
