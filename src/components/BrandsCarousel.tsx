// ── FIX 4: BrandsCarousel.tsx — داینامیک با API ─────────────────────────────
// این کامپوننت رو جایگزین BrandsCarousel هاردکد‌شده فعلیت کن

import { useEffect, useState, useRef } from 'react';

// ── Type ─────────────────────────────────────────────────────────────────────
interface Brand {
  id: number | string;
  name: string;
  slug: string;
  logo?: string | null;    // URL عکس لوگو از API
  logoAlt?: string;
}

// ── Fallback: برندهای هاردکد شده اگه API جواب نداد ─────────────────────────
const FALLBACK_BRANDS: Brand[] = [
  { id: 1, name: 'Siemens', slug: 'siemens' },
  { id: 2, name: 'Endress+Hauser', slug: 'endress-hauser' },
  { id: 3, name: 'Honeywell', slug: 'honeywell' },
  { id: 4, name: 'Emerson', slug: 'emerson' },
  { id: 5, name: 'ABB', slug: 'abb' },
  { id: 6, name: 'Rockwell', slug: 'rockwell' },
  { id: 7, name: 'Peak Scientific', slug: 'peak' },
  { id: 8, name: 'Dräger', slug: 'drager' },
  { id: 9, name: 'KNF', slug: 'knf' },
  { id: 10, name: 'Yokogawa', slug: 'yokogawa' },
  { id: 11, name: 'Brooks Instrument', slug: 'brooks' },
  { id: 12, name: 'Schneider Electric', slug: 'schneider' },
];

// ── Helper: حرف اول برند رو برای placeholder logo نشون بده ─────────────────
function BrandPlaceholder({ name }: { name: string }) {
  return (
    <div className="w-24 h-12 flex items-center justify-center bg-muted/40 rounded-lg border border-border/40">
      <span className="text-lg font-black text-muted-foreground/60">
        {name.charAt(0).toUpperCase()}
      </span>
    </div>
  );
}

export function BrandsCarousel() {
  const [brands, setBrands] = useState<Brand[]>(FALLBACK_BRANDS);
  const [loading, setLoading] = useState(true);
  const trackRef = useRef<HTMLDivElement>(null);

  // ── FIX 4: fetch brands از API ─────────────────────────────────────────────
  useEffect(() => {
    const apiBase = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000';
    fetch(`${apiBase}/api/brands?per_page=50`)
      .then(res => {
        if (!res.ok) throw new Error('API error');
        return res.json();
      })
      .then((data: any) => {
        // ── ساپورت هر دو فرمت — مثل Products fix ──────────────────────────
        const items: Brand[] = Array.isArray(data) ? data : (data?.data ?? []);
        if (items.length > 0) setBrands(items);
      })
      .catch(() => {
        // API جواب نداد — fallback روی FALLBACK_BRANDS بمون
        console.warn('[BrandsCarousel] API unavailable, using static fallback');
      })
      .finally(() => setLoading(false));
  }, []);

  // ── Infinite scroll animation ──────────────────────────────────────────────
  // برندها رو دوبار repeat میکنه تا حلقه بی‌نهایت بشه
  const displayBrands = [...brands, ...brands];

  return (
    <div className="overflow-hidden w-full py-6 relative" aria-label="برندهای معتبر">
      {/* Gradient mask چپ و راست */}
      <div className="absolute inset-y-0 right-0 w-20 bg-gradient-to-l from-background to-transparent z-10 pointer-events-none" />
      <div className="absolute inset-y-0 left-0 w-20 bg-gradient-to-r from-background to-transparent z-10 pointer-events-none" />

      <div
        ref={trackRef}
        className="flex gap-8 items-center animate-scroll"
        style={{
          width: 'max-content',
          animation: 'scroll-brands 30s linear infinite',
        }}
      >
        {displayBrands.map((brand, idx) => (
          <a
            key={`${brand.id}-${idx}`}
            href={`/brands/${brand.slug}`}
            className="flex-shrink-0 opacity-60 hover:opacity-100 transition-opacity duration-200"
            title={brand.name}
          >
            {brand.logo ? (
              <img
                src={brand.logo}
                alt={brand.logoAlt ?? `لوگوی ${brand.name}`}
                className="h-10 w-auto max-w-[96px] object-contain filter grayscale hover:grayscale-0 transition-all"
                loading="lazy"
              />
            ) : (
              <BrandPlaceholder name={brand.name} />
            )}
          </a>
        ))}
      </div>

      {/* ── Animation CSS ── در global CSS یا tailwind config اضافه کن ─────── */}
      <style>{`
        @keyframes scroll-brands {
          0%   { transform: translateX(0); }
          100% { transform: translateX(-50%); }
        }
        .animate-scroll:hover {
          animation-play-state: paused;
        }
      `}</style>
    </div>
  );
}
