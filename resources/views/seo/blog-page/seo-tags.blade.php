<meta property="og:site_name" content="{{ settings('branding.site_name') }}" />
<meta property="twitter:card" content="summary" />
<meta property="twitter:title" content="Blog - {{ settings('branding.site_name') }}" />
<meta property="twitter:description" content="Novedades, guías y artículos sobre la plataforma {{ settings('branding.site_name') }}." />
<meta property="og:type" content="website" />
<title>Blog - {{ settings('branding.site_name') }}</title>
<meta
    property="og:title"
    content="Blog - {{ settings('branding.site_name') }}"
/>
<meta property="og:url" content="{{ url('/blog') }}" />
<link rel="canonical" href="{{ url('/blog') }}" />
<meta
    property="og:description"
    content="Novedades, guías y artículos sobre la plataforma {{ settings('branding.site_name') }}."
/>
<meta
    name="description"
    content="Novedades, guías y artículos sobre la plataforma {{ settings('branding.site_name') }}."
/>

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "CollectionPage",
    "name": "Blog - {{ settings('branding.site_name') }}",
    "description": "Novedades, guías y artículos sobre la plataforma {{ settings('branding.site_name') }}.",
    "url": "{{ url('/blog') }}",
    "isPartOf": {
        "@type": "WebSite",
        "name": "{{ settings('branding.site_name') }}",
        "url": "{{ url('/') }}"
    }
}
</script>