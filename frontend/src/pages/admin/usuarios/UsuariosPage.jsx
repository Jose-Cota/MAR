import { useState, useEffect } from 'react';
import { usuariosService } from '../../../services/usuariosService';
import UsuarioFormModal from './UsuarioFormModal';
import PasswordChangeModal from './PasswordChangeModal';

export default function UsuariosPage() {
  const [page, setPage] = useState(0);
  const rowsPerPage = 15;
  const [search, setSearch] = useState('');
  const [usuarios, setUsuarios] = useState([]);
  const [loading, setLoading] = useState(false);

  const [formOpen, setFormOpen] = useState(false);
  const [passwordOpen, setPasswordOpen] = useState(false);
  const [selectedUsuario, setSelectedUsuario] = useState(null);

  const [deleteOpen, setDeleteOpen] = useState(false);
  const [usuarioToDelete, setUsuarioToDelete] = useState(null);
  const [toggleConfirm, setToggleConfirm] = useState(null);

  useEffect(() => { fetchUsuarios(); }, []);

  const fetchUsuarios = async () => {
    setLoading(true);
    try {
      const data = await usuariosService.getUsuarios();
      setUsuarios(data);
    } catch (error) {
      console.error('Error fetching usuarios:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleOpenForm = (usuario = null) => {
    setSelectedUsuario(usuario);
    setFormOpen(true);
  };

  const handleOpenPassword = (usuario) => {
    setSelectedUsuario(usuario);
    setPasswordOpen(true);
  };

  const handleToggleActive = async () => {
    if (!toggleConfirm) return;
    try {
      await usuariosService.toggleActive(toggleConfirm.usuario_poa_id);
      fetchUsuarios();
    } catch (error) {
      console.error('Error toggling active status:', error);
    } finally {
      setToggleConfirm(null);
    }
  };

  const handleConfirmDelete = async () => {
    if (!usuarioToDelete) return;
    try {
      await usuariosService.forceDelete(usuarioToDelete.usuario_poa_id);
      fetchUsuarios();
    } catch (error) {
      console.error('Error deleting user:', error);
    } finally {
      setDeleteOpen(false);
      setUsuarioToDelete(null);
    }
  };

  const filteredUsuarios = usuarios.filter((u) => {
    const q = search.toLowerCase();
    return (
      (u.nombre && u.nombre.toLowerCase().includes(q)) ||
      (u.apellido_paterno && u.apellido_paterno.toLowerCase().includes(q)) ||
      (u.usuario && u.usuario.toLowerCase().includes(q)) ||
      (u.area_nombre && u.area_nombre.toLowerCase().includes(q)) ||
      (u.unidades_responsables && u.unidades_responsables.some(ur => ur.nombre?.toLowerCase().includes(q)))
    );
  });

  const paginated = filteredUsuarios.slice(page * rowsPerPage, (page + 1) * rowsPerPage);
  const totalPages = Math.ceil(filteredUsuarios.length / rowsPerPage);

  const roleColor = (roleName) => {
    if (!roleName) return {};
    if (roleName.includes('Super')) return { background: '#dbeafe', color: '#1e40af' };
    if (roleName.includes('Administrador')) return { background: '#ede9fe', color: '#5b21b6' };
    if (roleName.includes('Validador')) return { background: '#d1fae5', color: '#065f46' };
    if (roleName.includes('Capturista')) return { background: '#fef3c7', color: '#92400e' };
    return { background: '#e7f0f8', color: '#174f7d' };
  };

  return (
    <>
      <div className="page-head">
        <div>
          <h1>Usuarios y Permisos</h1>
          <p>Gestión de accesos, roles y unidades responsables asignadas.</p>
        </div>
        <button className="btn primary" onClick={() => handleOpenForm()}>
          + Nuevo Usuario
        </button>
      </div>

      <section className="panel" style={{ padding: '0' }}>
        {/* Toolbar */}
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '14px 18px', borderBottom: '1px solid var(--line)' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
            <span style={{ color: 'var(--muted)', fontSize: '13px' }}>Total:</span>
            <strong>{filteredUsuarios.length}</strong>
            <span style={{ color: 'var(--muted)', fontSize: '13px' }}>usuarios</span>
          </div>
          <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
            <span style={{ color: 'var(--muted)', fontSize: '13px' }}>Buscar:</span>
            <input
              className="input"
              style={{ width: '240px', padding: '7px 10px' }}
              value={search}
              onChange={e => { setSearch(e.target.value); setPage(0); }}
              placeholder="Nombre, usuario o área…"
            />
          </div>
        </div>

        {/* Tabla */}
        <div style={{ overflowX: 'auto' }}>
          <table>
            <thead>
              <tr>
                <th style={{ width: '18%' }}>Nombre</th>
                <th style={{ width: '10%' }}>Usuario</th>
                <th style={{ width: '20%' }}>Área / URG</th>
                <th style={{ width: '15%' }}>Responsable Operativo</th>
                <th style={{ width: '12%' }}>Rol</th>
                <th style={{ width: '15%' }}>Permisos adicionales</th>
                <th style={{ width: '6%' }}>Estado</th>
                <th style={{ width: '4%' }}></th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr><td colSpan="8" className="muted" style={{ textAlign: 'center', padding: '24px' }}>Cargando...</td></tr>
              ) : paginated.length === 0 ? (
                <tr><td colSpan="8" className="muted" style={{ textAlign: 'center', padding: '24px' }}>No se encontraron registros</td></tr>
              ) : paginated.map((row) => (
                <tr key={row.usuario_poa_id}>
                  {/* Nombre */}
                  <td>
                    <div style={{ fontWeight: 600 }}>{row.nombre} {row.apellido_paterno} {row.apellido_materno}</div>
                    {row.correo && <div style={{ fontSize: '11px', color: 'var(--muted)' }}>{row.correo}</div>}
                  </td>

                  {/* Usuario */}
                  <td style={{ fontFamily: 'monospace', fontSize: '12px' }}>{row.usuario}</td>

                  {/* Área / URG */}
                  <td style={{ fontSize: '12px' }}>
                    {row.unidades_responsables?.length > 0 ? (
                      <div style={{ display: 'flex', flexDirection: 'column', gap: '3px' }}>
                        {row.unidades_responsables.map(ur => (
                          <span key={ur.unidad_responsable_gasto_id} className="chip" style={{ fontSize: '11px' }}>
                            {ur.numero} — {ur.nombre}
                          </span>
                        ))}
                      </div>
                    ) : (
                      <span className="muted" style={{ fontSize: '11px', fontStyle: 'italic' }}>Sin UR asignada</span>
                    )}
                  </td>

                  {/* RO */}
                  <td style={{ fontSize: '12px' }}>
                    {row.responsables_operativos?.length > 0 ? (
                      <div style={{ display: 'flex', flexDirection: 'column', gap: '3px' }}>
                        {row.responsables_operativos.map(ro => (
                          <span key={ro.responsable_operativo_id} style={{ fontSize: '11px', color: 'var(--text)' }}>
                            {ro.urg_numero ? `${ro.urg_numero}.` : ''}{ro.numero} — {ro.nombre}
                          </span>
                        ))}
                      </div>
                    ) : (
                      <span className="muted" style={{ fontSize: '11px', fontStyle: 'italic' }}>Sin RO</span>
                    )}
                  </td>

                  {/* Rol */}
                  <td>
                    <div style={{ display: 'flex', flexWrap: 'wrap', gap: '4px' }}>
                      {(row.roles || []).map(rolName => (
                        <span
                          key={rolName}
                          className="chip"
                          style={{ fontSize: '11px', fontWeight: 700, ...roleColor(rolName) }}
                        >
                          {rolName}
                        </span>
                      ))}
                      {(!row.roles || row.roles.length === 0) && (
                        <span className="muted" style={{ fontSize: '11px', fontStyle: 'italic' }}>Sin rol</span>
                      )}
                    </div>
                  </td>

                  {/* Permisos */}
                  <td style={{ fontSize: '11px' }}>
                    {row.unidades_responsables?.length > 0 ? (
                      <div style={{ display: 'flex', flexDirection: 'column', gap: '3px' }}>
                        {row.unidades_responsables.map(ur => (
                          <div key={ur.unidad_responsable_gasto_id} style={{ display: 'flex', alignItems: 'center', gap: '4px' }}>
                            <span style={{ color: 'var(--muted)' }}>{ur.numero}</span>
                            <span style={{ fontSize: '10px', background: '#edf5fb', color: '#174f7d', borderRadius: '10px', padding: '1px 6px' }}>
                              {(row.roles || []).join(', ') || 'Usuario'}
                            </span>
                          </div>
                        ))}
                      </div>
                    ) : (
                      <span className="muted" style={{ fontStyle: 'italic' }}>—</span>
                    )}
                  </td>

                  {/* Estado */}
                  <td>
                    <span
                      className="chip"
                      style={{
                        fontWeight: 700,
                        fontSize: '11px',
                        background: row.activo === 1 ? '#d1fae5' : '#fee2e2',
                        color: row.activo === 1 ? '#065f46' : '#991b1b',
                      }}
                    >
                      {row.activo === 1 ? 'Activo' : 'Inactivo'}
                    </span>
                  </td>

                  {/* Acciones */}
                  <td style={{ whiteSpace: 'nowrap', textAlign: 'right' }}>
                    <button className="icon-btn" title="Editar" onClick={() => handleOpenForm(row)}>✏️</button>
                    <button className="icon-btn" title="Cambiar contraseña" onClick={() => handleOpenPassword(row)}>🔑</button>
                    <button
                      className="icon-btn"
                      title={row.activo === 1 ? 'Deshabilitar' : 'Habilitar'}
                      style={{ color: row.activo === 1 ? '#b7791f' : '#2d7d46' }}
                      onClick={() => setToggleConfirm(row)}
                    >
                      {row.activo === 1 ? '🚫' : '✅'}
                    </button>
                    <button className="icon-btn" title="Eliminar" style={{ color: '#ad2e24' }} onClick={() => { setUsuarioToDelete(row); setDeleteOpen(true); }}>🗑️</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {/* Paginación */}
        {totalPages > 1 && (
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '12px 18px', borderTop: '1px solid var(--line)', fontSize: '13px' }}>
            <span className="muted">
              Mostrando {page * rowsPerPage + 1}–{Math.min((page + 1) * rowsPerPage, filteredUsuarios.length)} de {filteredUsuarios.length}
            </span>
            <div style={{ display: 'flex', gap: '6px' }}>
              <button className="btn" disabled={page === 0} onClick={() => setPage(p => p - 1)}>‹ Anterior</button>
              <button className="btn" disabled={page >= totalPages - 1} onClick={() => setPage(p => p + 1)}>Siguiente ›</button>
            </div>
          </div>
        )}
      </section>

      {/* Modal editar/crear */}
      {formOpen && (
        <UsuarioFormModal
          open={formOpen}
          onClose={() => setFormOpen(false)}
          usuario={selectedUsuario}
          onSuccess={() => { fetchUsuarios(); setFormOpen(false); }}
        />
      )}

      {/* Modal contraseña */}
      {passwordOpen && (
        <PasswordChangeModal
          open={passwordOpen}
          onClose={() => setPasswordOpen(false)}
          usuario={selectedUsuario}
          onSuccess={() => setPasswordOpen(false)}
        />
      )}

      {/* Modal confirmar toggle activo */}
      {toggleConfirm && (
        <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.45)', zIndex: 50, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
          <div style={{ background: '#fff', borderRadius: '12px', padding: '28px 32px', maxWidth: '420px', width: '90%', boxShadow: '0 20px 60px rgba(0,0,0,0.25)' }}>
            <h3 style={{ margin: '0 0 12px', color: 'var(--navy)' }}>
              {toggleConfirm.activo === 1 ? '🚫 Deshabilitar usuario' : '✅ Habilitar usuario'}
            </h3>
            <p style={{ color: 'var(--muted)', margin: '0 0 20px' }}>
              ¿Estás seguro de que deseas {toggleConfirm.activo === 1 ? 'deshabilitar' : 'habilitar'} al usuario <strong>{toggleConfirm.usuario}</strong>?
            </p>
            <div style={{ display: 'flex', gap: '10px', justifyContent: 'flex-end' }}>
              <button className="btn" onClick={() => setToggleConfirm(null)}>Cancelar</button>
              <button
                className="btn primary"
                style={{ background: toggleConfirm.activo === 1 ? '#b7791f' : '#2d7d46', borderColor: toggleConfirm.activo === 1 ? '#b7791f' : '#2d7d46' }}
                onClick={handleToggleActive}
              >
                {toggleConfirm.activo === 1 ? 'Sí, deshabilitar' : 'Sí, habilitar'}
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Modal confirmar eliminar */}
      {deleteOpen && (
        <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.45)', zIndex: 50, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
          <div style={{ background: '#fff', borderRadius: '12px', padding: '28px 32px', maxWidth: '420px', width: '90%', boxShadow: '0 20px 60px rgba(0,0,0,0.25)' }}>
            <h3 style={{ margin: '0 0 12px', color: '#ad2e24' }}>🗑️ Eliminar usuario</h3>
            <p style={{ color: 'var(--muted)', margin: '0 0 8px' }}>
              ¿Estás seguro de que deseas eliminar permanentemente al usuario <strong>{usuarioToDelete?.usuario}</strong>?
            </p>
            <p style={{ color: 'var(--danger)', fontSize: '13px', margin: '0 0 20px' }}>
              Esta acción no se puede deshacer y eliminará todos sus permisos y asignaciones.
            </p>
            <div style={{ display: 'flex', gap: '10px', justifyContent: 'flex-end' }}>
              <button className="btn" onClick={() => setDeleteOpen(false)}>Cancelar</button>
              <button className="btn primary" style={{ background: '#ad2e24', borderColor: '#ad2e24' }} onClick={handleConfirmDelete}>
                Sí, eliminar
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
}
