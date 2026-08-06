import { useState } from 'react';
import { motion } from 'framer-motion';
import { SEO } from '@/components/SEO';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { Mail, MapPin, Phone, Clock, CheckCircle2, X } from 'lucide-react';
import { useRFQ } from '@/contexts/RFQContext';
import { useToast } from '@/hooks/use-toast';

export default function Contact() {
  const { products, removeProduct, clearProducts } = useRFQ();
  const { toast } = useToast();
  const [showAdvanced, setShowAdvanced] = useState(false);
  const [formData, setFormData] = useState({
    name: '', company: '', email: '', city: '', phone: '', equipment: '', timeline: '', requirements: ''
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    toast({
      title: "استعلام ثبت شد",
      description: "کارشناسان فنی ما در اسرع وقت با شما تماس خواهند گرفت.",
    });
    setFormData({ name: '', company: '', email: '', city: '', phone: '', equipment: '', timeline: '', requirements: '' });
    clearProducts();
  };

  return (
    <div className="min-h-screen bg-background">
      <SEO
        title="استعلام قیمت و مشاوره فنی"
        description="استعلام قیمت تجهیزات ابزار دقیق و اتوماسیون صنعتی. مشاوره رایگان توسط مهندسان متخصص."
        keywords="استعلام قیمت, مشاوره فنی, تجهیزات صنعتی, ابزار دقیق"
      />

      <section className="bg-gradient-to-br from-[hsl(var(--hero-gradient-start))] to-[hsl(var(--hero-gradient-end))] text-primary-foreground py-10 border-b border-border">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
            <h1 className="text-4xl font-black mb-3">استعلام قیمت و مشاوره</h1>
            <p className="text-lg text-primary-foreground/80 max-w-3xl">
              فرم را تکمیل کنید. مهندسان ما در کمتر از ۲۴ ساعت پاسخ خواهند داد.
            </p>
          </motion.div>
        </div>
      </section>

      <section className="py-12">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid lg:grid-cols-3 gap-8">
            <div className="lg:col-span-2">
              <Card className="border-border/60">
                <CardHeader>
                  <CardTitle>فرم استعلام قیمت</CardTitle>
                  <CardDescription>فیلدهای ستاره‌دار الزامی هستند *</CardDescription>
                </CardHeader>
                <CardContent>
                  <form onSubmit={handleSubmit} className="space-y-6">
                    {products.length > 0 && (
                      <div>
                        <Label>تجهیزات انتخاب‌شده ({products.length})</Label>
                        <div className="mt-2 space-y-2">
                          {products.map(product => (
                            <div key={product.id} className="flex items-center justify-between p-3 bg-muted rounded-lg">
                              <div>
                                <p className="font-bold text-sm">{product.name}</p>
                                <p className="text-xs text-muted-foreground font-mono ltr">{product.grade}</p>
                              </div>
                              <Button type="button" variant="ghost" size="sm" onClick={() => removeProduct(product.id)}>
                                <X className="h-4 w-4" />
                              </Button>
                            </div>
                          ))}
                        </div>
                      </div>
                    )}

                    <div className="grid md:grid-cols-2 gap-4">
                      <div>
                        <Label htmlFor="name">نام و نام خانوادگی *</Label>
                        <Input id="name" required value={formData.name} onChange={(e) => setFormData({...formData, name: e.target.value})} placeholder="نام کامل" />
                      </div>
                      <div>
                        <Label htmlFor="company">نام شرکت/سازمان *</Label>
                        <Input id="company" required value={formData.company} onChange={(e) => setFormData({...formData, company: e.target.value})} placeholder="نام شرکت" />
                      </div>
                    </div>

                    <div className="grid md:grid-cols-2 gap-4">
                      <div>
                        <Label htmlFor="email">ایمیل *</Label>
                        <Input id="email" type="email" required value={formData.email} onChange={(e) => setFormData({...formData, email: e.target.value})} placeholder="example@company.com" className="ltr text-left" dir="ltr" />
                      </div>
                      <div>
                        <Label htmlFor="phone">شماره تماس *</Label>
                        <Input id="phone" required value={formData.phone} onChange={(e) => setFormData({...formData, phone: e.target.value})} placeholder="۰۹۱۲۳۴۵۶۷۸۹" className="ltr text-left" dir="ltr" />
                      </div>
                    </div>

                    <div>
                      <Label htmlFor="city">شهر *</Label>
                      <Select value={formData.city} onValueChange={(value) => setFormData({...formData, city: value})}>
                        <SelectTrigger><SelectValue placeholder="انتخاب شهر" /></SelectTrigger>
                        <SelectContent>
                          <SelectItem value="tehran">تهران</SelectItem>
                          <SelectItem value="isfahan">اصفهان</SelectItem>
                          <SelectItem value="mashhad">مشهد</SelectItem>
                          <SelectItem value="tabriz">تبریز</SelectItem>
                          <SelectItem value="shiraz">شیراز</SelectItem>
                          <SelectItem value="assaluyeh">عسلویه</SelectItem>
                          <SelectItem value="mahshahr">ماهشهر</SelectItem>
                          <SelectItem value="other">سایر</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>

                    {products.length === 0 && (
                      <div>
                        <Label htmlFor="equipment">تجهیزات مورد نیاز *</Label>
                        <Textarea id="equipment" required={products.length === 0} placeholder="مثلاً: ژنراتور نیتروژن، فلومتر الکترومغناطیسی DN50، PLC زیمنس S7-1500" rows={2} />
                      </div>
                    )}

                    <div>
                      <Button type="button" variant="outline" onClick={() => setShowAdvanced(!showAdvanced)} className="w-full">
                        {showAdvanced ? 'پنهان کردن' : 'نمایش'} اطلاعات تکمیلی (اختیاری)
                      </Button>
                    </div>

                    {showAdvanced && (
                      <motion.div initial={{ opacity: 0, height: 0 }} animate={{ opacity: 1, height: 'auto' }} className="space-y-4 pt-4 border-t border-border">
                        <div>
                          <Label htmlFor="timeline">زمان‌بندی پروژه</Label>
                          <Select value={formData.timeline} onValueChange={(value) => setFormData({...formData, timeline: value})}>
                            <SelectTrigger><SelectValue placeholder="انتخاب زمان‌بندی" /></SelectTrigger>
                            <SelectContent>
                              <SelectItem value="urgent">فوری (کمتر از ۱ ماه)</SelectItem>
                              <SelectItem value="1month">طی ۱ تا ۳ ماه</SelectItem>
                              <SelectItem value="flexible">منعطف</SelectItem>
                              <SelectItem value="research">فقط بررسی فنی</SelectItem>
                            </SelectContent>
                          </Select>
                        </div>
                        <div>
                          <Label htmlFor="requirements">مشخصات فنی خاص</Label>
                          <Textarea id="requirements" value={formData.requirements} onChange={(e) => setFormData({...formData, requirements: e.target.value})} placeholder="فشار، دبی، تاییدیه ATEX، پروتکل ارتباطی خاص..." rows={4} />
                        </div>
                      </motion.div>
                    )}

                    <div className="flex items-start gap-2">
                      <Checkbox id="privacy" required />
                      <Label htmlFor="privacy" className="text-sm text-muted-foreground cursor-pointer">
                        با ثبت این فرم، موافقت خود را با سیاست حفظ حریم خصوصی اعلام می‌کنم *
                      </Label>
                    </div>

                    <Button type="submit" size="lg" variant="cta" className="w-full">ارسال استعلام قیمت</Button>

                    <p className="text-xs text-center text-muted-foreground">
                      <Clock className="inline h-3 w-3 ml-1" />
                      پاسخ در کمتر از ۲۴ ساعت کاری
                    </p>
                  </form>
                </CardContent>
              </Card>
            </div>

            <div className="space-y-6">
              <Card className="border-border/60">
                <CardHeader><CardTitle>اطلاعات تماس</CardTitle></CardHeader>
                <CardContent className="space-y-4">
                  <div className="flex items-start">
                    <MapPin className="h-5 w-5 text-accent ml-3 mt-0.5 flex-shrink-0" />
                    <div>
                      <p className="font-bold text-sm">دفتر مرکزی</p>
                      <p className="text-sm text-muted-foreground">تهران، ایران</p>
                    </div>
                  </div>
                  <div className="flex items-start">
                    <Mail className="h-5 w-5 text-accent ml-3 mt-0.5 flex-shrink-0" />
                    <div>
                      <p className="font-bold text-sm">ایمیل</p>
                      <a href="mailto:info@parsid.ir" className="text-sm text-primary hover:underline ltr">info@parsid.ir</a>
                    </div>
                  </div>
                  <div className="flex items-start">
                    <Phone className="h-5 w-5 text-accent ml-3 mt-0.5 flex-shrink-0" />
                    <div>
                      <p className="font-bold text-sm">تلفن</p>
                      <p className="text-sm text-muted-foreground ltr">+98 21 XXXX XXXX</p>
                    </div>
                  </div>
                  <div className="flex items-start">
                    <Clock className="h-5 w-5 text-accent ml-3 mt-0.5 flex-shrink-0" />
                    <div>
                      <p className="font-bold text-sm">ساعات کاری</p>
                      <p className="text-sm text-muted-foreground">شنبه تا چهارشنبه: ۸ تا ۱۷</p>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card className="border-border/60">
                <CardHeader><CardTitle>خدمات ما</CardTitle></CardHeader>
                <CardContent className="space-y-3">
                  {[
                    'مشاوره رایگان مهندسی تجهیزات',
                    'ارائه قیمت شفاف و رقابتی',
                    'تجهیزات اصل با گارانتی رسمی',
                    'نصب و کمیسیونینگ تخصصی',
                    'آموزش اپراتورها و مهندسان',
                    'پشتیبانی فنی ۲۴/۷',
                  ].map(service => (
                    <div key={service} className="flex items-start">
                      <CheckCircle2 className="h-4 w-4 text-accent ml-2 mt-0.5 flex-shrink-0" />
                      <p className="text-sm text-muted-foreground">{service}</p>
                    </div>
                  ))}
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}
