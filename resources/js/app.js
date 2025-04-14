import './bootstrap';
import './main'; // NOTE: add new main.js

import.meta.glob ([
    '../images/**',
    '../fonts/**',
])

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
