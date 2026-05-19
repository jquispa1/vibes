import {useParams, useSearchParams} from 'react-router';
import {PageStatus} from '@common/http/page-status';
import {Navbar} from '@common/ui/navigation/navbar/navbar';
import {Footer} from '@common/ui/footer/footer';
import {PageMetaTags} from '@common/http/page-meta-tags';
import {useBlogCategory} from './requests/use-blog-category';
import {useBlogPosts} from './requests/use-blog-posts';
import {BlogPageContent} from './blog-page';

export function BlogCategoryPage() {
  const {slug = ''} = useParams();
  const [searchParams] = useSearchParams();
  const pageParam = searchParams.get('page');
  const pageNumber = pageParam ? Number(pageParam) : undefined;
  const page = Number.isFinite(pageNumber) && pageNumber! > 0 ? pageNumber : undefined;
  const categoryQuery = useBlogCategory(slug);
  const postsQuery = useBlogPosts({category: slug, page});

  return (
    <div className="flex min-h-screen flex-col bg">
      <PageMetaTags query={categoryQuery} />
      <Navbar
        menuPosition="custom-page-navbar"
        className="sticky top-0 flex-shrink-0"
      />
      <div className="flex-auto">
        {!categoryQuery.data || !postsQuery.data ? (
          <PageStatus query={postsQuery} loaderClassName="mt-80" />
        ) : (
          <BlogPageContent
            title={categoryQuery.data.blogCategory.name}
            subtitle={`Posts en la categoría ${categoryQuery.data.blogCategory.name}`}
            posts={postsQuery.data.pagination.data}
            pagination={postsQuery.data.pagination}
          />
        )}
      </div>
      <Footer className="mx-14 md:mx-40" />
    </div>
  );
}
