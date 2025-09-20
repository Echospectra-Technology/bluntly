<footer class="bg-white border-t border-gray-200 py-8 px-6">
    <div class="max-w-6xl mx-auto">
        <div class="grid md:grid-cols-4 gap-6">
            <!-- Brand Column -->
            <div class="md:col-span-1">
                <h3 class="text-base font-medium text-black mb-3">Bluntly</h3>
                <p class="text-xs text-gray-600 leading-snug">
                    The space to say what you can't say anywhere else. Anonymous voices, unfiltered truths.
                </p>
            </div>

            <!-- Platform Links -->
            <div>
                <h4 class="text-xs font-medium text-black mb-3 uppercase tracking-wide">Platform</h4>
                <div class="space-y-2">
                    <a href="{{ route('feed') }}"
                        class="block text-xs text-gray-600 hover:text-black transition-colors">Read
                        Stories</a>
                    <a href="{{ route('themes') }}"
                        class="block text-xs text-gray-600 hover:text-black transition-colors">Weekly Themes</a>
                    <a href="{{ route('post.create') }}"
                        class="block text-xs text-gray-600 hover:text-black transition-colors">Post
                        Anonymously</a>
                    <a href="{{ route('trending') }}"
                        class="block text-xs text-gray-600 hover:text-black transition-colors">Trending</a>
                    <a href="/categories"
                        class="block text-xs text-gray-600 hover:text-black transition-colors">Categories</a>
                </div>
            </div>

            <!-- Community Links -->
            <div>
                <h4 class="text-xs font-medium text-black mb-3 uppercase tracking-wide">Community</h4>
                <div class="space-y-2">
                    <a href="{{ route('rules') }}" class="block text-xs text-gray-600 hover:text-black transition-colors">Rules &
                        Guidelines</a>
                    <a href="{{ route('safety') }}" class="block text-xs text-gray-600 hover:text-black transition-colors">Safety</a>
                    <a href="{{ route('moderation') }}"
                        class="block text-xs text-gray-600 hover:text-black transition-colors">Moderation</a>
                    <a href="{{ route('report') }}" class="block text-xs text-gray-600 hover:text-black transition-colors">Report
                        Content</a>
                </div>
            </div>

            <!-- Legal Links -->
            <div>
                <h4 class="text-xs font-medium text-black mb-3 uppercase tracking-wide">Legal</h4>
                <div class="space-y-2">
                    <a href="{{ route('terms') }}" class="block text-xs text-gray-600 hover:text-black transition-colors">Terms of
                        Use</a>
                    <a href="{{ route('privacy') }}" class="block text-xs text-gray-600 hover:text-black transition-colors">Privacy
                        Policy</a>
                    <a href="{{ route('cookies') }}" class="block text-xs text-gray-600 hover:text-black transition-colors">Cookie
                        Policy</a>
                    <a href="{{ route('about') }}" class="block text-xs text-gray-600 hover:text-black transition-colors">About
                        Us</a>
                </div>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="mt-8 pt-6 border-t border-gray-100">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <p class="text-xs text-gray-500">© 2025 Bluntly. All rights reserved.</p>
                <div class="mt-3 md:mt-0 flex items-center space-x-4">
                    <a href="/contact" class="text-xs text-gray-500 hover:text-black transition-colors">Contact</a>
                    <a href="/support" class="text-xs text-gray-500 hover:text-black transition-colors">Support</a>
                    <a href="/feedback" class="text-xs text-gray-500 hover:text-black transition-colors">Feedback</a>
                </div>
            </div>
        </div>
    </div>
</footer>
