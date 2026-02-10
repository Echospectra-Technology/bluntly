<?php

use Livewire\Volt\Component;
use App\Models\Story;
use App\Models\Tag;
use App\Services\AnonymousUserService;
use App\Services\ModerationEngine;
use Illuminate\Support\Str;

new class extends Component {
    public $isOpen = false;
    public $title = '';
    public $category = 'confession';
    public $content = '';
    public $anonymous_handle = '';
    public $tags = [];
    public $tagInput = '';
    public $suggestedTags = [];
    public $selectedTheme = null;
    public $availableThemes = [];

    public function mount()
    {
        $anonymousService = app(AnonymousUserService::class);
        $this->anonymous_handle = $anonymousService->generateAlias();

        // Load suggested tags
        $this->suggestedTags = Tag::orderBy('name')->limit(10)->pluck('name')->toArray();

        // Load available themes
        $this->availableThemes = \App\Models\WeeklyTheme::current()->get();
    }

    public function openModal()
    {
        $this->isOpen = true;
        $this->dispatch('modal-opened');
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['title', 'content', 'tags', 'tagInput', 'category', 'selectedTheme']);
        $this->dispatch('close-quick-post');
    }

    public function generateNewHandle()
    {
        $anonymousService = app(AnonymousUserService::class);
        $this->anonymous_handle = $anonymousService->generateAlias();
    }

    public function setCategory($value)
    {
        $this->category = $value;
    }

    public function setTheme($themeId)
    {
        $this->selectedTheme = $this->selectedTheme == $themeId ? null : $themeId;
    }

    public function addTag($tag)
    {
        $tag = trim($tag);
        if ($tag && !in_array($tag, $this->tags) && count($this->tags) < 5) {
            $this->tags[] = $tag;
            $this->tagInput = '';
        }
    }

    public function removeTag($index)
    {
        if (isset($this->tags[$index])) {
            unset($this->tags[$index]);
            $this->tags = array_values($this->tags);
        }
    }

    public function publish()
    {
        $this->validate(
            [
                'title' => 'required|string|max:120|min:3',
                'content' => 'required|string|min:10',
                'category' => 'required|in:confession,rant,gist,story',
            ],
            [
                'title.required' => 'Please provide a title for your story.',
                'title.min' => 'Title must be at least 3 characters.',
                'title.max' => 'Title cannot exceed 120 characters.',
                'content.required' => 'Please write your story content.',
                'content.min' => 'Story content must be at least 10 characters.',
            ],
        );

        try {
            $anonymousService = app(AnonymousUserService::class);
            $anonymousId = $anonymousService->getAnonymousId();

            $story = Story::create([
                'title' => $this->title,
                'body' => $this->content,
                'slug' => $this->generateUniqueSlug($this->title),
                'alias' => $this->anonymous_handle,
                'cookie_hash' => $anonymousId,
                'status' => 'published',
                'category' => $this->category,
                'theme_id' => $this->selectedTheme,
                'upvotes' => 0,
                'downvotes' => 0,
                'views' => 0,
            ]);

            // Run through moderation engine
            $moderationEngine = app(ModerationEngine::class);
            $moderationResult = $moderationEngine->moderateStory($story);

            // Handle tags
            if (!empty($this->tags)) {
                $tagIds = [];
                foreach ($this->tags as $tagName) {
                    $tag = Tag::firstOrCreate([
                        'name' => $tagName,
                        'slug' => Str::slug($tagName),
                    ]);
                    $tagIds[] = $tag->id;
                }
                $story->tags()->attach($tagIds);
            }

            $message = $this->getModerationMessage($moderationResult);

            $this->dispatch('toast-show', [
                'message' => $message,
                'type' => $moderationResult['status'] === 'auto_blocked' ? 'warning' : 'success',
            ]);

            $this->closeModal();

            // Redirect to the new post
            return redirect()->route('post', $story->slug);
        } catch (\Exception $e) {
            $this->dispatch('toast-show', [
                'message' => 'There was an error publishing your story. Please try again.',
                'type' => 'error',
            ]);
        }
    }

    private function generateUniqueSlug($title)
    {
        $baseSlug = Str::slug($title);
        $nextId = Story::where('status', 'published')->count() + 1;
        $slug = $baseSlug . '-' . $nextId;

        $counter = $nextId;
        while (Story::where('slug', $slug)->exists()) {
            $counter++;
            $slug = $baseSlug . '-' . $counter;
        }

        return $slug;
    }

    private function getModerationMessage(array $moderationResult): string
    {
        switch ($moderationResult['status']) {
            case 'safe':
                return 'Your story has been published successfully!';
            case 'review':
                return 'Your story has been published and is under review.';
            case 'auto_blocked':
                return 'Your story is currently hidden pending review due to potential content issues.';
            default:
                return 'Your story has been published successfully!';
        }
    }

    public function getFilteredSuggestionsProperty()
    {
        if (empty(trim($this->tagInput))) {
            return [];
        }

        return array_filter($this->suggestedTags, function ($tag) {
            return stripos($tag, $this->tagInput) !== false && !in_array($tag, $this->tags);
        });
    }
}; ?>

