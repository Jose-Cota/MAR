import axios from '../utils/axios';

const endpoints = {
  list: '/usuarios',
  create: '/usuarios',
  update: (id) => `/usuarios/${id}`,
  delete: (id) => `/usuarios/${id}`,
  changePassword: (id) => `/usuarios/${id}/password`,
};

export const usuariosService = {
  /**
   * Get list of all users
   */
  getUsuarios: async () => {
    const response = await axios.get(endpoints.list);
    return response.data;
  },

  /**
   * Create new user
   */
  createUsuario: async (data) => {
    const response = await axios.post(endpoints.create, data);
    return response.data;
  },

  /**
   * Update existing user
   */
  updateUsuario: async (id, data) => {
    const response = await axios.put(endpoints.update(id), data);
    return response.data;
  },

  /**
   * Toggle user active status
   */
  toggleActive: async (id) => {
    const response = await axios.delete(endpoints.delete(id));
    return response.data;
  },

  /**
   * Permanently delete user
   */
  forceDelete: async (id) => {
    const response = await axios.delete(`/usuarios/${id}/force`);
    return response.data;
  },

  /**
   * Update user password
   */
  updatePassword: async (id, passwords) => {
    const response = await axios.put(endpoints.changePassword(id), passwords);
    return response.data;
  }
};
