const fs = require('fs');
const html = fs.readFileSync('Sistema_MAR_TECDMX_2026_2027_v7_7.html', 'utf8');
const lines = html.split('\n');
lines.forEach((l, i) => {
    if (l.includes('2027') && l.includes('2026') && (l.includes('map') || l.includes('replace'))) {
        console.log(`Line ${i}: ${l.substring(0, 150)}`);
    }
});
