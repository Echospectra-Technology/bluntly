import Alpine from 'alpinejs'
import persist from '@alpinejs/persist'

Alpine.plugin(persist)

window.Alpine = Alpine

// Dark mode store
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

Alpine.start()
