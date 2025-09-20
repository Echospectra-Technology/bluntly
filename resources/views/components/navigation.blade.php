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

        <!-- Consistent Navigation for All Pages -->
        <div class="flex items-center space-x-6">
            <!-- Navigation Links -->
            <div class="hidden lg:flex items-center space-x-4">
                <a href="{{ route('feed') }}"
                    class="text-sm text-gray-600 hover:text-black transition-colors {{ $currentPage === 'stories' ? 'font-medium text-black' : '' }}">Stories</a>
                <a href="{{ route('themes') }}"
                    class="text-sm text-gray-600 hover:text-black transition-colors {{ $currentPage === 'themes' ? 'font-medium text-black' : '' }}">Themes</a>
            </div>

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

            <!-- Authentication Buttons -->
            @inject('authService', 'App\Services\AnonymousAccountService')
            @if ($authService->isLoggedIn())
                <!-- User Menu -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="flex items-center space-x-2 text-sm text-gray-600 hover:text-black transition-colors">
                        <span class="hidden md:block">{{ '@' . $authService->getCurrentUser()->username }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>

                    <div x-show="open" @click.away="open = false" x-transition
                        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-50">
                        <div class="py-1">
                            <div class="px-4 py-2 text-sm text-gray-500 border-b border-gray-100">
                                Signed in as<br>
                                <span
                                    class="font-medium text-gray-900">{{ '@' . $authService->getCurrentUser()->username }}</span>
                            </div>
                            <form method="POST" action="{{ route('auth.logout') }}">
                                @csrf
                                <button type="submit"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <!-- Login/Signup Buttons -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('auth.login') }}"
                        class="text-sm text-gray-600 hover:text-black transition-colors">
                        Sign In
                    </a>
                    <a href="{{ route('auth.signup') }}"
                        class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded-full text-sm font-medium hover:bg-gray-200 transition-colors">
                        Sign Up
                    </a>
                </div>
            @endif

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
    </div>

    <!-- Consistent Mobile Menu for All Pages -->
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

                <!-- Mobile Authentication Links -->
                @inject('authService', 'App\Services\AnonymousAccountService')
                @if ($authService->isLoggedIn())
                    <div class="border-t border-gray-200 pt-4">
                        <div class="text-xs text-gray-500 mb-2">Signed in as</div>
                        <div class="font-medium text-sm text-gray-900 mb-3">
                            {{ '@' . $authService->getCurrentUser()->username }}</div>
                        <form method="POST" action="{{ route('auth.logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium text-center">
                                Sign Out
                            </button>
                        </form>
                    </div>
                @else
                    <div class="border-t border-gray-200 pt-4 space-y-2">
                        <a href="{{ route('auth.login') }}"
                            class="block w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium text-center">
                            Sign In
                        </a>
                        <a href="{{ route('auth.signup') }}"
                            class="block w-full border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium text-center">
                            Sign Up
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</nav>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    }
</script>
