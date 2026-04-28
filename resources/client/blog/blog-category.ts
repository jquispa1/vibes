export const BLOG_CATEGORY_MODEL = 'blog_category';

export interface BlogCategory {
  id: number;
  name: string;
  description?: string;
  model_type: typeof BLOG_CATEGORY_MODEL;
}