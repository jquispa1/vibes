@extends('common::prerender.base')

@section('head')
    @include($seoTagsView ?? 'seo.blog-post-page.seo-tags')
@endsection

@section('body')
    <h1>{{ $blogPost->title }}</h1>

    @if ($blogPost->excerpt)
        <p>{{ $blogPost->excerpt }}</p>
    @endif

    <main>
        {!! $blogPost->content !!}
    </main>
@endsection