import { motion } from 'framer-motion';
import { SEO } from '@/components/SEO';
import { generateOrganizationSchema } from '@/lib/structured-data';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Globe, Target, Users, Factory, Shield } from 'lucide-react';
import { useSitePage } from '@/api';

export default function About() {
  const { data: page } = useSitePage('about');

  const values = [
    { icon: Target, title: 'دقت و اطمینان', description: 'ارائه تجهیزات صنعتی با بالاترین دقت و قابلیت اطمینان برای فرآیندهای حیاتی' },
    { icon: Shield, title: 'ایمنی صنعتی', description: 'تمامی تجهیزات مطابق با استانداردهای ATEX، IECEx و CE برای محیط‌های خطرناک' },
    { icon: Users, title: 'تیم مهندسی متخصص', description: 'مهندسان ابزار دقیق و اتوماسیون با سال‌ها تجربه در پروژه‌های صنعتی' },
    { icon: Globe, title: 'نمایندگی‌های معتبر', description: 'نماینده رسمی برندهای جهانی Siemens، Parker، Endress+Hauser و Dräger' }
  ];

  const organizationSchema = generateOrganizationSchema({
    name: 'تول‌مستر',
    url: 'https://toolmaster.com',
    logo: 'https://toolmaster.com/logo.png',
    description: 'تأمین‌کننده تجهیزات ابزار دقیق و اتوماسیون صنعتی',
    address: { addressLocality: 'تهران', addressCountry: 'ایران' }
  });

  return (
    <div className="min-h-screen bg-background">
      <SEO
        title={page?.meta_title || 'درباره ما'}
        description={page?.meta_description || 'شرکت تول‌مستر تامین‌کننده تخصصی تجهیزات ابزار دقیق'}
        keywords={page?.meta_keywords || 'درباره تول‌مستر, ابزار دقیق'}
        structuredData={organizationSchema}
      />

      {/* Hero - همیشه نشون میده */}
      <section className="bg-gradient-to-br from-[hsl(var(--hero-gradient-start))] to-[hsl(var(--hero-gradient-end))] text-primary-foreground py-20">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="max-w-3xl">
            <h1 className="text-4xl md:text-5xl font-black mb-6">
              {page?.hero_title || 'شریک مطمئن صنعت ایران'}
            </h1>
            <p className="text-xl text-primary-foreground/85">
              {page?.hero_description || 'شرکت تول‌مستر با هدف تأمین تجهیزات ابزار دقیق و اتوماسیون صنعتی با کیفیت، در خدمت صنایع حیاتی کشور است.'}
            </p>
          </motion.div>
        </div>
      </section>

      {/* محتوای صفحه از API یا متن پیش‌فرض */}
      <section className="py-20">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="max-w-4xl mx-auto">
            <motion.div initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }}>
              <h2 className="text-3xl font-black mb-6 text-foreground">داستان ما</h2>
              {page?.content ? (
                <div className="prose prose-lg max-w-none text-muted-foreground" dangerouslySetInnerHTML={{ __html: page.content }} />
              ) : (
                <div className="prose prose-lg max-w-none text-muted-foreground space-y-4">
                  <p>شرکت تول‌مستر از سال ۱۳۸۳ فعالیت خود را در زمینه تأمین تجهیزات ابزار دقیق و اتوماسیون صنعتی آغاز کرد.</p>
                  <p>تیم ما متشکل از مهندسان ابزار دقیق، اتوماسیون و کنترل با تجربه اجرای پروژه‌های بزرگ صنعتی است.</p>
                </div>
              )}
            </motion.div>
          </div>
        </div>
      </section>

      {/* Values */}
      <section className="py-20 bg-muted/50">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} className="text-center mb-12">
            <h2 className="text-3xl font-black mb-4 text-foreground">ارزش‌های ما</h2>
          </motion.div>
          <div className="grid md:grid-cols-2 gap-6 max-w-5xl mx-auto">
            {values.map((value, index) => (
              <motion.div key={value.title} initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ delay: index * 0.1 }}>
                <Card className="h-full border-border/60">
                  <CardHeader>
                    <div className="flex items-start">
                      <div className="p-2 bg-primary/10 rounded-lg ml-4">
                        <value.icon className="h-6 w-6 text-primary" />
                      </div>
                      <div>
                        <CardTitle className="mb-2 text-base">{value.title}</CardTitle>
                        <p className="text-muted-foreground text-sm">{value.description}</p>
                      </div>
                    </div>
                  </CardHeader>
                </Card>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* Advantages */}
      <section className="py-20">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="max-w-4xl mx-auto">
            <motion.div initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }}>
              <h2 className="text-3xl font-black mb-6 text-foreground">مزایای همکاری با ما</h2>
              <div className="grid md:grid-cols-3 gap-6">
                {[
                  { title: 'کالای اصل با گارانتی', desc: 'تمام تجهیزات اصل با سریال نامبر معتبر و گارانتی رسمی' },
                  { title: 'پشتیبانی ۲۴/۷', desc: 'تیم فنی آماده پاسخگویی شبانه‌روزی برای خطوط تولید' },
                  { title: 'نصب و کمیسیونینگ', desc: 'نصب تخصصی، راه‌اندازی و آموزش اپراتورها در محل' },
                ].map(item => (
                  <Card key={item.title} className="border-border/60">
                    <CardContent className="pt-6">
                      <h3 className="font-bold mb-2 text-sm">{item.title}</h3>
                      <p className="text-sm text-muted-foreground">{item.desc}</p>
                    </CardContent>
                  </Card>
                ))}
              </div>
            </motion.div>
          </div>
        </div>
      </section>

      {/* Team */}
      <section className="py-20 bg-muted/50">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} className="text-center mb-12">
            <h2 className="text-3xl font-black mb-4 text-foreground">تیم ما</h2>
          </motion.div>
          <div className="max-w-3xl mx-auto">
            <Card className="border-border/60">
              <CardContent className="pt-6 text-center">
                <Factory className="h-12 w-12 mx-auto text-primary/40 mb-4" />
                <p className="text-muted-foreground">تیم ما متشکل از مهندسان متخصص ابزار دقیق و اتوماسیون با تجربه اجرای پروژه در صنایع نفت، گاز، پتروشیمی، نیروگاه و معادن است.</p>
              </CardContent>
            </Card>
          </div>
        </div>
      </section>
    </div>
  );
}
