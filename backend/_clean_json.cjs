const fs = require('fs');
let html = fs.readFileSync('C:\\Cota\\MAR\\Sistema_MAR_TECDMX_2026_2027_v7_7.html', 'utf8');

// Find poaActions:[
let pos = html.indexOf('poaActions:[');
let json = html.substring(pos + 11);

// Parse the array
let parsed = [];
let buffer = '';
let inString = false;
let brackets = 0;
let i = 0;

for (; i < json.length; i++) {
    let c = json[i];
    if (c === '"' && json[i-1] !== '\\') inString = !inString;
    if (!inString) {
        if (c === '[') brackets++;
        if (c === ']') {
            brackets--;
            if (brackets === 0) {
                buffer += c;
                break;
            }
        }
    }
    buffer += c;
}

fs.writeFileSync('C:\\Cota\\MAR\\backend\\poa_actions_clean.json', buffer);
console.log('Wrote ' + buffer.length + ' bytes');
