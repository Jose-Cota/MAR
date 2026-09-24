const fs = require('fs');

let html = fs.readFileSync('C:\\Cota\\MAR\\Sistema_MAR_TECDMX_2026_2027_v7_7.html', 'utf8');
let pos = html.indexOf('const db={');
if (pos === -1) pos = html.indexOf('const db = {');
let startPos = html.indexOf('{', pos);
let endPos = html.indexOf('return db', startPos);
// Let's find the `};` before `return db`
let text = html.substring(startPos, endPos);
let lastBrace = text.lastIndexOf('}');
let jsObj = text.substring(0, lastBrace + 1);

try {
    let db = new Function('return ' + jsObj)();
    fs.writeFileSync('C:\\Cota\\MAR\\backend\\poa_actions_clean.json', JSON.stringify(db.poaActions, null, 2));
    fs.writeFileSync('C:\\Cota\\MAR\\backend\\poa_projects_clean.json', JSON.stringify(db.poaProjects, null, 2));
    fs.writeFileSync('C:\\Cota\\MAR\\backend\\areas_clean.json', JSON.stringify(db.areas, null, 2));
    fs.writeFileSync('C:\\Cota\\MAR\\backend\\risks_clean.json', JSON.stringify(db.risks, null, 2));
    console.log('Successfully extracted DB with ' + db.poaActions.length + ' actions and ' + db.risks.length + ' risks');
} catch (e) {
    console.error('Eval error:', e);
}
