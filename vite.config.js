// vite.config.js
import { defineConfig } from "vite"
import { resolve } from 'path'

export default defineConfig({
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
