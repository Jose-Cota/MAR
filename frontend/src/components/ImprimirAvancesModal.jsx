import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  Typography,
  Box,
  IconButton,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  GlobalStyles
} from '@mui/material';
import Iconify from './Iconify';
import logo from '../assets/logo_tecdmx.png';

export default function ImprimirAvancesModal({ open, onClose, mes, groupedData = {}, ejercicio }) {
  const handlePrint = () => {
    window.print();
  };

  return (
    <>
      {/* Estilos de impresión: encabezado repetido + números de página */}
      <GlobalStyles styles={`
        @media print {
          @page {
            margin: 1.5cm;
            @bottom-center {
              content: "Página " counter(page) " de " counter(pages);
              font-size: 9pt;
              color: #555;
              font-family: Arial, sans-serif;
            }
          }

          /* Eliminar espacios extra que generan página vacía */
          body, html {
            margin: 0 !important;
            padding: 0 !important;
          }
          .MuiDialogContent-root {
            padding-bottom: 0 !important;
          }
          .MuiDialog-paper {
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
          }
        }
      `} />
      <Dialog open={open} onClose={onClose} maxWidth="lg" fullWidth>
      <DialogTitle sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', '@media print': { display: 'none' } }}>
        Imprimir avances
        <IconButton onClick={onClose}>
          <Iconify icon="mdi:close" />
        </IconButton>
      </DialogTitle>
      
      <DialogContent dividers sx={{ backgroundColor: '#fff', display: 'flex', flexDirection: 'column', alignItems: 'center', '@media print': { border: 'none' } }}>
        <Box sx={{ width: '100%', pt: 0, pb: 0, px: 3 }}>
          <Box sx={{ mt: 0.5 }}>
            {Object.keys(groupedData).length === 0 ? (
              <Typography variant="body1" color="text.secondary" align="center" sx={{ py: 4 }}>
                No hay datos disponibles.
              </Typography>
            ) : (
              <TableContainer sx={{ border: 'none', borderRadius: 2, overflow: 'hidden', boxShadow: 'none' }}>
                <Table size="small" sx={{ '& td, & th': { border: 'none', py: 0.4 } }}>
                  <TableHead>
                    <TableRow sx={{ backgroundColor: '#fff', borderBottom: '2px solid #1976d2' }}>
                      <TableCell colSpan={3} sx={{ padding: '0 0 8px 0 !important', borderBottom: 'none !important' }}>
                        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                          <Box sx={{ width: '100px' }}>
                            <img src={logo} alt="Logo" style={{ width: '100%', height: 'auto' }} />
                          </Box>
                          <Box sx={{ textAlign: 'center', flexGrow: 1 }}>
                            <Typography variant="subtitle1" sx={{ fontWeight: 'bold', color: '#212121', mb: 0, fontSize: '1.27rem', lineHeight: 1.2 }}>
                              Programa Operativo Anual {ejercicio}
                            </Typography>
                            <Typography variant="h5" sx={{ fontWeight: 'bold', color: '#0d47a1', mb: 0, lineHeight: 1.2 }}>
                              Avance de Proyectos por Unidad Responsable
                            </Typography>
                            <Typography variant="subtitle1" sx={{ color: 'text.secondary', fontWeight: 'medium', lineHeight: 1.2 }}>
                              Enero - {mes}
                            </Typography>
                          </Box>
                          <Box sx={{ width: '100px' }}>
                          </Box>
                        </Box>
                      </TableCell>
                    </TableRow>
                    <TableRow sx={{ backgroundColor: '#37474f' }}>
                      <TableCell align="center" sx={{ width: '18%', fontWeight: 'bold', color: '#eceff1', fontSize: '0.75rem', letterSpacing: '0.05em', textTransform: 'uppercase' }}>UR</TableCell>
                      <TableCell align="center" sx={{ width: '76%', fontWeight: 'bold', color: '#eceff1', fontSize: '0.75rem', letterSpacing: '0.05em', textTransform: 'uppercase' }}>Avance del Proyecto</TableCell>
                      <TableCell align="center" sx={{ width: '6%', fontWeight: 'bold', color: '#eceff1', fontSize: '0.75rem', letterSpacing: '0.05em', textTransform: 'uppercase' }}>%</TableCell>
                    </TableRow>
                  </TableHead>
                  <TableBody>
                    {Object.entries(groupedData).map(([urName, proyectos], index) => (
                      <TableRow
                        key={index}
                        sx={{
                          verticalAlign: 'middle',
                          backgroundColor: index % 2 === 0 ? '#ffffff' : '#e0e0e0',
                          '&:hover': { backgroundColor: index % 2 === 0 ? '#f0f0f0' : '#d6d6d6' },
                        }}
                      >
                        <TableCell align="center" sx={{ color: '#37474f', fontWeight: 600, fontSize: '0.75rem' }}>
                          {urName}
                        </TableCell>
                        <TableCell>
                          {proyectos.map((row, pyIndex) => (
                            <Box key={pyIndex} sx={{ mb: pyIndex < proyectos.length - 1 ? 0.75 : 0, position: 'relative', borderRadius: 1, overflow: 'hidden', backgroundColor: '#e3f2fd' }}>
                              <Box sx={{
                                position: 'absolute',
                                top: 0, left: 0, bottom: 0,
                                width: `${row.avance}%`,
                                backgroundColor: '#0d47a1',
                                borderRadius: 1,
                              }} />
                              <Typography sx={{
                                position: 'relative',
                                zIndex: 1,
                                px: 1.5,
                                py: 0.6,
                                fontSize: '0.7rem',
                                fontWeight: 600,
                                fontFamily: 'monospace',
                                lineHeight: 1.4,
                                color: '#fff',
                                textShadow: '0 0 4px rgba(0,0,0,0.6)',
                                whiteSpace: 'normal',
                              }}>
                                {row.clave} {row.nombre}
                              </Typography>
                            </Box>
                          ))}
                        </TableCell>
                        <TableCell align="center">
                          {proyectos.map((row, pyIndex) => (
                            <Typography key={pyIndex} sx={{ fontSize: '0.63rem', fontWeight: 'bold', color: 'text.secondary', mb: pyIndex < proyectos.length - 1 ? 0.75 : 0, display: 'block' }}>
                              {`${Math.round(row.avance)}%`}
                            </Typography>
                          ))}
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </TableContainer>
            )}
          </Box>
        </Box>
      </DialogContent>
      
      <DialogActions sx={{ '@media print': { display: 'none' } }}>
        <Button onClick={onClose} color="inherit">
          Cancelar
        </Button>
        <Button onClick={handlePrint} variant="contained" color="primary" startIcon={<Iconify icon="mdi:printer" />}>
          Imprimir
        </Button>
      </DialogActions>
    </Dialog>
    </>
  );
}
