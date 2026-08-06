import { useEffect, useRef, useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { Menu, X, Phone, Globe, ChevronDown } from 'lucide-react';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { MegaMenu } from './MegaMenu';
import { ArticlesMegaMenu } from './ArticlesMegaMenu';
import { MobileNav } from './MobileNav';
import { DesktopDropdown } from './DesktopDropdown';
import { useNavigation } from '@/api/hooks';
import type { NavigationItem } from '@/api/types';

export function Navigation() {
  const location = useLocation();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [megaMenuOpen, setMegaMenuOpen] = useState<string | null>(null);
  const [dropdownOpen, setDropdownOpen] = useState<string | null>(null);
  const [isShrunk, setIsShrunk] = useState(false);
  const megaMenuRef = useRef<HTMLDivElement | null>(null);
  const articlesMegaMenuRef = useRef<HTMLDivElement | null>(null);

  const { data: apiNavItems, isLoading } = useNavigation();

  // Build NavigationItem tree from API flat list
  const buildNavTree = (items: any[]): NavigationItem[] => {
    if (!items || items.length === 0) return fallbackNav;
    const roots = items.filter(i => !i.parent_id && i.is_active);
    return roots.map(root => ({
      id: String(root.id),
      label: root.label,
      href: root.href,
      children: items
        .filter(c => c.parent_id === root.id && c.is_active)
        .sort((a, b) => a.sort_order - b.sort_order)
        .map(c => ({
          id: String(c.id),
          label: c.label,
          href: c.href,
          children: items
            .filter(cc => cc.parent_id === c.id && cc.is_active)
            .sort((a, b) => a.sort_order - b.sort_order)
            .map(cc => ({ id: String(cc.id), label: cc.label, href: cc.href })),
        })),
    }));
  };

  const navItems: NavigationItem[] = buildNavTree(apiNavItems || []);

  // Find special items by href pattern
  const productCategoriesItem = navItems.find(i => i.href?.includes('/products') && !i.href?.includes('/blog'));
  const brandsItem = navItems.find(i => i.href?.includes('/brands'));
  const articlesItem = navItems.find(i => i.href?.includes('/blog') || i.label?.includes('مقالات'));
  const projectsItem = navItems.find(i => i.href?.includes('/projects') || i.label?.includes('پروژه'));
  const aboutItem = navItems.find(i => i.href?.includes('/about') || i.label?.includes('درباره'));

  useEffect(() => {
    const onScroll = () => setIsShrunk(window.scrollY > 40);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  const isActive = (href: string) =>
    location.pathname === href || location.pathname.startsWith(href + '/');

  useEffect(() => {
    const onPointerDown = (event: MouseEvent) => {
      const target = event.target as Node;
      if (
        !megaMenuRef.current?.contains(target) &&
        !articlesMegaMenuRef.current?.contains(target)
      ) {
        setMegaMenuOpen(null);
      }
    };
    const onEscape = (event: KeyboardEvent) => {
      if (event.key === 'Escape') setMegaMenuOpen(null);
    };
    document.addEventListener('mousedown', onPointerDown);
    document.addEventListener('keydown', onEscape);
    return () => {
      document.removeEventListener('mousedown', onPointerDown);
      document.removeEventListener('keydown', onEscape);
    };
  }, []);

  // Fake NavigationItem for MegaMenu/ArticlesMegaMenu (they use item.id)
  const fakeProductItem: NavigationItem = { id: 'product-categories', label: productCategoriesItem?.label || 'دسته‌بندی محصولات', href: '/products' };
  const fakeArticlesItem: NavigationItem = { id: 'articles', label: articlesItem?.label || 'مقالات', href: '/blog' };

  return (
    <nav className="sticky top-0 z-50 w-full">
      {/* Top Trust Bar */}
      <div className="bg-primary text-primary-foreground">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="hidden lg:flex items-center justify-between py-2 text-xs">
            <div className="flex items-center gap-4">
              <span className="flex items-center gap-1.5">
                <Phone className="h-3 w-3" />
                <span className="ltr">021-66120746</span>
              </span>
            </div>
            <div className="flex-1 mx-8 overflow-hidden">
              <div className="animate-marquee whitespace-nowrap text-primary-foreground/80">
                ✅ تأمین مستقیم از ۵۰+ برند بین‌المللی &nbsp;&nbsp;|&nbsp;&nbsp; ✅ پشتیبانی فنی ۲۴/۷ &nbsp;&nbsp;|&nbsp;&nbsp; ✅ ارسال به تمام شهرهای صنعتی ایران
              </div>
            </div>
            <div className="flex items-center gap-3">
              <button className="flex items-center gap-1 hover:text-accent transition-colors">
                <Globe className="h-3 w-3" />
                <span>EN/FA</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Main Navigation */}
      <div className="bg-background border-b border-border shadow-sm">
        <div className="container relative mx-auto px-4 sm:px-6 lg:px-8">
          {/* Desktop */}
          <div className={cn('hidden lg:flex items-center justify-between transition-all duration-300', isShrunk ? 'py-2' : 'py-3')}>
            {/* Logo */}
            <Link to="/" className="flex items-center gap-3">
              <div className={cn('grid place-items-center rounded-lg bg-primary transition-all', isShrunk ? 'h-9 w-9' : 'h-11 w-11')}>
                <span className="text-sm font-black text-primary-foreground">TM</span>
              </div>
              <div>
                <p className="text-base font-bold text-foreground">تول‌مستر</p>
                <p className="text-[10px] text-muted-foreground ltr">ToolMaster Industrial</p>
              </div>
            </Link>

            {/* Center Nav */}
            <div className="flex items-center gap-1">
              {/* Mega Menu Trigger — محصولات */}
              {(productCategoriesItem || !isLoading) && (
                <div ref={megaMenuRef}>
                  <button
                    type="button"
                    onClick={() => {
                      setMegaMenuOpen(prev => prev === 'product-categories' ? null : 'product-categories');
                      setDropdownOpen(null);
                    }}
                    className={cn(
                      'flex items-center gap-1 rounded-md px-3 py-2 text-sm font-semibold transition-colors',
                      isActive('/products') ? 'text-primary bg-primary/5' : 'text-foreground hover:text-primary hover:bg-muted'
                    )}
                  >
                    {fakeProductItem.label}
                    <ChevronDown className="h-3.5 w-3.5" />
                  </button>
                  {megaMenuOpen === 'product-categories' && (
                    <MegaMenu item={fakeProductItem} onClose={() => setMegaMenuOpen(null)} />
                  )}
                </div>
              )}

              {/* برندها */}
              {brandsItem && (
                <DesktopDropdown
                  item={brandsItem}
                  isOpen={dropdownOpen === 'brands'}
                  onOpenChange={open => {
                    setDropdownOpen(open ? 'brands' : null);
                    if (open) setMegaMenuOpen(null);
                  }}
                  isActive={isActive}
                />
              )}

              {/* مقالات */}
              {(articlesItem || !isLoading) && (
                <div ref={articlesMegaMenuRef}>
                  <button
                    type="button"
                    onClick={() => {
                      setMegaMenuOpen(prev => prev === 'articles' ? null : 'articles');
                      setDropdownOpen(null);
                    }}
                    className={cn(
                      'flex items-center gap-1 rounded-md px-3 py-2 text-sm font-semibold transition-colors',
                      isActive('/blog') ? 'text-primary bg-primary/5' : 'text-foreground hover:text-primary hover:bg-muted'
                    )}
                  >
                    {fakeArticlesItem.label}
                    <ChevronDown className="h-3.5 w-3.5" />
                  </button>
                  {megaMenuOpen === 'articles' && (
                    <ArticlesMegaMenu onClose={() => setMegaMenuOpen(null)} />
                  )}
                </div>
              )}

              {/* پروژه‌ها */}
              {projectsItem && (
                <Link
                  to={projectsItem.href || '/projects'}
                  onClick={() => { setMegaMenuOpen(null); setDropdownOpen(null); }}
                  className={cn(
                    'rounded-md px-3 py-2 text-sm font-semibold transition-colors',
                    isActive(projectsItem.href || '') ? 'text-primary bg-primary/5' : 'text-foreground hover:text-primary hover:bg-muted'
                  )}
                >
                  {projectsItem.label}
                </Link>
              )}

              {/* درباره ما */}
              {aboutItem && (
                <DesktopDropdown
                  item={aboutItem}
                  isOpen={dropdownOpen === 'about'}
                  onOpenChange={open => {
                    setDropdownOpen(open ? 'about' : null);
                    if (open) setMegaMenuOpen(null);
                  }}
                  isActive={isActive}
                />
              )}

              {/* آیتم‌های اضافی از دیتابیس که در دسته‌های بالا نیستن */}
              {navItems
                .filter(item => {
                  const knownHrefs = ['/products', '/brands', '/blog', '/projects', '/about'];
                  return !knownHrefs.some(h => item.href?.startsWith(h));
                })
                .map(item => (
                  <Link
                    key={item.id}
                    to={item.href || '#'}
                    className={cn(
                      'rounded-md px-3 py-2 text-sm font-semibold transition-colors',
                      isActive(item.href || '') ? 'text-primary bg-primary/5' : 'text-foreground hover:text-primary hover:bg-muted'
                    )}
                  >
                    {item.label}
                  </Link>
                ))}
            </div>

            {/* Right Actions */}
            <div className="flex items-center gap-3">
              <Button asChild className="bg-accent text-accent-foreground hover:bg-accent/90 font-bold shadow-md">
                <Link to="/contact">
                  <Phone className="h-4 w-4 ml-1" />
                  درخواست مشاوره
                </Link>
              </Button>
            </div>
          </div>

          {/* Mobile */}
          <div className="flex items-center justify-between py-3 lg:hidden">
            <Link to="/" className="flex items-center gap-2">
              <div className="flex h-8 w-8 items-center justify-center rounded-md bg-primary">
                <span className="text-xs font-bold text-primary-foreground">TM</span>
              </div>
              <span className="text-base font-bold text-foreground">تول‌مستر</span>
            </Link>
            <Button variant="ghost" size="sm" onClick={() => setMobileMenuOpen(!mobileMenuOpen)}>
              {mobileMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
            </Button>
          </div>

          {mobileMenuOpen && (
            <div className="max-h-[70vh] overflow-y-auto border-t border-border py-4 lg:hidden">
              <MobileNav items={navItems.length > 0 ? navItems : fallbackNav} onClose={() => setMobileMenuOpen(false)} />
            </div>
          )}
        </div>
      </div>
    </nav>
  );
}

// Fallback در صورتی که API جواب نداد
const fallbackNav: NavigationItem[] = [
  { id: 'product-categories', label: 'دسته‌بندی محصولات', href: '/products' },
  { id: 'brands', label: 'برندها', href: '/brands' },
  { id: 'articles', label: 'مقالات', href: '/blog' },
  { id: 'projects', label: 'پروژه‌ها', href: '/projects' },
  { id: 'about', label: 'درباره ما', href: '/about' },
];
