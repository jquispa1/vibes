export const BLOG_CATEGORY_MODEL = 'blog_category';

export interface BlogCategory {
  id: number;
  name: string;
  slug: string;
  model_type: typeof BLOG_CATEGORY_MODEL;
}