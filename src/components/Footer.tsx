import { Link } from 'react-router-dom';
import { Mail, MapPin, Phone, ArrowUp, Clock } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useSettings } from '@/api/hooks';

export function Footer() {
  const { data: settings } = useSettings();

  const scrollToTop = () => window.scrollTo({ top: 0, behavior: 'smooth' });

  // خواندن تنظیمات از API یا مقادیر پیش‌فرض
  const address = settings?.address || 'تهران، خیابان توحید، خیابان طوسی، پلاک ۱۶۲، واحد ۹';
  const phone = settings?.phone || '021-66120746';
  const email = settings?.email || 'info@toolmaster.com';
  const workingHours = settings?.working_hours || 'شنبه تا چهارشنبه: ۹:۰۰-۱۷:۰۰';
  const companyName = settings?.company_name || 'شرکت مهندسی تول‌مستر';
  const companyNameEn = settings?.company_name_en || 'ToolMaster Engineering Co.';
  const tagline = settings?.tagline || 'مرجع تخصصی ابزار دقیق و اتوماسیون صنعتی ایران';
  const copyrightYear = settings?.founded_year || '۱۳۸۹';

  return (
    <footer className="bg-primary text-primary-foreground">
      <div className="h-px bg-gradient-to-r from-transparent via-primary-foreground/15 to-transparent" />
      <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 lg:divide-x lg:divide-primary-foreground/10 lg:[direction:rtl]">
          {/* Column 1: Contact & Logo */}
          <div className="lg:pl-6">
            <div className="flex items-center gap-3 mb-5">
              <div className="grid place-items-center h-10 w-10 rounded-lg bg-accent">
                <span className="text-sm font-black text-accent-foreground">TM</span>
              </div>
              <div>
                <p className="text-base font-bold">{companyName}</p>
                <p className="text-[10px] text-primary-foreground/60 ltr">{companyNameEn}</p>
              </div>
            </div>
            <p className="text-sm text-primary-foreground/70 leading-relaxed mb-5">{tagline}</p>
            <ul className="space-y-3">
              <li className="flex items-start text-sm text-primary-foreground/70">
                <MapPin className="h-4 w-4 ml-2 mt-0.5 flex-shrink-0 text-accent" />
                <span>{address}</span>
              </li>
              <li className="flex items-center text-sm text-primary-foreground/70">
                <Phone className="h-4 w-4 ml-2 flex-shrink-0 text-accent" />
                <span className="ltr">{phone}</span>
              </li>
              <li className="flex items-center text-sm text-primary-foreground/70">
                <Mail className="h-4 w-4 ml-2 flex-shrink-0 text-accent" />
                <a href={`mailto:${email}`} className="hover:text-accent transition-colors ltr">{email}</a>
              </li>
              <li className="flex items-center text-sm text-primary-foreground/70">
                <Clock className="h-4 w-4 ml-2 flex-shrink-0 text-accent" />
                <span>{workingHours}</span>
              </li>
            </ul>
          </div>

          {/* Column 2: Quick Access */}
          <div className="lg:pl-6">
            <h3 className="font-bold mb-4 text-sm">📋 دسترسی سریع</h3>
            <ul className="space-y-2.5">
              {[
                { label: 'صفحه اصلی', link: '/' },
                { label: 'درباره ما', link: '/about' },
                { label: 'تماس با ما', link: '/contact' },
                { label: 'اخبار و مقالات', link: '/blog' },
                { label: 'پروژه‌های اجرا شده', link: '/projects' },
                { label: 'گواهینامه‌ها و تأییدیه‌ها', link: '/sustainability' },
                { label: 'شرایط و ضوابط', link: '/terms' },
                { label: 'حریم خصوصی', link: '/privacy' },
                { label: 'سوالات متداول', link: '/faq' },
              ].map(item => (
                <li key={item.label}>
                  <Link to={item.link} className="text-sm text-primary-foreground/60 hover:text-accent transition-colors">
                    {item.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Column 3: Products */}
          <div className="lg:pl-6">
            <h3 className="font-bold mb-4 text-sm">🔧 محصولات</h3>
            <ul className="space-y-2.5">
              {[
                { label: 'ژنراتورهای گاز', link: '/products/category/gas-generators' },
                { label: 'ژنراتور هیدروژن', link: '/products?type=hydrogen-gen' },
                { label: 'ژنراتور نیتروژن', link: '/products?type=nitrogen-gen' },
                { label: 'پمپ‌های آزمایشگاهی', link: '/products/category/lab-pumps' },
                { label: 'دتکتورهای گاز', link: '/products/category/gas-detectors' },
                { label: 'فلومتر و فلوکنترلر', link: '/products/category/flow-meters' },
                { label: 'تجهیزات PLC', link: '/products/category/plc-equipment' },
                { label: 'سنسورهای صنعتی', link: '/products?type=plc-io' },
                { label: 'تجهیزات کالیبراسیون', link: '/products/category/calibration' },
                { label: 'لوازم جانبی', link: '/resources' },
              ].map(item => (
                <li key={item.label}>
                  <Link to={item.link} className="text-sm text-primary-foreground/60 hover:text-accent transition-colors">
                    {item.label}
                  </Link>
                </li>
              ))}
            </ul>
            <Link to="/products" className="inline-block mt-3 text-xs font-semibold text-accent hover:text-accent/80 transition-colors">
              مشاهده تمام محصولات ←
            </Link>
          </div>

          {/* Column 4: Newsletter */}
          <div className="lg:pl-6">
            <h3 className="font-bold mb-4 text-sm">📰 خبرنامه</h3>
            <p className="text-sm text-primary-foreground/60 mb-3">آخرین مقالات، اخبار صنعت و پیشنهادات ویژه را دریافت کنید.</p>
            <div className="flex gap-2 mb-6">
              <input
                type="email"
                placeholder="ایمیل شما..."
                className="flex-1 rounded-md bg-primary-foreground/10 border border-primary-foreground/20 px-3 py-2 text-sm text-primary-foreground placeholder:text-primary-foreground/40 focus:outline-none focus:ring-1 focus:ring-accent"
              />
              <Button className="bg-accent text-accent-foreground hover:bg-accent/90 text-xs px-4">عضویت</Button>
            </div>

            <div className="border-t border-primary-foreground/10 my-4" />
            <h3 className="font-bold mb-3 text-sm">⚡ درخواست‌های سریع</h3>
            <ul className="space-y-2">
              <li>
                <Link to="/contact" className="text-sm text-primary-foreground/60 hover:text-accent transition-colors">
                  📞 تماس فوری با کارشناس
                </Link>
              </li>
              <li>
                <Link to="/contact" className="text-sm text-primary-foreground/60 hover:text-accent transition-colors">
                  📩 درخواست پیش‌فاکتور
                </Link>
              </li>
              <li>
                <Link to="/resources" className="text-sm text-primary-foreground/60 hover:text-accent transition-colors">
                  📄 درخواست کاتالوگ
                </Link>
              </li>
            </ul>
          </div>
        </div>
      </div>

      {/* Bottom Bar */}
      <div className="border-t border-primary-foreground/10" style={{ backgroundColor: 'hsl(210, 100%, 13%)' }}>
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col md:flex-row justify-between items-center gap-4">
          <p className="text-xs text-primary-foreground/50">
            © تمامی حقوق مادی و معنوی برای {companyName} محفوظ است. ({copyrightYear}-{new Date().getFullYear()})
          </p>
          <div className="flex items-center gap-4 text-xs text-primary-foreground/50 flex-wrap justify-center">
            <span>• دارنده نماد اعتماد الکترونیکی</span>
            <span>• عضو انجمن مهندسان ابزار دقیق ایران</span>
            <span>• مجوز فعالیت وزارت صنعت</span>
            <span>• گواهی ISO 9001:2015</span>
          </div>
          <button
            onClick={scrollToTop}
            className="rounded-full bg-primary-foreground/10 p-2 hover:bg-primary-foreground/20 transition"
            aria-label="بازگشت به بالا"
          >
            <ArrowUp className="h-4 w-4" />
          </button>
        </div>
      </div>
    </footer>
  );
}
