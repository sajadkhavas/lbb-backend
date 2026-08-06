import { Link } from 'react-router-dom';
import { motion } from 'framer-motion';
import { SEO } from '@/components/SEO';
import { generateBreadcrumbSchema } from '@/lib/structured-data';
import { brandSEOData } from '@/data/brand-seo';
import { ArrowLeft } from 'lucide-react';

import logoSiemens from '@/assets/brands/siemens.png';
import logoEndress from '@/assets/brands/endress-hauser.png';
import logoHoneywell from '@/assets/brands/honeywell.png';
import logoEmerson from '@/assets/brands/emerson.png';
import logoAbb from '@/assets/brands/abb.png';
import logoRockwell from '@/assets/brands/rockwell.png';
import logoPeak from '@/assets/brands/peak.png';
import logoDrager from '@/assets/brands/drager.png';
import logoKnf from '@/assets/brands/knf.png';
import logoYokogawa from '@/assets/brands/yokogawa.png';
import logoBrooks from '@/assets/brands/brooks.png';
import logoSchneider from '@/assets/brands/schneider.png';

const brandLogos: Record<string, string> = {
  siemens: logoSiemens,
  'endress-hauser': logoEndress,
  honeywell: logoHoneywell,
  emerson: logoEmerson,
  abb: logoAbb,
  rockwell: logoRockwell,
  peak: logoPeak,
  drager: logoDrager,
  knf: logoKnf,
  yokogawa: logoYokogawa,
  brooks: logoBrooks,
  schneider: logoSchneider,
};

