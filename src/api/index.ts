// HTTP Client
export { api, setAuthToken, getAuthToken, clearAuth, ApiError } from './client';
export type { ApiResponse, PaginatedResponse, PaginationMeta, ApiErrorResponse } from './client';

// TypeScript Types
export type {
  ApiProduct,
  ApiProductSpecs,
  ApiCategory,
  ApiBrand,
  ApiBlogPost,
  ApiAuthor,
  ApiRFQRequest,
  ApiRFQItem,
  ApiRFQResponse,
  ApiContactRequest,
  ApiSEO,
  ApiUser,
  ApiLoginRequest,
  ApiLoginResponse,
  ApiRegisterRequest,
  ApiSearchResult,
  ApiSiteSettings,
  ApiSlider,
  ApiSitePage,
  ApiNavigationItem,
} from './types';

// Service Layer
export {
  productService,
  categoryService,
  brandService,
  blogService,
  rfqService,
  contactService,
  searchService,
  authService,
  settingsService,
  sliderService,
  newsletterService,
  pageService,
  navigationService,
  isApiConfigured,
} from './services';

// React Query Hooks
export {
  queryKeys,
  useProducts,
  useProduct,
  useFeaturedProducts,
  useSimilarProducts,
  useCategories,
  useCategory,
  useCategoryProducts,
  useBrands,
  useBrand,
  useBrandProducts,
  useBlogPosts,
  useBlogPost,
  useLatestBlogPosts,
  useSearch,
  useSubmitRFQ,
  useSubmitContact,
  useSubscribeNewsletter,
  useSiteSettings,
  useSliders,
  useSitePage,
  useNavigation,
} from './hooks';
