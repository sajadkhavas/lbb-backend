import DOMPurify from 'dompurify';
import { useState, useEffect, useMemo, useRef, type Dispatch, type SetStateAction } from 'react';
import { Link, useNavigate, useParams, useSearchParams } from 'react-router-dom';
import { motion } from 'framer-motion';
import type { CheckedState } from '@radix-ui/react-checkbox';
import { Helmet } from 'react-helmet-async';
import { SEO } from '@/components/SEO';
import { generateBreadcrumbSchema } from '@/lib/structured-data';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Checkbox } from '@/components/ui/checkbox';
import { Search, Filter, Settings, CheckCircle, Download, X, ChevronDown, ChevronUp, ChevronLeft, ChevronRight } from 'lucide-react';
import { useRFQ } from '@/contexts/RFQContext';
import { useToast } from '@/hooks/use-toast';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { products as staticProducts, productBrands, productCountries, countryLabels, usageLabels, priceRangeLabels, type Product, type UsageType, type PriceRange } from '@/data/products';
import { productCategories, equipmentTypes } from '@/data/product-taxonomy';
import { categorySEOData } from '@/data/category-seo';
import { subcategorySEOData } from '@/data/subcategory-seo';
import { categoryUIData } from '@/data/category-ui';
import { generateSupportiveSeoHtml } from '@/lib/seo-content';
import { productService } from '@/api/services';

import prodAirPeak from '@/assets/products/air-generator-peak.jpg';
import prodDcSEmerson from '@/assets/products/dcs-emerson.jpg';
import prodDcSYokogawa from '@/assets/products/dcs-yokogawa.jpg';
import prodDetectorDrager from '@/assets/products/detector-drager.jpg';
import prodDriveAbb from '@/assets/products/drive-abb.jpg';
import prodFlowcontrollerBronkhorst from '@/assets/products/flowcontroller-bronkhorst.jpg';
import prodFlowcontrollerBrooks from '@/assets/products/flowcontroller-brooks.jpg';
import prodFlowmeterEndress from '@/assets/products/flowmeter-endress.jpg';
import prodFlowmeterSick from '@/assets/products/flowmeter-sick.jpg';
import prodGasDetectorDrager4x from '@/assets/products/gas-detector-drager-4x.jpg';
import prodGasDetectorHoneywell from '@/assets/products/gas-detector-honeywell.jpg';
import prodGasDetectorMsa from '@/assets/products/gas-detector-msa.jpg';
import prodHmiSiemens from '@/assets/products/hmi-siemens.jpg';
import prodHydrogenPeak from '@/assets/products/hydrogen-generator-peak.jpg';
import prodNitrogenParker from '@/assets/products/nitrogen-generator-parker.jpg';
import prodNitrogenPeak from '@/assets/products/nitrogen-generator-peak.jpg';
import prodPeristalticWatson from '@/assets/products/peristaltic-pump-watson.jpg';
import prodPlcIoSiemens from '@/assets/products/plc-io-siemens.jpg';
import prodPlcRockwell from '@/assets/products/plc-rockwell.jpg';
import prodPlcSchneider from '@/assets/products/plc-schneider.jpg';
import prodPlcSiemens from '@/assets/products/plc-siemens.jpg';
import prodPumpKnf from '@/assets/products/pump-knf.jpg';
import prodVacuumEdwards from '@/assets/products/vacuum-pump-edwards.jpg';
import prodPlaceholder from '@/assets/products/plc-siemens.jpg';

const imageMap: Record<string, string> = {
  'air-generator-peak': prodAirPeak,
  'dcs-emerson': prodDcSEmerson,
  'dcs-yokogawa': prodDcSYokogawa,
  'detector-drager': prodDetectorDrager,
  'drive-abb': prodDriveAbb,
  'flowcontroller-bronkhorst': prodFlowcontrollerBronkhorst,
  'flowcontroller-brooks': prodFlowcontrollerBrooks,
  'flowmeter-endress': prodFlowmeterEndress,
  'flowmeter-sick': prodFlowmeterSick,
  'gas-detector-drager-4x': prodGasDetectorDrager4x,
  'gas-detector-honeywell': prodGasDetectorHoneywell,
  'gas-detector-msa': prodGasDetectorMsa,
  'hmi-siemens': prodHmiSiemens,
  'hydrogen-generator-peak': prodHydrogenPeak,
  'nitrogen-generator-parker': prodNitrogenParker,
  'nitrogen-generator-peak': prodNitrogenPeak,
  'peristaltic-pump-watson': prodPeristalticWatson,
  'plc-io-siemens': prodPlcIoSiemens,
  'plc-rockwell': prodPlcRockwell,
  'plc-schneider': prodPlcSchneider,
  'plc-siemens': prodPlcSiemens,
  'pump-knf': prodPumpKnf,
  'vacuum-pump-edwards': prodVacuumEdwards,
};

function resolveImage(image: string | null | undefined): string {
  if (!image) return prodPlaceholder;
  if (image.startsWith('http')) return image;
  return imageMap[image] ?? prodPlaceholder;
}

const parseMultiParam = (value: string | null) => value ? value.split(',').filter(Boolean) : [];

// ── FIX 1: آپدیت apiToLocal — از data.data خوانده میشه نه data مستقیم ──────────
function apiToLocal(p: any): Product {
  return {
    id: p.id,
    slug: p.slug,
    name: p.name,
    model: p.model,
    type: p.type,
    category: p.category,
    brand: p.brand,
    country: p.country,
    usage: p.usage ?? [],
    priceRange: p.priceRange ?? 'mid',
    inStock: p.inStock ?? true,
    isFeatured: p.isFeatured ?? false,
    description: p.description ?? '',
    image: p.image ?? null,
    applications: p.applications ?? [],
    specs: p.specs ?? {},
  } as Product;
}

