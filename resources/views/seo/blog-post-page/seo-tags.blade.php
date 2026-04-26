<meta property="og:site_name" content="{{ settings('branding.site_name') }}" />
<meta property="twitter:card" content="summary_large_image" />
<meta property="twitter:title" content="{{ $blogPost->meta_title ?: $blogPost->title }} - {{ settings('branding.site_name') }}" />
<meta property="og:type" content="article" />
<title>{{ $blogPost->meta_title ?: $blogPost->title }} - {{ settings('branding.site_name') }}</title>
<meta
    property="og:title"
    content="{{ $blogPost->meta_title ?: $blogPost->title }} - {{ settings('branding.site_name') }}"
/>
<meta property="og:url" content="{{ url('/blog/' . $blogPost->slug) }}" />
<link rel="canonical" href="{{ url('/blog/' . $blogPost->slug) }}" />

@if ($blogPost->meta_description ?: $blogPost->excerpt)
    <meta property="og:description" content="{{ $blogPost->meta_description ?: $blogPost->excerpt }}" />
    <meta name="description" content="{{ $blogPost->meta_description ?: $blogPost->excerpt }}" />
    <meta property="twitter:description" content="{{ $blogPost->meta_description ?: $blogPost->excerpt }}" />
@endif

@if ($blogPost->featured_image)
    <meta property="og:image" content="{{ $blogPost->featured_image }}" />
    <meta property="twitter:image" content="{{ $blogPost->featured_image }}" />
@endif

@if ($blogPost->published_at)
    <meta property="article:published_time" content="{{ $blogPost->published_at->toIso8601String() }}" />
@endif

@if ($blogPost->updated_at)
    <meta property="article:modified_time" content="{{ $blogPost->updated_at->toIso8601String() }}" />
@endif

<meta property="article:author" content="{{ $blogPost->author?->name ?? settings('branding.site_name') }}" />

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": "{{ $blogPost->meta_title ?: $blogPost->title }}",
    "url": "{{ url('/blog/' . $blogPost->slug) }}",
    @if ($blogPost->meta_description ?: $blogPost->excerpt)
    "description": "{{ $blogPost->meta_description ?: $blogPost->excerpt }}",
    @endif
    @if ($blogPost->featured_image)
    "image": "{{ $blogPost->featured_image }}",
    @endif
    @if ($blogPost->published_at)
    "datePublished": "{{ $blogPost->published_at->toIso8601String() }}",
    @endif
    @if ($blogPost->updated_at)
    "dateModified": "{{ $blogPost->updated_at->toIso8601String() }}",
    @endif
    "author": {
        "@type": "Person",
        "name": "{{ $blogPost->author?->name ?? settings('branding.site_name') }}"
    },
    "publisher": {
        "@type": "Organization",
        "name": "{{ settings('branding.site_name') }}",
        "url": "{{ url('/') }}"
    },
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "{{ url('/blog/' . $blogPost->slug) }}"
    }
}
</script>