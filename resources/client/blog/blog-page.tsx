import {Link} from 'react-router';
import {PageStatus} from '@common/http/page-status';
import {useBlogPosts} from './requests/use-blog-posts';
import {Navbar} from '@common/ui/navigation/navbar/navbar';
import {Footer} from '@common/ui/footer/footer';

export function BlogPage() {
  const query = useBlogPosts();

  return (
    <div className="flex min-h-screen flex-col bg">
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

function BlogPageContent({posts}: {posts: any[]}) {
  return (
    <div className="mx-auto min-h-[calc(100vh-6rem)] max-w-5xl px-6 py-16">
      <div className="mb-12 max-w-3xl">
        <p className="mb-3 text-sm font-semibold uppercase tracking-[0.3em] text-primary">
          Blog
        </p>
        <h1 className="text-4xl font-extrabold tracking-tight text-foreground md:text-6xl">
          Novedades, guías y artículos SEO
        </h1>
        <p className="mt-5 text-lg text-muted-foreground">
          Un espacio para publicar contenido largo, estructurado y fácil de
          navegar dentro del proyecto.
        </p>
      </div>

      <div className="grid gap-6 md:grid-cols-2">
        {posts.map(post => (
          <article
            key={post.id}
            className="rounded-3xl border border-border/60 bg-card/80 p-6 shadow-sm backdrop-blur"
          >
            <div className="mb-4 flex items-center gap-3 text-xs uppercase tracking-[0.24em] text-muted-foreground">
              <span>{post.published_at ? new Date(post.published_at).toLocaleDateString() : 'Borrador'}</span>
              {post.author?.name ? <span>· {post.author.name}</span> : null}
            </div>
            <h2 className="text-2xl font-bold leading-tight text-foreground">
              <Link className="hover:underline" to={`/blog/${post.slug}`}>
                {post.title}
              </Link>
            </h2>
            <p className="mt-4 line-clamp-4 text-sm leading-7 text-muted-foreground">
              {post.excerpt || post.meta_description || 'Sin resumen disponible.'}
            </p>
            <div className="mt-6">
              <Link
                className="inline-flex items-center gap-2 rounded-full bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground transition hover:opacity-90"
                to={`/blog/${post.slug}`}
              >
                Leer artículo
              </Link>
            </div>
          </article>
        ))}
      </div>
    </div>
  );
}