export interface NavigationItem {
  id: string;
  label: string;
  href?: string;
  icon?: string;
  description?: string;
  children?: NavigationItem[];
}

export const navigationData: NavigationItem[] = [
  {
    id: 'product-categories',
    label: 'دسته‌بندی محصولات',
    href: '/products',
    description: 'مرور کامل تجهیزات ابزار دقیق و اتوماسیون صنعتی',
    children: [
      {
        id: 'gas-generators',
        label: 'ژنراتورهای گاز',
        href: '/products/category/gas-generators',
        icon: 'Flame',
        description: 'ژنراتورهای هیدروژن، نیتروژن و هوای خشک',
        children: [
          { id: 'hydrogen-gen', label: 'ژنراتور هیدروژن (صنعتی و آزمایشگاهی)', href: '/products/category/gas-generators/hydrogen-gen' },
          { id: 'nitrogen-gen', label: 'ژنراتور نیتروژن (PSA و غشایی)', href: '/products/category/gas-generators/nitrogen-gen' },
          { id: 'dry-air-gen', label: 'ژنراتور هوای خشک', href: '/products/category/gas-generators/dry-air-gen' },
        ]
      },
      {
        id: 'measurement',
        label: 'تجهیزات اندازه‌گیری و آنالیز',
        href: '/products/category/gas-detectors',
        icon: 'Gauge',
        description: 'دتکتور گاز، فلومتر و کنترلر',
        children: [
          { id: 'gas-detectors', label: 'دتکتورهای گاز (ثابت و پرتابل)', href: '/products/category/gas-detectors' },
          { id: 'flow-meters', label: 'فلومتر (جرمی، الکترومغناطیسی، ورتکس)', href: '/products/category/flow-meters' },
          { id: 'flow-controllers', label: 'فلوکنترلر و رکوردر', href: '/products/category/flow-meters/mass-flow-controller' },
        ]
      },
      {
        id: 'control-automation',
        label: 'کنترل و اتوماسیون',
        href: '/products/category/plc-equipment',
        icon: 'Cpu',
        description: 'PLC، HMI و سنسورهای صنعتی',
        children: [
          { id: 'plc-modules', label: 'PLC و ماژول‌ها', href: '/products/category/plc-equipment/plc-cpu' },
          { id: 'hmi-panels', label: 'HMI و پنل‌های لمسی', href: '/products/category/plc-equipment/hmi-panel' },
          { id: 'industrial-sensors', label: 'سنسورهای صنعتی', href: '/products/category/plc-equipment/plc-io' },
        ]
      },
      {
        id: 'lab-pumps',
        label: 'پمپ‌های آزمایشگاهی',
        href: '/products/category/lab-pumps',
        icon: 'Droplets',
        description: 'پمپ خلاء، پریستالتیک و دیافراگمی',
        children: [
          { id: 'vacuum-pump', label: 'پمپ خلاء روتاری', href: '/products/category/lab-pumps/vacuum-pump' },
          { id: 'peristaltic-pump', label: 'پمپ پریستالتیک', href: '/products/category/lab-pumps/peristaltic-pump' },
          { id: 'diaphragm-pump', label: 'پمپ دیافراگمی', href: '/products/category/lab-pumps/diaphragm-pump' },
        ]
      },
    ]
  },
  {
    id: 'brands',
    label: 'برندها',
    href: '/brands',
    description: '۱۲ برند معتبر ابزار دقیق و اتوماسیون صنعتی',
    children: [
      { id: 'siemens', label: 'Siemens', href: '/brands/siemens' },
      { id: 'endress', label: 'Endress+Hauser', href: '/brands/endress-hauser' },
      { id: 'honeywell', label: 'Honeywell', href: '/brands/honeywell' },
      { id: 'emerson', label: 'Emerson', href: '/brands/emerson' },
      { id: 'abb', label: 'ABB', href: '/brands/abb' },
      { id: 'rockwell', label: 'Rockwell Automation', href: '/brands/rockwell' },
      { id: 'peak', label: 'Peak Scientific', href: '/brands/peak' },
      { id: 'drager', label: 'Dräger', href: '/brands/drager' },
      { id: 'knf', label: 'KNF', href: '/brands/knf' },
      { id: 'yokogawa', label: 'Yokogawa', href: '/brands/yokogawa' },
      { id: 'brooks', label: 'Brooks Instrument', href: '/brands/brooks' },
      { id: 'schneider', label: 'Schneider Electric', href: '/brands/schneider' },
    ]
  },
  {
    id: 'articles',
    label: 'مقالات و راهنما',
    href: '/blog',
    children: [
      { id: 'buying-guide', label: 'راهنمای خرید', href: '/blog?category=buying-guide' },
      { id: 'technical-training', label: 'آموزش تخصصی', href: '/blog?category=technical' },
      { id: 'market-analysis', label: 'تحلیل بازار', href: '/blog?category=market' },
      { id: 'industry-news', label: 'اخبار صنعت', href: '/blog?category=news' },
    ]
  },
  {
    id: 'projects',
    label: 'پروژه‌ها',
    href: '/projects',
  },
  {
    id: 'about',
    label: 'درباره ما',
    href: '/about',
    children: [
      { id: 'company', label: 'معرفی شرکت', href: '/about' },
      { id: 'contact', label: 'تماس با ما', href: '/contact' },
    ]
  },
];
