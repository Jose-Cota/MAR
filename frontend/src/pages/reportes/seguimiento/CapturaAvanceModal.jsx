import React, { useState, useEffect } from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  Typography,
  Box,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  TextField,
  CircularProgress,
  IconButton
} from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';
import axios from "../../../utils/axios";
import ValidationModal from '../../../components/ui/ValidationModal';

const mesesList = [
  { id: 1, name: 'Enero' },
  { id: 2, name: 'Febrero' },
  { id: 3, name: 'Marzo' },
  { id: 4, name: 'Abril' },
  { id: 5, name: 'Mayo' },
  { id: 6, name: 'Junio' },
  { id: 7, name: 'Julio' },
  { id: 8, name: 'Agosto' },
  { id: 9, name: 'Septiembre' },
  { id: 10, name: 'Octubre' },
  { id: 11, name: 'Noviembre' },
  { id: 12, name: 'Diciembre' },
];

export default function CapturaAvanceModal({ open, onClose, meta, proyectoId, onSuccess }) {
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [avances, setAvances] = useState({});
  const [validationErrors, setValidationErrors] = useState([]);
  const [errorModalOpen, setErrorModalOpen] = useState(false);

  useEffect(() => {
    if (open && meta) {
      loadMetasData();
    } else {
      setAvances({});
      setErrorMsg(null);
    }
  }, [open, meta]);

  useEffect(() => {
    if (!open) {
      setValidationErrors([]);
      setErrorModalOpen(false);
    }
  }, [open]);

  const loadMetasData = async () => {
    setLoading(true);
    try {
      const avanceMap = {};
      
      mesesList.forEach(m => {
        avanceMap[m.id] = { numero: 0, explicacion: '', saved: false };
      });

      if (meta.avances && Array.isArray(meta.avances)) {
        meta.avances.forEach(av => {
          if (avanceMap[av.mes_id]) {
            avanceMap[av.mes_id] = {
              numero: av.numero || 0,
              explicacion: av.explicacion || '',
              saved: true
            };
          }
        });
      }

      setAvances(avanceMap);
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (mesId, field, value) => {
    setAvances(prev => ({
      ...prev,
      [mesId]: {
        ...prev[mesId],
        [field]: value
      }
    }));
  };

  const handleSave = async (mesId) => {
    const data = avances[mesId];
    if (data.numero === '' || isNaN(data.numero)) {
      setValidationErrors([`Avance Físico del mes de ${mesesList.find(m => m.id === mesId)?.name} (debe ser un número válido)`]);
      setErrorModalOpen(true);
      return;
    }

    setSaving(true);
    try {
      await axios.put('/seguimiento/avance', {
        meta_id: meta.meta_id || meta.id,
        proyecto_id: proyectoId,
        mes_id: mesId,
        numero: parseFloat(data.numero),
        explicacion: data.explicacion
      });
      
      setAvances(prev => ({
        ...prev,
        [mesId]: { ...prev[mesId], saved: true }
      }));
      
      if (onSuccess) onSuccess();
    } catch (error) {
      console.error(error);
      setValidationErrors([error.response?.data?.message || 'Error al guardar el avance.']);
      setErrorModalOpen(true);
    } finally {
      setSaving(false);
    }
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
      <DialogTitle sx={{ m: 0, p: 2, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <Typography variant="h6">Captura de Seguimiento - {meta?.clave || meta?.numero || ''}</Typography>
        <IconButton onClick={onClose} size="small">
          <CloseIcon />
        </IconButton>
      </DialogTitle>
      
      <DialogContent dividers>
        <Typography variant="subtitle1" gutterBottom sx={{ fontWeight: 'bold' }}>
          {meta?.nombre}
        </Typography>

        {/* error display removed; now uses ValidationModal */}

        {loading ? (
          <Box display="flex" justifyContent="center" p={3}>
            <CircularProgress />
          </Box>
        ) : (
          <TableContainer>
            <Table size="small">
              <TableHead>
                <TableRow>
                  <TableCell>Mes</TableCell>
                  <TableCell>Avance Físico (Realizado)</TableCell>
                  <TableCell>Justificación / Explicación</TableCell>
                  <TableCell align="center">Acción</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {mesesList.map((m) => {
                  const data = avances[m.id] || { numero: 0, explicacion: '' };
                  return (
                    <TableRow key={m.id}>
                      <TableCell sx={{ fontWeight: 500 }}>{m.name}</TableCell>
                      <TableCell>
                        <TextField
                          size="small"
                          type="number"
                          fullWidth
                          value={data.numero}
                          onChange={(e) => handleChange(m.id, 'numero', e.target.value)}
                        />
                      </TableCell>
                      <TableCell>
                        <TextField
                          size="small"
                          fullWidth
                          placeholder="Explicación si aplica..."
                          value={data.explicacion}
                          onChange={(e) => handleChange(m.id, 'explicacion', e.target.value)}
                        />
                      </TableCell>
                      <TableCell align="center">
                        <Button
                          variant={data.saved ? "outlined" : "contained"}
                          color={data.saved ? "success" : "primary"}
                          size="small"
                          disabled={saving}
                          onClick={() => handleSave(m.id)}
                        >
                          {data.saved ? 'Actualizar' : 'Guardar'}
                        </Button>
                      </TableCell>
                    </TableRow>
                  );
                })}
              </TableBody>
            </Table>
          </TableContainer>
        )}
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose} color="inherit">Cerrar</Button>
      </DialogActions>
      <ValidationModal open={errorModalOpen} onClose={() => setErrorModalOpen(false)} errors={validationErrors} />
    </Dialog>
  );
}
