import { Link } from 'react-router-dom';
import { motion, useScroll, useTransform } from 'framer-motion';
import { useRef } from 'react';
import { SEO } from '@/components/SEO';
import { generateOrganizationSchema, generateWebSiteSchema } from '@/lib/structured-data';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
  Shield, Zap, Globe, FileText, Award, Clock, Users, CheckCircle2,
  ArrowLeft, Flame, Droplets, AlertTriangle, Activity, Cpu,
  Factory, Wrench, Truck, Headphones, Package, Phone,
  Gauge, BookOpen, TrendingUp, Newspaper, Star, ChevronLeft,
  ArrowUpRight, Layers, Settings, BarChart3,
} from 'lucide-react';
import { AdvancedSearchBar } from '@/components/AdvancedSearchBar';
import { BrandsCarousel } from '@/components/BrandsCarousel';

import heroImage from '@/assets/hero-industrial.jpg';
import industryOilGas from '@/assets/industry-oil-gas.jpg';
import industryPetrochemical from '@/assets/industry-petrochemical.jpg';
import industryPharma from '@/assets/industry-pharma.jpg';
import industryPower from '@/assets/industry-power.jpg';
import industryWater from '@/assets/industry-water.jpg';
import industryMining from '@/assets/industry-mining.jpg';

import catGasGen from '@/assets/categories/gas-generators.jpg';
import catHydrogenGen from '@/assets/categories/hydrogen-gen.jpg';
import catNitrogenGen from '@/assets/categories/nitrogen-gen.jpg';
import catLabPumps from '@/assets/categories/lab-pumps.jpg';
import catGasDetectors from '@/assets/categories/gas-detectors.jpg';
import catFlowMeters from '@/assets/categories/flow-meters.jpg';
import catPlcEquipment from '@/assets/categories/plc-equipment.jpg';
import catCalibration from '@/assets/categories/calibration.jpg';

import prodNitrogenPeak from '@/assets/products/nitrogen-generator-peak.jpg';
import prodFlowmeterEndress from '@/assets/products/flowmeter-endress.jpg';
import prodDetectorHoneywell from '@/assets/products/gas-detector-honeywell.jpg';
import prodPlcSiemens from '@/assets/products/plc-siemens.jpg';
import prodPumpKnf from '@/assets/products/pump-knf.jpg';
import prodDcsEmerson from '@/assets/products/dcs-emerson.jpg';
import prodDriveAbb from '@/assets/products/drive-abb.jpg';
import prodPlcRockwell from '@/assets/products/plc-rockwell.jpg';
import prodDetectorDrager from '@/assets/products/detector-drager.jpg';
import prodDcsYokogawa from '@/assets/products/dcs-yokogawa.jpg';
import prodFlowBrooks from '@/assets/products/flowcontroller-brooks.jpg';
import prodPlcSchneider from '@/assets/products/plc-schneider.jpg';

import blogIot from '@/assets/blog/iot-instrumentation.jpg';
import blogAutomation from '@/assets/blog/automation-iran.jpg';
import blogCalibration from '@/assets/blog/calibration-importance.jpg';
import blogFlowmeter from '@/assets/blog/flowmeter-guide.jpg';
import blogGasDetector from '@/assets/blog/gas-detector-install.jpg';
import blogPlc from '@/assets/blog/plc-comparison.jpg';

// ─── Animation Variants ───────────────────────────────────────────
const fadeUp = {
  hidden: { opacity: 0, y: 32 },
  show: { opacity: 1, y: 0, transition: { duration: 0.6, ease: [0.22, 1, 0.36, 1] } },
};

const stagger = {
  hidden: {},
  show: { transition: { staggerChildren: 0.07 } },
};

const fadeLeft = {
  hidden: { opacity: 0, x: 30 },
  show: { opacity: 1, x: 0, transition: { duration: 0.6, ease: [0.22, 1, 0.36, 1] } },
};

// ─── Stat Card ────────────────────────────────────────────────────
function StatPill({ value, label }: { value: string; label: string }) {
  return (
    <div className="flex flex-col items-center text-center px-6 py-4 border-l border-white/10 first:border-l-0">
      <span className="text-3xl font-black text-accent ltr tracking-tight">{value}</span>
      <span className="text-xs text-white/50 mt-1 leading-snug">{label}</span>
    </div>
  );
}

// ─── Section Label ────────────────────────────────────────────────
function SectionLabel({ children }: { children: React.ReactNode }) {
  return (
    <span className="inline-flex items-center gap-2 text-xs font-bold tracking-widest text-primary uppercase mb-4">
      <span className="h-px w-6 bg-primary" />
      {children}
      <span className="h-px w-6 bg-primary" />
    </span>
  );
}

