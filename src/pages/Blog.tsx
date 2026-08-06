import { useState, useEffect, useMemo, useRef, type Dispatch, type SetStateAction } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { motion } from 'framer-motion';
import { Helmet } from 'react-helmet-async';
import { SEO } from '@/components/SEO';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Calendar, Clock, ArrowLeft, User, Search, Filter, X, ChevronDown, ChevronUp, ChevronLeft as ChevronLeftIcon, ChevronRight } from 'lucide-react';
import { equipmentTypes } from '@/data/product-taxonomy';
import { blogService } from '@/api/services';
import type { CheckedState } from '@radix-ui/react-checkbox';

// ── Static fallback (اگه API جواب نداد) ────────────────────────────────────
import { blogPosts as staticBlogPosts } from '@/data/blog-posts';

const ITEMS_PER_PAGE = 9;

const parseMultiParam = (value: string | null) => value ? value.split(',').filter(Boolean) : [];

const mainCategoryGroups = [
  { key: 'gas-generators', label: 'ژنراتورهای گاز', categories: ['gas-generators'] },
  { key: 'gas-detectors', label: 'دتکتورهای گاز', categories: ['gas-detectors'] },
  { key: 'flow-meters', label: 'فلومتر و فلوکنترلر', categories: ['flow-meters'] },
  { key: 'plc-equipment', label: 'تجهیزات PLC', categories: ['plc-equipment'] },
  { key: 'lab-pumps', label: 'پمپ‌های آزمایشگاهی', categories: ['lab-pumps'] },
  { key: 'calibration', label: 'کالیبراسیون و لوازم جانبی', categories: ['calibration'] },
].map((group) => ({
  ...group,
  typeEntries: Object.entries(equipmentTypes).filter(([_, type]) => group.categories.includes(type.category)),
}));

const allMainCategories = [...new Set(mainCategoryGroups.flatMap((g) => g.categories))];
const allMainTypes = mainCategoryGroups.flatMap((g) => g.typeEntries.map(([k]) => k));