<div x-data="{ show: false }"
     @keydown.escape.window="if (show) { show = false; $wire.closeModal(); }"
     @open-quick-post.window="show = true; $wire.openModal()"
     @close-quick-post.window="show = false"
     x-init="
         $watch('show', value => {
             if (!value) {
                 document.body.style.overflow = '';
             } else {
                 document.body.style.overflow = 'hidden';
             }
         });
         Livewire.on('modal-opened', () => { show = true; });
     ">
    <!-- Modal Backdrop -->
    <div x-show="show"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-hidden"
         style="display: none;">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm modal-backdrop" @click="show = false; $wire.closeModal()"></div>

        <!-- Modal Container -->
        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center md:items-center md:p-4">
                <!-- Modal Content -->
                <div x-show="show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-full md:translate-y-0 md:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 md:scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 md:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-full md:translate-y-0 md:scale-95"
                     class="relative w-full md:max-w-2xl bg-white md:rounded-2xl shadow-2xl modal-content-mobile md:modal-content-desktop">

                    <!-- Header -->
                    <div class="sticky top-0 bg-white border-b border-gray-200 px-4 md:px-6 py-4 md:rounded-t-2xl">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-medium text-gray-900">Quick Post</h2>
                            <button @click="show = false; $wire.closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Form Content -->
                    <div class="px-4 md:px-6 py-4 space-y-4 max-h-[calc(100vh-140px)] md:max-h-[70vh] overflow-y-auto">

                        <!-- Anonymous Handle -->
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Posting as</p>
                                <p class="text-sm font-medium text-gray-900">{{ '@' . $anonymous_handle }}</p>
                            </div>
                            <button type="button"
                                    wire:click="generateNewHandle"
                                    wire:loading.attr="disabled"
                                    wire:target="generateNewHandle"
                                    class="text-xs text-gray-600 hover:text-black transition-colors flex items-center gap-1 disabled:opacity-50">
                                <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <span wire:loading.remove wire:target="generateNewHandle">New</span>
                                <span wire:loading wire:target="generateNewHandle">...</span>
                            </button>
                        </div>

                        <!-- Category Selection -->
                        <div class="relative z-10">
                            <label class="block text-xs font-medium text-gray-700 mb-2">Category</label>
                            <div class="flex flex-wrap gap-2 relative z-10">
                                <button type="button"
                                    wire:click="setCategory('confession')"
                                    class="px-4 py-2 rounded-full text-sm font-medium transition-colors cursor-pointer relative z-10 {{ $category === 'confession' ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                    Confession
                                </button>
                                <button type="button"
                                    wire:click="setCategory('rant')"
                                    class="px-4 py-2 rounded-full text-sm font-medium transition-colors cursor-pointer relative z-10 {{ $category === 'rant' ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                    Rant
                                </button>
                                <button type="button"
                                    wire:click="setCategory('gist')"
                                    class="px-4 py-2 rounded-full text-sm font-medium transition-colors cursor-pointer relative z-10 {{ $category === 'gist' ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                    Gist
                                </button>
                                <button type="button"
                                    wire:click="setCategory('story')"
                                    class="px-4 py-2 rounded-full text-sm font-medium transition-colors cursor-pointer relative z-10 {{ $category === 'story' ? 'bg-black text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                    Story
                                </button>
                            </div>
                        </div>

                        <!-- Weekly Theme (if available) -->
                        @if(count($availableThemes) > 0)
                            <div x-data="{ expanded: false }">
                                <button type="button" @click="expanded = !expanded" class="w-full flex items-center justify-between text-xs font-medium text-gray-700 mb-2">
                                    <span>Weekly Theme (Optional)</span>
                                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div x-show="expanded"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                                     x-transition:enter-end="opacity-100 transform translate-y-0"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 transform translate-y-0"
                                     x-transition:leave-end="opacity-0 transform -translate-y-2"
                                     style="display: none;">
                                    @foreach($availableThemes as $theme)
                                        <div class="mb-2 p-3 border rounded-lg cursor-pointer transition-all {{ $selectedTheme == $theme->id ? 'border-black bg-gray-50' : 'border-gray-200 hover:border-gray-300' }}"
                                             wire:click="setTheme({{ $theme->id }})">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <h3 class="text-sm font-medium text-gray-900">{{ $theme->name }}</h3>
                                                    <p class="text-xs text-gray-600 mt-0.5">{{ Str::limit($theme->description, 60) }}</p>
                                                </div>
                                                <div class="w-4 h-4 rounded-full border-2 {{ $selectedTheme == $theme->id ? 'border-black bg-black' : 'border-gray-300' }}">
                                                    @if($selectedTheme == $theme->id)
                                                        <div class="w-2 h-2 bg-white rounded-full mt-0.5 ml-0.5"></div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Title -->
                        <div>
                            <label for="quick-title" class="block text-xs font-medium text-gray-700 mb-2">Title</label>
                            <input type="text" id="quick-title" wire:model.live="title"
                                placeholder="Give your post a title..."
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent text-sm placeholder-gray-400"
                                maxlength="120">
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-xs text-gray-500">Min 10 characters</span>
                                <span class="text-xs {{ strlen($title) > 120 ? 'text-red-600' : 'text-gray-500' }}">{{ strlen($title) }}/120</span>
                            </div>
                            @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Content -->
                        <div>
                            <label for="quick-content" class="block text-xs font-medium text-gray-700 mb-2">Your Story</label>
                            <textarea id="quick-content" wire:model.live="content" rows="8"
                                placeholder="Write your story here... Be authentic and share what's on your mind."
                                class="w-full px-3 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent text-sm placeholder-gray-400 resize-none"></textarea>
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-xs text-gray-500">Min 50 characters</span>
                                <span class="text-xs text-gray-500">{{ strlen(strip_tags($content)) }} characters</span>
                            </div>
                            @error('content') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Tags (Collapsible) -->
                        <div x-data="{ tagsExpanded: false }">
                            <button type="button" @click="tagsExpanded = !tagsExpanded" class="w-full flex items-center justify-between text-xs font-medium text-gray-700 mb-2">
                                <span>Tags (Optional)</span>
                                <div class="flex items-center gap-2">
                                    @if(count($tags) > 0)
                                        <span class="bg-black text-white px-2 py-0.5 rounded-full text-xs">{{ count($tags) }}</span>
                                    @endif
                                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': tagsExpanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </button>
                            <div x-show="tagsExpanded"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                                 x-transition:enter-end="opacity-100 transform translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 transform translate-y-0"
                                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                                 style="display: none;">
                                @if(count($tags) > 0)
                                    <div class="flex flex-wrap gap-2 mb-2">
                                        @foreach($tags as $index => $tag)
                                            <span class="inline-flex items-center gap-1 bg-black text-white px-3 py-1 rounded-full text-xs">
                                                #{{ $tag }}
                                                <button type="button" wire:click="removeTag({{ $index }})" class="text-gray-300 hover:text-white">×</button>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="relative">
                                    <input type="text" wire:model.live="tagInput"
                                        placeholder="Type to add tags..."
                                        class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-black text-sm"
                                        @if(count($tags) >= 5) disabled @endif
                                        @keydown.enter.prevent="$wire.addTag(tagInput)"
                                        @keydown.comma.prevent="$wire.addTag(tagInput)">

                                    @if(trim($tagInput) !== '')
                                        <div class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-40 overflow-y-auto">
                                            @if(count($this->filteredSuggestions) > 0)
                                                @foreach($this->filteredSuggestions as $suggestion)
                                                    <button type="button" wire:click="addTag('{{ $suggestion }}')"
                                                        class="w-full text-left px-3 py-2 hover:bg-gray-50 text-sm">
                                                        #{{ $suggestion }}
                                                    </button>
                                                @endforeach
                                            @else
                                                <button type="button" wire:click="addTag('{{ trim($tagInput) }}')"
                                                    class="w-full text-left px-3 py-2 hover:bg-gray-50 text-sm">
                                                    Create: <strong>#{{ trim($tagInput) }}</strong>
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Press Enter or comma to add. Max 5 tags ({{ count($tags) }}/5)</p>
                            </div>
                        </div>

                        <!-- Quick Guidelines -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <p class="text-xs text-blue-800">
                                Keep it authentic and respectful. No personal attacks or identifying information.
                                <a href="/rules" target="_blank" class="underline hover:text-blue-900">View guidelines →</a>
                            </p>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="sticky bottom-0 bg-white border-t border-gray-200 px-4 md:px-6 py-4 md:rounded-b-2xl">
                        <div class="flex items-center justify-end gap-3">
                            <button type="button" @click="show = false; $wire.closeModal()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-black transition-colors">
                                Cancel
                            </button>
                            <button wire:click="publish" wire:loading.attr="disabled" wire:target="publish"
                                class="bg-black text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                <span wire:loading.remove wire:target="publish">Publish</span>
                                <span wire:loading wire:target="publish" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Publishing...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
