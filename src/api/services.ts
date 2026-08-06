import { api } from './client';
import type { ProductsParams, BlogParams, PaginatedResult, PaginationMeta } from './types';

// ─── unwrap helpers ───────────────────────────────────────────────────────────

/**
 * برای endpoint‌هایی که یک آیتم برمی‌گردونن (single resource)
 * بکند: { data: {...} }
 */
const unwrapSingle = (response: any) => response?.data ?? response;

/**
 * برای endpoint‌هایی که لیست بدون pagination برمی‌گردونن
 * بکند: { data: [...] }
 */
const unwrapList = (response: any): any[] => {
  if (Array.isArray(response)) return response;
  if (Array.isArray(response?.data)) return response.data;
  return [];
};

/**
 * برای endpoint‌هایی که paginated هستن
 * بکند: { data: [...], links: {...}, meta: { current_page, last_page, per_page, total, ... } }
 */
const unwrapPaginated = <T>(response: any): PaginatedResult<T> => {
  return {
    data: Array.isArray(response?.data) ? response.data : [],
    meta: response?.meta ?? {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
      from: null,
      to: null,
    } as PaginationMeta,
  };
};

// ─── isApiConfigured ─────────────────────────────────────────────────────────

export function isApiConfigured(): boolean {
  return !!import.meta.env.VITE_API_BASE_URL;
}

// ─── productService ───────────────────────────────────────────────────────────

export const productService = {
  // لیست paginated: { data: [], meta: {} }
  getAll: (params?: ProductsParams) =>
    api.get('/products', params as any).then(unwrapPaginated),

  // آیتم منفرد: { data: {} }
  getBySlug: (slug: string) =>
    api.get(`/products/${slug}`).then(unwrapSingle),

  // لیست ساده (بدون pagination)
  getFeatured: () =>
    api.get('/products/featured').then(unwrapList),

  // لیست ساده
  getSimilar: (slug: string) =>
    api.get(`/products/${slug}/similar`).then(unwrapList),
};

// ─── categoryService ──────────────────────────────────────────────────────────

export const categoryService = {
  // لیست ساده (cache شده در بکند)
  getAll: () =>
    api.get('/categories').then(unwrapList),

  // آیتم منفرد
  getBySlug: (slug: string) =>
    api.get(`/categories/${slug}`).then(unwrapSingle),

  // لیست paginated
  getProducts: (slug: string, params?: ProductsParams) =>
    api.get(`/categories/${slug}/products`, params as any).then(unwrapPaginated),

  // لیست ساده
  getSubcategories: (slug: string) =>
    api.get(`/categories/${slug}/subcategories`).then(unwrapList),
};

// ─── brandService ─────────────────────────────────────────────────────────────

export const brandService = {
  // لیست ساده (cache شده در بکند)
  getAll: () =>
    api.get('/brands').then(unwrapList),

  // آیتم منفرد
  getBySlug: (slug: string) =>
    api.get(`/brands/${slug}`).then(unwrapSingle),

  // لیست paginated
  getProducts: (slug: string, params?: ProductsParams) =>
    api.get(`/brands/${slug}/products`, params as any).then(unwrapPaginated),
};

// ─── blogService ──────────────────────────────────────────────────────────────

export const blogService = {
  // لیست paginated
  getAll: (params?: BlogParams) =>
    api.get('/blog', params as any).then(unwrapPaginated),

  // آیتم منفرد
  getBySlug: (slug: string) =>
    api.get(`/blog/${slug}`).then(unwrapSingle),

  // لیست ساده (3 تا آخرین)
  getLatest: () =>
    api.get('/blog/latest').then(unwrapList),
};

// ─── rfqService ───────────────────────────────────────────────────────────────

export const rfqService = {
  submit: (payload: any) => api.post('/rfq', payload),
  getByReference: (ref: string) => api.get(`/rfq/${ref}`).then(unwrapSingle),
};

// ─── contactService ───────────────────────────────────────────────────────────

export const contactService = {
  submit: (payload: any) => api.post('/contact', payload),
};

// ─── searchService ────────────────────────────────────────────────────────────

export const searchService = {
  search: (query: string) => api.get('/search', { q: query } as any).then(unwrapSingle),
};

// ─── authService ──────────────────────────────────────────────────────────────
// ⚠️ نکته: این routes هنوز در بکند تعریف نشدن — مشکل ۲ را ببینید

export const authService = {
  login: (payload: any) => api.post('/auth/login', payload),
  logout: () => api.post('/auth/logout'),
  me: () => api.get('/auth/me').then(unwrapSingle),
};

// ─── settingsService ──────────────────────────────────────────────────────────

export const settingsService = {
  get: () => api.get('/settings').then(unwrapSingle),
};

// ─── sliderService ────────────────────────────────────────────────────────────

export const sliderService = {
  getAll: () => api.get('/sliders').then(unwrapList),
};

// ─── newsletterService ────────────────────────────────────────────────────────

export const newsletterService = {
  subscribe: (payload: any) => api.post('/newsletter', payload),
};

// ─── pageService ──────────────────────────────────────────────────────────────

export const pageService = {
  getBySlug: (slug: string) => api.get(`/pages/${slug}`).then(unwrapSingle),
};

// ─── navigationService ────────────────────────────────────────────────────────

export const navigationService = {
  getAll: () => api.get('/navigation').then(unwrapList),
};

// ─── Named exports (backward compatibility) ───────────────────────────────────

export const fetchProducts = (params?: ProductsParams) => productService.getAll(params);
export const fetchProduct = (slug: string) => productService.getBySlug(slug);
export const fetchFeaturedProducts = () => productService.getFeatured();
export const fetchSimilarProducts = (slug: string) => productService.getSimilar(slug);
export const fetchCategories = () => categoryService.getAll();
export const fetchCategory = (slug: string) => categoryService.getBySlug(slug);
export const fetchCategoryProducts = (slug: string, params?: ProductsParams) => categoryService.getProducts(slug, params);
export const fetchBrands = () => brandService.getAll();
export const fetchBrand = (slug: string) => brandService.getBySlug(slug);
export const fetchBrandProducts = (slug: string) => brandService.getProducts(slug);
export const fetchBlogPosts = (params?: BlogParams) => blogService.getAll(params);
export const fetchBlogPost = (slug: string) => blogService.getBySlug(slug);
export const fetchLatestBlogPosts = () => blogService.getLatest();
export const fetchSettings = () => settingsService.get();
export const fetchSliders = () => sliderService.getAll();
export const fetchNavigation = () => navigationService.getAll();
export const fetchSitePage = (slug: string) => pageService.getBySlug(slug);
export const submitContactForm = (payload: any) => contactService.submit(payload);
export const submitNewsletterForm = (payload: any) => newsletterService.subscribe(payload);
export const submitRFQ = (payload: any) => rfqService.submit(payload);
