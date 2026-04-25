import {Link} from 'react-router';
import {PageStatus} from '@common/http/page-status';
import {useBlogPost} from './requests/use-blog-post';

export function BlogPostPage() {
  const query = useBlogPost();

  if (!query.data) {
    return <PageStatus query={query} loaderIsScreen={false} />;
  }

  const post = query.data.blogPost;

  return (
    <article className="mx-auto min-h-[calc(100vh-6rem)] max-w-4xl px-6 py-16">
      <div className="mb-10 flex items-center justify-between gap-4">
        <Link className="text-sm font-semibold text-primary hover:underline" to="/blog">
          ← Volver al blog
        </Link>
        <span className="text-sm text-muted-foreground">
          {post.published_at ? new Date(post.published_at).toLocaleDateString() : 'Borrador'}
        </span>
      </div>

      <header className="mb-10">
        <p className="mb-3 text-sm font-semibold uppercase tracking-[0.3em] text-primary">
          Blog
        </p>
        <h1 className="text-4xl font-extrabold tracking-tight text-foreground md:text-6xl">
          {post.title}
        </h1>
        <p className="mt-5 text-lg leading-8 text-muted-foreground">
          {post.excerpt || post.meta_description || ''}
        </p>
      </header>

      {post.featured_image ? (
        <img
          alt={post.title}
          className="mb-10 w-full rounded-3xl object-cover shadow-lg"
          src={post.featured_image}
        />
      ) : null}

      <div
        className="prose prose-neutral max-w-none prose-headings:scroll-mt-24 prose-a:text-primary prose-img:rounded-2xl"
        dangerouslySetInnerHTML={{__html: post.content}}
      />
    </article>
  );
}