import DOMPurify from 'dompurify';
import { useState, useEffect } from 'react';
import { Link, useParams, useSearchParams, Navigate } from 'react-router-dom';
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
import { Search, Filter, Settings, CheckCircle, Download } from 'lucide-react';
import { useRFQ } from '@/contexts/RFQContext';
import { useToast } from '@/hooks/use-toast';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { products, productBrands, type Product } from '@/data/products';
import { equipmentTypes } from '@/data/product-taxonomy';
import { categorySEOData } from '@/data/category-seo';
import { categoryUIData } from '@/data/category-ui';
import { generateSupportiveSeoHtml } from '@/lib/seo-content';

// Product images map
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
  'hmi-siemens': prodHmiSiemens
};

export default function CategoryPage() {
  const { slug } = useParams<{slug: string;}>();
  const [searchParams] = useSearchParams();
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedType, setSelectedType] = useState<string>('all');
  const [selectedBrand, setSelectedBrand] = useState<string>('all');
  const [showInStockOnly, setShowInStockOnly] = useState(false);
  const { addProduct } = useRFQ();
  const { toast } = useToast();

  const categoryData = slug ? categorySEOData[slug] : null;

  useEffect(() => {
    const type = searchParams.get('type');
    const brand = searchParams.get('brand');
    if (type) setSelectedType(type);
    if (brand) setSelectedBrand(brand);
  }, [searchParams]);

  if (!categoryData) return <Navigate to="/products" replace />;

  const categoryUi = categoryUIData[categoryData.slug];
  const categoryProducts = products.filter((p) => p.category === categoryData.filterCategory);

  const filteredProducts = categoryProducts.filter((product) => {
    const matchesSearch = product.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
    product.model.toLowerCase().includes(searchQuery.toLowerCase()) ||
    product.brand.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesType = selectedType === 'all' || product.type === selectedType;
    const matchesBrand = selectedBrand === 'all' || product.brand === selectedBrand;
    const matchesStock = !showInStockOnly || product.inStock;
    return matchesSearch && matchesType && matchesBrand && matchesStock;
  });

  const handleAddToRFQ = (product: Product) => {
    addProduct({ id: product.id, name: product.name, type: product.type, grade: product.model });
    toast({ title: "به سبد استعلام اضافه شد", description: `${product.name} به لیست استعلام شما اضافه شد.` });
  };

  const availableTypes = Object.entries(equipmentTypes).filter(([_, t]) => t.category === categoryData.filterCategory);
  const availableBrands = [...new Set(categoryProducts.map((p) => p.brand))].sort();

  const enrichedContent = `${categoryData.content}${generateSupportiveSeoHtml(categoryData.title, categoryData.keywords)}`;

  const breadcrumbSchema = generateBreadcrumbSchema([
  { name: 'خانه', url: 'https://toolmaster.com' },
  { name: 'محصولات', url: 'https://toolmaster.com/products' },
  { name: categoryData.title, url: `https://toolmaster.com/products/category/${slug}` }]
  );

  return (
    <div className="min-h-screen bg-background" dir="rtl">
      <SEO
        title={categoryData.metaTitle}
        description={categoryData.metaDescription}
        keywords={categoryData.keywords}
        structuredData={breadcrumbSchema} />


      {/* Hero */}
      <section className="bg-gradient-to-br from-[hsl(var(--hero-gradient-start))] to-[hsl(var(--hero-gradient-end))] text-primary-foreground py-10 border-b border-border">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <h1 className="text-3xl md:text-4xl font-black mb-3 text-right">{categoryData.heroTitle}</h1>
          <p className="text-lg text-primary-foreground/80 max-w-3xl text-right">{categoryData.heroDescription}</p>
        </div>
      </section>


      {/* Category Intro Card */}
      <section className="py-7 border-b border-border bg-muted/20">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="rounded-2xl border border-border/60 bg-background overflow-hidden">
            <div className="p-5 md:p-7">
              <h2 className="text-2xl font-extrabold text-right mb-3">زیرمجموعه‌های {categoryData.title}</h2>
              <p className="text-sm md:text-base text-muted-foreground text-right leading-8 mb-5">
                این دسته‌بندی شامل زیرگروه‌های تخصصی است. برای دسترسی سریع‌تر، روی هر زیرمجموعه کلیک کنید تا همان محصولات با فیلتر آماده نمایش داده شوند.
              </p>
              <div className="grid gap-3 sm:grid-cols-2 md:grid-cols-3">
                {(categoryUi?.subcategories ?? []).map((sub) =>
                <Link
                  key={sub.id}
                  to={sub.type ? `/products/category/${categoryData.slug}/${sub.type}` : `/products/category/${categoryData.slug}`}
                  className="group rounded-lg border border-border/60 overflow-hidden hover:border-accent/40 hover:shadow-md transition-all">

                    {sub.image &&
                  <div className="h-32 overflow-hidden bg-muted/20">
                        <img src={sub.image} alt={sub.imageAlt || sub.label} className="h-full w-full object-contain p-2 group-hover:scale-105 transition-transform duration-300" loading="lazy" />
                      </div>
                  }
                    <div className="p-3 text-right">
                      <h3 className="font-bold text-sm mb-1">{sub.label}</h3>
                      <p className="text-xs text-muted-foreground leading-6">{sub.description}</p>
                    </div>
                  </Link>
                )}
              </div>
            </div>
          </div>
        </div>
      </section>
      {/* Search */}
      <section className="py-4 bg-background border-b border-border sticky top-16 z-40 backdrop-blur bg-background/95">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex gap-4">
            <div className="flex-1 relative">
              <Search className="absolute right-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input placeholder="جستجو بر اساس نام، مدل یا برند..." value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)} className="pr-10 text-right" />
            </div>
            <Sheet>
              <SheetTrigger asChild>
                <Button variant="outline" className="lg:hidden"><Filter className="h-4 w-4 ml-2" />فیلترها</Button>
              </SheetTrigger>
              <SheetContent side="right">
                <SheetHeader><SheetTitle className="text-right">فیلترهای فنی</SheetTitle></SheetHeader>
                <div className="mt-6"><FilterSidebar availableTypes={availableTypes} availableBrands={availableBrands} selectedType={selectedType} setSelectedType={setSelectedType} selectedBrand={selectedBrand} setSelectedBrand={setSelectedBrand} showInStockOnly={showInStockOnly} setShowInStockOnly={setShowInStockOnly} /></div>
              </SheetContent>
            </Sheet>
          </div>
        </div>
      </section>

      {/* Content */}
      <section className="py-8">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex gap-8">
            <aside className="hidden lg:block w-72 flex-shrink-0">
              <div className="sticky top-32">
                <Card className="border-border/60">
                  <CardHeader><CardTitle className="text-base flex items-center gap-2"><Filter className="h-4 w-4" />فیلتر فنی</CardTitle></CardHeader>
                  <CardContent>
                    <FilterSidebar availableTypes={availableTypes} availableBrands={availableBrands} selectedType={selectedType} setSelectedType={setSelectedType} selectedBrand={selectedBrand} setSelectedBrand={setSelectedBrand} showInStockOnly={showInStockOnly} setShowInStockOnly={setShowInStockOnly} />
                  </CardContent>
                </Card>
              </div>
            </aside>

            <div className="flex-1">
              <div className="mb-6">
                <p className="text-sm text-muted-foreground text-right">نمایش {filteredProducts.length} محصول</p>
              </div>

              <div className="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
                {filteredProducts.map((product, index) =>
                <motion.div key={product.id} initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: index * 0.04 }}>
                    <Card className="h-full hover:shadow-lg transition-all flex flex-col border-border/60 hover:border-accent/30 overflow-hidden">
                      {product.image && imageMap[product.image] &&
                    <div className="h-40 bg-muted/20 overflow-hidden">
                          <img src={imageMap[product.image]} alt={product.name} className="h-full w-full object-contain p-3" loading="lazy" />
                        </div>
                    }
                      <CardHeader className="flex-1">
                        <div className="flex items-start justify-between mb-2">
                          <div className="flex gap-2">
                            {product.inStock ?
                          <Badge variant="secondary" className="text-xs"><CheckCircle className="h-3 w-3 ml-1" />موجود</Badge> :

                          <Badge variant="outline" className="text-xs text-muted-foreground">سفارشی</Badge>
                          }
                          </div>
                          <Button variant="ghost" size="icon" className="h-8 w-8 text-muted-foreground hover:text-accent" title="دانلود دیتاشیت"><Download className="h-4 w-4" /></Button>
                        </div>
                        <CardTitle className="text-base leading-snug text-right">{product.name}</CardTitle>
                        <CardDescription className="text-right">
                          <span className="font-mono text-xs ltr">{product.model}</span>
                          <span className="mx-2 text-border">|</span>
                          <span className="text-xs">{product.brand}</span>
                        </CardDescription>
                        {product.description && <p className="text-xs text-muted-foreground mt-2 line-clamp-2 text-right leading-relaxed">{product.description}</p>}
                      </CardHeader>
                      <CardContent className="pt-0">
                        <div className="flex gap-2">
                          <Button asChild variant="outline" className="flex-1" size="sm"><Link to={`/products/${product.id}`}>جزئیات فنی</Link></Button>
                          <Button onClick={() => handleAddToRFQ(product)} size="sm" variant="cta" className="flex-1">استعلام قیمت</Button>
                        </div>
                      </CardContent>
                    </Card>
                  </motion.div>
                )}
              </div>

              {filteredProducts.length === 0 &&
              <div className="text-center py-12">
                  <Settings className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                  <h3 className="text-lg font-bold mb-2">محصولی یافت نشد</h3>
                  <p className="text-muted-foreground">فیلترها یا عبارت جستجو را تغییر دهید</p>
                </div>
              }

              {/* SEO Content */}
              <div className="mt-12 rounded-xl border border-border/60 bg-muted/10 p-6 md:p-8">
                <div className="prose prose-sm max-w-none dark:prose-invert text-right
                  prose-headings:text-foreground prose-headings:font-extrabold
                  prose-h2:text-lg prose-h2:mt-6 prose-h2:mb-3 prose-h2:border-b prose-h2:border-border/40 prose-h2:pb-2
                  prose-h3:text-base prose-h3:mt-4 prose-h3:mb-2
                  prose-p:text-muted-foreground prose-p:text-sm prose-p:leading-7
                  prose-strong:text-foreground/90
                  prose-li:text-sm prose-li:text-muted-foreground" dir="rtl">
                  <div dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(enrichedContent.replace(/^## (.+)$/gm, '<h2>$1</h2>').replace(/^### (.+)$/gm, '<h3>$1</h3>').replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>').replace(/\n\n/g, '</p><p>').replace(/^(?!<[hp])(.+)$/gm, '<p>$1</p>')) }} />
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>);

}

