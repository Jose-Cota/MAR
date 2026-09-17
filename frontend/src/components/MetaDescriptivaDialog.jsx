import { useEffect, useState } from 'react';
import {
  Box,
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  FormControl,
  InputLabel,
  MenuItem,
  Select,
  TextField,
  InputAdornment,
  Typography,
  Autocomplete,
} from '@mui/material';
import Iconify from './Iconify';

const MONTHS = [
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

const DEFAULT_FORM = {
  nombre: '',
  unidad_medida_id: '',
  tipo: 'principal',
  programable: '',
  peso: '',
  orden: '',
  meses: MONTHS.reduce((accumulator, month) => {
    accumulator[month.id] = '';
    return accumulator;
  }, {}),
};

export default function MetaDescriptivaDialog({
  open,
  onClose,
  onSave,
  unidadMedidas = [],
  meta = null,
  maxPermitido = 100,
  sumaPorcentajes = 0,
}) {
  const [form, setForm] = useState(DEFAULT_FORM);
  const [saving, setSaving] = useState(false);
  const [validationErrors, setValidationErrors] = useState([]);
  const [errorModalOpen, setErrorModalOpen] = useState(false);

  useEffect(() => {
    if (!open) {
      return;
    }

    if (meta?.id) {
      setForm({
        nombre: meta.nombre || '',
        unidad_medida_id: meta.unidad_medida_id?.toString() || '',
        tipo: meta.tipo || 'principal',
        programable: meta.tmc === 1,
        peso: meta.peso || '',
        orden: meta.orden || '',
        meses: MONTHS.reduce((accumulator, month) => {
          const dbValue = meta.meses?.[month.id];
          if (meta.tmc !== 1) {
            accumulator[month.id] = (!dbValue || dbValue == 0) ? 'NP' : dbValue;
          } else {
            accumulator[month.id] = dbValue ?? '';
          }
          return accumulator;
        }, {}),
      });
      return;
    }

    setForm({
      ...DEFAULT_FORM,
      tipo: meta?.tipo || 'principal',
      orden: meta?.orden || '',
    });
  }, [meta, open]);

  const handleChange = (field, value) => {
    setForm((prev) => {
      const newForm = { ...prev, [field]: value };
      
      // Auto-fill meses when programable changes
      if (field === 'programable') {
        const isProgramable = value === true || value === 'true';
        const newValue = isProgramable ? '0' : 'NP';
        newForm.meses = MONTHS.reduce((acc, month) => {
          acc[month.id] = newValue;
          return acc;
        }, {});
      }
      return newForm;
    });
  };

  const handleMonthChange = (monthId, value) => {
    let sanitized = value.toUpperCase();
    
    if (form.tipo === 'complementaria' && form.programable === false) {
      if (sanitized !== '' && sanitized !== 'N' && sanitized !== 'NP' && !/^\d+$/.test(sanitized)) {
        return;
      }
    } else {
      if (sanitized !== '' && !/^\d+$/.test(sanitized)) {
        return;
      }
    }

    setForm((prev) => ({
      ...prev,
      meses: {
        ...prev.meses,
        [monthId]: sanitized,
      },
    }));
  };

  const handleSubmit = async () => {
    const errors = [];
    if (!form.nombre?.trim()) errors.push('Denominación de la meta');
    
    if (form.tipo === 'complementaria') {
      if (!form.unidad_medida_id) errors.push('Unidad de medida');
      if (form.programable === '' || form.programable === null) errors.push('Tipo (Programable / No Programable)');
      if (form.peso === '' || form.peso === null || form.peso < 0) errors.push('Valor (%)');
    }

    if (errors.length > 0) {
      setValidationErrors(errors);
      setErrorModalOpen(true);
      return;
    }
    
    setSaving(true);
    try {
      await onSave({
        id: meta?.id || null,
        meta_id: meta?.meta_id || meta?.meta_padre_id || null,
        nombre: (form.nombre || '').trim(),
        unidad_medida_id: form.tipo === 'principal' ? (unidadMedidas?.length > 0 ? Number(unidadMedidas[0].id) : 1) : Number(form.unidad_medida_id || 0),
        tipo: form.tipo,
        programable: form.tipo === 'complementaria' ? form.programable : null,
        peso: form.tipo === 'complementaria' ? Number(form.peso || 0) : 0,
        orden: form.tipo === 'complementaria' && form.orden !== '' && form.orden != null ? Number(form.orden) : (meta?.orden ?? null),
        tmc: form.tipo === 'complementaria' ? (form.programable ? 1 : 0) : 1,
        meses: Object.fromEntries(
          MONTHS.map((month) => {
            const val = form.meses[month.id];
            return [month.id, (val === 'NP' || val === 'N' || val === '') ? 0 : Number(val)];
          })
        ),
      });
      onClose();
    } catch (e) {
      console.error('Error on save in MetaDescriptivaDialog:', e);
    } finally {
      setSaving(false);
    }
  };

  const isEditMode = Boolean(meta?.id);
  const modalTitle = isEditMode 
    ? 'Editar meta...' 
    : `Agregar meta ${meta?.tipo === 'complementaria' ? 'complementaria...' : 'principal...'}`;
    
  const limiteDisponibleRaw = isEditMode ? maxPermitido + (Number(meta?.peso) || 0) : maxPermitido;
  const limiteDisponible = parseFloat(limiteDisponibleRaw.toFixed(2));
  const currentPeso = Number(form.peso) || 0;
  const errorMaximo = form.tipo === 'complementaria' && currentPeso > limiteDisponible;

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth PaperProps={{ sx: { borderRadius: 1, overflow: 'hidden', width: '80%', maxWidth: 720 } }}>
      <DialogTitle sx={{ p: 1.5, fontSize: '1rem', display: 'flex', alignItems: 'center', gap: 1, background: 'linear-gradient(135deg, #0a1e42 0%, #0d2f66 50%, #1e52a8 100%)', color: '#fff' }}>
        <Iconify icon={isEditMode ? 'mdi:pencil-circle' : 'mdi:bullseye-arrow'} width={20} />
        {modalTitle}
      </DialogTitle>
      <DialogContent sx={{ p: 1.5, pt: 4 }}>
        <Box sx={{ display: 'grid', gridTemplateColumns: { xs: '1fr', md: form.tipo === 'complementaria' ? '1fr 1fr' : '1fr' }, gap: 1, mt: 2 }}>
          <FormControl fullWidth size="small" required>
            <InputLabel id="meta-tipo-label" sx={{ fontSize: '0.8rem' }}>Tipo</InputLabel>
            <Select
              labelId="meta-tipo-label"
              label="Tipo"
              value={form.tipo}
              onChange={(event) => handleChange('tipo', event.target.value)}
              inputProps={{ readOnly: true }}
              sx={{ backgroundColor: 'rgba(0, 0, 0, 0.03)', fontSize: '0.8rem' }}
            >
              <MenuItem value="principal" sx={{ fontSize: '0.8rem' }}>Principal</MenuItem>
              <MenuItem value="complementaria" sx={{ fontSize: '0.8rem' }}>Complementaria</MenuItem>
            </Select>
          </FormControl>
          {form.tipo === 'complementaria' && (
            <FormControl fullWidth size="small" required>
              <Autocomplete
                size="small"
                options={[...unidadMedidas].sort((a, b) => (a.nombre || '').localeCompare(b.nombre || ''))}
                getOptionLabel={(option) => option.nombre || ''}
                value={[...unidadMedidas].find((u) => u.id.toString() === form.unidad_medida_id?.toString()) || null}
                onChange={(event, newValue) => {
                  handleChange('unidad_medida_id', newValue ? newValue.id.toString() : '');
                }}
                isOptionEqualToValue={(option, value) => option.id.toString() === value.id.toString()}
                renderInput={(params) => (
                  <TextField
                    {...params}
                    required
                    label="Unidad de medida"
                    InputLabelProps={{ ...params.InputLabelProps, sx: { fontSize: '0.8rem', ...params.InputLabelProps?.sx } }}
                    InputProps={{ ...params.InputProps, sx: { fontSize: '0.8rem', ...params.InputProps?.sx } }}
                  />
                )}
                componentsProps={{
                  paper: {
                    sx: { fontSize: '0.8rem' }
                  }
                }}
              />
            </FormControl>
          )}
          <Box sx={{ gridColumn: { md: 'span 2' }, display: 'flex', gap: 1 }}>
            {form.tipo === 'complementaria' && (
              <TextField
                label="No. Meta"
                type="number"
                value={form.orden}
                onChange={(event) => handleChange('orden', event.target.value)}
                disabled
                size="small"
                sx={{ width: '100px' }}
                InputProps={{ sx: { fontSize: '0.8rem' } }}
                InputLabelProps={{ sx: { fontSize: '0.8rem' } }}
              />
            )}
            <TextField
              required
              label="Denominación de la meta"
              value={form.nombre}
              onChange={(event) => handleChange('nombre', event.target.value)}
              fullWidth
              multiline
              rows={3}
              size="small"
              InputProps={{ sx: { fontSize: '0.8rem' } }}
              InputLabelProps={{ sx: { fontSize: '0.8rem' } }}
            />
          </Box>
          {form.tipo === 'complementaria' && (
            <>
              <FormControl fullWidth size="small" required>
                <InputLabel id="meta-programable-label" sx={{ fontSize: '0.8rem' }}>Tipo</InputLabel>
                <Select
                  labelId="meta-programable-label"
                  label="Tipo"
                  value={form.programable}
                  onChange={(event) => handleChange('programable', event.target.value)}
                  sx={{ fontSize: '0.8rem' }}
                >
                  <MenuItem value={true} sx={{ fontSize: '0.8rem' }}>Programable</MenuItem>
                  <MenuItem value={false} sx={{ fontSize: '0.8rem' }}>No Programable</MenuItem>
                </Select>
              </FormControl>
              <TextField
                required
                label="Valor (%)"
                type="number"
                value={form.peso}
                onChange={(event) => {
                  const raw = event.target.value;
                  // Allow only up to 2 decimal places
                  if (raw === '' || /^\d*\.?\d{0,2}$/.test(raw)) {
                    handleChange('peso', raw);
                  }
                }}
                fullWidth
                size="small"
                disabled={form.tipo === 'principal'}
                error={errorMaximo}
                helperText={errorMaximo ? `Máximo permitido: ${limiteDisponible}%` : `Disponible: ${limiteDisponible}%`}
                inputProps={{ step: '0.01', min: '0', max: '100' }}
                InputProps={{ sx: { fontSize: '0.8rem' } }}
                InputLabelProps={{ sx: { fontSize: '0.8rem' } }}
              />
            </>
          )}
        </Box>

        <Box
          sx={{
        mt: 1.5,
            display: 'grid',
            gridTemplateColumns: 'repeat(6, minmax(0, 1fr))',
            gap: 1,
          }}
        >
          {MONTHS.map((month) => {
            const displaySumaPorcentajes = parseFloat(Number(sumaPorcentajes).toFixed(2));
            return (
              <TextField
                key={month.id}
                label={month.label}
                type={form.programable === false && form.tipo === 'complementaria' ? 'text' : 'number'}
                size="small"
                value={form.tipo === 'principal' ? displaySumaPorcentajes : form.meses[month.id]}
                disabled={form.tipo === 'principal'}
                onChange={(event) => handleMonthChange(month.id, event.target.value)}
                inputProps={{ sx: { textAlign: 'center', fontSize: '0.8rem' } }}
                InputLabelProps={{ sx: { fontSize: '0.75rem' } }}
                InputProps={{ 
                  endAdornment: form.tipo === 'principal' ? <InputAdornment position="end" sx={{ '& .MuiTypography-root': { fontSize: '0.75rem' }, marginLeft: '-4px' }}>%</InputAdornment> : null
                }}
              />
            );
          })}
        </Box>
      </DialogContent>
      <DialogActions sx={{ px: 1.5, pb: 1.5, pt: 0 }}>
        <Button onClick={onClose} color="inherit" size="small" startIcon={<Iconify icon="mdi:close" />}>
          Cancelar
        </Button>
        <Button onClick={handleSubmit} variant="contained" disabled={saving || errorMaximo} size="small" startIcon={<Iconify icon="mdi:content-save" />}>
          {saving ? 'Guardando...' : 'Guardar'}
        </Button>
      </DialogActions>
      
      {/* Modal de Información Incompleta */}
      <Dialog open={errorModalOpen} onClose={() => setErrorModalOpen(false)} maxWidth="xs" fullWidth PaperProps={{ sx: { borderRadius: 2 } }}>
        <DialogTitle sx={{ display: 'flex', alignItems: 'center', gap: 1, color: 'error.main', pb: 1 }}>
          <Iconify icon="mdi:alert-circle" width={24} />
          Información incompleta
        </DialogTitle>
        <DialogContent>
          <Typography variant="body2" sx={{ mb: 2 }}>
            Para guardar esta meta, es necesario capturar los siguientes campos obligatorios:
          </Typography>
          <Box component="ul" sx={{ pl: 3, m: 0 }}>
            {validationErrors.map((err, i) => (
              <Typography component="li" key={i} variant="body2" color="error.main" sx={{ fontWeight: 500, mb: 0.5 }}>
                {err}
              </Typography>
            ))}
          </Box>
        </DialogContent>
        <DialogActions sx={{ p: 2, pt: 1 }}>
          <Button onClick={() => setErrorModalOpen(false)} variant="contained" color="error" fullWidth>
            Entendido
          </Button>
        </DialogActions>
      </Dialog>
    </Dialog>
  );
}
