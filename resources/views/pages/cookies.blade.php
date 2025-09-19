@extends('layouts.web')

@section('title', 'Cookie Policy - Bluntly')

@section('content')
    <x-navigation current-page="cookies" />

    <div class="bg-white min-h-screen py-16">
        <div class="max-w-4xl mx-auto px-6">
            <!-- Header -->
            <div class="mb-12">
                <h1 class="text-3xl md:text-4xl font-light text-gray-900 mb-4">Cookie Policy</h1>
                <p class="text-lg text-gray-600 leading-relaxed">
                    How Bluntly uses cookies to protect your anonymity while providing essential functionality
                </p>
                <p class="text-sm text-gray-500 mt-2">Last updated: {{ date('F j, Y') }}</p>
            </div>

            <!-- Main Content -->
            <div class="prose max-w-none space-y-8">
                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">What Are Cookies?</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Cookies are small text files that are stored on your device when you visit our website. They help us provide you with a better experience while maintaining your anonymity.
                    </p>
                    <p class="text-gray-700 leading-relaxed">
                        At Bluntly, we use cookies minimally and only for essential functionality. We do not use tracking cookies or share cookie data with third parties.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">How We Use Cookies</h2>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Essential Cookies Only</h3>
                        <div class="space-y-4">
                            <div class="border-l-4 border-black pl-4">
                                <h4 class="font-medium text-gray-900">Anonymous Voting</h4>
                                <p class="text-gray-700 text-sm">
                                    We use a temporary identifier to prevent duplicate voting on stories and comments, while keeping your identity completely anonymous.
                                </p>
                            </div>
                            <div class="border-l-4 border-gray-300 pl-4">
                                <h4 class="font-medium text-gray-900">Session Management</h4>
                                <p class="text-gray-700 text-sm">
                                    Basic session cookies to maintain your browsing session and remember your preferences like filter settings.
                                </p>
                            </div>
                            <div class="border-l-4 border-gray-300 pl-4">
                                <h4 class="font-medium text-gray-900">Security</h4>
                                <p class="text-gray-700 text-sm">
                                    CSRF protection cookies to prevent malicious attacks and keep the platform secure.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">What We Don't Do</h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="bg-white p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">No Tracking</h3>
                            <ul class="text-gray-700 text-sm space-y-2">
                                <li>• No analytics cookies</li>
                                <li>• No advertising cookies</li>
                                <li>• No cross-site tracking</li>
                                <li>• No behavioral profiling</li>
                            </ul>
                        </div>
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Privacy First</h3>
                            <ul class="text-gray-700 text-sm space-y-2">
                                <li>• Anonymous identifiers only</li>
                                <li>• No personal data collection</li>
                                <li>• No third-party sharing</li>
                                <li>• Minimal data retention</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Cookie Types We Use</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full border border-gray-200 rounded-lg overflow-hidden">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-900">Cookie Name</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-900">Purpose</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-900">Duration</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900 font-mono">laravel_session</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">Maintains your browsing session</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">2 hours</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-900 font-mono">XSRF-TOKEN</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">Security protection against attacks</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">2 hours</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900 font-mono">anonymous_id</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">Anonymous voting and interaction tracking</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">30 days</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Managing Your Cookies</h2>
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Browser Settings</h3>
                        <p class="text-gray-700 text-sm mb-4">
                            You can control cookies through your browser settings. However, disabling essential cookies may affect the functionality of Bluntly.
                        </p>
                        <div class="grid md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <h4 class="font-medium text-gray-900 mb-2">If you disable cookies:</h4>
                                <ul class="text-gray-700 space-y-1">
                                    <li>• You can still read stories</li>
                                    <li>• You won't be able to vote</li>
                                    <li>• You won't be able to post</li>
                                    <li>• Some features may not work</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 mb-2">Browser instructions:</h4>
                                <ul class="text-gray-700 space-y-1">
                                    <li>• Chrome: Settings → Privacy → Cookies</li>
                                    <li>• Firefox: Preferences → Privacy → Cookies</li>
                                    <li>• Safari: Preferences → Privacy → Cookies</li>
                                    <li>• Edge: Settings → Privacy → Cookies</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Updates to This Policy</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        We may update this Cookie Policy from time to time. Any changes will be posted on this page with an updated revision date.
                    </p>
                    <p class="text-gray-700 leading-relaxed">
                        We encourage you to review this policy periodically to stay informed about how we use cookies.
                    </p>
                </section>

                <section class="bg-gray-900 text-white p-8 rounded-lg">
                    <h2 class="text-2xl font-medium mb-4">Questions About Our Cookie Policy?</h2>
                    <p class="text-gray-300 mb-6">
                        If you have any questions about how we use cookies or our privacy practices, we'd be happy to help.
                    </p>
                    <a href="/contact" 
                       class="inline-block bg-white text-black px-6 py-3 rounded-lg font-medium hover:bg-gray-100 transition-colors">
                        Contact Us
                    </a>
                </section>
            </div>
        </div>
    </div>

    <x-footer />
@endsection