<footer class="bg-white border-t border-gray-200 py-12 px-6">
    <div class="max-w-6xl mx-auto">
        <div class="grid md:grid-cols-4 gap-8">
            <!-- Brand Column -->
            <div class="md:col-span-1">
                <h3 class="text-lg font-medium text-black mb-4">Bluntly</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    The space to say what you can't say anywhere else. Anonymous voices, unfiltered truths.
                </p>
            </div>

            <!-- Platform Links -->
            <div>
                <h4 class="text-sm font-medium text-black mb-4 uppercase tracking-wide">Platform</h4>
                <div class="space-y-3">
                    <a href="{{ route('feed') }}"
                        class="block text-sm text-gray-600 hover:text-black transition-colors">Read
                        Stories</a>
                    <a href="{{ route('post.create') }}"
                        class="block text-sm text-gray-600 hover:text-black transition-colors">Post
                        Anonymously</a>
                    <a href="{{ route('trending') }}"
                        class="block text-sm text-gray-600 hover:text-black transition-colors">Trending</a>
                    <a href="/categories"
                        class="block text-sm text-gray-600 hover:text-black transition-colors">Categories</a>
                </div>
            </div>

            <!-- Community Links -->
            <div>
                <h4 class="text-sm font-medium text-black mb-4 uppercase tracking-wide">Community</h4>
                <div class="space-y-3">
                    <a href="/rules" class="block text-sm text-gray-600 hover:text-black transition-colors">Rules &
                        Guidelines</a>
                    <a href="/safety" class="block text-sm text-gray-600 hover:text-black transition-colors">Safety</a>
                    <a href="/moderation"
                        class="block text-sm text-gray-600 hover:text-black transition-colors">Moderation</a>
                    <a href="/report" class="block text-sm text-gray-600 hover:text-black transition-colors">Report
                        Content</a>
                </div>
            </div>

            <!-- Legal Links -->
            <div>
                <h4 class="text-sm font-medium text-black mb-4 uppercase tracking-wide">Legal</h4>
                <div class="space-y-3">
                    <a href="/terms" class="block text-sm text-gray-600 hover:text-black transition-colors">Terms of
                        Use</a>
                    <a href="/privacy" class="block text-sm text-gray-600 hover:text-black transition-colors">Privacy
                        Policy</a>
                    <a href="/cookies" class="block text-sm text-gray-600 hover:text-black transition-colors">Cookie
                        Policy</a>
                    <a href="/about" class="block text-sm text-gray-600 hover:text-black transition-colors">About
                        Us</a>
                </div>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="mt-12 pt-8 border-t border-gray-100">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <p class="text-sm text-gray-500">© 2025 Bluntly. All rights reserved.</p>
                <div class="mt-4 md:mt-0 flex items-center space-x-6">
                    <a href="/contact" class="text-sm text-gray-500 hover:text-black transition-colors">Contact</a>
                    <a href="/support" class="text-sm text-gray-500 hover:text-black transition-colors">Support</a>
                    <a href="/feedback" class="text-sm text-gray-500 hover:text-black transition-colors">Feedback</a>
                </div>
            </div>
        </div>
    </div>
</footer>
