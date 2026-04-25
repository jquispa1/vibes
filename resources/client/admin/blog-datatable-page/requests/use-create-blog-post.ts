import {useMutation} from '@tanstack/react-query';
import {apiClient, queryClient} from '@common/http/query-client';
import {showHttpErrorToast} from '@common/http/show-http-error-toast';
import {DatatableDataQueryKey} from '@common/datatable/requests/paginated-resources';
import {toast} from '@ui/toast/toast';
import {message} from '@ui/i18n/message';
import {BlogPost} from '@app/blog/requests/use-blog-posts';

export interface CreateBlogPostPayload {
  title?: string;
  slug?: string;
  excerpt?: string;
  content?: string;
  featured_image?: string;
  status?: 'draft' | 'published';
  meta_title?: string;
  meta_description?: string;
}

interface Response {
  blogPost: BlogPost;
}

export function useCreateBlogPost() {
  return useMutation({
    mutationFn: (payload: CreateBlogPostPayload) =>
      apiClient.post<Response>('blog-posts', payload).then(r => r.data),
    onError: err => showHttpErrorToast(err),
    onSuccess: async () => {
      await queryClient.invalidateQueries({queryKey: ['blogPosts']});
      await queryClient.invalidateQueries({queryKey: DatatableDataQueryKey('blog-posts')});
      toast(message('Post created'));
    },
  });
}