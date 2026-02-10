<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Story;
use App\Models\Comment;
use App\Services\AnonymousUserService;

new #[Layout('components.layouts.app')] class extends Component {
    public $slug;
    public $story;
    public $newComment = '';
    public $replyingTo = null;
    public $newReply = '';
    public $showShareModal = false;

    public function mount($slug)
    {
        $this->slug = $slug;
        $this->story = Story::with(['tags', 'comments.replies', 'theme'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Record view
        $anonymousService = app(AnonymousUserService::class);
        $anonymousService->recordStoryView($this->story->id);
    }

    public function getCommentsProperty()
    {
        return Comment::with(['replies'])
            ->where('story_id', $this->story->id)
            ->whereNull('parent_id')
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function toggleVote($voteType)
    {
        $anonymousService = app(AnonymousUserService::class);
        $anonymousId = $anonymousService->getAnonymousId();

        $existingVote = \App\Models\Vote::where('item_type', 'story')->where('item_id', $this->story->id)->where('cookie_hash', $anonymousId)->first();

        if ($existingVote) {
            if ($existingVote->value === $voteType) {
                // Remove vote if clicking same button
                $existingVote->delete();
            } else {
                // Change vote
                $existingVote->update(['value' => $voteType]);
            }
        } else {
            // Create new vote
            \App\Models\Vote::create([
                'item_type' => 'story',
                'item_id' => $this->story->id,
                'value' => $voteType,
                'cookie_hash' => $anonymousId,
                'created_at' => now(),
            ]);
        }

        $this->updateStoryVoteCounts();
    }

    public function toggleCommentVote($commentId, $voteType)
    {
        $anonymousService = app(AnonymousUserService::class);
        $anonymousId = $anonymousService->getAnonymousId();

        $existingVote = \App\Models\Vote::where('item_type', 'comment')->where('item_id', $commentId)->where('cookie_hash', $anonymousId)->first();

        if ($existingVote) {
            if ($existingVote->value === $voteType) {
                $existingVote->delete();
            } else {
                $existingVote->update(['value' => $voteType]);
            }
        } else {
            \App\Models\Vote::create([
                'item_type' => 'comment',
                'item_id' => $commentId,
                'value' => $voteType,
                'cookie_hash' => $anonymousId,
                'created_at' => now(),
            ]);
        }

        $this->updateCommentVoteCounts($commentId);
    }

    private function updateStoryVoteCounts()
    {
        $upvotes = \App\Models\Vote::where('item_type', 'story')->where('item_id', $this->story->id)->where('value', 'up')->count();

        $downvotes = \App\Models\Vote::where('item_type', 'story')->where('item_id', $this->story->id)->where('value', 'down')->count();

        $this->story->update([
            'upvotes' => $upvotes,
            'downvotes' => $downvotes,
        ]);

        $this->story->refresh();
    }

    private function updateCommentVoteCounts($commentId)
    {
        $upvotes = \App\Models\Vote::where('item_type', 'comment')->where('item_id', $commentId)->where('value', 'up')->count();

        $downvotes = \App\Models\Vote::where('item_type', 'comment')->where('item_id', $commentId)->where('value', 'down')->count();

        Comment::where('id', $commentId)->update([
            'upvotes' => $upvotes,
            'downvotes' => $downvotes,
        ]);
    }

    public function getUserVote($itemType, $itemId)
    {
        $anonymousService = app(AnonymousUserService::class);
        return $anonymousService->hasVotedOn($itemType, $itemId);
    }

    public function addComment()
    {
        try {
            $this->validate(
                [
                    'newComment' => 'required|string|min:2|max:2000',
                ],
                [
                    'newComment.required' => 'Please write a comment.',
                    'newComment.min' => 'Comment must be at least 2 characters.',
                    'newComment.max' => 'Comment cannot exceed 2000 characters.',
                ],
            );

            $anonymousService = app(AnonymousUserService::class);
            $anonymousId = $anonymousService->getAnonymousId();

            // Convert line breaks to paragraphs for better formatting
            $formattedComment = $this->formatTextToParagraphs($this->newComment);

            Comment::create([
                'story_id' => $this->story->id,
                'parent_id' => null,
                'body' => $formattedComment,
                'alias' => $anonymousService->generateAlias(),
                'cookie_hash' => $anonymousId,
                'status' => 'published',
                'upvotes' => 0,
                'downvotes' => 0,
            ]);

            $this->newComment = '';
            $this->dispatch('toast-show', [
                'message' => 'Comment added successfully!',
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('toast-show', [
                'message' => 'Failed to add comment. Please try again.',
                'type' => 'error',
            ]);
        }
    }

    public function startReply($commentId)
    {
        $this->replyingTo = $commentId;
        $this->newReply = '';
    }

    public function cancelReply()
    {
        $this->replyingTo = null;
        $this->newReply = '';
    }

    public function addReply($commentId)
    {
        try {
            $this->validate(
                [
                    'newReply' => 'required|string|min:2|max:1000',
                ],
                [
                    'newReply.required' => 'Please write a reply.',
                    'newReply.min' => 'Reply must be at least 2 characters.',
                    'newReply.max' => 'Reply cannot exceed 1000 characters.',
                ],
            );

            $anonymousService = app(AnonymousUserService::class);
            $anonymousId = $anonymousService->getAnonymousId();

            // Convert line breaks to paragraphs for better formatting
            $formattedReply = $this->formatTextToParagraphs($this->newReply);

            Comment::create([
                'story_id' => $this->story->id,
                'parent_id' => $commentId,
                'body' => $formattedReply,
                'alias' => $anonymousService->generateAlias(),
                'cookie_hash' => $anonymousId,
                'status' => 'published',
                'upvotes' => 0,
                'downvotes' => 0,
            ]);

            $this->replyingTo = null;
            $this->newReply = '';
            $this->dispatch('toast-show', [
                'message' => 'Reply added successfully!',
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('toast-show', [
                'message' => 'Failed to add reply. Please try again.',
                'type' => 'error',
            ]);
        }
    }

    public function toggleShareModal()
    {
        $this->showShareModal = !$this->showShareModal;
    }

    public function shareToSocial($platform)
    {
        $url = route('post', $this->story->slug);
        $title = $this->story->title;
        $text = urlencode($title . ' - Read this story on Bluntly');

        $shareUrls = [
            'twitter' => "https://twitter.com/intent/tweet?text={$text}&url={$url}",
            'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$url}",
            'whatsapp' => "https://wa.me/?text={$text}%20{$url}",
            'linkedin' => "https://www.linkedin.com/sharing/share-offsite/?url={$url}",
            'telegram' => "https://t.me/share/url?url={$url}&text={$text}",
            'reddit' => "https://reddit.com/submit?url={$url}&title={$title}",
        ];

        if (isset($shareUrls[$platform])) {
            $this->dispatch('openShareUrl', $shareUrls[$platform]);
        }

        $this->showShareModal = false;
    }

    public function getUserVoteStatus($storyId)
    {
        $anonymousService = app(AnonymousUserService::class);
        return $anonymousService->hasVotedOn('story', $storyId);
    }

    public function getRelatedStoriesProperty()
    {
        $relatedStories = collect();
        $currentStoryId = $this->story->id;

        // Priority 1: Same theme (if story has a theme)
        if ($this->story->theme_id) {
            $themeStories = Story::with(['tags', 'theme'])
                ->where('status', 'published')
                ->where('theme_id', $this->story->theme_id)
                ->where('id', '!=', $currentStoryId)
                ->orderByRaw('(upvotes - downvotes) DESC')
                ->take(6)
                ->get();

            $relatedStories = $relatedStories->merge($themeStories);
        }

        // Priority 2: Same category (if we need more stories)
        if ($relatedStories->count() < 6 && $this->story->category) {
            $categoryStories = Story::with(['tags', 'theme'])
                ->where('status', 'published')
                ->where('category', $this->story->category)
                ->where('id', '!=', $currentStoryId)
                ->whereNotIn('id', $relatedStories->pluck('id')->toArray())
                ->orderByRaw('(upvotes - downvotes) DESC')
                ->take(6 - $relatedStories->count())
                ->get();

            $relatedStories = $relatedStories->merge($categoryStories);
        }

        // Priority 3: Same tags (if we need more stories)
        if ($relatedStories->count() < 6 && $this->story->tags->isNotEmpty()) {
            $tagIds = $this->story->tags->pluck('id')->toArray();

            $tagStories = Story::with(['tags', 'theme'])
                ->where('status', 'published')
                ->where('id', '!=', $currentStoryId)
                ->whereNotIn('id', $relatedStories->pluck('id')->toArray())
                ->whereHas('tags', function ($query) use ($tagIds) {
                    $query->whereIn('tags.id', $tagIds);
                })
                ->orderByRaw('(upvotes - downvotes) DESC')
                ->take(6 - $relatedStories->count())
                ->get();

            $relatedStories = $relatedStories->merge($tagStories);
        }

        // If still need more stories, fill with random popular stories
        if ($relatedStories->count() < 6) {
            $randomStories = Story::with(['tags', 'theme'])
                ->where('status', 'published')
                ->where('id', '!=', $currentStoryId)
                ->whereNotIn('id', $relatedStories->pluck('id')->toArray())
                ->orderByRaw('(upvotes - downvotes) DESC')
                ->take(6 - $relatedStories->count())
                ->get();

            $relatedStories = $relatedStories->merge($randomStories);
        }

        return $relatedStories->take(6);
    }

    private function formatTextToParagraphs($text)
    {
        // Clean the text
        $text = trim($text);

        // Split by double line breaks (paragraph separators)
        $paragraphs = preg_split('/\n\s*\n/', $text);

        // Filter out empty paragraphs and wrap each in <p> tags
        $formattedParagraphs = array_filter(
            array_map(function ($paragraph) {
                $paragraph = trim($paragraph);
                if (empty($paragraph)) {
                    return '';
                }
                // Convert single line breaks to <br> tags within paragraphs
                $paragraph = nl2br($paragraph);
                return '<p>' . $paragraph . '</p>';
            }, $paragraphs),
        );

        // Join paragraphs
        return implode('', $formattedParagraphs);
    }
}; ?>

<div>
    <x-navigation current-page="story" />

    <!-- Story Content -->
    <article class="bg-white dark:bg-zinc-900 min-h-screen">
        <!-- Back Navigation -->
        <div class="max-w-3xl mx-auto px-4 md:px-6 pt-6">
            <a href="{{ route('feed') }}"
                class="inline-flex items-center text-sm text-gray-500 dark:text-zinc-400 hover:text-black dark:hover:text-white transition-colors">
                ← Back to Stories
            </a>
        </div>

        <!-- Story Header -->
        <header class="max-w-3xl mx-auto px-4 md:px-6 pt-6 pb-3">
            <!-- Story Meta -->
            <div class="flex flex-col space-y-2 md:space-y-0 md:flex-row md:items-center md:justify-between mb-3">
                <div class="flex items-center flex-wrap gap-2">
                    <span class="text-xs font-medium text-gray-700 dark:text-zinc-300">{{ '@' . $story->alias }}</span>
                    <span class="text-gray-300 dark:text-zinc-600 hidden md:inline">•</span>
                    <span class="text-xs text-gray-500 dark:text-zinc-400">{{ $story->created_at->diffForHumans() }}</span>
                    @if ($story->category)
                        <span class="text-gray-300 dark:text-zinc-600 hidden md:inline">•</span>
                        <span
                            class="text-xs uppercase tracking-wide text-gray-400 dark:text-zinc-500 bg-gray-100 dark:bg-zinc-800 px-2 py-1 rounded">{{ strtoupper($story->category) }}</span>
                    @endif
                    @if ($story->theme)
                        <span class="text-gray-300 dark:text-zinc-600 hidden md:inline">•</span>
                        <a href="{{ route('theme.details', $story->theme->slug) }}"
                            class="text-xs bg-black text-white dark:bg-white dark:text-black px-2 py-1 rounded-full hover:bg-gray-800 dark:hover:bg-zinc-200 transition-colors">
                            {{ $story->theme->name }}
                        </a>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center space-x-3 md:space-x-4">
                    <button wire:click="toggleShareModal"
                        class="text-gray-500 dark:text-zinc-400 hover:text-black dark:hover:text-white transition-colors text-sm px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-zinc-800 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z">
                            </path>
                        </svg>
                        Share
                    </button>
                    <button
                        class="text-red-500 hover:text-red-700 transition-colors text-sm px-2 py-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 14.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                        Report
                    </button>
                </div>
            </div>

            <!-- Story Title -->
            <h1 class="text-lg md:text-xl lg:text-2xl font-medium leading-tight text-gray-900 dark:text-zinc-100">
                {{ $story->title }}
            </h1>
        </header>

        <!-- Story Body -->
        <div class="max-w-3xl mx-auto px-4 md:px-6">
            <div class="prose max-w-none">
                <div class="text-sm leading-snug text-gray-700 dark:text-zinc-300 mb-4 story-content compact-text">
                    {!! $story->body !!}
                </div>
            </div>
        </div>

        <!-- Story Engagement -->
        <div class="max-w-3xl mx-auto px-4 md:px-6 py-4 border-t border-gray-100 dark:border-zinc-800">
            <div class="flex flex-col space-y-3 md:space-y-0 md:flex-row md:items-center md:justify-between">
                <!-- Voting -->
                <div class="flex items-center space-x-4 md:space-x-6">
                    <div class="flex items-center gap-2">
                        <button wire:click="toggleVote('up')"
                            class="p-1.5 rounded-full transition-colors {{ $this->getUserVoteStatus($story->id) === 'up' ? 'text-green-600 bg-green-50 dark:bg-green-900/30' : 'text-gray-500 dark:text-zinc-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-900/30' }}">
                            ↑
                        </button>
                        <span class="text-xs font-medium text-gray-700 dark:text-zinc-300">{{ $story->upvotes }}</span>
                        <button wire:click="toggleVote('down')"
                            class="p-1.5 rounded-full transition-colors {{ $this->getUserVoteStatus($story->id) === 'down' ? 'text-red-600 bg-red-50 dark:bg-red-900/30' : 'text-gray-500 dark:text-zinc-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30' }}">
                            ↓
                        </button>
                    </div>

                    <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-zinc-400">
                        <span>💬</span>
                        <span>{{ $this->comments->count() }}</span>
                    </div>

                    <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-zinc-400">
                        <span>👁</span>
                        <span>{{ number_format($story->views) }}</span>
                    </div>
                </div>

                <!-- Share -->
                <button wire:click="toggleShareModal"
                    class="flex items-center gap-2 px-3 py-2 text-xs text-gray-600 dark:text-zinc-400 hover:text-black dark:hover:text-white transition-colors self-start md:self-auto hover:bg-gray-50 dark:hover:bg-zinc-800 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z">
                        </path>
                    </svg>
                    Share
                </button>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="max-w-3xl mx-auto px-4 md:px-6 py-4 border-t border-gray-100 dark:border-zinc-800">
            <h3 class="text-base font-medium mb-4 text-gray-900 dark:text-zinc-100">Comments</h3>

            <!-- Comments List -->
            <div class="space-y-6">
                @forelse ($this->comments as $comment)
                    <div class="comment-thread">
                        <div class="flex space-x-3">
                            <!-- Avatar placeholder -->
                            <div
                                class="flex-shrink-0 w-6 h-6 bg-gray-300 dark:bg-zinc-600 rounded-full flex items-center justify-center text-xs font-medium text-gray-600 dark:text-zinc-300">
                                {{ strtoupper(substr($comment->alias, 0, 1)) }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg p-3 shadow-sm">
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs font-medium text-gray-700 dark:text-zinc-300">@
                                                {{ $comment->alias }}</span>
                                            <span
                                                class="text-xs text-gray-400 dark:text-zinc-500">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button wire:click="toggleCommentVote({{ $comment->id }}, 'up')"
                                                class="text-xs text-gray-500 dark:text-zinc-400 hover:text-green-600 px-2 py-1 rounded-full hover:bg-green-50 dark:hover:bg-green-900/30 transition-colors">↑
                                                {{ $comment->upvotes }}</button>
                                            <button wire:click="toggleCommentVote({{ $comment->id }}, 'down')"
                                                class="text-xs text-gray-500 dark:text-zinc-400 hover:text-red-600 px-2 py-1 rounded-full hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors">↓</button>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-700 dark:text-zinc-300 leading-snug mb-2 comment-content compact-text">
                                        {!! $comment->body !!}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Replies -->
                        @if ($comment->replies->isNotEmpty())
                            <div class="ml-11 mt-4 space-y-4 border-l-2 border-gray-200 dark:border-zinc-700 pl-4">
                                @foreach ($comment->replies as $reply)
                                    <div class="flex space-x-3">
                                        <!-- Reply Avatar -->
                                        <div
                                            class="flex-shrink-0 w-7 h-7 bg-gray-300 dark:bg-zinc-600 rounded-full flex items-center justify-center text-xs font-medium text-gray-600 dark:text-zinc-300">
                                            {{ strtoupper(substr($reply->alias, 0, 1)) }}
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 p-3 rounded-lg">
                                                <div class="flex items-center justify-between mb-2">
                                                    <div class="flex items-center space-x-2">
                                                        <span class="text-xs font-medium text-gray-700 dark:text-zinc-300">
                                                            {{ '@' . $reply->alias }}</span>
                                                        <span
                                                            class="text-xs text-gray-400 dark:text-zinc-500">{{ $reply->created_at->diffForHumans() }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <button
                                                            wire:click="toggleCommentVote({{ $reply->id }}, 'up')"
                                                            class="text-xs text-gray-500 dark:text-zinc-400 hover:text-green-600 px-2 py-1 rounded-full hover:bg-green-50 dark:hover:bg-green-900/30 transition-colors">↑
                                                            {{ $reply->upvotes }}</button>
                                                        <button
                                                            wire:click="toggleCommentVote({{ $reply->id }}, 'down')"
                                                            class="text-xs text-gray-500 dark:text-zinc-400 hover:text-red-600 px-2 py-1 rounded-full hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors">↓</button>
                                                    </div>
                                                </div>
                                                <div class="text-sm text-gray-700 dark:text-zinc-300 leading-relaxed comment-content">
                                                    {!! $reply->body !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-8">
                        <p class="text-gray-500 dark:text-zinc-400">No AI comments yet. Our AI personas will start discussing this soon!</p>
                    </div>
                @endforelse
            </div>

            <!-- Load More Comments -->
            <div class="text-center mt-8">
                <button class="text-sm text-gray-600 dark:text-zinc-400 hover:text-black dark:hover:text-white transition-colors">
                    Load more comments
                </button>
            </div>
        </div>

        <!-- Related Stories -->
        @if ($this->relatedStories->isNotEmpty())
            <div class="max-w-3xl mx-auto px-4 md:px-6 py-4 md:py-6 border-t border-gray-100 dark:border-zinc-800">
                <h3 class="text-sm md:text-base font-medium mb-3 md:mb-4 text-gray-900 dark:text-zinc-100">More posts like this</h3>

                <div class="space-y-3 md:space-y-4">
                    @foreach ($this->relatedStories as $relatedStory)
                        <a href="{{ route('post', $relatedStory->slug) }}"
                            class="related-story-card block p-3 md:p-4 border border-gray-200 dark:border-zinc-700 rounded-lg hover:shadow-md active:shadow-sm transition-shadow bg-white dark:bg-zinc-800">

                            <!-- Mobile-first header with stacked layout -->
                            <div class="space-y-2 md:space-y-0">
                                <!-- Top row: Author and category -->
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-xs font-medium text-gray-700 dark:text-zinc-300">{{ '@' . $relatedStory->alias }}</span>
                                    @if ($relatedStory->category)
                                        <span
                                            class="text-xs text-gray-400 dark:text-zinc-500 bg-gray-100 dark:bg-zinc-800 px-2 py-1 rounded">{{ strtoupper($relatedStory->category) }}</span>
                                    @endif
                                </div>

                                <!-- Theme badge on separate line for mobile -->
                                @if ($relatedStory->theme)
                                    <div class="flex items-start">
                                        <span
                                            class="inline-flex items-center text-xs bg-black text-white dark:bg-white dark:text-black px-3 py-1 rounded-full">
                                            <span class="w-1.5 h-1.5 bg-white dark:bg-black rounded-full mr-2"></span>
                                            {{ Str::limit($relatedStory->theme->name, 30) }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Title with mobile-optimized sizing -->
                            <h4
                                class="text-sm md:text-base font-medium text-gray-900 dark:text-zinc-100 mt-3 mb-2 leading-tight line-clamp-2">
                                {{ $relatedStory->title }}
                            </h4>

                            <!-- Description with mobile-friendly text -->
                            <p class="text-xs md:text-sm text-gray-600 dark:text-zinc-400 line-clamp-2 leading-relaxed mb-3">
                                {{ Str::limit(strip_tags($relatedStory->body), 100) }}
                            </p>

                            <!-- Bottom section with improved mobile layout -->
                            <div
                                class="flex flex-col space-y-2 md:space-y-0 md:flex-row md:items-center md:justify-between pt-2 border-t border-gray-100 dark:border-zinc-800">
                                <!-- Tags row -->
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if ($relatedStory->tags->isNotEmpty())
                                        @foreach ($relatedStory->tags->take(2) as $tag)
                                            <span
                                                class="text-xs text-gray-500 dark:text-zinc-400 bg-gray-50 dark:bg-zinc-800 px-2 py-1 rounded">#{{ $tag->name }}</span>
                                        @endforeach
                                    @endif
                                </div>

                                <!-- Stats with larger touch targets -->
                                <div class="flex items-center gap-3 md:gap-4">
                                    <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-zinc-400">
                                        <span class="text-green-600">↑</span>
                                        <span class="font-medium">{{ $relatedStory->upvotes }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-zinc-400">
                                        <span>💬</span>
                                        <span>{{ $relatedStory->comments->count() }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-zinc-400">
                                        <span>👁</span>
                                        <span>{{ number_format($relatedStory->views) }}</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- Show all button for mobile -->
                <div class="mt-4 md:mt-6 text-center">
                    <a href="{{ route('feed') }}"
                        class="inline-flex items-center text-sm text-gray-600 dark:text-zinc-400 hover:text-black dark:hover:text-white transition-colors px-4 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800">
                        See more stories →
                    </a>
                </div>
            </div>
        @endif
    </article>

    <!-- Share Modal -->
    @if ($showShareModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 dark:bg-opacity-70 flex items-center justify-center z-50"
            wire:click="toggleShareModal">
            <div class="bg-white dark:bg-zinc-800 rounded-lg p-6 w-full max-w-md mx-4" wire:click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-zinc-100">Share this story</h3>
                    <button wire:click="toggleShareModal" class="text-gray-400 dark:text-zinc-500 hover:text-gray-600 dark:hover:text-zinc-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <!-- Twitter -->
                    <button wire:click="shareToSocial('twitter')"
                        class="flex items-center gap-3 p-3 border border-gray-200 dark:border-zinc-700 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors">
                        <div
                            class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                            𝕏
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-zinc-300">Twitter</span>
                    </button>

                    <!-- Facebook -->
                    <button wire:click="shareToSocial('facebook')"
                        class="flex items-center gap-3 p-3 border border-gray-200 dark:border-zinc-700 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors">
                        <div
                            class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-bold">
                            f
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-zinc-300">Facebook</span>
                    </button>

                    <!-- WhatsApp -->
                    <button wire:click="shareToSocial('whatsapp')"
                        class="flex items-center gap-3 p-3 border border-gray-200 dark:border-zinc-700 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors">
                        <div
                            class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                            W
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-zinc-300">WhatsApp</span>
                    </button>

                    <!-- LinkedIn -->
                    <button wire:click="shareToSocial('linkedin')"
                        class="flex items-center gap-3 p-3 border border-gray-200 dark:border-zinc-700 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors">
                        <div
                            class="w-8 h-8 bg-blue-700 rounded-full flex items-center justify-center text-white text-sm font-bold">
                            in
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-zinc-300">LinkedIn</span>
                    </button>

                    <!-- Telegram -->
                    <button wire:click="shareToSocial('telegram')"
                        class="flex items-center gap-3 p-3 border border-gray-200 dark:border-zinc-700 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors">
                        <div
                            class="w-8 h-8 bg-blue-400 rounded-full flex items-center justify-center text-white text-sm font-bold">
                            T
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-zinc-300">Telegram</span>
                    </button>

                    <!-- Reddit -->
                    <button wire:click="shareToSocial('reddit')"
                        class="flex items-center gap-3 p-3 border border-gray-200 dark:border-zinc-700 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors">
                        <div
                            class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                            R
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-zinc-300">Reddit</span>
                    </button>
                </div>

                <!-- Copy Link -->
                <div class="border-t border-gray-200 dark:border-zinc-700 pt-4">
                    <button onclick="copyToClipboard()"
                        class="w-full flex items-center gap-3 p-3 border border-gray-200 dark:border-zinc-700 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors">
                        <svg class="w-5 h-5 text-gray-500 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                            </path>
                        </svg>
                        <span class="text-sm font-medium text-gray-700 dark:text-zinc-300">Copy link</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <x-footer />

    <!-- Paragraph Spacing Styles -->
    <style>
        /* Story content paragraph spacing */
        .story-content p {
            margin-bottom: 1.25rem;
        }

        .story-content p:last-child {
            margin-bottom: 0;
        }

        /* Comment content paragraph spacing */
        .comment-content p {
            margin-bottom: 0.75rem;
        }

        .comment-content p:last-child {
            margin-bottom: 0;
        }

        /* Preserve line breaks for simple text */
        .story-content,
        .comment-content {
            white-space: pre-line;
        }

        /* Override white-space for paragraphs to prevent double spacing */
        .story-content p,
        .comment-content p {
            white-space: normal;
        }

        /* Handle other Quill elements */
        .story-content h1,
        .story-content h2,
        .story-content h3 {
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }

        .story-content h1:first-child,
        .story-content h2:first-child,
        .story-content h3:first-child {
            margin-top: 0;
        }

        .story-content ul,
        .story-content ol {
            margin-bottom: 1.25rem;
            padding-left: 1.5rem;
        }

        .story-content li {
            margin-bottom: 0.5rem;
        }

        .story-content blockquote {
            margin: 1.25rem 0;
            padding-left: 1rem;
            border-left: 4px solid #e5e7eb;
            font-style: italic;
            color: #6b7280;
        }

        /* Mobile optimizations for related stories */
        @media (max-width: 768px) {
            .line-clamp-2 {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            /* Better touch targets on mobile */
            .related-story-card {
                min-height: 44px;
                /* iOS recommended minimum touch target */
            }

            /* Enhanced active states for mobile */
            .related-story-card:active {
                transform: scale(0.98);
                transition: transform 0.1s ease;
            }
        }

        /* Line clamp utilities for all screen sizes */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>

    <script>
        // Listen for the share URL event
        window.addEventListener('openShareUrl', event => {
            window.open(event.detail, '_blank', 'width=600,height=400');
        });

        // Copy to clipboard function
        function copyToClipboard() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                // You could add a toast notification here
                alert('Link copied to clipboard!');
            });
        }
    </script>
</div>
