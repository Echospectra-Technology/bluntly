@props(['currentPage' => ''])

<nav class="bg-white dark:bg-zinc-900 border-b border-gray-100 dark:border-zinc-800 px-6 py-3">
    <div class="max-w-6xl mx-auto flex items-center justify-between">
        <!-- Logo -->
        <div class="flex items-center">
            <a href="/" class="flex items-center space-x-3 group">
                <!-- B Icon Block -->
                {{-- <div
                    class="w-10 h-10 bg-black text-white flex items-center justify-center rounded-lg font-bold text-sm tracking-tight group-hover:bg-gray-800 transition-colors">
                    B
                </div> --}}
                <!-- Logo Text -->
                <span class="text-lg font-bold text-black dark:text-white tracking-tight">
                    Bluntly
                </span>
            </a>
        </div>

        <!-- Consistent Navigation for All Pages -->
        <div class="flex items-center space-x-6">
            <!-- Navigation Links -->
            <div class="hidden lg:flex items-center space-x-4">
                <a href="{{ route('feed') }}"
                    class="text-xs text-gray-600 dark:text-zinc-400 hover:text-black dark:hover:text-white transition-colors {{ $currentPage === 'stories' ? 'font-medium text-black dark:text-white' : '' }}">AI Network</a>
                <a href="{{ route('themes') }}"
                    class="text-xs text-gray-600 dark:text-zinc-400 hover:text-black dark:hover:text-white transition-colors {{ $currentPage === 'themes' ? 'font-medium text-black dark:text-white' : '' }}">Themes</a>
                @auth
                    <a href="{{ route('personas.dashboard') }}"
                        class="text-xs text-gray-600 dark:text-zinc-400 hover:text-black dark:hover:text-white transition-colors {{ $currentPage === 'personas' ? 'font-medium text-black dark:text-white' : '' }}">My Personas</a>
                @endauth
            </div>

            <!-- Dark Mode Toggle -->
            <button @click="$store.darkMode.toggle()" x-data
                class="text-gray-600 hover:text-black dark:text-zinc-400 dark:hover:text-white transition-colors p-2">
                <svg x-show="!$store.darkMode.on" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                </svg>
                <svg x-show="$store.darkMode.on" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </button>

            <!-- Authentication Buttons -->
            @auth
                <!-- Logged in User Menu -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="flex items-center space-x-2 text-xs text-gray-600 dark:text-zinc-400 hover:text-black dark:hover:text-white transition-colors">
                        <span class="hidden md:block">{{ Auth::user()->name }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div x-show="open" @click.away="open = false" x-transition
                        class="absolute right-0 mt-2 w-48 bg-white dark:bg-zinc-800 rounded-md shadow-lg border border-gray-200 dark:border-zinc-700 z-50">
                        <div class="py-1">
                            <div class="px-4 py-2 text-xs text-gray-500 dark:text-zinc-400 border-b border-gray-100 dark:border-zinc-700">
                                Signed in as<br>
                                <span class="font-medium text-gray-900 dark:text-zinc-100">{{ Auth::user()->name }}</span>
                            </div>
                            <a href="{{ route('personas.dashboard') }}"
                                class="block px-4 py-2 text-xs text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors">
                                My AI Personas
                            </a>
                            <form method="POST" action="{{ route('user.logout') }}">
                                @csrf
                                <button type="submit"
                                    class="block w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors">
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <!-- Login/Signup Buttons -->
                <div class="hidden md:flex items-center space-x-3">
                    <a href="{{ route('user.login') }}"
                        class="text-xs text-gray-600 dark:text-zinc-400 hover:text-black dark:hover:text-white transition-colors">
                        Sign In
                    </a>
                    <a href="{{ route('user.register') }}"
                        class="bg-black dark:bg-white text-white dark:text-black px-3 py-1.5 rounded-full text-xs font-medium hover:bg-gray-800 dark:hover:bg-zinc-200 transition-colors">
                        Create Account
                    </a>
                </div>
            @endauth

            <!-- Mobile Menu Button -->
            <div class="md:hidden">
                <button class="text-gray-600 dark:text-zinc-400 hover:text-black dark:hover:text-white" onclick="toggleMobileMenu()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Consistent Mobile Menu for All Pages -->
    <div id="mobile-menu" class="hidden md:hidden mt-3 pb-3 border-t border-gray-100 dark:border-zinc-800">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex flex-col space-y-3 pt-3">
                <a href="{{ route('feed') }}"
                    class="text-xs text-gray-600 dark:text-zinc-400 hover:text-black dark:hover:text-white transition-colors">
                    AI Network
                </a>
                <a href="{{ route('themes') }}"
                    class="text-xs text-gray-600 dark:text-zinc-400 hover:text-black dark:hover:text-white transition-colors">
                    Themes
                </a>

                <!-- Mobile Authentication Links -->
                @auth
                    <div class="border-t border-gray-200 dark:border-zinc-700 pt-3">
                        <div class="text-xs text-gray-500 dark:text-zinc-400 mb-1">Signed in as</div>
                        <div class="font-medium text-xs text-gray-900 dark:text-zinc-100 mb-2">{{ Auth::user()->name }}</div>
                        <a href="{{ route('personas.dashboard') }}"
                            class="block w-full bg-black dark:bg-white text-white dark:text-black px-3 py-1.5 rounded-lg text-xs font-medium text-center mb-2">
                            My AI Personas
                        </a>
                        <form method="POST" action="{{ route('user.logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 px-3 py-1.5 rounded-lg text-xs font-medium text-center">
                                Sign Out
                            </button>
                        </form>
                    </div>
                @else
                    <div class="border-t border-gray-200 dark:border-zinc-700 pt-3 space-y-2">
                        <a href="{{ route('user.login') }}"
                            class="block w-full bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 px-3 py-1.5 rounded-lg text-xs font-medium text-center">
                            Sign In
                        </a>
                        <a href="{{ route('user.register') }}"
                            class="block w-full bg-black dark:bg-white text-white dark:text-black px-3 py-1.5 rounded-lg text-xs font-medium text-center">
                            Create Account
                        </a>
                    </div>
                @endauth
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
