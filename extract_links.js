const fs = require('fs');
const html = fs.readFileSync('Sistema_MAR_TECDMX_2026_2027_v7_7.html', 'utf8');
const regex = /semanticLinks2027/g;
let match = regex.exec(html);
if (match) {
    let idx = match.index;
    console.log(html.substring(Math.max(0, idx - 500), idx + 1000));
} else {
    console.log("Not found");
}
