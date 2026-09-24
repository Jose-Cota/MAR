const fs = require('fs');
const j = JSON.parse(fs.readFileSync('poa_2027_parchado.json', 'utf8'));
const paah = j.actions.filter(a => a.areaId === 'PAAH');
console.log(`PAAH actions count: ${paah.length}`);
console.log(paah.map(a => `${a.number}: ${a.text}`).join('\n'));
