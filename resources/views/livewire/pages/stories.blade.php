<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Story;
use App\Models\WeeklyTheme;
use App\Models\Tag;
use App\Models\Vote;
use App\Services\AnonymousUserService;
use App\Services\PersonalizedFeedService;

new #[Layout('layouts.app')] class extends Component {
    public $currentFilter = 'newest';
    public $selectedCategory = 'all';
    public $selectedTheme = null;
    public $isLoadingMore = false;
    public $currentPage = 1;
    public $perPage = 10;
    public $storyIds = [];
    public $hasMorePages = true;
    public $totalViewedToday = 0;
    public $showProgressMarker = true;

    public function mount()
    {
        // Check for theme parameter in URL
        $themeSlug = request()->get('theme');
        if ($themeSlug) {
            $theme = WeeklyTheme::where('slug', $themeSlug)->first();
            if ($theme) {
                $this->selectedTheme = $theme->id;
            }
        }

        // Initialize session tracking for progress
        $this->totalViewedToday = session('stories_viewed_today', 0);

        // Load initial stories
        $this->loadInitialStories();
    }

    public function loadInitialStories()
    {
        if ($this->currentFilter === 'personalized' || $this->currentFilter === 'newest') {
            $this->loadPersonalizedStories();
        } else {
            $stories = $this->getStoriesQuery()->take($this->perPage)->get();

            $this->storyIds = $stories->pluck('id')->toArray();
            $this->hasMorePages = $stories->count() === $this->perPage;
        }
    }

    private function loadPersonalizedStories()
    {
        $anonymousService = app(AnonymousUserService::class);
        $personalizedFeedService = app(PersonalizedFeedService::class);

        $anonymousId = $anonymousService->getAnonymousId();
        $userLocation = $anonymousService->getUserLocation($anonymousId);

        // Request more stories than perPage to check if there are more
        $stories = $personalizedFeedService->getPersonalizedFeed(
            $anonymousId,
            $userLocation,
            $this->perPage + 1, // Request one extra to check for more
            [], // No exclude IDs for initial load
            $this->selectedCategory,
            $this->selectedTheme,
        );

        // Check if there are more stories available
        $this->hasMorePages = $stories->count() > $this->perPage;

        // Take only the requested number of stories
        $actualStories = $stories->take($this->perPage);
        $this->storyIds = $actualStories->pluck('id')->toArray();
    }

    public function setFilter($filter)
    {
        $this->currentFilter = $filter;
        $this->resetStories();
    }

    public function setCategory($category)
    {
        $this->selectedCategory = $category;
        $this->resetStories();
    }

    public function resetStories()
    {
        $this->currentPage = 1;
        $this->storyIds = [];
        $this->hasMorePages = true;
        $this->loadInitialStories();
    }

    public function clearThemeFilter()
    {
        $this->selectedTheme = null;
        $this->resetStories();
    }

    public function getCurrentThemeProperty()
    {
        if ($this->selectedTheme) {
            return WeeklyTheme::find($this->selectedTheme);
        }
        return null;
    }

    public function getStoriesQuery()
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

        return $query;
    }

    public function getStoriesProperty()
    {
        if (empty($this->storyIds)) {
            return collect();
        }

        // Fetch fresh models with relationships in the correct order
        $stories = Story::with([
            'tags',
            'comments' => function ($query) {
                $query->whereNull('parent_id')->where('status', 'published')->orderByRaw('(upvotes - downvotes) DESC')->limit(2);
            },
            'theme',
        ])
            ->whereIn('id', $this->storyIds)
            ->get()
            ->sortBy(function ($story) {
                return array_search($story->id, $this->storyIds);
            });

        return $stories;
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

        $existingVote = Vote::where('item_type', 'story')->where('item_id', $storyId)->where('cookie_hash', $anonymousId)->first();

        if ($existingVote) {
            if ($existingVote->value === $voteType) {
                // Remove vote if clicking same button
                $existingVote->delete();
                $this->updateStoryVoteCounts($storyId);
            } else {
                // Change vote
                $existingVote->update(['value' => $voteType]);
                $this->updateStoryVoteCounts($storyId);
                // Track the interaction for personalization
                $anonymousService->trackInteraction($anonymousId, $storyId, $voteType === 'up' ? 'upvote' : 'downvote');
            }
        } else {
            // Create new vote
            Vote::create([
                'item_type' => 'story',
                'item_id' => $storyId,
                'value' => $voteType,
                'cookie_hash' => $anonymousId,
                'created_at' => now(),
            ]);
            $this->updateStoryVoteCounts($storyId);
            // Track the interaction for personalization
            $anonymousService->trackInteraction($anonymousId, $storyId, $voteType === 'up' ? 'upvote' : 'downvote');
        }
    }

    private function updateStoryVoteCounts($storyId)
    {
        $upvotes = Vote::where('item_type', 'story')->where('item_id', $storyId)->where('value', 'up')->count();

        $downvotes = Vote::where('item_type', 'story')->where('item_id', $storyId)->where('value', 'down')->count();

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
        if ($this->isLoadingMore || !$this->hasMorePages) {
            return;
        }

        $this->isLoadingMore = true;

        try {
            $this->currentPage++;

            if ($this->currentFilter === 'personalized' || $this->currentFilter === 'newest') {
                $this->loadMorePersonalizedStories();
            } else {
                $newStories = $this->getStoriesQuery()
                    ->skip(($this->currentPage - 1) * $this->perPage)
                    ->take($this->perPage)
                    ->get();

                if ($newStories->count() > 0) {
                    // Append new story IDs to existing collection
                    $newIds = $newStories->pluck('id')->toArray();
                    $this->storyIds = array_merge($this->storyIds, $newIds);

                    // Update viewed count
                    $this->totalViewedToday += $newStories->count();
                    session(['stories_viewed_today' => $this->totalViewedToday]);

                    // Check if there are more pages
                    $this->hasMorePages = $newStories->count() === $this->perPage;
                } else {
                    $this->hasMorePages = false;
                }
            }
        } finally {
            $this->isLoadingMore = false;
        }
    }

    private function loadMorePersonalizedStories()
    {
        $anonymousService = app(AnonymousUserService::class);
        $personalizedFeedService = app(PersonalizedFeedService::class);

        $anonymousId = $anonymousService->getAnonymousId();
        $userLocation = $anonymousService->getUserLocation($anonymousId);

        // Request more stories than perPage to check if there are more
        $newStories = $personalizedFeedService->getPersonalizedFeed(
            $anonymousId,
            $userLocation,
            $this->perPage + 1, // Request one extra to check for more
            $this->storyIds, // Exclude already loaded stories
            $this->selectedCategory,
            $this->selectedTheme,
        );

        if ($newStories->count() > 0) {
            // Check if there are more stories available
            $this->hasMorePages = $newStories->count() > $this->perPage;

            // Take only the requested number of stories
            $actualNewStories = $newStories->take($this->perPage);
            $newIds = $actualNewStories->pluck('id')->toArray();
            $this->storyIds = array_merge($this->storyIds, $newIds);
        } else {
            $this->hasMorePages = false;
        }
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
                            <span class="flex items-center gap-1">
                                <span>✨</span>
                                For You
                            </span>
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
                $feedCurrentTheme = WeeklyTheme::current()->first();
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
                                    Show Theme Posts →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- Main Content Grid -->
        <div class="max-w-6xl mx-auto px-4 md:px-6 py-2 md:py-3">
            <div class="grid lg:grid-cols-3 lg:gap-8">
                <!-- Stories Feed -->
                <div class="lg:col-span-2">
                    <div class="space-y-1 md:space-y-2">
                        @forelse ($this->stories as $index => $story)
                            <!-- Progress marker every 10 posts -->
                            @if ($index > 0 && $index % 10 === 0 && $showProgressMarker)
                                <div class="flex items-center justify-center py-3 my-2">
                                    <div
                                        class="bg-gradient-to-r from-gray-100 via-gray-200 to-gray-100 rounded-full px-4 py-2 shadow-sm">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                            <span class="text-xs text-gray-600 font-medium">
                                                Story {{ $index + $totalViewedToday }} of
                                                {{ $index + $totalViewedToday + 20 }} today
                                            </span>
                                            <div class="w-2 h-2 bg-blue-400 rounded-full animate-pulse"
                                                style="animation-delay: 0.5s"></div>
                                        </div>
                                        <div class="text-center mt-1">
                                            <span class="text-[10px] text-gray-500">Keep scrolling for more →</span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <article
                                class="border-b border-gray-50 pb-2 md:pb-3 last:border-b-0 hover:bg-gray-50/50 transition-colors duration-200 
                                {{ $index % 2 === 1 ? 'bg-gray-50/20' : 'bg-white' }} px-2 md:px-3 py-2 rounded-sm">
                                <!-- Mobile-first metadata layout -->
                                <div class="mb-2 space-y-1">
                                    <!-- Main metadata line -->
                                    <div class="flex items-center text-xs">
                                        <span
                                            class="font-medium text-gray-700 text-xs">{{ '@' . $story->alias }}</span>
                                        <span class="mx-2 text-gray-300">•</span>
                                        <span
                                            class="text-gray-500 text-xs">{{ $story->created_at->diffForHumans() }}</span>
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
                                        class="text-sm md:text-base lg:text-lg font-medium mb-1 leading-tight hover:text-gray-700 cursor-pointer transition-colors">
                                        {{ $story->title }}
                                    </h2>
                                </a>

                                <div class="text-xs text-gray-600 leading-snug mb-2">
                                    @php
                                        $preview = Str::limit(strip_tags($story->body), 150);
                                        $isLimited = strlen(strip_tags($story->body)) > 150;
                                    @endphp
                                    <div class="line-clamp-3">
                                        {{ $preview }}
                                        @if ($isLimited)
                                            <a href="{{ route('post', $story->slug) }}"
                                                class="text-gray-800 hover:text-black font-medium transition-colors">...Read
                                                more</a>
                                        @endif
                                    </div>
                                </div>

                                <!-- Tags -->
                                @if ($story->tags->isNotEmpty())
                                    <div class="flex flex-wrap gap-1 mb-2">
                                        @foreach ($story->tags->take(3) as $tag)
                                            <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs">
                                                #{{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="flex items-center justify-between group">
                                    <div class="flex items-center space-x-3 md:space-x-4 text-xs text-gray-500">
                                        <div class="flex items-center gap-1">
                                            <button wire:click="toggleVote({{ $story->id }}, 'up')"
                                                class="hover:text-green-600 hover:bg-green-50 transition-all duration-200 p-1.5 rounded-full {{ $this->getUserVote($story->id) === 'up' ? 'text-green-600 bg-green-50' : '' }}">
                                                ↑
                                            </button>
                                            <span
                                                class="text-xs font-medium min-w-[1rem] text-center">{{ $story->upvotes }}</span>
                                            <button wire:click="toggleVote({{ $story->id }}, 'down')"
                                                class="hover:text-red-600 hover:bg-red-50 transition-all duration-200 p-1.5 rounded-full {{ $this->getUserVote($story->id) === 'down' ? 'text-red-600 bg-red-50' : '' }}">
                                                ↓
                                            </button>
                                        </div>
                                        <a href="{{ route('post', $story->slug) }}#comments"
                                            class="flex items-center gap-1 hover:text-gray-800 transition-colors">
                                            <span>💬</span>
                                            <span class="text-xs hidden sm:inline">{{ $story->comments->count() }}
                                                comments</span>
                                            <span class="text-xs sm:hidden">{{ $story->comments->count() }}</span>
                                        </a>
                                        <span class="flex items-center gap-1">
                                            <span>👁</span>
                                            <span class="text-xs hidden sm:inline">{{ number_format($story->views) }}
                                                views</span>
                                            <span class="text-xs sm:hidden">{{ number_format($story->views) }}</span>
                                        </span>
                                    </div>

                                    <!-- Quick actions - visible on hover for desktop -->
                                    <div
                                        class="hidden md:flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <button onclick="copyToClipboard('{{ route('post', $story->slug) }}')"
                                            class="text-gray-500 hover:text-black transition-colors text-xs px-2 py-1 rounded hover:bg-gray-100 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z">
                                                </path>
                                            </svg>
                                            Share
                                        </button>
                                        <a href="{{ route('post', $story->slug) }}"
                                            class="text-gray-500 hover:text-black transition-colors text-xs px-2 py-1 rounded hover:bg-gray-100">
                                            Read
                                        </a>
                                    </div>
                                    <!-- Mobile share button -->
                                    <button onclick="copyToClipboard('{{ route('post', $story->slug) }}')"
                                        class="md:hidden text-gray-500 hover:text-black transition-colors text-xs px-2 py-1 rounded hover:bg-gray-100 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z">
                                            </path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Inline Comments Preview -->
                                @if ($story->comments->count() > 0)
                                    <div class="mt-2 pt-2 border-t border-gray-50">
                                        <div class="space-y-1">
                                            @foreach ($story->comments->take(2) as $comment)
                                                <div class="flex items-start gap-2 text-xs">
                                                    <div
                                                        class="w-4 h-4 bg-gray-200 rounded-full flex items-center justify-center text-[10px] text-gray-600 flex-shrink-0">
                                                        {{ strtoupper(substr($comment->alias, 0, 1)) }}
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <span
                                                            class="font-medium text-gray-700 text-[10px]">{{ '@' . $comment->alias }}</span>
                                                        <p class="text-gray-600 line-clamp-2 leading-tight">
                                                            {{ Str::limit(strip_tags($comment->body), 80) }}
                                                        </p>
                                                    </div>
                                                    @if ($comment->upvotes > 0)
                                                        <span
                                                            class="text-[10px] text-green-600 flex items-center gap-0.5">
                                                            ↑{{ $comment->upvotes }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                            @if ($story->comments->count() > 2)
                                                <a href="{{ route('post', $story->slug) }}#comments"
                                                    class="text-[10px] text-gray-500 hover:text-gray-800 transition-colors">
                                                    View {{ $story->comments->count() - 2 }} more comments →
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </article>
                        @empty
                            <div class="text-center py-12">
                                <p class="text-gray-500">No stories found for the selected criteria.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Infinite Scroll Loading -->
                    @if ($hasMorePages)
                        <!-- Enhanced loading indicator with skeleton preview -->
                        <div class="flex justify-center py-4">
                            <div wire:loading wire:target="loadMore"
                                class="flex items-center space-x-2 text-gray-400 animate-pulse">
                                <div class="flex space-x-1">
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"
                                        style="animation-delay: 0.1s"></div>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"
                                        style="animation-delay: 0.2s"></div>
                                </div>
                                <span class="text-xs font-medium">Loading more stories...</span>
                            </div>
                        </div>

                        <!-- Skeleton loading preview -->
                        <div wire:loading wire:target="loadMore" class="space-y-2">
                            @for ($i = 0; $i < 3; $i++)
                                <div class="bg-gray-50/50 p-3 rounded-sm animate-pulse">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="w-4 h-4 bg-gray-200 rounded-full"></div>
                                        <div class="h-3 bg-gray-200 rounded w-20"></div>
                                        <div class="h-3 bg-gray-200 rounded w-16"></div>
                                    </div>
                                    <div class="h-4 bg-gray-200 rounded w-full mb-2"></div>
                                    <div class="h-3 bg-gray-200 rounded w-3/4"></div>
                                </div>
                            @endfor
                        </div>

                        <!-- Infinite Scroll Trigger -->
                        <div x-data="infiniteScroll" x-init="init()" class="h-1" x-ref="sentinel"></div>
                    @endif
                </div>

                <!-- Right Sidebar -->
                <div class="hidden lg:block space-y-4 sticky top-35 self-start">
                    <!-- Top Stories -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-xs font-medium mb-2">Top Picks</h3>
                        <div class="space-y-2">
                            @foreach ($this->topStories as $story)
                                <article class="border-b border-gray-200 pb-2 last:border-b-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span
                                            class="text-xs font-medium text-gray-700">{{ '@' . $story->alias }}</span>
                                        @if ($story->category)
                                            <span class="text-[10px] text-gray-400 bg-gray-200 px-1.5 py-0.5 rounded">
                                                {{ $story->category }}
                                            </span>
                                        @endif
                                    </div>
                                    <a href="{{ route('post', $story->slug) }}">
                                        <h4
                                            class="text-xs font-medium text-gray-900 mb-1 leading-tight hover:text-gray-700 transition-colors">
                                            {{ Str::limit($story->title, 60) }}
                                        </h4>
                                    </a>
                                    <div class="flex items-center gap-2 text-[10px] text-gray-500">
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
                    <div class="bg-gradient-to-br from-gray-900 to-black p-4 rounded-lg text-white">
                        <div class="text-center">
                            <h3 class="text-xs font-medium mb-2">Share Your Story</h3>
                            <p class="text-[10px] text-gray-300 mb-3">Your voice matters. Post anonymously and connect
                                with
                                others who understand.</p>
                            <a href="/post/create"
                                class="inline-block bg-white text-black px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-gray-100 transition-colors">
                                Write Your Story
                            </a>
                        </div>
                    </div>

                    <!-- Trending Topics -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-xs font-medium mb-2">Trending Topics</h3>
                        <div class="flex flex-wrap gap-1">
                            @foreach ($this->trendingTags as $tag)
                                <button wire:click="setCategory('{{ $tag->slug }}')"
                                    class="bg-white border border-gray-200 px-2 py-1 rounded-full text-[10px] text-gray-700 hover:border-gray-300 cursor-pointer transition-colors {{ $selectedCategory === $tag->slug ? 'bg-black text-white border-black' : '' }}">
                                    #{{ $tag->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Community Guidelines -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-xs font-medium mb-2">Community</h3>
                        <div class="space-y-1 text-[10px] text-gray-600">
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
                lastLoadTime: 0,

                init() {
                    this.setupIntersectionObserver();
                },

                setupIntersectionObserver() {
                    this.observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting && !this.isLoading && this
                                .canLoad()) {
                                this.loadMore();
                            }
                        });
                    }, {
                        rootMargin: '300px 0px', // Increased trigger distance for faster doomscroll loading
                        threshold: 0.05
                    });

                    this.observer.observe(this.$refs.sentinel);
                },

                canLoad() {
                    // Reduced delay for faster doomscroll experience - minimum 300ms between loads
                    const now = Date.now();
                    return (now - this.lastLoadTime) > 300;
                },

                loadMore() {
                    if (this.isLoading || !this.canLoad()) return;

                    this.isLoading = true;
                    this.lastLoadTime = Date.now();

                    // Call Livewire loadMore method
                    this.$wire.call('loadMore').then(() => {
                        // Add a small delay to ensure smooth loading
                        setTimeout(() => {
                            this.isLoading = false;
                        }, 500);
                    }).catch(() => {
                        this.isLoading = false;
                    });
                }
            }));
        });
    </script>
</div>
