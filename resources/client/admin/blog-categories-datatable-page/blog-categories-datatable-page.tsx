import React, {Fragment} from 'react';
import {ColumnConfig} from '@common/datatable/column-config';
import {DataTablePage} from '@common/datatable/page/data-table-page';
import {DataTableEmptyStateMessage} from '@common/datatable/page/data-table-emty-state-message';
import {DeleteSelectedItemsAction} from '@common/datatable/page/delete-selected-items-action';
import {DataTableAddItemButton} from '@common/datatable/data-table-add-item-button';
import {Trans} from '@ui/i18n/trans';
import {FormattedNumber} from '@ui/i18n/formatted-number';
import {IconButton} from '@ui/buttons/icon-button';
import {EditIcon} from '@ui/icons/material/Edit';
import {DialogTrigger} from '@ui/overlays/dialog/dialog-trigger';
import articlesSvg from '@common/admin/custom-pages/articles.svg';
import {BlogCategory} from '@app/blog/blog-category';
import {CreateBlogCategoryDialog} from './create-blog-category-dialog';
import {UpdateBlogCategoryDialog} from './update-blog-category-dialog';

const columns: ColumnConfig<BlogCategory & {posts_count?: number}>[] = [
  {
    key: 'name',
    allowsSorting: true,
    visibleInMode: 'all',
    width: 'flex-3 min-w-200',
    header: () => <Trans message="Name" />,
    body: category => <span className="font-medium">{category.name}</span>,
  },
  {
    key: 'slug',
    allowsSorting: true,
    header: () => <Trans message="Slug" />,
    body: category => category.slug,
  },
  {
    key: 'posts_count',
    header: () => <Trans message="Posts" />,
    body: category => <FormattedNumber value={category.posts_count || 0} />,
  },
  {
    key: 'id',
    header: () => <Trans message="ID" />,
    body: category => <FormattedNumber value={category.id} />,
  },
  {
    key: 'actions',
    header: () => <Trans message="Actions" />,
    hideHeader: true,
    align: 'end',
    visibleInMode: 'all',
    width: 'w-42 flex-shrink-0',
    body: category => (
      <DialogTrigger type="modal">
        <IconButton size="md" className="text-muted">
          <EditIcon />
        </IconButton>
        <UpdateBlogCategoryDialog blogCategory={category} />
      </DialogTrigger>
    ),
  },
];

export function BlogCategoriesDatatablePage() {
  return (
    <DataTablePage
      endpoint="blog-categories"
      title={<Trans message="Blog categories" />}
      columns={columns}
      queryParams={{withCount: 'blogPosts'}}
      actions={<Actions />}
      selectedActions={<DeleteSelectedItemsAction />}
      emptyStateMessage={
        <DataTableEmptyStateMessage
          image={articlesSvg}
          title={<Trans message="No blog categories have been created yet" />}
          filteringTitle={<Trans message="No matching blog categories" />}
        />
      }
    />
  );
}

function Actions() {
  return (
    <Fragment>
      <DialogTrigger type="modal">
        <DataTableAddItemButton>
          <Trans message="New category" />
        </DataTableAddItemButton>
        <CreateBlogCategoryDialog />
      </DialogTrigger>
    </Fragment>
  );
}