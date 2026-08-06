import { Suspense, lazy } from 'react';
import { BrowserRouter, Routes, Route, useLocation } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { RFQProvider } from '@/contexts/RFQContext';
import { Toaster } from '@/components/ui/toaster';
import { Navigation } from '@/components/Navigation';
import { Footer } from '@/components/Footer';
import { ScrollToTop } from '@/components/ScrollToTop';
import { Breadcrumbs } from '@/components/Breadcrumbs';

// ── FIX 8: QueryClient با تنظیمات کامل ───────────────────────────────────────
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      retryDelay: (attemptIndex) => Math.min(1000 * 2 ** attemptIndex, 10000),
      staleTime: 5 * 60 * 1000,
      refetchOnWindowFocus: false,
    },
    mutations: {
      retry: 0,
      onError: (error: unknown) => {
        console.error('[QueryClient] Mutation error:', error);
      },
    },
  },
});

// ── Eager (سبک) ───────────────────────────────────────────────────────────────
import Home from '@/pages/Home';
import NotFound from '@/pages/NotFound';

// ── Lazy (سنگین) ──────────────────────────────────────────────────────────────
const Products       = lazy(() => import('@/pages/Products'));
const Blog           = lazy(() => import('@/pages/Blog'));
const BlogArticle    = lazy(() => import('@/pages/BlogArticle'));
const BrandPage      = lazy(() => import('@/pages/BrandPage'));
const Brands         = lazy(() => import('@/pages/Brands'));
const ProductDetail  = lazy(() => import('@/pages/ProductDetail'));
const CategoryPage   = lazy(() => import('@/pages/CategoryPage'));
const SubcategoryPage = lazy(() => import('@/pages/SubcategoryPage'));
const Contact        = lazy(() => import('@/pages/Contact'));
const About          = lazy(() => import('@/pages/About'));
const Resources      = lazy(() => import('@/pages/Resources'));
const Sustainability = lazy(() => import('@/pages/Sustainability'));
const StaticSeoPage  = lazy(() => import('@/pages/StaticSeoPage'));

function PageLoader() {
  return (
    <div className="min-h-screen flex items-center justify-center">
      <div className="flex flex-col items-center gap-3">
        <div className="animate-spin h-8 w-8 border-4 border-primary border-t-transparent rounded-full" />
        <p className="text-sm text-muted-foreground">در حال بارگذاری...</p>
      </div>
    </div>
  );
}

// ── Layout با Breadcrumb ───────────────────────────────────────────────────────
// Breadcrumbs رو داخل BrowserRouter میذاریم چون به useLocation نیاز داره
function AppLayout({ children }: { children: React.ReactNode }) {
  return (
    <>
      <Navigation />
      <Breadcrumbs />
      <main>
        {children}
      </main>
      <Footer />
      <Toaster />
    </>
  );
}

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <RFQProvider>
        <BrowserRouter>
          <ScrollToTop />
          <AppLayout>
            <Suspense fallback={<PageLoader />}>
              <Routes>
                {/* ── صفحات اصلی ───────────────────────────────────────────── */}
                <Route path="/"                              element={<Home />} />
                <Route path="/products"                      element={<Products />} />
                <Route path="/products/category/:slug"       element={<Products />} />
                <Route path="/products/category/:slug/:subSlug" element={<Products />} />
                <Route path="/products/:id"                  element={<ProductDetail />} />

                {/* ── بلاگ ─────────────────────────────────────────────────── */}
                <Route path="/blog"                          element={<Blog />} />
                <Route path="/blog/:id"                      element={<BlogArticle />} />

                {/* ── برندها ───────────────────────────────────────────────── */}
                <Route path="/brands"                        element={<Brands />} />
                <Route path="/brands/:slug"                  element={<BrandPage />} />

                {/* ── دسته‌بندی‌ها ──────────────────────────────────────────── */}
                <Route path="/category/:slug"                element={<CategoryPage />} />
                <Route path="/category/:slug/:subSlug"       element={<SubcategoryPage />} />

                {/* ── صفحات ثابت ───────────────────────────────────────────── */}
                <Route path="/contact"                       element={<Contact />} />
                <Route path="/about"                         element={<About />} />
                <Route path="/resources"                     element={<Resources />} />
                <Route path="/sustainability"                element={<Sustainability />} />

                {/* ── صفحات SEO داینامیک ────────────────────────────────────── */}
                <Route path="/pages/:pageKey"                element={<StaticSeoPage pageKey="" />} />

                <Route path="*"                              element={<NotFound />} />
              </Routes>
            </Suspense>
          </AppLayout>
        </BrowserRouter>
      </RFQProvider>
    </QueryClientProvider>
  );
}
