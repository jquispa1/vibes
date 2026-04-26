import {Link} from 'react-router';
import {PageStatus} from '@common/http/page-status';
import {useBlogPosts, BlogPost} from './requests/use-blog-posts';
import {Navbar} from '@common/ui/navigation/navbar/navbar';
import {Footer} from '@common/ui/footer/footer';
import {Helmet} from '@common/seo/helmet';

export function BlogPage() {
  const query = useBlogPosts();

  return (
    <div className="flex min-h-screen flex-col bg">
      <Helmet>
        <title>Blog - Vibeturn</title>
        <meta
          name="description"
          content="Novedades, guías y artículos sobre la plataforma Vibeturn."
        />
        <meta property="og:title" content="Blog - Vibeturn" />
        <meta
          property="og:description"
          content="Novedades, guías y artículos sobre la plataforma Vibeturn."
        />
        <meta property="og:type" content="website" />
      </Helmet>
      <Navbar
        menuPosition="custom-page-navbar"
        className="sticky top-0 flex-shrink-0"
      />
      <div className="flex-auto">
        {!query.data ? (
          <PageStatus query={query} loaderClassName="mt-80" />
        ) : (
          <BlogPageContent posts={query.data.pagination.data} />
        )}
      </div>
      <Footer className="mx-14 md:mx-40" />
    </div>
  );
}

function BlogPageContent({posts}: {posts: BlogPost[]}) {
  const featured = posts[0];
  const rest = posts.slice(1);

  return (
    <div className="px-16 md:px-24">
      <div className="mx-auto max-w-1280 py-48 md:py-80">
        <div className="mb-48 md:mb-64">
          <h1 className="text-4xl font-bold tracking-tight md:text-5xl">
            Blog
          </h1>
          <p className="mt-12 max-w-640 text-lg text-muted">
            Novedades, guías y artículos sobre la plataforma.
          </p>
        </div>

        {featured && (
          <Link
            to={`/blog/${featured.slug}`}
            className="group mb-48 block overflow-hidden rounded-2xl border transition-colors hover:border-primary/40 md:mb-64"
          >
            <div className="flex flex-col md:flex-row">
              {featured.featured_image && (
                <div className="aspect-video w-full flex-shrink-0 overflow-hidden md:aspect-auto md:w-1/2">
                  <img
                    src={featured.featured_image}
                    alt={featured.title}
                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                  />
                </div>
              )}
              <div
                className={`flex flex-col justify-center p-24 md:p-40 ${!featured.featured_image ? 'w-full' : ''}`}
              >
                <div className="mb-12 flex items-center gap-8 text-xs uppercase tracking-widest text-muted">
                  {featured.published_at ? (
                    <time>
                      {new Date(featured.published_at).toLocaleDateString(
                        'es-ES',
                        {year: 'numeric', month: 'long', day: 'numeric'},
                      )}
                    </time>
                  ) : (
                    <span className="rounded-full bg-chip px-8 py-2 text-[11px]">
                      Borrador
                    </span>
                  )}
                  {featured.author?.name && (
                    <>
                      <span className="text-muted/40">·</span>
                      <span>{featured.author.name}</span>
                    </>
                  )}
                </div>
                <h2 className="text-2xl font-bold leading-tight md:text-3xl">
                  {featured.title}
                </h2>
                {(featured.excerpt || featured.meta_description) && (
                  <p className="mt-12 line-clamp-3 text-[15px] leading-relaxed text-muted">
                    {featured.excerpt || featured.meta_description}
                  </p>
                )}
                <span className="mt-20 inline-flex items-center gap-6 text-sm font-semibold text-primary">
                  Leer artículo
                  <svg
                    width="16"
                    height="16"
                    viewBox="0 0 16 16"
                    fill="none"
                    className="transition-transform group-hover:translate-x-4"
                  >
                    <path
                      d="M3 8h10m0 0L9 4m4 4L9 12"
                      stroke="currentColor"
                      strokeWidth="1.5"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    />
                  </svg>
                </span>
              </div>
            </div>
          </Link>
        )}

        {rest.length > 0 && (
          <div className="grid gap-24 sm:grid-cols-2 lg:grid-cols-3">
            {rest.map(post => (
              <BlogCard key={post.id} post={post} />
            ))}
          </div>
        )}
      </div>
    </div>
  );
}

function BlogCard({post}: {post: BlogPost}) {
  return (
    <Link
      to={`/blog/${post.slug}`}
      className="group flex flex-col overflow-hidden rounded-xl border transition-colors hover:border-primary/40"
    >
      {post.featured_image && (
        <div className="aspect-video overflow-hidden">
          <img
            src={post.featured_image}
            alt={post.title}
            className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
          />
        </div>
      )}
      <div className="flex flex-1 flex-col p-20">
        <div className="mb-10 flex items-center gap-8 text-xs uppercase tracking-widest text-muted">
          {post.published_at ? (
            <time>
              {new Date(post.published_at).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
              })}
            </time>
          ) : (
            <span className="rounded-full bg-chip px-8 py-2 text-[11px]">
              Borrador
            </span>
          )}
          {post.author?.name && (
            <>
              <span className="text-muted/40">·</span>
              <span>{post.author.name}</span>
            </>
          )}
        </div>
        <h3 className="text-lg font-semibold leading-snug">{post.title}</h3>
        <p className="mt-8 line-clamp-3 flex-1 text-sm leading-relaxed text-muted">
          {post.excerpt || post.meta_description || ''}
        </p>
        <span className="mt-16 inline-flex items-center gap-6 text-sm font-semibold text-primary">
          Leer
          <svg
            width="14"
            height="14"
            viewBox="0 0 16 16"
            fill="none"
            className="transition-transform group-hover:translate-x-4"
          >
            <path
              d="M3 8h10m0 0L9 4m4 4L9 12"
              stroke="currentColor"
              strokeWidth="1.5"
              strokeLinecap="round"
              strokeLinejoin="round"
            />
          </svg>
        </span>
      </div>
    </Link>
  );
}