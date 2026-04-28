import {FormTextField} from '@ui/forms/input-field/text-field/text-field';
import {Trans} from '@ui/i18n/trans';
import {Form} from '@ui/forms/form';
import {UseFormReturn} from 'react-hook-form';

interface Props {
  onSubmit: (values: any) => void;
  formId: string;
  form: UseFormReturn<any>;
}

export function CrupdateBlogCategoryForm({
  form,
  onSubmit,
  formId,
}: Props) {
  return (
    <Form id={formId} form={form} onSubmit={onSubmit}>
      <FormTextField
        name="name"
        label={<Trans message="Name" />}
        className="mb-24"
        required
        autoFocus
      />
      <FormTextField
        name="slug"
        label={<Trans message="Slug" />}
        description={<Trans message="Unique category slug." />}
        className="mb-24"
        required
      />
    </Form>
  );
}