function FilterSidebar({ availableTypes, availableBrands, selectedType, setSelectedType, selectedBrand, setSelectedBrand, showInStockOnly, setShowInStockOnly








}: {availableTypes: [string, {label: string;category: string;fullName: string;}][];availableBrands: string[];selectedType: string;setSelectedType: (v: string) => void;selectedBrand: string;setSelectedBrand: (v: string) => void;showInStockOnly: boolean;setShowInStockOnly: (v: boolean) => void;}) {
  return (
    <div className="space-y-6" dir="rtl">
      <div>
        <h3 className="font-bold mb-3 text-sm text-right">نوع تجهیزات</h3>
        <Select value={selectedType} onValueChange={setSelectedType}>
          <SelectTrigger><SelectValue placeholder="همه انواع" /></SelectTrigger>
          <SelectContent>
            <SelectItem value="all">همه انواع</SelectItem>
            {availableTypes.map(([key, type]) => <SelectItem key={key} value={key}>{type.label}</SelectItem>)}
          </SelectContent>
        </Select>
      </div>
      <div>
        <h3 className="font-bold mb-3 text-sm text-right">برند</h3>
        <Select value={selectedBrand} onValueChange={setSelectedBrand}>
          <SelectTrigger><SelectValue placeholder="همه برندها" /></SelectTrigger>
          <SelectContent>
            <SelectItem value="all">همه برندها</SelectItem>
            {availableBrands.map((brand) => <SelectItem key={brand} value={brand}>{brand}</SelectItem>)}
          </SelectContent>
        </Select>
      </div>
      



    </div>);

}