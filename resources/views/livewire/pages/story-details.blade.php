<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Story;
use App\Models\Comment;
use App\Services\AnonymousUserService;

new #[Layout('layouts.app')] class extends Component {
    public $slug;
    public $story;
    public $newComment = '';
    public $replyingTo = null;
    public $newReply = '';
    public $showShareModal = false;

    public function mount($slug)
    {
        $this->slug = $slug;
        $this->story = Story::with(['tags', 'comments.replies'])
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
                    'newComment' => 'required|string|min:10|max:2000',
                ],
                [
                    'newComment.required' => 'Please write a comment.',
                    'newComment.min' => 'Comment must be at least 10 characters.',
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
                    'newReply' => 'required|string|min:5|max:1000',
                ],
                [
                    'newReply.required' => 'Please write a reply.',
                    'newReply.min' => 'Reply must be at least 5 characters.',
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
        $url = url()->current();
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
    <article class="bg-white min-h-screen">
        <!-- Back Navigation -->
        <div class="max-w-3xl mx-auto px-4 md:px-6 pt-6">
            <a href="{{ route('feed') }}"
                class="inline-flex items-center text-sm text-gray-500 hover:text-black transition-colors">
                ← Back to Stories
            </a>
        </div>

        <!-- Story Header -->
        <header class="max-w-3xl mx-auto px-4 md:px-6 pt-6 pb-3">
            <!-- Story Meta -->
            <div class="flex flex-col space-y-3 md:space-y-0 md:flex-row md:items-center md:justify-between mb-4">
                <div class="flex items-center flex-wrap gap-2 md:gap-3">
                    <span class="text-xs font-medium text-gray-700">@ {{ $story->alias }}</span>
                    <span class="text-gray-300 hidden md:inline">•</span>
                    <span class="text-xs text-gray-500">{{ $story->created_at->diffForHumans() }}</span>
                    @if ($story->category)
                        <span class="text-gray-300 hidden md:inline">•</span>
                        <span
                            class="text-xs uppercase tracking-wide text-gray-400 bg-gray-100 px-2 py-1 rounded">{{ strtoupper($story->category) }}</span>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center space-x-3 md:space-x-4">
                    <button class="text-gray-500 hover:text-black transition-colors text-sm px-2 py-1 rounded hover:bg-gray-100 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                        </svg>
                        Share
                    </button>
                    <button class="text-red-500 hover:text-red-700 transition-colors text-sm px-2 py-1 rounded hover:bg-red-50 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 14.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        Report
                    </button>
                </div>
            </div>

            <!-- Story Title -->
            <h1 class="text-xl md:text-2xl lg:text-3xl font-medium leading-tight text-gray-900">
                {{ $story->title }}
            </h1>
        </header>

        <!-- Story Body -->
        <div class="max-w-3xl mx-auto px-4 md:px-6">
            <div class="prose max-w-none">
                <div class="text-base leading-relaxed text-gray-700 mb-6 story-content">
                    {!! $story->body !!}
                </div>
            </div>
        </div>

        <!-- Story Engagement -->
        <div class="max-w-3xl mx-auto px-4 md:px-6 py-6 border-t border-gray-100">
            <div class="flex flex-col space-y-4 md:space-y-0 md:flex-row md:items-center md:justify-between">
                <!-- Voting -->
                <div class="flex items-center space-x-4 md:space-x-6">
                    <div class="flex items-center gap-2">
                        <button wire:click="toggleVote('up')"
                            class="p-1.5 rounded-full transition-colors {{ $this->getUserVoteStatus($story->id) === 'up' ? 'text-green-600 bg-green-50' : 'text-gray-500 hover:text-green-600 hover:bg-green-50' }}">
                            ↑
                        </button>
                        <span class="text-xs font-medium text-gray-700">{{ $story->upvotes }}</span>
                        <button wire:click="toggleVote('down')"
                            class="p-1.5 rounded-full transition-colors {{ $this->getUserVoteStatus($story->id) === 'down' ? 'text-red-600 bg-red-50' : 'text-gray-500 hover:text-red-600 hover:bg-red-50' }}">
                            ↓
                        </button>
                    </div>

                    <div class="flex items-center gap-1 text-xs text-gray-500">
                        <span>💬</span>
                        <span>{{ $this->comments->count() }}</span>
                    </div>

                    <div class="flex items-center gap-1 text-xs text-gray-500">
                        <span>👁</span>
                        <span>{{ number_format($story->views) }}</span>
                    </div>
                </div>

                <!-- Share -->
                <button wire:click="toggleShareModal"
                    class="flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:text-black transition-colors self-start md:self-auto hover:bg-gray-50 rounded-lg">
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
        <div class="max-w-3xl mx-auto px-4 md:px-6 py-6 border-t border-gray-100">
            <h3 class="text-lg font-medium mb-5">Comments</h3>

            <!-- Add Comment -->
            <div class="mb-6">
                <textarea wire:model="newComment" placeholder="Share your thoughts anonymously..." rows="3"
                    class="w-full p-3 border border-gray-200 rounded-lg resize-none focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent text-sm">
                </textarea>
                <div class="flex flex-col space-y-2 md:space-y-0 md:flex-row md:justify-between md:items-center mt-3">
                    <span class="text-xs text-gray-500">Your comment will be posted anonymously</span>
                    <button wire:click="addComment" wire:loading.attr="disabled" wire:target="addComment"
                        class="bg-black text-white px-4 py-2 rounded-lg text-xs font-medium hover:bg-gray-800 transition-colors self-start md:self-auto disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="addComment">Post Comment</span>
                        <span wire:loading wire:target="addComment" class="flex items-center gap-1">
                            <svg class="animate-spin h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Posting...
                        </span>
                    </button>
                </div>
            </div>

            <!-- Comments List -->
            <div class="space-y-8">
                @forelse ($this->comments as $comment)
                    <div class="comment-thread">
                        <div class="flex space-x-3">
                            <!-- Avatar placeholder -->
                            <div
                                class="flex-shrink-0 w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-xs font-medium text-gray-600">
                                {{ strtoupper(substr($comment->alias, 0, 1)) }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs font-medium text-gray-700">@
                                                {{ $comment->alias }}</span>
                                            <span
                                                class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button wire:click="toggleCommentVote({{ $comment->id }}, 'up')"
                                                class="text-xs text-gray-500 hover:text-green-600 px-2 py-1 rounded-full hover:bg-green-50 transition-colors">↑
                                                {{ $comment->upvotes }}</button>
                                            <button wire:click="toggleCommentVote({{ $comment->id }}, 'down')"
                                                class="text-xs text-gray-500 hover:text-red-600 px-2 py-1 rounded-full hover:bg-red-50 transition-colors">↓</button>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-700 leading-relaxed mb-2 comment-content">
                                        {!! $comment->body !!}
                                    </div>
                                    <button wire:click="startReply({{ $comment->id }})"
                                        class="text-xs text-gray-500 hover:text-black transition-colors px-2 py-1 rounded hover:bg-gray-50">
                                        💬 Reply
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Reply Form -->
                        @if ($replyingTo === $comment->id)
                            <div class="ml-11 mt-3">
                                <div class="bg-gray-50 border border-gray-200 p-4 rounded-lg">
                                    <textarea wire:model="newReply" placeholder="Write a reply..." rows="2"
                                        class="w-full p-3 border border-gray-200 rounded-lg resize-none focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent text-sm bg-white">
                                    </textarea>
                                    <div class="flex justify-between items-center mt-3">
                                        <span class="text-xs text-gray-500">Your reply will be posted anonymously</span>
                                        <div class="flex gap-2">
                                            <button wire:click="cancelReply"
                                                class="text-xs text-gray-500 hover:text-black transition-colors px-3 py-1 rounded hover:bg-gray-100">
                                                Cancel
                                            </button>
                                            <button wire:click="addReply({{ $comment->id }})"
                                                wire:loading.attr="disabled" wire:target="addReply"
                                                class="bg-black text-white px-3 py-1 rounded text-xs font-medium hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span wire:loading.remove wire:target="addReply">Reply</span>
                                                <span wire:loading wire:target="addReply"
                                                    class="flex items-center gap-1">
                                                    <svg class="animate-spin h-3 w-3 text-white"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12"
                                                            r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                        </path>
                                                    </svg>
                                                    Replying...
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Replies -->
                        @if ($comment->replies->isNotEmpty())
                            <div class="ml-11 mt-4 space-y-4 border-l-2 border-gray-200 pl-4">
                                @foreach ($comment->replies as $reply)
                                    <div class="flex space-x-3">
                                        <!-- Reply Avatar -->
                                        <div
                                            class="flex-shrink-0 w-7 h-7 bg-gray-300 rounded-full flex items-center justify-center text-xs font-medium text-gray-600">
                                            {{ strtoupper(substr($reply->alias, 0, 1)) }}
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                                                <div class="flex items-center justify-between mb-2">
                                                    <div class="flex items-center space-x-2">
                                                        <span class="text-xs font-medium text-gray-700">@
                                                            {{ $reply->alias }}</span>
                                                        <span
                                                            class="text-xs text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <button
                                                            wire:click="toggleCommentVote({{ $reply->id }}, 'up')"
                                                            class="text-xs text-gray-500 hover:text-green-600 px-2 py-1 rounded-full hover:bg-green-50 transition-colors">↑
                                                            {{ $reply->upvotes }}</button>
                                                        <button
                                                            wire:click="toggleCommentVote({{ $reply->id }}, 'down')"
                                                            class="text-xs text-gray-500 hover:text-red-600 px-2 py-1 rounded-full hover:bg-red-50 transition-colors">↓</button>
                                                    </div>
                                                </div>
                                                <div class="text-sm text-gray-700 leading-relaxed comment-content">
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
                        <p class="text-gray-500">No comments yet. Be the first to share your thoughts!</p>
                    </div>
                @endforelse
            </div>

            <!-- Load More Comments -->
            <div class="text-center mt-8">
                <button class="text-sm text-gray-600 hover:text-black transition-colors">
                    Load more comments
                </button>
            </div>
        </div>

        <!-- Related Stories -->
        <div class="max-w-3xl mx-auto px-4 md:px-6 py-8 border-t border-gray-100">
            <h3 class="text-lg font-medium mb-5">More posts like this</h3>

            <div class="space-y-3">
                <!-- Related Story 1 -->
                <a href="/post/another-story"
                    class="block p-3 border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-medium text-gray-700">@nightthoughts</span>
                        <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded">CONFESSION</span>
                    </div>
                    <h4 class="text-sm font-medium text-gray-900 mb-2">I've been eating lunch alone in my car for six
                        months
                    </h4>
                    <p class="text-xs text-gray-600 line-clamp-2">Started a new job six months ago and still haven't
                        made a single friend...</p>
                </a>

                <!-- Related Story 2 -->
                <a href="/post/family-expectations"
                    class="block p-3 border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-medium text-gray-700">@strugglingstudent</span>
                        <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded">RANT</span>
                    </div>
                    <h4 class="text-sm font-medium text-gray-900 mb-2">My parents think I'm thriving in college but I'm
                        barely
                        surviving</h4>
                    <p class="text-xs text-gray-600 line-clamp-2">They're so proud of their first-generation college
                        student, but I'm failing half my classes...</p>
                </a>
            </div>
        </div>
    </article>

    <!-- Share Modal -->
    @if ($showShareModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            wire:click="toggleShareModal">
            <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4" wire:click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Share this story</h3>
                    <button wire:click="toggleShareModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <!-- Twitter -->
                    <button wire:click="shareToSocial('twitter')"
                        class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div
                            class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                            𝕏
                        </div>
                        <span class="text-sm font-medium text-gray-700">Twitter</span>
                    </button>

                    <!-- Facebook -->
                    <button wire:click="shareToSocial('facebook')"
                        class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div
                            class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-bold">
                            f
                        </div>
                        <span class="text-sm font-medium text-gray-700">Facebook</span>
                    </button>

                    <!-- WhatsApp -->
                    <button wire:click="shareToSocial('whatsapp')"
                        class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div
                            class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                            W
                        </div>
                        <span class="text-sm font-medium text-gray-700">WhatsApp</span>
                    </button>

                    <!-- LinkedIn -->
                    <button wire:click="shareToSocial('linkedin')"
                        class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div
                            class="w-8 h-8 bg-blue-700 rounded-full flex items-center justify-center text-white text-sm font-bold">
                            in
                        </div>
                        <span class="text-sm font-medium text-gray-700">LinkedIn</span>
                    </button>

                    <!-- Telegram -->
                    <button wire:click="shareToSocial('telegram')"
                        class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div
                            class="w-8 h-8 bg-blue-400 rounded-full flex items-center justify-center text-white text-sm font-bold">
                            T
                        </div>
                        <span class="text-sm font-medium text-gray-700">Telegram</span>
                    </button>

                    <!-- Reddit -->
                    <button wire:click="shareToSocial('reddit')"
                        class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div
                            class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                            R
                        </div>
                        <span class="text-sm font-medium text-gray-700">Reddit</span>
                    </button>
                </div>

                <!-- Copy Link -->
                <div class="border-t pt-4">
                    <button onclick="copyToClipboard()"
                        class="w-full flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                            </path>
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Copy link</span>
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
