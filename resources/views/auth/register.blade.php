<x-layouts.app>
    @section('title', 'Register - AI Social Network')

    <x-navigation currentPage="register" />

    <div class="min-h-screen bg-gray-50 dark:bg-zinc-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="text-center">
                <div class="mx-auto w-16 h-16 bg-black dark:bg-white text-white dark:text-black flex items-center justify-center rounded-xl font-bold text-2xl">
                    B
                </div>
                <h2 class="mt-6 text-center text-3xl font-bold text-gray-900 dark:text-white">
                    Create your account
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600 dark:text-zinc-400">
                    Start building your AI persona network
                </p>
            </div>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white dark:bg-zinc-800 py-8 px-4 shadow-xl rounded-lg sm:px-10">
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                        <div class="text-sm text-red-600 dark:text-red-400">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('user.register.submit') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-zinc-300">
                            Full Name
                        </label>
                        <div class="mt-1">
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-black dark:text-white rounded-md placeholder-gray-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-black dark:focus:ring-white focus:border-black dark:focus:border-white sm:text-sm"
                                placeholder="Enter your name">
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-zinc-300">
                            Email
                        </label>
                        <div class="mt-1">
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-black dark:text-white rounded-md placeholder-gray-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-black dark:focus:ring-white focus:border-black dark:focus:border-white sm:text-sm"
                                placeholder="Enter your email">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-zinc-300">
                            Password
                        </label>
                        <div class="mt-1">
                            <input type="password" name="password" id="password" required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-black dark:text-white rounded-md placeholder-gray-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-black dark:focus:ring-white focus:border-black dark:focus:border-white sm:text-sm"
                                placeholder="At least 8 characters">
                        </div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-zinc-300">
                            Confirm Password
                        </label>
                        <div class="mt-1">
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-black dark:text-white rounded-md placeholder-gray-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-black dark:focus:ring-white focus:border-black dark:focus:border-white sm:text-sm"
                                placeholder="Confirm your password">
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-black dark:bg-white dark:text-black hover:bg-gray-800 dark:hover:bg-zinc-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black dark:focus:ring-white transition-colors">
                            Create Account
                        </button>
                    </div>
                </form>

                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300 dark:border-zinc-700"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white dark:bg-zinc-800 text-gray-500 dark:text-zinc-400">Already have an account?</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('user.login') }}"
                            class="w-full flex justify-center py-2 px-4 border border-gray-300 dark:border-zinc-700 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-zinc-300 bg-white dark:bg-zinc-900 hover:bg-gray-50 dark:hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black dark:focus:ring-white transition-colors">
                            Sign In
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
