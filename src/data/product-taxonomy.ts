export const productCategories = {
  'gas-generators': {
    id: 'gas-generators',
    label: 'ژنراتورهای گاز',
    description: 'ژنراتورهای هیدروژن، نیتروژن و هوای خشک'
  },
  'lab-pumps': {
    id: 'lab-pumps',
    label: 'پمپ‌های آزمایشگاهی',
    description: 'پمپ‌های خلاء، پریستالتیک و دیافراگمی'
  },
  'gas-detectors': {
    id: 'gas-detectors',
    label: 'دتکتورهای گاز',
    description: 'دتکتورهای گاز سمی، قابل اشتعال و چند گازی'
  },
  'flow-meters': {
    id: 'flow-meters',
    label: 'فلومتر و فلوکنترلر',
    description: 'فلومترها و کنترلرهای جریان صنعتی'
  },
  'plc-equipment': {
    id: 'plc-equipment',
    label: 'تجهیزات PLC',
    description: 'ماژول‌های PLC، CPU و پنل‌های HMI'
  },
  'calibration': {
    id: 'calibration',
    label: 'کالیبراسیون و لوازم جانبی',
    description: 'گاز کالیبراسیون، قطعات یدکی و خدمات دوره‌ای ابزار دقیق'
  }
} as const;

export const equipmentTypes = {
  // Gas Generators
  'hydrogen-gen': { label: 'ژنراتور هیدروژن', category: 'gas-generators', fullName: 'Hydrogen Generator' },
  'nitrogen-gen': { label: 'ژنراتور نیتروژن', category: 'gas-generators', fullName: 'Nitrogen Generator' },
  'dry-air-gen': { label: 'ژنراتور هوای خشک', category: 'gas-generators', fullName: 'Dry Air Generator' },

  // Lab Pumps
  'vacuum-pump': { label: 'پمپ خلاء روتاری', category: 'lab-pumps', fullName: 'Rotary Vacuum Pump' },
  'peristaltic-pump': { label: 'پمپ پریستالتیک', category: 'lab-pumps', fullName: 'Peristaltic Pump' },
  'diaphragm-pump': { label: 'پمپ دیافراگمی', category: 'lab-pumps', fullName: 'Diaphragm Pump' },

  // Gas Detectors
  'toxic-detector': { label: 'دتکتور گاز سمی', category: 'gas-detectors', fullName: 'Toxic Gas Detector' },
  'flammable-detector': { label: 'دتکتور گاز قابل اشتعال', category: 'gas-detectors', fullName: 'Flammable Gas Detector' },
  'multi-gas': { label: 'دتکتور چند گازی', category: 'gas-detectors', fullName: 'Multi-Gas Detector' },

  // Flow Meters & Controllers
  'electromagnetic-flow': { label: 'فلومتر الکترومغناطیسی', category: 'flow-meters', fullName: 'Electromagnetic Flow Meter' },
  'mass-flow-controller': { label: 'فلوکنترلر جرمی', category: 'flow-meters', fullName: 'Mass Flow Controller' },
  'ultrasonic-flow': { label: 'فلومتر اولتراسونیک', category: 'flow-meters', fullName: 'Ultrasonic Flow Meter' },

  // PLC Equipment
  'plc-cpu': { label: 'ماژول CPU', category: 'plc-equipment', fullName: 'PLC CPU Module' },
  'plc-io': { label: 'ماژول ورودی/خروجی', category: 'plc-equipment', fullName: 'PLC I/O Module' },
  'hmi-panel': { label: 'پنل HMI', category: 'plc-equipment', fullName: 'HMI Touch Panel' },
} as const;

export type ProductCategory = keyof typeof productCategories;
export type EquipmentType = keyof typeof equipmentTypes;

// Keep backward compatibility alias
export type PolymerType = EquipmentType;
export const polymerTypes = equipmentTypes;
