import blogFlowmeter from '@/assets/blog/flowmeter-guide.jpg';
import blogGasDetector from '@/assets/blog/gas-detector-install.jpg';
import blogPlc from '@/assets/blog/plc-comparison.jpg';
import blogIot from '@/assets/blog/iot-instrumentation.jpg';
import blogAutomation from '@/assets/blog/automation-iran.jpg';
import blogCalibration from '@/assets/blog/calibration-importance.jpg';
import blogLabPumpsGuide from '@/assets/blog/lab-pumps-guide.jpg';
import blogCalibrationChecklist from '@/assets/blog/calibration-checklist.jpg';
import blogGasGeneratorsOverview from '@/assets/blog/gas-generators-overview.jpg';
import blogHydrogenGenerator from '@/assets/blog/hydrogen-generator-lab.jpg';
import blogNitrogenGenerator from '@/assets/blog/nitrogen-generator-industrial.jpg';
import blogDryAirGenerator from '@/assets/blog/dry-air-generator.jpg';
import blogElectromagneticFlowmeter from '@/assets/blog/electromagnetic-flowmeter.jpg';
import blogMassFlowController from '@/assets/blog/mass-flow-controller.jpg';
import blogPlcCpu from '@/assets/blog/plc-cpu-module.jpg';
import blogHmiPanel from '@/assets/blog/hmi-panel-industrial.jpg';
import blogPlcIo from '@/assets/blog/plc-io-modules.jpg';
import blogVacuumPump from '@/assets/blog/vacuum-pump-lab.jpg';
import blogPeristalticPump from '@/assets/blog/peristaltic-pump-lab.jpg';
import blogDiaphragmPump from '@/assets/blog/diaphragm-pump-lab.jpg';

export interface BlogPostSummary {
  id: string;
  title: string;
  excerpt: string;
  category: string;
  productCategories: string[];
  productTypes: string[];
  date: string;
  readTime: string;
  author: string;
  image: string;
}

