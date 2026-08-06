import { useState, useEffect, useCallback } from 'react';
import { productService } from '@/api/services';

export interface ApiProduct {
  id: string;
  slug: string;
  name: string;
  model: string;
  type: string;
  category: string;
  categoryName: string;
  brand: string;
  brandSlug: string;
  country: string;
  usage: string[];
  priceRange: string;
  applications: string[];
  inStock: boolean;
  isFeatured: boolean;
  description: string;
  longDescription: string | null;
  excerpt: string | null;
  image: string | null;
  gallery: string[];
  specs: Record<string, string>;
  viewCount: number;
  rfqCount: number;
  seo: {
    title: string;
    description: string;
    keywords: string | null;
    schema: Record<string, unknown>;
  };
}

export interface UseApiProductsOptions {
  category?: string;
  brand?: string;
  type?: string;
  country?: string;
  price_range?: string;
  in_stock?: boolean;
  usage?: string;
  search?: string;
  per_page?: number;
}

export function useApiProducts(options: UseApiProductsOptions = {}) {
  const [products, setProducts] = useState<ApiProduct[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [total, setTotal] = useState(0);
  const [allProducts, setAllProducts] = useState<ApiProduct[]>([]);
  const [allLoaded, setAllLoaded] = useState(false);

  // Load all pages
  const loadAll = useCallback(async () => {
    try {
      setLoading(true);
      setError(null);

      const params: Record<string, string | number | boolean> = {
        per_page: 100,
      };

      if (options.category) params.category = options.category;
      if (options.brand) params.brand = options.brand;
      if (options.type) params.type = options.type;
      if (options.country) params.country = options.country;
      if (options.price_range) params.price_range = options.price_range;
      if (options.in_stock !== undefined) params.in_stock = options.in_stock;
      if (options.usage) params.usage = options.usage;
      if (options.search) params.search = options.search;

      const data = await productService.getAll(params);
      const items: ApiProduct[] = Array.isArray(data) ? data : (data?.data ?? []);
      setAllProducts(items);
      setProducts(items);
      setTotal(items.length);
      setAllLoaded(true);
    } catch (err) {
      setError('خطا در بارگذاری محصولات از API');
      console.error('API Error:', err);
    } finally {
      setLoading(false);
    }
  }, [
    options.category,
    options.brand,
    options.type,
    options.country,
    options.price_range,
    options.in_stock,
    options.usage,
    options.search,
  ]);

  useEffect(() => {
    loadAll();
  }, [loadAll]);

  return { products, allProducts, loading, error, total, allLoaded, reload: loadAll };
}

// Convert API product to local Product type for compatibility
export function apiProductToLocal(p: ApiProduct) {
  return {
    id: p.id,
    slug: p.slug,
    name: p.name,
    model: p.model,
    type: p.type,
    category: p.category,
    brand: p.brand,
    country: p.country,
    usage: p.usage as any[],
    priceRange: p.priceRange as any,
    inStock: p.inStock,
    isFeatured: p.isFeatured,
    description: p.description,
    longDescription: p.longDescription,
    excerpt: p.excerpt,
    image: p.image,
    gallery: p.gallery,
    specs: p.specs,
    applications: p.applications,
    viewCount: p.viewCount,
    rfqCount: p.rfqCount,
    seo: p.seo,
  };
}
