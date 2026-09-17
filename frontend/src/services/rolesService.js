import axios from '../utils/axios';

export const rolesService = {
  getRolesAndPermissions: async () => {
    const response = await axios.get('/roles');
    return response.data;
  },

  updateRolePermissions: async (roleId, permissions) => {
    const response = await axios.put(`/roles/${roleId}`, { permissions });
    return response.data;
  }
};
