import catGasGenerators from '@/assets/categories/gas-generators.jpg';
import catLabPumps from '@/assets/categories/lab-pumps.jpg';
import catGasDetectors from '@/assets/categories/gas-detectors.jpg';
import catFlowMeters from '@/assets/categories/flow-meters.jpg';
import catPlcEquipment from '@/assets/categories/plc-equipment.jpg';
import catCalibration from '@/assets/categories/calibration.jpg';

// Subcategory images (using relevant product/category images)
import catHydrogenGen from '@/assets/categories/hydrogen-gen.jpg';
import catNitrogenGen from '@/assets/categories/nitrogen-gen.jpg';
import imgAirPeak from '@/assets/products/air-generator-peak.jpg';
import imgVacuumEdwards from '@/assets/products/vacuum-pump-edwards.jpg';
import imgPeristalticWatson from '@/assets/products/peristaltic-pump-watson.jpg';
import imgPumpKnf from '@/assets/products/pump-knf.jpg';
import imgDetectorHoneywell from '@/assets/products/gas-detector-honeywell.jpg';
import imgDetectorDrager4x from '@/assets/products/gas-detector-drager-4x.jpg';
import imgDetectorDrager from '@/assets/products/detector-drager.jpg';
import imgFlowmeterEndress from '@/assets/products/flowmeter-endress.jpg';
import imgFlowBrooks from '@/assets/products/flowcontroller-brooks.jpg';
import imgFlowmeterSick from '@/assets/products/flowmeter-sick.jpg';
import imgPlcSiemens from '@/assets/products/plc-siemens.jpg';
import imgPlcIoSiemens from '@/assets/products/plc-io-siemens.jpg';
import imgHmiSiemens from '@/assets/products/hmi-siemens.jpg';

export interface CategorySubcategory {
  id: string;
  label: string;
  description: string;
  type?: string;
  image?: string;
  imageAlt?: string;
}

export interface CategoryUI {
  image: string;
  imageAlt: string;
  subcategories: CategorySubcategory[];
}

