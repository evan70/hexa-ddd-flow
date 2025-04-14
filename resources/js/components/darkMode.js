// Dark mode functionality
const darkMode = {
    init() {
        // Check for saved theme preference or use the system preference
        const isDarkMode = localStorage.getItem('darkMode') === 'true' || 
            (localStorage.getItem('darkMode') === null && 
             window.matchMedia('(prefers-color-scheme: dark)').matches);
        
        // Set initial theme
        if (isDarkMode) {
            document.documentElement.classList.add('dark');
        }
        
        // Setup toggle functionality
        const darkModeToggle = document.getElementById('darkModeToggle');
        if (darkModeToggle) {
            // Update toggle state
            this.updateToggleState(isDarkMode);
            
            // Add click event listener
            darkModeToggle.addEventListener('click', () => {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('darkMode', isDark.toString());
                this.updateToggleState(isDark);
            });
        }
    },
    
    // Update the toggle button state
    updateToggleState(isDark) {
        const darkModeToggle = document.getElementById('darkModeToggle');
        const darkIcon = document.getElementById('darkIcon');
        const lightIcon = document.getElementById('lightIcon');
        
        if (darkModeToggle && darkIcon && lightIcon) {
            if (isDark) {
                darkIcon.classList.add('hidden');
                lightIcon.classList.remove('hidden');
                darkModeToggle.setAttribute('aria-checked', 'true');
            } else {
                darkIcon.classList.remove('hidden');
                lightIcon.classList.add('hidden');
                darkModeToggle.setAttribute('aria-checked', 'false');
            }
        }
    }
};

export default darkMode;
