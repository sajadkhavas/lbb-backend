import DOMPurify from 'dompurify';
import { useState, useEffect } from 'react';
import { Link, useParams, Navigate } from 'react-router-dom';
import { motion } from 'framer-motion';
import { SEO } from '@/components/SEO';
import { generateBreadcrumbSchema } from '@/lib/structured-data';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { Search, Filter, Settings, CheckCircle, Download, ChevronLeft, ChevronRight } from 'lucide-react';
import { useRFQ } from '@/contexts/RFQContext';
import { useToast } from '@/hooks/use-toast';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { productCategories } from '@/data/product-taxonomy';
import { brandSEOData } from '@/data/brand-seo';
import { generateSupportiveSeoHtml } from '@/lib/seo-content';
import { brandService } from '@/api/services';

// ── Static fallback ────────────────────────────────────────────────────────
import { products as staticProducts, type Product } from '@/data/products';
import prodNitrogenPeak from '@/assets/products/nitrogen-generator-peak.jpg';
import prodFlowmeterEndress from '@/assets/products/flowmeter-endress.jpg';
import prodDetectorHoneywell from '@/assets/products/gas-detector-honeywell.jpg';
import prodPlcSiemens from '@/assets/products/plc-siemens.jpg';
import prodPumpKnf from '@/assets/products/pump-knf.jpg';
import prodDcsEmerson from '@/assets/products/dcs-emerson.jpg';
import prodDriveAbb from '@/assets/products/drive-abb.jpg';
import prodPlcRockwell from '@/assets/products/plc-rockwell.jpg';
import prodDetectorDrager from '@/assets/products/detector-drager.jpg';
import prodDcsYokogawa from '@/assets/products/dcs-yokogawa.jpg';
import prodFlowBrooks from '@/assets/products/flowcontroller-brooks.jpg';
import prodPlcSchneider from '@/assets/products/plc-schneider.jpg';
import prodNitrogenParker from '@/assets/products/nitrogen-generator-parker.jpg';
import prodHydrogenPeak from '@/assets/products/hydrogen-generator-peak.jpg';
import prodAirPeak from '@/assets/products/air-generator-peak.jpg';
import prodVacuumEdwards from '@/assets/products/vacuum-pump-edwards.jpg';
import prodPeristalticWatson from '@/assets/products/peristaltic-pump-watson.jpg';
import prodDetectorDrager4x from '@/assets/products/gas-detector-drager-4x.jpg';
import prodDetectorMsa from '@/assets/products/gas-detector-msa.jpg';
import prodFlowBronkhorst from '@/assets/products/flowcontroller-bronkhorst.jpg';
import prodFlowmeterSick from '@/assets/products/flowmeter-sick.jpg';
import prodPlcIoSiemens from '@/assets/products/plc-io-siemens.jpg';
import prodHmiSiemens from '@/assets/products/hmi-siemens.jpg';

const imageMap: Record<string, string> = {
  'nitrogen-generator-peak': prodNitrogenPeak,
  'flowmeter-endress': prodFlowmeterEndress,
  'gas-detector-honeywell': prodDetectorHoneywell,
  'plc-siemens': prodPlcSiemens,
  'pump-knf': prodPumpKnf,
  'dcs-emerson': prodDcsEmerson,
  'drive-abb': prodDriveAbb,
  'plc-rockwell': prodPlcRockwell,
  'detector-drager': prodDetectorDrager,
  'dcs-yokogawa': prodDcsYokogawa,
  'flowcontroller-brooks': prodFlowBrooks,
  'plc-schneider': prodPlcSchneider,
  'nitrogen-generator-parker': prodNitrogenParker,
  'hydrogen-generator-peak': prodHydrogenPeak,
  'air-generator-peak': prodAirPeak,
  'vacuum-pump-edwards': prodVacuumEdwards,
  'peristaltic-pump-watson': prodPeristalticWatson,
  'gas-detector-drager-4x': prodDetectorDrager4x,
  'gas-detector-msa': prodDetectorMsa,
  'flowcontroller-bronkhorst': prodFlowBronkhorst,
  'flowmeter-sick': prodFlowmeterSick,
  'plc-io-siemens': prodPlcIoSiemens,
  'hmi-siemens': prodHmiSiemens,
};

