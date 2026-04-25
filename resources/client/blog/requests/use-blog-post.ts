import {useQuery} from '@tanstack/react-query';
import {apiClient} from '@common/http/query-client';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {useParams} from 'react-router';
import {BlogPost} from './use-blog-posts';

export interface BlogPostResponse extends BackendResponse {
  blogPost: BlogPost;
}

export function useBlogPost() {
  const {slug} = useParams();

  return useQuery({
    queryKey: ['blogPosts', slug],
    queryFn: () => fetchBlogPost(slug!),
    enabled: !!slug,
  });
}

function fetchBlogPost(slug: string) {
  return apiClient
    .get<BlogPostResponse>(`blog-posts/${slug}`)
    .then(response => response.data);
}