const axios = require('axios');

async function test() {
    try {
        const res = await axios.get('http://127.0.0.1:8023/api/reportes/seguimiento/avances?ejercicio=2026&mes=Junio', {
            headers: {
                'Accept': 'application/json',
            }
        });
        console.log(res.data);
    } catch (e) {
        console.log("Error status:", e.response ? e.response.status : e.message);
        console.log("Error data:", e.response ? e.response.data : '');
    }
}

test();
