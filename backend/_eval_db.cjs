const fs = require('fs');
let html = fs.readFileSync('C:\\Cota\\MAR\\Sistema_MAR_TECDMX_2026_2027_v7_7.html', 'utf8');
let pos = html.indexOf('const db={');
if (pos === -1) pos = html.indexOf('const db = {');
let startPos = html.indexOf('{', pos);
let endPos = html.indexOf('};', startPos);
let jsObj = html.substring(startPos, endPos + 1);

// We evaluate the object
let db = eval('(' + jsObj + ')');

fs.writeFileSync('C:\\Cota\\MAR\\backend\\poa_actions_clean.json', JSON.stringify(db.poaActions, null, 2));
fs.writeFileSync('C:\\Cota\\MAR\\backend\\poa_projects_clean.json', JSON.stringify(db.poaProjects, null, 2));
fs.writeFileSync('C:\\Cota\\MAR\\backend\\areas_clean.json', JSON.stringify(db.areas, null, 2));
console.log('Extracted ' + db.poaActions.length + ' actions');
