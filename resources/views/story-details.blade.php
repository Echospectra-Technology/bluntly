@extends('layouts.web')

@section('title', $story->title . ' - Bluntly')

@section('meta_description', Str::limit(strip_tags($story->body), 160))
@section('meta_keywords', 'anonymous story, confession, rant, ' . ($story->category ? $story->category . ', ' : '') . ($story->tags->isNotEmpty() ? $story->tags->pluck('name')->implode(', ') : 'anonymous posting'))
@section('canonical_url', route('post', $story->slug))

@section('og_title', $story->title)
@section('og_description', Str::limit(strip_tags($story->body), 160))
@section('og_type', 'article')
@section('og_url', route('post', $story->slug))
@section('og_image', route('post.share-image', $story->slug))
@section('og_image_width', '1200')
@section('og_image_height', '630')
@section('og_image_alt', $story->title . ' - Bluntly')

@section('article_published_time', $story->created_at->toISOString())
@section('article_modified_time', $story->updated_at->toISOString())
@section('article_author', '@' . $story->alias)
@section('article_section', $story->category ?? '')
@section('article_tag', $story->tags->isNotEmpty() ? $story->tags->pluck('name')->implode(', ') : '')

@push('structured-data')
@php
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $story->title,
        'description' => Str::limit(strip_tags($story->body), 160),
        'articleBody' => strip_tags($story->body),
        'url' => route('post', $story->slug),
        'datePublished' => $story->created_at->toISOString(),
        'dateModified' => $story->updated_at->toISOString(),
        'author' => [
            '@type' => 'Person',
            'name' => '@' . $story->alias
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Bluntly',
            'url' => url('/'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset('apple-touch-icon.png')
            ]
        ],
        'interactionStatistic' => [
            [
                '@type' => 'InteractionCounter',
                'interactionType' => 'https://schema.org/LikeAction',
                'userInteractionCount' => $story->upvotes
            ],
            [
                '@type' => 'InteractionCounter',
                'interactionType' => 'https://schema.org/DislikeAction',
                'userInteractionCount' => $story->downvotes
            ],
            [
                '@type' => 'InteractionCounter',
                'interactionType' => 'https://schema.org/CommentAction',
                'userInteractionCount' => $story->comments->count()
            ]
        ],
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => route('post', $story->slug)
        ]
    ];

    if ($story->category) {
        $structuredData['articleSection'] = $story->category;
    }

    if ($story->tags->isNotEmpty()) {
        $structuredData['keywords'] = $story->tags->pluck('name')->toArray();
    }
@endphp

<script type="application/ld+json">
{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
    @livewire('pages.story-details', ['slug' => $story->slug])
@endsection
