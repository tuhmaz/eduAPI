import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { glob } from 'glob';
import path from 'path';
import viteCompression from 'vite-plugin-compression';

/**
 * Get Files from a directory
 * @param {string} query
 * @returns array
 */
function GetFilesArray(query) {
  return Array.from(new Set(glob.sync(query))); // Remove duplicate files by using Set initially
}

// File paths to be collected
const fileQueries = {
  pageJsFiles: 'resources/assets/js/*.js',
  vendorJsFiles: 'resources/assets/vendor/js/*.js',
  libsJsFiles: 'resources/assets/vendor/libs/**/*.js',
  coreScssFiles: 'resources/assets/vendor/scss/**/!(_)*.scss',
  libsScssFiles: 'resources/assets/vendor/libs/**/!(_)*.scss',
  libsCssFiles: 'resources/assets/vendor/libs/**/*.css',
  fontsScssFiles: 'resources/assets/vendor/fonts/!(_)*.scss',
  customJsFiles: 'resources/js/*.js',
  customCssFiles: 'resources/css/*.css',
  assetsCssFiles: 'resources/assets/css/*.css'  // Added assets CSS files
};

// Collect all files
function collectInputFiles() {
  const inputFiles = [];
  for (const key in fileQueries) {
    const files = GetFilesArray(fileQueries[key]);
    inputFiles.push(...files);
  }
  return [
    'resources/assets/css/edu.css',  // Explicitly include edu.css
    'resources/css/app.css',
    'resources/css/monitoring.css',
    'resources/css/security-logs.css',
    'resources/js/app.js',
    'resources/js/monitoring.js',
    'resources/assets/js/security-logs.js',
    ...inputFiles
  ];
}

export default defineConfig({
  plugins: [
    laravel({
      input: [
        ...collectInputFiles(),
        'node_modules/select2/dist/js/select2.min.js',
        'node_modules/flatpickr/dist/flatpickr.min.js',
        'node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js',
        'node_modules/select2/dist/css/select2.min.css',
        'node_modules/flatpickr/dist/flatpickr.min.css',
        'node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css'
      ],
      refresh: true
    }),
    viteCompression()
  ],
  resolve: {
    alias: {
      '~': path.resolve(__dirname, 'node_modules'),
      '@': path.resolve(__dirname, 'resources')
    }
  },
  build: {
    chunkSizeWarningLimit: 1600,
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['jquery', 'select2', 'flatpickr', 'datatables.net-bs5']
        }
      }
    }
  }
});
