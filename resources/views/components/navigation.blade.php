@props(['currentPage' => ''])

<nav class="bg-white border-b border-gray-100 px-6 py-4">
    <div class="max-w-6xl mx-auto flex items-center justify-between">
        <!-- Logo -->
        <div class="flex items-center">
            <a href="/" class="flex items-center space-x-3 group">
                <!-- B Icon Block -->
                <div
                    class="w-10 h-10 bg-black text-white flex items-center justify-center rounded-lg font-bold text-sm tracking-tight group-hover:bg-gray-800 transition-colors">
                    B
                </div>
                <!-- Logo Text -->
                <span class="text-xl font-bold text-black tracking-tight">
                    Bluntly
                </span>
            </a>
        </div>

        @if ($currentPage === 'home')
            <!-- Full Navigation Menu for Home Page -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="{{ route('feed') }}" class="text-sm text-gray-600 hover:text-black transition-colors">Read Stories</a>
                <a href="{{ route('post.create') }}" class="text-sm text-gray-600 hover:text-black transition-colors">Post
                    Anonymously</a>
                <a href="{{ route('rules') }}" class="text-sm text-gray-600 hover:text-black transition-colors">Rules &
                    Guidelines</a>
                <a href="{{ route('terms') }}" class="text-sm text-gray-600 hover:text-black transition-colors">Terms of Use</a>
                <a href="{{ route('privacy') }}" class="text-sm text-gray-600 hover:text-black transition-colors">Privacy Policy</a>
                <a href="{{ route('about') }}" class="text-sm text-gray-600 hover:text-black transition-colors">About Us</a>
            </div>

            <!-- Mobile Menu Button for Home -->
            <div class="md:hidden">
                <button class="text-gray-600 hover:text-black" onclick="toggleMobileMenu()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        @else
            <!-- Medium-style Navigation for Other Pages -->
            <div class="flex items-center space-x-6">
                <!-- Search -->
                <div class="hidden md:flex items-center">
                    <div class="relative">
                        <input type="text" placeholder="Search stories..."
                            class="bg-gray-50 border border-gray-200 rounded-full px-4 py-2 pl-10 text-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Write Button -->
                <a href="{{ route('post.create') }}"
                    class="bg-black text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-gray-800 transition-colors">
                    Write
                </a>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button class="text-gray-600 hover:text-black" onclick="toggleMobileMenu()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @endif
    </div>

    @if ($currentPage === 'home')
        <!-- Full Mobile Menu for Home Page -->
        <div id="mobile-menu" class="hidden md:hidden mt-4 pb-4 border-t border-gray-100">
            <div class="max-w-6xl mx-auto px-6">
                <div class="flex flex-col space-y-4 pt-4">
                    <a href="{{ route('feed') }}" class="text-sm text-gray-600">Read Stories</a>
                    <a href="{{ route('post.create') }}" class="text-sm text-gray-600">Post Anonymously</a>
                    <a href="{{ route('rules') }}" class="text-sm text-gray-600">Rules & Guidelines</a>
                    <a href="{{ route('terms') }}" class="text-sm text-gray-600">Terms of Use</a>
                    <a href="{{ route('privacy') }}" class="text-sm text-gray-600">Privacy Policy</a>
                    <a href="{{ route('about') }}" class="text-sm text-gray-600">About Us</a>
                </div>
            </div>
        </div>
    @else
        <!-- Simple Mobile Menu for Other Pages -->
        <div id="mobile-menu" class="hidden md:hidden mt-4 pb-4 border-t border-gray-100">
            <div class="max-w-6xl mx-auto px-6">
                <div class="flex flex-col space-y-4 pt-4">
                    <div class="relative">
                        <input type="text" placeholder="Search stories..."
                            class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 pl-10 text-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <a href="{{ route('post.create') }}"
                        class="bg-black text-white px-4 py-2 rounded-lg text-sm font-medium text-center">
                        Write a Story
                    </a>
                </div>
            </div>
        </div>
    @endif
</nav>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    }
</script>
