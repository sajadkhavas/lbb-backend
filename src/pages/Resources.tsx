import { useState } from 'react';
import { motion } from 'framer-motion';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Search, Download, FileText, Shield, Settings } from 'lucide-react';
import { SEO } from '@/components/SEO';

interface Resource {
  id: string;
  title: string;
  type: 'دیتاشیت' | 'کاتالوگ' | 'راهنما';
  product: string;
  model: string;
  lastUpdated: string;
}

const mockResources: Resource[] = [
  { id: '1', title: 'دیتاشیت ژنراتور نیتروژن NG-500', type: 'دیتاشیت', product: 'ژنراتور نیتروژن', model: 'NG-500', lastUpdated: '2024-03-01' },
  { id: '2', title: 'راهنمای نصب ژنراتور هیدروژن HG-300', type: 'راهنما', product: 'ژنراتور هیدروژن', model: 'HG-300', lastUpdated: '2024-02-15' },
  { id: '3', title: 'کاتالوگ دتکتورهای گاز Dräger', type: 'کاتالوگ', product: 'دتکتور گاز', model: 'GD-4X', lastUpdated: '2024-03-10' },
  { id: '4', title: 'دیتاشیت فلومتر الکترومغناطیسی FM-200', type: 'دیتاشیت', product: 'فلومتر', model: 'FM-200', lastUpdated: '2024-02-20' },
  { id: '5', title: 'راهنمای برنامه‌نویسی PLC S7-1500', type: 'راهنما', product: 'PLC زیمنس', model: 'S7-1500', lastUpdated: '2024-01-25' },
  { id: '6', title: 'دیتاشیت فلوکنترلر جرمی MFC-100', type: 'دیتاشیت', product: 'فلوکنترلر', model: 'MFC-100', lastUpdated: '2024-03-05' },
  { id: '7', title: 'کاتالوگ پمپ‌های خلاء Edwards', type: 'کاتالوگ', product: 'پمپ خلاء', model: 'VP-100', lastUpdated: '2024-02-01' },
  { id: '8', title: 'راهنمای کالیبراسیون دتکتور GD-FIX', type: 'راهنما', product: 'دتکتور گاز ثابت', model: 'GD-FIX', lastUpdated: '2024-01-30' },
];

