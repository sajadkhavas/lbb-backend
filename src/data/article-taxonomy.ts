export interface ArticleCategory {
  id: string;
  label: string;
  parentId?: string;
  description?: string;
  seoTitle?: string;
  seoDescription?: string;
}

export const articleCategories: ArticleCategory[] = [
  {
    id: 'technical',
    label: 'مقالات تخصصی',
    description: 'تحلیل فنی تجهیزات ابزار دقیق و اتوماسیون.',
    seoTitle: 'مقالات تخصصی ابزار دقیق | تول‌مستر',
    seoDescription: 'مقالات تخصصی در زمینه دتکتور گاز، فلومتر، PLC و تجهیزات آزمایشگاهی.'
  },
  {
    id: 'buying-guide',
    label: 'راهنمای خرید',
    description: 'راهنمای انتخاب تجهیزات متناسب با پروژه.',
    seoTitle: 'راهنمای خرید تجهیزات ابزار دقیق | تول‌مستر',
    seoDescription: 'راهنمای خرید ژنراتور گاز، پمپ، فلومتر و PLC با نکات کاربردی.'
  },
  {
    id: 'industrial-safety',
    label: 'ایمنی و استاندارد',
    description: 'استانداردها، کالیبراسیون و ایمنی صنعتی.',
    seoTitle: 'مقالات ایمنی صنعتی و استانداردها | تول‌مستر',
    seoDescription: 'مطالب ایمنی صنعتی، استانداردهای ATEX و روش‌های کالیبراسیون تجهیزات.'
  },
  {
    id: 'market-trends',
    label: 'فناوری و بازار',
    description: 'تحلیل روندهای فناوری و بازار صنعت.',
    seoTitle: 'تحلیل فناوری و بازار ابزار دقیق | تول‌مستر',
    seoDescription: 'اخبار، تحلیل بازار و فناوری‌های نوین در صنعت ابزار دقیق و اتوماسیون.'
  },
  { id: 'gas-generators', label: 'ژنراتورهای گاز', parentId: 'buying-guide' },
  { id: 'gas-detectors', label: 'دتکتورهای گاز', parentId: 'industrial-safety' },
  { id: 'flow-meters', label: 'فلومتر و فلوکنترلر', parentId: 'technical' },
  { id: 'plc-equipment', label: 'PLC و اتوماسیون', parentId: 'technical' },
  { id: 'lab-pumps', label: 'پمپ‌های آزمایشگاهی', parentId: 'buying-guide' },
  { id: 'calibration', label: 'کالیبراسیون', parentId: 'industrial-safety' },
];

export const mainArticleCategories = articleCategories.filter((c) => !c.parentId);

export const subCategoriesByParent = mainArticleCategories.reduce<Record<string, ArticleCategory[]>>((acc, parent) => {
  acc[parent.id] = articleCategories.filter((c) => c.parentId === parent.id);
  return acc;
}, {});
