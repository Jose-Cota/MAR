import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import axios from '../utils/axios';

const useGlobalStore = create(
  persist(
    (set) => ({
      ejercicio: 2026,
      etapasActivas: [],
      settings: {},
      fetchEtapasActivas: async () => {
        try {
          const res = await axios.get('/etapas-activas');
          set({ etapasActivas: res.data.etapas || [] });
        } catch (error) {
          console.error('Error fetching etapas activas', error);
          set({ etapasActivas: [] });
        }
      },
      fetchSettings: async () => {
        try {
          const res = await axios.get('/settings');
          set({ settings: res.data.data || {} });
        } catch (error) {
          console.error('Error fetching settings', error);
          set({ settings: {} });
        }
      },
    }),
    {
      name: 'poa-global-storage',
      partialize: (state) => ({}), // Do not persist anything; always fetch fresh on load
    }
  )
);

export default useGlobalStore;
