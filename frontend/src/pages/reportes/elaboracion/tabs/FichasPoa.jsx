import React, { useState, useEffect } from 'react';
import {
  Box,
  Typography,
  Button,
  CircularProgress
} from '@mui/material';
import PictureAsPdfIcon from '@mui/icons-material/PictureAsPdf';
import axios from '../../../../utils/axios';
import useGlobalStore from '../../../../stores/useGlobalStore';

export default function FichasPoa() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isGenerating, setIsGenerating] = useState(false);
  
  // Custom simple table state
  const [selectedIds, setSelectedIds] = useState([]);
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(0);
  const rowsPerPage = 10;

  const ejercicio = useGlobalStore((state) => state.ejercicio);

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true);
      try {
        const response = await axios.get('/elaboracion/fichas-poa', {
          params: { ejercicio },
        });
        setData(response.data);
      } catch (error) {
        console.error('Error fetching fichas', error);
      } finally {
        setLoading(false);
      }
    };

    if (ejercicio) {
      fetchData();
    }
  }, [ejercicio]);

  const handleSelectAll = (e) => {
    if (e.target.checked) {
      setSelectedIds(filteredData.map(row => row.id));
    } else {
      setSelectedIds([]);
    }
  };

  const handleSelectOne = (e, id) => {
    if (e.target.checked) {
      setSelectedIds(prev => [...prev, id]);
    } else {
      setSelectedIds(prev => prev.filter(item => item !== id));
    }
  };

  const handleSearchChange = (e) => {
    setSearch(e.target.value);
    setPage(0);
  };

  const filteredData = data.filter(row => 
    row.denominacion.toLowerCase().includes(search.toLowerCase()) ||
    row.py.includes(search)
  );

  const paginatedData = filteredData.slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage);
  const totalPages = Math.ceil(filteredData.length / rowsPerPage);

  const handleGeneratePdf = async () => {
    if (selectedIds.length === 0) {
      alert('Por favor selecciona al menos un proyecto.');
      return;
    }

    setIsGenerating(true);
    try {
      const response = await axios.post('/elaboracion/fichas-poa/pdf', {
        proyectos: selectedIds
      }, {
        responseType: 'blob' // Important for downloading PDF
      });

      // Create a link to download the blob
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', 'fichas_poa.pdf');
      document.body.appendChild(link);
      link.click();
      link.parentNode.removeChild(link);

    } catch (error) {
      console.error('Error generating PDF', error);
      alert('Hubo un error al generar el PDF.');
    } finally {
      setIsGenerating(false);
    }
  };

  if (loading) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', p: 5 }}>
        <CircularProgress />
      </Box>
    );
  }

  return (
    <Box>
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 3 }}>
        <Typography variant="h6">Generación Personalizada de Fichas POA</Typography>
        <Button 
          variant="contained" 
          color="info" 
          startIcon={isGenerating ? <CircularProgress size={20} color="inherit" /> : <PictureAsPdfIcon />}
          onClick={handleGeneratePdf}
          disabled={isGenerating || selectedIds.length === 0}
        >
          {isGenerating ? 'Construyendo...' : 'Construir PDF'}
        </Button>
      </Box>

      {/* Toolbar / Search */}
      <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 2 }}>
        <Typography>Mostrar {rowsPerPage} registros</Typography>
        <Box>
          Buscar: <input type="text" value={search} onChange={handleSearchChange} style={{ marginLeft: 8, padding: '4px' }} />
        </Box>
      </Box>

      {/* Table */}
      <table style={{ width: '100%', borderCollapse: 'collapse', textAlign: 'left', fontFamily: 'Arial, sans-serif' }}>
        <thead>
          <tr style={{ borderBottom: '1px solid #ccc', backgroundColor: '#f9f9f9' }}>
            <th style={{ padding: '6px 12px' }}>
              <input 
                type="checkbox" 
                onChange={handleSelectAll} 
                checked={selectedIds.length === filteredData.length && filteredData.length > 0} 
              />
            </th>
            <th style={{ padding: '6px 12px' }}>URG</th>
            <th style={{ padding: '6px 12px' }}>RO</th>
            <th style={{ padding: '6px 12px' }}>PG</th>
            <th style={{ padding: '6px 12px' }}>SP</th>
            <th style={{ padding: '6px 12px' }}>PY</th>
            <th style={{ padding: '6px 12px' }}>Denominación</th>
          </tr>
        </thead>
        <tbody>
          {paginatedData.map((row, index) => (
            <tr key={row.id} style={{ borderBottom: '1px solid #eee', backgroundColor: index % 2 === 0 ? '#f0f0f0' : '#ffffff' }}>
              <td style={{ padding: '4px 12px' }}>
                <input 
                  type="checkbox" 
                  onChange={(e) => handleSelectOne(e, row.id)}
                  checked={selectedIds.includes(row.id)}
                />
              </td>
              <td style={{ padding: '4px 12px' }}>{row.urg}</td>
              <td style={{ padding: '4px 12px' }}>{row.ro}</td>
              <td style={{ padding: '4px 12px' }}>{row.pg}</td>
              <td style={{ padding: '4px 12px' }}>{row.sp}</td>
              <td style={{ padding: '4px 12px' }}>{row.py}</td>
              <td style={{ padding: '4px 12px', fontSize: '0.85rem' }}>{row.denominacion}</td>
            </tr>
          ))}
        </tbody>
      </table>

      {/* Pagination */}
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mt: 2 }}>
        <Typography variant="body2" color="text.secondary">
          Mostrando registros del {page * rowsPerPage + 1} al {Math.min((page + 1) * rowsPerPage, filteredData.length)} de un total de {filteredData.length} registros
        </Typography>
        <Box>
          <button 
            disabled={page === 0} 
            onClick={() => setPage(p => p - 1)}
            style={{ padding: '6px 12px', marginRight: 4, cursor: page === 0 ? 'not-allowed' : 'pointer' }}
          >
            Anterior
          </button>
          <span style={{ padding: '6px 12px', backgroundColor: '#1976d2', color: 'white' }}>{page + 1}</span>
          <button 
            disabled={page >= totalPages - 1} 
            onClick={() => setPage(p => p + 1)}
            style={{ padding: '6px 12px', marginLeft: 4, cursor: page >= totalPages - 1 ? 'not-allowed' : 'pointer' }}
          >
            Siguiente
          </button>
        </Box>
      </Box>

    </Box>
  );
}
