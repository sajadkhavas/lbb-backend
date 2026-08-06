import type { EquipmentType } from './product-taxonomy';

export type UsageType = 'educational' | 'research' | 'industrial';
export type PriceRange = 'budget' | 'mid' | 'premium';

export const usageLabels: Record<UsageType, string> = {
  educational: 'آموزشی',
  research: 'پژوهشی',
  industrial: 'فنی / صنعتی',
};

export const priceRangeLabels: Record<PriceRange, string> = {
  budget: 'ارزان',
  mid: 'متوسط',
  premium: 'گران',
};

export const countryLabels: Record<string, string> = {
  'US': 'آمریکا',
  'UK': 'انگلستان',
  'DE': 'آلمان',
  'JP': 'ژاپن',
  'CH': 'سوئیس',
  'NL': 'هلند',
  'SE': 'سوئد',
  'FR': 'فرانسه',
  'ZA': 'آفریقای جنوبی',
};

export interface Product {
  id: string;
  name: string;
  model: string;
  type: string;
  category: string;
  brand: string;
  country: string;
  usage: UsageType[];
  priceRange: PriceRange;
  applications: string[];
  inStock: boolean;
  description?: string;
  image?: string;
  specs?: {
    range?: string;
    accuracy?: string;
    resolution?: string;
    pressure?: string;
    flowRate?: string;
    purity?: string;
    gasType?: string;
    voltage?: string;
    protocol?: string;
    ioCount?: string;
    certification?: string;
  };
}