export default function Brands() {
  const brands = Object.values(brandSEOData);

  const breadcrumbSchema = generateBreadcrumbSchema([
    { name: 'خانه', url: 'https://toolmaster.com' },
    { name: 'برندها', url: 'https://toolmaster.com/brands' },
  ]);

  return (
    <div className="min-h-screen bg-background" dir="rtl">
      <SEO
        title="برندهای تجهیزات ابزار دقیق و اتوماسیون صنعتی | تول‌مستر"
        description="مشاهده تمامی برندهای معتبر ابزار دقیق و اتوماسیون صنعتی شامل Siemens، ABB، Honeywell، Emerson، Endress+Hauser و ۷ برند دیگر. نماینده رسمی در ایران."
        keywords="برندهای ابزار دقیق, Siemens, ABB, Honeywell, Emerson, Endress+Hauser, Dräger, Yokogawa, Rockwell, Peak Scientific, KNF, Brooks, Schneider Electric"
        structuredData={breadcrumbSchema}
      />

      <section className="bg-gradient-to-br from-[hsl(var(--hero-gradient-start))] to-[hsl(var(--hero-gradient-end))] text-primary-foreground py-12 border-b border-border">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <h1 className="text-3xl md:text-4xl font-black mb-3 text-right">برندهای تجهیزات صنعتی</h1>
          <p className="text-lg text-primary-foreground/80 max-w-3xl text-right">
            نمایندگی رسمی ۱۲ برند پیشرو جهانی در حوزه ابزار دقیق، اتوماسیون و ایمنی صنعتی
          </p>
        </div>
      </section>

      <section className="py-10">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            {brands.map((brand, index) => (
              <motion.div key={brand.slug} initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: index * 0.05 }}>
                <Link to={`/brands/${brand.slug}`} className="group block rounded-2xl border border-border/60 bg-background hover:border-accent/40 hover:shadow-lg transition-all overflow-hidden">
                  <div className="h-28 flex items-center justify-center bg-muted/20 p-5">
                    {brandLogos[brand.slug] && (
                      <img src={brandLogos[brand.slug]} alt={brand.name} className="max-h-16 max-w-[140px] object-contain opacity-80 group-hover:opacity-100 transition-opacity" loading="lazy" />
                    )}
                  </div>
                  <div className="p-4">
                    <h2 className="font-bold text-sm mb-1 text-right">{brand.name}</h2>
                    <p className="text-xs text-muted-foreground text-right line-clamp-2 leading-6">{brand.heroDescription}</p>
                    <div className="flex items-center gap-1 mt-3 text-xs text-accent font-medium">
                      <span>مشاهده محصولات</span>
                      <ArrowLeft className="h-3 w-3" />
                    </div>
                  </div>
                </Link>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      <section className="py-10 border-t border-border bg-muted/10">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="prose prose-lg max-w-none dark:prose-invert text-right" dir="rtl">
            <h2>تول‌مستر: نماینده رسمی برندهای پیشرو ابزار دقیق و اتوماسیون صنعتی</h2>
            <p>شرکت تول‌مستر به عنوان یکی از معتبرترین تأمین‌کنندگان تجهیزات ابزار دقیق و اتوماسیون صنعتی در ایران، نمایندگی رسمی <strong>۱۲ برند پیشرو جهانی</strong> را بر عهده دارد. ما با ارائه محصولات اصل، خدمات فنی تخصصی و پشتیبانی ۲۴/۷، شریک مطمئن صنایع ایران در مسیر ارتقاء بهره‌وری و ایمنی هستیم.</p>

            <h3>حوزه‌های تخصصی برندهای ما</h3>
            <p>برندهای موجود در تول‌مستر پنج حوزه اصلی صنعتی را پوشش می‌دهند: <strong>اتوماسیون و کنترل فرآیند</strong> (Siemens، Rockwell Automation، Schneider Electric، Emerson، Yokogawa)، <strong>ابزار دقیق و اندازه‌گیری</strong> (Endress+Hauser، Brooks Instrument)، <strong>ایمنی صنعتی و تشخیص گاز</strong> (Honeywell، Dräger)، <strong>ژنراتورهای گاز آزمایشگاهی</strong> (Peak Scientific) و <strong>پمپ‌ها و تجهیزات آزمایشگاهی</strong> (KNF، ABB).</p>

            <h3>چرا برندهای اصل از تول‌مستر؟</h3>
            <p>خرید تجهیزات صنعتی از نماینده رسمی مزایای متعددی دارد. اول و مهم‌تر از همه، <strong>تضمین اصالت کالا</strong> و ارائه گارانتی معتبر از سوی کمپانی سازنده. ما تمام محصولات را مستقیماً از کارخانه‌های تولیدکننده وارد می‌کنیم و هر کالا با سریال نامبر اصلی و گواهینامه کیفیت ارائه می‌شود.</p>
            <p>دوم، <strong>خدمات فنی تخصصی</strong> شامل مشاوره فنی رایگان پیش از خرید، نصب و راه‌اندازی توسط مهندسان مجرب، آموزش بهره‌برداری، کالیبراسیون دوره‌ای و تعمیرات تخصصی. تیم مهندسی تول‌مستر با دوره‌های آموزشی در کارخانه‌های سازنده، دانش فنی به‌روز و تجربه عملی در پروژه‌های بزرگ صنعتی ایران را دارا هستند.</p>

            <h3>صنایع تحت پوشش</h3>
            <p>برندهای عرضه‌شده توسط تول‌مستر در طیف وسیعی از صنایع ایران کاربرد دارند: <strong>نفت، گاز و پتروشیمی</strong> (سیستم‌های کنترل DCS، دتکتورهای گاز، ولوهای کنترلی)، <strong>نیروگاه‌ها</strong> (PLC، درایو و سیستم‌های ایمنی)، <strong>آب و فاضلاب</strong> (فلومتر، ترانسمیتر فشار و سطح‌سنج)، <strong>داروسازی و بیوتکنولوژی</strong> (ژنراتور گاز، پمپ پریستالتیک)، <strong>صنایع غذایی</strong> (اتوماسیون خطوط تولید و بسته‌بندی) و <strong>معادن و فولاد</strong> (درایو، PLC و تجهیزات ایمنی).</p>

            <h3>خدمات جامع تول‌مستر</h3>
            <p>ما فراتر از فروش تجهیزات، خدمات جامعی ارائه می‌دهیم: <strong>مشاوره فنی و انتخاب تجهیزات</strong> متناسب با نیاز پروژه، <strong>طراحی و اجرای سیستم‌های اتوماسیون</strong>، <strong>برنامه‌نویسی PLC و SCADA</strong>، <strong>کالیبراسیون ابزار دقیق</strong> مطابق استانداردهای ISO 17025، <strong>تأمین قطعات یدکی اصلی</strong> و <strong>قرارداد نگهداری و تعمیرات سالانه</strong>.</p>
            <p>برای دریافت مشاوره رایگان و استعلام قیمت تجهیزات هر یک از برندهای فوق، با کارشناسان فنی تول‌مستر تماس بگیرید یا از طریق فرم استعلام آنلاین اقدام نمایید. ارسال سریع به تمام شهرهای ایران با بسته‌بندی استاندارد صنعتی.</p>
          </div>
        </div>
      </section>
    </div>
  );
}
