import {useMutation} from '@tanstack/react-query';
import {apiClient, queryClient} from '@common/http/query-client';
import {showHttpErrorToast} from '@common/http/show-http-error-toast';
import {DatatableDataQueryKey} from '@common/datatable/requests/paginated-resources';
import {toast} from '@ui/toast/toast';
import {message} from '@ui/i18n/message';
import {useParams} from 'react-router';
import {BlogPost} from '@app/blog/requests/use-blog-posts';
import {CreateBlogPostPayload} from './use-create-blog-post';

interface Response {
  blogPost: BlogPost;
}

export function useUpdateBlogPost() {
  const {slug} = useParams();

  return useMutation({
    mutationFn: (payload: CreateBlogPostPayload) =>
      apiClient.put<Response>(`blog-posts/${slug}`, payload).then(r => r.data),
    onError: err => showHttpErrorToast(err),
    onSuccess: async () => {
      await queryClient.invalidateQueries({queryKey: ['blogPosts']});
      await queryClient.invalidateQueries({queryKey: DatatableDataQueryKey('blog-posts')});
      toast(message('Post updated'));
    },
  });
}