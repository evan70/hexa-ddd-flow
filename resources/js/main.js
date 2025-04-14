import.meta.glob([
    '../images/**',
    '../fonts/**',
]);

import '../css/app.css';
import { gsap } from 'gsap';

// Lazy load components
const loadComponent = async (name) => {
  return import(`./components/${name}.js`)
    .then(module => module.default)
    .catch(() => {
      console.warn(`Failed to load component: ${name}`);
      return null;
    });
};

// Initialize components
document.addEventListener('DOMContentLoaded', async () => {
  // Load and initialize dark mode
  const darkMode = await loadComponent('darkMode');
  if (darkMode) darkMode.init();
  
  // Load and initialize animations
  const animations = await loadComponent('animations');
  if (animations) animations.init();
  
  // Simple header animation without loading the full animations component
  if (!animations) {
    gsap.from('header', { opacity: 0, y: -20, duration: 0.8 });
  }
  
  // Log initialization
  console.log('Application initialized with lazy loading');
});
