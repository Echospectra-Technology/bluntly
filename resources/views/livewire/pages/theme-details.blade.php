<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\WeeklyTheme;
use App\Models\Story;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public $theme;
    public $currentFilter = 'newest';

    public function mount($slug)
    {
        $this->theme = WeeklyTheme::where('slug', $slug)->firstOrFail();
    }

    public function setFilter($filter)
    {
        $this->currentFilter = $filter;
        $this->resetPage();
    }

    public function getStoriesProperty()
    {
        $query = Story::with(['tags', 'comments'])
            ->where('status', 'published')
            ->where('theme_id', $this->theme->id);

        // Apply sorting
        switch ($this->currentFilter) {
            case 'trending':
                $query
                    ->where('created_at', '>=', now()->subDay())
                    ->orderByRaw('(upvotes - downvotes) DESC')
                    ->orderBy('views', 'desc');
                break;
            case 'popular':
                $query->orderByRaw('(upvotes - downvotes) DESC')->orderBy('views', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        return $query->paginate(10);
    }

    public function getStoryCountProperty()
    {
        return Story::where('status', 'published')
            ->where('theme_id', $this->theme->id)
            ->count();
    }
}; ?>

<div>
    <x-navigation current-page="themes" />

    <!-- Header Section -->
    <div class="bg-gray-50 border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-6 py-8">
            <div class="max-w-4xl">
                <!-- Breadcrumb -->
                <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
                    <a href="{{ route('themes') }}" class="hover:text-black transition-colors">Weekly Themes</a>
                    <span>→</span>
                    <span class="text-gray-900">{{ $theme->name }}</span>
                </div>

                <!-- Theme Header -->
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-500">
                            @if($theme->status === 'active')
                                This Week's Theme
                            @elseif($theme->status === 'upcoming')
                                Upcoming Theme
                            @else
                                Past Theme
                            @endif
                        </span>
                        @if($theme->status === 'active' && $theme->days_remaining > 0)
                            <span class="text-gray-400">• {{ $theme->days_remaining }} day{{ $theme->days_remaining == 1 ? '' : 's' }} left</span>
                        @endif
                    </div>
                </div>

                <h1 class="text-xl md:text-2xl font-medium mb-3">{{ $theme->name }}</h1>
                <p class="text-sm text-gray-600 font-light leading-relaxed mb-4">{{ $theme->description }}</p>
                
                @if($theme->prompt_text)
                    <div class="bg-white rounded-lg p-4 border border-gray-200 mb-4">
                        <p class="text-gray-600 italic text-sm">"{{ $theme->prompt_text }}"</p>
                    </div>
                @endif

                <!-- Stats and Actions -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="text-sm text-gray-600">
                        {{ $this->storyCount }} {{ $this->storyCount == 1 ? 'story' : 'stories' }} shared
                        @if($theme->status !== 'past')
                            • {{ $theme->start_date->format('M j') }} - {{ $theme->end_date->format('M j, Y') }}
                        @else
                            • {{ $theme->start_date->format('M j') }} - {{ $theme->end_date->format('M j, Y') }}
                        @endif
                    </div>

                    @if($theme->status === 'active')
                        <a href="{{ route('post.create') }}?theme={{ $theme->slug }}" 
                           class="bg-black text-white px-6 py-3 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors text-center">
                            Share Your Story
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white min-h-screen">
        <!-- Filter Bar -->
        @if($this->storyCount > 0)
            <div class="bg-white border-b border-gray-100">
                <div class="max-w-6xl mx-auto px-6 py-4">
                    <div class="flex items-center space-x-1">
                        <button wire:click="setFilter('newest')"
                            class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ $currentFilter === 'newest' ? 'bg-black text-white' : 'text-gray-600 hover:text-black' }}">
                            Newest
                        </button>
                        <button wire:click="setFilter('trending')"
                            class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ $currentFilter === 'trending' ? 'bg-black text-white' : 'text-gray-600 hover:text-black' }}">
                            Trending
                        </button>
                        <button wire:click="setFilter('popular')"
                            class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ $currentFilter === 'popular' ? 'bg-black text-white' : 'text-gray-600 hover:text-black' }}">
                            Popular
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Stories -->
        <div class="max-w-6xl mx-auto px-6 py-8">
            <div class="max-w-4xl">
                @if($this->storyCount > 0)
                    <div class="space-y-12">
                        @foreach ($this->stories as $story)
                            <article class="border-b border-gray-100 pb-12 last:border-b-0">
                                <div class="flex items-center mb-4">
                                    <span class="text-sm font-medium text-gray-700">@ {{ $story->alias }}</span>
                                    <span class="mx-2 text-gray-300">•</span>
                                    <span class="text-sm text-gray-500">{{ $story->created_at->diffForHumans() }}</span>
                                    @if ($story->category)
                                        <span class="mx-2 text-gray-300">•</span>
                                        <span class="text-xs uppercase tracking-wide text-gray-400 bg-gray-100 px-2 py-1 rounded">
                                            {{ $story->category }}
                                        </span>
                                    @endif
                                </div>

                                <a href="{{ route('post', $story->slug) }}" class="block">
                                    <h2 class="text-xl md:text-2xl font-medium mb-3 leading-tight hover:text-gray-700 cursor-pointer transition-colors">
                                        {{ $story->title }}
                                    </h2>
                                </a>

                                <p class="text-base text-gray-600 leading-relaxed mb-3">
                                    {{ Str::limit(strip_tags($story->body), 200) }}
                                </p>

                                <!-- Story meta -->
                                <div class="flex items-center justify-between text-sm text-gray-500">
                                    <div class="flex items-center space-x-4">
                                        <span class="flex items-center gap-1">
                                            <span>↑</span>
                                            <span>{{ $story->upvotes }}</span>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <span>↓</span>
                                            <span>{{ $story->downvotes }}</span>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <span>💬</span>
                                            <span>{{ $story->comments->count() }}</span>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <span>👁</span>
                                            <span>{{ number_format($story->views) }}</span>
                                        </span>
                                    </div>

                                    <a href="{{ route('post', $story->slug) }}" 
                                       class="text-gray-600 hover:text-black transition-colors font-medium">
                                        Read more →
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-12">
                        {{ $this->stories->links() }}
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="text-center py-16">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No stories yet</h3>
                        <p class="text-gray-600 text-sm mb-6">Be the first to share a story for this theme!</p>
                        
                        @if($theme->status === 'active')
                            <a href="{{ route('post.create') }}?theme={{ $theme->slug }}" 
                               class="bg-black text-white px-6 py-3 rounded-lg font-medium hover:bg-gray-800 transition-colors">
                                Share Your Story
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>