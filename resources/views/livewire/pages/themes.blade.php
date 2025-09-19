<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\WeeklyTheme;

new #[Layout('layouts.app')] class extends Component {
    public $currentThemes = [];
    public $upcomingThemes = [];
    public $pastThemes = [];

    public function mount()
    {
        $this->currentThemes = WeeklyTheme::current()->get();
        $this->upcomingThemes = WeeklyTheme::upcoming()->orderBy('start_date')->get();
        $this->pastThemes = WeeklyTheme::past()
            ->withCount('stories')
            ->orderBy('end_date', 'desc')
            ->take(10)
            ->get();
    }
}; ?>

<div>
    <x-navigation current-page="themes" />

    <!-- Header Section -->
    <div class="bg-gray-50 border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-6 py-8">
            <div class="max-w-4xl">
                <h1 class="text-xl md:text-2xl font-light mb-3">Weekly Themes</h1>
                <p class="text-sm text-gray-600 font-light">Join our community in exploring different topics each week. Share your experiences and connect with others through themed storytelling.</p>
            </div>
        </div>
    </div>

    <div class="bg-white min-h-screen">
        <div class="max-w-6xl mx-auto px-6 py-8">

            <!-- Current Theme -->
            @if(count($currentThemes) > 0)
                <section class="mb-16">
                    <h2 class="text-lg font-medium text-gray-900 mb-6">Currently Active</h2>
                    
                    @foreach($currentThemes as $theme)
                        <div class="border border-gray-200 rounded-lg p-6 md:p-8 mb-8">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="text-sm font-medium text-gray-500">This Week's Theme</span>
                                @if($theme->days_remaining > 0)
                                    <span class="text-gray-400">• {{ $theme->days_remaining }} day{{ $theme->days_remaining == 1 ? '' : 's' }} left</span>
                                @endif
                            </div>
                            
                            <h3 class="text-xl md:text-2xl font-medium text-gray-900 mb-4">{{ $theme->name }}</h3>
                            <p class="text-gray-600 mb-6 leading-relaxed">{{ $theme->description }}</p>
                            
                            @if($theme->prompt_text)
                                <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-100">
                                    <p class="text-gray-600 italic">"{{ $theme->prompt_text }}"</p>
                                </div>
                            @endif
                            
                            <div class="flex flex-col sm:flex-row gap-4">
                                <a href="{{ route('post.create') }}?theme={{ $theme->slug }}" 
                                   class="bg-black text-white px-6 py-3 rounded-lg font-medium hover:bg-gray-800 transition-colors text-center">
                                    Share Your Story
                                </a>
                                
                                @if($theme->stories()->published()->count() > 0)
                                    <a href="{{ route('feed') }}?theme={{ $theme->slug }}" 
                                       class="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-50 transition-colors text-center">
                                        Read {{ $theme->stories()->published()->count() }} {{ $theme->stories()->published()->count() == 1 ? 'Story' : 'Stories' }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </section>
            @endif

            <!-- Upcoming Themes -->
            @if(count($upcomingThemes) > 0)
                <section class="mb-16">
                    <h2 class="text-lg font-medium text-gray-900 mb-6">Coming Up</h2>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        @foreach($upcomingThemes as $theme)
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="text-sm font-medium text-gray-500">{{ $theme->start_date->format('M j') }} - {{ $theme->end_date->format('M j') }}</span>
                                </div>
                                
                                <h3 class="text-lg font-medium text-gray-900 mb-3">{{ $theme->name }}</h3>
                                <p class="text-gray-600 mb-4 text-sm leading-relaxed">{{ $theme->description }}</p>
                                
                                @if($theme->prompt_text)
                                    <p class="text-xs text-gray-500 italic">"{{ \Str::limit($theme->prompt_text, 100) }}"</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Past Themes -->
            @if(count($pastThemes) > 0)
                <section>
                    <h2 class="text-lg font-medium text-gray-900 mb-6">Past Themes</h2>
                    
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($pastThemes as $theme)
                            <div class="bg-white border border-gray-200 rounded-lg p-6 hover:border-gray-300 transition-colors">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="text-xs text-gray-500">{{ $theme->start_date->format('M j') }} - {{ $theme->end_date->format('M j, Y') }}</span>
                                </div>
                                
                                <h3 class="text-lg font-medium text-gray-900 mb-3">{{ $theme->name }}</h3>
                                <p class="text-gray-600 text-sm mb-4 leading-relaxed">{{ \Str::limit($theme->description, 80) }}</p>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">
                                        {{ $theme->stories_count }} {{ $theme->stories_count == 1 ? 'story' : 'stories' }}
                                    </span>
                                    
                                    @if($theme->stories_count > 0)
                                        <a href="{{ route('feed') }}?theme={{ $theme->slug }}" 
                                           class="text-black text-sm font-medium hover:text-gray-600 transition-colors">
                                            Read Stories →
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Empty State -->
            @if(count($currentThemes) === 0 && count($upcomingThemes) === 0 && count($pastThemes) === 0)
                <div class="text-center py-16">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No themes available yet</h3>
                    <p class="text-gray-600 text-sm">Weekly themes will appear here when they're scheduled.</p>
                </div>
            @endif
        </div>
    </div>
</div>