<!-- Toast Container -->
<div x-data="{ 
    toasts: [],
    addToast(message, type = 'success', duration = 3000) {
        const id = Date.now() + Math.random();
        const toast = { id, message, type, duration };
        this.toasts.push(toast);
        
        setTimeout(() => {
            this.removeToast(id);
        }, duration);
    },
    removeToast(id) {
        this.toasts = this.toasts.filter(toast => toast.id !== id);
    }
}" 
@toast-show.window="addToast($event.detail.message, $event.detail.type, $event.detail.duration)"
class="fixed top-4 right-4 z-50 space-y-2">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="true" 
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
             x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto flex ring-1 ring-black ring-opacity-5">
            <div class="flex-1 w-0 p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <!-- Success Icon -->
                        <svg x-show="toast.type === 'success'" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <!-- Error Icon -->
                        <svg x-show="toast.type === 'error'" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <!-- Warning Icon -->
                        <svg x-show="toast.type === 'warning'" class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <!-- Info Icon -->
                        <svg x-show="toast.type === 'info'" class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900" x-text="toast.message"></p>
                    </div>
                </div>
            </div>
            <div class="flex border-l border-gray-200">
                <button @click="removeToast(toast.id)" 
                        class="w-full border border-transparent rounded-none rounded-r-lg p-4 flex items-center justify-center text-sm font-medium text-gray-600 hover:text-gray-500 focus:outline-none">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </template>
</div>

<!-- Alpine.js Toast Helper Script -->
<script>
    window.showToast = function(message, type = 'success', duration = 3000) {
        window.dispatchEvent(new CustomEvent('toast-show', {
            detail: { message, type, duration }
        }));
    };
</script>