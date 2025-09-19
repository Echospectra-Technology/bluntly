@extends('layouts.web')

@section('title', 'Bluntly - Say it as it is.')
@section('meta_keywords',
    'anonymous stories, confessions, rants, anonymous posting, share secrets, unfiltered content,
    anonymous social media')

    @push('structured-data')
        @php
            $websiteStructuredData = [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => 'Bluntly',
                'description' =>
                    "The space to say what you can't say anywhere else. Anonymous voices, unfiltered truths.",
                'url' => url('/'),
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => url('/posts') . '?search={search_term_string}',
                    ],
                    'query-input' => 'required name=search_term_string',
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'Bluntly',
                    'url' => url('/'),
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => asset('apple-touch-icon.png'),
                    ],
                ],
            ];

            $itemListStructuredData = [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => 'Trending Stories on Bluntly',
                'description' => 'Latest trending anonymous stories and confessions',
                'itemListElement' => [],
            ];

            foreach ($trendingStories as $index => $story) {
                $itemListStructuredData['itemListElement'][] = [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'item' => [
                        '@type' => 'Article',
                        '@id' => route('post', $story->slug),
                        'headline' => $story->title,
                        'description' => Str::limit(strip_tags($story->body), 160),
                        'url' => route('post', $story->slug),
                        'datePublished' => $story->created_at->toISOString(),
                        'author' => [
                            '@type' => 'Person',
                            'name' => '@' . $story->alias,
                        ],
                    ],
                ];
            }
        @endphp

        <script type="application/ld+json">
{!! json_encode($websiteStructuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>

        <script type="application/ld+json">
{!! json_encode($itemListStructuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
    @endpush

@section('content')
    <x-navigation current-page="home" />

    <!-- Hero Section -->
    <section class="min-h-screen flex items-center justify-center px-6 pt-4 pb-12 relative overflow-hidden">
        <!-- Background Lines -->
        <div class="absolute inset-0 opacity-[0.12]">
            <div class="absolute top-0 left-1/4 w-px h-full bg-gray-300"></div>
            <div class="absolute top-0 left-1/2 w-px h-full bg-gray-300"></div>
            <div class="absolute top-0 left-3/4 w-px h-full bg-gray-300"></div>
            <div class="absolute top-1/4 left-0 w-full h-px bg-gray-300"></div>
            <div class="absolute top-1/2 left-0 w-full h-px bg-gray-300"></div>
            <div class="absolute top-3/4 left-0 w-full h-px bg-gray-300"></div>
        </div>
        <div class="max-w-4xl mx-auto text-center relative z-10">
            <!-- Main Heading -->
            <h1 class="text-3xl md:text-4xl lg:text-5xl font-light leading-tight mb-6">
                Say it.
                <span class="block">As it is.</span>
            </h1>

            <!-- Subtitle -->
            <h2 class="text-base md:text-lg lg:text-xl text-gray-600 mb-8 max-w-3xl mx-auto leading-relaxed font-light">
                Anonymous stories, rants, and confessions — raw and unpolished.
            </h2>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
                <a href="/post/create"
                    class="bg-black text-white px-7 py-3 rounded-lg text-base font-medium hover:bg-white hover:text-black border-2 border-black transition-all duration-200 min-w-[180px]">
                    Share Your Story
                </a>
                <a href="{{ route('feed') }}"
                    class="bg-white text-black px-7 py-3 rounded-lg text-base font-medium hover:bg-black hover:text-white border-2 border-black transition-all duration-200 min-w-[180px]">
                    Browse Posts
                </a>
            </div>

            <!-- Secondary Tagline -->
            <p class="text-base text-gray-500 max-w-2xl mx-auto">
                Bluntly is the space to say what you can't say anywhere else.
            </p>
        </div>
    </section>

    <!-- Weekly Theme Banner -->
    @if ($currentTheme)
        <section class="py-12 px-6 bg-gray-50 border-y border-gray-100">
            <div class="max-w-4xl mx-auto text-center">
                <div
                    class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-lg text-sm font-medium text-gray-600 mb-4 border border-gray-200">
                    <span>This Week's Theme</span>
                    @if ($currentTheme->days_remaining > 0)
                        <span class="text-gray-400">• {{ $currentTheme->days_remaining }}
                            day{{ $currentTheme->days_remaining == 1 ? '' : 's' }} left</span>
                    @endif
                </div>

                <h2 class="text-xl md:text-2xl font-medium text-gray-900 mb-4">
                    {{ $currentTheme->name }}
                </h2>

                <p class="text-base text-gray-600 mb-6 max-w-2xl mx-auto leading-relaxed">
                    {{ $currentTheme->description }}
                </p>

                @if ($currentTheme->prompt_text)
                    <div class="bg-white rounded-lg p-4 mb-6 border border-gray-200">
                        <p class="text-gray-600 italic">
                            "{{ $currentTheme->prompt_text }}"
                        </p>
                    </div>
                @endif

                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="{{ route('post.create') }}?theme={{ $currentTheme->slug }}"
                        class="bg-black text-white px-8 py-3 rounded-lg text-base font-medium hover:bg-gray-800 transition-colors">
                        Share Your Story
                    </a>

                    @if ($currentTheme->stories()->published()->count() > 0)
                        <a href="{{ route('feed') }}?theme={{ $currentTheme->slug }}"
                            class="text-gray-600 font-medium hover:text-black transition-colors">
                            Read {{ $currentTheme->stories()->published()->count() }}
                            {{ $currentTheme->stories()->published()->count() == 1 ? 'story' : 'stories' }} →
                        </a>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <!-- Trending Stories Preview Section -->
    <section class="py-16 px-6 bg-gray-50">
        <div class="max-w-6xl mx-auto">
            <h3 class="text-xl md:text-2xl font-semibold text-center mb-8">Trending Stories</h3>

            <div class="grid md:grid-cols-3 gap-8 mb-12">
                @forelse ($trendingStories as $story)
                    <div
                        class="bg-white p-6 rounded-lg border border-gray-200 hover:shadow-lg transition-shadow duration-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm text-gray-600 font-medium">{{ '@' . $story->alias }}</span>
                            <div class="flex items-center gap-2">
                                @if ($story->theme)
                                    <a href="{{ route('theme.details', $story->theme->slug) }}" 
                                       class="text-xs bg-black text-white px-2 py-1 rounded-full hover:bg-gray-800 transition-colors">
                                        {{ Str::limit($story->theme->name, 20) }}
                                    </a>
                                @endif
                                @if ($story->category)
                                    <span class="text-xs text-gray-400">{{ strtoupper($story->category) }}</span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('post', $story->slug) }}">
                            <h4 class="text-lg font-medium mb-3 leading-tight hover:text-gray-700 transition-colors">
                                {{ $story->title }}
                            </h4>
                        </a>
                        <p class="text-gray-600 leading-relaxed mb-4">
                            {{ Str::limit(strip_tags($story->body), 120) }}
                        </p>
                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-1">
                                    <button class="hover:text-green-600 transition-colors"
                                        aria-label="Upvote story">↑</button>
                                    <span class="text-xs font-medium">{{ $story->upvotes }}</span>
                                    <button class="hover:text-red-600 transition-colors"
                                        aria-label="Downvote story">↓</button>
                                </div>
                                <span class="flex items-center gap-1">
                                    <span aria-label="Comments">💬</span>
                                    <span class="text-xs">{{ $story->comments->count() }}</span>
                                </span>
                            </div>
                            <div class="flex items-center gap-4">
                                <span><span aria-label="Views">👁</span> {{ number_format($story->views) }}</span>
                                <span>{{ $story->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12">
                        <p class="text-gray-500">No trending stories available yet.</p>
                        <a href="/post/create"
                            class="inline-block mt-4 bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                            Be the first to share your story
                        </a>
                    </div>
                @endforelse
            </div>

            <div class="text-center">
                <a href="{{ route('feed') }}"
                    class="inline-block bg-black text-white px-8 py-3 rounded-lg text-lg font-medium hover:bg-gray-800 transition-colors duration-200">
                    See All Stories
                </a>
            </div>
        </div>
    </section>

    <x-footer />
@endsection
