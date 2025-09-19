@extends('layouts.web')

@section('title', 'Terms of Use - Bluntly')

@section('content')
    <x-navigation current-page="terms" />

    <div class="bg-white min-h-screen py-16">
        <div class="max-w-4xl mx-auto px-6">
            <!-- Header -->
            <div class="mb-12">
                <h1 class="text-3xl md:text-4xl font-light text-gray-900 mb-4">Terms of Use</h1>
                <p class="text-lg text-gray-600 leading-relaxed">
                    The rules and agreements that govern your use of Bluntly
                </p>
                <p class="text-sm text-gray-500 mt-2">Last updated: {{ date('F j, Y') }}</p>
            </div>

            <!-- Main Content -->
            <div class="prose max-w-none space-y-8">
                <section class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Agreement to Terms</h2>
                    <p class="text-gray-700 leading-relaxed">
                        By accessing and using Bluntly, you agree to be bound by these Terms of Use and our Privacy Policy. 
                        If you don't agree with any part of these terms, please don't use our platform.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">What Bluntly Is</h2>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <p class="text-gray-700 leading-relaxed mb-4">
                            Bluntly is an anonymous platform for sharing personal stories, confessions, rants, and experiences. 
                            Our mission is to provide a safe space for authentic expression without the barriers of identity.
                        </p>
                        <div class="grid md:grid-cols-3 gap-4">
                            <div class="text-center">
                                <h3 class="font-medium text-gray-900 mb-1">Anonymous Expression</h3>
                                <p class="text-gray-600 text-sm">Share your truth without revealing your identity</p>
                            </div>
                            <div class="text-center">
                                <h3 class="font-medium text-gray-900 mb-1">Community Support</h3>
                                <p class="text-gray-600 text-sm">Connect with others through shared experiences</p>
                            </div>
                            <div class="text-center">
                                <h3 class="font-medium text-gray-900 mb-1">Safe Space</h3>
                                <p class="text-gray-600 text-sm">Moderated environment focused on empathy</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Your Rights and Responsibilities</h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">You Can</h3>
                            <ul class="text-gray-700 text-sm space-y-2">
                                <li>• Share your personal stories anonymously</li>
                                <li>• Express your authentic thoughts and feelings</li>
                                <li>• Vote on and comment on stories</li>
                                <li>• Report inappropriate content</li>
                                <li>• Request removal of your content</li>
                                <li>• Use the platform completely anonymously</li>
                            </ul>
                        </div>
                        <div class="bg-gray-100 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">You Cannot</h3>
                            <ul class="text-gray-700 text-sm space-y-2">
                                <li>• Post illegal, harmful, or threatening content</li>
                                <li>• Attempt to identify other users</li>
                                <li>• Spam, manipulate, or abuse the platform</li>
                                <li>• Share explicit sexual content</li>
                                <li>• Post content that promotes self-harm</li>
                                <li>• Violate others' privacy or rights</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Content Guidelines</h2>
                    <div class="space-y-4">
                        <div class="border border-gray-200 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Acceptable Content</h3>
                            <p class="text-gray-700 text-sm mb-3">
                                We encourage honest, authentic sharing while maintaining a safe environment for everyone.
                            </p>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Encouraged:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Personal experiences and stories</li>
                                        <li>• Honest confessions and rants</li>
                                        <li>• Mental health struggles</li>
                                        <li>• Relationship experiences</li>
                                        <li>• Work and life challenges</li>
                                        <li>• Social observations</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">With caution:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Difficult topics (with sensitivity)</li>
                                        <li>• Critical opinions (respectfully)</li>
                                        <li>• Personal conflicts (anonymously)</li>
                                        <li>• Controversial subjects (thoughtfully)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border border-gray-200 bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Prohibited Content</h3>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Harmful Content:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Threats or incitement to violence</li>
                                        <li>• Detailed self-harm instructions</li>
                                        <li>• Explicit sexual content</li>
                                        <li>• Child exploitation material</li>
                                        <li>• Doxxing or personal information</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Disruptive Content:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Spam or repetitive posts</li>
                                        <li>• False or misleading information</li>
                                        <li>• Commercial advertising</li>
                                        <li>• Attempts to identify users</li>
                                        <li>• Platform manipulation</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Anonymity and Privacy</h2>
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Our Commitment</h3>
                        <p class="text-gray-700 text-sm mb-4">
                            Anonymity is fundamental to Bluntly. We are committed to protecting your identity and privacy.
                        </p>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-medium text-gray-900 mb-2">We protect:</h4>
                                <ul class="text-gray-700 text-sm space-y-1">
                                    <li>• Your identity and personal information</li>
                                    <li>• Your browsing and interaction patterns</li>
                                    <li>• Your location and device information</li>
                                    <li>• Your anonymity from other users</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 mb-2">You agree to:</h4>
                                <ul class="text-gray-700 text-sm space-y-1">
                                    <li>• Respect others' anonymity</li>
                                    <li>• Not attempt to identify other users</li>
                                    <li>• Keep personal details to yourself</li>
                                    <li>• Report privacy violations</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Moderation and Enforcement</h2>
                    <div class="space-y-4">
                        <div class="border border-gray-200 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">How We Moderate</h3>
                            <p class="text-gray-700 text-sm mb-4">
                                Our moderation focuses on maintaining safety while preserving the authentic, unfiltered nature of the platform.
                            </p>
                            <div class="grid md:grid-cols-3 gap-4">
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Community Reports</h4>
                                    <p class="text-gray-600 text-sm">Users report content that violates guidelines</p>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Human Review</h4>
                                    <p class="text-gray-600 text-sm">Trained moderators review reported content</p>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Contextual Decisions</h4>
                                    <p class="text-gray-600 text-sm">Actions based on content and community impact</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border border-gray-200 bg-gray-100 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Enforcement Actions</h3>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Content Actions:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Content removal</li>
                                        <li>• Content editing (minimal)</li>
                                        <li>• Content warnings</li>
                                        <li>• Community guidance</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Severe Violations:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Temporary posting restrictions</li>
                                        <li>• Platform access limitations</li>
                                        <li>• Reporting to authorities (if legally required)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Disclaimers and Limitations</h2>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Important Disclaimers</h3>
                        <div class="space-y-4 text-sm text-gray-700">
                            <p>
                                <strong>Content Authenticity:</strong> While we encourage honest sharing, we cannot verify the truthfulness of anonymous posts. Use your judgment when reading and responding to content.
                            </p>
                            <p>
                                <strong>Mental Health:</strong> Bluntly is not a substitute for professional mental health care. If you're experiencing crisis or serious mental health issues, please seek help from qualified professionals.
                            </p>
                            <p>
                                <strong>Platform Availability:</strong> We strive to keep Bluntly available 24/7, but cannot guarantee uninterrupted service. We may need to suspend service for maintenance or other reasons.
                            </p>
                            <p>
                                <strong>User Content:</strong> You retain ownership of your content, but by posting on Bluntly, you grant us license to display, distribute, and moderate your content as necessary for platform operation.
                            </p>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Changes to Terms</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        We may update these Terms of Use from time to time to reflect changes in our practices or legal requirements. 
                        Significant changes will be announced prominently on the platform.
                    </p>
                    <p class="text-gray-700 leading-relaxed">
                        Continued use of Bluntly after changes are posted constitutes acceptance of the new terms.
                    </p>
                </section>

                <section class="bg-gray-900 text-white p-8 rounded-lg">
                    <h2 class="text-2xl font-medium mb-4">Contact and Support</h2>
                    <p class="text-gray-300 mb-6">
                        Have questions about these terms or need help with the platform? We're here to assist you.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="/contact" 
                           class="bg-white text-black px-6 py-3 rounded-lg font-medium hover:bg-gray-100 transition-colors text-center">
                            Contact Support
                        </a>
                        <a href="/rules" 
                           class="border border-white text-white px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-black transition-colors text-center">
                            Community Guidelines
                        </a>
                        <a href="/report" 
                           class="border border-white text-white px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-black transition-colors text-center">
                            Report Issue
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <x-footer />
@endsection