export const blogPosts: BlogPostSummary[] = [
  {
    id: '1',
    title: 'راهنمای جامع انتخاب فلومتر صنعتی: الکترومغناطیسی، اولتراسونیک یا کوریولیس؟',
    excerpt: 'بررسی تخصصی انواع فلومترهای صنعتی با مقایسه دقت، رنج اندازه‌گیری، سازگاری با سیالات مختلف و هزینه نگهداری.',
    category: 'راهنمای خرید',
    productCategories: ['flow-meters'],
    productTypes: ['electromagnetic-flow', 'ultrasonic-flow'],
    date: '1403-01-20',
    readTime: '۸ دقیقه',
    author: 'مهندس محمدی',
    image: blogFlowmeter,
  },
  {
    id: '2',
    title: '۷ نکته حیاتی برای نصب و راه‌اندازی دتکتور گاز در محیط‌های ATEX',
    excerpt: 'الزامات استاندارد IEC 60079 برای نصب دتکتورهای گاز در مناطق خطرناک Zone 0، 1 و 2.',
    category: 'ایمنی صنعتی',
    productCategories: ['gas-detectors'],
    productTypes: ['multi-gas', 'toxic-detector'],
    date: '1403-01-15',
    readTime: '۱۲ دقیقه',
    author: 'مهندس رضایی',
    image: blogGasDetector,
  },
  {
    id: '3',
    title: 'مقایسه کارشناسی PLC زیمنس S7-1500، راکول CompactLogix و اشنایدر M580',
    excerpt: 'تحلیل جامع سه PLC محبوب بازار ایران از نظر سرعت پردازش، ظرفیت I/O و پروتکل‌های ارتباطی.',
    category: 'مقاله تخصصی',
    productCategories: ['plc-equipment'],
    productTypes: ['plc-cpu', 'hmi-panel'],
    date: '1403-01-10',
    readTime: '۱۰ دقیقه',
    author: 'مهندس کریمی',
    image: blogPlc,
  },
  {
    id: '4',
    title: 'اینترنت اشیاء صنعتی (IIoT) و آینده ابزار دقیق: از سنسور تا Cloud',
    excerpt: 'بررسی نقش IIoT در تحول صنعت ابزار دقیق: از سنسورهای هوشمند WirelessHART تا پلتفرم‌های ابری.',
    category: 'فناوری نوین',
    productCategories: ['plc-equipment', 'flow-meters'],
    productTypes: ['plc-cpu', 'plc-io', 'mass-flow-controller'],
    date: '1402-12-25',
    readTime: '۶ دقیقه',
    author: 'مهندس احمدی',
    image: blogIot,
  },
  {
    id: '5',
    title: 'چالش‌ها و فرصت‌های اتوماسیون صنعتی در ایران: نگاهی به آینده',
    excerpt: 'تحلیل وضعیت فعلی بازار اتوماسیون صنعتی ایران و فرصت‌های رشد در صنایع نفت و گاز و پتروشیمی.',
    category: 'تحلیل بازار',
    productCategories: ['plc-equipment', 'gas-detectors'],
    productTypes: ['plc-cpu', 'multi-gas'],
    date: '1402-12-20',
    readTime: '۸ دقیقه',
    author: 'دکتر رضایی',
    image: blogAutomation,
  },
  {
    id: '6',
    title: 'اهمیت کالیبراسیون دوره‌ای ابزار دقیق و تأثیر آن بر کیفیت تولید',
    excerpt: 'چرا کالیبراسیون منظم تجهیزات اندازه‌گیری حیاتی است؟ بررسی استاندارد ISO 17025.',
    category: 'کیفیت و استاندارد',
    productCategories: ['gas-generators', 'flow-meters', 'gas-detectors'],
    productTypes: ['nitrogen-gen', 'electromagnetic-flow', 'multi-gas'],
    date: '1402-12-15',
    readTime: '۷ دقیقه',
    author: 'مهندس موسوی',
    image: blogCalibration,
  },
  {
    id: '7',
    title: 'راهنمای انتخاب پمپ خلأ و پریستالتیک آزمایشگاهی برای کاربردهای دقیق',
    excerpt: 'مقایسه تخصصی پمپ‌های خلأ، پریستالتیک و دیافراگمی برای HPLC، نمونه‌برداری گاز و فرآیندهای آزمایشگاهی.',
    category: 'راهنمای خرید',
    productCategories: ['lab-pumps'],
    productTypes: ['vacuum-pump', 'peristaltic-pump', 'diaphragm-pump'],
    date: '1403-01-05',
    readTime: '۹ دقیقه',
    author: 'مهندس نادری',
    image: blogLabPumpsGuide,
  },
  {
    id: '8',
    title: 'چک‌لیست کامل کالیبراسیون دتکتورها و فلومترها در پروژه‌های صنعتی',
    excerpt: 'یک راهنمای عملی برای برنامه‌ریزی کالیبراسیون، انتخاب گاز مرجع، مستندسازی و کاهش توقف خط تولید.',
    category: 'کیفیت و استاندارد',
    productCategories: ['calibration'],
    productTypes: ['electromagnetic-flow', 'toxic-detector'],
    date: '1403-01-01',
    readTime: '۱۱ دقیقه',
    author: 'مهندس قاسمی',
    image: blogCalibrationChecklist,
  },
  {
    id: '9',
    title: 'راهنمای جامع ژنراتورهای گاز آزمایشگاهی و صنعتی: هیدروژن، نیتروژن و هوای خشک',
    excerpt: 'بررسی کامل انواع ژنراتورهای گاز، اصول عملکرد، مقایسه فناوری‌های PSA، غشایی و الکترولیز و راهنمای انتخاب.',
    category: 'راهنمای خرید',
    productCategories: ['gas-generators'],
    productTypes: ['hydrogen-gen', 'nitrogen-gen', 'dry-air-gen'],
    date: '1403-02-01',
    readTime: '۱۰ دقیقه',
    author: 'مهندس احمدی',
    image: blogGasGeneratorsOverview,
  },
  {
    id: '10',
    title: 'ژنراتور هیدروژن آزمایشگاهی: اصول الکترولیز PEM و کاربرد در GC و ICP',
    excerpt: 'بررسی عملکرد ژنراتورهای هیدروژن PEM برای گاز حامل کروماتوگرافی گازی و جایگزینی سیلندر هیدروژن.',
    category: 'مقاله تخصصی',
    productCategories: ['gas-generators'],
    productTypes: ['hydrogen-gen'],
    date: '1403-02-05',
    readTime: '۹ دقیقه',
    author: 'مهندس کریمی',
    image: blogHydrogenGenerator,
  },
  {
    id: '11',
    title: 'ژنراتور نیتروژن PSA و غشایی: مقایسه فنی و اقتصادی برای صنایع بسته‌بندی و آزمایشگاه',
    excerpt: 'تحلیل دو فناوری اصلی تولید نیتروژن از هوا: جذب نوسانی فشار (PSA) و غشای فیبر توخالی.',
    category: 'مقاله تخصصی',
    productCategories: ['gas-generators'],
    productTypes: ['nitrogen-gen'],
    date: '1403-02-08',
    readTime: '۱۱ دقیقه',
    author: 'مهندس محمدی',
    image: blogNitrogenGenerator,
  },
  {
    id: '12',
    title: 'ژنراتور هوای خشک و هوای صفر: کاربرد در FID و TOC و الزامات خلوص',
    excerpt: 'راهنمای انتخاب ژنراتور هوای خشک برای دستگاه‌های آنالیز FID، TOC و سیستم‌های پنوماتیک آزمایشگاهی.',
    category: 'راهنمای خرید',
    productCategories: ['gas-generators'],
    productTypes: ['dry-air-gen'],
    date: '1403-02-10',
    readTime: '۷ دقیقه',
    author: 'مهندس نادری',
    image: blogDryAirGenerator,
  },
  {
    id: '13',
    title: 'انواع دتکتور گاز صنعتی: ثابت، پرتابل و چند گازی — راهنمای انتخاب جامع',
    excerpt: 'بررسی فناوری‌های سنسور (کاتالیستی، الکتروشیمیایی، مادون قرمز) و معیارهای انتخاب دتکتور گاز مناسب.',
    category: 'راهنمای خرید',
    productCategories: ['gas-detectors'],
    productTypes: ['toxic-detector', 'flammable-detector', 'multi-gas'],
    date: '1403-02-12',
    readTime: '۱۲ دقیقه',
    author: 'مهندس رضایی',
    image: blogGasDetector,
  },
  {
    id: '14',
    title: 'فلومتر الکترومغناطیسی در صنعت آب و فاضلاب: انتخاب لاینر، الکترود و سایز مناسب',
    excerpt: 'راهنمای تخصصی انتخاب فلومتر الکترومغناطیسی با تمرکز بر جنس لاینر، مواد الکترود و محاسبه سایز.',
    category: 'مقاله تخصصی',
    productCategories: ['flow-meters'],
    productTypes: ['electromagnetic-flow'],
    date: '1403-02-15',
    readTime: '۱۰ دقیقه',
    author: 'مهندس محمدی',
    image: blogElectromagneticFlowmeter,
  },
  {
    id: '15',
    title: 'فلوکنترلر جرمی (MFC): اصول عملکرد، کالیبراسیون و کاربرد در نیمه‌هادی و آزمایشگاه',
    excerpt: 'بررسی اصول فلوکنترلر جرمی حرارتی و کوریولیس، نحوه کالیبراسیون و کاربردهای صنعتی و پژوهشی.',
    category: 'مقاله تخصصی',
    productCategories: ['flow-meters'],
    productTypes: ['mass-flow-controller'],
    date: '1403-02-18',
    readTime: '۸ دقیقه',
    author: 'مهندس کریمی',
    image: blogMassFlowController,
  },
  {
    id: '16',
    title: 'ماژول CPU در PLC: مقایسه عملکرد Siemens S7-1500، S7-1200 و Rockwell CompactLogix',
    excerpt: 'تحلیل سرعت پردازش، حافظه، پروتکل‌های ارتباطی و قابلیت‌های ایمنی ماژول‌های CPU پیشرفته.',
    category: 'مقاله تخصصی',
    productCategories: ['plc-equipment'],
    productTypes: ['plc-cpu'],
    date: '1403-02-20',
    readTime: '۱۰ دقیقه',
    author: 'مهندس کریمی',
    image: blogPlcCpu,
  },
  {
    id: '17',
    title: 'پنل HMI صنعتی: راهنمای انتخاب بر اساس سایز، رزولوشن و پروتکل ارتباطی',
    excerpt: 'مقایسه پنل‌های HMI زیمنس (Comfort Panel)، راکول (PanelView) و اشنایدر (Magelis) برای پروژه‌های اتوماسیون.',
    category: 'راهنمای خرید',
    productCategories: ['plc-equipment'],
    productTypes: ['hmi-panel'],
    date: '1403-02-22',
    readTime: '۸ دقیقه',
    author: 'مهندس احمدی',
    image: blogHmiPanel,
  },
  {
    id: '18',
    title: 'ماژول ورودی/خروجی (I/O) در سیستم‌های PLC: آنالوگ، دیجیتال و ماژول‌های ایمنی',
    excerpt: 'بررسی انواع ماژول‌های I/O، نحوه انتخاب بر اساس نوع سیگنال و الزامات ایمنی عملکردی SIL.',
    category: 'مقاله تخصصی',
    productCategories: ['plc-equipment'],
    productTypes: ['plc-io'],
    date: '1403-02-25',
    readTime: '۹ دقیقه',
    author: 'مهندس رضایی',
    image: blogPlcIo,
  },
  {
    id: '19',
    title: 'پمپ خلاء روتاری: اصول عملکرد، انتخاب روغن و نگهداری پیشگیرانه',
    excerpt: 'راهنمای جامع پمپ‌های خلاء روتاری تک‌مرحله و دو‌مرحله‌ای برای آزمایشگاه، خشک‌کن و سیستم‌های فیلتراسیون.',
    category: 'مقاله تخصصی',
    productCategories: ['lab-pumps'],
    productTypes: ['vacuum-pump'],
    date: '1403-02-28',
    readTime: '۸ دقیقه',
    author: 'مهندس نادری',
    image: blogVacuumPump,
  },
  {
    id: '20',
    title: 'پمپ پریستالتیک: اصول عملکرد، انتخاب تیوب و کاربرد در انتقال دقیق سیالات',
    excerpt: 'بررسی مکانیزم پریستالتیک، جنس تیوبینگ (سیلیکون، PharMed، Viton) و نرخ جریان قابل تنظیم.',
    category: 'مقاله تخصصی',
    productCategories: ['lab-pumps'],
    productTypes: ['peristaltic-pump'],
    date: '1403-03-01',
    readTime: '۷ دقیقه',
    author: 'مهندس موسوی',
    image: blogPeristalticPump,
  },
  {
    id: '21',
    title: 'پمپ دیافراگمی آزمایشگاهی: مقایسه با پمپ خلاء و کاربرد در نمونه‌برداری گاز',
    excerpt: 'تحلیل مزایای پمپ دیافراگمی KNF و مقایسه با پمپ‌های روتاری برای کاربردهای خشک و بدون روغن.',
    category: 'راهنمای خرید',
    productCategories: ['lab-pumps'],
    productTypes: ['diaphragm-pump'],
    date: '1403-03-05',
    readTime: '۶ دقیقه',
    author: 'مهندس قاسمی',
    image: blogDiaphragmPump,
  },
];
