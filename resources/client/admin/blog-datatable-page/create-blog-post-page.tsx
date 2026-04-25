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

const ArticleBodyEditor = React.lazy(
  () => import('@common/article-editor/article-body-editor'),
);

export function CreateBlogPostPage() {
  const navigate = useNavigate();
  const createPost = useCreateBlogPost();
  const form = useForm<CreateBlogPostPayload>({
    defaultValues: {
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
                <div className="mx-auto flex max-w-3xl flex-col gap-6">
                  <ArticleEditorTitle />
                  <input
                    className="w-full rounded-xl border border-border bg-background px-4 py-3"
                    placeholder="Slug"
                    {...form.register('slug')}
                  />
                  <FormImageSelector
                    name="featured_image"
                    diskPrefix="blog_media"
                    variant="input"
                    label={<Trans message="Featured image" />}
                  />
                  <textarea
                    className="min-h-28 w-full rounded-xl border border-border bg-background px-4 py-3"
                    placeholder="Excerpt"
                    {...form.register('excerpt')}
                  />
                  <input
                    className="w-full rounded-xl border border-border bg-background px-4 py-3"
                    placeholder="Meta title"
                    {...form.register('meta_title')}
                  />
                  <textarea
                    className="min-h-28 w-full rounded-xl border border-border bg-background px-4 py-3"
                    placeholder="Meta description"
                    {...form.register('meta_description')}
                  />
                  <div className="w-full rounded-xl border border-border bg-background px-4 py-3">
                    <label className="mb-2 block text-sm font-medium">Status</label>
                    <select
                      className="w-full bg-transparent outline-none"
                      {...form.register('status')}
                    >
                      <option value="draft">Draft</option>
                      <option value="published">Published</option>
                    </select>
                  </div>
                  {content}
                </div>
              </div>
            </FormProvider>
          </FileUploadProvider>
        )}
      </ArticleBodyEditor>
    </Suspense>
  );
}