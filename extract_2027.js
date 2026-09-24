const fs = require('fs');
const html = fs.readFileSync('Sistema_MAR_TECDMX_2026_2027_v7_7.html', 'utf8');

// Match `const OFFICIAL_POA_2027 = {...};`
const match = html.match(/const OFFICIAL_POA_2027 = (\{[\s\S]*?\});/);

if (match) {
    fs.writeFileSync('poa_2027_parchado.json', match[1]);
    console.log('Exito! poa_2027_parchado.json creado.');
} else {
    console.log('No se pudo encontrar OFFICIAL_POA_2027');
}