export default function Home() {
  const heroRef = useRef<HTMLElement>(null);
  const { scrollYProgress } = useScroll({ target: heroRef, offset: ['start start', 'end start'] });
  const heroY = useTransform(scrollYProgress, [0, 1], ['0%', '20%']);
  const heroOpacity = useTransform(scrollYProgress, [0, 0.8], [1, 0]);

  const categories = [
    { title: 'ژنراتورهای گاز', img: catGasGen, link: '/products/category/gas-generators', count: '۴۸ محصول' },
    { title: 'ژنراتور هیدروژن', img: catHydrogenGen, link: '/products/category/gas-generators/hydrogen-gen', count: '۲۳ محصول' },
    { title: 'ژنراتور نیتروژن', img: catNitrogenGen, link: '/products/category/gas-generators/nitrogen-gen', count: '۳۱ محصول' },
    { title: 'پمپ‌های آزمایشگاهی', img: catLabPumps, link: '/products/category/lab-pumps', count: '۵۷ محصول' },
    { title: 'دتکتورهای گاز', img: catGasDetectors, link: '/products/category/gas-detectors', count: '۴۲ محصول' },
    { title: 'فلومتر و فلوکنترلر', img: catFlowMeters, link: '/products/category/flow-meters', count: '۶۵ محصول' },
    { title: 'تجهیزات PLC', img: catPlcEquipment, link: '/products/category/plc-equipment', count: '۳۸ محصول' },
    { title: 'کالیبراسیون', img: catCalibration, link: '/products/category/calibration', count: '۲۹ محصول' },
  ];

  const featuredProducts = [
    { id: 'genius-xe35', name: 'ژنراتور نیتروژن Genius XE 35', model: 'Genius XE 35', brand: 'Peak Scientific', cat: 'ژنراتور گاز', badge: 'پرفروش', img: prodNitrogenPeak, features: ['خلوص ۹۹.۵٪ برای LC-MS', 'فلو تا ۳۵ لیتر در دقیقه', 'تولید بدون سیلندر'] },
    { id: 'promag-w400', name: 'فلومتر الکترومغناطیسی Promag W 400', model: 'Promag W 400', brand: 'Endress+Hauser', cat: 'فلومتر', badge: 'جدید', img: prodFlowmeterEndress, features: ['دقت ±۰.۲٪', 'پروتکل HART/Modbus', 'لاینر PFA ضد خوردگی'] },
    { id: 'bw-clip4', name: 'دتکتور ۴ گاز BW Clip4', model: 'BW Clip4', brand: 'Honeywell', cat: 'دتکتور گاز', badge: 'ویژه', img: prodDetectorHoneywell, features: ['سنسور O₂, LEL, CO, H₂S', 'تاییدیه ATEX Zone 0', 'باتری ۲ ساله'] },
    { id: 's7-1500f', name: 'PLC SIMATIC S7-1500F', model: 'CPU 1513F-1 PN', brand: 'Siemens', cat: 'اتوماسیون', badge: 'محبوب', img: prodPlcSiemens, features: ['Safety Integrated', 'OPC UA و PROFINET', 'تا ۶۵,۰۰۰ نقطه I/O'] },
    { id: 'n840-knf', name: 'پمپ دیافراگمی LABOPORT N 840', model: 'N 840.3 FT.18', brand: 'KNF', cat: 'پمپ', badge: 'صنعتی', img: prodPumpKnf, features: ['بدون روغن', 'مقاوم در برابر خوردگی', 'دبی ۳۴ لیتر/دقیقه'] },
    { id: 'deltav-emerson', name: 'سیستم کنترل DeltaV S-Series', model: 'DeltaV S-Series', brand: 'Emerson', cat: 'کنترل', badge: 'پیشرفته', img: prodDcsEmerson, features: ['معماری باز', 'هوش مصنوعی پیش‌بینانه', 'یکپارچه با IIoT'] },
    { id: 'acs580-abb', name: 'درایو فرکانس متغیر ACS580', model: 'ACS580-01', brand: 'ABB', cat: 'درایو', badge: 'پرفروش', img: prodDriveAbb, features: ['۰.۷۵ تا ۵۰۰ کیلووات', 'رابط لمسی هوشمند', 'صرفه‌جویی ۳۰٪ انرژی'] },
    { id: 'compactlogix-5380', name: 'PLC CompactLogix 5380', model: '5069-L310ER', brand: 'Rockwell', cat: 'اتوماسیون', badge: 'جدید', img: prodPlcRockwell, features: ['حافظه ۴ مگابایت', 'EtherNet/IP و CIP Safety', 'اسکن ۰.۲ میلی‌ثانیه'] },
    { id: 'xam5600', name: 'دتکتور چندگاز X-am 5600', model: 'X-am 5600', brand: 'Dräger', cat: 'دتکتور گاز', badge: 'ویژه', img: prodDetectorDrager, features: ['تشخیص ۶ گاز همزمان', 'IECEx و ATEX', 'Bluetooth یکپارچه'] },
    { id: 'centum-vp-yokogawa', name: 'سیستم CENTUM VP R6', model: 'CENTUM VP R6', brand: 'Yokogawa', cat: 'کنترل', badge: 'پیشرفته', img: prodDcsYokogawa, features: ['قابلیت اطمینان ۹۹.۹۹۹۹۹٪', 'افزونگی کامل', 'ProSafe-RS یکپارچه'] },
    { id: 'gf80-brooks', name: 'فلوکنترلر جرمی GF80', model: 'GF80', brand: 'Brooks', cat: 'فلوکنترلر', badge: 'دقیق', img: prodFlowBrooks, features: ['دقت ±۰.۵٪', 'پاسخ‌دهی زیر ۵۰۰ms', 'EtherCAT و DeviceNet'] },
    { id: 'modicon-m580', name: 'PLC Modicon M580', model: 'BMEP584040', brand: 'Schneider', cat: 'اتوماسیون', badge: 'محبوب', img: prodPlcSchneider, features: ['Ethernet یکپارچه', 'Achilles Level 2', 'تا ۱۲۸,۰۰۰ I/O دیجیتال'] },
  ];

  const articles = [
    { cat: 'راهنمای خرید', title: 'راهنمای کامل خرید فلومتر صنعتی', time: '۸ دقیقه', level: 'متوسط', icon: BookOpen, img: blogFlowmeter },
    { cat: 'آموزش تخصصی', title: '۷ گام طلایی برای نصب و کالیبراسیون دتکتور گاز', time: '۱۲ دقیقه', level: 'پیشرفته', icon: Wrench, img: blogGasDetector },
    { cat: 'تحلیل بازار', title: 'مقایسه ۳ غول PLC در بازار ایران', time: '۱۰ دقیقه', level: 'عمومی', icon: TrendingUp, img: blogPlc },
  ];

  const latestBlogPosts = [
    { cat: 'فناوری‌های نوین', title: 'اینترنت اشیاء (IoT) چگونه صنعت ابزار دقیق را متحول می‌کند؟', date: '۱۵ فروردین ۱۴۰۳', author: 'مهندس احمدی', time: '۶ دقیقه', img: blogIot },
    { cat: 'اتوماسیون صنعتی', title: 'بررسی مزایا و چالش‌های پروژه‌های اتوماسیون در ایران', date: '۱۰ فروردین ۱۴۰۳', author: 'دکتر رضایی', time: '۸ دقیقه', img: blogAutomation },
    { cat: 'کیفیت و استاندارد', title: 'اهمیت کالیبراسیون دوره‌ای در صنایع حساس', date: '۵ فروردین ۱۴۰۳', author: 'مهندس موسوی', time: '۷ دقیقه', img: blogCalibration },
  ];

  const organizationSchema = generateOrganizationSchema({
    name: 'تول‌مستر', url: 'https://toolmaster.com', logo: 'https://toolmaster.com/logo.png',
    description: 'مرجع تخصصی ابزار دقیق و اتوماسیون صنعتی ایران',
    address: { addressLocality: 'تهران', addressCountry: 'ایران' },
    contactPoint: { telephone: '021-66120746', email: 'info@toolmaster.com', contactType: 'فروش و پشتیبانی فنی' },
  });
  const websiteSchema = generateWebSiteSchema('تول‌مستر', 'https://toolmaster.com');

  return (
    <div className="min-h-screen bg-background overflow-x-hidden">
      <SEO
        title="مرجع تخصصی ابزار دقیق و اتوماسیون صنعتی | تول‌مستر"
        description="تأمین مستقیم تجهیزات ابزار دقیق از برندهای معتبر جهانی، مشاوره رایگان مهندسی و خدمات نصب و کالیبراسیون."
        keywords="ابزار دقیق, اتوماسیون صنعتی, ژنراتور نیتروژن, فلومتر, دتکتور گاز, PLC, تول‌مستر"
        structuredData={[organizationSchema, websiteSchema]}
      />

      {/* ══════════════════════════════════════════════
          SECTION 1 — HERO
      ══════════════════════════════════════════════ */}
      <section ref={heroRef} className="relative min-h-[92vh] flex flex-col justify-end overflow-hidden">
        {/* Parallax background */}
        <motion.div style={{ y: heroY }} className="absolute inset-0 scale-110">
          <img src={heroImage} alt="اتاق کنترل صنعتی مدرن" className="h-full w-full object-cover" />
        </motion.div>

        {/* Layered overlays */}
        <div className="absolute inset-0 bg-gradient-to-t from-[#050d18] via-[#050d18]/60 to-[#050d18]/20" />
        <div className="absolute inset-0 bg-gradient-to-l from-transparent to-[#050d18]/50" />

        {/* Decorative grid overlay */}
        <div
          className="absolute inset-0 opacity-[0.04]"
          style={{
            backgroundImage: `linear-gradient(rgba(255,255,255,0.8) 1px, transparent 1px),
                             linear-gradient(90deg, rgba(255,255,255,0.8) 1px, transparent 1px)`,
            backgroundSize: '80px 80px',
          }}
        />

        {/* Content */}
        <motion.div style={{ opacity: heroOpacity }} className="container relative z-10 mx-auto px-4 sm:px-6 lg:px-8 pb-20">
          <motion.div
            className="max-w-3xl"
            initial="hidden"
            animate="show"
            variants={stagger}
          >
            {/* Eyebrow */}
            <motion.div variants={fadeUp} className="flex items-center gap-3 mb-6">
              <span className="h-px w-10 bg-accent" />
              <span className="text-xs font-bold tracking-[0.2em] text-accent uppercase">ToolMaster — مرجع صنعت ایران</span>
            </motion.div>

            {/* Headline */}
            <motion.h1 variants={fadeUp} className="text-4xl md:text-6xl font-black mb-6 leading-[1.15] text-white">
              تجهیزات{' '}
              <span
                className="relative inline-block text-accent"
                style={{ textShadow: '0 0 60px hsl(var(--accent)/0.4)' }}
              >
                ابزار دقیق
              </span>
              <br />
              از برندهای برتر جهان
            </motion.h1>

            <motion.p variants={fadeUp} className="text-base md:text-lg mb-10 text-white/60 leading-relaxed max-w-xl">
              تأمین مستقیم از ۵۰+ برند معتبر بین‌المللی، مشاوره رایگان توسط مهندسان متخصص و خدمات جامع نصب، کالیبراسیون و پشتیبانی فنی.
            </motion.p>

            {/* CTAs */}
            <motion.div variants={fadeUp} className="flex flex-wrap gap-3 mb-14">
              <Button asChild size="lg" className="bg-accent text-accent-foreground hover:bg-accent/90 font-bold h-12 px-8 shadow-[0_0_30px_hsl(var(--accent)/0.3)]">
                <Link to="/products">
                  <Package className="h-4 w-4 ml-2" />
                  مشاهده محصولات
                </Link>
              </Button>
              <Button asChild size="lg" variant="ghost" className="border border-white/20 text-white hover:bg-white/10 hover:border-white/40 font-bold h-12 px-8 backdrop-blur-sm">
                <Link to="/contact">
                  <Phone className="h-4 w-4 ml-2" />
                  مشاوره رایگان
                </Link>
              </Button>
            </motion.div>

            {/* Stats strip */}
            <motion.div
              variants={fadeUp}
              className="inline-flex rounded-xl border border-white/10 bg-white/5 backdrop-blur-md overflow-hidden"
            >
              <StatPill value="۱۵+" label="سال تجربه" />
              <StatPill value="۵۰۰+" label="مشتری فعال" />
              <StatPill value="۵۰+" label="برند بین‌المللی" />
              <StatPill value="۹۸٪" label="رضایت مشتری" />
            </motion.div>
          </motion.div>
        </motion.div>

        {/* Bottom fade */}
        <div className="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-background to-transparent" />
      </section>

      {/* ══════════════════════════════════════════════
          SEARCH BAR
      ══════════════════════════════════════════════ */}
      <div className="relative z-20 -mt-6">
        <AdvancedSearchBar />
      </div>

      {/* ══════════════════════════════════════════════
          SECTION 2 — TRUST BADGES
      ══════════════════════════════════════════════ */}
      <section className="py-10 border-y border-border/50 bg-muted/20">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            {[
              { icon: Shield, title: 'گارانتی ۱۸ ماهه', desc: 'پوشش کامل قطعات و خدمات' },
              { icon: Truck, title: 'ارسال سریع', desc: 'به تمام شهرهای صنعتی کشور' },
              { icon: Headphones, title: 'پشتیبانی ۲۴/۷', desc: 'تیم مهندسی همیشه در دسترس' },
              { icon: Award, title: 'اصالت کالا', desc: 'تأمین مستقیم از تولیدکننده' },
            ].map((item, i) => (
              <motion.div
                key={item.title}
                initial={{ opacity: 0, y: 16 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: i * 0.08, duration: 0.5 }}
                className="flex items-start gap-3 p-4 rounded-xl bg-card border border-border/50 hover:border-primary/30 transition-colors"
              >
                <div className="shrink-0 h-9 w-9 rounded-lg bg-primary/10 flex items-center justify-center">
                  <item.icon className="h-4 w-4 text-primary" />
                </div>
                <div>
                  <p className="text-sm font-bold text-foreground">{item.title}</p>
                  <p className="text-xs text-muted-foreground">{item.desc}</p>
                </div>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* ══════════════════════════════════════════════
          SECTION 3 — PRODUCT CATEGORIES
      ══════════════════════════════════════════════ */}
      <section className="py-24 bg-background">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div
            className="flex flex-col items-center text-center mb-16"
            initial="hidden" whileInView="show" viewport={{ once: true }} variants={fadeUp}
          >
            <SectionLabel>دسته‌بندی محصولات</SectionLabel>
            <h2 className="text-3xl md:text-4xl font-black text-foreground max-w-xl">
              تجهیزات تخصصی در دسته‌بندی‌های منظم
            </h2>
            <p className="text-muted-foreground mt-3 max-w-md text-sm">
              از برندهای معتبر جهانی، دسته‌بندی شده برای انتخاب سریع و آسان
            </p>
          </motion.div>

          <motion.div
            className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4"
            initial="hidden" whileInView="show" viewport={{ once: true }} variants={stagger}
          >
            {categories.map((cat, i) => (
              <motion.div key={cat.title} variants={fadeUp}>
                <Link
                  to={cat.link}
                  className="group relative block rounded-2xl overflow-hidden aspect-[4/3] border border-border hover:border-primary/40 transition-all hover:shadow-xl hover:-translate-y-1 duration-300"
                >
                  <img
                    src={cat.img}
                    alt={cat.title}
                    className="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                    loading="lazy"
                  />
                  {/* Multi-layer gradient */}
                  <div className="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-black/10" />
                  <div className="absolute inset-0 bg-primary/0 group-hover:bg-primary/10 transition-colors duration-300" />

                  {/* Count badge */}
                  <div className="absolute top-3 right-3">
                    <span className="text-[10px] font-bold bg-black/60 backdrop-blur-sm text-white/70 px-2 py-1 rounded-full">
                      {cat.count}
                    </span>
                  </div>

                  {/* Arrow icon */}
                  <div className="absolute top-3 left-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <div className="h-7 w-7 rounded-full bg-accent flex items-center justify-center">
                      <ArrowUpRight className="h-3.5 w-3.5 text-accent-foreground" />
                    </div>
                  </div>

                  {/* Bottom label */}
                  <div className="absolute bottom-0 right-0 left-0 p-4">
                    <h3 className="font-bold text-white text-sm mb-0.5 drop-shadow-lg">{cat.title}</h3>
                    <span className="text-[11px] text-accent font-semibold opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center gap-1">
                      مشاهده محصولات <ChevronLeft className="h-3 w-3" />
                    </span>
                  </div>
                </Link>
              </motion.div>
            ))}
          </motion.div>

          {/* CTA Banner */}
          <motion.div
            className="mt-10 rounded-2xl overflow-hidden relative"
            initial="hidden" whileInView="show" viewport={{ once: true }} variants={fadeUp}
          >
            <div className="absolute inset-0 bg-gradient-to-l from-primary via-primary to-primary/80" />
            <div
              className="absolute inset-0 opacity-10"
              style={{
                backgroundImage: `radial-gradient(circle at 20% 50%, white 1px, transparent 1px)`,
                backgroundSize: '32px 32px',
              }}
            />
            <div className="relative p-6 md:p-8 flex flex-col md:flex-row items-center justify-between gap-5">
              <div className="text-center md:text-right">
                <p className="text-lg font-black text-primary-foreground">در انتخاب محصول مناسب نیاز به راهنمایی دارید؟</p>
                <p className="text-sm text-primary-foreground/60 mt-1">مهندسان ما بهترین گزینه را با توجه به نیاز شما پیشنهاد می‌دهند</p>
              </div>
              <Button asChild className="shrink-0 bg-accent text-accent-foreground hover:bg-accent/90 font-bold shadow-lg h-11 px-6">
                <Link to="/contact">دریافت مشاوره رایگان</Link>
              </Button>
            </div>
          </motion.div>
        </div>
      </section>

      {/* ══════════════════════════════════════════════
          BRANDS CAROUSEL
      ══════════════════════════════════════════════ */}
      <BrandsCarousel />

      {/* ══════════════════════════════════════════════
          SECTION 4 — COMPANY INTRO
      ══════════════════════════════════════════════ */}
      <section className="py-24 bg-background relative overflow-hidden">
        {/* Decorative blob */}
        <div className="absolute -left-64 top-1/2 -translate-y-1/2 h-[600px] w-[600px] rounded-full bg-primary/5 blur-[100px] pointer-events-none" />

        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid md:grid-cols-2 gap-16 items-center max-w-6xl mx-auto">

            {/* Left — Text */}
            <motion.div initial="hidden" whileInView="show" viewport={{ once: true }} variants={fadeLeft}>
              <SectionLabel>درباره تول‌مستر</SectionLabel>
              <h2 className="text-3xl md:text-4xl font-black mb-4 text-foreground leading-tight">
                یک دهه اعتماد صنعت ایران
                <br />
                <span className="text-primary">به دقت و تخصص</span>
              </h2>
              <p className="text-sm text-muted-foreground leading-[2] mb-8">
                شرکت تول‌مستر با بهره‌گیری از بیش از یک دهه تجربه تخصصی در حوزه ابزار دقیق و اتوماسیون صنعتی، به عنوان یکی از معتبرترین تأمین‌کنندگان تجهیزات آزمایشگاهی و صنعتی ایران فعالیت می‌کند. با ارتباطات مستقیم با برندهای برتر جهانی، صنایع ایران را به جدیدترین فناوری‌های روز دنیا متصل می‌کنیم.
              </p>

              {/* Feature grid */}
              <div className="grid grid-cols-2 gap-3 mb-8">
                {[
                  { icon: Users, title: 'نمایندگی مستقیم', desc: 'نمایندگی مجاز ۲۰+ برند بین‌المللی' },
                  { icon: Shield, title: 'مشاوره تخصصی', desc: 'تیم ۱۵ نفره مهندسان ابزار دقیق' },
                  { icon: Wrench, title: 'تعمیر و نگهداری', desc: 'گارانتی ۱۸ ماهه و پشتیبانی ۲۴/۷' },
                  { icon: Truck, title: 'تحویل سریع', desc: 'انبار مرکزی با ۵۰۰+ کالای آماده' },
                ].map(item => (
                  <div
                    key={item.title}
                    className="group rounded-xl border border-border bg-card p-4 hover:border-primary/30 hover:bg-primary/5 transition-all cursor-default"
                  >
                    <div className="h-8 w-8 rounded-lg bg-primary/10 flex items-center justify-center mb-3 group-hover:bg-primary/20 transition-colors">
                      <item.icon className="h-4 w-4 text-primary" />
                    </div>
                    <p className="text-xs font-bold text-foreground">{item.title}</p>
                    <p className="text-[11px] text-muted-foreground mt-0.5">{item.desc}</p>
                  </div>
                ))}
              </div>

              <Button asChild className="bg-accent text-accent-foreground hover:bg-accent/90 font-bold">
                <Link to="/contact">
                  <Phone className="h-4 w-4 ml-2" />
                  تماس با کارشناسان تول‌مستر
                </Link>
              </Button>
            </motion.div>

            {/* Right — Stats + Industries */}
            <motion.div
              initial={{ opacity: 0, x: -30 }}
              whileInView={{ opacity: 1, x: 0 }}
              viewport={{ once: true }}
              transition={{ duration: 0.6, ease: [0.22, 1, 0.36, 1] }}
            >
              {/* Stats card */}
              <div className="rounded-2xl bg-primary p-6 mb-4 relative overflow-hidden">
                <div
                  className="absolute inset-0 opacity-5"
                  style={{
                    backgroundImage: `linear-gradient(135deg, white 25%, transparent 25%),
                                     linear-gradient(225deg, white 25%, transparent 25%)`,
                    backgroundSize: '20px 20px',
                  }}
                />
                <p className="text-xs font-bold text-primary-foreground/60 mb-5 tracking-widest uppercase">تول‌مستر در یک نگاه</p>
                <div className="space-y-3">
                  {[
                    { value: '۱۵+', label: 'سال تجربه تخصصی' },
                    { value: '۲۰۰+', label: 'پروژه موفق اجرا شده' },
                    { value: '۵۰۰+', label: 'مشتری صنعتی فعال' },
                    { value: '۹۸٪', label: 'رضایت مشتریان' },
                    { value: '۵۰+', label: 'برند بین‌المللی' },
                  ].map(stat => (
                    <div key={stat.label} className="flex items-center gap-4 border-b border-primary-foreground/10 pb-3 last:border-0 last:pb-0">
                      <span className="text-2xl font-black text-accent min-w-[64px] ltr">{stat.value}</span>
                      <span className="text-sm text-primary-foreground/70">{stat.label}</span>
                    </div>
                  ))}
                </div>
              </div>

              {/* Industries grid */}
              <div className="grid grid-cols-3 gap-2">
                {[
                  { name: 'نفت و گاز', img: industryOilGas },
                  { name: 'پتروشیمی', img: industryPetrochemical },
                  { name: 'داروسازی', img: industryPharma },
                  { name: 'نیروگاه', img: industryPower },
                  { name: 'آب و فاضلاب', img: industryWater },
                  { name: 'معادن', img: industryMining },
                ].map(ind => (
                  <div key={ind.name} className="relative rounded-xl overflow-hidden aspect-square group cursor-pointer">
                    <img src={ind.img} alt={ind.name} className="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy" />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />
                    <div className="absolute inset-0 bg-primary/0 group-hover:bg-primary/20 transition-colors duration-300" />
                    <p className="absolute bottom-2 right-0 left-0 text-center text-[10px] font-bold text-white">{ind.name}</p>
                  </div>
                ))}
              </div>
            </motion.div>
          </div>
        </div>
      </section>

      {/* ══════════════════════════════════════════════
          SECTION 5 — FEATURED PRODUCTS
      ══════════════════════════════════════════════ */}
      <section className="py-24 bg-muted/30">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div className="flex flex-col items-center text-center mb-16" initial="hidden" whileInView="show" viewport={{ once: true }} variants={fadeUp}>
            <SectionLabel>منتخب مهندسان</SectionLabel>
            <h2 className="text-3xl md:text-4xl font-black text-foreground">محصولات پیشنهادی تول‌مستر</h2>
            <p className="text-muted-foreground mt-3 text-sm max-w-md">
              از هر برند همکار یک محصول شاخص، منتخب برای عملکرد بهینه و بهره‌وری بالا
            </p>
          </motion.div>

          <motion.div
            className="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
            initial="hidden" whileInView="show" viewport={{ once: true }} variants={stagger}
          >
            {featuredProducts.map((product) => (
              <motion.div key={product.model} variants={fadeUp}>
                <div className="group rounded-2xl border border-border bg-card overflow-hidden h-full flex flex-col hover:border-primary/30 hover:shadow-xl transition-all duration-300">
                  {/* Image */}
                  <div className="relative h-44 bg-muted/20 overflow-hidden">
                    <img
                      src={product.img}
                      alt={`${product.name} — ${product.brand}`}
                      className="h-full w-full object-contain p-4 group-hover:scale-105 transition-transform duration-500"
                      loading="lazy"
                    />
                    <Badge className="absolute top-2 right-2 bg-accent/90 text-accent-foreground border-0 text-[10px] font-bold">
                      {product.badge}
                    </Badge>
                    <span className="absolute top-2 left-2 text-[10px] bg-card/90 backdrop-blur-sm rounded-md px-2 py-1 text-muted-foreground border border-border/50">
                      {product.cat}
                    </span>
                  </div>

                  {/* Body */}
                  <div className="p-4 flex flex-col flex-1">
                    <h3 className="text-sm font-bold text-foreground mb-1 leading-snug">{product.name}</h3>
                    <p className="text-xs text-muted-foreground mb-0.5 ltr font-mono">{product.model}</p>
                    <p className="text-xs text-primary font-bold mb-3 ltr">{product.brand}</p>

                    <ul className="space-y-1.5 mb-4 flex-1">
                      {product.features.map(f => (
                        <li key={f} className="flex items-start gap-2 text-xs text-muted-foreground">
                          <CheckCircle2 className="h-3 w-3 text-green-500 flex-shrink-0 mt-0.5" />
                          {f}
                        </li>
                      ))}
                    </ul>

                    <div className="flex gap-2 mt-auto">
                      <Button size="sm" className="flex-1 bg-accent text-accent-foreground hover:bg-accent/90 text-xs font-bold h-8">
                        استعلام قیمت
                      </Button>
                      <Button asChild size="sm" variant="outline" className="text-xs h-8 px-3">
                        <Link to={`/products/${product.id}`}>
                          <ArrowUpRight className="h-3 w-3" />
                        </Link>
                      </Button>
                    </div>
                  </div>
                </div>
              </motion.div>
            ))}
          </motion.div>

          {/* Disclaimer */}
          <motion.div
            initial="hidden" whileInView="show" viewport={{ once: true }} variants={fadeUp}
            className="mt-8 rounded-xl border border-border bg-card/50 p-4 flex flex-col sm:flex-row items-center gap-3 text-sm text-muted-foreground"
          >
            <span className="text-primary">ℹ️</span>
            <span>قیمت‌ها بر اساس مشخصات فنی، تعداد و شرایط پرداخت متغیر است.</span>
            <span className="sm:mr-auto text-primary font-semibold text-xs">📞 برای استعلام قیمت با کارشناس مربوطه تماس بگیرید</span>
          </motion.div>
        </div>
      </section>

      {/* ══════════════════════════════════════════════
          SECTION 6 — ACADEMY
      ══════════════════════════════════════════════ */}
      <section className="py-24 bg-background">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div className="flex flex-col items-center text-center mb-16" initial="hidden" whileInView="show" viewport={{ once: true }} variants={fadeUp}>
            <SectionLabel>آکادمی تول‌مستر</SectionLabel>
            <h2 className="text-3xl md:text-4xl font-black text-foreground">دانش تخصصی ابزار دقیق</h2>
            <p className="text-muted-foreground mt-3 text-sm">برای انتخاب آگاهانه و بهره‌برداری بهینه از تجهیزات</p>
          </motion.div>

          <div className="grid md:grid-cols-3 lg:grid-cols-4 gap-4">
            {articles.map((article, i) => (
              <motion.div key={article.title} initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ delay: i * 0.08, duration: 0.5 }}>
                <Link
                  to="/blog"
                  className="group block rounded-2xl border border-border bg-card overflow-hidden h-full hover:border-primary/30 hover:shadow-lg transition-all duration-300"
                >
                  <div className="h-40 overflow-hidden relative">
                    <img src={article.img} alt={article.title} className="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent" />
                  </div>
                  <div className="p-5">
                    <div className="flex items-center gap-2 mb-3">
                      <div className="h-6 w-6 rounded-md bg-primary/10 flex items-center justify-center">
                        <article.icon className="h-3 w-3 text-primary" />
                      </div>
                      <span className="text-[10px] font-bold text-primary tracking-wide">{article.cat}</span>
                    </div>
                    <h3 className="text-sm font-bold text-foreground mb-3 group-hover:text-primary transition-colors leading-snug">{article.title}</h3>
                    <div className="flex items-center gap-3 text-[10px] text-muted-foreground">
                      <span className="flex items-center gap-1"><Clock className="h-3 w-3" /> {article.time}</span>
                      <span className="flex items-center gap-1"><BarChart3 className="h-3 w-3" /> {article.level}</span>
                    </div>
                  </div>
                </Link>
              </motion.div>
            ))}

            {/* Lead magnet card */}
            <motion.div initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ delay: 0.28, duration: 0.5 }}>
              <div className="rounded-2xl bg-primary text-primary-foreground p-5 h-full relative overflow-hidden">
                <div className="absolute -top-8 -left-8 h-32 w-32 rounded-full bg-white/5" />
                <div className="absolute -bottom-6 -right-6 h-24 w-24 rounded-full bg-white/5" />
                <div className="relative">
                  <p className="text-sm font-black mb-1">📦 بسته آموزشی رایگان</p>
                  <p className="text-[10px] text-primary-foreground/50 mb-4">ویژه مهندسان صنعت ایران — ۱۴۰۳</p>
                  <ul className="space-y-2 text-xs text-primary-foreground/75 mb-5">
                    <li className="flex items-center gap-2"><CheckCircle2 className="h-3 w-3 text-accent shrink-0" /> کاتالوگ فنی فلومتر E+H</li>
                    <li className="flex items-center gap-2"><CheckCircle2 className="h-3 w-3 text-accent shrink-0" /> راهنمای نصب PLC زیمنس</li>
                    <li className="flex items-center gap-2"><CheckCircle2 className="h-3 w-3 text-accent shrink-0" /> جدول مقایسه ۱۰ برند دتکتور</li>
                    <li className="flex items-center gap-2"><CheckCircle2 className="h-3 w-3 text-accent shrink-0" /> چک‌لیست نگهداری پیشگیرانه</li>
                  </ul>
                  <input
                    type="email"
                    placeholder="ایمیل شما..."
                    className="w-full rounded-lg bg-primary-foreground/10 border border-primary-foreground/20 px-3 py-2.5 text-xs text-primary-foreground placeholder:text-primary-foreground/30 focus:outline-none focus:ring-2 focus:ring-accent/50 mb-2"
                  />
                  <Button className="w-full bg-accent text-accent-foreground hover:bg-accent/90 text-xs font-bold">
                    دریافت رایگان
                  </Button>
                </div>
              </div>
            </motion.div>
          </div>
        </div>
      </section>

      {/* ══════════════════════════════════════════════
          SECTION 7 — LATEST BLOG
      ══════════════════════════════════════════════ */}
      <section className="py-24 bg-muted/30">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div className="flex flex-col sm:flex-row items-start sm:items-end justify-between gap-4 mb-12" initial="hidden" whileInView="show" viewport={{ once: true }} variants={fadeUp}>
            <div>
              <SectionLabel>مجله تخصصی</SectionLabel>
              <h2 className="text-3xl md:text-4xl font-black text-foreground">آخرین مقالات</h2>
            </div>
            <Button asChild variant="outline" size="sm" className="shrink-0">
              <Link to="/blog">مشاهده همه <ChevronLeft className="h-3.5 w-3.5 mr-1" /></Link>
            </Button>
          </motion.div>

          <div className="grid md:grid-cols-3 gap-5 mb-8">
            {latestBlogPosts.map((post, i) => (
              <motion.div key={post.title} initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ delay: i * 0.1, duration: 0.5 }}>
                <Link to="/blog" className="group block rounded-2xl border border-border bg-card overflow-hidden h-full hover:shadow-lg hover:border-primary/30 transition-all duration-300">
                  <div className="h-44 overflow-hidden relative">
                    <img src={post.img} alt={post.title} className="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent" />
                    <Badge variant="secondary" className="absolute bottom-3 right-3 text-[10px]">{post.cat}</Badge>
                  </div>
                  <div className="p-5">
                    <h3 className="text-sm font-bold text-foreground mb-3 group-hover:text-primary transition-colors leading-relaxed">{post.title}</h3>
                    <div className="flex items-center justify-between text-[10px] text-muted-foreground">
                      <span>{post.author}</span>
                      <span className="flex items-center gap-1"><Clock className="h-3 w-3" /> {post.time}</span>
                    </div>
                  </div>
                </Link>
              </motion.div>
            ))}
          </div>

          {/* Topic pills */}
          <motion.div className="flex flex-wrap gap-2" initial="hidden" whileInView="show" viewport={{ once: true }} variants={fadeUp}>
            {['راهنمای خرید', 'آموزش تخصصی', 'تحلیل بازار', 'اخبار صنعت', 'فناوری‌های نوین', 'کیفیت و استاندارد'].map(cat => (
              <Link
                key={cat}
                to={`/blog?category=${cat}`}
                className="rounded-full border border-border bg-card px-4 py-1.5 text-xs font-medium text-foreground hover:bg-primary hover:text-primary-foreground hover:border-primary transition-colors"
              >
                {cat}
              </Link>
            ))}
          </motion.div>
        </div>
      </section>

      {/* ══════════════════════════════════════════════
          SECTION 8 — FINAL CTA
      ══════════════════════════════════════════════ */}
      <section className="relative py-24 overflow-hidden">
        {/* Background */}
        <div className="absolute inset-0 bg-primary" />
        <div
          className="absolute inset-0 opacity-[0.06]"
          style={{
            backgroundImage: `linear-gradient(rgba(255,255,255,1) 1px, transparent 1px),
                             linear-gradient(90deg, rgba(255,255,255,1) 1px, transparent 1px)`,
            backgroundSize: '60px 60px',
          }}
        />
        <div className="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-primary-foreground/20 to-transparent" />
        <div className="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-primary-foreground/20 to-transparent" />

        {/* Decorative circles */}
        <div className="absolute -right-32 top-1/2 -translate-y-1/2 h-[500px] w-[500px] rounded-full border border-primary-foreground/10" />
        <div className="absolute -right-16 top-1/2 -translate-y-1/2 h-[350px] w-[350px] rounded-full border border-primary-foreground/10" />

        <div className="container relative z-10 mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div className="max-w-2xl mx-auto text-center" initial="hidden" whileInView="show" viewport={{ once: true }} variants={stagger}>
            <motion.div variants={fadeUp} className="flex items-center justify-center gap-2 mb-4">
              <span className="h-px w-8 bg-accent" />
              <span className="text-xs font-bold tracking-widest text-accent uppercase">همین الان شروع کنید</span>
              <span className="h-px w-8 bg-accent" />
            </motion.div>
            <motion.h2 variants={fadeUp} className="text-3xl md:text-5xl font-black text-primary-foreground mb-4 leading-tight">
              پروژه صنعتی جدید دارید؟
            </motion.h2>
            <motion.p variants={fadeUp} className="text-primary-foreground/60 mb-10 text-lg leading-relaxed">
              با مهندسان تول‌مستر مشورت کنید. مشاوره اولیه کاملاً رایگان است.
            </motion.p>
            <motion.div variants={fadeUp} className="flex flex-wrap gap-3 justify-center">
              <Button asChild size="lg" className="bg-accent text-accent-foreground hover:bg-accent/90 font-bold h-12 px-8 shadow-[0_0_30px_hsl(var(--accent)/0.4)]">
                <Link to="/contact">درخواست مشاوره رایگان</Link>
              </Button>
              <Button asChild size="lg" variant="ghost" className="border border-primary-foreground/20 text-primary-foreground hover:bg-primary-foreground/10 h-12 px-8">
                <Link to="/products">
                  <Package className="h-4 w-4 ml-2" />
                  مشاهده محصولات
                </Link>
              </Button>
            </motion.div>
          </motion.div>
        </div>
      </section>
    </div>
  );
}
