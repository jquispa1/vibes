<meta property="og:site_name" content="{{ settings('branding.site_name') }}" />
<meta property="twitter:card" content="summary_large_image" />
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
@endif

@if ($blogPost->featured_image)
    <meta property="og:image" content="{{ $blogPost->featured_image }}" />
@endif

@if ($blogPost->published_at)
    <meta property="article:published_time" content="{{ $blogPost->published_at }}" />
@endif