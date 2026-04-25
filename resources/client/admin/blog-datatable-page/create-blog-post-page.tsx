import React, {Suspense} from 'react';
import {Link} from 'react-router';
import {FormProvider, useForm} from 'react-hook-form';
import {FileUploadProvider} from '@common/uploads/uploader/file-upload-provider';
import {ArticleEditorTitle} from '@common/article-editor/article-editor-title';
import {ArticleEditorStickyHeader} from '@common/article-editor/article-editor-sticky-header';
import {useNavigate} from '@common/ui/navigation/use-navigate';
import {Button} from '@ui/buttons/button';
import {FullPageLoader} from '@ui/progress/full-page-loader';
import {CreateBlogPostPayload, useCreateBlogPost} from './requests/use-create-blog-post';
import {FormImageSelector} from '@common/uploads/components/image-selector';
import {Trans} from '@ui/i18n/trans';
import {FormTextField} from '@ui/forms/input-field/text-field/text-field';
import {FormSelect} from '@ui/forms/select/select';
import {Item} from '@ui/forms/listbox/item';

const ArticleBodyEditor = React.lazy(
  () => import('@common/article-editor/article-body-editor'),
);

export function CreateBlogPostPage() {
  const navigate = useNavigate();
  const createPost = useCreateBlogPost();
  const form = useForm<CreateBlogPostPayload>({
    defaultValues: {
      title: '',
      status: 'draft',
    },
  });

  const handleSave = (content: string) => {
    createPost.mutate(
      {
        ...form.getValues(),
        content,
      },
      {
        onSuccess: () => navigate('../', {relative: 'path'}),
      },
    );
  };

  return (
    <Suspense fallback={<FullPageLoader />}>
      <ArticleBodyEditor>
        {(content, editor) => (
          <FileUploadProvider>
            <FormProvider {...form}>
              <ArticleEditorStickyHeader
                editor={editor}
                slugPrefix="blog"
                backLink="../"
                isLoading={createPost.isPending}
                onSave={handleSave}
                saveButton={
                  form.watch('slug') ? (
                    <Button
                      elementType={Link}
                      to={`/blog/${form.watch('slug')}`}
                      target="_blank"
                      variant="outline"
                      size="sm"
                    >
                      Preview
                    </Button>
                  ) : undefined
                }
              />
              <div className="mx-20">
                <div className="mx-auto max-w-3xl">
                  <ArticleEditorTitle />
                  <FormTextField
                    className="mb-24"
                    name="slug"
                    label={<Trans message="Slug" />}
                  />
                  <FormImageSelector
                    className="mb-24"
                    name="featured_image"
                    diskPrefix="blog_media"
                    variant="input"
                    label={<Trans message="Featured image" />}
                  />
                  <FormTextField
                    className="mb-24"
                    name="excerpt"
                    label={<Trans message="Excerpt" />}
                    inputElementType="textarea"
                    rows={4}
                  />
                  <FormTextField
                    className="mb-24"
                    name="meta_title"
                    label={<Trans message="Meta title" />}
                  />
                  <FormTextField
                    className="mb-24"
                    name="meta_description"
                    label={<Trans message="Meta description" />}
                    inputElementType="textarea"
                    rows={4}
                  />
                  <FormSelect
                    className="mb-24"
                    name="status"
                    selectionMode="single"
                    label={<Trans message="Status" />}
                  >
                    <Item value="draft">
                      <Trans message="Draft" />
                    </Item>
                    <Item value="published">
                      <Trans message="Published" />
                    </Item>
                  </FormSelect>
                  <div className="prose dark:prose-invert">{content}</div>
                </div>
              </div>
            </FormProvider>
          </FileUploadProvider>
        )}
      </ArticleBodyEditor>
    </Suspense>
  );
}