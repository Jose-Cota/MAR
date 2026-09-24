const fs = require('fs');
const html = fs.readFileSync('Sistema_MAR_TECDMX_2026_2027_v7_7.html', 'utf8');
const regex = /\/\* MAR_V\d+_.*?_MASTER_MATRIX \*\/.{0,2000}/g;
let match;
while ((match = regex.exec(html)) !== null) {
    console.log(match[0]);
}
