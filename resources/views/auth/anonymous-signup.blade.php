<x-layouts.app>
    @section('title', 'Sign Up - Bluntly')
    
    <x-navigation currentPage="signup" />
    
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="text-center">
                <div class="mx-auto w-16 h-16 bg-black text-white flex items-center justify-center rounded-xl font-bold text-2xl">
                    B
                </div>
                <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">
                    Create your anonymous account
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Join the community while staying completely anonymous
                </p>
            </div>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-xl rounded-lg sm:px-10">
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                        <div class="text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                        <div class="text-sm text-green-600">
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('auth.register') }}" class="space-y-6" id="signup-form">
                    @csrf

                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">
                            Username
                        </label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="text" name="username" id="username" value="{{ old('username', $suggestedUsername ?? '') }}" required
                                class="flex-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-l-md placeholder-gray-400 focus:outline-none focus:ring-black focus:border-black sm:text-sm"
                                placeholder="Choose a username">
                            <button type="button" id="generate-username"
                                class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 text-gray-500 text-sm hover:bg-gray-100 transition-colors">
                                🎲
                            </button>
                        </div>
                        <div id="username-feedback" class="mt-1 text-xs"></div>
                        <p class="mt-1 text-xs text-gray-500">
                            Choose a unique username. You can always use our random generator.
                        </p>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email (Optional)
                        </label>
                        <div class="mt-1">
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-black focus:border-black sm:text-sm"
                                placeholder="your@email.com (optional)">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Optional. Only used for account recovery. We never send promotional emails.
                        </p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Password
                        </label>
                        <div class="mt-1">
                            <input type="password" name="password" id="password" required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-black focus:border-black sm:text-sm"
                                placeholder="Create a password">
                        </div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                            Confirm Password
                        </label>
                        <div class="mt-1">
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-black focus:border-black sm:text-sm"
                                placeholder="Confirm your password">
                        </div>
                    </div>

                    <div class="text-xs text-gray-500 bg-gray-50 p-3 rounded-md">
                        <p class="font-medium text-gray-700 mb-1">🔒 Privacy Promise:</p>
                        <ul class="space-y-1">
                            <li>• Your identity remains completely anonymous</li>
                            <li>• We don't track or store personal information</li>
                            <li>• All your stories and comments stay private to your username</li>
                        </ul>
                    </div>

                    <div>
                        <button type="submit" id="submit-button"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            Create Anonymous Account
                        </button>
                    </div>
                </form>

                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">Already have an account?</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('auth.login') }}"
                            class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black transition-colors">
                            Sign In Instead
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Username generation and validation
        document.getElementById('generate-username').addEventListener('click', async function() {
            try {
                const response = await fetch('{{ route('auth.generate-username') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') || '{{ csrf_token() }}'
                    }
                });
                
                const data = await response.json();
                if (data.username) {
                    document.getElementById('username').value = data.username;
                    checkUsername();
                }
            } catch (error) {
                console.error('Error generating username:', error);
            }
        });

        // Real-time username validation
        let usernameTimeout;
        document.getElementById('username').addEventListener('input', function() {
            clearTimeout(usernameTimeout);
            usernameTimeout = setTimeout(checkUsername, 500);
        });

        async function checkUsername() {
            const username = document.getElementById('username').value;
            const feedback = document.getElementById('username-feedback');
            const submitButton = document.getElementById('submit-button');
            
            if (!username || username.length < 3) {
                feedback.innerHTML = '';
                submitButton.disabled = false;
                return;
            }

            try {
                const response = await fetch('{{ route('auth.check-username') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') || '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ username })
                });
                
                const data = await response.json();
                
                if (data.available) {
                    feedback.innerHTML = '<span class="text-green-600">✓ ' + data.message + '</span>';
                    submitButton.disabled = false;
                } else {
                    feedback.innerHTML = '<span class="text-red-600">✗ ' + data.message + '</span>';
                    submitButton.disabled = true;
                }
            } catch (error) {
                console.error('Error checking username:', error);
                feedback.innerHTML = '<span class="text-gray-500">Unable to check username availability</span>';
                submitButton.disabled = false;
            }
        }

        // Add CSRF token to page if it doesn't exist
        if (!document.querySelector('meta[name="csrf-token"]')) {
            const meta = document.createElement('meta');
            meta.name = 'csrf-token';
            meta.content = '{{ csrf_token() }}';
            document.head.appendChild(meta);
        }
    </script>
</x-layouts.app>