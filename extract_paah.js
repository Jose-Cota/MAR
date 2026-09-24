const fs = require('fs');
const html = fs.readFileSync('Sistema_MAR_TECDMX_2026_2027_v7_7.html', 'utf8');
const idx = html.indexOf('Aprobar los criterios de jurisprudencia y tesis relevantes');
console.log("Found without 'de':", idx > -1);
if (idx > -1) {
    console.log(html.substring(Math.max(0, idx - 100), idx + 100));
}
