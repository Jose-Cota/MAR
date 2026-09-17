import { NavLink } from 'react-router-dom';

export default function NavbarVertical() {
  return (
    <div className="sidebar">
      <nav>
        <NavLink to="/" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
          ⌂ <span>Tablero general</span>
        </NavLink>
        <NavLink to="/poa" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
          ▦ <span>POA</span>
        </NavLink>
        <NavLink to="/riesgos" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
          ▦ <span>Riesgos / MAR</span>
        </NavLink>
        <NavLink to="/controles" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
          ▣ <span>Controles</span>
        </NavLink>
        <NavLink to="/indicadores" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
          ▤ <span>Indicadores</span>
        </NavLink>
        <NavLink to="/seguimiento" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
          ↗ <span>Seguimiento</span>
        </NavLink>
        <NavLink to="/reportes" className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}>
          ▥ <span>Reportes</span>
        </NavLink>
        {/* We can add other routes here later as needed */}
      </nav>
      <div className="side-footer">
        Versión autónoma 2.1<br />
        <small>Riesgos / MAR con edición y trazabilidad</small>
      </div>
    </div>
  );
}
