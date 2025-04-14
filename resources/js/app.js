import './bootstrap';
import './main'; // NOTE: add new main.js

// Import SVG icons directly
import arrowIcon from '@/images/icons/dropdown-arrow.svg';

// Example of using imported SVG
const createSvgIcon = () => {
    const iconContainer = document.createElement('div');
    iconContainer.innerHTML = `
        <h3>Dynamically imported SVG icon:</h3>
        <img src="${arrowIcon}" alt="Dropdown Arrow" />
    `;
    return iconContainer;
};

// Example of using SVG from public directory
const createPublicSvgIcon = () => {
    const iconContainer = document.createElement('div');
    iconContainer.innerHTML = `
        <h3>SVG from public directory:</h3>
        <img src="/images/icons/dropdown-arrow.svg" alt="Dropdown Arrow" />
    `;
    return iconContainer;
};

// Add icons to the page when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('#app') || document.body;
    container.appendChild(createSvgIcon());
    container.appendChild(createPublicSvgIcon());
});

// Import all images and fonts
import.meta.glob ([
    '../images/**',
    '../fonts/**',
])

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
