@extends('layouts.web')

@section('title', 'Moderation - Bluntly')

@section('content')
    <x-navigation current-page="moderation" />

    <div class="bg-white min-h-screen py-16">
        <div class="max-w-4xl mx-auto px-6">
            <!-- Header -->
            <div class="mb-12">
                <h1 class="text-3xl md:text-4xl font-light text-gray-900 mb-4">Moderation</h1>
                <p class="text-lg text-gray-600 leading-relaxed">
                    How we maintain a safe, supportive community while preserving authentic expression
                </p>
            </div>

            <!-- Main Content -->
            <div class="prose max-w-none space-y-8">
                <section class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Our Moderation Philosophy</h2>
                    <p class="text-gray-700 leading-relaxed">
                        We believe in the power of unfiltered expression, but we also recognize the need for safety and respect. 
                        Our moderation approach balances authenticity with community protection, intervening only when necessary 
                        to prevent harm while preserving the raw, honest nature of Bluntly.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">How Moderation Works</h2>
                    <div class="grid md:grid-cols-3 gap-6">
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <div class="text-center mb-4">
                                <div class="w-12 h-12 bg-gray-500 rounded-full flex items-center justify-center text-white font-bold text-xl mx-auto">
                                    1
                                </div>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-3 text-center">Community Reports</h3>
                            <p class="text-gray-700 text-sm text-center">
                                Community members report content that may violate our guidelines using the report button.
                            </p>
                        </div>
                        
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <div class="text-center mb-4">
                                <div class="w-12 h-12 bg-gray-600 rounded-full flex items-center justify-center text-white font-bold text-xl mx-auto">
                                    2
                                </div>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-3 text-center">Human Review</h3>
                            <p class="text-gray-700 text-sm text-center">
                                Trained human moderators review reported content within context and community guidelines.
                            </p>
                        </div>
                        
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <div class="text-center mb-4">
                                <div class="w-12 h-12 bg-gray-700 rounded-full flex items-center justify-center text-white font-bold text-xl mx-auto">
                                    3
                                </div>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-3 text-center">Contextual Action</h3>
                            <p class="text-gray-700 text-sm text-center">
                                Actions are taken based on content severity, community impact, and user intent.
                            </p>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">What We Moderate</h2>
                    <div class="space-y-4">
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Immediate Action Required</h3>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Safety Threats:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Suicide threats or plans</li>
                                        <li>• Threats of violence toward others</li>
                                        <li>• Self-harm instructions</li>
                                        <li>• Child safety concerns</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Illegal Content:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Illegal activities or content</li>
                                        <li>• Child exploitation material</li>
                                        <li>• Non-consensual intimate images</li>
                                        <li>• Doxxing and personal information</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-100 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Review and Potential Action</h3>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Community Disruption:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Spam or repetitive posting</li>
                                        <li>• Trolling or inflammatory content</li>
                                        <li>• Harassment campaigns</li>
                                        <li>• Platform manipulation</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Policy Violations:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Attempts to identify users</li>
                                        <li>• Commercial advertising</li>
                                        <li>• Explicit sexual content</li>
                                        <li>• Hate speech targeting groups</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Generally Allowed (With Context)</h3>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Authentic Expression:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Raw emotions and frustrations</li>
                                        <li>• Controversial opinions</li>
                                        <li>• Difficult personal experiences</li>
                                        <li>• Mental health struggles</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Critical Content:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Criticism of public figures</li>
                                        <li>• Social and political commentary</li>
                                        <li>• Personal conflicts (anonymized)</li>
                                        <li>• Workplace complaints</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Moderation Actions</h2>
                    <div class="space-y-4">
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Range of Responses</h3>
                            <div class="space-y-4">
                                <div class="border-l-4 border-green-500 pl-4">
                                    <h4 class="font-medium text-gray-900 mb-1">Educational Guidance</h4>
                                    <p class="text-gray-700 text-sm">
                                        For minor violations or misunderstandings, we provide community guidance and education about guidelines.
                                    </p>
                                </div>
                                
                                <div class="border-l-4 border-yellow-500 pl-4">
                                    <h4 class="font-medium text-gray-900 mb-1">Content Warnings</h4>
                                    <p class="text-gray-700 text-sm">
                                        For sensitive but allowable content, we may add warnings to help users make informed choices.
                                    </p>
                                </div>
                                
                                <div class="border-l-4 border-orange-500 pl-4">
                                    <h4 class="font-medium text-gray-900 mb-1">Content Removal</h4>
                                    <p class="text-gray-700 text-sm">
                                        Content that violates guidelines is removed with explanation to help users understand the decision.
                                    </p>
                                </div>
                                
                                <div class="border-l-4 border-red-500 pl-4">
                                    <h4 class="font-medium text-gray-900 mb-1">Access Restrictions</h4>
                                    <p class="text-gray-700 text-sm">
                                        For severe or repeated violations, we may temporarily restrict posting abilities or platform access.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Our Moderation Team</h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Who We Are</h3>
                            <ul class="text-gray-700 text-sm space-y-2">
                                <li>• Trained professionals with mental health awareness</li>
                                <li>• Diverse team reflecting our community</li>
                                <li>• Regular training on crisis intervention</li>
                                <li>• Clear escalation procedures for serious issues</li>
                                <li>• Commitment to fair and consistent decisions</li>
                            </ul>
                        </div>
                        
                        <div class="bg-gray-100 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">How We Protect Anonymity</h3>
                            <ul class="text-gray-700 text-sm space-y-2">
                                <li>• Moderators cannot identify users</li>
                                <li>• No access to personal information</li>
                                <li>• Focus on content, not individuals</li>
                                <li>• Anonymous alias systems for all interactions</li>
                                <li>• Strict confidentiality protocols</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Response Times & Priorities</h2>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Our Commitment to Timely Response</h3>
                        <div class="grid md:grid-cols-3 gap-4">
                            <div class="bg-gray-100 p-4 rounded border border-gray-200">
                                <h4 class="font-medium text-gray-900 mb-2">Crisis Content</h4>
                                <p class="text-gray-700 text-sm mb-2"><strong>Target: 15 minutes</strong></p>
                                <p class="text-gray-600 text-xs">Suicide threats, violence, child safety</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded border border-gray-200">
                                <h4 class="font-medium text-gray-900 mb-2">Harmful Content</h4>
                                <p class="text-gray-700 text-sm mb-2"><strong>Target: 2 hours</strong></p>
                                <p class="text-gray-600 text-xs">Harassment, doxxing, serious violations</p>
                            </div>
                            
                            <div class="bg-white p-4 rounded border border-gray-200">
                                <h4 class="font-medium text-gray-900 mb-2">General Reports</h4>
                                <p class="text-gray-700 text-sm mb-2"><strong>Target: 24 hours</strong></p>
                                <p class="text-gray-600 text-xs">Spam, policy violations, community concerns</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Appeals Process</h2>
                    <div class="space-y-4">
                        <div class="border border-gray-200 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">If You Disagree with a Decision</h3>
                            <p class="text-gray-700 text-sm mb-4">
                                We understand that moderation decisions can sometimes feel unfair. Here's how to appeal:
                            </p>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Appeal Process:</h4>
                                    <ol class="text-gray-700 text-sm space-y-1 list-decimal list-inside">
                                        <li>Use the report system to file an appeal</li>
                                        <li>Provide the specific content or action details</li>
                                        <li>Explain why you believe the decision was incorrect</li>
                                        <li>Wait for review by a different moderator</li>
                                    </ol>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">What to Include:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• URL or reference to the content</li>
                                        <li>• Clear explanation of your perspective</li>
                                        <li>• Any relevant context we might have missed</li>
                                        <li>• Specific guideline you believe applies</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Community Self-Moderation</h2>
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">How You Can Help</h3>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-medium text-gray-900 mb-3">Positive Community Actions:</h4>
                                <ul class="text-gray-700 text-sm space-y-2">
                                    <li>• Upvote supportive and helpful content</li>
                                    <li>• Provide empathetic responses to struggles</li>
                                    <li>• Share helpful resources when appropriate</li>
                                    <li>• Model respectful disagreement</li>
                                    <li>• Welcome new community members</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 mb-3">Reporting Responsibilities:</h4>
                                <ul class="text-gray-700 text-sm space-y-2">
                                    <li>• Report content that violates guidelines</li>
                                    <li>• Report crisis situations immediately</li>
                                    <li>• Provide context when reporting</li>
                                    <li>• Don't abuse the reporting system</li>
                                    <li>• Trust the moderation process</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Transparency & Accountability</h2>
                    <div class="space-y-4">
                        <div class="border border-gray-200 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Our Commitments</h3>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Regular Reporting:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Monthly community updates on moderation</li>
                                        <li>• Statistics on report types and actions</li>
                                        <li>• Policy clarifications based on feedback</li>
                                        <li>• Appeals outcomes and learnings</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Continuous Improvement:</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Regular review of moderation decisions</li>
                                        <li>• Community feedback integration</li>
                                        <li>• Moderator training updates</li>
                                        <li>• Policy refinements based on experience</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="bg-gray-900 text-white p-8 rounded-lg">
                    <h2 class="text-2xl font-medium mb-4">Working Together</h2>
                    <p class="text-gray-300 mb-6">
                        Effective moderation is a partnership between our team and the community. Together, we can maintain 
                        a space where people feel safe to share their authentic experiences while protecting everyone from harm.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="/report" 
                           class="bg-white text-black px-6 py-3 rounded-lg font-medium hover:bg-gray-100 transition-colors text-center">
                            Report Content
                        </a>
                        <a href="/rules" 
                           class="border border-white text-white px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-black transition-colors text-center">
                            Community Guidelines
                        </a>
                        <a href="/contact" 
                           class="border border-white text-white px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-black transition-colors text-center">
                            Contact Moderators
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <x-footer />
@endsection