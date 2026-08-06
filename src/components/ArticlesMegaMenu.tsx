import { useMemo, useState, type ComponentType } from 'react';
import { Link } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import { Activity, BookOpen, ChevronLeft, Cpu, Droplets, Flame, Gauge, Phone } from 'lucide-react';
import { cn } from '@/lib/utils';

interface ArticlesMegaMenuProps {
  onClose: () => void;
}

interface ArticleCategoryGroup {
  id: string;
  label: string;
  description: string;
  icon: ComponentType<{ className?: string; }>;
  productHref: string;
  subcategories: { id: string; label: string; articleId: string; productHref: string; }[];
}

const articleCategories: ArticleCategoryGroup[] = [
  {
    id: 'gas-generators',
    label: 'ژنراتورهای گاز',
    description: 'مقالات تخصصی درباره ژنراتورهای هیدروژن، نیتروژن و هوای خشک',
    icon: Flame,
    productHref: '/products/category/gas-generators',
    subcategories: [
      { id: 'hydrogen-gen', label: 'ژنراتور هیدروژن', articleId: '10', productHref: '/products/category/gas-generators/hydrogen-gen' },
      { id: 'nitrogen-gen', label: 'ژنراتور نیتروژن', articleId: '11', productHref: '/products/category/gas-generators/nitrogen-gen' },
      { id: 'dry-air-gen', label: 'ژنراتور هوای خشک', articleId: '12', productHref: '/products/category/gas-generators/dry-air-gen' }]
  },
  {
    id: 'gas-detectors',
    label: 'تجهیزات اندازه‌گیری و آنالیز',
    description: 'راهنماهای انتخاب و نصب دتکتورهای گاز، فلومترها و فلوکنترلرها',
    icon: Gauge,
    productHref: '/products/category/gas-detectors',
    subcategories: [
      { id: 'gas-detectors', label: 'دتکتورهای گاز', articleId: '13', productHref: '/products/category/gas-detectors' },
      { id: 'flow-meters', label: 'فلومتر صنعتی', articleId: '14', productHref: '/products/category/flow-meters' },
      { id: 'mass-flow-controller', label: 'فلوکنترلر جرمی', articleId: '15', productHref: '/products/category/flow-meters/mass-flow-controller' }]
  },
  {
    id: 'plc-equipment',
    label: 'کنترل و اتوماسیون',
    description: 'آموزش و بررسی PLC، ماژول‌های I/O و پنل‌های HMI',
    icon: Cpu,
    productHref: '/products/category/plc-equipment',
    subcategories: [
      { id: 'plc-cpu', label: 'PLC و ماژول CPU', articleId: '16', productHref: '/products/category/plc-equipment/plc-cpu' },
      { id: 'hmi-panel', label: 'پنل HMI', articleId: '17', productHref: '/products/category/plc-equipment/hmi-panel' },
      { id: 'plc-io', label: 'ماژول ورودی/خروجی', articleId: '18', productHref: '/products/category/plc-equipment/plc-io' }]
  },
  {
    id: 'lab-pumps',
    label: 'پمپ‌های آزمایشگاهی',
    description: 'مقالات فنی درباره پمپ خلاء، پریستالتیک و دیافراگمی',
    icon: Droplets,
    productHref: '/products/category/lab-pumps',
    subcategories: [
      { id: 'vacuum-pump', label: 'پمپ خلاء روتاری', articleId: '19', productHref: '/products/category/lab-pumps/vacuum-pump' },
      { id: 'peristaltic-pump', label: 'پمپ پریستالتیک', articleId: '20', productHref: '/products/category/lab-pumps/peristaltic-pump' },
      { id: 'diaphragm-pump', label: 'پمپ دیافراگمی', articleId: '21', productHref: '/products/category/lab-pumps/diaphragm-pump' }]
  }];


