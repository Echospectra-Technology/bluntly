@extends('layouts.web')

@section('title', $story->title . ' - Bluntly')

@section('og_title', $story->title)
@section('og_description', Str::limit(strip_tags($story->body), 160))
@section('og_type', 'article')
@section('og_url', url()->current())

@section('meta_description', Str::limit(strip_tags($story->body), 160))

@section('content')
    @livewire('pages.story-details', ['slug' => $story->slug])
@endsection
