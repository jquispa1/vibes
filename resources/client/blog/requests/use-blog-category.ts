import {useQuery} from '@tanstack/react-query';
import {apiClient} from '@common/http/query-client';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {BlogCategory} from '@app/blog/blog-category';

export interface BlogCategoryResponse extends BackendResponse {
  blogCategory: BlogCategory & {blogPosts_count?: number};
}

export function useBlogCategory(slug: string) {
  return useQuery({
    queryKey: ['blogCategory', slug],
    queryFn: () => fetchBlogCategory(slug),
    enabled: !!slug,
  });
}

function fetchBlogCategory(slug: string) {
  return apiClient
    .get<BlogCategoryResponse>(`blog-categories/${slug}`)
    .then(response => response.data);
}