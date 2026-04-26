@extends('common::prerender.base')

@section('head')
    @include($seoTagsView ?? 'seo.blog-page.seo-tags')
@endsection

@section('body')
    <h1>Blog</h1>

    @foreach ($pagination as $post)
        <article>
            <h2><a href="{{ url('/blog/' . $post->slug) }}">{{ $post->title }}</a></h2>
            @if ($post->excerpt)
                <p>{{ $post->excerpt }}</p>
            @endif
        </article>
    @endforeach
@endsection