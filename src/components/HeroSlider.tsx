import { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useSliders } from '@/api/hooks';

import slider1 from '@/assets/slider-1.png';
import slider2 from '@/assets/slider-2.png';
import slider3 from '@/assets/slider-3.png';

const fallbackSlides = [
  {
    image: slider1,
    title: 'تجهیزات ابزار دقیق پیشرفته',
    subtitle: 'مانیتورینگ و کنترل هوشمند فرآیندهای صنعتی',
    cta: { label: 'مشاهده محصولات', link: '/products' },
  },
  {
    image: slider2,
    title: 'اتوماسیون صنعتی نوین',
    subtitle: 'طراحی، نصب و راه‌اندازی سیستم‌های اتوماسیون',
    cta: { label: 'درخواست مشاوره', link: '/contact' },
  },
  {
    image: slider3,
    title: 'تأمین تجهیزات صنعتی',
    subtitle: 'ژنراتور گاز، PLC، فلومتر و دتکتور با گارانتی معتبر',
    cta: { label: 'استعلام قیمت', link: '/contact' },
  },
];

const INTERVAL = 5000;

export function HeroSlider() {
  const [current, setCurrent] = useState(0);
  const { data: apiSliders } = useSliders();

  // Map API sliders to slide format
  const slides = (apiSliders && apiSliders.length > 0)
    ? apiSliders.map((s: any) => ({
        image: s.image_url
          ? (s.image_url.startsWith('http') ? s.image_url : `${import.meta.env.VITE_API_BASE_URL?.replace('/api/v1', '')}${s.image_url}`)
          : fallbackSlides[0].image,
        title: s.title || s.heading || '',
        subtitle: s.subtitle || s.description || '',
        cta: {
          label: s.cta_label || s.button_text || 'مشاهده محصولات',
          link: s.cta_url || s.button_url || s.link || '/products',
        },
      }))
    : fallbackSlides;

  const next = useCallback(() => setCurrent((p) => (p + 1) % slides.length), [slides.length]);
  const prev = useCallback(() => setCurrent((p) => (p - 1 + slides.length) % slides.length), [slides.length]);

  useEffect(() => {
    setCurrent(0);
  }, [slides.length]);

  useEffect(() => {
    const id = setInterval(next, INTERVAL);
    return () => clearInterval(id);
  }, [next]);

  return (
    <section className="relative h-[85vh] min-h-[520px] overflow-hidden bg-[hsl(232,50%,6%)]">
      {/* Images */}
      <AnimatePresence mode="wait">
        <motion.img
          key={current}
          src={slides[current].image}
          alt={slides[current].title}
          className="absolute inset-0 h-full w-full object-cover"
          initial={{ opacity: 0, scale: 1.05 }}
          animate={{ opacity: 1, scale: 1 }}
          exit={{ opacity: 0 }}
          transition={{ duration: 0.8, ease: 'easeInOut' }}
        />
      </AnimatePresence>

      {/* Overlay */}
      <div className="absolute inset-0 bg-gradient-to-l from-[hsl(232,50%,6%)/92%] via-[hsl(232,50%,6%)/70%] to-[hsl(232,50%,6%)/30%]" />

      {/* Content */}
      <div className="container relative z-10 mx-auto flex h-full items-center px-4 sm:px-6 lg:px-8">
        <AnimatePresence mode="wait">
          <motion.div
            key={current}
            className="max-w-2xl"
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -20 }}
            transition={{ duration: 0.5, delay: 0.2 }}
          >
            <h1 className="text-4xl md:text-6xl font-black mb-4 leading-[1.2] text-white">
              {slides[current].title}
            </h1>
            <p className="text-lg md:text-xl mb-8 text-slate-300 leading-relaxed max-w-xl">
              {slides[current].subtitle}
            </p>
            <Button asChild size="lg" variant="cta">
              <Link to={slides[current].cta.link}>{slides[current].cta.label}</Link>
            </Button>
          </motion.div>
        </AnimatePresence>
      </div>

      {/* Arrows */}
      <button
        onClick={prev}
        className="absolute left-4 top-1/2 -translate-y-1/2 z-20 rounded-full bg-white/10 backdrop-blur p-2 text-white hover:bg-white/20 transition"
        aria-label="اسلاید قبلی"
      >
        <ChevronLeft className="h-5 w-5" />
      </button>
      <button
        onClick={next}
        className="absolute right-4 top-1/2 -translate-y-1/2 z-20 rounded-full bg-white/10 backdrop-blur p-2 text-white hover:bg-white/20 transition"
        aria-label="اسلاید بعدی"
      >
        <ChevronRight className="h-5 w-5" />
      </button>

      {/* Dots */}
      <div className="absolute bottom-8 left-1/2 -translate-x-1/2 z-20 flex gap-2">
        {slides.map((_, i) => (
          <button
            key={i}
            onClick={() => setCurrent(i)}
            className={`h-2 rounded-full transition-all duration-300 ${
              i === current ? 'w-8 bg-accent' : 'w-2 bg-white/40'
            }`}
            aria-label={`اسلاید ${i + 1}`}
          />
        ))}
      </div>
    </section>
  );
}
