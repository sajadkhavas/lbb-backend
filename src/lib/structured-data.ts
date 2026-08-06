// Helper functions to generate structured data (Schema.org JSON-LD)

export interface Organization {
  name: string;
  url: string;
  logo: string;
  description: string;
  address?: {
    streetAddress?: string;
    addressLocality: string;
    addressCountry: string;
  };
  contactPoint?: {
    telephone: string;
    email: string;
    contactType: string;
  };
}

export interface Product {
  name: string;
  description: string;
  image?: string;
  brand: string;
  offers?: {
    price?: string;
    priceCurrency?: string;
    availability: string;
  };
  aggregateRating?: {
    ratingValue: number;
    reviewCount: number;
  };
}

export interface Article {
  headline: string;
  description: string;
  image?: string;
  author: string;
  publisher: Organization;
  datePublished: string;
  dateModified?: string;
}

export function generateOrganizationSchema(org: Organization) {
  return {
    '@context': 'https://schema.org',
    '@type': 'Organization',
    name: org.name,
    url: org.url,
    logo: org.logo,
    description: org.description,
    ...(org.address && {
      address: {
        '@type': 'PostalAddress',
        addressLocality: org.address.addressLocality,
        addressCountry: org.address.addressCountry,
        ...(org.address.streetAddress && {
          streetAddress: org.address.streetAddress
        })
      }
    }),
    ...(org.contactPoint && {
      contactPoint: {
        '@type': 'ContactPoint',
        telephone: org.contactPoint.telephone,
        email: org.contactPoint.email,
        contactType: org.contactPoint.contactType
      }
    })
  };
}

export function generateProductSchema(product: Product) {
  return {
    '@context': 'https://schema.org',
    '@type': 'Product',
    name: product.name,
    description: product.description,
    ...(product.image && { image: product.image }),
    brand: {
      '@type': 'Brand',
      name: product.brand
    },
    ...(product.offers && {
      offers: {
        '@type': 'Offer',
        ...(product.offers.price && { price: product.offers.price }),
        ...(product.offers.priceCurrency && { priceCurrency: product.offers.priceCurrency }),
        availability: `https://schema.org/${product.offers.availability}`
      }
    }),
    ...(product.aggregateRating && {
      aggregateRating: {
        '@type': 'AggregateRating',
        ratingValue: product.aggregateRating.ratingValue,
        reviewCount: product.aggregateRating.reviewCount
      }
    })
  };
}

export function generateArticleSchema(article: Article) {
  return {
    '@context': 'https://schema.org',
    '@type': 'Article',
    headline: article.headline,
    description: article.description,
    ...(article.image && { image: article.image }),
    author: {
      '@type': 'Person',
      name: article.author
    },
    publisher: {
      '@type': 'Organization',
      name: article.publisher.name,
      logo: {
        '@type': 'ImageObject',
        url: article.publisher.logo
      }
    },
    datePublished: article.datePublished,
    ...(article.dateModified && { dateModified: article.dateModified })
  };
}

export function generateBreadcrumbSchema(items: { name: string; url: string }[]) {
  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: items.map((item, index) => ({
      '@type': 'ListItem',
      position: index + 1,
      name: item.name,
      item: item.url
    }))
  };
}

export function generateWebSiteSchema(name: string, url: string) {
  return {
    '@context': 'https://schema.org',
    '@type': 'WebSite',
    name,
    url,
    potentialAction: {
      '@type': 'SearchAction',
      target: {
        '@type': 'EntryPoint',
        urlTemplate: `${url}/products?search={search_term_string}`
      },
      'query-input': 'required name=search_term_string'
    }
  };
}
