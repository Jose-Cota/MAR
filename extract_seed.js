const fs = require('fs');
const html = fs.readFileSync('c:\\cota\\MAR\\Sistema_MAR_TECDMX_2026_2027_v5_1.html', 'utf8');
const match = html.match(/const SEED = (\{.*?\});\n/s);
if (match) {
    fs.writeFileSync('c:\\cota\\MAR\\scratch_seed.json', match[1]);
    console.log('Successfully extracted SEED to scratch_seed.json');
} else {
    // Try a different regex if the first one fails
    const match2 = html.match(/const SEED = (\{[\s\S]*?\});/);
    if (match2) {
        fs.writeFileSync('c:\\cota\\MAR\\scratch_seed.json', match2[1]);
        console.log('Successfully extracted SEED to scratch_seed.json');
    } else {
        console.log('Could not find SEED object');
    }
}
