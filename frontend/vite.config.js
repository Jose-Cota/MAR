import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    host: true,
    port: 5200,
    strictPort: true,
    proxy: {
      '/api': {
        target: 'http://localhost:8200',
        changeOrigin: true,
      },
    },
  },
  preview: {
    host: true,
    port: 5200,
    strictPort: true,
  },
})
