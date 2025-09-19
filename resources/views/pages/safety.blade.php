@extends('layouts.web')

@section('title', 'Safety - Bluntly')

@section('content')
    <x-navigation current-page="safety" />

    <div class="bg-white min-h-screen py-16">
        <div class="max-w-4xl mx-auto px-6">
            <!-- Header -->
            <div class="mb-12">
                <h1 class="text-3xl md:text-4xl font-light text-gray-900 mb-4">Safety & Support</h1>
                <p class="text-lg text-gray-600 leading-relaxed">
                    Your safety and well-being are our top priority at Bluntly
                </p>
            </div>

            <!-- Main Content -->
            <div class="prose max-w-none space-y-8">
                <section class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">If You're in Crisis</h2>
                    <p class="text-gray-700 leading-relaxed text-lg mb-4">
                        <strong>If you're having thoughts of suicide or self-harm, please reach out for help immediately.</strong>
                    </p>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="bg-white p-4 rounded border border-gray-200">
                            <h3 class="font-medium text-gray-900 mb-2">United States</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>• <strong>988 Suicide & Crisis Lifeline</strong></li>
                                <li>• Text "HELLO" to 741741 (Crisis Text Line)</li>
                                <li>• Call 911 for emergencies</li>
                            </ul>
                        </div>
                        <div class="bg-white p-4 rounded border border-gray-200">
                            <h3 class="font-medium text-gray-900 mb-2">International</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>• Visit <strong>befrienders.org</strong></li>
                                <li>• Check your local emergency services</li>
                                <li>• Contact your local mental health services</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Crisis Resources</h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Mental Health Support</h3>
                                <ul class="text-gray-700 text-sm space-y-2">
                                    <li>• <strong>NAMI:</strong> 1-800-950-NAMI (6264)</li>
                                    <li>• <strong>SAMHSA:</strong> 1-800-662-4357</li>
                                    <li>• <strong>Crisis Text Line:</strong> Text HOME to 741741</li>
                                    <li>• <strong>National Suicide Prevention Lifeline:</strong> 988</li>
                                </ul>
                            </div>
                            
                            <div class="bg-gray-100 p-6 rounded-lg border border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">LGBTQ+ Support</h3>
                                <ul class="text-gray-700 text-sm space-y-2">
                                    <li>• <strong>The Trevor Project:</strong> 1-866-488-7386</li>
                                    <li>• <strong>Trans Lifeline:</strong> 877-565-8860</li>
                                    <li>• <strong>LGBT National Hotline:</strong> 1-888-843-4564</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="bg-white p-6 rounded-lg border border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Youth & Teen Support</h3>
                                <ul class="text-gray-700 text-sm space-y-2">
                                    <li>• <strong>Teen Line:</strong> 1-800-852-8336</li>
                                    <li>• <strong>Boys Town:</strong> 1-800-448-3000</li>
                                    <li>• <strong>National Runaway Safeline:</strong> 1-800-786-2929</li>
                                </ul>
                            </div>
                            
                            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Abuse & Violence</h3>
                                <ul class="text-gray-700 text-sm space-y-2">
                                    <li>• <strong>Domestic Violence Hotline:</strong> 1-800-799-7233</li>
                                    <li>• <strong>RAINN Sexual Assault Hotline:</strong> 1-800-656-4673</li>
                                    <li>• <strong>Childhelp National Child Abuse:</strong> 1-800-422-4453</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Platform Safety Features</h2>
                    <div class="space-y-4">
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">How We Keep You Safe</h3>
                            <div class="grid md:grid-cols-3 gap-4">
                                <div class="bg-white p-4 rounded border">
                                    <h4 class="font-medium text-gray-900 mb-2">Anonymity Protection</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• No personal data collection</li>
                                        <li>• Anonymous posting and commenting</li>
                                        <li>• Random alias generation</li>
                                        <li>• No user tracking</li>
                                    </ul>
                                </div>
                                <div class="bg-white p-4 rounded border">
                                    <h4 class="font-medium text-gray-900 mb-2">Content Moderation</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Human moderators review reports</li>
                                        <li>• Community reporting system</li>
                                        <li>• Quick response to dangerous content</li>
                                        <li>• Clear community guidelines</li>
                                    </ul>
                                </div>
                                <div class="bg-white p-4 rounded border">
                                    <h4 class="font-medium text-gray-900 mb-2">Crisis Intervention</h4>
                                    <ul class="text-gray-700 text-sm space-y-1">
                                        <li>• Suicide risk detection</li>
                                        <li>• Immediate resource provision</li>
                                        <li>• Professional support referrals</li>
                                        <li>• Emergency contact protocols</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Personal Safety Tips</h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="border border-gray-200 p-6 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Protecting Your Identity</h3>
                                <ul class="text-gray-700 text-sm space-y-2">
                                    <li>• Don't share specific details that could identify you</li>
                                    <li>• Avoid mentioning exact locations, names, or dates</li>
                                    <li>• Be careful about unique story details</li>
                                    <li>• Never share contact information</li>
                                    <li>• Use generic language for personal details</li>
                                </ul>
                            </div>
                            
                            <div class="border border-gray-200 p-6 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Emotional Safety</h3>
                                <ul class="text-gray-700 text-sm space-y-2">
                                    <li>• Set boundaries about what you share</li>
                                    <li>• Take breaks if content becomes overwhelming</li>
                                    <li>• Remember that not all advice is professional</li>
                                    <li>• Seek professional help for serious issues</li>
                                    <li>• Don't feel obligated to share everything</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="border border-gray-200 p-6 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Warning Signs to Report</h3>
                                <ul class="text-gray-700 text-sm space-y-2">
                                    <li>• Suicide threats or plans</li>
                                    <li>• Threats of violence toward others</li>
                                    <li>• Attempts to identify or contact users</li>
                                    <li>• Sharing of personal information</li>
                                    <li>• Harassment or bullying behavior</li>
                                    <li>• Content promoting self-harm</li>
                                </ul>
                            </div>
                            
                            <div class="border border-gray-200 p-6 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Supporting Others Safely</h3>
                                <ul class="text-gray-700 text-sm space-y-2">
                                    <li>• Offer empathy, not advice unless asked</li>
                                    <li>• Share resources rather than personal experiences</li>
                                    <li>• Don't try to be a therapist</li>
                                    <li>• Report concerning content to moderators</li>
                                    <li>• Know your own limits and boundaries</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Mental Health Resources</h2>
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Professional Help Options</h3>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-medium text-gray-900 mb-3">Finding Professional Help</h4>
                                <ul class="text-gray-700 text-sm space-y-2">
                                    <li>• <strong>Psychology Today:</strong> therapist directory</li>
                                    <li>• <strong>Your insurance provider:</strong> covered therapists</li>
                                    <li>• <strong>Community health centers:</strong> sliding scale fees</li>
                                    <li>• <strong>University counseling:</strong> student services</li>
                                    <li>• <strong>Employee assistance programs:</strong> workplace resources</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 mb-3">Online Mental Health</h4>
                                <ul class="text-gray-700 text-sm space-y-2">
                                    <li>• <strong>BetterHelp:</strong> online therapy platform</li>
                                    <li>• <strong>Talkspace:</strong> text-based therapy</li>
                                    <li>• <strong>MDLIVE:</strong> online psychiatry</li>
                                    <li>• <strong>Cerebral:</strong> medication management</li>
                                    <li>• <strong>Headspace:</strong> meditation and mindfulness</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Self-Care Strategies</h2>
                    <div class="grid md:grid-cols-3 gap-6">
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Mindfulness & Coping</h3>
                            <ul class="text-gray-700 text-sm space-y-2">
                                <li>• Deep breathing exercises</li>
                                <li>• Grounding techniques (5-4-3-2-1)</li>
                                <li>• Progressive muscle relaxation</li>
                                <li>• Meditation and mindfulness apps</li>
                                <li>• Journaling and reflection</li>
                            </ul>
                        </div>
                        
                        <div class="bg-gray-100 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Physical Wellness</h3>
                            <ul class="text-gray-700 text-sm space-y-2">
                                <li>• Regular exercise or movement</li>
                                <li>• Consistent sleep schedule</li>
                                <li>• Healthy eating habits</li>
                                <li>• Limiting alcohol and substances</li>
                                <li>• Spending time outdoors</li>
                            </ul>
                        </div>
                        
                        <div class="bg-white p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Social Support</h3>
                            <ul class="text-gray-700 text-sm space-y-2">
                                <li>• Connecting with trusted friends</li>
                                <li>• Joining support groups</li>
                                <li>• Participating in community activities</li>
                                <li>• Seeking professional counseling</li>
                                <li>• Building healthy relationships</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Safety for Specific Groups</h2>
                    <div class="space-y-4">
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Women's Safety Resources</h3>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <ul class="text-gray-700 text-sm space-y-2">
                                        <li>• <strong>National Domestic Violence Hotline:</strong> 1-800-799-7233</li>
                                        <li>• <strong>RAINN Sexual Assault Hotline:</strong> 1-800-656-4673</li>
                                        <li>• <strong>National Dating Abuse Helpline:</strong> 1-866-331-9474</li>
                                    </ul>
                                </div>
                                <div>
                                    <ul class="text-gray-700 text-sm space-y-2">
                                        <li>• <strong>National Sexual Violence Resource Center</strong></li>
                                        <li>• <strong>Women's Law:</strong> legal information resource</li>
                                        <li>• <strong>Local women's shelters and support groups</strong></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-100 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">LGBTQ+ Safety Resources</h3>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <ul class="text-gray-700 text-sm space-y-2">
                                        <li>• <strong>The Trevor Project:</strong> 1-866-488-7386</li>
                                        <li>• <strong>Trans Lifeline:</strong> 877-565-8860</li>
                                        <li>• <strong>LGBT National Hotline:</strong> 1-888-843-4564</li>
                                    </ul>
                                </div>
                                <div>
                                    <ul class="text-gray-700 text-sm space-y-2">
                                        <li>• <strong>PFLAG:</strong> family support network</li>
                                        <li>• <strong>GLAAD:</strong> advocacy and resources</li>
                                        <li>• <strong>Local LGBTQ+ community centers</strong></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="bg-gray-900 text-white p-8 rounded-lg">
                    <h2 class="text-2xl font-medium mb-4">We're Here for You</h2>
                    <p class="text-gray-300 mb-6">
                        Your safety and well-being matter to us. If you're struggling, please don't hesitate to reach out for help. 
                        You are not alone, and there are people who want to support you.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="/report" 
                           class="bg-white text-black px-6 py-3 rounded-lg font-medium hover:bg-gray-100 transition-colors text-center">
                            Report Safety Concern
                        </a>
                        <a href="/contact" 
                           class="border border-white text-white px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-black transition-colors text-center">
                            Contact Support
                        </a>
                        <a href="/rules" 
                           class="border border-white text-white px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-black transition-colors text-center">
                            Community Guidelines
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <x-footer />
@endsection