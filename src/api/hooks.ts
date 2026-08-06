import { useQuery, useMutation } from '@tanstack/react-query';
import {
  productService,
  categoryService,
  brandService,
  blogService,
  rfqService,
  contactService,
  searchService,
  settingsService,
  sliderService,
  newsletterService,
  pageService,
  navigationService,
} from './services';
import type { ProductsParams, BlogParams } from './types';

// ─── Query Keys ───────────────────────────────────────────────────────────────

export const queryKeys = {
  products: (params?: ProductsParams) => ['products', params] as const,
  product: (slug: string) => ['product', slug] as const,
  featuredProducts: () => ['products', 'featured'] as const,
  similarProducts: (slug: string) => ['products', slug, 'similar'] as const,
  categories: () => ['categories'] as const,
  category: (slug: string) => ['category', slug] as const,
  categoryProducts: (slug: string, params?: ProductsParams) => ['category', slug, 'products', params] as const,
  brands: () => ['brands'] as const,
  brand: (slug: string) => ['brand', slug] as const,
  brandProducts: (slug: string) => ['brand', slug, 'products'] as const,
  blog: (params?: BlogParams) => ['blog', params] as const,
  blogPost: (slug: string) => ['blog', slug] as const,
  latestBlog: () => ['blog', 'latest'] as const,
  search: (q: string) => ['search', q] as const,
  settings: () => ['settings'] as const,
  sliders: () => ['sliders'] as const,
  sitePage: (slug: string) => ['site-page', slug] as const,
  navigation: () => ['navigation'] as const,
};

const STALE = 5 * 60 * 1000; // 5 دقیقه

// ─── Products ─────────────────────────────────────────────────────────────────

/**
 * لیست paginated محصولات
 * برگشتی: { data: ApiProduct[], meta: PaginationMeta }
 */
export function useProducts(params?: ProductsParams) {
  return useQuery({
    queryKey: queryKeys.products(params),
    queryFn: () => productService.getAll(params),
    staleTime: STALE,
  });
}

export function useProduct(slug: string) {
  return useQuery({
    queryKey: queryKeys.product(slug),
    queryFn: () => productService.getBySlug(slug),
    enabled: !!slug,
    staleTime: STALE,
  });
}

export function useFeaturedProducts() {
  return useQuery({
    queryKey: queryKeys.featuredProducts(),
    queryFn: productService.getFeatured,
    staleTime: STALE,
  });
}

export function useSimilarProducts(slug: string) {
  return useQuery({
    queryKey: queryKeys.similarProducts(slug),
    queryFn: () => productService.getSimilar(slug),
    enabled: !!slug,
    staleTime: STALE,
  });
}

// ─── Categories ───────────────────────────────────────────────────────────────

export function useCategories() {
  return useQuery({
    queryKey: queryKeys.categories(),
    queryFn: categoryService.getAll,
    staleTime: STALE,
  });
}

export function useCategory(slug: string) {
  return useQuery({
    queryKey: queryKeys.category(slug),
    queryFn: () => categoryService.getBySlug(slug),
    enabled: !!slug,
    staleTime: STALE,
  });
}

/**
 * محصولات یک دسته‌بندی — paginated
 * برگشتی: { data: ApiProduct[], meta: PaginationMeta }
 */
export function useCategoryProducts(slug: string, params?: ProductsParams) {
  return useQuery({
    queryKey: queryKeys.categoryProducts(slug, params),
    queryFn: () => categoryService.getProducts(slug, params),
    enabled: !!slug,
    staleTime: STALE,
  });
}

// ─── Brands ───────────────────────────────────────────────────────────────────

export function useBrands() {
  return useQuery({
    queryKey: queryKeys.brands(),
    queryFn: brandService.getAll,
    staleTime: STALE,
  });
}

export function useBrand(slug: string) {
  return useQuery({
    queryKey: queryKeys.brand(slug),
    queryFn: () => brandService.getBySlug(slug),
    enabled: !!slug,
    staleTime: STALE,
  });
}

export function useBrandProducts(slug: string) {
  return useQuery({
    queryKey: queryKeys.brandProducts(slug),
    queryFn: () => brandService.getProducts(slug),
    enabled: !!slug,
    staleTime: STALE,
  });
}

// ─── Blog ─────────────────────────────────────────────────────────────────────

/**
 * لیست paginated پست‌های بلاگ
 * برگشتی: { data: ApiBlogPost[], meta: PaginationMeta }
 */
export function useBlogPosts(params?: BlogParams) {
  return useQuery({
    queryKey: queryKeys.blog(params),
    queryFn: () => blogService.getAll(params),
    staleTime: STALE,
  });
}

export function useBlogPost(slug: string) {
  return useQuery({
    queryKey: queryKeys.blogPost(slug),
    queryFn: () => blogService.getBySlug(slug),
    enabled: !!slug,
    staleTime: STALE,
  });
}

export function useLatestBlogPosts() {
  return useQuery({
    queryKey: queryKeys.latestBlog(),
    queryFn: blogService.getLatest,
    staleTime: STALE,
  });
}

// ─── Search ───────────────────────────────────────────────────────────────────

export function useSearch(query: string) {
  return useQuery({
    queryKey: queryKeys.search(query),
    queryFn: () => searchService.search(query),
    enabled: query.length > 1,
    staleTime: 60_000,
  });
}

// ─── Mutations ────────────────────────────────────────────────────────────────

export function useSubmitRFQ() {
  return useMutation({ mutationFn: (payload: any) => rfqService.submit(payload) });
}

export function useSubmitContact() {
  return useMutation({ mutationFn: (payload: any) => contactService.submit(payload) });
}

export function useSubscribeNewsletter() {
  return useMutation({ mutationFn: (payload: any) => newsletterService.subscribe(payload) });
}

// ─── Site Settings ────────────────────────────────────────────────────────────

export function useSiteSettings() {
  return useQuery({
    queryKey: queryKeys.settings(),
    queryFn: settingsService.get,
    staleTime: 10 * 60 * 1000,
  });
}

export const useSettings = useSiteSettings;

// ─── Sliders ──────────────────────────────────────────────────────────────────

export function useSliders() {
  return useQuery({
    queryKey: queryKeys.sliders(),
    queryFn: sliderService.getAll,
    staleTime: STALE,
  });
}

// ─── Site Pages ───────────────────────────────────────────────────────────────

export function useSitePage(slug: string) {
  return useQuery({
    queryKey: queryKeys.sitePage(slug),
    queryFn: () => pageService.getBySlug(slug),
    enabled: !!slug,
    staleTime: STALE,
  });
}

// ─── Navigation ───────────────────────────────────────────────────────────────

export function useNavigation() {
  return useQuery({
    queryKey: queryKeys.navigation(),
    queryFn: navigationService.getAll,
    staleTime: 10 * 60 * 1000,
  });
}
