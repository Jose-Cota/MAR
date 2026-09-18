import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from '../utils/axios';
import useGlobalStore from '../stores/useGlobalStore';
import useAuth from '../hooks/useAuth';

const cuadrante = (p, i) => {
  p = Number(p); i = Number(i);
  if (p > 5 && i > 5) return 'QI';
  if (p > 5 && i <= 5) return 'QII';
  if (p <= 5 && i <= 5) return 'QIII';
  return 'QIV';
};

export default function Dashboard() {
  const [areas, setAreas] = useState([]);
  const [riesgos, setRiesgos] = useState([]);
  const [loading, setLoading] = useState(true);
  const ejercicio = useGlobalStore((s) => s.ejercicio);
  const { user } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    fetchData();
  }, [ejercicio]);

  const fetchData = async () => {
    setLoading(true);
    try {
      const [resRiesgos, resAreas] = await Promise.all([
        axios.get(`/riesgos?ejercicio_id=${ejercicio}`),
        axios.get('/unidades-responsables'),
      ]);
      setRiesgos(resRiesgos.data.data || resRiesgos.data || []);
      setAreas(resAreas.data.data || resAreas.data || []);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const isAdmin = user?.role === 'Administrador' || user?.roles?.some(r => r.name === 'Administrador');

  // Stats calculation
  const getStats = (areaIds = null) => {
    const rs = areaIds 
      ? riesgos.filter(r => areaIds.includes(String(r.area_id)))
      : riesgos;
    
    return {
      risks: rs.length,
      validated: rs.filter(r => r.status === 'Validado').length,
      review: rs.filter(r => r.status === 'En revisión').length,
      returned: rs.filter(r => r.status === 'Devuelto con observaciones').length,
      q: {
        QI: rs.filter(r => cuadrante(r.probabilidad, r.impacto) === 'QI').length,
        QII: rs.filter(r => cuadrante(r.probabilidad, r.impacto) === 'QII').length,
        QIII: rs.filter(r => cuadrante(r.probabilidad, r.impacto) === 'QIII').length,
        QIV: rs.filter(r => cuadrante(r.probabilidad, r.impacto) === 'QIV').length,
      }
    };
  };

  const s = getStats();

  if (loading) {
    return <div className="notice">Cargando tablero...</div>;
  }

  return (
    <>
      <div className="page-head">
        <div>
          <h1>{isAdmin ? 'Tablero institucional' : 'Mis áreas'}</h1>
          <p>{isAdmin ? 'Visión consolidada de todas las Unidades Responsables.' : 'Información limitada a las áreas que tienes asignadas.'}</p>
        </div>
      </div>
      
      {String(ejercicio) === '2026' && (
        <div className="notice">
          <strong>Ejercicio 2026 – Implementación:</strong> T1, T2 y T3 corresponden a integración inicial retrospectiva enero–septiembre; T4 operará como seguimiento ordinario.
        </div>
      )}

      <div className="kpis">
        <div className="kpi"><span>Áreas</span><b>{areas.length}</b></div>
        <div className="kpi"><span>Riesgos</span><b>{s.risks}</b></div>
        <div className="kpi"><span>Validados</span><b>{s.validated}</b></div>
        <div className="kpi"><span>En revisión</span><b>{s.review}</b></div>
        <div className="kpi"><span>Devueltos</span><b>{s.returned}</b></div>
      </div>

      <div className="grid-2">
        <section className="panel">
          <h2>Avance por área</h2>
          <div className="area-cards">
            {areas.map(a => {
              const x = getStats([String(a.unidad_responsable_gasto_id || a.id_unidad || a.id)]);
              const pct = x.risks ? Math.round((x.validated / x.risks) * 100) : 0;
              return (
                <button 
                  key={a.unidad_responsable_gasto_id || a.id_unidad || a.id || a.nombre} 
                  className="area-card" 
                  onClick={() => navigate('/riesgos')} // Ideally, pass areaId via state/context, or rely on Risks page defaults
                >
                  <b>{a.nombre || a.denominacion}</b>
                  <span>{x.validated}/{x.risks} riesgos validados</span>
                  <div className="bar"><i style={{ width: `${pct}%` }}></i></div>
                  <small>{pct}%</small>
                </button>
              );
            })}
          </div>
        </section>

        <section className="panel">
          <h2>Distribución por cuadrante</h2>
          <div className="quadrant-mini">
            <div className="q2">QII <b>{s.q.QII}</b></div>
            <div className="q1">QI <b>{s.q.QI}</b></div>
            <div className="q3">QIII <b>{s.q.QIII}</b></div>
            <div className="q4">QIV <b>{s.q.QIV}</b></div>
          </div>
          <p className="muted">La ubicación se recalcula automáticamente cuando cambian probabilidad o impacto.</p>
        </section>
      </div>
    </>
  );
}
