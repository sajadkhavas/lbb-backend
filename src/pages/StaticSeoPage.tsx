import { useSitePage } from '@/api/hooks';
import { SEO } from '@/components/SEO';

interface StaticSeoPageProps {
  pageKey: string;
}

export default function StaticSeoPage({ pageKey }: StaticSeoPageProps) {
  const { data: page, isLoading, error } = useSitePage(pageKey);

  if (isLoading) {
    return (
      <div className="min-h-[60vh] flex items-center justify-center">
        <div className="animate-spin h-8 w-8 border-4 border-primary border-t-transparent rounded-full" />
      </div>
    );
  }

  if (error || !page) {
    return (
      <div className="min-h-[60vh] flex items-center justify-center text-muted-foreground">
        <p>صفحه یافت نشد.</p>
      </div>
    );
  }

  return (
    <>
      <SEO
        title={page.meta_title || page.title}
        description={page.meta_description || ''}
        keywords={page.meta_keywords || ''}
      />

      {/* Hero */}
      {(page.hero_title || page.hero_description) && (
        <section
          className="py-20 text-center text-white"
          style={{
            background: 'linear-gradient(135deg, hsl(var(--hero-gradient-start)), hsl(var(--hero-gradient-end)))',
          }}
        >
          <div className="container mx-auto px-4 sm:px-6 lg:px-8 max-w-3xl">
            {page.hero_title && (
              <h1 className="text-3xl md:text-5xl font-black mb-4 leading-snug">{page.hero_title}</h1>
            )}
            {page.hero_description && (
              <p className="text-lg text-white/80 leading-relaxed">{page.hero_description}</p>
            )}
          </div>
        </section>
      )}

      {/* Content */}
      <section className="py-16">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
          {!page.hero_title && (
            <h1 className="text-3xl font-black mb-8 text-foreground">{page.title}</h1>
          )}
          {page.content ? (
            <div
              className="prose prose-lg max-w-none text-foreground leading-relaxed"
              dangerouslySetInnerHTML={{ __html: page.content }}
            />
          ) : (
            <p className="text-muted-foreground">محتوایی برای این صفحه وارد نشده است.</p>
          )}
        </div>
      </section>
    </>
  );
}
