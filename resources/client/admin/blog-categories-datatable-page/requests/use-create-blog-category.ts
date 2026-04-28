import {useMutation} from '@tanstack/react-query';
import {apiClient, queryClient} from '@common/http/query-client';
import {useTrans} from '@ui/i18n/use-trans';
import {BackendResponse} from '@common/http/backend-response/backend-response';
import {toast} from '@ui/toast/toast';
import {message} from '@ui/i18n/message';
import {DatatableDataQueryKey} from '@common/datatable/requests/paginated-resources';
import {onFormQueryError} from '@common/errors/on-form-query-error';
import {UseFormReturn} from 'react-hook-form';
import {BlogCategory} from '@app/blog/blog-category';

interface Response extends BackendResponse {
  blogCategory: BlogCategory;
}

export interface CreateBlogCategoryPayload {
  name: string;
  slug: string;
}

export function useCreateBlogCategory(form: UseFormReturn<CreateBlogCategoryPayload>) {
  const {trans} = useTrans();
  return useMutation({
    mutationFn: (props: CreateBlogCategoryPayload) =>
      apiClient.post<Response>('blog-categories', props).then(r => r.data),
    onSuccess: () => {
      toast(trans(message('Category created')));
      queryClient.invalidateQueries({queryKey: DatatableDataQueryKey('blog-categories')});
    },
    onError: err => onFormQueryError(err, form),
  });
}