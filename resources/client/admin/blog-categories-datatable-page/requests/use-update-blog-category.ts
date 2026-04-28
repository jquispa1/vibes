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
import {CreateBlogCategoryPayload} from './use-create-blog-category';

interface Response extends BackendResponse {
  blogCategory: BlogCategory;
}

export interface UpdateBlogCategoryPayload extends CreateBlogCategoryPayload {
  id: number;
}

export function useUpdateBlogCategory(form: UseFormReturn<UpdateBlogCategoryPayload>) {
  const {trans} = useTrans();
  return useMutation({
    mutationFn: (props: UpdateBlogCategoryPayload) => updateBlogCategory(props),
    onSuccess: () => {
      toast(trans(message('Category updated')));
      queryClient.invalidateQueries({queryKey: DatatableDataQueryKey('blog-categories')});
    },
    onError: err => onFormQueryError(err, form),
  });
}

function updateBlogCategory({id, ...payload}: UpdateBlogCategoryPayload): Promise<Response> {
  return apiClient.put(`blog-categories/${id}`, payload).then(r => r.data);
}