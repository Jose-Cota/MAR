import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from '../../utils/axios';
import useGlobalStore from '../../stores/useGlobalStore';

export default function RiesgoEditorPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const isNew = !id;
  const { ejercicio } = useGlobalStore();

  const [formData, setFormData] = useState({
    area_id: '',
    ejercicio_id: new Date().getFullYear(),
    objetivo: '',
    riesgo: '',
    probabilidad: '',
    impacto: '',
    status: 'Borrador',
    controles: [],
    indicadores: [],
    actividades: []
  });

  const [areas, setAreas] = useState([]);

  useEffect(() => {
    fetchAreas();
    if (!isNew) {
      fetchRiesgo();
    }
  }, [id]);

  const fetchAreas = async () => {
    try {
      const res = await axios.get('/unidades-responsables');
      setAreas(res.data.data || res.data);
    } catch (e) {
      console.error(e);
    }
  };

  const fetchRiesgo = async () => {
    try {
      const res = await axios.get(`/riesgos/${id}`);
      setFormData({
        ...res.data,
        actividades: res.data.actividades.map(a => a.id_actividad || a.id) // Map this depending on backend structure
      });
    } catch (e) {
      console.error(e);
    }
  };

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      if (isNew) {
        await axios.post('/riesgos', formData);
      } else {
        await axios.put(`/riesgos/${id}`, formData);
      }
      navigate('/riesgos');
    } catch (error) {
      console.error(error);
      alert('Error guardando el riesgo');
    }
  };

  return (
    <>
      <div className="page-head">
        <div>
          <h1>{isNew ? 'Agregar riesgo' : 'Editar riesgo'}</h1>
          <p>La información capturada alimenta el Mapa, la MAR y los anexos derivados.</p>
        </div>
        <button className="btn" onClick={() => navigate('/riesgos')}>Volver</button>
      </div>

      <form onSubmit={handleSubmit}>
        <section className="panel">
          <h2>Identificación y alineación</h2>
          <div className="form-grid">
            <label>
              Ejercicio
              <input className="input" name="ejercicio_id" value={formData.ejercicio_id} readOnly />
            </label>
            <label>
              Área
              <select className="input" name="area_id" value={formData.area_id} onChange={handleChange} required>
                <option value="">Seleccionar...</option>
                {areas.map(a => (
                  <option key={a.unidad_responsable_gasto_id || a.id_unidad || a.id} value={a.unidad_responsable_gasto_id || a.id_unidad || a.id}>{a.nombre || a.denominacion}</option>
                ))}
              </select>
            </label>
          </div>
        </section>

        <section className="panel">
          <h2>Objetivo, riesgo y efectos</h2>
          <label>
            Objetivo
            <textarea className="input" name="objetivo" value={formData.objetivo} onChange={handleChange} required />
          </label>
          <label>
            Riesgo
            <textarea className="input" name="riesgo" value={formData.riesgo} onChange={handleChange} required />
          </label>
        </section>

        <section className="panel">
          <h2>Valoración</h2>
          <div className="form-grid">
            <label>
              Probabilidad 0–10
              <input type="number" className="input" name="probabilidad" value={formData.probabilidad} onChange={handleChange} min="0" max="10" required />
            </label>
            <label>
              Impacto 0–10
              <input type="number" className="input" name="impacto" value={formData.impacto} onChange={handleChange} min="0" max="10" required />
            </label>
          </div>
        </section>

        <section className="panel">
          <div className="actions">
            <button className="btn primary" type="submit">Guardar cambios</button>
          </div>
        </section>
      </form>
    </>
  );
}