// ── FIX 1: تعداد آیتم در هر صفحه ──────────────────────────────────────────────
const ITEMS_PER_PAGE = 12;

export default function Products() {
  const { slug, subSlug } = useParams<{ slug?: string; subSlug?: string }>();
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();
  const [searchQuery, setSearchQuery] = useState('');

  const [apiProducts, setApiProducts] = useState<Product[]>([]);
  const [apiLoading, setApiLoading] = useState(true);
  const [apiError, setApiError] = useState(false);

  // ── FIX 1: pagination state ────────────────────────────────────────────────
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [totalItems, setTotalItems] = useState(0);

  useEffect(() => {
    let cancelled = false;
    setApiLoading(true);
    // ── FIX 1: پاس دادن page به API ──────────────────────────────────────────
    productService.getAll({ per_page: ITEMS_PER_PAGE, page: currentPage })
      .then((data: any) => {
        if (cancelled) return;
        // ── FIX 1: ساپورت هر دو فرمت — data مستقیم آرایه یا data.data آرایه ──
        const items = Array.isArray(data) ? data : (data?.data ?? []);
        const meta = data?.meta;
        if (items.length > 0) setApiProducts(items.map(apiToLocal));
        // ── FIX 1: ذخیره pagination meta ──────────────────────────────────────
        if (meta) {
          setTotalPages(meta.last_page ?? 1);
          setTotalItems(meta.total ?? items.length);
        }
        setApiLoading(false);
      })
      .catch(() => {
        if (!cancelled) { setApiError(true); setApiLoading(false); }
      });
    return () => { cancelled = true; };
  }, [currentPage]); // ← وقتی صفحه عوض میشه دوباره fetch میکنه

  // ── FIX 1: وقتی فیلتر عوض میشه برگرد به صفحه اول ───────────────────────────
  const resetPage = () => setCurrentPage(1);

  const products = apiProducts.length > 0 ? apiProducts : staticProducts;

  const resolvedInitialSlug = slug ? (slug === 'measurement' ? 'gas-detectors' : slug) : null;

  const [selectedCategories, setSelectedCategories] = useState<string[]>(() => {
    if (resolvedInitialSlug) return [resolvedInitialSlug];
    return parseMultiParam(searchParams.get('category'));
  });
  const [selectedTypes, setSelectedTypes] = useState<string[]>(() => {
    if (subSlug && subcategorySEOData[subSlug]) return [subSlug];
    if (slug) return [];
    return parseMultiParam(searchParams.get('type'));
  });
  const [selectedBrands, setSelectedBrands] = useState<string[]>(() => slug ? [] : parseMultiParam(searchParams.get('brand')));
  const [selectedCountries, setSelectedCountries] = useState<string[]>(() => slug ? [] : parseMultiParam(searchParams.get('country')));
  const [selectedUsages, setSelectedUsages] = useState<UsageType[]>(() => slug ? [] : parseMultiParam(searchParams.get('usage')) as UsageType[]);
  const [selectedPriceRanges, setSelectedPriceRanges] = useState<PriceRange[]>(() => slug ? [] : parseMultiParam(searchParams.get('price')) as PriceRange[]);

  const [expandedCategories, setExpandedCategories] = useState<string[]>(() => {
    if (resolvedInitialSlug) return [resolvedInitialSlug];
    const cats = parseMultiParam(searchParams.get('category'));
    return cats.length > 0 ? cats : [];
  });
  const [expandedSections, setExpandedSections] = useState<Record<string, boolean>>({
    brands: true, countries: true, usage: true, price: true,
  });

  const { addProduct } = useRFQ();
  const { toast } = useToast();

  const isInternalUrlUpdate = useRef(false);
  const skipNextUrlSync = useRef(true);
  const slugJustChanged = useRef(false);

  useEffect(() => {
    if (slug) {
      slugJustChanged.current = true;
      const resolvedCat = slug === 'measurement' ? 'gas-detectors' : slug;
      setSelectedCategories([resolvedCat]);
      setExpandedCategories([resolvedCat]);
      if (subSlug && subcategorySEOData[subSlug]) {
        setSelectedTypes([subSlug]);
      } else {
        setSelectedTypes([]);
      }
      setSelectedBrands([]);
      setSelectedCountries([]);
      setSelectedUsages([]);
      setSelectedPriceRanges([]);
      setSearchQuery('');
      resetPage(); // ← FIX 1
    }
  }, [slug, subSlug]);

  useEffect(() => {
    if (slug) return;
    if (isInternalUrlUpdate.current) { isInternalUrlUpdate.current = false; return; }
    const categories = parseMultiParam(searchParams.get('category'));
    const types = parseMultiParam(searchParams.get('type'));
    const brands = parseMultiParam(searchParams.get('brand'));
    const countries = parseMultiParam(searchParams.get('country'));
    const usages = parseMultiParam(searchParams.get('usage')) as UsageType[];
    const prices = parseMultiParam(searchParams.get('price')) as PriceRange[];
    const search = searchParams.get('search') || '';
    setSelectedCategories(categories);
    setSelectedTypes(types);
    setSelectedBrands(brands);
    setSelectedCountries(countries);
    setSelectedUsages(usages);
    setSelectedPriceRanges(prices);
    setSearchQuery(search);
    if (categories.length > 0) setExpandedCategories(prev => [...new Set([...prev, ...categories])]);
    skipNextUrlSync.current = true;
    resetPage(); // ← FIX 1
  }, [searchParams, slug]);

  useEffect(() => {
    if (slug) return;
    if (skipNextUrlSync.current) { skipNextUrlSync.current = false; return; }
    const params = new URLSearchParams();
    if (selectedCategories.length > 0) params.set('category', selectedCategories.join(','));
    if (selectedTypes.length > 0) params.set('type', selectedTypes.join(','));
    if (selectedBrands.length > 0) params.set('brand', selectedBrands.join(','));
    if (selectedCountries.length > 0) params.set('country', selectedCountries.join(','));
    if (selectedUsages.length > 0) params.set('usage', selectedUsages.join(','));
    if (selectedPriceRanges.length > 0) params.set('price', selectedPriceRanges.join(','));
    if (searchQuery) params.set('search', searchQuery);
    isInternalUrlUpdate.current = true;
    setSearchParams(params, { replace: true });
  }, [selectedCategories, selectedTypes, selectedBrands, selectedCountries, selectedUsages, selectedPriceRanges, searchQuery, setSearchParams, slug]);

  useEffect(() => {
    if (selectedCategories.length === 0) return;
    setSelectedTypes(prev => {
      const pruned = prev.filter(typeKey => {
        const type = equipmentTypes[typeKey as keyof typeof equipmentTypes];
        return type && selectedCategories.includes(type.category);
      });
      return pruned.length === prev.length ? prev : pruned;
    });
  }, [selectedCategories]);

  useEffect(() => {
    if (!slug) return;
    if (slugJustChanged.current) { slugJustChanged.current = false; return; }
    const resolvedSlug = slug === 'measurement' ? 'gas-detectors' : slug;
    const matchesSlug = selectedCategories.length === 1 && selectedCategories[0] === resolvedSlug;
    const matchesSubSlug = subSlug
      ? (selectedTypes.length === 1 && selectedTypes[0] === subSlug)
      : selectedTypes.length === 0;
    const hasNoOtherFilters =
      selectedBrands.length === 0 && selectedCountries.length === 0 &&
      selectedUsages.length === 0 && selectedPriceRanges.length === 0 && searchQuery === '';
    if (matchesSlug && matchesSubSlug && hasNoOtherFilters) return;
    const params = new URLSearchParams();
    if (selectedCategories.length) params.set('category', selectedCategories.join(','));
    if (selectedTypes.length) params.set('type', selectedTypes.join(','));
    if (selectedBrands.length) params.set('brand', selectedBrands.join(','));
    if (selectedCountries.length) params.set('country', selectedCountries.join(','));
    if (selectedUsages.length) params.set('usage', selectedUsages.join(','));
    if (selectedPriceRanges.length) params.set('price', selectedPriceRanges.join(','));
    if (searchQuery) params.set('search', searchQuery);
    navigate(`/products?${params.toString()}`, { replace: true });
  }, [slug, subSlug, selectedCategories, selectedTypes, selectedBrands, selectedCountries, selectedUsages, selectedPriceRanges, searchQuery, navigate]);

  const toggleFilter = <T extends string>(arr: T[], val: T, setter: Dispatch<SetStateAction<T[]>>) => {
    setter(prev => prev.includes(val) ? prev.filter(v => v !== val) : [...prev, val]);
    resetPage(); // ← FIX 1
  };

  const toggleCategory = (catKey: string) => {
    setExpandedCategories(prev => prev.includes(catKey) ? prev.filter(c => c !== catKey) : [...prev, catKey]);
  };

  const toggleSection = (sectionKey: string) => {
    setExpandedSections(prev => ({ ...prev, [sectionKey]: !prev[sectionKey] }));
  };

  const activeFilterCount = selectedCategories.length + selectedTypes.length + selectedBrands.length + selectedCountries.length + selectedUsages.length + selectedPriceRanges.length;

  const clearAllFilters = () => {
    setSelectedCategories([]); setSelectedTypes([]); setSelectedBrands([]);
    setSelectedCountries([]); setSelectedUsages([]); setSelectedPriceRanges([]);
    setSearchQuery('');
    resetPage(); // ← FIX 1
  };

  // ── FIX 1: اگه API pagination داره، filteredProducts همون apiProducts هست ──
  // اگه static data داره یا API بدون pagination، فیلتر کلاینت‌ساید انجام میشه
  const filteredProducts = useMemo(() => {
    if (apiProducts.length > 0) {
      // API نتایج فیلترشده رو برمیگردونه — فقط نمایش بده
      return apiProducts;
    }
    // fallback به static data با فیلتر کلاینت‌ساید
    return staticProducts.filter(product => {
      const matchesSearch = !searchQuery ||
        product.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        product.model.toLowerCase().includes(searchQuery.toLowerCase()) ||
        product.brand.toLowerCase().includes(searchQuery.toLowerCase());
      const matchesCategory = selectedCategories.length === 0 || selectedCategories.includes(product.category);
      const matchesType = selectedTypes.length === 0 || selectedTypes.includes(product.type);
      const matchesBrand = selectedBrands.length === 0 || selectedBrands.includes(product.brand);
      const matchesCountry = selectedCountries.length === 0 || selectedCountries.includes(product.country);
      const matchesUsage = selectedUsages.length === 0 || selectedUsages.some(u => product.usage.includes(u));
      const matchesPrice = selectedPriceRanges.length === 0 || selectedPriceRanges.includes(product.priceRange);
      return matchesSearch && matchesCategory && matchesType && matchesBrand && matchesCountry && matchesUsage && matchesPrice;
    });
  }, [apiProducts, staticProducts, searchQuery, selectedCategories, selectedTypes, selectedBrands, selectedCountries, selectedUsages, selectedPriceRanges]);

  // ── FIX 1: pagination کلاینت‌ساید برای static data ─────────────────────────
  const staticTotalPages = Math.ceil(filteredProducts.length / ITEMS_PER_PAGE);
  const paginatedProducts = apiProducts.length > 0
    ? filteredProducts // API خودش صفحه‌بندی کرده
    : filteredProducts.slice((currentPage - 1) * ITEMS_PER_PAGE, currentPage * ITEMS_PER_PAGE);

  const effectiveTotalPages = apiProducts.length > 0 ? totalPages : staticTotalPages;
  const effectiveTotalItems = apiProducts.length > 0 ? totalItems : filteredProducts.length;

  const mainCategoryGroups = useMemo(() => {
    const groups = [
      { key: 'gas-generators', label: 'ژنراتورهای گاز', categories: ['gas-generators'] },
      { key: 'gas-detectors', label: 'دتکتورهای گاز', categories: ['gas-detectors'] },
      { key: 'flow-meters', label: 'فلومتر و فلوکنترلر', categories: ['flow-meters'] },
      { key: 'plc-equipment', label: 'تجهیزات PLC', categories: ['plc-equipment'] },
      { key: 'lab-pumps', label: 'پمپ‌های آزمایشگاهی', categories: ['lab-pumps'] },
      { key: 'calibration', label: 'کالیبراسیون و لوازم جانبی', categories: ['calibration'] },
    ];
    return groups.map(group => ({
      ...group,
      typeEntries: Object.entries(equipmentTypes).filter(([_, type]) => group.categories.includes(type.category)),
    }));
  }, []);

  const allMainCategories = useMemo(() => mainCategoryGroups.flatMap(g => g.categories), [mainCategoryGroups]);
  const allMainTypes = useMemo(() => mainCategoryGroups.flatMap(g => g.typeEntries.map(([key]) => key)), [mainCategoryGroups]);
  const isAllProductsSelected = allMainCategories.every(cat => selectedCategories.includes(cat));

  const toggleMainGroup = (group: (typeof mainCategoryGroups)[number]) => {
    const isGroupSelected = group.categories.every(cat => selectedCategories.includes(cat));
    if (isGroupSelected) {
      setSelectedCategories(prev => prev.filter(cat => !group.categories.includes(cat)));
      setSelectedTypes(prev => prev.filter(t => !group.typeEntries.some(([key]) => key === t)));
    } else {
      setSelectedCategories(prev => [...new Set([...prev, ...group.categories])]);
      setExpandedCategories(prev => prev.includes(group.key) ? prev : [...prev, group.key]);
    }
    resetPage(); // ← FIX 1
  };

  const getGroupCheckedState = (group: (typeof mainCategoryGroups)[number]): CheckedState => {
    const selectedCount = group.categories.filter(cat => selectedCategories.includes(cat)).length;
    if (selectedCount === 0) return false;
    if (selectedCount === group.categories.length) return true;
    return 'indeterminate';
  };

  const toggleAllProducts = () => {
    if (isAllProductsSelected) {
      setSelectedCategories([]); setSelectedTypes([]);
    } else {
      setSelectedCategories(allMainCategories);
      setSelectedTypes(allMainTypes);
      setExpandedCategories(mainCategoryGroups.map(g => g.key));
    }
    resetPage(); // ← FIX 1
  };

  const availableBrands = useMemo(() => {
    const source = products;
    if (selectedCategories.length === 0) return [...new Set(source.map(p => p.brand))].sort();
    return [...new Set(source.filter(p => selectedCategories.includes(p.category)).map(p => p.brand))].sort();
  }, [products, selectedCategories]);

  const availableCountries = productCountries;

  const seoContent = useMemo(() => {
    if (selectedTypes.length === 1 && selectedCategories.length === 1) {
      const subData = subcategorySEOData[selectedTypes[0]];
      if (subData) return { type: 'sub' as const, data: subData };
    }
    if (selectedCategories.length === 1) {
      const catData = categorySEOData[selectedCategories[0] as keyof typeof categorySEOData];
      if (catData) return { type: 'cat' as const, data: catData };
    }
    return { type: 'general' as const, data: null };
  }, [selectedCategories, selectedTypes]);

  const canonicalUrl = useMemo(() => {
    if (slug && subSlug) return `https://toolmaster.com/products/category/${slug}/${subSlug}`;
    if (slug) return `https://toolmaster.com/products/category/${slug}`;
    if (seoContent.type === 'sub' && seoContent.data) return `https://toolmaster.com/products/category/${seoContent.data.parentSlug}/${seoContent.data.slug}`;
    if (seoContent.type === 'cat' && seoContent.data) return `https://toolmaster.com/products/category/${seoContent.data.slug}`;
    return 'https://toolmaster.com/products';
  }, [slug, subSlug, seoContent]);

  const pageTitle = useMemo(() => {
    if (seoContent.type === 'sub' && seoContent.data) return seoContent.data.metaTitle;
    if (seoContent.type === 'cat' && seoContent.data) return seoContent.data.metaTitle;
    return 'کاتالوگ تجهیزات ابزار دقیق و اتوماسیون صنعتی | تول‌مستر';
  }, [seoContent]);

  const pageDescription = useMemo(() => {
    if (seoContent.type === 'sub' && seoContent.data) return seoContent.data.metaDescription;
    if (seoContent.type === 'cat' && seoContent.data) return seoContent.data.metaDescription;
    return 'مشاهده کامل تجهیزات ابزار دقیق و اتوماسیون صنعتی: ژنراتور گاز، پمپ، دتکتور، فلومتر و PLC از برندهای معتبر';
  }, [seoContent]);

  const pageKeywords = useMemo(() => {
    if (seoContent.type === 'sub' && seoContent.data) return seoContent.data.keywords;
    if (seoContent.type === 'cat' && seoContent.data) return seoContent.data.keywords;
    return 'تجهیزات صنعتی, ژنراتور نیتروژن, پمپ خلاء, دتکتور گاز, فلومتر, PLC زیمنس, ابزار دقیق';
  }, [seoContent]);

  const heroTitle = useMemo(() => {
    if (seoContent.type === 'sub' && seoContent.data) return seoContent.data.heroTitle;
    if (seoContent.type === 'cat' && seoContent.data) return seoContent.data.heroTitle;
    return 'کاتالوگ تجهیزات صنعتی';
  }, [seoContent]);

  const heroDescription = useMemo(() => {
    if (seoContent.type === 'sub' && seoContent.data) return seoContent.data.heroDescription;
    if (seoContent.type === 'cat' && seoContent.data) return seoContent.data.heroDescription;
    return 'مشاهده و استعلام قیمت تجهیزات ابزار دقیق و اتوماسیون. تمام تجهیزات با گارانتی و دیتاشیت فنی.';
  }, [seoContent]);

  const noindex = !slug && (selectedCategories.length !== 1 || selectedTypes.length > 1 || selectedBrands.length > 0 || selectedCountries.length > 0 || selectedUsages.length > 0 || selectedPriceRanges.length > 0 || searchQuery !== '');

  const seoContentHtml = useMemo(() => {
    const processContent = (content: string, keywords: string) => {
      const enriched = `${content}${generateSupportiveSeoHtml(
        seoContent.type === 'sub' ? (seoContent.data as any)?.title : (seoContent.data as any)?.title,
        keywords
      )}`;
      return enriched
        .replace(/^## (.+)$/gm, '<h2>$1</h2>')
        .replace(/^### (.+)$/gm, '<h3>$1</h3>')
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/\n\n/g, '</p><p>')
        .replace(/^(?!<[hp])(.+)$/gm, '<p>$1</p>');
    };
    if (seoContent.type === 'sub' && seoContent.data) return processContent(seoContent.data.content, seoContent.data.keywords);
    if (seoContent.type === 'cat' && seoContent.data) return processContent(seoContent.data.content, seoContent.data.keywords);
    return null;
  }, [seoContent]);

  const activeCategoryUI = useMemo(() => {
    if (selectedCategories.length === 1 && selectedTypes.length === 0) {
      return categoryUIData[selectedCategories[0]] || null;
    }
    return null;
  }, [selectedCategories, selectedTypes]);

  const handleAddToRFQ = (product: Product) => {
    addProduct({ id: product.id, name: product.name, type: product.type, grade: product.model });
    toast({ title: 'به سبد استعلام اضافه شد', description: `${product.name} به لیست استعلام شما اضافه شد.` });
  };

  const breadcrumbItems = useMemo(() => {
    const items = [
      { name: 'خانه', url: 'https://toolmaster.com' },
      { name: 'محصولات', url: 'https://toolmaster.com/products' },
    ];
    if (seoContent.type === 'cat' && seoContent.data) {
      items.push({ name: seoContent.data.title, url: `https://toolmaster.com/products/category/${seoContent.data.slug}` });
    }
    if (seoContent.type === 'sub' && seoContent.data) {
      const parentData = categorySEOData[seoContent.data.parentSlug as keyof typeof categorySEOData];
      if (parentData) items.push({ name: parentData.title, url: `https://toolmaster.com/products/category/${seoContent.data.parentSlug}` });
      items.push({ name: seoContent.data.title, url: `https://toolmaster.com/products/category/${seoContent.data.parentSlug}/${seoContent.data.slug}` });
    }
    return items;
  }, [seoContent]);

  const breadcrumbSchema = generateBreadcrumbSchema(breadcrumbItems);

  const FilterSidebar = () => (
    <div className="space-y-5" dir="rtl">
      {activeFilterCount > 0 && (
        <div className="flex items-center justify-between p-2.5 bg-accent/10 rounded-lg border border-accent/20">
          <span className="text-xs font-bold text-accent">{activeFilterCount} فیلتر فعال</span>
          <Button variant="ghost" size="sm" onClick={clearAllFilters} className="h-7 text-xs gap-1">
            <X className="h-3 w-3" /> پاک کردن
          </Button>
        </div>
      )}

      <div>
        <h3 className="font-bold mb-2.5 text-sm text-right">محصولات</h3>
        <div className="space-y-2">
          <div className="border-b border-border/40 pb-2">
            <label className="flex items-center gap-2 cursor-pointer group">
              <Checkbox checked={isAllProductsSelected} onCheckedChange={toggleAllProducts} />
              <span className="text-sm font-extrabold group-hover:text-accent transition-colors">همه محصولات</span>
            </label>
          </div>
          {mainCategoryGroups.map((group) => {
            const isExpanded = expandedCategories.includes(group.key);
            return (
              <div key={group.key} className="border-b border-border/40 pb-2 last:border-0">
                <div className="flex items-center justify-between">
                  <label className="flex items-center gap-2 cursor-pointer group flex-1">
                    <Checkbox checked={getGroupCheckedState(group)} onCheckedChange={() => toggleMainGroup(group)} />
                    <span className="text-sm font-medium group-hover:text-accent transition-colors">{group.label}</span>
                  </label>
                  {group.typeEntries.length > 0 && (
                    <button onClick={() => toggleCategory(group.key)} className="p-1 hover:bg-accent/10 rounded-sm transition-colors">
                      {isExpanded ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />}
                    </button>
                  )}
                </div>
                {isExpanded && group.typeEntries.length > 0 && (
                  <div className="mr-6 mt-2 space-y-1.5">
                    {group.typeEntries.map(([typeKey, type]) => (
                      <label key={typeKey} className="flex items-center gap-2 cursor-pointer group">
                        <Checkbox checked={selectedTypes.includes(typeKey)} onCheckedChange={() => toggleFilter(selectedTypes, typeKey, setSelectedTypes)} />
                        <span className="text-sm group-hover:text-accent transition-colors">{type.label}</span>
                      </label>
                    ))}
                  </div>
                )}
              </div>
            );
          })}
        </div>
      </div>

      <div className="border-b border-border/40 pb-2">
        <button onClick={() => toggleSection('brands')} className="w-full flex items-center justify-between mb-2.5">
          <h3 className="font-bold text-sm text-right">شرکت سازنده</h3>
          {expandedSections.brands ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />}
        </button>
        {expandedSections.brands && (
          <div className="space-y-2 max-h-48 overflow-y-auto">
            {availableBrands.map(brand => (
              <label key={brand} className="flex items-center gap-2 cursor-pointer group">
                <Checkbox checked={selectedBrands.includes(brand)} onCheckedChange={() => toggleFilter(selectedBrands, brand, setSelectedBrands)} />
                <span className="text-sm group-hover:text-accent transition-colors">{brand}</span>
              </label>
            ))}
          </div>
        )}
      </div>

      <div className="border-b border-border/40 pb-2">
        <button onClick={() => toggleSection('countries')} className="w-full flex items-center justify-between mb-2.5">
          <h3 className="font-bold text-sm text-right">کشور سازنده</h3>
          {expandedSections.countries ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />}
        </button>
        {expandedSections.countries && (
          <div className="space-y-2">
            {availableCountries.map(country => (
              <label key={country} className="flex items-center gap-2 cursor-pointer group">
                <Checkbox checked={selectedCountries.includes(country)} onCheckedChange={() => toggleFilter(selectedCountries, country, setSelectedCountries)} />
                <span className="text-sm group-hover:text-accent transition-colors">{countryLabels[country] || country}</span>
              </label>
            ))}
          </div>
        )}
      </div>

      <div className="border-b border-border/40 pb-2">
        <button onClick={() => toggleSection('usage')} className="w-full flex items-center justify-between mb-2.5">
          <h3 className="font-bold text-sm text-right">کاربرد</h3>
          {expandedSections.usage ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />}
        </button>
        {expandedSections.usage && (
          <div className="space-y-2">
            {(Object.entries(usageLabels) as [UsageType, string][]).map(([key, label]) => (
              <label key={key} className="flex items-center gap-2 cursor-pointer group">
                <Checkbox checked={selectedUsages.includes(key)} onCheckedChange={() => toggleFilter(selectedUsages, key, setSelectedUsages)} />
                <span className="text-sm group-hover:text-accent transition-colors">{label}</span>
              </label>
            ))}
          </div>
        )}
      </div>

      <div>
        <button onClick={() => toggleSection('price')} className="w-full flex items-center justify-between mb-2.5">
          <h3 className="font-bold text-sm text-right">حدود قیمت</h3>
          {expandedSections.price ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />}
        </button>
        {expandedSections.price && (
          <div className="space-y-2">
            {(Object.entries(priceRangeLabels) as [PriceRange, string][]).map(([key, label]) => (
              <label key={key} className="flex items-center gap-2 cursor-pointer group">
                <Checkbox checked={selectedPriceRanges.includes(key)} onCheckedChange={() => toggleFilter(selectedPriceRanges, key, setSelectedPriceRanges)} />
                <span className="text-sm group-hover:text-accent transition-colors">{label}</span>
              </label>
            ))}
          </div>
        )}
      </div>
    </div>
  );

  // ── FIX 1: کامپوننت Pagination ────────────────────────────────────────────
  const Pagination = () => {
    if (effectiveTotalPages <= 1) return null;
    return (
      <div className="flex items-center justify-center gap-2 mt-10" dir="ltr">
        <Button
          variant="outline"
          size="sm"
          onClick={() => setCurrentPage(p => Math.max(1, p - 1))}
          disabled={currentPage === 1}
          className="gap-1"
        >
          <ChevronRight className="h-4 w-4" />
          قبلی
        </Button>
        <div className="flex items-center gap-1">
          {Array.from({ length: Math.min(5, effectiveTotalPages) }, (_, i) => {
            let page: number;
            if (effectiveTotalPages <= 5) {
              page = i + 1;
            } else if (currentPage <= 3) {
              page = i + 1;
            } else if (currentPage >= effectiveTotalPages - 2) {
              page = effectiveTotalPages - 4 + i;
            } else {
              page = currentPage - 2 + i;
            }
            return (
              <Button
                key={page}
                variant={currentPage === page ? 'default' : 'outline'}
                size="sm"
                onClick={() => setCurrentPage(page)}
                className="h-8 w-8 p-0"
              >
                {page}
              </Button>
            );
          })}
        </div>
        <Button
          variant="outline"
          size="sm"
          onClick={() => setCurrentPage(p => Math.min(effectiveTotalPages, p + 1))}
          disabled={currentPage === effectiveTotalPages}
          className="gap-1"
        >
          بعدی
          <ChevronLeft className="h-4 w-4" />
        </Button>
      </div>
    );
  };

  return (
    <div className="min-h-screen bg-background" dir="rtl">
      <SEO title={pageTitle} description={pageDescription} keywords={pageKeywords} structuredData={breadcrumbSchema} />
      <Helmet>
        {noindex && <meta name="robots" content="noindex, follow" />}
        <link rel="canonical" href={canonicalUrl} />
      </Helmet>

      {/* Hero */}
      <section className="bg-gradient-to-br from-[hsl(var(--hero-gradient-start))] to-[hsl(var(--hero-gradient-end))] text-primary-foreground py-10 border-b border-border">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <h1 className="text-4xl font-black mb-3 text-right">{heroTitle}</h1>
          <p className="text-lg text-primary-foreground/80 max-w-3xl text-right">{heroDescription}</p>
          {apiLoading && <p className="text-sm text-primary-foreground/60 mt-2">در حال بارگذاری محصولات از پایگاه داده...</p>}
          {apiError && <p className="text-sm text-yellow-300 mt-2">⚠️ اتصال به API برقرار نشد — نمایش داده‌های پیش‌فرض</p>}
        </div>
      </section>

      {/* SEO Content */}
      {seoContentHtml && (
        <section className="py-8 border-b border-border bg-muted/20">
          <div className="container mx-auto px-4 sm:px-6 lg:px-8">
            <div className="rounded-2xl border border-border/60 bg-background p-5 md:p-8">
              <div className="prose prose-sm max-w-none dark:prose-invert text-right
                prose-headings:text-foreground prose-headings:font-extrabold
                prose-h2:text-lg prose-h2:mt-6 prose-h2:mb-3 prose-h2:border-b prose-h2:border-border/40 prose-h2:pb-2
                prose-h3:text-base prose-h3:mt-4 prose-h3:mb-2
                prose-p:text-muted-foreground prose-p:text-sm prose-p:leading-7
                prose-strong:text-foreground/90" dir="rtl">
                <div dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(seoContentHtml) }} />
              </div>
            </div>
          </div>
        </section>
      )}

      {seoContent.type === 'general' && (
        <section className="py-6 border-b border-border">
          <div className="container mx-auto px-4 sm:px-6 lg:px-8">
            <div className="text-right max-w-4xl">
              <h2 className="text-xl font-extrabold mb-3">محصولات و تجهیزات ابزار دقیق تول‌مستر</h2>
              <p className="text-sm text-muted-foreground leading-7">
                تول‌مستر مجموعه‌ای کامل از تجهیزات ابزار دقیق و اتوماسیون صنعتی را از برندهای معتبر جهانی ارائه می‌دهد.
              </p>
            </div>
          </div>
        </section>
      )}

      {activeCategoryUI && (
        <section className="py-6 border-b border-border bg-muted/20">
          <div className="container mx-auto px-4 sm:px-6 lg:px-8">
            <h2 className="text-lg font-black text-right mb-4">زیرمجموعه‌های تخصصی</h2>
            <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
              {activeCategoryUI.subcategories.map((sub) => (
                <button key={sub.id}
                  onClick={() => { if (sub.type) { navigate(`/products/category/${selectedCategories[0]}/${sub.type}`); } }}
                  className="group overflow-hidden rounded-lg border border-border/60 bg-background hover:shadow-md hover:border-accent/30 transition-all text-right">
                  {sub.image && (
                    <div className="h-24 overflow-hidden bg-muted/30">
                      <img src={sub.image} alt={sub.imageAlt || sub.label} className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy" />
                    </div>
                  )}
                  <div className="p-2.5">
                    <h3 className="text-xs font-bold leading-tight">{sub.label}</h3>
                    <p className="text-xs text-muted-foreground mt-1 line-clamp-2">{sub.description}</p>
                  </div>
                </button>
              ))}
            </div>
          </div>
        </section>
      )}

      {seoContent.type === 'general' && (
        <section className="py-6 border-b border-border bg-muted/20">
          <div className="container mx-auto px-4 sm:px-6 lg:px-8">
            <h2 className="text-lg font-black text-right mb-4">دسته‌بندی‌های اصلی</h2>
            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
              {Object.values(categorySEOData).map((category) => {
                const catUI = categoryUIData[category.slug];
                return (
                  <Link
                    key={category.slug}
                    to={`/products/category/${category.slug}`}
                    className="group overflow-hidden rounded-lg border border-border/60 bg-background hover:shadow-md hover:border-accent/30 transition-all"
                  >
                    {catUI?.image && (
                      <div className="h-24 overflow-hidden bg-muted/30">
                        <img src={catUI.image} alt={catUI.imageAlt ?? category.title} className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy" />
                      </div>
                    )}
                    <div className="p-2.5 text-right">
                      <h3 className="text-xs font-bold leading-tight">{category.title}</h3>
                    </div>
                  </Link>
                );
              })}
            </div>
          </div>
        </section>
      )}

      {/* Search bar */}
      <section className="py-4 bg-background border-b border-border sticky top-16 z-40 backdrop-blur bg-background/95">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex gap-4">
            <div className="flex-1 relative">
              <Search className="absolute right-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="جستجو بر اساس نام، مدل یا برند..."
                value={searchQuery}
                onChange={(e) => { setSearchQuery(e.target.value); resetPage(); }}
                className="pr-10 text-right"
              />
            </div>
            <Sheet>
              <SheetTrigger asChild>
                <Button variant="outline" className="lg:hidden relative">
                  <Filter className="h-4 w-4 ml-2" />
                  فیلترها
                  {activeFilterCount > 0 && (
                    <Badge variant="default" className="absolute -top-2 -left-2 h-5 w-5 p-0 flex items-center justify-center text-[10px]">{activeFilterCount}</Badge>
                  )}
                </Button>
              </SheetTrigger>
              <SheetContent side="right">
                <SheetHeader><SheetTitle className="text-right">فیلترهای فنی</SheetTitle></SheetHeader>
                <div className="mt-6 overflow-y-auto max-h-[calc(100vh-120px)]"><FilterSidebar /></div>
              </SheetContent>
            </Sheet>
          </div>
        </div>
      </section>

      {/* Main content */}
      <section className="py-8">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex gap-8">
            <aside className="hidden lg:block w-72 flex-shrink-0">
              <div className="sticky top-32 max-h-[calc(100vh-160px)] overflow-y-auto">
                <Card className="border-border/60">
                  <CardHeader className="pb-3">
                    <CardTitle className="text-base flex items-center gap-2"><Filter className="h-4 w-4" />فیلتر فنی</CardTitle>
                  </CardHeader>
                  <CardContent><FilterSidebar /></CardContent>
                </Card>
              </div>
            </aside>

            <div className="flex-1">
              <div className="mb-6 flex items-center justify-between">
                {/* ── FIX 1: نمایش total از meta ─────────────────────────────── */}
                <p className="text-sm text-muted-foreground text-right">
                  نمایش {effectiveTotalItems} محصول
                  {apiProducts.length > 0 && <span className="mr-2 text-green-600 text-xs">✓ از پایگاه داده</span>}
                </p>
                {activeFilterCount > 0 && (
                  <div className="hidden lg:flex flex-wrap gap-1.5">
                    {selectedCategories.map((cat) => (
                      <Badge key={cat} variant="secondary" className="text-xs gap-1 cursor-pointer" onClick={() => { toggleFilter(selectedCategories, cat, setSelectedCategories); }}>
                        {productCategories[cat as keyof typeof productCategories]?.label || cat}<X className="h-3 w-3" />
                      </Badge>
                    ))}
                    {selectedTypes.map((typeKey) => (
                      <Badge key={typeKey} variant="secondary" className="text-xs gap-1 cursor-pointer" onClick={() => { toggleFilter(selectedTypes, typeKey, setSelectedTypes); }}>
                        {equipmentTypes[typeKey as keyof typeof equipmentTypes]?.label || typeKey}<X className="h-3 w-3" />
                      </Badge>
                    ))}
                  </div>
                )}
              </div>

              {apiLoading && (
                <div className="flex items-center justify-center py-8">
                  <div className="animate-spin h-6 w-6 border-4 border-primary border-t-transparent rounded-full ml-3"></div>
                  <span className="text-muted-foreground text-sm">در حال بارگذاری از پایگاه داده...</span>
                </div>
              )}

              {/* ── FIX 1: از paginatedProducts استفاده میکنه نه filteredProducts ── */}
              <div className="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
                {paginatedProducts.map((product, index) => (
                  <motion.div key={product.id} initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: index * 0.04 }}>
                    <Card className="h-full hover:shadow-lg transition-all border-border/60 hover:border-accent/30 overflow-hidden">
                      <div className="h-48 overflow-hidden bg-muted/20">
                        <img
                          src={resolveImage(product.image)}
                          alt={product.name}
                          className="h-full w-full object-cover transition-transform duration-300 hover:scale-105"
                          loading="lazy"
                        />
                      </div>
                      <CardHeader className="pb-3">
                        <div className="flex items-center justify-between gap-2">
                          <Badge variant={product.inStock ? 'default' : 'outline'} className="text-xs">
                            {product.inStock ? 'موجود' : 'ناموجود'}
                          </Badge>
                          <Badge variant="secondary" className="text-xs">{countryLabels[product.country] || product.country}</Badge>
                        </div>
                        <CardTitle className="text-base text-right leading-7">{product.name}</CardTitle>
                        <CardDescription className="text-right">{product.model} • {product.brand}</CardDescription>
                      </CardHeader>
                      <CardContent className="space-y-3">
                        <p className="text-sm text-muted-foreground line-clamp-2 text-right">{product.description}</p>
                        <div className="flex flex-wrap gap-1">
                          <Badge variant="outline" className="text-xs">{productCategories[product.category as keyof typeof productCategories]?.label || product.category}</Badge>
                          <Badge variant="outline" className="text-xs">{equipmentTypes[product.type as keyof typeof equipmentTypes]?.label || product.type}</Badge>
                          <Badge variant="outline" className="text-xs">{priceRangeLabels[product.priceRange]}</Badge>
                        </div>
                        <div className="grid grid-cols-2 gap-2">
                          <Button asChild size="sm" variant="outline">
                            <Link to={`/products/${product.id}`}><Download className="h-4 w-4 ml-1" />مشخصات</Link>
                          </Button>
                          <Button size="sm" onClick={() => handleAddToRFQ(product)}>
                            <CheckCircle className="h-4 w-4 ml-1" />استعلام
                          </Button>
                        </div>
                      </CardContent>
                    </Card>
                  </motion.div>
                ))}
              </div>

              {/* ── FIX 1: کامپوننت Pagination ────────────────────────────────── */}
              <Pagination />

              {paginatedProducts.length === 0 && !apiLoading && (
                <div className="text-center py-12">
                  <Settings className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                  <h3 className="text-lg font-bold mb-2">محصولی یافت نشد</h3>
                  <p className="text-muted-foreground">فیلترها یا عبارت جستجو را تغییر دهید</p>
                </div>
              )}
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}
