import {PageStatus} from '@common/http/page-status';
import {PageMetaTags} from '@common/http/page-meta-tags';
import {useBlogPost} from '@app/blog/requests/use-blog-post';
import React, {Fragment, Suspense} from 'react';
import {FormProvider, useForm} from 'react-hook-form';
import {FileUploadProvider} from '@common/uploads/uploader/file-upload-provider';
import {ArticleEditorTitle} from '@common/article-editor/article-editor-title';
import {ArticleEditorStickyHeader} from '@common/article-editor/article-editor-sticky-header';
import {useNavigate} from '@common/ui/navigation/use-navigate';
import {Button} from '@ui/buttons/button';
import {FullPageLoader} from '@ui/progress/full-page-loader';
import {CreateBlogPostPayload} from './requests/use-create-blog-post';
import {useUpdateBlogPost} from './requests/use-update-blog-post';
import {Link} from 'react-router';
import {FormImageSelector} from '@common/uploads/components/image-selector';
import {Trans} from '@ui/i18n/trans';

const ArticleBodyEditor = React.lazy(
  () => import('@common/article-editor/article-body-editor'),
);

export function EditBlogPostPage() {
  const query = useBlogPost();

  return query.data ? (
    <Fragment>
      <PageMetaTags
        query={query}
        title={query.data.blogPost.meta_title || query.data.blogPost.title}
        description={query.data.blogPost.meta_description || query.data.blogPost.excerpt || ''}
      />
      <PageContent />
    </Fragment>
  ) : (
    <div className="relative h-full w-full">
      <PageStatus query={query} />
    </div>
  );
}

function PageContent() {
  const navigate = useNavigate();
  const query = useBlogPost();
  const updatePost = useUpdateBlogPost();
  const form = useForm<CreateBlogPostPayload>({
    defaultValues: {
      title: query.data?.blogPost.title,
      slug: query.data?.blogPost.slug,
      excerpt: query.data?.blogPost.excerpt || '',
      featured_image: query.data?.blogPost.featured_image || '',
      meta_title: query.data?.blogPost.meta_title || '',
      meta_description: query.data?.blogPost.meta_description || '',
      status: query.data?.blogPost.status,
    },
  });

  const handleSave = (content: string) => {
    updatePost.mutate(
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
      <ArticleBodyEditor initialContent={query.data?.blogPost.content}>
        {(content, editor) => (
          <FileUploadProvider>
            <FormProvider {...form}>
              <ArticleEditorStickyHeader
                editor={editor}
                slugPrefix="blog"
                backLink="../.."
                isLoading={updatePost.isPending}
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