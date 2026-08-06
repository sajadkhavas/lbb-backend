export interface ImageConfig {
  src: string;
  alt: string;
  description: string;
}

export const siteImages = {
  // Home page
  homeHero: {
    src: '/images/hero-home-polymers.jpg',
    alt: 'Aerial view of Dubai port with colorful polymer pellets and containers',
    description: 'Industrial polymer logistics: port containers and colorful plastic granules representing global supply chain'
  },
  homeQuality: {
    src: '/images/home-quality-lab.jpg',
    alt: 'Laboratory technician testing polymer samples with modern equipment',
    description: 'Quality control lab scene with polymer testing and analysis equipment'
  },
  homeSustainability: {
    src: '/images/home-sustainability-recycling.jpg',
    alt: 'Circular recycling process showing sorted plastic waste transformed into clean granules',
    description: 'Sustainable polymer recycling: from waste plastic to high-quality recycled granules'
  },
  
  // Products page
  productsHero: {
    src: '/images/hero-products-pellets.jpg',
    alt: 'Collage of colorful polymer pellets in various grades with technical diagrams',
    description: 'Diverse polymer pellets and granules with technical specifications overlay'
  },
  
  // About page
  aboutHero: {
    src: '/images/hero-about-facility.jpg',
    alt: 'Modern polymer processing facility with organized warehouse and quality control lab',
    description: 'PolySource facility: advanced warehouse and laboratory for polymer processing'
  },
  aboutTeam: {
    src: '/images/about-team.jpg',
    alt: 'Professional team of polymer specialists and engineers in industrial setting',
    description: 'PolySource team: experienced polymer specialists and technical experts'
  },
  
  // Sustainability page
  sustainabilityHero: {
    src: '/images/hero-sustainability-circular.jpg',
    alt: 'Circular economy visualization with recycling arrows and clean polymer pellets',
    description: 'Circular economy in polymer industry: closed-loop recycling system'
  },
  sustainabilityProcess: {
    src: '/images/sustainability-sorting.jpg',
    alt: 'Advanced plastic sorting and processing line for recycling operations',
    description: 'High-tech recycling: automated sorting and processing of plastic waste'
  },
  
  // Services/Contact
  servicesConsulting: {
    src: '/images/services-consulting.jpg',
    alt: 'Engineers reviewing technical polymer specifications and material data sheets',
    description: 'Technical consulting: material selection and polymer engineering support'
  },
  servicesLogistics: {
    src: '/images/services-logistics.jpg',
    alt: 'Container port with trucks and global shipping infrastructure',
    description: 'Global logistics: efficient polymer transportation and supply chain'
  },
  
  // Product category images
  categoryPetrochemical: {
    src: '/images/category-petrochemical.jpg',
    alt: 'Virgin polymer pellets in natural white color with petrochemical plant in background',
    description: 'Petrochemical raw materials: high-purity virgin polymers'
  },
  categoryRecycled: {
    src: '/images/category-recycled.jpg',
    alt: 'Recycled polymer granules in various colors with sustainability certification',
    description: 'Recycled materials: certified sustainable polymer granules'
  },
  categoryCompounds: {
    src: '/images/category-compounds.jpg',
    alt: 'Colored masterbatch pellets and technical compound samples with testing equipment',
    description: 'Compounds and masterbatches: custom formulations and color solutions'
  },
  categoryFinished: {
    src: '/images/category-finished.jpg',
    alt: 'Various finished polymer parts including automotive components and packaging',
    description: 'Finished products: molded parts and polymer applications'
  }
};
