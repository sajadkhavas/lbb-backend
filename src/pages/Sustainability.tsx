import { motion } from 'framer-motion';
import { SEO } from '@/components/SEO';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Award, CheckCircle2, Target, Shield, AlertTriangle, FileText } from 'lucide-react';

export default function Sustainability() {
  const principles = [
    {
      icon: Shield,
      title: 'ایمنی عملکردی',
      description: 'تجهیزات مطابق با SIL (Safety Integrity Level) و الزامات ایمنی عملکردی IEC 61508'
    },
    {
      icon: AlertTriangle,
      title: 'محیط‌های خطرناک',
      description: 'تمامی تجهیزات مرتبط دارای تاییدیه ATEX و IECEx برای مناطق Zone 0, 1, 2'
    },
    {
      icon: Target,
      title: 'دقت و قابلیت ردیابی',
      description: 'کالیبراسیون مطابق استانداردهای بین‌المللی با قابلیت ردیابی به مراجع ملی و بین‌المللی'
    },
    {
      icon: FileText,
      title: 'مستندسازی کامل',
      description: 'ارائه مستندات فنی کامل شامل دیتاشیت، گواهینامه، نقشه و دستورالعمل نگهداری'
    }
  ];

  const certifications = [
    'ATEX (2014/34/EU)',
    'IECEx',
    'SIL 2 / SIL 3',
    'CE Marking',
    'ISO 9001:2015',
    'ISO 17025',
    'ISO 14001',
    'UL Listed',
  ];

  return (
    <div className="min-h-screen bg-background">
      <SEO
        title="استانداردها و گواهینامه‌ها"
        description="تجهیزات ما مطابق با استانداردهای ATEX, IECEx, SIL, CE و ISO هستند. تعهد به ایمنی و دقت صنعتی."
        keywords="ATEX, IECEx, SIL, استانداردهای صنعتی, ایمنی, کالیبراسیون, گواهینامه"
      />

      <section className="bg-gradient-to-br from-[hsl(var(--hero-gradient-start))] to-[hsl(var(--hero-gradient-end))] text-primary-foreground py-20">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="max-w-3xl">
            <Badge className="mb-4 bg-accent text-accent-foreground border-none font-bold">
              <Award className="h-3 w-3 ml-1" />
              استانداردها و ایمنی
            </Badge>
            <h1 className="text-4xl md:text-5xl font-black mb-6">
              دقت، ایمنی، اعتماد
            </h1>
            <p className="text-xl text-primary-foreground/85">
              تعهد ما به ارائه تجهیزات مطابق با بالاترین استانداردهای بین‌المللی ایمنی و کیفیت
            </p>
          </motion.div>
        </div>
      </section>

      <section className="py-20 bg-muted/50">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid md:grid-cols-2 gap-6 max-w-5xl mx-auto">
            {principles.map((principle, index) => (
              <motion.div
                key={principle.title}
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: index * 0.1 }}
              >
                <Card className="h-full border-border/60">
                  <CardHeader>
                    <div className="flex items-start">
                      <div className="p-2 bg-accent/10 rounded-lg ml-4">
                        <principle.icon className="h-6 w-6 text-accent" />
                      </div>
                      <div>
                        <CardTitle className="mb-2 text-base">{principle.title}</CardTitle>
                        <CardDescription>{principle.description}</CardDescription>
                      </div>
                    </div>
                  </CardHeader>
                </Card>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      <section className="py-20">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="max-w-4xl mx-auto">
            <h2 className="text-3xl font-black mb-6 text-foreground">گواهینامه‌ها و استانداردها</h2>
            <Card className="border-border/60">
              <CardContent className="pt-6">
                <div className="flex flex-wrap gap-3">
                  {certifications.map(cert => (
                    <Badge key={cert} variant="secondary" className="text-sm font-mono">
                      {cert}
                    </Badge>
                  ))}
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </section>

      <section className="py-20 bg-primary/5 border-y border-primary/10">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            {[
              { value: '+۲۰۰۰', label: 'پروژه اجرا شده' },
              { value: '۹۹.۵٪', label: 'رضایت مشتریان' },
              { value: '۲۴/۷', label: 'پشتیبانی فنی' },
            ].map(stat => (
              <Card key={stat.label} className="text-center border-border/60">
                <CardContent className="pt-6">
                  <p className="text-4xl font-black text-foreground mb-2">{stat.value}</p>
                  <p className="text-sm text-muted-foreground">{stat.label}</p>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </section>
    </div>
  );
}