export function ArticlesMegaMenu({ onClose }: ArticlesMegaMenuProps) {
  const [activeGroupId, setActiveGroupId] = useState(articleCategories[0].id);
  const activeGroup = useMemo(
    () => articleCategories.find((g) => g.id === activeGroupId) ?? articleCategories[0],
    [activeGroupId]
  );

  // Map main category to blog filter URL
  const mainArticleMap: Record<string, string> = {
    'gas-generators': '/blog?category=gas-generators',
    'gas-detectors': '/blog?category=gas-detectors',
    'plc-equipment': '/blog?category=plc-equipment',
    'lab-pumps': '/blog?category=lab-pumps'
  };

  return (
    <>
      <div className="fixed inset-0 top-0 z-[90] bg-black/50 backdrop-blur-sm" onClick={onClose} />

      <div className="absolute left-0 right-0 top-full z-[100] border-t border-border bg-popover shadow-xl">
        <div className="mx-auto max-w-[1600px] px-8 py-6">
          <div className="mb-3 flex justify-between items-center">
            <h3 className="text-sm font-bold text-foreground flex items-center gap-2">
              <BookOpen className="h-4 w-4 text-accent" />
              مقالات تخصصی بر اساس دسته‌بندی محصولات
            </h3>
          </div>

          <div className="grid grid-cols-[22%_50%_28%] gap-4">
            {/* Left: Category buttons */}
            <div className="space-y-1 rounded-xl border border-border bg-muted/30 p-2">
              {articleCategories.map((group) => {
                const Icon = group.icon;
                const isActiveGroup = activeGroup.id === group.id;
                return (
                  <button
                    key={group.id}
                    onMouseEnter={() => setActiveGroupId(group.id)}
                    onClick={() => setActiveGroupId(group.id)}
                    className={cn(
                      'group flex w-full items-center justify-between rounded-lg px-3 py-3 text-right transition-all duration-200',
                      isActiveGroup ? 'bg-primary text-primary-foreground shadow-sm' : 'text-foreground hover:bg-muted'
                    )}>

                    <span className="flex items-center gap-2 text-sm font-semibold">
                      <Icon className={cn('h-4 w-4', isActiveGroup ? 'text-accent' : 'text-primary')} />
                      {group.label}
                    </span>
                  </button>);

              })}
            </div>

            {/* Center: Subcategory article links */}
            <div className="rounded-xl border border-border bg-muted/20 p-5">
              <AnimatePresence mode="wait">
                <motion.div
                  key={activeGroup.id}
                  initial={{ opacity: 0, y: 8 }}
                  animate={{ opacity: 1, y: 0 }}
                  exit={{ opacity: 0, y: -8 }}
                  transition={{ duration: 0.15 }}
                  className="space-y-4">

                  <div>
                    <h4 className="text-base font-bold text-foreground">مقالات {activeGroup.label}</h4>
                    <p className="mt-1 text-sm text-muted-foreground">{activeGroup.description}</p>
                  </div>

                  {/* Main category article */}












                  {/* Subcategory articles */}
                  <div className="grid grid-cols-2 gap-2">
                    {activeGroup.subcategories.map((sub) =>
                      <Link
                        key={sub.id}
                        to={`/blog?category=${activeGroup.id}&type=${sub.id}`}
                        onClick={onClose}
                        className="group flex items-center justify-between rounded-lg border border-border bg-card px-4 py-3 text-sm text-foreground transition hover:border-primary/30 hover:bg-primary/5">

                        <span className="font-medium">{sub.label}</span>
                        <ChevronLeft className="h-4 w-4 text-primary/60 transition group-hover:-translate-x-0.5" />
                      </Link>
                    )}
                  </div>









                </motion.div>
              </AnimatePresence>
            </div>

            {/* Right: CTA panel */}
            <div className="min-w-0 space-y-4">





              <Link to="/blog?category=buying-guide" onClick={onClose} className="block rounded-xl border border-border bg-card p-3 transition hover:bg-muted/50">
                <p className="text-xs font-bold text-foreground">🛒 راهنمای خرید</p>
                <p className="mt-1 text-[11px] text-muted-foreground">کمک در انتخاب بهترین تجهیزات</p>
              </Link>

              <div className="rounded-xl bg-primary p-3 text-primary-foreground">
                <p className="text-xs font-bold">مشاوره رایگان با مهندس متخصص</p>
                <p className="mt-1 text-[11px] text-primary-foreground/80">تیم فنی ToolMaster آماده پاسخگویی است</p>
                <Link to="/contact" onClick={onClose} className="mt-2 inline-flex items-center gap-1 rounded-md bg-accent px-3 py-1.5 text-[11px] font-bold text-accent-foreground transition hover:bg-accent/90">
                  <Phone className="h-3 w-3" />
                  تماس با کارشناس
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </>);

}