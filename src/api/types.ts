/**
 * ──── API Type Definitions ────
 * این فایل با دقت با Resources بکند match شده:
 *   ProductResource, CategoryResource, BrandResource,
 *   BlogPostResource, SubcategoryResource
 */

// ─── Product ──────────────────────────────────────────────────────────────────

export interface ApiProduct {
  // شناسه‌ها
  id: string;           // = slug (بکند: 'id' => $this->slug)
  slug: string;

  // اطلاعات اصلی
  name: string;
  model: string;
  type: string | null;          // subcategory slug
  category: string | null;      // category slug
  categoryName: string | null;  // category name
  brand: string | null;         // brand name
  brandSlug: string | null;     // brand slug (بکند: 'brandSlug' => $this->brand?->slug)

  // محتوا
  description: string | null;
  longDescription: string | null;
  excerpt: string | null;       // بکند: 'excerpt' (نه short_description)

  // رسانه
  image: string | null;
  gallery: string[];

  // ویژگی‌ها
  country: string | null;
  usage: string[];
  priceRange: string | null;
  applications: string[];
  specs: ApiProductSpecs;

  // وضعیت (بکند: camelCase)
  inStock: boolean;       // بکند: 'inStock' => (bool) $this->in_stock
  isFeatured: boolean;    // بکند: 'isFeatured' => (bool) $this->is_featured

  // آمار
  viewCount: number;
  rfqCount: number;

  // SEO
  seo: ApiProductSEO;
}

export interface ApiProductSpecs {
  range?: string;
  accuracy?: string;
  resolution?: string;
  pressure?: string;
  flow_rate?: string;
  purity?: string;
  gas_type?: string;
  voltage?: string;
  protocol?: string;
  io_count?: string;
  certification?: string;
  [key: string]: string | undefined;
}

export interface ApiProductSEO {
  title: string | null;
  description: string | null;
  keywords: string | null;
  schema: Record<string, unknown> | null;
}

// ─── Category ─────────────────────────────────────────────────────────────────

export interface ApiCategory {
  id: string;           // = slug (بکند: 'id' => $this->slug)
  slug: string;
  name: string;
  label: string;        // بکند: 'label' => $this->name (همون name)

  description: string | null;
  longDescription: string | null;  // بکند: 'longDescription'
  image: string | null;
  icon: string | null;

  productCount: number;            // بکند: 'productCount'
  subcategories?: ApiSubcategory[];

  seo: ApiCategorySEO;
}

export interface ApiCategorySEO {
  title: string | null;
  description: string | null;
  keywords: string | null;
  heroTitle: string | null;
  heroSubtitle: string | null;
  schema: Record<string, unknown> | null;
  faqSchema: Record<string, unknown> | null;
}

// ─── Subcategory ──────────────────────────────────────────────────────────────

export interface ApiSubcategory {
  id: string;           // = slug
  name: string;
  slug: string;
  fullNameEn: string | null;   // بکند: 'fullNameEn'
  description: string | null;
  image: string | null;
  seo: ApiSubcategorySEO;
}

export interface ApiSubcategorySEO {
  title: string | null;
  description: string | null;
  keywords: string | null;
  longDescription: string | null;
  faqSchema: Record<string, unknown> | null;
}

// ─── Brand ────────────────────────────────────────────────────────────────────

export interface ApiBrand {
  id: string;           // = slug (بکند: 'id' => $this->slug)
  slug: string;
  name: string;
  country: string | null;
  description: string | null;
  longDescription: string | null;   // بکند: 'longDescription'
  logo: string | null;
  website: string | null;
  isFeatured: boolean;              // بکند: 'isFeatured'
  productCount: number;             // بکند: 'productCount'
  seo: ApiBrandSEO;
}

export interface ApiBrandSEO {
  title: string | null;
  description: string | null;
  keywords: string | null;
}

// ─── Blog ─────────────────────────────────────────────────────────────────────

