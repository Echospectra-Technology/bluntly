// Manually bundle Livewire and Alpine
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

// Make Alpine globally available
window.Alpine = Alpine;

// Dark mode store - Livewire's Alpine already includes persist plugin
// We need to register the store BEFORE calling Livewire.start()
document.addEventListener('alpine:init', () => {
    Alpine.store('darkMode', {
        on: Alpine.$persist(false).as('darkMode'),

        init() {
            // Check system preference if no stored preference
            if (localStorage.getItem('_x_darkMode') === null) {
                this.on = window.matchMedia('(prefers-color-scheme: dark)').matches;
            }
            this.updateDOM();
        },

        toggle() {
            this.on = !this.on;
            this.updateDOM();
        },

        updateDOM() {
            if (this.on) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    });

    // Initialize dark mode
    Alpine.store('darkMode').init();
});

// Start Livewire (this also starts Alpine and triggers alpine:init)
Livewire.start();
