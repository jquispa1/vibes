import {RouteObject} from 'react-router';
import {ContactUsPage} from '@common/contact/contact-us-page';
import {CustomPageLayout} from '@common/custom-page/custom-page-layout';
import {NotFoundPage} from '@common/ui/not-found-page/not-found-page';
import React from 'react';

const lazyRoute = async (
  cmp: keyof typeof import('@app/blog/routes/blog-routes.lazy'),
) => {
  const exports = await import('@app/blog/routes/blog-routes.lazy');
  return {
    Component: exports[cmp],
  };
};

export const commonRoutes: RouteObject[] = [
  {
    path: 'blog',
    lazy: () => lazyRoute('BlogPage'),
  },
  {
    path: 'spotify/import',
    lazy: () => lazyRoute('SpotifyImportPage'),
  },
  {
    path: 'blog/category/:slug',
    lazy: () => lazyRoute('BlogCategoryPage'),
  },
  {
    path: 'blog/:slug',
    lazy: () => lazyRoute('BlogPostPage'),
  },
  {
    path: 'contact',
    element: <ContactUsPage />,
  },
  {
    path: 'pages/:pageSlug',
    element: <CustomPageLayout />,
  },
  {
    path: 'pages/:pageId/:pageSlug',
    element: <CustomPageLayout />,
  },
  {
    path: '404',
    element: <NotFoundPage />,
  },
];