export interface ApiBlogPost {
  id: string;           // = slug (بکند: 'id' => $this->slug)
  slug: string;
  title: string;
  excerpt: string | null;
  content: string;
  author: string | null;    // بکند: 'author' => $this->author (string, نه object)
  category: string | null;
  tags: string[];
  image: string | null;
  readTime: number | null;  // بکند: 'readTime' => $this->read_time
  publishedAt: string | null; // بکند: 'publishedAt' => ISO8601
  viewCount: number;
  seo: ApiBlogSEO;
}

export interface ApiBlogSEO {
  title: string | null;
  description: string | null;
  keywords: string | null;
}

// ─── RFQ ──────────────────────────────────────────────────────────────────────

export interface ApiRFQRequest {
  name: string;
  email: string;
  phone?: string;
  company?: string;
  message?: string;
  items: ApiRFQItem[];
}

export interface ApiRFQItem {
  product_id: string;
  quantity?: number;
  notes?: string;
}

export interface ApiRFQResponse {
  id: number;
  reference_number: string;
  status: 'pending' | 'reviewed' | 'quoted' | 'accepted' | 'rejected';
  created_at: string;
}

// ─── Contact ──────────────────────────────────────────────────────────────────

export interface ApiContactRequest {
  name: string;
  email: string;
  phone?: string;
  company?: string;
  subject: string;
  message: string;
}

// ─── Auth ─────────────────────────────────────────────────────────────────────

export interface ApiUser {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  company: string | null;
  role: 'customer' | 'admin';
  avatar: string | null;
  created_at: string;
}

export interface ApiLoginRequest {
  email: string;
  password: string;
}

export interface ApiLoginResponse {
  user: ApiUser;
  token: string;
  token_type: 'Bearer';
}

export interface ApiRegisterRequest {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  phone?: string;
  company?: string;
}

// ─── Search ───────────────────────────────────────────────────────────────────

export interface ApiSearchResult {
  products: ApiProduct[];
  categories: ApiCategory[];
  brands: ApiBrand[];
  blog_posts: ApiBlogPost[];
  total: number;
}

// ─── Settings ─────────────────────────────────────────────────────────────────

export interface ApiSiteSettings {
  site_name: string;
  site_description: string;
  logo: string | null;
  favicon: string | null;
  phone: string;
  email: string;
  address: string;
  social: {
    instagram?: string;
    linkedin?: string;
    telegram?: string;
    whatsapp?: string;
  };
  working_hours: string;
}

// ─── Slider ───────────────────────────────────────────────────────────────────

export interface ApiSlider {
  id: number;
  title: string;
  subtitle: string | null;
  image: string;
  link: string | null;
  button_text: string | null;
  sort_order: number;
  is_active: boolean;
}

// ─── Site Pages & Navigation ──────────────────────────────────────────────────

export interface ApiSitePage {
  id: number;
  slug: string;
  title: string;
  hero_title: string | null;
  hero_description: string | null;
  content: string | null;
  meta_title: string | null;
  meta_description: string | null;
  meta_keywords: string | null;
  status: 'published' | 'draft';
  created_at: string;
  updated_at: string;
}

export interface ApiNavigationItem {
  id: number;
  label: string;
  href: string;
  parent_id: number | null;
  sort_order: number;
  is_active: boolean;
  icon: string | null;
  description: string | null;
  children: ApiNavigationItem[];
}

// ─── Pagination ───────────────────────────────────────────────────────────────

export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
}

export interface PaginatedResult<T> {
  data: T[];
  meta: PaginationMeta;
}

// ─── Params ───────────────────────────────────────────────────────────────────

export interface ProductsParams {
  page?: number;
  per_page?: number;
  category?: string;
  brand?: string;
  search?: string;
  featured?: boolean;
  in_stock?: boolean;
}

export interface BlogParams {
  page?: number;
  per_page?: number;
  category?: string;
  tag?: string;
  search?: string;
}
