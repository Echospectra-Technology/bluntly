import Alpine from 'alpinejs'
import persist from '@alpinejs/persist'

// Register persist plugin first
Alpine.plugin(persist)

// Dark mode store
document.addEventListener('alpine:init', () => {
    Alpine.store('darkMode', {
        on: Alpine.$persist(false).as('darkMode'),

        init() {
            // Check system preference if no stored preference
            if (localStorage.getItem('_x_darkMode') === null) {
                this.on = window.matchMedia('(prefers-color-scheme: dark)').matches
            }
            this.updateDOM()
        },

        toggle() {
            this.on = !this.on
            this.updateDOM()
        },

        updateDOM() {
            if (this.on) {
                document.documentElement.classList.add('dark')
            } else {
                document.documentElement.classList.remove('dark')
            }
        }
    })
})

// Make Alpine available globally
window.Alpine = Alpine

// Start Alpine
Alpine.start()
