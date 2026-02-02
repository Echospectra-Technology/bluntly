<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Story;
use App\Models\Tag;
use App\Services\AnonymousUserService;
use App\Services\ModerationEngine;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

new #[Layout('layouts.app')] class extends Component {
    public $title = '';
    public $category = 'confession';
    public $content = '';
    public $anonymous_handle = '';
    public $tags = [];
    public $tagInput = '';
    public $suggestedTags = [];
    public $isDraft = false;
    public $selectedTheme = null;
    public $availableThemes = [];

    public function mount()
    {
        $anonymousService = app(AnonymousUserService::class);
        $this->anonymous_handle = $anonymousService->generateAlias();

        // Load suggested tags from database
        $this->suggestedTags = Tag::orderBy('name')->pluck('name')->toArray();

        // Load available themes (only current themes)
        $this->availableThemes = \App\Models\WeeklyTheme::current()->get();

        // Check if theme is provided via URL parameter
        $themeSlug = request()->get('theme');
        if ($themeSlug) {
            $theme = \App\Models\WeeklyTheme::where('slug', $themeSlug)->first();
            if ($theme) {
                $this->selectedTheme = $theme->id;
            }
        }
    }

    public function generateNewHandle()
    {
        $anonymousService = app(AnonymousUserService::class);
        $this->anonymous_handle = $anonymousService->generateAlias();
    }

    public function publish()
    {
        $this->validate(
            [
                'title' => 'required|string|max:120|min:10',
                'content' => 'required|string|min:50',
                'category' => 'required|in:confession,rant,gist,story',
            ],
            [
                'title.required' => 'Please provide a title for your story.',
                'title.min' => 'Title must be at least 10 characters.',
                'title.max' => 'Title cannot exceed 120 characters.',
                'content.required' => 'Please write your story content.',
                'content.min' => 'Story content must be at least 50 characters.',
            ],
        );

        try {
            $anonymousService = app(AnonymousUserService::class);
            $anonymousId = $anonymousService->getAnonymousId();

            // Create the story first
            $story = Story::create([
                'title' => $this->title,
                'body' => $this->content,
                'slug' => $this->generateUniqueSlug($this->title),
                'alias' => $this->anonymous_handle,
                'cookie_hash' => $anonymousId,
                'status' => 'published', // Will be updated by moderation
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

            // Show appropriate message based on moderation result
            $message = $this->getModerationMessage($moderationResult);
            
            $this->dispatch('toast-show', [
                'message' => $message,
                'type' => $moderationResult['status'] === 'auto_blocked' ? 'warning' : 'success',
            ]);

            return redirect()->route('post', $story->slug);
        } catch (\Exception $e) {
            $this->dispatch('toast-show', [
                'message' => 'There was an error publishing your story. Please try again.',
                'type' => 'error',
            ]);
        }
    }

    public function saveDraft()
    {
        $this->validate([
            'title' => 'nullable|string|max:120',
            'content' => 'nullable|string',
        ]);

        $this->isDraft = true;
        $this->dispatch('toast-show', [
            'message' => 'Draft saved successfully!',
            'type' => 'success',
        ]);
    }

    private function generateUniqueSlug($title)
    {
        $baseSlug = Str::slug($title);
        
        // Get the next incremental ID based on total published stories count + 1
        $nextId = Story::where('status', 'published')->count() + 1;
        
        // Always append the incremental ID to ensure uniqueness
        $slug = $baseSlug . '-' . $nextId;
        
        // Double-check for uniqueness (in case of race conditions)
        $counter = $nextId;
        while (Story::where('slug', $slug)->exists()) {
            $counter++;
            $slug = $baseSlug . '-' . $counter;
        }

        return $slug;
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

    public function addTagFromInput()
    {
        if (trim($this->tagInput)) {
            $this->addTag($this->tagInput);
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

    public function updated($propertyName)
    {
        // Clear any flash messages when user starts typing
        if (in_array($propertyName, ['title', 'content'])) {
            session()->forget(['success', 'error']);
        }
    }

    private function getModerationMessage(array $moderationResult): string
    {
        switch ($moderationResult['status']) {
            case 'safe':
                return 'Your story has been published successfully!';
            case 'review':
                return 'Your story has been published and is under review. It may be hidden if it violates our guidelines.';
            case 'auto_blocked':
                return 'Your story has been published but is currently hidden pending review due to potential content issues. Please review our community guidelines.';
            default:
                return 'Your story has been published successfully!';
        }
    }
}; ?>

<div>
    <x-navigation current-page="create-post" />

    <div class="bg-white dark:bg-zinc-900 min-h-screen">
        <!-- Header -->
        <div class="max-w-4xl mx-auto px-4 md:px-6 py-4 md:py-6">
            <div
                class="flex flex-col space-y-3 md:space-y-0 md:flex-row md:items-center md:justify-between mb-4 md:mb-6">
                <div>
                    <h1 class="text-lg md:text-xl font-medium text-gray-900 dark:text-white">What's on your mind?</h1>
                    <p class="text-xs text-gray-600 dark:text-zinc-400 mt-1">Your voice matters. Post anonymously and be heard.</p>
                </div>
                <a href="{{ route('feed') }}"
                    class="text-xs text-gray-500 dark:text-zinc-400 hover:text-black dark:hover:text-white transition-colors self-start md:self-auto">
                    ← Back to Stories
                </a>
            </div>

            <!-- Success/Error Messages -->
            @if (session()->has('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <p class="text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <p class="text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <ul class="text-red-800 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form -->
            <form wire:submit="publish" class="space-y-4 md:space-y-5">
                <!-- Anonymous Handle -->
                <div class="bg-gray-50 dark:bg-zinc-800 p-3 rounded-lg">
                    <div class="flex flex-col space-y-2 md:space-y-0 md:flex-row md:items-center md:justify-between">
                        <div>
                            <label class="text-xs font-medium text-gray-700 dark:text-zinc-300">Posting as</label>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs md:text-sm font-medium text-gray-900 dark:text-white">@
                                    {{ $anonymous_handle }}</span>
                                <button type="button" wire:click="generateNewHandle"
                                    class="text-xs text-gray-500 dark:text-zinc-400 hover:text-black dark:hover:text-white transition-colors">
                                    Generate new
                                </button>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-zinc-400">
                            Anonymous • No personal info stored
                        </div>
                    </div>
                </div>

                <!-- Category Selection -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-zinc-300 mb-2">Category</label>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="$set('category', 'confession')"
                            class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors {{ $category === 'confession' ? 'bg-black text-white dark:bg-white dark:text-black' : 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 hover:bg-gray-200 dark:hover:bg-zinc-700' }}">
                            Confession
                        </button>
                        <button type="button" wire:click="$set('category', 'rant')"
                            class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors {{ $category === 'rant' ? 'bg-black text-white dark:bg-white dark:text-black' : 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 hover:bg-gray-200 dark:hover:bg-zinc-700' }}">
                            Rant
                        </button>
                        <button type="button" wire:click="$set('category', 'gist')"
                            class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors {{ $category === 'gist' ? 'bg-black text-white dark:bg-white dark:text-black' : 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 hover:bg-gray-200 dark:hover:bg-zinc-700' }}">
                            Gist
                        </button>
                        <button type="button" wire:click="$set('category', 'story')"
                            class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors {{ $category === 'story' ? 'bg-black text-white dark:bg-white dark:text-black' : 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-zinc-300 hover:bg-gray-200 dark:hover:bg-zinc-700' }}">
                            Story
                        </button>
                    </div>
                </div>

                <!-- Weekly Theme Selection -->
                @if (count($availableThemes) > 0)
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-zinc-300 mb-2">
                            Weekly Theme (Optional)
                        </label>

                        @foreach ($availableThemes as $theme)
                            <div class="mb-3 p-4 border rounded-lg cursor-pointer transition-all duration-200 hover:border-purple-300 dark:hover:border-purple-600 hover:bg-purple-50/50 dark:hover:bg-purple-900/20 {{ $selectedTheme == $theme->id ? 'border-purple-500 bg-purple-50 dark:border-purple-600 dark:bg-purple-900/30' : 'border-gray-200 dark:border-zinc-800' }}"
                                wire:click="$set('selectedTheme', {{ $selectedTheme == $theme->id ? 'null' : $theme->id }})">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $theme->name }}</h3>
                                            @if ($theme->status === 'active')
                                                <span
                                                    class="text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 px-2 py-1 rounded-full">Active</span>
                                            @elseif($theme->status === 'upcoming')
                                                <span
                                                    class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 px-2 py-1 rounded-full">Upcoming</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-zinc-400 mb-1">{{ $theme->description }}</p>
                                        @if ($theme->prompt_text)
                                            <p class="text-xs text-gray-500 dark:text-zinc-400 italic">"{{ $theme->prompt_text }}"</p>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div
                                            class="w-4 h-4 rounded-full border-2 {{ $selectedTheme == $theme->id ? 'border-purple-500 bg-purple-500' : 'border-gray-300 dark:border-zinc-600' }}">
                                            @if ($selectedTheme == $theme->id)
                                                <div class="w-2 h-2 bg-white rounded-full mt-0.5 ml-0.5"></div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-2">
                            Participating in weekly themes helps your story reach more readers interested in the topic.
                        </p>
                    </div>
                @endif

                <!-- Title -->
                <div>
                    <label for="title" class="block text-xs font-medium text-gray-700 dark:text-zinc-300 mb-2">Title</label>
                    <input type="text" id="title" wire:model="title"
                        placeholder="Give your post a compelling title..."
                        class="w-full text-base md:text-lg font-medium border-0 border-b border-gray-200 dark:border-zinc-700 focus:border-black dark:focus:border-white focus:ring-0 focus:outline-none bg-transparent dark:bg-transparent placeholder-gray-400 dark:placeholder-zinc-500 text-black dark:text-white pb-2"
                        maxlength="120">
                    <div
                        class="flex flex-col space-y-1 md:space-y-0 md:flex-row md:justify-between md:items-center mt-2">
                        <span class="text-xs text-gray-500 dark:text-zinc-400">A good title helps others find and connect with your
                            post</span>
                        <span class="text-xs text-gray-500 dark:text-zinc-400">{{ strlen($title) }}/120</span>
                    </div>
                </div>

                <!-- Tags -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-zinc-300 mb-2">Tags (Optional)</label>

                    <!-- Selected Tags -->
                    @if (count($tags) > 0)
                        <div class="flex flex-wrap gap-2 mb-3">
                            @foreach ($tags as $index => $tag)
                                <span
                                    class="inline-flex items-center gap-1 bg-black text-white dark:bg-white dark:text-black px-3 py-1 rounded-full text-xs">
                                    #{{ $tag }}
                                    <button type="button" wire:click="removeTag({{ $index }})"
                                        class="text-gray-300 dark:text-gray-700 hover:text-white dark:hover:text-black transition-colors">
                                        ×
                                    </button>
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <!-- Tag Input -->
                    <div class="relative">
                        <input type="text" wire:model.live="tagInput" wire:keydown.enter="addTagFromInput"
                            wire:keydown.comma="addTagFromInput" placeholder="Add tags to help others find your post..."
                            class="w-full px-3 py-2 border border-gray-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent bg-white dark:bg-zinc-800 text-black dark:text-white placeholder-gray-400 dark:placeholder-zinc-500 text-sm"
                            maxlength="20" @if (count($tags) >= 5) disabled @endif>

                        <!-- Suggestions Dropdown -->
                        @if (trim($tagInput) !== '')
                            <div
                                class="absolute z-10 w-full mt-1 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                @if (trim($tagInput) && !in_array(trim($tagInput), $this->filteredSuggestions))
                                    <button type="button" wire:click="addTag('{{ trim($tagInput) }}')"
                                        class="w-full text-left px-3 py-2 hover:bg-gray-50 dark:hover:bg-zinc-700 border-b border-gray-100 dark:border-zinc-700 text-sm">
                                        <span class="text-gray-500 dark:text-zinc-400">Create:</span>
                                        <strong class="text-black dark:text-white">#{{ trim($tagInput) }}</strong>
                                    </button>
                                @endif

                                @if (count($this->filteredSuggestions) > 0)
                                    @foreach ($this->filteredSuggestions as $suggestion)
                                        <button type="button" wire:click="addTag('{{ $suggestion }}')"
                                            class="w-full text-left px-3 py-2 hover:bg-gray-50 dark:hover:bg-zinc-700 text-sm text-black dark:text-white">
                                            #{{ $suggestion }}
                                        </button>
                                    @endforeach
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-between items-center mt-2">
                        <span class="text-xs text-gray-500 dark:text-zinc-400">Press Enter or comma to add tags. Max 5 tags.</span>
                        <span class="text-xs text-gray-500 dark:text-zinc-400">{{ count($tags) }}/5</span>
                    </div>
                </div>

                <!-- Content Editor -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-zinc-300 mb-2">Your Post</label>
                    <div wire:ignore class="editor-wrapper">
                        <!-- Custom Toolbar -->
                        <div id="toolbar-container" class="editor-toolbar">
                            <!-- Text Formatting -->
                            <div class="toolbar-group">
                                <select class="ql-header" title="Text Style">
                                    <option value="1">Heading 1</option>
                                    <option value="2">Heading 2</option>
                                    <option value="3">Heading 3</option>
                                    <option selected="">Normal</option>
                                </select>
                            </div>

                            <!-- Basic Formatting -->
                            <div class="toolbar-group">
                                <button class="ql-bold" title="Bold"></button>
                                <button class="ql-italic" title="Italic"></button>
                                <button class="ql-underline" title="Underline"></button>
                            </div>

                            <!-- Lists -->
                            <div class="toolbar-group">
                                <button class="ql-list" value="ordered" title="Numbered List"></button>
                                <button class="ql-list" value="bullet" title="Bullet List"></button>
                            </div>

                            <!-- Quote & Alignment -->
                            <div class="toolbar-group">
                                <button class="ql-blockquote" title="Quote"></button>
                                <button class="ql-align" value="" title="Left Align"></button>
                                <button class="ql-align" value="center" title="Center Align"></button>
                                <button class="ql-align" value="right" title="Right Align"></button>
                            </div>

                            <!-- Utilities -->
                            <div class="toolbar-group">
                                <button class="ql-clean" title="Remove Formatting"></button>
                            </div>
                        </div>

                        <!-- Editor Container -->
                        <div id="editor-container" class="editor-content"></div>
                    </div>

                    <div
                        class="flex flex-col space-y-2 md:space-y-0 md:flex-row md:justify-between md:items-center mt-3">
                        <div class="flex items-center space-x-4">
                            <span class="text-xs text-gray-500 dark:text-zinc-400">Be authentic. Share what's really on your mind.</span>
                            <button type="button" onclick="toggleFullscreen()"
                                class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
                                📝 Focus Mode
                            </button>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="text-xs text-gray-500 dark:text-zinc-400" id="char-count">0 characters</span>
                            <span class="text-xs text-gray-500 dark:text-zinc-400" id="word-count">0 words</span>
                        </div>
                    </div>
                </div>

                <!-- Guidelines -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                    <h3 class="text-xs font-medium text-blue-900 dark:text-blue-300 mb-2">Before you post</h3>
                    <ul class="text-xs text-blue-800 dark:text-blue-400 space-y-1">
                        <li>• Keep it authentic and respectful</li>
                        <li>• No personal attacks or harassment</li>
                        <li>• Avoid sharing identifying information</li>
                        <li>• Your post will be editable/deletable for 7 days</li>
                    </ul>
                </div>

                <!-- Actions -->
                <div
                    class="flex flex-col space-y-3 md:space-y-0 md:flex-row md:items-center md:justify-between pt-4 border-t border-gray-100 dark:border-zinc-800">
                    <button type="button" wire:click="saveDraft" wire:loading.attr="disabled"
                        wire:target="saveDraft"
                        class="text-xs text-gray-600 dark:text-zinc-400 hover:text-black dark:hover:text-white transition-colors self-start md:self-auto disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveDraft">Save Draft</span>
                        <span wire:loading wire:target="saveDraft">Saving...</span>
                    </button>
                    <div class="flex flex-col space-y-2 md:space-y-0 md:flex-row md:items-center md:gap-3">
                        <a href="{{ route('feed') }}"
                            class="px-4 py-1.5 text-xs font-medium text-gray-700 dark:text-zinc-300 hover:text-black dark:hover:text-white transition-colors text-center border border-gray-200 dark:border-zinc-700 rounded-lg md:border-0">
                            Cancel
                        </a>
                        <button type="submit" wire:loading.attr="disabled" wire:target="publish"
                            class="bg-black text-white dark:bg-white dark:text-black px-4 py-2 md:py-1.5 rounded-lg text-xs font-medium hover:bg-gray-800 dark:hover:bg-zinc-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="publish">Publish Post</span>
                            <span wire:loading wire:target="publish" class="flex items-center gap-2">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white dark:text-black"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Publishing...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Quill.js CSS and JS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

    <!-- Custom Editor Styles -->
    <style>
        .editor-wrapper {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.2s ease;
            background: white;
        }

        .editor-wrapper:hover {
            border-color: #d1d5db;
        }

        .editor-wrapper:focus-within {
            border-color: #000;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
        }

        .editor-toolbar {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .toolbar-group {
            display: flex;
            align-items: center;
            gap: 4px;
            padding-right: 8px;
            border-right: 1px solid #e5e7eb;
        }

        .toolbar-group:last-child {
            border-right: none;
        }

        .editor-toolbar button {
            padding: 6px 8px;
            border: 1px solid transparent;
            border-radius: 6px;
            background: transparent;
            color: #374151;
            cursor: pointer;
            transition: all 0.15s ease;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
        }

        .editor-toolbar button:hover {
            background: #e5e7eb;
            color: #111827;
        }

        .editor-toolbar button.ql-active {
            background: #000;
            color: white;
            border-color: #000;
        }

        .editor-toolbar select {
            padding: 4px 8px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: white;
            color: #374151;
            font-size: 13px;
            cursor: pointer;
            min-width: 120px;
        }

        .editor-toolbar select:focus {
            outline: none;
            border-color: #000;
        }

        .editor-content {
            min-height: 350px;
            font-size: 16px;
            line-height: 1.6;
        }

        .editor-content .ql-editor {
            padding: 24px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #111827;
            min-height: 320px;
            background-color: #ffffff;
        }

        /* Dark mode for Quill editor */
        .dark .editor-content .ql-editor {
            background-color: #18181b;
            color: #fafafa;
        }

        .editor-content .ql-editor.ql-blank::before {
            color: #9ca3af;
            font-style: normal;
            left: 24px;
            font-size: 16px;
        }

        .dark .editor-content .ql-editor.ql-blank::before {
            color: #71717a;
        }

        .editor-content .ql-editor h1 {
            font-size: 1.875rem;
            font-weight: 700;
            line-height: 1.2;
            margin: 1rem 0;
        }

        .editor-content .ql-editor h2 {
            font-size: 1.5rem;
            font-weight: 600;
            line-height: 1.3;
            margin: 0.875rem 0;
        }

        .editor-content .ql-editor h3 {
            font-size: 1.25rem;
            font-weight: 600;
            line-height: 1.4;
            margin: 0.75rem 0;
        }

        .editor-content .ql-editor p {
            margin: 0.5rem 0;
        }

        .editor-content .ql-editor blockquote {
            border-left: 4px solid #000;
            margin: 1rem 0;
            padding-left: 1rem;
            font-style: italic;
            color: #4b5563;
        }

        .dark .editor-content .ql-editor blockquote {
            border-left-color: #71717a;
            color: #a1a1aa;
        }

        .editor-content .ql-editor ul,
        .editor-content .ql-editor ol {
            margin: 0.5rem 0;
            padding-left: 1.5rem;
        }

        .editor-content .ql-editor li {
            margin: 0.25rem 0;
        }

        /* Focus Mode Styles */
        .editor-fullscreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 9999;
            background: white;
            border-radius: 0 !important;
            border: none !important;
        }

        .dark .editor-fullscreen {
            background: #18181b;
        }

        .editor-fullscreen .editor-content {
            min-height: calc(100vh - 80px);
        }

        .editor-fullscreen .editor-content .ql-editor {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            min-height: calc(100vh - 160px);
        }

        .editor-fullscreen .editor-toolbar {
            padding: 16px 40px;
            justify-content: center;
            background: white;
            border-bottom: 2px solid #f3f4f6;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .editor-toolbar {
                padding: 8px 12px;
                gap: 4px;
            }

            .toolbar-group {
                gap: 2px;
                padding-right: 6px;
            }

            .editor-toolbar button {
                min-width: 28px;
                height: 28px;
                padding: 4px 6px;
                font-size: 13px;
            }

            .editor-toolbar select {
                min-width: 100px;
                font-size: 12px;
            }

            .editor-content .ql-editor {
                padding: 16px;
                font-size: 15px;
                min-height: 280px;
            }

            .editor-fullscreen .editor-content .ql-editor {
                padding: 20px;
            }

            .editor-fullscreen .editor-toolbar {
                padding: 12px 20px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeQuillEditor();
        });

        // Listen for Livewire updates
        document.addEventListener('livewire:navigated', function() {
            initializeQuillEditor();
        });

        function initializeQuillEditor() {
            // Check if editor already exists
            if (window.quillEditor) {
                return;
            }

            // Wait for the container to be available
            const container = document.getElementById('editor-container');
            if (!container) {
                setTimeout(initializeQuillEditor, 100);
                return;
            }

            var quill = new Quill('#editor-container', {
                theme: 'snow',
                placeholder: 'Start writing your post... Take your time, and let your thoughts flow naturally.',
                modules: {
                    toolbar: '#toolbar-container'
                }
            });

            // Store reference to prevent re-initialization
            window.quillEditor = quill;

            // Update content when editor changes
            quill.on('text-change', function() {
                const content = quill.root.innerHTML;

                // Use Alpine.js or direct Livewire call to avoid re-rendering
                if (window.Livewire) {
                    window.Livewire.find(@this.id).set('content', content, false);
                }

                updateCounts();
            });

            // Set initial content if exists
            @if ($content)
                quill.root.innerHTML = @json($content);
                updateCounts();
            @endif

            // Add keyboard shortcuts
            quill.keyboard.addBinding({
                key: 'S',
                ctrlKey: true,
                metaKey: true,
                preventDefault: true,
                handler: function() {
                    // Trigger save draft
                    @this.call('saveDraft');
                    showNotification('Draft saved!', 'success');
                }
            });

            // Focus the editor
            setTimeout(() => {
                quill.focus();
            }, 100);
        }

        function updateCounts() {
            const quill = window.quillEditor;
            if (!quill) return;

            const text = quill.getText();
            const plainText = text.trim();

            // Word count
            const wordCount = plainText.length > 0 ? plainText.split(/\s+/).filter(word => word.length > 0).length : 0;
            const wordCountElement = document.getElementById('word-count');
            if (wordCountElement) {
                wordCountElement.textContent = wordCount + ' words';
            }

            // Character count
            const charCount = plainText.length;
            const charCountElement = document.getElementById('char-count');
            if (charCountElement) {
                charCountElement.textContent = charCount + ' characters';
            }
        }

        function toggleFullscreen() {
            const wrapper = document.querySelector('.editor-wrapper');
            const button = event.target;

            if (wrapper.classList.contains('editor-fullscreen')) {
                // Exit fullscreen
                wrapper.classList.remove('editor-fullscreen');
                button.textContent = '📝 Focus Mode';
                document.body.style.overflow = '';
            } else {
                // Enter fullscreen
                wrapper.classList.add('editor-fullscreen');
                button.textContent = '🔙 Exit Focus';
                document.body.style.overflow = 'hidden';

                // Focus the editor
                if (window.quillEditor) {
                    setTimeout(() => {
                        window.quillEditor.focus();
                    }, 100);
                }
            }
        }

        function showNotification(message, type = 'info') {
            // Simple notification system
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-4 py-2 rounded-lg text-white text-sm z-50 transition-all duration-300 ${
                type === 'success' ? 'bg-green-500' : 'bg-blue-500'
            }`;
            notification.textContent = message;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 2000);
        }

        // Handle ESC key to exit fullscreen
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const wrapper = document.querySelector('.editor-wrapper');
                if (wrapper && wrapper.classList.contains('editor-fullscreen')) {
                    toggleFullscreen();
                }
            }
        });

        // Handle tag input events
        document.addEventListener('DOMContentLoaded', function() {
            const tagInput = document.querySelector('input[wire\\:model\\.live="tagInput"]');
            if (tagInput) {
                tagInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ',') {
                        e.preventDefault();

                        // Trigger Livewire method
                        @this.call('addTagFromInput');
                    }
                });
            }
        });
    </script>
</div>
