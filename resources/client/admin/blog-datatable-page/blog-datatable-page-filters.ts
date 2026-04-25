import {message} from '@ui/i18n/message';
import {
  BackendFilter,
  FilterControlType,
  FilterOperator,
} from '@common/datatable/filters/backend-filter';
import {
  createdAtFilter,
  timestampFilter,
  updatedAtFilter,
} from '@common/datatable/filters/timestamp-filters';

export const BlogDatatablePageFilters: BackendFilter[] = [
  {
    key: 'status',
    label: message('Status'),
    description: message('Whether post is draft or published'),
    defaultOperator: FilterOperator.eq,
    control: {
      type: FilterControlType.Select,
      defaultValue: 'published',
      options: [
        {key: 'published', label: message('Published'), value: 'published'},
        {key: 'draft', label: message('Draft'), value: 'draft'},
      ],
    },
  },
  timestampFilter({
    key: 'published_at',
    label: message('Published date'),
    description: message('Date post was published'),
  }),
  createdAtFilter({
    description: message('Date post was created'),
  }),
  updatedAtFilter({
    description: message('Date post was last updated'),
  }),
];