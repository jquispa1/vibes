import {useQuery} from '@tanstack/react-query';
import {apiClient} from '@common/http/query-client';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {BlogCategory} from '@app/blog/blog-category';

export interface BlogAuthor {
  id: number;
  name: string;
}

export interface BlogPost {
  id: number;
  title: string;
  slug: string;
  excerpt?: string | null;
  content: string;
  featured_image?: string | null;
  meta_title?: string | null;
  meta_description?: string | null;
  published_at?: string | null;
  created_at?: string;
  updated_at?: string;
  status: 'draft' | 'published';
  author?: BlogAuthor | null;
  categories?: BlogCategory[];
}

export interface BlogPostsResponse extends BackendResponse {
  pagination: {
    data: BlogPost[];
    current_page: number;
    last_page: number;
    total: number;
  };
}

export function useBlogPosts() {
  return useQuery({
    queryKey: ['blogPosts'],
    queryFn: () => fetchBlogPosts(),
  });
}

function fetchBlogPosts() {
  return apiClient
    .get<BlogPostsResponse>('blog-posts', {params: {publishedOnly: true}})
    .then(response => response.data);
}