export default function Resources() {
  const [searchQuery, setSearchQuery] = useState('');

  const filteredResources = mockResources.filter(r =>
    r.title.includes(searchQuery) || r.product.includes(searchQuery) || r.model.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const getTypeIcon = (type: string) => {
    switch (type) {
      case 'دیتاشیت': return <FileText className="h-4 w-4" />;
      case 'راهنما': return <Shield className="h-4 w-4" />;
      case 'کاتالوگ': return <Settings className="h-4 w-4" />;
      default: return <FileText className="h-4 w-4" />;
    }
  };

  return (
    <div className="min-h-screen bg-background">
      <SEO
        title="مستندات فنی و دیتاشیت"
        description="دانلود دیتاشیت، کاتالوگ و راهنمای فنی تجهیزات ابزار دقیق و اتوماسیون صنعتی"
        keywords="دیتاشیت, کاتالوگ تجهیزات, راهنمای فنی, ابزار دقیق"
      />

      <section className="bg-gradient-to-br from-[hsl(var(--hero-gradient-start))] to-[hsl(var(--hero-gradient-end))] text-primary-foreground py-10 border-b border-border">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
            <h1 className="text-4xl font-black mb-3">مستندات فنی</h1>
            <p className="text-lg text-primary-foreground/80 max-w-3xl">
              دانلود دیتاشیت‌ها، کاتالوگ‌ها و راهنماهای فنی تمام تجهیزات صنعتی
            </p>
          </motion.div>
        </div>
      </section>

      <section className="py-4 bg-background border-b border-border sticky top-16 z-40 backdrop-blur bg-background/95">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="relative max-w-2xl mx-auto">
            <Search className="absolute right-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
            <Input placeholder="جستجو بر اساس نام محصول یا مدل..." value={searchQuery} onChange={(e) => setSearchQuery(e.target.value)} className="pr-10" />
          </div>
        </div>
      </section>

      <section className="py-12 bg-muted/30">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto">
            <Card className="border-border/60">
              <CardHeader>
                <FileText className="h-8 w-8 text-accent mb-3" />
                <CardTitle className="text-lg">دیتاشیت</CardTitle>
                <CardDescription>مشخصات فنی کامل و پارامترهای عملکردی</CardDescription>
              </CardHeader>
            </Card>
            <Card className="border-border/60">
              <CardHeader>
                <Settings className="h-8 w-8 text-accent mb-3" />
                <CardTitle className="text-lg">کاتالوگ</CardTitle>
                <CardDescription>معرفی کامل خانواده محصولات و گزینه‌ها</CardDescription>
              </CardHeader>
            </Card>
            <Card className="border-border/60">
              <CardHeader>
                <Shield className="h-8 w-8 text-accent mb-3" />
                <CardTitle className="text-lg">راهنمای فنی</CardTitle>
                <CardDescription>نصب، راه‌اندازی، کالیبراسیون و نگهداری</CardDescription>
              </CardHeader>
            </Card>
          </div>
        </div>
      </section>

      <section className="py-12">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <p className="text-sm text-muted-foreground mb-6">نمایش {filteredResources.length} سند</p>
          <div className="space-y-4">
            {filteredResources.map((resource, index) => (
              <motion.div key={resource.id} initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: index * 0.04 }}>
                <Card className="hover:shadow-md transition-shadow border-border/60">
                  <CardContent className="p-6">
                    <div className="flex items-start justify-between">
                      <div className="flex-1">
                        <div className="flex items-center gap-2 mb-2">
                          <Badge variant={resource.type === 'دیتاشیت' ? 'default' : resource.type === 'راهنما' ? 'secondary' : 'outline'}>
                            {getTypeIcon(resource.type)}
                            <span className="mr-1">{resource.type}</span>
                          </Badge>
                          <span className="text-xs text-muted-foreground">
                            {new Date(resource.lastUpdated).toLocaleDateString('fa-IR')}
                          </span>
                        </div>
                        <h3 className="font-bold text-base mb-1">{resource.title}</h3>
                        <p className="text-xs font-mono text-muted-foreground ltr">{resource.model}</p>
                      </div>
                      <Button variant="outline" size="sm">
                        <Download className="h-4 w-4 ml-2" />
                        دانلود PDF
                      </Button>
                    </div>
                  </CardContent>
                </Card>
              </motion.div>
            ))}
          </div>

          {filteredResources.length === 0 && (
            <div className="text-center py-12">
              <FileText className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
              <h3 className="text-lg font-bold mb-2">سندی یافت نشد</h3>
              <p className="text-muted-foreground">عبارت جستجو را تغییر دهید</p>
            </div>
          )}
        </div>
      </section>

      <section className="py-12 bg-muted/50 border-t border-border">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <Card className="max-w-3xl mx-auto border-border/60">
            <CardHeader>
              <CardTitle>سند خاصی نیاز دارید؟</CardTitle>
              <CardDescription>با تیم فنی ما تماس بگیرید:</CardDescription>
            </CardHeader>
            <CardContent>
              <ul className="space-y-2 text-sm text-muted-foreground mb-6">
                <li>• دیتاشیت‌های اختصاصی</li>
                <li>• گواهینامه‌های ATEX / IECEx / CE</li>
                <li>• مستندات SIL و ایمنی عملکردی</li>
                <li>• نقشه‌های ابعادی و مکانیکی</li>
              </ul>
              <Button variant="cta">تماس با پشتیبانی فنی</Button>
            </CardContent>
          </Card>
        </div>
      </section>
    </div>
  );
}
