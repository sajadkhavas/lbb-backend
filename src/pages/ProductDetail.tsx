import { useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { motion } from 'framer-motion';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { ArrowRight, Download, Shield, Truck, FileText, CheckCircle2, AlertTriangle, Award } from 'lucide-react';
import { useRFQ } from '@/contexts/RFQContext';
import { useToast } from '@/hooks/use-toast';
import { products, type Product } from '@/data/products';
import { equipmentTypes } from '@/data/product-taxonomy';


import prodNitrogenPeak from '@/assets/products/nitrogen-generator-peak.jpg';
import prodFlowmeterEndress from '@/assets/products/flowmeter-endress.jpg';
import prodGasDetectorHoneywell from '@/assets/products/gas-detector-honeywell.jpg';
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

export default function ProductDetail() {
  const { id } = useParams();
  const { addProduct } = useRFQ();
  const { toast } = useToast();
  const [quantity, setQuantity] = useState('');

  // Find product from data
  const foundProduct = products.find(p => p.id === id);

  const product: Product = foundProduct || {
    id: 'ng-500',
    name: 'ژنراتور نیتروژن NG-500',
    model: 'NG-500',
    type: 'nitrogen-gen',
    category: 'gas-generators',
    brand: 'Parker Hannifin',
    country: 'US',
    usage: ['research', 'industrial'],
    priceRange: 'mid',
    applications: ['GC-MS', 'LCMS', 'آنالیز عنصری', 'بلانکتینگ'],
    inStock: true,
    description: 'ژنراتور نیتروژن با خلوص بالا برای کاربردهای آزمایشگاهی و صنعتی',
    specs: {
      purity: '99.999%',
      flowRate: '0-500 mL/min',
      pressure: '0-100 psi',
      accuracy: '±0.1%',
    }
  };

  const faqs = [
    {
      question: 'حداقل سفارش چقدر است؟',
      answer: 'حداقل سفارش ۱ دستگاه است. برای سفارش‌های عمده تخفیف ویژه ارائه می‌شود. برای اطلاع از قیمت تماس بگیرید.'
    },
    {
      question: 'آیا گارانتی و خدمات پس از فروش دارد؟',
      answer: 'بله، تمامی تجهیزات با گارانتی رسمی ارائه می‌شوند. خدمات پس از فروش شامل نصب، آموزش، کالیبراسیون و تعمیرات می‌باشد.'
    },
    {
      question: 'آیا دیتاشیت و مستندات فنی موجود است؟',
      answer: 'بله، برای تمامی محصولات دیتاشیت فنی (TDS)، گواهینامه‌های کالیبراسیون و مستندات انطباق با استانداردها ارائه می‌شود.'
    },
    {
      question: 'آیا نصب و آموزش انجام می‌دهید؟',
      answer: 'بله، تیم مهندسی ما نصب و راه‌اندازی تجهیزات و آموزش کامل اپراتورها را در محل مشتری انجام می‌دهد.'
    },
    {
      question: 'زمان تحویل چقدر است؟',
      answer: 'برای تجهیزات موجود در انبار، ۳ تا ۷ روز کاری. برای تجهیزات سفارشی، بسته به مدل، ۴ تا ۱۲ هفته زمان لازم است.'
    }
  ];

  const handleAddToRFQ = () => {
    addProduct({
      id: product.id,
      name: product.name,
      type: product.type,
      grade: product.model
    });
    toast({
      title: "به سبد استعلام اضافه شد",
      description: `${product.name} به لیست استعلام شما اضافه شد.`,
    });
  };

  const typeName = equipmentTypes[product.type as keyof typeof equipmentTypes]?.label || product.type;

  return (
    <div className="min-h-screen bg-background" dir="rtl">
      {/* Breadcrumb */}
      <section className="bg-muted/50 py-4 border-b border-border">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <Button asChild variant="ghost" size="sm">
            <Link to={`/products?category=${product.category}&type=${product.type}`}>
              <ArrowRight className="h-4 w-4 ml-2" />
              بازگشت به محصولات
            </Link>
          </Button>
        </div>
      </section>


      {/* Product Image */}
      {(() => {
        const imgMap: Record<string, string> = {
          'nitrogen-generator-peak': prodNitrogenPeak,
          'flowmeter-endress': prodFlowmeterEndress,
          'gas-detector-honeywell': prodGasDetectorHoneywell,
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
        const imgSrc = product.image ? imgMap[product.image] : null;
        if (!imgSrc) return null;
        return (
          <section className="py-6 border-b border-border bg-muted/10">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
              <div className="max-w-lg mx-auto bg-background rounded-xl border border-border/60 overflow-hidden p-6">
                <img src={imgSrc} alt={product.name} className="w-full h-auto max-h-80 object-contain" />
              </div>
            </div>
          </section>
        );
      })()}
      {/* Product Header */}
      <section className="py-8 border-b border-border">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex flex-col lg:flex-row gap-8">
            <div className="flex-1">
              <div className="flex flex-wrap gap-2 mb-4">
                {product.inStock ? (
                  <Badge variant="secondary">
                    <CheckCircle2 className="h-3 w-3 ml-1" />
                    موجود در انبار
                  </Badge>
                ) : (
                  <Badge variant="outline">سفارشی</Badge>
                )}
                <Badge variant="outline" className="border-primary text-primary font-mono ltr">
                  {product.brand}
                </Badge>
                {product.specs?.certification && (
                  <Badge variant="outline" className="border-accent text-accent">
                    <Shield className="h-3 w-3 ml-1" />
                    {product.specs.certification}
                  </Badge>
                )}
              </div>
              <h1 className="text-3xl md:text-4xl font-black mb-2 text-foreground">{product.name}</h1>
              <p className="text-lg text-muted-foreground font-mono mb-4 ltr">{product.model}</p>
              <p className="text-muted-foreground max-w-3xl">{product.description}</p>

              <div className="mt-6 flex flex-wrap gap-3">
                <Button onClick={handleAddToRFQ} size="lg" variant="cta">
                  استعلام قیمت
                </Button>
                <Button variant="outline" size="lg">
                  <Download className="h-4 w-4 ml-2" />
                  دانلود دیتاشیت
                </Button>
              </div>
            </div>

            {/* Quick RFQ Panel */}
            <Card className="lg:w-96 flex-shrink-0 border-border/60">
              <CardHeader>
                <CardTitle>استعلام سریع قیمت</CardTitle>
                <CardDescription>پاسخ در کمتر از ۲۴ ساعت</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <Label htmlFor="name">نام و نام خانوادگی *</Label>
                  <Input id="name" placeholder="نام کامل" />
                </div>
                <div>
                  <Label htmlFor="email">ایمیل *</Label>
                  <Input id="email" type="email" placeholder="example@company.com" className="ltr text-left" dir="ltr" style={{ textAlign: 'left' }} />
                </div>
                <div>
                  <Label htmlFor="company">شرکت/سازمان *</Label>
                  <Input id="company" placeholder="نام شرکت" />
                </div>
                <div>
                  <Label htmlFor="quantity">تعداد</Label>
                  <Input 
                    id="quantity" 
                    placeholder="مثلاً: ۲ دستگاه" 
                    value={quantity}
                    onChange={(e) => setQuantity(e.target.value)}
                  />
                </div>
                <div>
                  <Label htmlFor="notes">توضیحات</Label>
                  <Textarea id="notes" placeholder="مشخصات فنی خاص، تاییدیه‌ها، زمان تحویل..." rows={3} />
                </div>
                <Button variant="cta" className="w-full">ارسال استعلام</Button>
                <p className="text-xs text-muted-foreground text-center">
                  اطلاعات شما محرمانه باقی خواهد ماند.
                </p>
              </CardContent>
            </Card>
          </div>
        </div>
      </section>

      {/* Product Details Tabs */}
      <section className="py-12">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <Tabs defaultValue="overview" className="w-full">
            <TabsList className="w-full justify-start mb-8 flex-wrap h-auto" dir="rtl">
              <TabsTrigger value="overview">نمای کلی</TabsTrigger>
              <TabsTrigger value="specifications">مشخصات فنی</TabsTrigger>
              <TabsTrigger value="applications">کاربردها</TabsTrigger>
              <TabsTrigger value="documents">مستندات</TabsTrigger>
              <TabsTrigger value="faq">سوالات متداول</TabsTrigger>
            </TabsList>

            <TabsContent value="overview" className="space-y-6" dir="rtl">
              <Card className="border-border/60">
                <CardHeader className="text-right">
                  <CardTitle>معرفی محصول</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4 text-right">
                  <p className="text-muted-foreground">{product.description}</p>
                  <div className="grid md:grid-cols-2 gap-4 mt-6">
                    {[
                      { title: 'کیفیت تضمین‌شده', desc: 'تجهیزات اصل با گارانتی رسمی و سریال نامبر معتبر' },
                      { title: 'پشتیبانی فنی', desc: 'تیم مهندسی آماده پاسخگویی به سوالات فنی شما' },
                      { title: 'تحویل سریع', desc: 'ارسال سریع برای تجهیزات موجود در انبار' },
                      { title: 'نصب و آموزش', desc: 'نصب تخصصی و آموزش اپراتور در محل مشتری' },
                    ].map(item => (
                      <div key={item.title} className="flex items-start">
                        <CheckCircle2 className="h-5 w-5 text-accent ml-3 mt-0.5 flex-shrink-0" />
                        <div>
                          <p className="font-bold text-sm">{item.title}</p>
                          <p className="text-sm text-muted-foreground">{item.desc}</p>
                        </div>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="specifications" className="space-y-6" dir="rtl">
              <Card className="border-border/60">
                <CardHeader className="text-right">
                  <CardTitle>مشخصات فنی</CardTitle>
                  <CardDescription>پارامترهای کلیدی و مقادیر عملکردی</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="overflow-x-auto">
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead className="font-bold">پارامتر</TableHead>
                          <TableHead className="font-bold">مقدار</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        <TableRow>
                          <TableCell className="font-medium">مدل</TableCell>
                          <TableCell className="font-mono ltr">{product.model}</TableCell>
                        </TableRow>
                        <TableRow>
                          <TableCell className="font-medium">برند</TableCell>
                          <TableCell>{product.brand}</TableCell>
                        </TableRow>
                        <TableRow>
                          <TableCell className="font-medium">نوع</TableCell>
                          <TableCell>{typeName}</TableCell>
                        </TableRow>
                        {product.specs?.purity && (
                          <TableRow>
                            <TableCell className="font-medium">خلوص گاز</TableCell>
                            <TableCell className="font-mono ltr">{product.specs.purity}</TableCell>
                          </TableRow>
                        )}
                        {product.specs?.flowRate && (
                          <TableRow>
                            <TableCell className="font-medium">دبی / ظرفیت</TableCell>
                            <TableCell className="font-mono ltr">{product.specs.flowRate}</TableCell>
                          </TableRow>
                        )}
                        {product.specs?.pressure && (
                          <TableRow>
                            <TableCell className="font-medium">فشار</TableCell>
                            <TableCell className="font-mono ltr">{product.specs.pressure}</TableCell>
                          </TableRow>
                        )}
                        {product.specs?.accuracy && (
                          <TableRow>
                            <TableCell className="font-medium">دقت</TableCell>
                            <TableCell className="font-mono ltr">{product.specs.accuracy}</TableCell>
                          </TableRow>
                        )}
                        {product.specs?.range && (
                          <TableRow>
                            <TableCell className="font-medium">رنج اندازه‌گیری</TableCell>
                            <TableCell className="font-mono ltr">{product.specs.range}</TableCell>
                          </TableRow>
                        )}
                        {product.specs?.resolution && (
                          <TableRow>
                            <TableCell className="font-medium">رزولوشن</TableCell>
                            <TableCell className="font-mono ltr">{product.specs.resolution}</TableCell>
                          </TableRow>
                        )}
                        {product.specs?.voltage && (
                          <TableRow>
                            <TableCell className="font-medium">ولتاژ</TableCell>
                            <TableCell className="font-mono ltr">{product.specs.voltage}</TableCell>
                          </TableRow>
                        )}
                        {product.specs?.protocol && (
                          <TableRow>
                            <TableCell className="font-medium">پروتکل ارتباطی</TableCell>
                            <TableCell className="font-mono ltr">{product.specs.protocol}</TableCell>
                          </TableRow>
                        )}
                        {product.specs?.ioCount && (
                          <TableRow>
                            <TableCell className="font-medium">تعداد I/O</TableCell>
                            <TableCell>{product.specs.ioCount}</TableCell>
                          </TableRow>
                        )}
                        {product.specs?.gasType && (
                          <TableRow>
                            <TableCell className="font-medium">نوع گاز</TableCell>
                            <TableCell className="font-mono ltr">{product.specs.gasType}</TableCell>
                          </TableRow>
                        )}
                        {product.specs?.certification && (
                          <TableRow>
                            <TableCell className="font-medium">تاییدیه‌ها</TableCell>
                            <TableCell>{product.specs.certification}</TableCell>
                          </TableRow>
                        )}
                      </TableBody>
                    </Table>
                  </div>
                  <div className="mt-6 p-4 bg-muted/50 rounded-lg border border-border/60">
                    <p className="text-sm text-muted-foreground">
                      <strong>توجه:</strong> مقادیر فوق بر اساس شرایط استاندارد آزمایشگاهی هستند. برای شرایط خاص با تیم فنی تماس بگیرید.
                    </p>
                  </div>
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="applications" className="space-y-6" dir="rtl">
              <Card className="border-border/60">
                <CardHeader className="text-right">
                  <CardTitle>کاربردها و صنایع</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="grid sm:grid-cols-2 gap-3">
                    {product.applications.map(app => (
                      <div key={app} className="flex items-center p-3 bg-muted/30 rounded-lg border border-border/60">
                        <CheckCircle2 className="h-4 w-4 text-accent ml-3 flex-shrink-0" />
                        <span className="text-sm font-medium">{app}</span>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="documents" className="space-y-6" dir="rtl">
              <Card className="border-border/60">
                <CardHeader className="text-right">
                  <CardTitle className="flex items-center gap-2">
                    <FileText className="h-5 w-5" />
                    مستندات فنی
                  </CardTitle>
                  <CardDescription>دانلود دیتاشیت، گواهینامه و مستندات فنی</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="grid sm:grid-cols-3 gap-4">
                    <Button variant="outline" className="w-full justify-start h-auto py-3">
                      <Download className="h-4 w-4 ml-2" />
                      <div className="text-right">
                        <div className="font-bold text-sm">TDS</div>
                        <div className="text-xs text-muted-foreground">دیتاشیت فنی</div>
                      </div>
                    </Button>
                    <Button variant="outline" className="w-full justify-start h-auto py-3">
                      <Download className="h-4 w-4 ml-2" />
                      <div className="text-right">
                        <div className="font-bold text-sm">SDS</div>
                        <div className="text-xs text-muted-foreground">برگه ایمنی</div>
                      </div>
                    </Button>
                    <Button variant="outline" className="w-full justify-start h-auto py-3">
                      <Download className="h-4 w-4 ml-2" />
                      <div className="text-right">
                        <div className="font-bold text-sm">گواهینامه</div>
                        <div className="text-xs text-muted-foreground">تاییدیه و انطباق</div>
                      </div>
                    </Button>
                  </div>
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="faq" className="space-y-6" dir="rtl">
              <Card className="border-border/60">
                <CardHeader className="text-right">
                  <CardTitle>سوالات متداول</CardTitle>
                  <CardDescription>پاسخ به پرسش‌های رایج درباره این محصول</CardDescription>
                </CardHeader>
                <CardContent>
                  <Accordion type="single" collapsible className="w-full">
                    {faqs.map((faq, index) => (
                      <AccordionItem key={index} value={`item-${index}`}>
                        <AccordionTrigger className="text-right font-bold text-sm">
                          {faq.question}
                        </AccordionTrigger>
                        <AccordionContent className="text-muted-foreground text-sm leading-relaxed">
                          {faq.answer}
                        </AccordionContent>
                      </AccordionItem>
                    ))}
                  </Accordion>
                </CardContent>
              </Card>
            </TabsContent>
          </Tabs>
        </div>
      </section>

      {/* ────── Similar Products ────── */}
      {(() => {
        const similarProducts = products.filter(
          p => p.id !== product.id && (p.category === product.category || p.type === product.type)
        ).slice(0, 4);
        if (similarProducts.length === 0) return null;

        const imageMap: Record<string, string> = {
          'nitrogen-generator-peak': prodNitrogenPeak,
          'flowmeter-endress': prodFlowmeterEndress,
          'gas-detector-honeywell': prodGasDetectorHoneywell,
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

        return (
          <section className="py-12 bg-muted/40" dir="rtl">
            <div className="container mx-auto px-4 sm:px-6 lg:px-8">
              <h2 className="text-2xl font-black mb-8 text-foreground text-right">محصولات مشابه</h2>
              <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                {similarProducts.map(sp => (
                  <Link key={sp.id} to={`/products/${sp.id}`} className="block rounded-xl border border-border bg-card overflow-hidden hover:border-primary/30 hover:shadow-md transition-all group">
                    <div className="h-40 bg-muted/30 overflow-hidden">
                      {sp.image && imageMap[sp.image] ? (
                        <img src={imageMap[sp.image]} alt={sp.name} className="h-full w-full object-contain p-3 group-hover:scale-105 transition-transform" loading="lazy" />
                      ) : (
                        <div className="h-full w-full flex items-center justify-center text-muted-foreground/30 text-4xl font-black">TM</div>
                      )}
                    </div>
                    <div className="p-4 text-right">
                      <h3 className="text-sm font-bold text-foreground mb-1 group-hover:text-primary transition-colors">{sp.name}</h3>
                      <p className="text-xs text-muted-foreground ltr">{sp.brand} — {sp.model}</p>
                    </div>
                  </Link>
                ))}
              </div>
            </div>
          </section>
        );
      })()}
    </div>
  );
}
