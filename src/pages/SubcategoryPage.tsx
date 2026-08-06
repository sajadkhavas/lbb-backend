import DOMPurify from 'dompurify';
import { useState } from 'react';
import { Link, useParams, Navigate } from 'react-router-dom';
import { motion } from 'framer-motion';
import { SEO } from '@/components/SEO';
import { generateBreadcrumbSchema } from '@/lib/structured-data';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Search, CheckCircle, Download, Settings } from 'lucide-react';
import { useRFQ } from '@/contexts/RFQContext';
import { useToast } from '@/hooks/use-toast';
import { products, type Product } from '@/data/products';
import { subcategorySEOData } from '@/data/subcategory-seo';
import { categorySEOData } from '@/data/category-seo';
import { generateSupportiveSeoHtml } from '@/lib/seo-content';

// Reuse image map
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

export default function SubcategoryPage() {
  const { slug, subSlug } = useParams<{ slug: string; subSlug: string }>();
  const [searchQuery, setSearchQuery] = useState('');
  const { addProduct } = useRFQ();
  const { toast } = useToast();

  const subData = subSlug ? subcategorySEOData[subSlug] : null;
  const parentData = slug ? categorySEOData[slug] : null;

  if (!subData || !parentData) return <Navigate to="/products" replace />;

  const filteredProducts = products
    .filter(p => p.type === subData.filterType)
    .filter(p =>
      p.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      p.model.toLowerCase().includes(searchQuery.toLowerCase()) ||
      p.brand.toLowerCase().includes(searchQuery.toLowerCase())
    );

  const handleAddToRFQ = (product: Product) => {
    addProduct({ id: product.id, name: product.name, type: product.type, grade: product.model });
    toast({ title: "به سبد استعلام اضافه شد", description: `${product.name} به لیست استعلام شما اضافه شد.` });
  };

  const enrichedContent = `${subData.content}${generateSupportiveSeoHtml(subData.title, subData.keywords)}`;

  const breadcrumbSchema = generateBreadcrumbSchema([
    { name: 'خانه', url: 'https://toolmaster.com' },
    { name: 'محصولات', url: 'https://toolmaster.com/products' },
    { name: parentData.title, url: `https://toolmaster.com/products/category/${slug}` },
    { name: subData.title, url: `https://toolmaster.com/products/category/${slug}/${subSlug}` },
  ]);

  return (
    <div className="min-h-screen bg-background" dir="rtl">
      <SEO title={subData.metaTitle} description={subData.metaDescription} keywords={subData.keywords} structuredData={breadcrumbSchema} />

      {/* Hero */}
      <section className="bg-gradient-to-br from-[hsl(var(--hero-gradient-start))] to-[hsl(var(--hero-gradient-end))] text-primary-foreground py-10 border-b border-border">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <h1 className="text-3xl md:text-4xl font-black mb-3 text-right">{subData.heroTitle}</h1>
          <p className="text-lg text-primary-foreground/80 max-w-3xl text-right">{subData.heroDescription}</p>
        </div>
      </section>

      {/* SEO Content */}
      <section className="py-8 border-b border-border bg-muted/20">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="rounded-2xl border border-border/60 bg-background p-5 md:p-8">
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
      </section>

      {/* Search + Products */}
      <section className="py-4 bg-background border-b border-border sticky top-16 z-40 backdrop-blur bg-background/95">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="relative">
            <Search className="absolute right-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
            <Input placeholder="جستجو بر اساس نام، مدل یا برند..." value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)} className="pr-10 text-right" />
          </div>
        </div>
      </section>

      <section className="py-8">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <p className="text-sm text-muted-foreground text-right mb-6">نمایش {filteredProducts.length} محصول</p>
          <div className="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
            {filteredProducts.map((product, index) => (
              <motion.div key={product.id} initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: index * 0.04 }}>
                <Card className="h-full hover:shadow-lg transition-all flex flex-col border-border/60 hover:border-accent/30 overflow-hidden">
                  {product.image && imageMap[product.image] && (
                    <div className="h-40 bg-muted/20 overflow-hidden">
                      <img src={imageMap[product.image]} alt={product.name} className="h-full w-full object-contain p-3" loading="lazy" />
                    </div>
                  )}
                  <CardHeader className="flex-1">
                    <div className="flex items-start justify-between mb-2">
                      <div className="flex gap-2">
                        {product.inStock ? (
                          <Badge variant="secondary" className="text-xs"><CheckCircle className="h-3 w-3 ml-1" />موجود</Badge>
                        ) : (
                          <Badge variant="outline" className="text-xs text-muted-foreground">سفارشی</Badge>
                        )}
                      </div>
                      <Button variant="ghost" size="icon" className="h-8 w-8 text-muted-foreground hover:text-accent" title="دانلود دیتاشیت"><Download className="h-4 w-4" /></Button>
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
            ))}
          </div>
          {filteredProducts.length === 0 && (
            <div className="text-center py-12">
              <Settings className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
              <h3 className="text-lg font-bold mb-2">محصولی یافت نشد</h3>
              <p className="text-muted-foreground">عبارت جستجو را تغییر دهید</p>
            </div>
          )}
        </div>
      </section>
    </div>
  );
}