function resolveImage(image: string | null | undefined): string | null {
  if (!image) return null;
  if (image.startsWith('http')) return image;
  return imageMap[image] ?? null;
}

const ITEMS_PER_PAGE = 12;

export default function BrandPage() {
  const { slug } = useParams<{ slug: string }>();
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState<string>('all');
  const [showInStockOnly, setShowInStockOnly] = useState(false);
  const { addProduct } = useRFQ();
  const { toast } = useToast();

  // ── FIX: API state ─────────────────────────────────────────────────────────
  const [apiProducts, setApiProducts] = useState<any[]>([]);
  const [apiLoading, setApiLoading] = useState(true);
  const [apiError, setApiError] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [totalItems, setTotalItems] = useState(0);

  const brandData = slug ? brandSEOData[slug] : null;
  if (!brandData) return <Navigate to="/products" replace />;

  useEffect(() => {
    if (!slug) return;
    let cancelled = false;
    setApiLoading(true);
    brandService.getProducts(slug, { per_page: ITEMS_PER_PAGE, page: currentPage } as any)
      .then((data: any) => {
        if (cancelled) return;
        const items = Array.isArray(data) ? data : (data?.data ?? []);
        const meta = data?.meta;
        if (items.length > 0) setApiProducts(items);
        if (meta) { setTotalPages(meta.last_page ?? 1); setTotalItems(meta.total ?? items.length); }
        setApiLoading(false);
      })
      .catch(() => {
        if (!cancelled) { setApiError(true); setApiLoading(false); }
      });
    return () => { cancelled = true; };
  }, [slug, currentPage]);

  // ── fallback به static data اگه API خطا داد ───────────────────────────────
  const brandSlugToName: Record<string, string> = {
    'siemens': 'Siemens', 'endress-hauser': 'Endress+Hauser', 'honeywell': 'Honeywell',
    'emerson': 'Emerson', 'abb': 'ABB', 'rockwell': 'Rockwell Automation',
    'peak': 'Peak Scientific', 'drager': 'Dräger', 'knf': 'KNF',
    'yokogawa': 'Yokogawa', 'brooks': 'Brooks Instrument', 'schneider': 'Schneider Electric',
  };
  const brandName = slug ? brandSlugToName[slug] : null;
  const staticBrandProducts = brandName ? staticProducts.filter(p => p.brand === brandName) : [];

  const sourceProducts = apiProducts.length > 0 ? apiProducts : staticBrandProducts;

  const filteredProducts = sourceProducts.filter((product: any) => {
    const matchesSearch = !searchQuery ||
      product.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      product.model.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesCategory = selectedCategory === 'all' || product.category === selectedCategory;
    const matchesStock = !showInStockOnly || product.inStock || product.in_stock;
    return matchesSearch && matchesCategory && matchesStock;
  });

  // client-side pagination فقط برای static fallback
  const paginatedProducts = apiProducts.length > 0
    ? filteredProducts
    : filteredProducts.slice((currentPage - 1) * ITEMS_PER_PAGE, currentPage * ITEMS_PER_PAGE);
  const effectiveTotalPages = apiProducts.length > 0 ? totalPages : Math.ceil(filteredProducts.length / ITEMS_PER_PAGE);
  const effectiveTotalItems = apiProducts.length > 0 ? totalItems : filteredProducts.length;

  const handleAddToRFQ = (product: any) => {
    addProduct({ id: product.id, name: product.name, type: product.type, grade: product.model });
    toast({ title: 'به سبد استعلام اضافه شد', description: `${product.name} به لیست استعلام شما اضافه شد.` });
  };

  const availableCategories = [...new Set(sourceProducts.map((p: any) => p.category))];
  const enrichedContent = `${brandData.content}${generateSupportiveSeoHtml(brandData.name, brandData.keywords)}`;
  const breadcrumbSchema = generateBreadcrumbSchema([
    { name: 'خانه', url: 'https://toolmaster.com' },
    { name: 'برندها', url: 'https://toolmaster.com/brands' },
    { name: brandData.name, url: `https://toolmaster.com/brands/${slug}` },
  ]);

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
            return (<Button key={page} variant={currentPage === page ? 'default' : 'outline'} size="sm" onClick={() => setCurrentPage(page)} className="h-8 w-8 p-0">{page}</Button>);
          })}
        </div>
        <Button variant="outline" size="sm" onClick={() => setCurrentPage(p => Math.min(effectiveTotalPages, p + 1))} disabled={currentPage === effectiveTotalPages} className="gap-1">
          بعدی<ChevronLeft className="h-4 w-4" />
        </Button>
      </div>
    );
  };

  const FilterPanel = () => (
    <div className="space-y-6" dir="rtl">
      <div>
        <h3 className="font-bold mb-3 text-sm text-right">دسته‌بندی</h3>
        <Select value={selectedCategory} onValueChange={setSelectedCategory}>
          <SelectTrigger><SelectValue placeholder="همه" /></SelectTrigger>
          <SelectContent>
            <SelectItem value="all">همه دسته‌بندی‌ها</SelectItem>
            {availableCategories.map(cat => (
              <SelectItem key={cat as string} value={cat as string}>
                {productCategories[cat as keyof typeof productCategories]?.label || (cat as string)}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
      </div>
      <div className="flex items-center gap-2">
        <Checkbox id="instock-brand" checked={showInStockOnly} onCheckedChange={(c) => setShowInStockOnly(c as boolean)} />
        <Label htmlFor="instock-brand" className="cursor-pointer text-sm">فقط کالای موجود</Label>
      </div>
    </div>
  );

  return (
    <div className="min-h-screen bg-background" dir="rtl">
      <SEO title={brandData.metaTitle} description={brandData.metaDescription} keywords={brandData.keywords} structuredData={breadcrumbSchema} />

      <section className="bg-gradient-to-br from-[hsl(var(--hero-gradient-start))] to-[hsl(var(--hero-gradient-end))] text-primary-foreground py-10 border-b border-border">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <h1 className="text-3xl md:text-4xl font-black mb-3 text-right">{brandData.heroTitle}</h1>
          <p className="text-lg text-primary-foreground/80 max-w-3xl text-right">{brandData.heroDescription}</p>
          {apiError && <p className="text-sm text-yellow-300 mt-2">⚠️ اتصال به API برقرار نشد — نمایش داده‌های پیش‌فرض</p>}
        </div>
      </section>

      <section className="py-4 bg-background border-b border-border sticky top-16 z-40 backdrop-blur bg-background/95">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex gap-4">
            <div className="flex-1 relative">
              <Search className="absolute right-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input placeholder="جستجو محصولات این برند..." value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)} className="pr-10 text-right" />
            </div>
            <Sheet>
              <SheetTrigger asChild>
                <Button variant="outline" className="lg:hidden"><Filter className="h-4 w-4 ml-2" />فیلترها</Button>
              </SheetTrigger>
              <SheetContent side="right">
                <SheetHeader><SheetTitle className="text-right">فیلترها</SheetTitle></SheetHeader>
                <div className="mt-6"><FilterPanel /></div>
              </SheetContent>
            </Sheet>
          </div>
        </div>
      </section>

      <section className="py-8">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex gap-8">
            <aside className="hidden lg:block w-72 flex-shrink-0">
              <div className="sticky top-32">
                <Card className="border-border/60">
                  <CardHeader><CardTitle className="text-base flex items-center gap-2"><Filter className="h-4 w-4" />فیلتر</CardTitle></CardHeader>
                  <CardContent><FilterPanel /></CardContent>
                </Card>
              </div>
            </aside>

            <div className="flex-1">
              <div className="mb-6">
                <p className="text-sm text-muted-foreground text-right">
                  نمایش {effectiveTotalItems} محصول {brandData.name}
                  {apiProducts.length > 0 && <span className="mr-2 text-green-600 text-xs">✓ از پایگاه داده</span>}
                </p>
              </div>

              {apiLoading && (
                <div className="flex items-center justify-center py-8">
                  <div className="animate-spin h-6 w-6 border-4 border-primary border-t-transparent rounded-full ml-3"></div>
                  <span className="text-muted-foreground text-sm">در حال بارگذاری...</span>
                </div>
              )}

              <div className="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
                {paginatedProducts.map((product: any, index: number) => {
                  const imgSrc = resolveImage(product.image);
                  return (
                    <motion.div key={product.id} initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: index * 0.04 }}>
                      <Card className="h-full hover:shadow-lg transition-all flex flex-col border-border/60 hover:border-accent/30 overflow-hidden">
                        {imgSrc && (
                          <div className="h-40 bg-muted/20 overflow-hidden">
                            <img src={imgSrc} alt={product.name} className="h-full w-full object-contain p-3" loading="lazy" />
                          </div>
                        )}
                        <CardHeader className="flex-1">
                          <div className="flex items-start justify-between mb-2">
                            <div className="flex gap-2">
                              {(product.inStock || product.in_stock) ? (
                                <Badge variant="secondary" className="text-xs"><CheckCircle className="h-3 w-3 ml-1" />موجود</Badge>
                              ) : (
                                <Badge variant="outline" className="text-xs text-muted-foreground">سفارشی</Badge>
                              )}
                            </div>
                            <Button variant="ghost" size="icon" className="h-8 w-8 text-muted-foreground hover:text-accent"><Download className="h-4 w-4" /></Button>
                          </div>
                          <CardTitle className="text-base leading-snug text-right">{product.name}</CardTitle>
                          <CardDescription className="text-right">
                            <span className="font-mono text-xs ltr">{product.model}</span>
                            <span className="mx-2 text-border">|</span>
                            <span className="text-xs">{product.brand}</span>
                          </CardDescription>
                          {product.description && (<p className="text-xs text-muted-foreground mt-2 line-clamp-2 text-right leading-relaxed">{product.description}</p>)}
                        </CardHeader>
                        <CardContent className="pt-0">
                          <div className="flex gap-2">
                            <Button asChild variant="outline" className="flex-1" size="sm"><Link to={`/products/${product.id}`}>جزئیات فنی</Link></Button>
                            <Button onClick={() => handleAddToRFQ(product)} size="sm" variant="cta" className="flex-1">استعلام قیمت</Button>
                          </div>
                        </CardContent>
                      </Card>
                    </motion.div>
                  );
                })}
              </div>

              <Pagination />

              {paginatedProducts.length === 0 && !apiLoading && (
                <div className="text-center py-12">
                  <Settings className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                  <h3 className="text-lg font-bold mb-2">محصولی یافت نشد</h3>
                  <p className="text-muted-foreground">فیلترها یا عبارت جستجو را تغییر دهید</p>
                </div>
              )}

              <div className="mt-16 prose prose-lg max-w-none dark:prose-invert text-right" dir="rtl">
                <div dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(enrichedContent.replace(/^## (.+)$/gm, '<h2>$1</h2>').replace(/^### (.+)$/gm, '<h3>$1</h3>').replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>').replace(/\n\n/g, '</p><p>').replace(/^(?!<[hp])(.+)$/gm, '<p>$1</p>')) }} />
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}
