// vite.config.js
import { defineConfig } from "vite"
import { resolve } from 'path'

export default defineConfig({
  server: {
    // Nastaví port pre Vite dev server
    port: 5173,
    // Nastaví, že Vite bude počúvať na všetkých rozhraniach
    host: '0.0.0.0',
    // Nastaví, že Vite bude používať HTTPS
    https: false,
    // Nastaví, že Vite bude automaticky otvárať prehliadač
    open: false,
    // Nastaví, že Vite bude používať HMR
    hmr: {
      // Nastaví, že HMR bude fungovať aj cez proxy
      clientPort: 5173,
      host: 'localhost',
    },
    // Nastaví, že Vite bude používať CORS
    cors: true,
    // Nastaví, že Vite bude používať proxy pre PHP server
    proxy: {
      // Keď sa pristupuje na /build, presmeruje na Vite dev server
      '/build': {
        target: 'http://localhost:5173',
        changeOrigin: true,
      }
    }
  },
  build: {
    outDir: 'public/build',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        app: resolve(__dirname, 'resources/js/app.js'),
        css: resolve(__dirname, 'resources/css/app.css'),
        sass: resolve(__dirname, 'resources/sass/main.sass')
      },
      output: {
        assetFileNames: 'assets/[name]-[hash][extname]',
        entryFileNames: 'js/[name]-[hash].js'
      }
    }
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources')
    }
  }
})
