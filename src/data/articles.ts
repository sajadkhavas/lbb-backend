import { blogPosts } from '@/data/blog-posts';

export interface Article {
  id: string;
  slug: string;
  title: string;
  excerpt: string;
  author: string;
  publishDate: string;
  categories: string[];
  tags: string[];
}

const mapCategory = (postCategory: string): string => {
  if (postCategory.includes('راهنمای خرید')) return 'buying-guide';
  if (postCategory.includes('ایمنی') || postCategory.includes('استاندارد')) return 'industrial-safety';
  if (postCategory.includes('فناوری') || postCategory.includes('تحلیل بازار')) return 'market-trends';
  return 'technical';
};

const slugify = (value: string) =>
  value
    .toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .trim()
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-');

export const articles: Article[] = blogPosts.map((post) => ({
  id: post.id,
  slug: slugify(post.title) || `article-${post.id}`,
  title: post.title,
  excerpt: post.excerpt,
  author: post.author,
  publishDate: post.date,
  categories: [...new Set([mapCategory(post.category), ...post.productCategories])],
  tags: [...new Set([post.category, ...post.productTypes])],
}));
