import { Link, useLocation } from 'react-router-dom';
import { ChevronLeft, Home } from 'lucide-react';
import { productCategories, equipmentTypes } from '@/data/product-taxonomy';
import { categorySEOData } from '@/data/category-seo';
import { subcategorySEOData } from '@/data/subcategory-seo';

const routeLabels: Record<string, string> = {
  '': 'خانه',
  products: 'محصولات',
  brands: 'برندها',
  about: 'درباره ما',
  contact: 'تماس با ما',
  blog: 'مقالات',
  resources: 'منابع',
  sustainability: 'گواهینامه‌ها',
  projects: 'پروژه‌ها',
};

export function Breadcrumbs() {
  const { pathname, search } = useLocation();
  if (pathname === '/') return null;

  const segments = pathname.split('/').filter(Boolean);

  // Build breadcrumb items, skipping "category" segment
  const breadcrumbItems: { path: string; label: string }[] = [];

  for (let i = 0; i < segments.length; i++) {
    const seg = segments[i];

    // Skip the literal "category" segment in /products/category/slug
    if (seg === 'category' && segments[i - 1] === 'products') {
      continue;
    }

    const path = '/' + segments.slice(0, i + 1).join('/');

    // Check if this is a category slug (after products/category/)
    if (segments[i - 1] === 'category' && segments[i - 2] === 'products') {
      const catData = categorySEOData[seg];
      breadcrumbItems.push({
        path,
        label: catData?.title || decodeURIComponent(seg),
      });
      continue;
    }

    // Check if this is a subcategory slug
    if (segments[i - 2] === 'category' && segments[i - 3] === 'products') {
      const subData = subcategorySEOData[seg];
      breadcrumbItems.push({
        path,
        label: subData?.title || decodeURIComponent(seg),
      });
      continue;
    }

    const label = routeLabels[seg] || decodeURIComponent(seg);
    breadcrumbItems.push({ path, label });
  }

  // Add filter-based breadcrumbs on /products page
  if (pathname === '/products' && search) {
    const params = new URLSearchParams(search);
    const categoryParam = params.get('category')?.split(',')[0];
    const typeParam = params.get('type')?.split(',')[0];

    if (categoryParam && productCategories[categoryParam as keyof typeof productCategories]) {
      breadcrumbItems.push({
        path: `/products/category/${categoryParam}`,
        label: productCategories[categoryParam as keyof typeof productCategories].label,
      });
    }

    if (typeParam && equipmentTypes[typeParam as keyof typeof equipmentTypes]) {
      breadcrumbItems.push({
        path: pathname + search,
        label: equipmentTypes[typeParam as keyof typeof equipmentTypes].label,
      });
    }
  }

  return (
    <nav aria-label="breadcrumb" className="bg-muted/50 border-b border-border">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <ol className="flex flex-wrap items-center gap-1.5 text-sm text-muted-foreground">
          <li className="inline-flex items-center">
            <Link to="/" className="flex items-center gap-1 hover:text-primary transition-colors">
              <Home className="h-3.5 w-3.5" />
              <span>خانه</span>
            </Link>
          </li>
          {breadcrumbItems.map((item, i) => {
            const isLast = i === breadcrumbItems.length - 1;
            return (
              <li key={`${item.path}-${item.label}`} className="inline-flex items-center gap-1.5">
                <ChevronLeft className="h-3.5 w-3.5 text-muted-foreground/50" />
                {isLast ? (
                  <span className="font-medium text-foreground">{item.label}</span>
                ) : (
                  <Link to={item.path} className="hover:text-primary transition-colors">{item.label}</Link>
                )}
              </li>
            );
          })}
        </ol>
      </div>
    </nav>
  );
}
