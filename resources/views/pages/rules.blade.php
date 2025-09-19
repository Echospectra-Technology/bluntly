@extends('layouts.web')

@section('title', 'Community Rules & Guidelines - Bluntly')

@section('content')
    <x-navigation current-page="rules" />

    <div class="bg-white min-h-screen py-16">
        <div class="max-w-4xl mx-auto px-6">
            <!-- Header -->
            <div class="mb-12">
                <h1 class="text-3xl md:text-4xl font-light text-gray-900 mb-4">Community Rules & Guidelines</h1>
                <p class="text-lg text-gray-600 leading-relaxed">
                    Creating a safe space for authentic, anonymous expression
                </p>
            </div>

            <!-- Main Content -->
            <div class="prose max-w-none space-y-8">
                <section class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Our Community Philosophy</h2>
                    <p class="text-gray-700 leading-relaxed">
                        Bluntly exists to give you the freedom to express your authentic self without judgment. 
                        These guidelines help us maintain a space where everyone feels safe to share their truth, 
                        while protecting the community from harm.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">The Golden Rules</h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-xl font-medium text-gray-900 mb-4">Be Authentic</h3>
                            <ul class="text-gray-700 text-sm space-y-2">
                                <li>• Share your real experiences and feelings</li>
                                <li>• Express yourself honestly and openly</li>
                                <li>• Don't fabricate stories for attention</li>
                                <li>• Let your true voice come through</li>
                            </ul>
                        </div>
                        <div class="bg-gray-100 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-xl font-medium text-gray-900 mb-4">Be Empathetic</h3>
                            <ul class="text-gray-700 text-sm space-y-2">
                                <li>• Remember there's a real person behind every story</li>
                                <li>• Offer support and understanding</li>
                                <li>• Consider how your words might affect others</li>
                                <li>• Practice compassion, even with difficult topics</li>
                            </ul>
                        </div>
                        <div class="bg-white p-6 rounded-lg border border-gray-200">
                            <h3 class="text-xl font-medium text-gray-900 mb-4">Respect Anonymity</h3>
                            <ul class="text-gray-700 text-sm space-y-2">
                                <li>• Never try to identify other users</li>
                                <li>• Don't share personal details about yourself</li>
                                <li>• Protect everyone's privacy and safety</li>
                                <li>• Report attempts to breach anonymity</li>
                            </ul>
                        </div>
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-xl font-medium text-gray-900 mb-4">No Harm</h3>
                            <ul class="text-gray-700 text-sm space-y-2">
                                <li>• Don't threaten, harass, or bully others</li>
                                <li>• Avoid content that promotes self-harm</li>
                                <li>• No illegal activities or content</li>
                                <li>• Keep everyone safe and protected</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Content Guidelines</h2>
                    
                    <div class="space-y-6">
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">What We Welcome</h3>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Personal Stories:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Life experiences and lessons learned</li>
                                        <li>• Relationship struggles and successes</li>
                                        <li>• Mental health journeys</li>
                                        <li>• Family and friendship dynamics</li>
                                        <li>• Work and career challenges</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Honest Expression:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Confessions and secrets</li>
                                        <li>• Rants about frustrations</li>
                                        <li>• Unpopular opinions</li>
                                        <li>• Social observations</li>
                                        <li>• Personal growth stories</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-100 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Sensitive Content (Approach with Care)</h3>
                            <p class="text-gray-700 text-sm mb-3">
                                These topics require extra sensitivity and thoughtfulness:
                            </p>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Mental Health:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Share your experiences, but avoid detailed self-harm methods</li>
                                        <li>• Offer hope and resources when possible</li>
                                        <li>• Remember others might be struggling too</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Difficult Topics:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Trauma (without graphic details)</li>
                                        <li>• Controversial opinions (respectfully)</li>
                                        <li>• Relationship conflicts (anonymously)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">What's Not Allowed</h3>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Harmful Content:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Threats or incitement to violence</li>
                                        <li>• Detailed self-harm instructions</li>
                                        <li>• Explicit sexual content</li>
                                        <li>• Content targeting minors</li>
                                        <li>• Doxxing or revealing personal info</li>
                                        <li>• Illegal activities or content</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Disruptive Behavior:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Spam or repetitive posting</li>
                                        <li>• Trolling or inflammatory content</li>
                                        <li>• Attempting to identify users</li>
                                        <li>• Commercial advertising</li>
                                        <li>• Platform manipulation</li>
                                        <li>• Harassment or bullying</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Community Interaction Guidelines</h2>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="border border-gray-200 p-6 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Commenting and Responses</h3>
                                <ul class="text-gray-700 text-sm space-y-2">
                                    <li>• <strong>Be supportive:</strong> Offer encouragement and understanding</li>
                                    <li>• <strong>Be constructive:</strong> If you disagree, explain respectfully</li>
                                    <li>• <strong>Be mindful:</strong> Remember the person behind the story</li>
                                    <li>• <strong>Be helpful:</strong> Share resources if relevant</li>
                                </ul>
                            </div>
                            
                            <div class="border border-gray-200 p-6 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Voting Guidelines</h3>
                                <ul class="text-gray-700 text-sm space-y-2">
                                    <li>• Upvote content that resonates or helps others</li>
                                    <li>• Downvote content that violates guidelines</li>
                                    <li>• Don't vote based on personal opinion alone</li>
                                    <li>• Consider the impact on the community</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="border border-gray-200 p-6 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Difficult Conversations</h3>
                                <ul class="text-gray-700 text-sm space-y-2">
                                    <li>• Approach with empathy and openness</li>
                                    <li>• Avoid judgment or unsolicited advice</li>
                                    <li>• Focus on support rather than solutions</li>
                                    <li>• Know when to step back from a conversation</li>
                                </ul>
                            </div>
                            
                            <div class="border border-gray-200 p-6 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Crisis Situations</h3>
                                <ul class="text-gray-700 text-sm space-y-2">
                                    <li>• Report posts expressing immediate self-harm</li>
                                    <li>• Provide crisis resources when appropriate</li>
                                    <li>• Don't attempt to be a therapist</li>
                                    <li>• Encourage professional help</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Reporting and Moderation</h2>
                    
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">When to Report Content</h3>
                        <div class="grid md:grid-cols-3 gap-4">
                            <div class="bg-white p-4 rounded border">
                                <h4 class="font-medium text-gray-900 mb-2">Immediate Dangers</h4>
                                <ul class="text-gray-700 text-sm space-y-1">
                                    <li>• Suicide threats</li>
                                    <li>• Violence threats</li>
                                    <li>• Illegal activities</li>
                                    <li>• Child safety concerns</li>
                                </ul>
                            </div>
                            <div class="bg-white p-4 rounded border">
                                <h4 class="font-medium text-gray-900 mb-2">Community Violations</h4>
                                <ul class="text-gray-700 text-sm space-y-1">
                                    <li>• Harassment or bullying</li>
                                    <li>• Doxxing attempts</li>
                                    <li>• Spam or trolling</li>
                                    <li>• Inappropriate content</li>
                                </ul>
                            </div>
                            <div class="bg-white p-4 rounded border">
                                <h4 class="font-medium text-gray-900 mb-2">Policy Violations</h4>
                                <ul class="text-gray-700 text-sm space-y-1">
                                    <li>• Terms of use violations</li>
                                    <li>• Privacy breaches</li>
                                    <li>• False information</li>
                                    <li>• Platform manipulation</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Consequences and Enforcement</h2>
                    
                    <div class="space-y-4">
                        <div class="border border-gray-200 bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Our Approach</h3>
                            <p class="text-gray-700 text-sm mb-4">
                                We believe in education and growth over punishment. Our goal is to maintain community safety while preserving the open nature of the platform.
                            </p>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Typical Actions:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Content removal with explanation</li>
                                        <li>• Community guidance and education</li>
                                        <li>• Temporary posting restrictions</li>
                                        <li>• Content warnings or labels</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Severe Violations:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Extended access restrictions</li>
                                        <li>• Platform bans for extreme cases</li>
                                        <li>• Law enforcement involvement if required</li>
                                        <li>• Account termination for repeated abuse</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Resources and Support</h2>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Crisis Resources</h3>
                            <ul class="text-gray-700 text-sm space-y-2">
                                <li>• <strong>US:</strong> 988 Suicide & Crisis Lifeline</li>
                                <li>• <strong>Crisis Text Line:</strong> Text HOME to 741741</li>
                                <li>• <strong>International:</strong> befrienders.org</li>
                                <li>• <strong>LGBTQ+:</strong> The Trevor Project (1-866-488-7386)</li>
                            </ul>
                        </div>
                        <div class="bg-gray-100 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Platform Support</h3>
                            <ul class="text-gray-700 text-sm space-y-2">
                                <li>• Report inappropriate content</li>
                                <li>• Contact moderation team</li>
                                <li>• Request content removal</li>
                                <li>• Get help with platform issues</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="bg-gray-900 text-white p-8 rounded-lg">
                    <h2 class="text-2xl font-medium mb-4">Building Community Together</h2>
                    <p class="text-gray-300 mb-6">
                        These guidelines evolve with our community. Your feedback and participation help us create a safer, 
                        more supportive space for authentic expression.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="/report" 
                           class="bg-white text-black px-6 py-3 rounded-lg font-medium hover:bg-gray-100 transition-colors text-center">
                            Report Content
                        </a>
                        <a href="/contact" 
                           class="border border-white text-white px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-black transition-colors text-center">
                            Contact Us
                        </a>
                        <a href="/safety" 
                           class="border border-white text-white px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-black transition-colors text-center">
                            Safety Resources
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <x-footer />
@endsection