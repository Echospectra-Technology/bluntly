<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Story;
use App\Models\Tag;
use App\Services\AnonymousUserService;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public $currentFilter = 'newest';
    public $selectedCategory = 'all';
    public $selectedTheme = null;

    public function mount()
    {
        // Check for theme parameter in URL
        $themeSlug = request()->get('theme');
        if ($themeSlug) {
            $theme = \App\Models\WeeklyTheme::where('slug', $themeSlug)->first();
            if ($theme) {
                $this->selectedTheme = $theme->id;
            }
        }
    }

    public function setFilter($filter)
    {
        $this->currentFilter = $filter;
        $this->resetPage();
    }

    public function setCategory($category)
    {
        $this->selectedCategory = $category;
        $this->resetPage();
    }

    public function clearThemeFilter()
    {
        $this->selectedTheme = null;
        $this->resetPage();
    }

    public function getCurrentThemeProperty()
    {
        if ($this->selectedTheme) {
            return \App\Models\WeeklyTheme::find($this->selectedTheme);
        }
        return null;
    }

    public function getStoriesProperty()
    {
        $query = Story::with(['tags', 'comments', 'theme'])->where('status', 'published');

        // Apply category filter
        if ($this->selectedCategory !== 'all') {
            $query->where('category', $this->selectedCategory);
        }

        // Apply theme filter
        if ($this->selectedTheme) {
            $query->where('theme_id', $this->selectedTheme);
        }

        // Apply sorting
        switch ($this->currentFilter) {
            case 'trending':
                // Trending: High vote ratio in last 24 hours
                $query
                    ->where('created_at', '>=', now()->subDay())
                    ->orderByRaw('(upvotes - downvotes) DESC')
                    ->orderBy('views', 'desc');
                break;
            case 'popular':
                // Popular: All time high votes
                $query->orderByRaw('(upvotes - downvotes) DESC')->orderBy('views', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        return $query->paginate(10);
    }

    public function getTrendingTagsProperty()
    {
        return Tag::withCount([
            'stories' => function ($query) {
                $query->where('status', 'published')->where('created_at', '>=', now()->subWeek());
            },
        ])
            ->orderBy('stories_count', 'desc')
            ->limit(8)
            ->get();
    }

    public function getTopStoriesProperty()
    {
        return Story::with(['tags'])
            ->where('status', 'published')
            ->where('created_at', '>=', now()->subWeek())
            ->orderByRaw('(upvotes - downvotes) DESC')
            ->limit(3)
            ->get();
    }

    public function toggleVote($storyId, $voteType)
    {
        $anonymousService = app(AnonymousUserService::class);
        $anonymousId = $anonymousService->getAnonymousId();

        $existingVote = \App\Models\Vote::where('item_type', 'story')->where('item_id', $storyId)->where('cookie_hash', $anonymousId)->first();

        if ($existingVote) {
            if ($existingVote->value === $voteType) {
                // Remove vote if clicking same button
                $existingVote->delete();
                $this->updateStoryVoteCounts($storyId);
            } else {
                // Change vote
                $existingVote->update(['value' => $voteType]);
                $this->updateStoryVoteCounts($storyId);
            }
        } else {
            // Create new vote
            \App\Models\Vote::create([
                'item_type' => 'story',
                'item_id' => $storyId,
                'value' => $voteType,
                'cookie_hash' => $anonymousId,
                'created_at' => now(),
            ]);
            $this->updateStoryVoteCounts($storyId);
        }
    }

    private function updateStoryVoteCounts($storyId)
    {
        $upvotes = \App\Models\Vote::where('item_type', 'story')->where('item_id', $storyId)->where('value', 'up')->count();

        $downvotes = \App\Models\Vote::where('item_type', 'story')->where('item_id', $storyId)->where('value', 'down')->count();

        Story::where('id', $storyId)->update([
            'upvotes' => $upvotes,
            'downvotes' => $downvotes,
        ]);
    }

    public function getUserVote($storyId)
    {
        $anonymousService = app(AnonymousUserService::class);
        return $anonymousService->hasVotedOn('story', $storyId);
    }

    public function loadMore()
    {
        $this->nextPage();
    }
}; ?>

<div>
    <x-navigation current-page="stories" />

    <!-- Main Content -->
    <div class="bg-white min-h-screen">
        <!-- Header Section -->
        <div class="bg-gray-50 border-b border-gray-100">
            <div class="max-w-6xl mx-auto px-4 md:px-6 py-4 md:py-6">
                <div class="max-w-4xl">
                    <h1 class="text-lg md:text-xl font-light mb-2">Stories</h1>
                    <p class="text-xs text-gray-600 font-light">Anonymous voices, unfiltered truths</p>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white border-b border-gray-100 sticky top-0 z-10">
            <div class="max-w-6xl mx-auto px-4 md:px-6 py-4">
                <div class="space-y-3">
                    <!-- Sort Options -->
                    <div class="flex items-center space-x-1 overflow-x-auto pb-1">
                        <button wire:click="setFilter('newest')"
                            class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-colors {{ $currentFilter === 'newest' ? 'bg-black text-white' : 'text-gray-600 hover:text-black' }}">
                            Newest
                        </button>
                        <button wire:click="setFilter('trending')"
                            class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-colors {{ $currentFilter === 'trending' ? 'bg-black text-white' : 'text-gray-600 hover:text-black' }}">
                            Trending
                        </button>
                        <button wire:click="setFilter('popular')"
                            class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-colors {{ $currentFilter === 'popular' ? 'bg-black text-white' : 'text-gray-600 hover:text-black' }}">
                            Popular
                        </button>
                    </div>

                    <!-- Category Filter -->
                    <div class="flex items-center space-x-1 overflow-x-auto pb-1">
                        <button wire:click="setCategory('all')"
                            class="flex-shrink-0 px-3 py-1.5 rounded-full text-sm font-medium transition-colors {{ $selectedCategory === 'all' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:text-black border border-gray-200' }}">
                            All
                        </button>
                        <button wire:click="setCategory('confession')"
                            class="flex-shrink-0 px-3 py-1.5 rounded-full text-sm font-medium transition-colors {{ $selectedCategory === 'confession' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:text-black border border-gray-200' }}">
                            Confession
                        </button>
                        <button wire:click="setCategory('rant')"
                            class="flex-shrink-0 px-3 py-1.5 rounded-full text-sm font-medium transition-colors {{ $selectedCategory === 'rant' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:text-black border border-gray-200' }}">
                            Rant
                        </button>
                        <button wire:click="setCategory('gist')"
                            class="flex-shrink-0 px-3 py-1.5 rounded-full text-sm font-medium transition-colors {{ $selectedCategory === 'gist' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:text-black border border-gray-200' }}">
                            Gist
                        </button>
                        <button wire:click="setCategory('story')"
                            class="flex-shrink-0 px-3 py-1.5 rounded-full text-sm font-medium transition-colors {{ $selectedCategory === 'story' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:text-black border border-gray-200' }}">
                            Story
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Theme Filter Display -->
        @if ($this->currentTheme)
            <div class="bg-gradient-to-r from-purple-50 to-blue-50 border-b border-purple-200">
                <div class="max-w-6xl mx-auto px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div>
                                <h3 class="font-medium text-gray-900">{{ $this->currentTheme->name }}</h3>
                                <p class="text-sm text-gray-600">{{ $this->currentTheme->description }}</p>
                            </div>
                        </div>
                        <button wire:click="clearThemeFilter"
                            class="text-sm text-purple-600 hover:text-purple-700 font-medium">
                            Show All Stories ×
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Weekly Theme Banner for Feed -->
        @if (!$this->currentTheme)
            @php
                $feedCurrentTheme = \App\Models\WeeklyTheme::current()->first();
            @endphp
            @if ($feedCurrentTheme)
                <div class="bg-gray-50 border-b border-gray-100">
                    <div class="max-w-6xl mx-auto px-4 md:px-6 py-4 md:py-6">
                        <div class="space-y-3 md:space-y-0 md:flex md:items-center md:justify-between">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-8 h-8 md:w-10 md:h-10 bg-black text-white rounded-lg flex items-center justify-center text-xs font-medium">
                                        T
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <h3 class="font-medium text-gray-900 text-sm md:text-base">
                                            {{ $feedCurrentTheme->name }}</h3>
                                        <span class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded-full">This
                                            Week</span>
                                        @if ($feedCurrentTheme->days_remaining > 0)
                                            <span class="text-xs text-gray-500 hidden sm:inline">
                                                {{ $feedCurrentTheme->days_remaining }}
                                                day{{ $feedCurrentTheme->days_remaining == 1 ? '' : 's' }} left
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs md:text-sm text-gray-600 leading-relaxed">
                                        {{ Str::limit($feedCurrentTheme->description, 100) }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 md:gap-3 md:flex-shrink-0">
                                <a href="{{ route('post.create') }}?theme={{ $feedCurrentTheme->slug }}"
                                    class="bg-black text-white px-3 md:px-4 py-2 rounded-lg text-xs md:text-sm font-medium hover:bg-gray-800 transition-colors">
                                    Share Story
                                </a>
                                <a href="{{ route('feed') }}?theme={{ $feedCurrentTheme->slug }}"
                                    class="text-gray-600 text-xs md:text-sm font-medium hover:text-black transition-colors">
                                    Filter by Theme →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- Main Content Grid -->
        <div class="max-w-6xl mx-auto px-4 md:px-6 py-4 md:py-6">
            <div class="grid lg:grid-cols-3 lg:gap-8">
                <!-- Stories Feed -->
                <div class="lg:col-span-2">
                    <div class="space-y-4 md:space-y-6">
                        @forelse ($this->stories as $story)
                            <article class="border-b border-gray-100 pb-4 md:pb-6 last:border-b-0">
                                <!-- Mobile-first metadata layout -->
                                <div class="mb-4 space-y-2">
                                    <!-- Main metadata line -->
                                    <div class="flex items-center text-sm">
                                        <span class="font-medium text-gray-700">{{ '@' . $story->alias }}</span>
                                        <span class="mx-2 text-gray-300">•</span>
                                        <span class="text-gray-500">{{ $story->created_at->diffForHumans() }}</span>
                                        @if ($story->category)
                                            <span class="mx-2 text-gray-300">•</span>
                                            <span
                                                class="text-xs uppercase tracking-wide text-gray-400 bg-gray-100 px-2 py-1 rounded">
                                                {{ $story->category }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Theme badge on separate line for mobile -->
                                    @if ($story->theme)
                                        <div class="flex items-center">
                                            <a href="{{ route('theme.details', $story->theme->slug) }}"
                                                class="inline-flex items-center text-xs bg-black text-white px-3 py-1.5 rounded-full hover:bg-gray-800 transition-colors">
                                                <span class="w-1.5 h-1.5 bg-white rounded-full mr-2"></span>
                                                {{ $story->theme->name }}
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <a href="{{ route('post', $story->slug) }}" class="block">
                                    <h2
                                        class="text-base md:text-lg lg:text-xl font-medium mb-2 leading-tight hover:text-gray-700 cursor-pointer transition-colors">
                                        {{ $story->title }}
                                    </h2>
                                </a>

                                <p class="text-xs md:text-sm text-gray-600 leading-relaxed mb-3">
                                    {{ Str::limit(strip_tags($story->body), 250) }}
                                </p>

                                <!-- Tags -->
                                @if ($story->tags->isNotEmpty())
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        @foreach ($story->tags->take(3) as $tag)
                                            <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs">
                                                #{{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4 md:space-x-6 text-sm text-gray-500">
                                        <div class="flex items-center gap-1">
                                            <button wire:click="toggleVote({{ $story->id }}, 'up')"
                                                class="hover:text-green-600 transition-colors p-1 {{ $this->getUserVote($story->id) === 'up' ? 'text-green-600' : '' }}">
                                                ↑
                                            </button>
                                            <span
                                                class="text-xs font-medium min-w-[1rem] text-center">{{ $story->upvotes }}</span>
                                            <button wire:click="toggleVote({{ $story->id }}, 'down')"
                                                class="hover:text-red-600 transition-colors p-1 {{ $this->getUserVote($story->id) === 'down' ? 'text-red-600' : '' }}">
                                                ↓
                                            </button>
                                        </div>
                                        <span class="flex items-center gap-1">
                                            <span>💬</span>
                                            <span class="text-xs hidden sm:inline">{{ $story->comments->count() }}
                                                comments</span>
                                            <span class="text-xs sm:hidden">{{ $story->comments->count() }}</span>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <span>👁</span>
                                            <span class="text-xs hidden sm:inline">{{ number_format($story->views) }}
                                                views</span>
                                            <span class="text-xs sm:hidden">{{ number_format($story->views) }}</span>
                                        </span>
                                    </div>

                                    <button onclick="copyToClipboard('{{ route('post', $story->slug) }}')"
                                        class="text-gray-500 hover:text-black transition-colors text-xs md:text-sm px-2 py-1 rounded hover:bg-gray-100 flex items-center gap-1">
                                        <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z">
                                            </path>
                                        </svg>
                                        <span class="hidden sm:inline">Share</span>
                                    </button>
                                </div>
                            </article>
                        @empty
                            <div class="text-center py-12">
                                <p class="text-gray-500">No stories found for the selected criteria.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Infinite Scroll Loading -->
                    @if ($this->stories->hasMorePages())
                        <!-- Subtle loading indicator that only shows when loading -->
                        <div wire:loading wire:target="loadMore" class="flex justify-center py-8">
                            <div class="flex items-center space-x-2 text-gray-400">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span class="text-sm">Loading...</span>
                            </div>
                        </div>

                        <!-- Infinite Scroll Trigger -->
                        <div x-data="infiniteScroll" x-init="init()" class="h-1" x-ref="sentinel"></div>
                    @endif
                </div>

                <!-- Right Sidebar -->
                <div class="hidden lg:block space-y-6 sticky top-35 self-start">
                    <!-- Top Stories -->
                    <div class="bg-gray-50 p-5 rounded-lg">
                        <h3 class="text-sm font-medium mb-3">Top Picks</h3>
                        <div class="space-y-3">
                            @foreach ($this->topStories as $story)
                                <article class="border-b border-gray-200 pb-3 last:border-b-0">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span
                                            class="text-xs font-medium text-gray-700">{{ '@' . $story->alias }}</span>
                                        @if ($story->category)
                                            <span class="text-xs text-gray-400 bg-gray-200 px-2 py-1 rounded">
                                                {{ $story->category }}
                                            </span>
                                        @endif
                                    </div>
                                    <a href="{{ route('post', $story->slug) }}">
                                        <h4
                                            class="text-sm font-medium text-gray-900 mb-2 leading-tight hover:text-gray-700 transition-colors">
                                            {{ Str::limit($story->title, 60) }}
                                        </h4>
                                    </a>
                                    <div class="flex items-center gap-3 text-xs text-gray-500">
                                        <span class="flex items-center gap-1">
                                            <span>↑</span>
                                            <span>{{ $story->upvotes }}</span>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <span>💬</span>
                                            <span>{{ $story->comments->count() }}</span>
                                        </span>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>

                    <!-- Featured/Ad Space -->
                    <div class="bg-gradient-to-br from-gray-900 to-black p-5 rounded-lg text-white">
                        <div class="text-center">
                            <h3 class="text-sm font-medium mb-2">Share Your Story</h3>
                            <p class="text-xs text-gray-300 mb-4">Your voice matters. Post anonymously and connect with
                                others who understand.</p>
                            <a href="/post/create"
                                class="inline-block bg-white text-black px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors">
                                Write Your Story
                            </a>
                        </div>
                    </div>

                    <!-- Trending Topics -->
                    <div class="bg-gray-50 p-5 rounded-lg">
                        <h3 class="text-sm font-medium mb-3">Trending Topics</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($this->trendingTags as $tag)
                                <button wire:click="setCategory('{{ $tag->slug }}')"
                                    class="bg-white border border-gray-200 px-3 py-1.5 rounded-full text-xs text-gray-700 hover:border-gray-300 cursor-pointer transition-colors {{ $selectedCategory === $tag->slug ? 'bg-black text-white border-black' : '' }}">
                                    #{{ $tag->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Community Guidelines -->
                    <div class="bg-gray-50 p-5 rounded-lg">
                        <h3 class="text-sm font-medium mb-3">Community</h3>
                        <div class="space-y-2 text-xs text-gray-600">
                            <a href="/rules" class="block hover:text-black transition-colors">Community
                                Guidelines</a>
                            <a href="/about" class="block hover:text-black transition-colors">About
                                Bluntly</a>
                            <a href="/privacy" class="block hover:text-black transition-colors">Privacy Policy</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-footer />

    <script>
        function copyToClipboard(url) {
            navigator.clipboard.writeText(url).then(() => {
                // Simple feedback - you could replace this with a toast notification
                const button = event.target.closest('button');
                const originalText = button.querySelector('svg + text') || button.textContent;
                button.innerHTML = button.innerHTML.replace('Share', 'Copied!');
                setTimeout(() => {
                    button.innerHTML = button.innerHTML.replace('Copied!', 'Share');
                }, 2000);
            }).catch(() => {
                // Fallback for older browsers
                const textarea = document.createElement('textarea');
                textarea.value = url;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                alert('Link copied to clipboard!');
            });
        }

        // Alpine.js component for infinite scroll
        document.addEventListener('alpine:init', () => {
            Alpine.data('infiniteScroll', () => ({
                observer: null,
                isLoading: false,

                init() {
                    this.setupIntersectionObserver();
                },

                setupIntersectionObserver() {
                    this.observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting && !this.isLoading) {
                                this.loadMore();
                            }
                        });
                    }, {
                        rootMargin: '300px 0px', // Trigger 300px before the element is visible
                        threshold: 0.1
                    });

                    this.observer.observe(this.$refs.sentinel);
                },

                loadMore() {
                    if (this.isLoading) return;

                    this.isLoading = true;

                    // Call Livewire loadMore method
                    this.$wire.call('loadMore').then(() => {
                        this.isLoading = false;
                    }).catch(() => {
                        this.isLoading = false;
                    });
                }
            }));
        });
    </script>
</div>
