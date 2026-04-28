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
  CreateBlogCategoryPayload,
  useCreateBlogCategory,
} from './requests/use-create-blog-category';

export function CreateBlogCategoryDialog() {
  const {close, formId} = useDialogContext();
  const form = useForm<CreateBlogCategoryPayload>();
  const createBlogCategory = useCreateBlogCategory(form);

  return (
    <Dialog>
      <DialogHeader>
        <Trans message="Create new category" />
      </DialogHeader>
      <DialogBody>
        <CrupdateBlogCategoryForm
          formId={formId}
          form={form}
          onSubmit={values => {
            createBlogCategory.mutate(values, {onSuccess: () => close()});
          }}
        />
      </DialogBody>
      <DialogFooter>
        <Button onClick={() => close()}>
          <Trans message="Cancel" />
        </Button>
        <Button
          form={formId}
          disabled={createBlogCategory.isPending}
          variant="flat"
          color="primary"
          type="submit"
        >
          <Trans message="Create" />
        </Button>
      </DialogFooter>
    </Dialog>
  );
}