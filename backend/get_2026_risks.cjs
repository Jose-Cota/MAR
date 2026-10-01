const fs = require('fs');

const data = JSON.parse(fs.readFileSync('c:/Cota/MAR/Respaldo_MAR_TECDMX_2026-09-23.json', 'utf8'));

// find objects with 'nombre' that includes 'Ponencia'
const ponencias = (data.urgs || data.areas || data).filter(a => a && a.nombre && a.nombre.includes('Ponencia'));

ponencias.forEach(p => {
    console.log(`\n--- ${p.nombre} ---`);
    const riesgos = data.riesgos ? data.riesgos.filter(r => r.urg_id === p.id) : (p.riesgos || p.riesgos2026 || []);
    
    const riesgos2026 = riesgos.filter(r => r.ejercicio_id == 2026 || r.ejercicio_id == 1 || (r.ejercicio && r.ejercicio.includes('2026')) || r.ejercicio_id == null);
    
    if (riesgos2026.length > 0) {
        riesgos2026.forEach(r => console.log(`- ${r.riesgo || r.descripcion}`));
    } else if (riesgos.length > 0) {
        riesgos.forEach(r => console.log(`- ${r.riesgo || r.descripcion} (Ejercicio: ${r.ejercicio_id})`));
    } else {
        console.log("No riesgos found");
    }
});
