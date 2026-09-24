const fs = require('fs');
const html = fs.readFileSync('Sistema_MAR_TECDMX_2026_2027_v7_7.html', 'utf8');
const scriptRegex = /<script>([\s\S]*?)<\/script>/g;
let match;
while ((match = scriptRegex.exec(html)) !== null) {
    if (match[1].includes('M.db.poaActions') || match[1].includes('M.db.poaProjects')) {
        if (match[1].includes('SG') || match[1].includes('Recepción, registro')) {
            console.log("------- FOUND SCRIPT -------");
            console.log(match[1].substring(0, 1000));
        }
    }
}
