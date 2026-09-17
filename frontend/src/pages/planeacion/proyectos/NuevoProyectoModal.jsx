import { useState, useEffect } from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  TextField,
  MenuItem,
  Alert,
  Stack,
  Box
} from '@mui/material';
import axios from '../../../utils/axios';
import useGlobalStore from '../../../stores/useGlobalStore';
import ValidationModal from '../../../components/ui/ValidationModal';

export default function NuevoProyectoModal({ open, onClose, onSuccess }) {
  const ejercicio = useGlobalStore((state) => state.ejercicio);
  
  const [unidades, setUnidades] = useState([]);
  const [responsables, setResponsables] = useState([]);
  const [programas, setProgramas] = useState([]);
  const [subprogramas, setSubprogramas] = useState([]);

  const [formData, setFormData] = useState({
    denominacion: '',
    urg_id: '',
    ro_id: '',
    pg_id: '',
    sp_id: '',
    py: '',
  });

  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const [validationErrors, setValidationErrors] = useState([]);
  const [errorModalOpen, setErrorModalOpen] = useState(false);

  useEffect(() => {
    if (open && ejercicio) {
      fetchUnidades();
      fetchResponsables();
      fetchProgramas();
      fetchSubprogramas();
      setFormData({
        denominacion: '',
        urg_id: '',
        ro_id: '',
        pg_id: '',
        sp_id: '',
        py: '',
      });
      setError('');
    }
  }, [open, ejercicio]);

  const fetchUnidades = async () => {
    try {
      const res = await axios.get(`/unidades-responsables?ejercicio=${ejercicio}`);
      setUnidades(res.data);
    } catch (err) {
      console.error(err);
    }
  };

  const fetchResponsables = async () => {
    try {
      const res = await axios.get(`/responsables-operativos?ejercicio=${ejercicio}`);
      setResponsables(res.data);
    } catch (err) {
      console.error(err);
    }
  };

  const fetchProgramas = async () => {
    try {
      const res = await axios.get(`/programas?ejercicio=${ejercicio}`);
      setProgramas(res.data);
    } catch (err) {
      console.error(err);
    }
  };

  const fetchSubprogramas = async () => {
    try {
      const res = await axios.get(`/subprogramas?ejercicio=${ejercicio}`);
      setSubprogramas(res.data);
    } catch (err) {
      console.error(err);
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => {
      const newData = { ...prev, [name]: value };
      
      // Auto-reset dependent fields
      if (name === 'urg_id') {
        newData.ro_id = '';
      }
      if (name === 'pg_id') {
        newData.sp_id = '';
      }
      
      return newData;
    });
  };

  const handleSubmit = async () => {
    setError('');
    
    const errors = [];
    if (!formData.denominacion) errors.push('Denominación del Proyecto');
    if (!formData.urg_id) errors.push('Unidad Responsable de Gasto (URG)');
    if (!formData.ro_id) errors.push('Responsable Operativo (RO)');
    if (!formData.pg_id) errors.push('Programa (PG)');
    if (!formData.sp_id) errors.push('Subprograma (SP)');
    if (!formData.py) errors.push('Clave Proyecto (PY)');

    if (errors.length > 0) {
      setValidationErrors(errors);
      setErrorModalOpen(true);
      return;
    }

    setLoading(true);
    try {
      await axios.post('/proyectos', {
        nombre: formData.denominacion,
        responsable_operativo_id: formData.ro_id,
        subprograma_id: formData.sp_id,
        numero: formData.py,
      });
      if (onSuccess) onSuccess();
    } catch (err) {
      console.error(err);
      setError(err.response?.data?.message || 'Ocurrió un error al guardar el proyecto.');
    } finally {
      setLoading(false);
    }
  };

  // Filtros en cascada
  const selectedUrg = unidades.find(u => u.unidad_responsable_gasto_id == formData.urg_id);
  const filteredResponsables = responsables.filter(r => 
    selectedUrg ? r.urnum === selectedUrg.numero : false
  );

  const selectedPg = programas.find(p => p.programa_id == formData.pg_id);
  const filteredSubprogramas = subprogramas.filter(sp => 
    selectedPg ? sp.programa_numero === selectedPg.numero : false
  );

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
      <DialogTitle>Nuevo Proyecto</DialogTitle>
      <DialogContent dividers>
        {error && <Alert severity="error" sx={{ mb: 2 }}>{error}</Alert>}
        <Stack spacing={2}>
          <Box>
            <TextField
              fullWidth
              label="Denominación del Proyecto"
              name="denominacion"
              value={formData.denominacion}
              onChange={handleChange}
              required
            />
          </Box>
          
          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
            <TextField
              fullWidth
              select
              label="Unidad Responsable de Gasto (URG)"
              name="urg_id"
              value={formData.urg_id}
              onChange={handleChange}
              required
              sx={{ flex: 1 }}
            >
              <MenuItem value=""><em>Seleccione</em></MenuItem>
              {unidades.map((u) => (
                <MenuItem key={u.unidad_responsable_gasto_id} value={u.unidad_responsable_gasto_id}>
                  {u.numero} - {u.nombre}
                </MenuItem>
              ))}
            </TextField>
            <TextField
              fullWidth
              select
              label="Responsable Operativo (RO)"
              name="ro_id"
              value={formData.ro_id}
              onChange={handleChange}
              required
              disabled={!formData.urg_id}
              sx={{ flex: 1 }}
            >
              <MenuItem value=""><em>Seleccione</em></MenuItem>
              {filteredResponsables.map((r) => (
                <MenuItem key={r.responsable_operativo_id} value={r.responsable_operativo_id}>
                  {r.ronum} - {r.ronom}
                </MenuItem>
              ))}
            </TextField>
          </Stack>

          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
            <TextField
              fullWidth
              select
              label="Programa (PG)"
              name="pg_id"
              value={formData.pg_id}
              onChange={handleChange}
              required
              sx={{ flex: 1 }}
            >
              <MenuItem value=""><em>Seleccione</em></MenuItem>
              {programas.map((p) => (
                <MenuItem key={p.programa_id} value={p.programa_id}>
                  {p.numero} - {p.nombre}
                </MenuItem>
              ))}
            </TextField>
            <TextField
              fullWidth
              select
              label="Subprograma (SP)"
              name="sp_id"
              value={formData.sp_id}
              onChange={handleChange}
              required
              disabled={!formData.pg_id}
              sx={{ flex: 1.5 }}
            >
              <MenuItem value=""><em>Seleccione</em></MenuItem>
              {filteredSubprogramas.map((sp) => (
                <MenuItem key={sp.subprograma_id} value={sp.subprograma_id}>
                  {sp.numero} - {sp.nombre}
                </MenuItem>
              ))}
            </TextField>
            <TextField
              label="Clave Proyecto (PY)"
              name="py"
              value={formData.py}
              onChange={handleChange}
              required
              inputProps={{ maxLength: 3 }}
              sx={{ width: { xs: '100%', md: 180 } }}
            />
          </Stack>
        </Stack>
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose} color="inherit" disabled={loading}>
          Cancelar
        </Button>
        <Button onClick={handleSubmit} variant="contained" color="primary" disabled={loading}>
          {loading ? 'Guardando...' : 'Guardar Proyecto'}
        </Button>
      </DialogActions>
      <ValidationModal open={errorModalOpen} onClose={() => setErrorModalOpen(false)} errors={validationErrors} />
    </Dialog>
  );
}
