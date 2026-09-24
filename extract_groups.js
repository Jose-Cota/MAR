const fs = require('fs');
const html = fs.readFileSync('Sistema_MAR_TECDMX_2026_2027_v7_7.html', 'utf8');
const regex = /function groups2027[\s\S]{0,1000}/;
const match = html.match(regex);
if (match) console.log(match[0]);
