@extends('layouts.web')

@section('title', 'Report Content - Bluntly')

@section('content')
    <x-navigation current-page="report" />

    <div class="bg-white min-h-screen py-16">
        <div class="max-w-4xl mx-auto px-6">
            <!-- Header -->
            <div class="mb-12">
                <h1 class="text-3xl md:text-4xl font-light text-gray-900 mb-4">Report Content</h1>
                <p class="text-lg text-gray-600 leading-relaxed">
                    Help us maintain a safe and supportive community by reporting content that violates our guidelines
                </p>
            </div>

            <!-- Main Content -->
            <div class="prose max-w-none space-y-8">
                <!-- Crisis Alert -->
                <section class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">In Crisis or Immediate Danger?</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        <strong>If someone is in immediate danger or expressing suicidal thoughts, please contact emergency services immediately.</strong>
                    </p>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="bg-white p-4 rounded border border-gray-200">
                            <h3 class="font-medium text-gray-900 mb-2">Emergency Contacts</h3>
                            <ul class="text-gray-700 text-sm space-y-1">
                                <li>• <strong>911</strong> - Emergency services</li>
                                <li>• <strong>988</strong> - Suicide & Crisis Lifeline</li>
                                <li>• <strong>741741</strong> - Crisis Text Line (text HOME)</li>
                            </ul>
                        </div>
                        <div class="bg-white p-4 rounded border border-gray-200">
                            <h3 class="font-medium text-gray-900 mb-2">Report Immediately</h3>
                            <p class="text-gray-700 text-sm">
                                After contacting emergency services, please also report the content to us so we can provide immediate support and resources.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Report Form -->
                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Report Content</h2>
                    <div class="bg-gray-50 p-8 rounded-lg border border-gray-200">
                        <form class="space-y-6">
                            <!-- Report Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-900 mb-3">What are you reporting?</label>
                                <div class="space-y-3">
                                    <label class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-white cursor-pointer">
                                        <input type="radio" name="report_type" value="crisis" class="mt-1">
                                        <div>
                                            <div class="font-medium text-gray-900">Crisis or Safety Concern</div>
                                            <div class="text-sm text-gray-600">Suicide threats, self-harm, violence, or immediate danger</div>
                                        </div>
                                    </label>
                                    
                                    <label class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-white cursor-pointer">
                                        <input type="radio" name="report_type" value="harassment" class="mt-1">
                                        <div>
                                            <div class="font-medium text-gray-900">Harassment or Bullying</div>
                                            <div class="text-sm text-gray-600">Targeted harassment, bullying, or abuse toward users</div>
                                        </div>
                                    </label>
                                    
                                    <label class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-white cursor-pointer">
                                        <input type="radio" name="report_type" value="privacy" class="mt-1">
                                        <div>
                                            <div class="font-medium text-gray-900">Privacy Violation</div>
                                            <div class="text-sm text-gray-600">Doxxing, sharing personal information, or attempts to identify users</div>
                                        </div>
                                    </label>
                                    
                                    <label class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-white cursor-pointer">
                                        <input type="radio" name="report_type" value="spam" class="mt-1">
                                        <div>
                                            <div class="font-medium text-gray-900">Spam or Abuse</div>
                                            <div class="text-sm text-gray-600">Repetitive posting, trolling, or platform manipulation</div>
                                        </div>
                                    </label>
                                    
                                    <label class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-white cursor-pointer">
                                        <input type="radio" name="report_type" value="inappropriate" class="mt-1">
                                        <div>
                                            <div class="font-medium text-gray-900">Inappropriate Content</div>
                                            <div class="text-sm text-gray-600">Explicit sexual content, illegal activities, or policy violations</div>
                                        </div>
                                    </label>
                                    
                                    <label class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-white cursor-pointer">
                                        <input type="radio" name="report_type" value="misinformation" class="mt-1">
                                        <div>
                                            <div class="font-medium text-gray-900">False Information</div>
                                            <div class="text-sm text-gray-600">Deliberately false or misleading information</div>
                                        </div>
                                    </label>
                                    
                                    <label class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-white cursor-pointer">
                                        <input type="radio" name="report_type" value="other" class="mt-1">
                                        <div>
                                            <div class="font-medium text-gray-900">Other</div>
                                            <div class="text-sm text-gray-600">Something else that concerns you</div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Content Location -->
                            <div>
                                <label for="content_url" class="block text-sm font-medium text-gray-900 mb-2">
                                    Content Location (URL or Description)
                                </label>
                                <input type="text" id="content_url" name="content_url" 
                                       placeholder="Paste the URL or describe where to find the content"
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                                <p class="text-sm text-gray-500 mt-1">
                                    Help us locate the content by providing the story URL, comment location, or specific description.
                                </p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-900 mb-2">
                                    Description of the Issue
                                </label>
                                <textarea id="description" name="description" rows="4"
                                          placeholder="Please describe what you're reporting and why it violates our community guidelines..."
                                          class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent resize-none"></textarea>
                                <p class="text-sm text-gray-500 mt-1">
                                    The more context you provide, the better we can understand and address the issue.
                                </p>
                            </div>

                            <!-- Additional Context -->
                            <div>
                                <label for="additional_info" class="block text-sm font-medium text-gray-900 mb-2">
                                    Additional Information (Optional)
                                </label>
                                <textarea id="additional_info" name="additional_info" rows="3"
                                          placeholder="Any additional context, screenshots description, or relevant details..."
                                          class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent resize-none"></textarea>
                            </div>

                            <!-- Anonymous Note -->
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <div class="flex items-start space-x-3">
                                    <div>
                                        <h4 class="font-medium text-gray-900 mb-1">Your Report is Anonymous</h4>
                                        <p class="text-gray-700 text-sm">
                                            We cannot identify who submits reports. The reported user will not know who reported them. 
                                            We may not be able to follow up with you directly, but we review all reports carefully.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end">
                                <button type="submit" 
                                        class="bg-black text-white px-8 py-3 rounded-lg font-medium hover:bg-gray-800 transition-colors">
                                    Submit Report
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                <!-- What Happens Next -->
                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">What Happens After You Report?</h2>
                    <div class="grid md:grid-cols-3 gap-6">
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <div class="text-center mb-4">
                                <div class="w-12 h-12 bg-gray-500 rounded-full flex items-center justify-center text-white font-bold text-xl mx-auto">
                                    1
                                </div>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-3 text-center">Immediate Review</h3>
                            <p class="text-gray-700 text-sm text-center">
                                Crisis reports are reviewed within 15 minutes. Other reports within 24 hours.
                            </p>
                        </div>
                        
                        <div class="bg-gray-100 p-6 rounded-lg border border-gray-200">
                            <div class="text-center mb-4">
                                <div class="w-12 h-12 bg-gray-600 rounded-full flex items-center justify-center text-white font-bold text-xl mx-auto">
                                    2
                                </div>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-3 text-center">Investigation</h3>
                            <p class="text-gray-700 text-sm text-center">
                                Our moderators review the content in context with our community guidelines.
                            </p>
                        </div>
                        
                        <div class="bg-white p-6 rounded-lg border border-gray-200">
                            <div class="text-center mb-4">
                                <div class="w-12 h-12 bg-gray-700 rounded-full flex items-center justify-center text-white font-bold text-xl mx-auto">
                                    3
                                </div>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-3 text-center">Action Taken</h3>
                            <p class="text-gray-700 text-sm text-center">
                                Appropriate action is taken based on the severity and community guidelines.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Types of Actions -->
                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Possible Actions We May Take</h2>
                    <div class="space-y-4">
                        <div class="border border-gray-200 p-6 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Content-Level Actions</h3>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <ul class="text-gray-700 text-sm space-y-2">
                                        <li>• <strong>Content removal</strong> for guideline violations</li>
                                        <li>• <strong>Content warnings</strong> for sensitive material</li>
                                        <li>• <strong>Crisis resources</strong> for mental health concerns</li>
                                        <li>• <strong>Community guidance</strong> for minor issues</li>
                                    </ul>
                                </div>
                                <div>
                                    <ul class="text-gray-700 text-sm space-y-2">
                                        <li>• <strong>Temporary restrictions</strong> for repeat violations</li>
                                        <li>• <strong>Platform access limits</strong> for severe cases</li>
                                        <li>• <strong>Law enforcement contact</strong> for illegal content</li>
                                        <li>• <strong>No action</strong> if content follows guidelines</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- False Reports -->
                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Reporting Responsibly</h2>
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Please Report Responsibly</h3>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-medium text-gray-900 mb-2">Good Reasons to Report:</h4>
                                <ul class="text-gray-700 text-sm space-y-1">
                                    <li>• Genuine safety concerns</li>
                                    <li>• Clear guideline violations</li>
                                    <li>• Harassment or abuse</li>
                                    <li>• Privacy violations</li>
                                    <li>• Spam or manipulation</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 mb-2">Don't Report For:</h4>
                                <ul class="text-gray-700 text-sm space-y-1">
                                    <li>• Disagreeing with opinions</li>
                                    <li>• Personal conflicts</li>
                                    <li>• Content you dislike</li>
                                    <li>• Different perspectives</li>
                                    <li>• Revenge or retaliation</li>
                                </ul>
                            </div>
                        </div>
                        <p class="text-gray-700 text-sm mt-4">
                            <strong>Note:</strong> Repeatedly filing false reports may result in limitations on your ability to report content.
                        </p>
                    </div>
                </section>

                <!-- Alternative Actions -->
                <section>
                    <h2 class="text-2xl font-medium text-gray-900 mb-4">Before You Report</h2>
                    <div class="bg-gray-100 p-6 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Consider These Alternatives</h3>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-medium text-gray-900 mb-2">For Disagreements:</h4>
                                <ul class="text-gray-700 text-sm space-y-1">
                                    <li>• Downvote content you disagree with</li>
                                    <li>• Share your perspective respectfully</li>
                                    <li>• Simply move on from content you dislike</li>
                                    <li>• Focus on supporting positive content</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 mb-2">For Concerns:</h4>
                                <ul class="text-gray-700 text-sm space-y-1">
                                    <li>• Offer support to users in distress</li>
                                    <li>• Share helpful resources</li>
                                    <li>• Encourage professional help when appropriate</li>
                                    <li>• Model positive community behavior</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="bg-gray-900 text-white p-8 rounded-lg">
                    <h2 class="text-2xl font-medium mb-4">Thank You for Keeping Our Community Safe</h2>
                    <p class="text-gray-300 mb-6">
                        Your reports help us maintain a supportive environment where everyone can share authentically. 
                        Together, we can build a community that values both honest expression and mutual respect.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="/rules" 
                           class="bg-white text-black px-6 py-3 rounded-lg font-medium hover:bg-gray-100 transition-colors text-center">
                            Community Guidelines
                        </a>
                        <a href="/safety" 
                           class="border border-white text-white px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-black transition-colors text-center">
                            Safety Resources
                        </a>
                        <a href="/contact" 
                           class="border border-white text-white px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-black transition-colors text-center">
                            Contact Support
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <x-footer />
@endsection