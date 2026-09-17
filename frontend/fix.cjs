const fs = require('fs');
const files = [
  'src/pages/configuracion/SeguimientoConfigPage.jsx',
  'src/pages/configuracion/ElaboracionConfigPage.jsx',
  'src/pages/configuracion/AnteproyectoConfigPage.jsx'
];
for (const file of files) {
  let content = fs.readFileSync(file, 'utf8');
  content = content.replace(/<Grid size={{ xs: , sm:  }}>/g, '<Grid size={{ xs: 12, sm: 6 }}>');
  content = content.replace(/<Grid size={{ xs:  }}>/g, '<Grid size={{ xs: 12 }}>');
  fs.writeFileSync(file, content);
}
console.log('Fixed Grids');
