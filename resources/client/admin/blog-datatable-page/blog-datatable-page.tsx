import React, {Fragment} from 'react';
import {Link} from 'react-router';
import {ColumnConfig} from '@common/datatable/column-config';
import {DataTablePage} from '@common/datatable/page/data-table-page';
import {DataTableEmptyStateMessage} from '@common/datatable/page/data-table-emty-state-message';
import {DeleteSelectedItemsAction} from '@common/datatable/page/delete-selected-items-action';
import {DataTableAddItemButton} from '@common/datatable/data-table-add-item-button';
import {Trans} from '@ui/i18n/trans';
import {FormattedDate} from '@ui/i18n/formatted-date';
import {FormattedNumber} from '@ui/i18n/formatted-number';
import {IconButton} from '@ui/buttons/icon-button';
import {EditIcon} from '@ui/icons/material/Edit';
import {OpenInNewIcon} from '@ui/icons/material/OpenInNew';
import {BlogPost} from '@app/blog/requests/use-blog-posts';
import articlesSvg from '@common/admin/custom-pages/articles.svg';
import {BlogDatatablePageFilters} from '@app/admin/blog-datatable-page/blog-datatable-page-filters';

const columns: ColumnConfig<BlogPost & {author?: {name: string}}>[] = [
  {
    key: 'title',
    allowsSorting: true,
    visibleInMode: 'all',
    width: 'flex-3 min-w-200',
    header: () => <Trans message="Title" />,
    body: post => <span className="font-medium">{post.title}</span>,
  },
  {
    key: 'slug',
    allowsSorting: true,
    header: () => <Trans message="Slug" />,
    body: post => post.slug,
  },
  {
    key: 'author',
    header: () => <Trans message="Author" />,
    body: post => post.author?.name || '-',
  },
  {
    key: 'status',
    header: () => <Trans message="Status" />,
    body: post => post.status,
  },
  {
    key: 'published_at',
    allowsSorting: true,
    header: () => <Trans message="Published" />,
    body: post =>
      post.published_at ? <FormattedDate date={post.published_at} /> : '-',
  },
  {
    key: 'id',
    header: () => <Trans message="ID" />,
    body: post => <FormattedNumber value={post.id} />,
  },
  {
    key: 'actions',
    header: () => <Trans message="Actions" />,
    hideHeader: true,
    align: 'end',
    visibleInMode: 'all',
    width: 'w-84 flex-shrink-0',
    body: post => (
      <div className="flex items-center gap-4">
        <IconButton
          size="md"
          className="text-muted"
          to={`/blog/${post.slug}`}
          elementType={Link}
          target="_blank"
        >
          <OpenInNewIcon />
        </IconButton>
        <IconButton
          size="md"
          className="text-muted"
          to={`${post.slug}/edit`}
          elementType={Link}
        >
          <EditIcon />
        </IconButton>
      </div>
    ),
  },
];

export function BlogDatatablePage() {
  return (
    <DataTablePage
      endpoint="blog-posts"
      title={<Trans message="Blog posts" />}
      columns={columns}
      filters={BlogDatatablePageFilters}
      queryParams={{with: 'author'}}
      actions={<Actions />}
      selectedActions={<DeleteSelectedItemsAction />}
      emptyStateMessage={
        <DataTableEmptyStateMessage
          image={articlesSvg}
          title={<Trans message="No blog posts have been created yet" />}
          filteringTitle={<Trans message="No matching blog posts" />}
        />
      }
    />
  );
}

function Actions() {
  return (
    <Fragment>
      <DataTableAddItemButton elementType={Link} to="new">
        <Trans message="New post" />
      </DataTableAddItemButton>
    </Fragment>
  );
}