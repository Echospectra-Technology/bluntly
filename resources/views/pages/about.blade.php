@extends('layouts.web')

@section('title', 'About Us - Bluntly')

@section('content')
    <x-navigation current-page="about" />

    <div class="bg-white min-h-screen py-16">
        <div class="max-w-4xl mx-auto px-6">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-3xl md:text-4xl font-light text-gray-900 mb-4">About Bluntly</h1>
                <p class="text-lg text-gray-600 leading-relaxed">
                    The space to say what you can't say anywhere else
                </p>
            </div>

            <!-- Main Content -->
            <div class="prose max-w-none">
                <div class="grid md:grid-cols-2 gap-12 mb-16">
                    <div>
                        <h2 class="text-2xl font-medium text-gray-900 mb-6">Our Mission</h2>
                        <p class="text-gray-700 leading-relaxed mb-4">
                            Bluntly exists because sometimes you need to speak your truth without the weight of your identity attached to it. We believe that anonymous expression can be liberating, healing, and deeply connecting.
                        </p>
                        <p class="text-gray-700 leading-relaxed">
                            Our platform provides a safe space for people to share their authentic experiences, confessions, rants, and stories without fear of judgment or social consequences.
                        </p>
                    </div>
                    <div>
                        <h2 class="text-2xl font-medium text-gray-900 mb-6">Why Anonymous?</h2>
                        <p class="text-gray-700 leading-relaxed mb-4">
                            Anonymity removes barriers to honest expression. When you don't have to worry about how your words might affect your relationships, career, or reputation, you can speak with raw honesty.
                        </p>
                        <p class="text-gray-700 leading-relaxed">
                            This creates a unique environment where people can find solidarity, understanding, and genuine human connection through shared experiences.
                        </p>
                    </div>
                </div>

                <div class="bg-gray-50 p-8 rounded-lg mb-16">
                    <h2 class="text-2xl font-medium text-gray-900 mb-6 text-center">What Makes Bluntly Different</h2>
                    <div class="grid md:grid-cols-3 gap-8">
                        <div class="text-center">
                            <div class="w-12 h-12 bg-black rounded-full flex items-center justify-center text-white font-bold text-xl mx-auto mb-4">
                                1
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">True Anonymity</h3>
                            <p class="text-gray-600 text-sm">
                                No usernames, no profiles, no tracking. Just pure, anonymous expression.
                            </p>
                        </div>
                        <div class="text-center">
                            <div class="w-12 h-12 bg-black rounded-full flex items-center justify-center text-white font-bold text-xl mx-auto mb-4">
                                2
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Unfiltered Truth</h3>
                            <p class="text-gray-600 text-sm">
                                Raw, honest stories without the polish of social media personas.
                            </p>
                        </div>
                        <div class="text-center">
                            <div class="w-12 h-12 bg-black rounded-full flex items-center justify-center text-white font-bold text-xl mx-auto mb-4">
                                3
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Safe Space</h3>
                            <p class="text-gray-600 text-sm">
                                A moderated community focused on empathy and understanding.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mb-16">
                    <h2 class="text-2xl font-medium text-gray-900 mb-6">Our Values</h2>
                    <div class="space-y-6">
                        <div class="border-l-4 border-black pl-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Authenticity</h3>
                            <p class="text-gray-700">
                                We value real, unfiltered human experiences over curated content.
                            </p>
                        </div>
                        <div class="border-l-4 border-gray-300 pl-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Empathy</h3>
                            <p class="text-gray-700">
                                We foster understanding and compassion, even when stories are difficult to hear.
                            </p>
                        </div>
                        <div class="border-l-4 border-gray-300 pl-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Safety</h3>
                            <p class="text-gray-700">
                                We protect our community through careful moderation and clear guidelines.
                            </p>
                        </div>
                        <div class="border-l-4 border-gray-300 pl-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Privacy</h3>
                            <p class="text-gray-700">
                                We respect anonymity and protect the privacy of all community members.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="text-center bg-gray-900 text-white p-8 rounded-lg">
                    <h2 class="text-2xl font-medium mb-4">Join Our Community</h2>
                    <p class="text-gray-300 mb-6 max-w-2xl mx-auto">
                        Whether you need to share your story or simply want to listen and support others, 
                        you have a place here. Your voice matters, and your experiences can help others feel less alone.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="/post/create" 
                           class="bg-white text-black px-6 py-3 rounded-lg font-medium hover:bg-gray-100 transition-colors">
                            Share Your Story
                        </a>
                        <a href="{{ route('feed') }}" 
                           class="border border-white text-white px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-black transition-colors">
                            Read Stories
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-footer />
@endsection