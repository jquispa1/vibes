import {Link} from 'react-router';
import {PageStatus} from '@common/http/page-status';
import {useBlogPost} from './requests/use-blog-post';
import {Navbar} from '@common/ui/navigation/navbar/navbar';
import {Footer} from '@common/ui/footer/footer';
import {useEffect, useRef} from 'react';
import {highlightAllCode} from '@common/text-editor/highlight/highlight-code';
import {PageMetaTags} from '@common/http/page-meta-tags';
import {BlogCategory} from './blog-category';

export function BlogPostPage() {
  const query = useBlogPost();
  const post = query.data?.blogPost;

  return (
    <div className="flex min-h-screen flex-col bg">
      <PageMetaTags query={query} />
      <Navbar
        menuPosition="custom-page-navbar"
        className="sticky top-0 flex-shrink-0"
      />
      <div className="flex-auto">
        {!post ? (
          <PageStatus query={query} loaderClassName="mt-80" />
        ) : (
          <BlogPostContent post={post} />
        )}
      </div>
      <Footer className="mx-14 md:mx-40" />
    </div>
  );
}

function BlogPostContent({post}: {post: any}) {
  const bodyRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (bodyRef.current) {
      highlightAllCode(bodyRef.current);
    }
  }, []);

  return (
    <div className="px-16 md:px-24">
      <article className="mx-auto max-w-850 py-48 md:py-80">
        <Link
          to="/blog"
          className="mb-32 inline-flex items-center gap-6 text-sm text-muted transition-colors hover:text-primary"
        >
          <svg
            width="16"
            height="16"
            viewBox="0 0 16 16"
            fill="none"
          >
            <path
              d="M13 8H3m0 0l4-4M3 8l4 4"
              stroke="currentColor"
              strokeWidth="1.5"
              strokeLinecap="round"
              strokeLinejoin="round"
            />
          </svg>
          Volver al blog
        </Link>

        <header className="mb-40">
          <div className="mb-16 flex items-center gap-8 text-xs uppercase tracking-widest text-muted">
            {post.published_at ? (
              <time>
                {new Date(post.published_at).toLocaleDateString('es-ES', {
                  year: 'numeric',
                  month: 'long',
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
          <h1 className="text-3xl font-bold leading-tight tracking-tight md:text-5xl">
            {post.title}
          </h1>
          {post.categories?.length ? (
            <div className="mt-16 flex flex-wrap gap-8">
              {post.categories.map((category: BlogCategory) => (
                <Link
                  key={category.id}
                  to={`/blog/category/${category.slug}`}
                  className="rounded-full border border-primary/20 bg-chip px-12 py-6 text-xs font-semibold tracking-wide text-primary transition-colors hover:border-primary/40"
                >
                  {category.name}
                </Link>
              ))}
            </div>
          ) : null}
          {(post.excerpt || post.meta_description) && (
            <p className="mt-16 text-lg leading-relaxed text-muted">
              {post.excerpt || post.meta_description}
            </p>
          )}
        </header>

        {post.featured_image && (
          <img
            alt={post.title}
            className="mb-40 w-full rounded-xl object-cover"
            src={post.featured_image}
          />
        )}

        <div
          ref={bodyRef}
          className="custom-page-body prose mx-auto dark:prose-invert whitespace-pre-wrap break-words"
          dangerouslySetInnerHTML={{__html: post.content}}
        />
      </article>
    </div>
  );
}