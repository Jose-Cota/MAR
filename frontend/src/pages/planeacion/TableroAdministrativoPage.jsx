import React, { useState, useEffect, useMemo } from 'react';
import {
  Box,
  Typography,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  CircularProgress,
  TextField,
  MenuItem,
  Select,
  Pagination,
  Chip,
  Button
} from '@mui/material';
import axios from '../../utils/axios';
import Iconify from '../../components/Iconify';
import useGlobalStore from '../../stores/useGlobalStore';

const TableroAdministrativoPage = () => {
  const ejercicio = useGlobalStore((state) => state.ejercicio);
  
  const [unidades, setUnidades] = useState([]);
  const [loading, setLoading] = useState(true);
  
  // Pagination & Search state
  const [searchQuery, setSearchQuery] = useState('');
  const [page, setPage] = useState(1);
  const [rowsPerPage, setRowsPerPage] = useState(10);

  const fetchTablero = async () => {
    try {
      setLoading(true);
      const response = await axios.get('/tablero-administrativo', {
        params: { ejercicio }
      });
      setUnidades(response.data);
    } catch (error) {
      console.error('Error fetching tablero:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (ejercicio) {
      fetchTablero();
    }
  }, [ejercicio]);

  // Flatten the hierarchical data into a single array of rows
  const flattenedRows = useMemo(() => {
    const rows = [];
    unidades.forEach((ur) => {
      // Parent UR row
      rows.push({
        type: 'ur',
        id: `ur-${ur.unidad_responsable_gasto_id}`,
        numero: ur.numero,
        nombre: ur.nombre,
        cerrada: ur.cerrada
      });
      
      // Child RO rows
      if (ur.responsables_operativos) {
        ur.responsables_operativos.forEach((ro) => {
          rows.push({
            type: 'ro',
            id: `ro-${ro.responsable_operativo_id}`,
            urNumero: ur.numero, // For display
            numero: ro.numero,
            nombre: ro.nombre
          });
        });
      }
    });
    return rows;
  }, [unidades]);

  // Filter rows based on search
  const filteredRows = useMemo(() => {
    if (!searchQuery) return flattenedRows;
    const lowerQuery = searchQuery.toLowerCase();
    return flattenedRows.filter(row => 
      (row.numero && row.numero.toString().toLowerCase().includes(lowerQuery)) ||
      (row.nombre && row.nombre.toLowerCase().includes(lowerQuery)) ||
      (row.urNumero && row.urNumero.toString().toLowerCase().includes(lowerQuery))
    );
  }, [flattenedRows, searchQuery]);

  // Calculate pagination
  const totalRecords = filteredRows.length;
  const totalPages = Math.ceil(totalRecords / rowsPerPage);
  const paginatedRows = filteredRows.slice((page - 1) * rowsPerPage, page * rowsPerPage);


  const handleDownloadExcel = async () => {
    try {
      const response = await axios.get('/reportes/excel/apertura-programatica', {
        params: { ejercicio },
        responseType: 'blob',
      });
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `Apertura_Programatica_POA_${ejercicio}.xlsx`);
      document.body.appendChild(link);
      link.click();
      link.remove();
    } catch (error) {
      console.error('Error downloading Excel:', error);
      alert('Error al descargar el archivo Excel.');
    }
  };

  const handleDownloadMatriz = async () => {
    try {
      const response = await axios.get('/reportes/excel/matriz-metas', {
        params: { ejercicio },
        responseType: 'blob',
      });
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `Matriz_Metas_POA_${ejercicio}.xlsx`);
      document.body.appendChild(link);
      link.click();
      link.remove();
    } catch (error) {
      console.error('Error downloading Excel:', error);
      alert('Error al descargar el archivo Excel.');
    }
  };

  const handleDownloadIndicadores = async () => {
    try {
      const response = await axios.get('/reportes/excel/indicadores', {
        params: { ejercicio },
        responseType: 'blob',
      });
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `Reporte_Indicadores_${ejercicio}.xlsx`);
      document.body.appendChild(link);
      link.click();
      link.remove();
    } catch (error) {
      console.error('Error downloading Excel:', error);
      alert('Error al descargar el archivo Excel.');
    }
  };

  const handlePageChange = (event, value) => {
    setPage(value);
  };

  const handleRowsPerPageChange = (event) => {
    setRowsPerPage(event.target.value);
    setPage(1); // Reset to first page
  };

  return (
    <Box p={3} sx={{ backgroundColor: '#fff', minHeight: '100vh' }}>
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 3 }}>
        <Typography variant="h4" sx={{ fontWeight: 'normal' }}>
          Tablero administrativo
        </Typography>
        <Box sx={{ display: 'flex', gap: 2, flexWrap: 'wrap', justifyContent: 'flex-end' }}>
          <Button 
            variant="contained" 
            color="info" 
            startIcon={<Iconify icon="mdi:microsoft-excel" />}
            onClick={handleDownloadMatriz}
          >
            Matriz Metas
          </Button>
          <Button 
            variant="contained" 
            color="warning" 
            startIcon={<Iconify icon="mdi:microsoft-excel" />}
            onClick={handleDownloadIndicadores}
          >
            Indicadores
          </Button>
          <Button 
            variant="contained" 
            color="success" 
            startIcon={<Iconify icon="mdi:microsoft-excel" />}
            onClick={handleDownloadExcel}
          >
            Exportar Apertura
          </Button>
        </Box>
      </Box>

      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end', mb: 2 }}>
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
          <Typography variant="body2" color="text.secondary">Mostrar</Typography>
          <Select
            size="small"
            value={rowsPerPage}
            onChange={handleRowsPerPageChange}
            sx={{ width: 80, height: 32 }}
          >
            <MenuItem value={10}>10</MenuItem>
            <MenuItem value={25}>25</MenuItem>
            <MenuItem value={50}>50</MenuItem>
            <MenuItem value={100}>100</MenuItem>
          </Select>
          <Typography variant="body2" color="text.secondary">registros</Typography>
        </Box>

        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
          <Typography variant="body2" color="text.secondary">Buscar:</Typography>
          <TextField
            size="small"
            variant="outlined"
            value={searchQuery}
            onChange={(e) => {
              setSearchQuery(e.target.value);
              setPage(1);
            }}
            sx={{ width: 250, '& .MuiInputBase-root': { height: 32 } }}
          />
        </Box>
      </Box>

      {loading ? (
        <Box display="flex" justifyContent="center" my={5}>
          <CircularProgress />
        </Box>
      ) : (
        <TableContainer component={Paper} elevation={0} sx={{ border: '1px solid #e0e0e0', borderRadius: 0 }}>
          <Table size="small">
            <TableHead>
              <TableRow sx={{ '& th': { borderBottom: '1px solid #e0e0e0' } }}>
                <TableCell sx={{ fontWeight: 'bold', width: '150px' }}>Número ↑↓</TableCell>
                <TableCell sx={{ fontWeight: 'bold' }}>Nombre</TableCell>
                <TableCell align="right" sx={{ fontWeight: 'bold', width: '150px' }}>↑↓ Estado ↑↓</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {paginatedRows.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={3} align="center" sx={{ py: 3 }}>
                    No se encontraron registros
                  </TableCell>
                </TableRow>
              ) : (
                paginatedRows.map((row, index) => {
                  const isUR = row.type === 'ur';
                  // Even RO rows get grey background, odd get white (based on their own internal index isn't needed, just alternate if we want, but the screenshot shows alternating grey/white for ROs).
                  // For simplicity, let's just make all RO rows alternate based on the index in the paginated list, or just use a fixed grey for all ROs?
                  // The screenshot shows: 01 01 (white), 01 02 (grey), 01 03 (white), 01 04 (grey)...
                  const roIndex = isUR ? -1 : parseInt(row.numero, 10); 
                  const roBg = roIndex % 2 === 0 ? '#f0f0f0' : '#ffffff';

                  return (
                    <TableRow 
                      key={row.id}
                      sx={{ 
                        backgroundColor: isUR ? '#4169e1' : roBg,
                        '& td': { 
                          color: isUR ? '#ffffff' : 'inherit',
                          borderBottom: 'none',
                          py: 1
                        }
                      }}
                    >
                      <TableCell sx={{ fontWeight: isUR ? 'bold' : 'normal', pl: isUR ? 2 : 4 }}>
                        {isUR ? row.numero : `${row.urNumero}          ${row.numero}`}
                      </TableCell>
                      <TableCell sx={{ fontWeight: isUR ? 'bold' : 'normal' }}>
                        {row.nombre}
                      </TableCell>
                      <TableCell align="right">
                        {isUR && (
                          <Chip 
                            label={row.cerrada ? 'Cerrada' : 'Abierta'} 
                            size="small"
                            sx={{ 
                              backgroundColor: row.cerrada ? '#ef5350' : '#4caf50',
                              color: '#fff',
                              borderRadius: '4px',
                              fontWeight: 'bold',
                              height: 24
                            }} 
                          />
                        )}
                      </TableCell>
                    </TableRow>
                  );
                })
              )}
            </TableBody>
          </Table>
        </TableContainer>
      )}

      {!loading && (
        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mt: 2 }}>
          <Typography variant="body2" color="text.secondary">
            Mostrando registros del {totalRecords === 0 ? 0 : (page - 1) * rowsPerPage + 1} al {Math.min(page * rowsPerPage, totalRecords)} de un total de {totalRecords} registros
          </Typography>
          <Pagination 
            count={totalPages} 
            page={page} 
            onChange={handlePageChange} 
            color="primary"
            shape="rounded"
            showFirstButton 
            showLastButton
            sx={{
              '& .MuiPaginationItem-root': {
                border: '1px solid #e0e0e0',
                borderRadius: 0,
                margin: 0,
              },
              '& .Mui-selected': {
                backgroundColor: '#4169e1 !important',
                color: '#fff',
              }
            }}
          />
        </Box>
      )}
    </Box>
  );
};

export default TableroAdministrativoPage;