export default function Blog() {
  const [searchParams, setSearchParams] = useSearchParams();

  const [searchQuery, setSearchQuery] = useState(() => searchParams.get('search') || '');
  const [selectedCategories, setSelectedCategories] = useState<string[]>(() => parseMultiParam(searchParams.get('category')));
  const [selectedTypes, setSelectedTypes] = useState<string[]>(() => parseMultiParam(searchParams.get('type')));
  const [expandedCategories, setExpandedCategories] = useState<string[]>(() => parseMultiParam(searchParams.get('category')));

  // ── FIX: API state ─────────────────────────────────────────────────────────
  const [apiPosts, setApiPosts] = useState<any[]>([]);
  const [apiLoading, setApiLoading] = useState(true);
  const [apiError, setApiError] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [totalItems, setTotalItems] = useState(0);

  useEffect(() => {
    let cancelled = false;
    setApiLoading(true);
    blogService.getAll({ per_page: ITEMS_PER_PAGE, page: currentPage } as any)
      .then((data: any) => {
        if (cancelled) return;
        const items = Array.isArray(data) ? data : (data?.data ?? []);
        const meta = data?.meta;
        if (items.length > 0) setApiPosts(items);
        if (meta) {
          setTotalPages(meta.last_page ?? 1);
          setTotalItems(meta.total ?? items.length);
        }
        setApiLoading(false);
      })
      .catch(() => {
        if (!cancelled) { setApiError(true); setApiLoading(false); }
      });
    return () => { cancelled = true; };
  }, [currentPage]);

  const resetPage = () => setCurrentPage(1);

  const isInternalUrlUpdate = useRef(false);
  const skipNextUrlSync = useRef(true);

  useEffect(() => {
    if (isInternalUrlUpdate.current) { isInternalUrlUpdate.current = false; return; }
    const cats = parseMultiParam(searchParams.get('category'));
    const types = parseMultiParam(searchParams.get('type'));
    const search = searchParams.get('search') || '';
    setSelectedCategories(cats);
    setSelectedTypes(types);
    setSearchQuery(search);
    if (cats.length > 0) setExpandedCategories(prev => [...new Set([...prev, ...cats])]);
    skipNextUrlSync.current = true;
    resetPage();
  }, [searchParams]);

  useEffect(() => {
    if (skipNextUrlSync.current) { skipNextUrlSync.current = false; return; }
    const params = new URLSearchParams();
    if (selectedCategories.length) params.set('category', selectedCategories.join(','));
    if (selectedTypes.length) params.set('type', selectedTypes.join(','));
    if (searchQuery) params.set('search', searchQuery);
    isInternalUrlUpdate.current = true;
    setSearchParams(params, { replace: true });
  }, [selectedCategories, selectedTypes, searchQuery, setSearchParams]);

  useEffect(() => {
    if (selectedCategories.length === 0) return;
    setSelectedTypes(prev => {
      const pruned = prev.filter(typeKey => {
        const type = equipmentTypes[typeKey as keyof typeof equipmentTypes];
        return type && selectedCategories.includes(type.category);
      });
      return pruned.length === prev.length ? prev : pruned;
    });
  }, [selectedCategories]);

  const toggleFilter = <T extends string>(arr: T[], val: T, setter: Dispatch<SetStateAction<T[]>>) => {
    setter(prev => prev.includes(val) ? prev.filter(v => v !== val) : [...prev, val]);
    resetPage();
  };

  const toggleCategory = (key: string) => {
    setExpandedCategories(prev => prev.includes(key) ? prev.filter(c => c !== key) : [...prev, key]);
  };

  const isAllSelected = allMainCategories.every(c => selectedCategories.includes(c));

  const toggleMainGroup = (group: (typeof mainCategoryGroups)[number]) => {
    const isGroupSelected = group.categories.every(c => selectedCategories.includes(c));
    if (isGroupSelected) {
      setSelectedCategories(prev => prev.filter(c => !group.categories.includes(c)));
      setSelectedTypes(prev => prev.filter(t => !group.typeEntries.some(([k]) => k === t)));
    } else {
      setSelectedCategories(prev => [...new Set([...prev, ...group.categories])]);
      setExpandedCategories(prev => prev.includes(group.key) ? prev : [...prev, group.key]);
    }
    resetPage();
  };

  const getGroupCheckedState = (group: (typeof mainCategoryGroups)[number]): CheckedState => {
    const count = group.categories.filter(c => selectedCategories.includes(c)).length;
    if (count === 0) return false;
    if (count === group.categories.length) return true;
    return 'indeterminate';
  };

  const toggleAll = () => {
    if (isAllSelected) { setSelectedCategories([]); setSelectedTypes([]); }
    else { setSelectedCategories(allMainCategories); setSelectedTypes(allMainTypes); setExpandedCategories(mainCategoryGroups.map(g => g.key)); }
    resetPage();
  };

  const clearAllFilters = () => {
    setSelectedCategories([]); setSelectedTypes([]); setSearchQuery('');
    resetPage();
  };

  const activeFilterCount = selectedCategories.length + selectedTypes.length;

  // ── FIX: اگه API داده داره از اون استفاده کن، وگرنه static ─────────────────
  const displayPosts = useMemo(() => {
    if (apiPosts.length > 0) return apiPosts; // API فیلتر سمت سرور انجام میده
    // fallback: static با فیلتر client-side
    return staticBlogPosts.filter(post => {
      const matchesSearch = !searchQuery || post.title.includes(searchQuery) || post.excerpt.includes(searchQuery);
      const matchesProductCat = selectedCategories.length === 0 || selectedCategories.some(c => (post as any).productCategories?.includes(c));
      const matchesType = selectedTypes.length === 0 || selectedTypes.some(t => (post as any).productTypes?.includes(t));
      return matchesSearch && matchesProductCat && matchesType;
    });
  }, [apiPosts, staticBlogPosts, searchQuery, selectedCategories, selectedTypes]);

  // Pagination برای static data
  const staticTotalPages = Math.ceil(displayPosts.length / ITEMS_PER_PAGE);
  const paginatedPosts = apiPosts.length > 0
    ? displayPosts
    : displayPosts.slice((currentPage - 1) * ITEMS_PER_PAGE, currentPage * ITEMS_PER_PAGE);

  const effectiveTotalPages = apiPosts.length > 0 ? totalPages : staticTotalPages;
  const effectiveTotalItems = apiPosts.length > 0 ? totalItems : displayPosts.length;

  const noindex = selectedCategories.length > 1 || selectedTypes.length > 0 || searchQuery !== '';
  const pageTitle = selectedCategories.length === 1
    ? `مقالات ${mainCategoryGroups.find(g => g.categories.includes(selectedCategories[0]))?.label || ''} | تول‌مستر`
    : 'دانش فنی و مقالات تخصصی ابزار دقیق | تول‌مستر';

  const Pagination = () => {
    if (effectiveTotalPages <= 1) return null;
    return (
      <div className="flex items-center justify-center gap-2 mt-10" dir="ltr">
        <Button variant="outline" size="sm" onClick={() => setCurrentPage(p => Math.max(1, p - 1))} disabled={currentPage === 1} className="gap-1">
          <ChevronRight className="h-4 w-4" />قبلی
        </Button>
        <div className="flex items-center gap-1">
          {Array.from({ length: Math.min(5, effectiveTotalPages) }, (_, i) => {
            let page = effectiveTotalPages <= 5 ? i + 1 : currentPage <= 3 ? i + 1 : currentPage >= effectiveTotalPages - 2 ? effectiveTotalPages - 4 + i : currentPage - 2 + i;
            return (
              <Button key={page} variant={currentPage === page ? 'default' : 'outline'} size="sm" onClick={() => setCurrentPage(page)} className="h-8 w-8 p-0">{page}</Button>
            );
          })}
        </div>
        <Button variant="outline" size="sm" onClick={() => setCurrentPage(p => Math.min(effectiveTotalPages, p + 1))} disabled={currentPage === effectiveTotalPages} className="gap-1">
          بعدی<ChevronLeftIcon className="h-4 w-4" />
        </Button>
      </div>
    );
  };

  const FilterSidebar = () => (
    <div className="space-y-5" dir="rtl">
      {activeFilterCount > 0 && (
        <div className="flex items-center justify-between p-2.5 bg-accent/10 rounded-lg border border-accent/20">
          <span className="text-xs font-bold text-accent">{activeFilterCount} فیلتر فعال</span>
          <Button variant="ghost" size="sm" onClick={clearAllFilters} className="h-7 text-xs gap-1"><X className="h-3 w-3" /> پاک کردن</Button>
        </div>
      )}
      <div>
        <h3 className="font-bold mb-2.5 text-sm text-right">دسته‌بندی محصولات مرتبط</h3>
        <div className="space-y-2">
          <div className="border-b border-border/40 pb-2">
            <label className="flex items-center gap-2 cursor-pointer group">
              <Checkbox checked={isAllSelected} onCheckedChange={toggleAll} />
              <span className="text-sm font-extrabold group-hover:text-accent transition-colors">همه دسته‌بندی‌ها</span>
            </label>
          </div>
          {mainCategoryGroups.map((group) => {
            const isExpanded = expandedCategories.includes(group.key);
            return (
              <div key={group.key} className="border-b border-border/40 pb-2 last:border-0">
                <div className="flex items-center justify-between">
                  <label className="flex items-center gap-2 cursor-pointer group flex-1">
                    <Checkbox checked={getGroupCheckedState(group)} onCheckedChange={() => toggleMainGroup(group)} />
                    <span className="text-sm font-medium group-hover:text-accent transition-colors">{group.label}</span>
                  </label>
                  {group.typeEntries.length > 0 && (
                    <button onClick={() => toggleCategory(group.key)} className="p-1 hover:bg-accent/10 rounded-sm transition-colors">
                      {isExpanded ? <ChevronUp className="h-4 w-4 text-muted-foreground" /> : <ChevronDown className="h-4 w-4 text-muted-foreground" />}
                    </button>
                  )}
                </div>
                {isExpanded && group.typeEntries.length > 0 && (
                  <div className="mr-6 mt-2 space-y-1.5">
                    {group.typeEntries.map(([typeKey, type]) => (
                      <label key={typeKey} className="flex items-center gap-2 cursor-pointer group">
                        <Checkbox checked={selectedTypes.includes(typeKey)} onCheckedChange={() => toggleFilter(selectedTypes, typeKey, setSelectedTypes)} />
                        <span className="text-sm group-hover:text-accent transition-colors">{type.label}</span>
                      </label>
                    ))}
                  </div>
                )}
              </div>
            );
          })}
        </div>
      </div>
    </div>
  );

  return (
    <div className="min-h-screen bg-background" dir="rtl">
      <SEO title={pageTitle} description="مقالات تخصصی، راهنماهای خرید و اخبار صنعت ابزار دقیق و اتوماسیون صنعتی ایران" keywords="مقالات ابزار دقیق, راهنمای خرید تجهیزات, اتوماسیون صنعتی" />
      <Helmet>
        {noindex && <meta name="robots" content="noindex, follow" />}
        <link rel="canonical" href="https://toolmaster.com/blog" />
      </Helmet>

      <section className="bg-gradient-to-br from-[hsl(var(--hero-gradient-start))] to-[hsl(var(--hero-gradient-end))] text-primary-foreground py-10 border-b border-border">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
            <h1 className="text-4xl font-black mb-3">دانش فنی و مقالات</h1>
            <p className="text-lg text-primary-foreground/80 max-w-3xl">راهنماهای کاربردی، مقالات تخصصی و آخرین اخبار صنعت ابزار دقیق و اتوماسیون</p>
            {apiError && <p className="text-sm text-yellow-300 mt-2">⚠️ اتصال به API برقرار نشد — نمایش داده‌های پیش‌فرض</p>}
          </motion.div>
        </div>
      </section>

      <section className="py-4 border-b border-border sticky top-16 z-40 backdrop-blur bg-background/95">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex gap-4">
            <div className="flex-1 relative">
              <Search className="absolute right-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input placeholder="جستجو در مقالات..." value={searchQuery} onChange={(e) => { setSearchQuery(e.target.value); resetPage(); }} className="pr-10 text-right" />
            </div>
            <Sheet>
              <SheetTrigger asChild>
                <Button variant="outline" className="lg:hidden relative">
                  <Filter className="h-4 w-4 ml-2" />فیلترها
                  {activeFilterCount > 0 && (<Badge variant="default" className="absolute -top-2 -left-2 h-5 w-5 p-0 flex items-center justify-center text-[10px]">{activeFilterCount}</Badge>)}
                </Button>
              </SheetTrigger>
              <SheetContent side="right">
                <SheetHeader><SheetTitle className="text-right">فیلتر دسته‌بندی</SheetTitle></SheetHeader>
                <div className="mt-6 overflow-y-auto max-h-[calc(100vh-120px)]"><FilterSidebar /></div>
              </SheetContent>
            </Sheet>
          </div>
        </div>
      </section>

      <section className="py-8">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex gap-8">
            <aside className="hidden lg:block w-64 flex-shrink-0">
              <div className="sticky top-32 max-h-[calc(100vh-160px)] overflow-y-auto">
                <Card className="border-border/60">
                  <CardHeader className="pb-3"><CardTitle className="text-base flex items-center gap-2"><Filter className="h-4 w-4" />فیلتر بر اساس محصول</CardTitle></CardHeader>
                  <CardContent><FilterSidebar /></CardContent>
                </Card>
              </div>
            </aside>

            <div className="flex-1">
              <div className="mb-4 flex items-center justify-between">
                <p className="text-sm text-muted-foreground">
                  {effectiveTotalItems} مقاله
                  {apiPosts.length > 0 && <span className="mr-2 text-green-600 text-xs">✓ از پایگاه داده</span>}
                </p>
                {activeFilterCount > 0 && (
                  <div className="hidden lg:flex flex-wrap gap-1.5">
                    {selectedCategories.map(c => {
                      const group = mainCategoryGroups.find(g => g.categories.includes(c));
                      return (<Badge key={c} variant="secondary" className="text-xs gap-1 cursor-pointer" onClick={() => toggleFilter(selectedCategories, c, setSelectedCategories)}>{group?.label || c}<X className="h-3 w-3" /></Badge>);
                    })}
                    {selectedTypes.map(t => {
                      const type = equipmentTypes[t as keyof typeof equipmentTypes];
                      return type ? (<Badge key={t} variant="secondary" className="text-xs gap-1 cursor-pointer" onClick={() => toggleFilter(selectedTypes, t, setSelectedTypes)}>{type.label}<X className="h-3 w-3" /></Badge>) : null;
                    })}
                  </div>
                )}
              </div>

              {apiLoading && (
                <div className="flex items-center justify-center py-8">
                  <div className="animate-spin h-6 w-6 border-4 border-primary border-t-transparent rounded-full ml-3"></div>
                  <span className="text-muted-foreground text-sm">در حال بارگذاری...</span>
                </div>
              )}

              <div className="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
                {paginatedPosts.map((post, index) => (
                  <motion.div key={post.id} initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: index * 0.1 }}>
                    <Link to={`/blog/${post.slug ?? post.id}`}>
                      <Card className="h-full hover:shadow-lg transition-all group border-border/60 hover:border-accent/30 overflow-hidden">
                        <div className="h-48 overflow-hidden">
                          <img src={post.image} alt={post.title} className="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy" />
                        </div>
                        <CardHeader>
                          <div className="flex items-center gap-2 mb-3">
                            <Badge variant="secondary">{post.category}</Badge>
                          </div>
                          <CardTitle className="group-hover:text-accent transition-colors line-clamp-2 text-base leading-relaxed">{post.title}</CardTitle>
                          <CardDescription className="line-clamp-3 leading-relaxed">{post.excerpt}</CardDescription>
                        </CardHeader>
                        <CardContent>
                          <div className="flex items-center justify-between text-xs text-muted-foreground">
                            <div className="flex items-center gap-3">
                              <span className="flex items-center"><Calendar className="h-3 w-3 ml-1" />{post.date ?? post.created_at}</span>
                              {post.readTime && <span className="flex items-center"><Clock className="h-3 w-3 ml-1" />{post.readTime}</span>}
                            </div>
                            {post.author && <span className="flex items-center"><User className="h-3 w-3 ml-1" />{typeof post.author === 'object' ? post.author.name : post.author}</span>}
                          </div>
                          <div className="mt-4 flex items-center text-sm text-accent font-bold">
                            مطالعه مقاله<ArrowLeft className="mr-2 h-4 w-4 group-hover:-translate-x-1 transition-transform" />
                          </div>
                        </CardContent>
                      </Card>
                    </Link>
                  </motion.div>
                ))}
              </div>

              <Pagination />

              {paginatedPosts.length === 0 && !apiLoading && (
                <div className="text-center py-12">
                  <Search className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                  <h3 className="text-lg font-bold mb-2">مقاله‌ای یافت نشد</h3>
                  <p className="text-muted-foreground mb-4">فیلترهای خود را تغییر دهید</p>
                  <Button variant="outline" onClick={clearAllFilters}>پاک کردن فیلترها</Button>
                </div>
              )}
            </div>
          </div>
        </div>
      </section>

      <section className="py-12 bg-muted/50 border-t border-border">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <Card className="max-w-2xl mx-auto border-border/60">
            <CardHeader className="text-center">
              <CardTitle>عضویت در خبرنامه فنی</CardTitle>
              <CardDescription>آخرین مقالات و اخبار صنعت ابزار دقیق در ایمیل شما</CardDescription>
            </CardHeader>
            <CardContent>
              <form className="flex gap-2">
                <input type="email" placeholder="ایمیل شما" className="flex-1 px-4 py-2 border border-border rounded-lg bg-background ltr text-left" dir="ltr" />
                <button type="submit" className="px-6 py-2 bg-accent text-accent-foreground rounded-lg hover:bg-accent/90 transition-colors font-bold">عضویت</button>
              </form>
            </CardContent>
          </Card>
        </div>
      </section>
    </div>
  );
}
