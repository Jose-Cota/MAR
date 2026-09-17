import { Container, Typography, Card, CardContent, Grid, Box, Button } from '@mui/material';
import Iconify from '../components/Iconify';
import { useTheme } from '@mui/material/styles';

const DOCUMENTOS = [
  {
    titulo: 'Manual de usuario',
    descripcion: 'Guía paso a paso para el uso del sistema POA 2027.',
    archivo: '/ManualUsuario_POA2027.pdf',
    icono: 'mdi:book-open-page-variant'
  },
  {
    titulo: 'Presentacion de Criterios y Directrices',
    descripcion: 'Presentación de Criterios y Directrices oficiales para la formulación del POA 2027.',
    archivo: '/PresentacionDirectricesCriterios_POA2027.pdf',
    icono: 'mdi:file-document-multiple'
  },
  {
    titulo: 'Presentacion del Sistema POA 2027',
    descripcion: 'Presentación detallada sobre la etapa de elaboración del POA.',
    archivo: '/Presentacion_POA2027-Final.pdf',
    icono: 'mdi:presentation-play'
  },
  {
    titulo: 'Criterios POA 2027',
    descripcion: 'Documento de Criterios para el POA 2027.',
    archivo: '/Criterios_POA2027.pdf',
    icono: 'mdi:file-document-outline'
  },
  {
    titulo: 'Directrices metodológicas',
    descripcion: 'Directrices Metodológicas POA 2027.',
    archivo: '/Directrices Metodológicas POA 2027.pdf',
    icono: 'mdi:book-open-variant'
  },
  {
    titulo: 'Línea del Tiempo',
    descripcion: 'Línea de Tiempo del proceso.',
    archivo: '/Línea de Tiempo.pdf',
    icono: 'mdi:timeline-text-outline'
  },
  {
    titulo: 'UR Centralizadoras',
    descripcion: 'Lista de UR centralizadoras.',
    archivo: '/UR centralizadoras.pdf',
    icono: 'mdi:office-building'
  },
  {
    titulo: 'Ficha POA 2027',
    descripcion: 'Ficha descriptiva del POA 2027.',
    archivo: '/Ficha POA 2027.pdf',
    icono: 'mdi:file-chart-outline'
  }
];

export default function DocumentosPage() {
  const theme = useTheme();

  return (
    <Container maxWidth="lg" sx={{ py: 5 }}>
      <Box sx={{ mb: 5, textAlign: 'center' }}>
        <Typography variant="h3" sx={{ color: 'text.primary', fontWeight: 700, mb: 1 }}>
          Documentos
        </Typography>
        <Typography variant="body1" sx={{ color: 'text.secondary', maxWidth: 600, mx: 'auto' }}>
          Consulta y descarga los manuales, presentaciones y lineamientos oficiales para el Programa Operativo Anual.
        </Typography>
      </Box>

      <Grid container spacing={3} justifyContent="center">
        {DOCUMENTOS.map((doc, index) => (
          <Grid key={index} size={{ xs: 12, sm: 6, md: 4 }}>
            <Card
              sx={{
                height: '100%',
                display: 'flex',
                flexDirection: 'column',
                transition: 'transform 0.2s, box-shadow 0.2s',
                '&:hover': {
                  transform: 'translateY(-4px)',
                  boxShadow: theme.shadows[8],
                },
                borderRadius: 2,
                border: '1px solid',
                borderColor: 'divider'
              }}
            >
              <CardContent sx={{ flexGrow: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', textAlign: 'center', p: 4 }}>
                <Box
                  sx={{
                    width: 64,
                    height: 64,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    borderRadius: '50%',
                    bgcolor: theme.palette.mode === 'light' ? 'rgba(31, 78, 121, 0.08)' : 'rgba(255, 255, 255, 0.08)',
                    color: '#1F4E79',
                    mb: 3
                  }}
                >
                  <Iconify icon={doc.icono} width={36} height={36} />
                </Box>
                <Typography variant="subtitle1" gutterBottom sx={{ fontWeight: 600, minHeight: 56, display: 'flex', alignItems: 'center' }}>
                  {doc.titulo}
                </Typography>
                <Typography variant="body2" sx={{ color: 'text.secondary', mb: 3, flexGrow: 1 }}>
                  {doc.descripcion}
                </Typography>
                <Button
                  component="a"
                  href={doc.archivo}
                  target="_blank"
                  rel="noopener noreferrer"
                  variant="contained"
                  startIcon={<Iconify icon="mdi:download" />}
                  sx={{
                    bgcolor: '#1F4E79',
                    '&:hover': { bgcolor: '#143352' },
                    width: '100%',
                    borderRadius: 2,
                    textTransform: 'none',
                    fontWeight: 600
                  }}
                >
                  Abrir / Descargar
                </Button>
              </CardContent>
            </Card>
          </Grid>
        ))}
      </Grid>
    </Container>
  );
}
