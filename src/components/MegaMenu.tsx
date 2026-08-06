import { type ComponentType, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import { Activity, ChevronLeft, Cpu, Droplets, Flame, Gauge, Phone } from 'lucide-react';
import { NavigationItem } from '@/data/navigation';
import { cn } from '@/lib/utils';

interface MegaMenuProps {
  item: NavigationItem;
  onClose: () => void;
}

interface CategoryGroup {
  id: string;
  label: string;
  description: string;
  icon: ComponentType<{ className?: string }>;
  href: string;
  subcategories: { id: string; label: string; href: string }[];
}

const megaCategories: CategoryGroup[] = [
  {
    id: 'gas-generators', label: 'ژنراتورهای گاز',
    description: 'ژنراتورهای هیدروژن، نیتروژن و هوای خشک برای کاربردهای صنعتی و آزمایشگاهی',
    icon: Flame, href: '/products/category/gas-generators',
    subcategories: [
      { id: 'hydrogen', label: 'ژنراتور هیدروژن (صنعتی و آزمایشگاهی)', href: '/products/category/gas-generators/hydrogen-gen' },
      { id: 'nitrogen', label: 'ژنراتور نیتروژن (PSA و غشایی)', href: '/products/category/gas-generators/nitrogen-gen' },
      { id: 'dry-air', label: 'ژنراتور هوای خشک', href: '/products/category/gas-generators/dry-air-gen' },
    ],
  },
  {
    id: 'measurement', label: 'تجهیزات اندازه‌گیری و آنالیز',
    description: 'دتکتورهای گاز، فلومترها و کنترلرهای دقیق برای مانیتورینگ فرآیند',
    icon: Gauge, href: '/products/category/gas-detectors',
    subcategories: [
      { id: 'gas-detectors', label: 'دتکتورهای گاز (ثابت و پرتابل)', href: '/products/category/gas-detectors' },
      { id: 'flow-meters', label: 'فلومتر (جرمی، الکترومغناطیسی، ورتکس)', href: '/products/category/flow-meters' },
      { id: 'flow-controllers', label: 'فلوکنترلر و رکوردر', href: '/products/category/flow-meters/mass-flow-controller' },
    ],
  },
  {
    id: 'control-automation', label: 'کنترل و اتوماسیون',
    description: 'PLC، ماژول‌های ورودی/خروجی، HMI و سنسورهای صنعتی',
    icon: Cpu, href: '/products/category/plc-equipment',
    subcategories: [
      { id: 'plc', label: 'PLC و ماژول‌ها', href: '/products/category/plc-equipment/plc-cpu' },
      { id: 'hmi', label: 'HMI و پنل‌های لمسی', href: '/products/category/plc-equipment/hmi-panel' },
      { id: 'sensors', label: 'سنسورهای صنعتی', href: '/products/category/plc-equipment/plc-io' },
    ],
  },
  {
    id: 'lab-pumps', label: 'پمپ‌های آزمایشگاهی',
    description: 'پمپ خلاء، پریستالتیک و دیافراگمی برای محیط‌های آزمایشگاهی و صنعتی',
    icon: Droplets, href: '/products/category/lab-pumps',
    subcategories: [
      { id: 'vacuum', label: 'پمپ خلاء روتاری', href: '/products/category/lab-pumps/vacuum-pump' },
      { id: 'peristaltic', label: 'پمپ پریستالتیک', href: '/products/category/lab-pumps/peristaltic-pump' },
      { id: 'diaphragm', label: 'پمپ دیافراگمی', href: '/products/category/lab-pumps/diaphragm-pump' },
    ],
  },
];

export function MegaMenu({ item, onClose }: MegaMenuProps) {
  const [activeGroupId, setActiveGroupId] = useState(megaCategories[0].id);
  const activeGroup = useMemo(
    () => megaCategories.find(g => g.id === activeGroupId) ?? megaCategories[0],
    [activeGroupId]
  );

  if (item.id !== 'product-categories') return null;

  return (
    <>
      {/* Backdrop overlay */}
      <div className="fixed inset-0 top-0 z-[90] bg-black/50 backdrop-blur-sm" onClick={onClose} />

      {/* Menu content */}
      <div className="absolute left-0 right-0 top-full z-[100] border-t border-border bg-popover shadow-xl">
        <div className="mx-auto max-w-[1600px] px-8 py-6">
          <div className="mb-3 flex justify-between items-center">
            <h3 className="text-sm font-bold text-foreground">دسته‌بندی محصولات</h3>
          </div>

          <div className="grid grid-cols-[22%_50%_28%] gap-4">
            <div className="space-y-1 rounded-xl border border-border bg-muted/30 p-2">
              {megaCategories.map(group => {
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
                    )}
                  >
                    <span className="flex items-center gap-2 text-sm font-semibold">
                      <Icon className={cn('h-4 w-4', isActiveGroup ? 'text-accent' : 'text-primary')} />
                      {group.label}
                    </span>
                  </button>
                );
              })}
            </div>

            <div className="rounded-xl border border-border bg-muted/20 p-5">
              <AnimatePresence mode="wait">
                <motion.div
                  key={activeGroup.id}
                  initial={{ opacity: 0, y: 8 }}
                  animate={{ opacity: 1, y: 0 }}
                  exit={{ opacity: 0, y: -8 }}
                  transition={{ duration: 0.15 }}
                  className="space-y-4"
                >
                  <div>
                    <h4 className="text-base font-bold text-foreground">{activeGroup.label}</h4>
                    <p className="mt-1 text-sm text-muted-foreground">{activeGroup.description}</p>
                  </div>

                  <div className="grid grid-cols-2 gap-2">
                    {activeGroup.subcategories.map(sub => (
                      <Link
                        key={sub.id}
                        to={sub.href}
                        onClick={onClose}
                        className="group flex items-center justify-between rounded-lg border border-border bg-card px-4 py-3 text-sm text-foreground transition hover:border-primary/30 hover:bg-primary/5"
                      >
                        <span className="font-medium">{sub.label}</span>
                        <ChevronLeft className="h-4 w-4 text-primary/60 transition group-hover:-translate-x-0.5" />
                      </Link>
                    ))}
                  </div>

                  <Link
                    to={activeGroup.href}
                    onClick={onClose}
                    className="inline-flex items-center gap-1 text-sm font-semibold text-primary transition hover:text-primary/80"
                  >
                    مشاهده همه {activeGroup.label}
                    <ChevronLeft className="h-4 w-4" />
                  </Link>
                </motion.div>
              </AnimatePresence>
            </div>

            <div className="min-w-0 space-y-4">
              <Link to="/blog?category=buying-guide" onClick={onClose} className="block rounded-xl border border-border bg-card p-3 transition hover:bg-muted/50">
                <p className="text-xs font-bold text-foreground">📚 راهنمای انتخاب جامع</p>
                <p className="mt-1 text-[11px] text-muted-foreground">کمک در انتخاب بهترین تجهیز</p>
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
    </>
  );
}
