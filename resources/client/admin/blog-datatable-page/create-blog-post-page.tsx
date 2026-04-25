import React, {Suspense, useState} from 'react';
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
import {DialogTrigger} from '@ui/overlays/dialog/dialog-trigger';
import {Dialog} from '@ui/overlays/dialog/dialog';
import {DialogHeader} from '@ui/overlays/dialog/dialog-header';
import {DialogBody} from '@ui/overlays/dialog/dialog-body';
import {DialogFooter} from '@ui/overlays/dialog/dialog-footer';
import {SettingsIcon} from '@ui/icons/material/Settings';
import {IconButton} from '@ui/buttons/icon-button';

import {useDialogContext} from '@ui/overlays/dialog/dialog-context';

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
        onSuccess: () => navigate('/admin/blog'),
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
                  <>
                    <BlogPostSettingsDialog />
                    {form.watch('slug') ? (
                      <Button
                        elementType={Link}
                        to={`/blog/${form.watch('slug')}`}
                        target="_blank"
                        variant="outline"
                        size="sm"
                      >
                        Preview
                      </Button>
                    ) : undefined}
                  </>
                }
              />
              <div className="mx-20">
                <div className="mx-auto max-w-3xl">
                  <ArticleEditorTitle />
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

function BlogPostSettingsDialog() {
  return (
    <DialogTrigger type="modal">
      <IconButton variant="outline" size="sm">
        <SettingsIcon />
      </IconButton>
      <BlogPostSettingsDialogContent />
    </DialogTrigger>
  );
}

function BlogPostSettingsDialogContent() {
  const {close} = useDialogContext();
  return (
    <Dialog size="lg">
      <DialogHeader>
        <Trans message="Post settings" />
      </DialogHeader>
      <DialogBody>
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
      </DialogBody>
      <DialogFooter>
        <Button
          variant="flat"
          color="primary"
          onClick={() => close()}
        >
          <Trans message="Done" />
        </Button>
      </DialogFooter>
    </Dialog>
  );
}