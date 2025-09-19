@extends('layouts.web')

@section('title', 'Privacy Policy - Bluntly')

@section('content')
    <x-navigation current-page="privacy" />

    <div class="bg-white min-h-screen py-16">
        <div class="max-w-4xl mx-auto px-6">
            <!-- Header -->
            <div class="mb-12">
                <h1 class="text-3xl md:text-4xl font-light text-gray-900 mb-4">Privacy Policy</h1>
                <p class="text-lg text-gray-600 leading-relaxed">
                    Your anonymity and privacy are at the core of everything we do at Bluntly
                </p>
                <p class="text-sm text-gray-500 mt-2">Last updated: {{ date('F j, Y') }}</p>
            </div>

            <!-- Main Content -->
            <div class="prose max-w-none space-y-8">
                <section class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Our Privacy Commitment</h2>
                    <p class="text-gray-700 leading-relaxed text-lg">
                        <strong>We don't collect personal information. We don't track you. We don't sell your data.</strong>
                        Your stories and interactions on Bluntly remain completely anonymous.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">What Information We Don't Collect</h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">No Personal Data</h3>
                            <ul class="text-gray-700 text-sm space-y-2">
                                <li>• No names or usernames</li>
                                <li>• No email addresses</li>
                                <li>• No phone numbers</li>
                                <li>• No location data</li>
                                <li>• No browsing history</li>
                                <li>• No device fingerprinting</li>
                            </ul>
                        </div>
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">No Tracking</h3>
                            <ul class="text-gray-700 text-sm space-y-2">
                                <li>• No analytics tracking</li>
                                <li>• No social media pixels</li>
                                <li>• No advertising IDs</li>
                                <li>• No cross-site tracking</li>
                                <li>• No behavioral profiling</li>
                                <li>• No data brokers</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">What We Do Collect (Minimally)</h2>
                    <div class="space-y-4">
                        <div class="border border-gray-200 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Story Content</h3>
                            <p class="text-gray-700 text-sm mb-2">
                                <strong>What:</strong> The stories, confessions, and comments you choose to share
                            </p>
                            <p class="text-gray-700 text-sm mb-2">
                                <strong>Why:</strong> To display your content on the platform
                            </p>
                            <p class="text-gray-700 text-sm">
                                <strong>Anonymous:</strong> Content is never linked to your identity
                            </p>
                        </div>

                        <div class="border border-gray-200 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Anonymous Identifiers</h3>
                            <p class="text-gray-700 text-sm mb-2">
                                <strong>What:</strong> Random, temporary identifiers for voting and interaction
                            </p>
                            <p class="text-gray-700 text-sm mb-2">
                                <strong>Why:</strong> To prevent vote manipulation and spam
                            </p>
                            <p class="text-gray-700 text-sm">
                                <strong>Anonymous:</strong> These IDs cannot be traced back to you
                            </p>
                        </div>

                        <div class="border border-gray-200 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Basic Technical Data</h3>
                            <p class="text-gray-700 text-sm mb-2">
                                <strong>What:</strong> IP addresses (hashed), basic browser info for security
                            </p>
                            <p class="text-gray-700 text-sm mb-2">
                                <strong>Why:</strong> To prevent abuse and protect the platform
                            </p>
                            <p class="text-gray-700 text-sm">
                                <strong>Retention:</strong> Automatically deleted after 7 days
                            </p>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">How We Protect Your Anonymity</h2>
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Technical Safeguards</h3>
                                <ul class="text-gray-700 text-sm space-y-2">
                                    <li>• Encrypted data transmission</li>
                                    <li>• No persistent user accounts</li>
                                    <li>• Random alias generation</li>
                                    <li>• Automatic data purging</li>
                                    <li>• No server logs retention</li>
                                </ul>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Operational Safeguards</h3>
                                <ul class="text-gray-700 text-sm space-y-2">
                                    <li>• No user identification attempts</li>
                                    <li>• Minimal staff access</li>
                                    <li>• Regular security audits</li>
                                    <li>• Zero data sharing policies</li>
                                    <li>• Anonymous moderation</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Data Sharing and Third Parties</h2>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-xl font-medium text-gray-900 mb-4 text-center">We Don't Share Your Data. Period.
                        </h3>
                        <div class="grid md:grid-cols-3 gap-4 text-center">
                            <div class="bg-white p-4 rounded border">
                                <h4 class="font-medium text-gray-900">No Advertisers</h4>
                                <p class="text-gray-600 text-sm">No data sold to advertising companies</p>
                            </div>
                            <div class="bg-white p-4 rounded border">
                                <h4 class="font-medium text-gray-900">No Data Brokers</h4>
                                <p class="text-gray-600 text-sm">No information sold to third parties</p>
                            </div>
                            <div class="bg-white p-4 rounded border">
                                <h4 class="font-medium text-gray-900">No Analytics</h4>
                                <p class="text-gray-600 text-sm">No Google Analytics or tracking services</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Legal Requests and Law Enforcement</h2>
                    <div class="border border-gray-200 bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Our Position</h3>
                        <p class="text-gray-700 text-sm mb-4">
                            We believe in protecting user anonymity even from legal requests. However, we may be legally
                            required to respond in certain circumstances.
                        </p>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-medium text-gray-900 mb-2">What we would provide:</h4>
                                <ul class="text-gray-700 text-sm space-y-1">
                                    <li>• Content of specific posts (if legally required)</li>
                                    <li>• Basic technical information</li>
                                    <li>• Timestamp data</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 mb-2">What we cannot provide:</h4>
                                <ul class="text-gray-700 text-sm space-y-1">
                                    <li>• User identity (we don't have it)</li>
                                    <li>• Personal information (we don't collect it)</li>
                                    <li>• Location data (we don't track it)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Your Rights and Control</h2>
                    <div class="space-y-4">
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Your Rights</h3>
                            <ul class="text-gray-700 text-sm space-y-2">
                                <li>• <strong>Access:</strong> Since we don't track you, there's no personal data to access
                                </li>
                                <li>• <strong>Deletion:</strong> Your content can be removed upon request</li>
                                <li>• <strong>Portability:</strong> Export your posted content if needed</li>
                                <li>• <strong>Correction:</strong> Edit or update your posts</li>
                            </ul>
                        </div>

                        <div class="bg-gray-100 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">How to Exercise Your Rights</h3>
                            <p class="text-gray-700 text-sm mb-4">
                                Since everything is anonymous, we can't identify which content is yours. You'll need to:
                            </p>
                            <ul class="text-gray-700 text-sm space-y-2">
                                <li>• Provide the exact URL of your post</li>
                                <li>• Include specific details that prove you authored the content</li>
                                <li>• Contact us through our report system</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Security Measures</h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="border border-gray-200 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Technical Security</h3>
                            <ul class="text-gray-700 text-sm space-y-2">
                                <li>• HTTPS encryption for all data</li>
                                <li>• Secure server infrastructure</li>
                                <li>• Regular security updates</li>
                                <li>• DDoS protection</li>
                                <li>• Intrusion detection</li>
                            </ul>
                        </div>
                        <div class="border border-gray-200 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Operational Security</h3>
                            <ul class="text-gray-700 text-sm space-y-2">
                                <li>• Limited staff access</li>
                                <li>• Regular security training</li>
                                <li>• Incident response procedures</li>
                                <li>• Third-party security audits</li>
                                <li>• Breach notification policies</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Changes to This Policy</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        We may update this Privacy Policy to reflect changes in our practices or legal requirements. Any
                        significant changes will be prominently announced on the platform.
                    </p>
                    <p class="text-gray-700 leading-relaxed">
                        We will never change our core commitment to anonymity and privacy without giving users clear notice
                        and options.
                    </p>
                </section>

                <section class="bg-gray-900 text-white p-8 rounded-lg">
                    <h2 class="text-2xl font-medium mb-4">Questions About Privacy?</h2>
                    <p class="text-gray-300 mb-6">
                        Your privacy is our priority. If you have questions about this policy or how we protect your
                        anonymity, we're here to help.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="/contact"
                            class="bg-white text-black px-6 py-3 rounded-lg font-medium hover:bg-gray-100 transition-colors text-center">
                            Contact Us
                        </a>
                        <a href="/report"
                            class="border border-white text-white px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-black transition-colors text-center">
                            Report Privacy Concern
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <x-footer />
@endsection
