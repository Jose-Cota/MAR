const fs = require('fs');

const riesgos = [{"id":12,"local_id":"R1","actividades":[{"accion_sustantiva_id":192,"proyecto_id":24}]}];
const actividades = [{"accion_sustantiva_id":192,"proyecto_id":24}];
const proyectoId = "24";

const projectActivitiesIds = actividades.filter(a => String(a.proyecto_id) === proyectoId).map(a => String(a.id || a.accion_sustantiva_id));
let count = 0;
riesgos.forEach(r => {
  const rActIds = (r.actividades || []).map(a => String(a.id || a.accion_sustantiva_id || a));
  if (rActIds.some(id => projectActivitiesIds.includes(id))) {
    count++;
  }
});

console.log("projectActivitiesIds", projectActivitiesIds);
console.log("count", count);
console.log("local_id", `R${count + 1}`);
