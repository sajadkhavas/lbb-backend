import React, { createContext, useContext, useState, ReactNode } from 'react';

export interface RFQProduct {
  id: string;
  name: string;
  type: string;
  grade?: string;
}

interface RFQContextType {
  products: RFQProduct[];
  addProduct: (product: RFQProduct) => void;
  removeProduct: (id: string) => void;
  clearProducts: () => void;
}

const RFQContext = createContext<RFQContextType | undefined>(undefined);

export function RFQProvider({ children }: { children: ReactNode }) {
  const [products, setProducts] = useState<RFQProduct[]>([]);

  const addProduct = (product: RFQProduct) => {
    setProducts(prev => {
      // Don't add duplicates
      if (prev.some(p => p.id === product.id)) {
        return prev;
      }
      return [...prev, product];
    });
  };

  const removeProduct = (id: string) => {
    setProducts(prev => prev.filter(p => p.id !== id));
  };

  const clearProducts = () => {
    setProducts([]);
  };

  return (
    <RFQContext.Provider value={{ products, addProduct, removeProduct, clearProducts }}>
      {children}
    </RFQContext.Provider>
  );
}

export function useRFQ() {
  const context = useContext(RFQContext);
  if (context === undefined) {
    throw new Error('useRFQ must be used within an RFQProvider');
  }
  return context;
}