export const categoryUIData: Record<string, CategoryUI> = {
  'gas-generators': {
    image: catGasGenerators,
    imageAlt: 'ژنراتورهای گاز صنعتی و آزمایشگاهی',
    subcategories: [
      { id: 'hydrogen-gen', label: 'ژنراتور هیدروژن', description: 'ژنراتور هیدروژن صنعتی و آزمایشگاهی با فناوری PEM', type: 'hydrogen-gen', image: catHydrogenGen, imageAlt: 'ژنراتور هیدروژن PEM' },
      { id: 'nitrogen-gen', label: 'ژنراتور نیتروژن', description: 'ژنراتور نیتروژن PSA و غشایی با خلوص بالا', type: 'nitrogen-gen', image: catNitrogenGen, imageAlt: 'ژنراتور نیتروژن PSA' },
      { id: 'dry-air-gen', label: 'ژنراتور هوای خشک', description: 'ژنراتور هوای خشک بدون روغن برای آنالایزرها', type: 'dry-air-gen', image: imgAirPeak, imageAlt: 'ژنراتور هوای خشک بدون روغن' },
    ],
  },
  'lab-pumps': {
    image: catLabPumps,
    imageAlt: 'پمپ‌های آزمایشگاهی و صنعتی',
    subcategories: [
      { id: 'vacuum-pump', label: 'پمپ خلاء روتاری', description: 'پمپ خلاء دو مرحله‌ای برای تقطیر و خشک‌سازی', type: 'vacuum-pump', image: imgVacuumEdwards, imageAlt: 'پمپ خلاء روتاری Edwards' },
      { id: 'peristaltic-pump', label: 'پمپ پریستالتیک', description: 'پمپ پریستالتیک دیجیتال برای انتقال سیال حساس', type: 'peristaltic-pump', image: imgPeristalticWatson, imageAlt: 'پمپ پریستالتیک Watson-Marlow' },
      { id: 'diaphragm-pump', label: 'پمپ دیافراگمی', description: 'پمپ دیافراگمی بدون روغن مقاوم در برابر خوردگی', type: 'diaphragm-pump', image: imgPumpKnf, imageAlt: 'پمپ دیافراگمی KNF' },
    ],
  },
  'gas-detectors': {
    image: catGasDetectors,
    imageAlt: 'دتکتورهای گاز صنعتی و ایمنی',
    subcategories: [
      { id: 'toxic-detector', label: 'دتکتور گاز سمی', description: 'تشخیص CO، H₂S، NO₂ و گازهای سمی', type: 'toxic-detector', image: imgDetectorHoneywell, imageAlt: 'دتکتور گاز سمی Honeywell' },
      { id: 'flammable-detector', label: 'دتکتور گاز قابل اشتعال', description: 'تشخیص LEL متان، پروپان و هیدروکربن‌ها', type: 'flammable-detector', image: imgDetectorDrager4x, imageAlt: 'دتکتور گاز قابل اشتعال Dräger' },
      { id: 'multi-gas', label: 'دتکتور چند گازی', description: 'تشخیص همزمان ۴ تا ۶ گاز با تاییدیه ATEX', type: 'multi-gas', image: imgDetectorDrager, imageAlt: 'دتکتور چند گازی Dräger' },
    ],
  },
  'flow-meters': {
    image: catFlowMeters,
    imageAlt: 'فلومتر و فلوکنترلر صنعتی',
    subcategories: [
      { id: 'electromagnetic-flow', label: 'فلومتر الکترومغناطیسی', description: 'اندازه‌گیری جریان مایعات رسانا با دقت بالا', type: 'electromagnetic-flow', image: imgFlowmeterEndress, imageAlt: 'فلومتر الکترومغناطیسی Endress+Hauser' },
      { id: 'mass-flow-controller', label: 'فلوکنترلر جرمی', description: 'کنترل دقیق دبی گاز برای فرآیندهای حساس', type: 'mass-flow-controller', image: imgFlowBrooks, imageAlt: 'فلوکنترلر جرمی Brooks' },
      { id: 'ultrasonic-flow', label: 'فلومتر اولتراسونیک', description: 'اندازه‌گیری غیرتماسی جریان گاز و مایع', type: 'ultrasonic-flow', image: imgFlowmeterSick, imageAlt: 'فلومتر اولتراسونیک SICK' },
    ],
  },
  'plc-equipment': {
    image: catPlcEquipment,
    imageAlt: 'تجهیزات PLC و اتوماسیون صنعتی',
    subcategories: [
      { id: 'plc-cpu', label: 'ماژول CPU', description: 'پردازنده‌های PLC از Siemens، Rockwell و Schneider', type: 'plc-cpu', image: imgPlcSiemens, imageAlt: 'PLC CPU Siemens S7-1500' },
      { id: 'plc-io', label: 'ماژول ورودی/خروجی', description: 'ماژول‌های آنالوگ و دیجیتال I/O', type: 'plc-io', image: imgPlcIoSiemens, imageAlt: 'ماژول I/O Siemens' },
      { id: 'hmi-panel', label: 'پنل HMI', description: 'پنل‌های لمسی اپراتوری و مانیتورینگ', type: 'hmi-panel', image: imgHmiSiemens, imageAlt: 'پنل HMI Siemens' },
    ],
  },
  'calibration': {
    image: catCalibration,
    imageAlt: 'کالیبراسیون و لوازم جانبی ابزار دقیق',
    subcategories: [
      { id: 'cal-flowmeter', label: 'کالیبراسیون فلومتر', description: 'خدمات کالیبراسیون فلومتر مطابق ISO 17025' },
      { id: 'cal-detector', label: 'کالیبراسیون دتکتور گاز', description: 'تست و تنظیم سنسورها با گاز استاندارد' },
      { id: 'spare-parts', label: 'قطعات یدکی و لوازم جانبی', description: 'سنسور، فیلتر، کابل و گاز کالیبراسیون' },
    ],
  },
};
