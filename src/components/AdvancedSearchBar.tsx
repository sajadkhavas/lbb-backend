import { useState, useRef, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Search, X, TrendingUp } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { motion, AnimatePresence } from 'framer-motion';
import { products } from '@/data/products';

const searchSuggestions = products.map(p => p.name);

const trendingSearches = [
  'PLC زیمنس',
  'نیتروژن ژنراتور',
  'دتکتور گاز',
  'فلومتر',
  'پمپ دیافراگمی',
];

export function AdvancedSearchBar() {
  const navigate = useNavigate();
  const [query, setQuery] = useState('');
  const [isFocused, setIsFocused] = useState(false);
  const [showSuggestions, setShowSuggestions] = useState(false);
  const [selectedCategory, setSelectedCategory] = useState('');
  const wrapperRef = useRef<HTMLDivElement>(null);

  const filtered = query.length > 0
    ? searchSuggestions.filter(s => s.toLowerCase().includes(query.toLowerCase()))
    : [];

  useEffect(() => {
    const handler = (e: MouseEvent) => {
      if (!wrapperRef.current?.contains(e.target as Node)) {
        setShowSuggestions(false);
      }
    };
    document.addEventListener('mousedown', handler);
    return () => document.removeEventListener('mousedown', handler);
  }, []);

  return (
    <section className="relative -mt-10 z-20">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8">
        <motion.div
          ref={wrapperRef}
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.3, duration: 0.5 }}
          className={cn(
            'rounded-2xl bg-card p-5 md:p-6 shadow-xl border-2 transition-all duration-300',
            isFocused
              ? 'border-accent shadow-[0_15px_40px_rgba(0,51,102,0.2)] -translate-y-1'
              : 'border-accent/40 shadow-[0_10px_30px_rgba(0,51,102,0.12)]'
          )}
        >
          {/* Title */}
          <div className="flex items-center gap-2 mb-3">
            <Search className="h-4 w-4 text-primary" />
            <p className="text-sm font-bold text-foreground">جستجوی پیشرفته محصولات</p>
            <p className="text-[11px] text-muted-foreground hidden sm:block">— نام محصول، مدل یا کد کالا را وارد کنید</p>
          </div>

          {/* Search Row */}
          <div className="flex flex-col md:flex-row gap-3">
            {/* Input with autocomplete */}
            <div className="relative flex-1">
              <div className="relative">
                <Search className="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
                <input
                  type="text"
                  value={query}
                  onChange={e => {
                    setQuery(e.target.value);
                    setShowSuggestions(true);
                  }}
                  onFocus={() => {
                    setIsFocused(true);
                    setShowSuggestions(true);
                  }}
                  onBlur={() => setIsFocused(false)}
                  placeholder="مثلاً: فلومتر الکترومغناطیسی Endress+Hauser..."
                  className="w-full rounded-lg border-2 border-primary/20 bg-muted/30 pr-10 pl-10 py-3.5 text-sm placeholder:text-muted-foreground focus:outline-none focus:bg-background focus:border-accent focus:shadow-[0_0_0_3px_rgba(255,215,0,0.2)] transition-all"
                />
                {query && (
                  <button onClick={() => { setQuery(''); setShowSuggestions(false); }} className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground">
                    <X className="h-4 w-4" />
                  </button>
                )}
              </div>

              {/* Autocomplete dropdown */}
              <AnimatePresence>
                {showSuggestions && filtered.length > 0 && (
                  <motion.div
                    initial={{ opacity: 0, y: -4 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: -4 }}
                    className="absolute top-full right-0 left-0 mt-1 bg-card border border-border rounded-lg shadow-lg z-30 overflow-hidden"
                  >
                    {filtered.slice(0, 6).map((item, i) => {
                      const matchedProduct = products.find(p => p.name === item);
                      return (
                      <button
                        key={item}
                        onMouseDown={() => { 
                          if (matchedProduct) {
                            navigate(`/products/${matchedProduct.id}`);
                          } else {
                            setQuery(item); 
                            setShowSuggestions(false); 
                          }
                        }}
                        className={cn(
                          'w-full text-right px-4 py-2.5 text-sm text-foreground hover:bg-primary/5 flex items-center gap-2 transition-colors',
                          i > 0 && 'border-t border-border/50'
                        )}
                      >
                        <Search className="h-3 w-3 text-muted-foreground flex-shrink-0" />
                        {item}
                      </button>
                      );
                    })}
                  </motion.div>
                )}
              </AnimatePresence>
            </div>

            {/* Category Select */}
            <select 
              value={selectedCategory}
              onChange={e => setSelectedCategory(e.target.value)}
              className="rounded-lg border-2 border-primary/20 bg-muted/30 px-4 py-3.5 text-sm text-foreground focus:outline-none focus:border-accent transition-colors min-w-[160px]"
            >
              <option value="">تمام دسته‌ها</option>
              <option value="flow-meters">فلومتر</option>
              <option value="gas-detectors">دتکتور گاز</option>
              <option value="plc-equipment">تجهیزات PLC</option>
              <option value="gas-generators">ژنراتور گاز</option>
              <option value="lab-pumps">پمپ آزمایشگاهی</option>
            </select>

            {/* Search Button */}
            <Button
              size="lg"
              onClick={() => {
                const params = new URLSearchParams();
                if (query) params.set('search', query);
                if (selectedCategory) params.set('category', selectedCategory);
                navigate(`/products?${params.toString()}`);
              }}
              className="bg-primary text-primary-foreground hover:bg-accent hover:text-accent-foreground font-bold px-8 transition-all duration-300 hover:scale-[1.03] shadow-md group"
            >
              <Search className="h-4 w-4 ml-2 group-hover:scale-110 transition-transform" />
              جستجو
            </Button>
          </div>

          {/* Trending Searches */}
          <div className="mt-3 flex flex-wrap items-center gap-2">
            <span className="flex items-center gap-1 text-[11px] text-muted-foreground">
              <TrendingUp className="h-3 w-3" />
              پرطرفدار:
            </span>
            {trendingSearches.map(tag => (
              <button
                key={tag}
                onClick={() => { navigate(`/products?search=${encodeURIComponent(tag)}`); }}
                className="rounded-full bg-primary/5 border border-primary/10 px-3 py-1 text-[11px] font-medium text-primary hover:bg-primary hover:text-primary-foreground transition-colors"
              >
                {tag}
              </button>
            ))}
          </div>
        </motion.div>
      </div>
    </section>
  );
}
