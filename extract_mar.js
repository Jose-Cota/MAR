const fs = require('fs');
const html = fs.readFileSync('c:/cota/MAR/Sistema_MAR_TECDMX_2026_2027_v7_7.html', 'utf8');

const start = html.indexOf('M.db = {');
const end = html.indexOf('M.ui = {', start);
const dbStr = html.substring(start + 7, end).trim().replace(/;$/, '');

// Try to parse it or just use regex
let db;
try {
    // It's a JS object, not strict JSON. Let's try to evaluate it in a context
    const vm = require('vm');
    const sandbox = { M: {} };
    vm.createContext(sandbox);
    vm.runInContext('M.db = ' + dbStr, sandbox);
    db = sandbox.M.db;
} catch (e) {
    console.error("Eval failed", e);
}

const result = [];
if (db && db.areas) {
    for (const [areaKey, areaName] of Object.entries(db.areas)) {
        if (!db.riesgos2027 || !db.riesgos2027[areaKey]) continue;
        
        const areaRiesgos = db.riesgos2027[areaKey];
        const actividades = [];
        
        // Find all activities for this area in 2027
        if (db.poaActions) {
            const acts = db.poaActions.filter(a => a.exercise === 2027 && a.area === areaKey);
            for (const act of acts) {
                // Find risks linked to this activity
                // In poaMatrix, it's activityId -> array of risk localIds
                let linkedRisks = [];
                if (db.poaMatrix && db.poaMatrix[act.id]) {
                    linkedRisks = db.poaMatrix[act.id].filter(rId => rId.includes('2027'));
                }
                
                actividades.push({
                    numero: act.numero || act.id.split('-').pop().replace('A', ''),
                    descripcion: act.denominacion || act.texto,
                    riesgos_vinculados: linkedRisks.map(r => r.split('-').pop()) // like 'R1', 'R2'
                });
            }
        }
        
        result.push({
            area_key: areaKey,
            area_name: areaName,
            actividades: actividades
        });
    }
}

fs.writeFileSync('c:/cota/MAR/backend/mar_matrix_2027.json', JSON.stringify(result, null, 2));
console.log("Extracted matrix to mar_matrix_2027.json");
