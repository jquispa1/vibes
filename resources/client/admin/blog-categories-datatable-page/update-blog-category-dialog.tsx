import {useDialogContext} from '@ui/overlays/dialog/dialog-context';
import {DialogBody} from '@ui/overlays/dialog/dialog-body';
import {DialogFooter} from '@ui/overlays/dialog/dialog-footer';
import {Dialog} from '@ui/overlays/dialog/dialog';
import {DialogHeader} from '@ui/overlays/dialog/dialog-header';
import {useForm} from 'react-hook-form';
import {Trans} from '@ui/i18n/trans';
import {Button} from '@ui/buttons/button';
import {CrupdateBlogCategoryForm} from './crupdate-blog-category-form';
import {
  UpdateBlogCategoryPayload,
  useUpdateBlogCategory,
} from './requests/use-update-blog-category';
import {BlogCategory} from '@app/blog/blog-category';

interface Props {
  blogCategory: BlogCategory;
}

export function UpdateBlogCategoryDialog({blogCategory}: Props) {
  const {close, formId} = useDialogContext();
  const form = useForm<UpdateBlogCategoryPayload>({
    defaultValues: {
      id: blogCategory.id,
      name: blogCategory.name,
      slug: blogCategory.slug,
    },
  });
  const updateBlogCategory = useUpdateBlogCategory(form);

  return (
    <Dialog>
      <DialogHeader>
        <Trans message="Update “:name“ category" values={{name: blogCategory.name}} />
      </DialogHeader>
      <DialogBody>
        <CrupdateBlogCategoryForm
          formId={formId}
          form={form}
          onSubmit={values => {
            updateBlogCategory.mutate(values, {onSuccess: () => close()});
          }}
        />
      </DialogBody>
      <DialogFooter>
        <Button onClick={() => close()}>
          <Trans message="Cancel" />
        </Button>
        <Button
          form={formId}
          disabled={updateBlogCategory.isPending}
          variant="flat"
          color="primary"
          type="submit"
        >
          <Trans message="Save" />
        </Button>
      </DialogFooter>
    </Dialog>
  );
}