export const products: Product[] = [
  // ──── Gas Generators ────
  {
    id: 'ng-500',
    name: 'ژنراتور نیتروژن NG-500',
    model: 'NG-500',
    type: 'nitrogen-gen',
    category: 'gas-generators',
    brand: 'Parker Hannifin',
    country: 'US',
    usage: ['research', 'industrial'],
    priceRange: 'mid',
    applications: ['GC-MS', 'LCMS', 'آنالیز عنصری', 'بلانکتینگ'],
    inStock: true,
    image: 'nitrogen-generator-parker',
    description: 'ژنراتور نیتروژن با خلوص بالا برای کاربردهای آزمایشگاهی و صنعتی. فناوری غشایی پیشرفته جهت تولید مداوم نیتروژن خالص بدون نیاز به سیلندر گاز.',
    specs: { purity: '99.999%', flowRate: '0-500 mL/min', pressure: '0-100 psi', accuracy: '±0.1%' }
  },
  {
    id: 'ng-1000',
    name: 'ژنراتور نیتروژن صنعتی NG-1000',
    model: 'NG-1000',
    type: 'nitrogen-gen',
    category: 'gas-generators',
    brand: 'Parker Hannifin',
    country: 'US',
    usage: ['industrial'],
    priceRange: 'premium',
    applications: ['صنایع غذایی', 'الکترونیک', 'متالورژی', 'بسته‌بندی'],
    inStock: true,
    image: 'nitrogen-generator-parker',
    description: 'ژنراتور نیتروژن صنعتی با ظرفیت بالا و عملکرد مداوم ۲۴/۷ برای خطوط تولید صنعتی.',
    specs: { purity: '99.99%', flowRate: '0-1000 mL/min', pressure: '0-150 psi', accuracy: '±0.05%' }
  },
  {
    id: 'hg-300',
    name: 'ژنراتور هیدروژن HG-300',
    model: 'HG-300',
    type: 'hydrogen-gen',
    category: 'gas-generators',
    brand: 'Peak Scientific',
    country: 'UK',
    usage: ['research', 'educational'],
    priceRange: 'mid',
    applications: ['GC', 'FID', 'سوخت سلولی', 'آزمایشگاه'],
    inStock: true,
    image: 'hydrogen-generator-peak',
    description: 'ژنراتور هیدروژن ایمن با فناوری PEM برای دستگاه‌های GC. تولید هیدروژن با خلوص فوق‌العاده بالا به صورت درجا.',
    specs: { purity: '99.9999%', flowRate: '0-300 mL/min', pressure: '0-100 psi', accuracy: '±0.5%' }
  },
  {
    id: 'genius-xe35',
    name: 'ژنراتور نیتروژن Genius XE 35',
    model: 'Genius XE 35',
    type: 'nitrogen-gen',
    category: 'gas-generators',
    brand: 'Peak Scientific',
    country: 'UK',
    usage: ['research'],
    priceRange: 'premium',
    applications: ['LC-MS', 'UHPLC-MS', 'آنالیز دارویی', 'تحقیقات'],
    inStock: true,
    image: 'nitrogen-generator-peak',
    description: 'ژنراتور نیتروژن آزمایشگاهی Peak Scientific با فناوری اختصاصی تولید نیتروژن، طراحی شده برای طیف‌سنج‌های جرمی LC-MS. خلوص ۹۹.۵٪ با دبی تا ۳۵ لیتر در دقیقه، حذف کامل نیاز به سیلندرهای گاز نیتروژن.',
    specs: { purity: '99.5%', flowRate: '0-35 L/min', pressure: '0-116 psi', accuracy: '±0.1%' }
  },
  {
    id: 'ag-200',
    name: 'ژنراتور هوای خشک AG-200',
    model: 'AG-200',
    type: 'dry-air-gen',
    category: 'gas-generators',
    brand: 'Peak Scientific',
    country: 'UK',
    usage: ['research', 'industrial'],
    priceRange: 'mid',
    applications: ['FTIR', 'TOC', 'آنالیز رطوبت', 'خشک‌سازی'],
    inStock: false,
    image: 'air-generator-peak',
    description: 'ژنراتور هوای خشک بدون روغن با نقطه شبنم پایین برای دستگاه‌های تحلیلی حساس.',
    specs: { flowRate: '0-200 L/min', pressure: '0-120 psi', accuracy: '±1%' }
  },

  // ──── Lab Pumps ────
  {
    id: 'vp-100',
    name: 'پمپ خلاء روتاری VP-100',
    model: 'VP-100',
    type: 'vacuum-pump',
    category: 'lab-pumps',
    brand: 'Edwards',
    country: 'UK',
    usage: ['research', 'industrial'],
    priceRange: 'mid',
    applications: ['تقطیر خلاء', 'خشک‌سازی', 'فیلتراسیون', 'SEM'],
    inStock: true,
    image: 'vacuum-pump-edwards',
    description: 'پمپ خلاء روتاری دو مرحله‌ای با عملکرد بالا و صدای کم برای کاربردهای آزمایشگاهی.',
    specs: { flowRate: '100 L/min', pressure: '10⁻³ mbar', voltage: '220V / 50Hz', accuracy: '±2%' }
  },
  {
    id: 'pp-50',
    name: 'پمپ پریستالتیک PP-50',
    model: 'PP-50',
    type: 'peristaltic-pump',
    category: 'lab-pumps',
    brand: 'Watson-Marlow',
    country: 'UK',
    usage: ['research', 'industrial'],
    priceRange: 'premium',
    applications: ['انتقال سیال', 'دوز‌بندی', 'فیلتراسیون', 'بیوتکنولوژی'],
    inStock: true,
    image: 'peristaltic-pump-watson',
    description: 'پمپ پریستالتیک دیجیتال با کنترل دقیق دبی و سرعت متغیر.',
    specs: { flowRate: '0.1-3400 mL/min', pressure: '0-3 bar', voltage: '110-240V', accuracy: '±0.5%' }
  },
  {
    id: 'n840-knf',
    name: 'پمپ دیافراگمی LABOPORT N 840',
    model: 'N 840.3 FT.18',
    type: 'diaphragm-pump',
    category: 'lab-pumps',
    brand: 'KNF',
    country: 'DE',
    usage: ['research', 'educational'],
    priceRange: 'budget',
    applications: ['انتقال گاز', 'نمونه‌برداری', 'خلاء ملایم', 'شیمی'],
    inStock: true,
    image: 'pump-knf',
    description: 'پمپ دیافراگمی بدون روغن KNF ساخت آلمان، مقاوم در برابر خوردگی مواد شیمیایی. طراحی فشرده با عملکرد بی‌صدا، مناسب برای آزمایشگاه‌های شیمی و تحقیقاتی. دبی حداکثر ۳۴ لیتر در دقیقه با طول عمر بالا.',
    specs: { flowRate: '34 L/min', pressure: '0-2 bar (فشار) / 100 mbar (خلاء)', voltage: '220V / 50Hz', accuracy: '±3%' }
  },

  // ──── Gas Detectors ────
  {
    id: 'gd-4x',
    name: 'دتکتور چند گازی پرتابل GD-4X',
    model: 'GD-4X',
    type: 'multi-gas',
    category: 'gas-detectors',
    brand: 'Dräger',
    country: 'DE',
    usage: ['industrial'],
    priceRange: 'mid',
    applications: ['ایمنی صنعتی', 'ورود به فضای بسته', 'پتروشیمی', 'معادن'],
    inStock: true,
    image: 'gas-detector-drager-4x',
    description: 'دتکتور پرتابل ۴ گازی با تاییدیه ATEX برای محیط‌های خطرناک.',
    specs: { gasType: 'O₂, LEL, CO, H₂S', accuracy: '±5% قرائت', certification: 'ATEX Zone 0, IECEx' }
  },
  {
    id: 'xam5600',
    name: 'دتکتور چندگاز X-am 5600',
    model: 'X-am 5600',
    type: 'multi-gas',
    category: 'gas-detectors',
    brand: 'Dräger',
    country: 'DE',
    usage: ['industrial'],
    priceRange: 'premium',
    applications: ['پتروشیمی', 'معادن', 'فضای بسته', 'آتش‌نشانی'],
    inStock: true,
    image: 'detector-drager',
    description: 'دتکتور گاز پرتابل Dräger X-am 5600 با قابلیت تشخیص همزمان تا ۶ گاز. مجهز به سنسورهای الکتروشیمیایی و مادون قرمز با تاییدیه IECEx و ATEX. قابلیت اتصال بیسیم Bluetooth برای انتقال داده به صورت Real-Time.',
    specs: { gasType: 'O₂, LEL, CO, H₂S, NO₂, SO₂', accuracy: '±3% قرائت', certification: 'ATEX Zone 0, IECEx, CSA' }
  },
  {
    id: 'bw-clip4',
    name: 'دتکتور ۴ گاز BW Clip4',
    model: 'BW Clip4',
    type: 'multi-gas',
    category: 'gas-detectors',
    brand: 'Honeywell',
    country: 'US',
    usage: ['industrial'],
    priceRange: 'budget',
    applications: ['ایمنی صنعتی', 'نفت و گاز', 'تعمیرات', 'فضای بسته'],
    inStock: true,
    image: 'gas-detector-honeywell',
    description: 'دتکتور ۴ گاز همزمان Honeywell BW Clip4 با سنسورهای O₂، LEL، CO و H₂S. دارای عمر باتری ۲ ساله بدون نیاز به شارژ، طراحی ضدآب و ضدضربه IP68، تاییدیه ATEX Zone 0 برای محیط‌های ضدانفجار.',
    specs: { gasType: 'O₂, LEL, CO, H₂S', accuracy: '±5% قرائت', certification: 'ATEX Zone 0, IECEx, CSA, UL' }
  },
  {
    id: 'gd-tox',
    name: 'دتکتور گاز سمی GD-TOX',
    model: 'GD-TOX',
    type: 'toxic-detector',
    category: 'gas-detectors',
    brand: 'MSA Safety',
    country: 'US',
    usage: ['industrial', 'research'],
    priceRange: 'mid',
    applications: ['آزمایشگاه شیمی', 'بیمارستان', 'صنایع شیمیایی', 'تصفیه‌خانه'],
    inStock: false,
    image: 'gas-detector-msa',
    description: 'دتکتور گاز سمی الکتروشیمیایی با حساسیت بسیار بالا.',
    specs: { gasType: 'CO, NO₂, Cl₂, NH₃, SO₂', accuracy: '±2 ppm', certification: 'ATEX, UL' }
  },

  // ──── Flow Meters & Controllers ────
  {
    id: 'promag-w400',
    name: 'فلومتر الکترومغناطیسی Promag W 400',
    model: 'Promag W 400',
    type: 'electromagnetic-flow',
    category: 'flow-meters',
    brand: 'Endress+Hauser',
    country: 'CH',
    usage: ['industrial'],
    priceRange: 'premium',
    applications: ['آب و فاضلاب', 'شیمیایی', 'غذایی', 'دارویی'],
    inStock: true,
    image: 'flowmeter-endress',
    description: 'فلومتر الکترومغناطیسی Endress+Hauser مدل Promag W 400 با دقت بالای ±۰.۲٪ طراحی شده برای صنایع آب و فاضلاب. لاینر مقاوم PFA ضد خوردگی، پروتکل‌های HART/Modbus/Profibus و قابلیت نصب در خطوط DN10 تا DN2000.',
    specs: { range: 'DN10 تا DN2000', flowRate: '0-10000 m³/h', accuracy: '±0.2%', protocol: 'HART / Modbus / Profibus' }
  },
  {
    id: 'fc-100',
    name: 'فلوکنترلر جرمی MFC-100',
    model: 'MFC-100',
    type: 'mass-flow-controller',
    category: 'flow-meters',
    brand: 'Bronkhorst',
    country: 'NL',
    usage: ['research', 'industrial'],
    priceRange: 'premium',
    applications: ['CVD', 'اسپاترینگ', 'آزمایشگاه', 'نیمه‌هادی'],
    inStock: true,
    image: 'flowcontroller-bronkhorst',
    description: 'فلوکنترلر جرمی حرارتی با کنترل دقیق گاز برای فرآیندهای حساس.',
    specs: { range: '0.1 sccm تا 50 slm', accuracy: '±0.5% مقدار واقعی', protocol: 'RS-485 / Modbus / EtherCAT', pressure: '0-10 bar' }
  },
  {
    id: 'gf80-brooks',
    name: 'فلوکنترلر جرمی GF80',
    model: 'GF80',
    type: 'mass-flow-controller',
    category: 'flow-meters',
    brand: 'Brooks Instrument',
    country: 'US',
    usage: ['research', 'industrial'],
    priceRange: 'premium',
    applications: ['نیمه‌هادی', 'CVD', 'اچینگ', 'آزمایشگاه'],
    inStock: true,
    image: 'flowcontroller-brooks',
    description: 'فلوکنترلر جرمی Brooks Instrument مدل GF80 با دقت ±۰.۵٪ مقدار واقعی. پاسخ‌دهی سریع کمتر از ۵۰۰ میلی‌ثانیه، پشتیبانی از پروتکل‌های EtherCAT و DeviceNet. طراحی شده برای فرآیندهای نیمه‌هادی و لایه‌نشانی.',
    specs: { range: '0.2 sccm تا 100 slm', accuracy: '±0.5% مقدار واقعی', protocol: 'EtherCAT / DeviceNet / RS-485', pressure: '0-7 bar' }
  },
  {
    id: 'fm-ul',
    name: 'فلومتر اولتراسونیک FM-UL',
    model: 'FM-UL',
    type: 'ultrasonic-flow',
    category: 'flow-meters',
    brand: 'SICK',
    country: 'DE',
    usage: ['industrial'],
    priceRange: 'mid',
    applications: ['گاز طبیعی', 'بخار', 'هوای فشرده', 'فلر'],
    inStock: true,
    image: 'flowmeter-sick',
    description: 'فلومتر اولتراسونیک غیرتماسی برای اندازه‌گیری بدون توقف خط.',
    specs: { range: 'DN25 تا DN3000', accuracy: '±1%', protocol: 'Modbus / HART' }
  },

  // ──── PLC Equipment ────
  {
    id: 's7-1500f',
    name: 'PLC SIMATIC S7-1500F',
    model: 'CPU 1513F-1 PN',
    type: 'plc-cpu',
    category: 'plc-equipment',
    brand: 'Siemens',
    country: 'DE',
    usage: ['industrial'],
    priceRange: 'premium',
    applications: ['اتوماسیون کارخانه', 'خطوط تولید', 'کنترل فرآیند', 'SCADA'],
    inStock: true,
    image: 'plc-siemens',
    description: 'پردازنده PLC سری S7-1500F زیمنس با امنیت یکپارچه Safety Integrated. پشتیبانی از پروتکل OPC UA و PROFINET، قابلیت پردازش تا ۶۵,۰۰۰ نقطه I/O. ایده‌آل برای کاربردهای ایمنی عملکردی SIL 3 در صنایع فرآیندی و تولیدی.',
    specs: { ioCount: 'حداکثر 65536 نقطه', protocol: 'PROFINET / PROFIBUS / OPC UA', accuracy: '±0.01%', certification: 'SIL 3, PLe' }
  },
  {
    id: 'compactlogix-5380',
    name: 'PLC CompactLogix 5380',
    model: '5069-L310ER',
    type: 'plc-cpu',
    category: 'plc-equipment',
    brand: 'Rockwell Automation',
    country: 'US',
    usage: ['industrial'],
    priceRange: 'premium',
    applications: ['خطوط تولید', 'بسته‌بندی', 'CNC', 'رباتیک'],
    inStock: true,
    image: 'plc-rockwell',
    description: 'PLC فشرده و قدرتمند Rockwell Automation مدل CompactLogix 5380 با حافظه کاربر ۴ مگابایت. پشتیبانی از EtherNet/IP و CIP Safety با زمان اسکن ۰.۲ میلی‌ثانیه. مناسب برای اتوماسیون ماشین‌آلات و خطوط بسته‌بندی.',
    specs: { ioCount: 'حداکثر 120000 نقطه', protocol: 'EtherNet/IP / CIP Safety', accuracy: '±0.02%' }
  },
  {
    id: 'modicon-m580',
    name: 'PLC Modicon M580',
    model: 'BMEP584040',
    type: 'plc-cpu',
    category: 'plc-equipment',
    brand: 'Schneider Electric',
    country: 'FR',
    usage: ['industrial'],
    priceRange: 'premium',
    applications: ['نیروگاه', 'آب و فاضلاب', 'نفت و گاز', 'معادن'],
    inStock: true,
    image: 'plc-schneider',
    description: 'اولین ePAC جهان با Ethernet یکپارچه از Schneider Electric. سایبر امنیت Achilles Level 2، ظرفیت تا ۱۲۸,۰۰۰ I/O دیجیتال. طراحی شده برای زیرساخت‌های حیاتی صنعتی.',
    specs: { ioCount: 'حداکثر 128000 نقطه', protocol: 'Ethernet / Modbus TCP / OPC UA', accuracy: '±0.01%', certification: 'Achilles Level 2, IEC 62443' }
  },
  {
    id: 'plc-io-sm',
    name: 'ماژول ورودی/خروجی SM-1231',
    model: 'SM-1231',
    type: 'plc-io',
    category: 'plc-equipment',
    brand: 'Siemens',
    country: 'DE',
    usage: ['industrial', 'educational'],
    priceRange: 'budget',
    applications: ['اندازه‌گیری آنالوگ', 'مانیتورینگ', 'جمع‌آوری داده', 'کنترل'],
    inStock: true,
    image: 'plc-io-siemens',
    description: 'ماژول ورودی آنالوگ ۸ کاناله با رزولوشن ۱۶ بیت.',
    specs: { ioCount: '8 AI / 16-bit', range: '±10V / 0-20mA / 4-20mA', accuracy: '±0.1%' }
  },
  {
    id: 'hmi-10',
    name: 'پنل HMI ده اینچ TP-1000',
    model: 'TP-1000',
    type: 'hmi-panel',
    category: 'plc-equipment',
    brand: 'Siemens',
    country: 'DE',
    usage: ['industrial', 'educational'],
    priceRange: 'mid',
    applications: ['واسط اپراتور', 'مانیتورینگ', 'کنترل فرآیند', 'خط تولید'],
    inStock: true,
    image: 'hmi-siemens',
    description: 'پنل لمسی HMI ده اینچ با نمایشگر TFT و ارتباط چندگانه.',
    specs: { resolution: '1024×600 پیکسل', protocol: 'PROFINET / Ethernet / USB' }
  },

  // ──── Brand-specific products ────
  {
    id: 'deltav-emerson',
    name: 'سیستم کنترل DeltaV S-Series',
    model: 'DeltaV S-Series',
    type: 'plc-cpu',
    category: 'plc-equipment',
    brand: 'Emerson',
    country: 'US',
    usage: ['industrial'],
    priceRange: 'premium',
    applications: ['پالایشگاه', 'پتروشیمی', 'داروسازی', 'نیروگاه'],
    inStock: true,
    image: 'dcs-emerson',
    description: 'سیستم کنترل توزیع‌شده Emerson DeltaV S-Series با معماری باز و قابل توسعه. تحلیل پیش‌بینانه با هوش مصنوعی و یکپارچگی کامل با پروتکل‌های IIoT. سیستم مدیریت فرآیند نسل جدید برای صنایع حساس.',
    specs: { ioCount: 'نامحدود (توسعه‌پذیر)', protocol: 'HART / Foundation Fieldbus / WirelessHART', certification: 'SIL 3, IEC 61508' }
  },
  {
    id: 'acs580-abb',
    name: 'درایو فرکانس متغیر ACS580',
    model: 'ACS580-01',
    type: 'plc-io',
    category: 'plc-equipment',
    brand: 'ABB',
    country: 'SE',
    usage: ['industrial'],
    priceRange: 'mid',
    applications: ['پمپ‌ها', 'فن‌ها', 'کمپرسورها', 'کانوایرها'],
    inStock: true,
    image: 'drive-abb',
    description: 'درایو فرکانس متغیر ABB مدل ACS580 با توان ۰.۷۵ تا ۵۰۰ کیلووات. رابط کاربری لمسی هوشمند، بهینه‌سازی مصرف انرژی تا ۳۰٪. مجهز به کنترل برداری بدون سنسور و حفاظت‌های جامع.',
    specs: { voltage: '380-480V 3AC', protocol: 'Modbus RTU / PROFINET / EtherNet/IP', accuracy: '±0.5% سرعت' }
  },
  {
    id: 'centum-vp-yokogawa',
    name: 'سیستم CENTUM VP R6',
    model: 'CENTUM VP R6',
    type: 'plc-cpu',
    category: 'plc-equipment',
    brand: 'Yokogawa',
    country: 'JP',
    usage: ['industrial'],
    priceRange: 'premium',
    applications: ['پالایشگاه', 'LNG', 'نیروگاه', 'پتروشیمی'],
    inStock: true,
    image: 'dcs-yokogawa',
    description: 'سیستم کنترل توزیع‌شده Yokogawa CENTUM VP R6 با قابلیت اطمینان ۹۹.۹۹۹۹۹٪. معماری جفت‌شده برای افزونگی کامل و یکپارچگی با سیستم ایمنی ProSafe-RS. استاندارد طلایی صنعت فرآیندی.',
    specs: { ioCount: 'حداکثر 40000 نقطه', protocol: 'FOUNDATION Fieldbus / HART / ISA100', certification: 'SIL 3, IEC 61511' }
  },
];

// Extract unique brands from products
export const productBrands = [...new Set(products.map(p => p.brand))].sort();

// Extract unique countries from products
export const productCountries = [...new Set(products.map(p => p.country))].sort();
