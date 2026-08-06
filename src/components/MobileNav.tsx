import { Link } from 'react-router-dom';
import { NavigationItem } from '@/data/navigation';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Button } from '@/components/ui/button';
import { ChevronDown, Phone } from 'lucide-react';
import { useState } from 'react';

interface MobileNavProps {
  items: NavigationItem[];
  onClose: () => void;
}

export function MobileNav({ items, onClose }: MobileNavProps) {
  return (
    <div className="space-y-4">
      {/* Brand Summary */}
      <div className="px-4 py-3 bg-primary/5 rounded-lg border border-primary/10">
        <p className="text-sm font-bold text-foreground">تول‌مستر | ToolMaster</p>
        <p className="text-xs text-muted-foreground mt-1">مرجع تخصصی ابزار دقیق و اتوماسیون صنعتی ایران</p>
      </div>

      {/* Nav Items */}
      <div className="space-y-1">
        {items.map((item) => (
          <MobileNavSection key={item.id} item={item} onClose={onClose} />
        ))}
      </div>

      {/* Bottom CTAs */}
      <div className="px-3 pt-4 border-t border-border space-y-2">
        <Link to="/contact" onClick={onClose}>
          <Button className="w-full bg-accent text-accent-foreground hover:bg-accent/90" size="lg">
            <Phone className="h-4 w-4 ml-2" />
            درخواست مشاوره رایگان
          </Button>
        </Link>
        <Link to="/products" onClick={onClose}>
          <Button variant="outline" className="w-full mt-2" size="lg">
            مشاهده تمام محصولات
          </Button>
        </Link>
      </div>
    </div>
  );
}

function MobileNavSection({ item, onClose }: { item: NavigationItem; onClose: () => void }) {
  const [isOpen, setIsOpen] = useState(false);
  const hasChildren = item.children && item.children.length > 0;

  if (!hasChildren) {
    return (
      <Link
        to={item.href || '#'}
        onClick={onClose}
        className="block px-3 py-2.5 text-sm font-medium text-foreground hover:bg-muted rounded-md transition-colors"
      >
        {item.label}
      </Link>
    );
  }

  return (
    <Collapsible open={isOpen} onOpenChange={setIsOpen}>
      <CollapsibleTrigger className="flex items-center justify-between w-full px-3 py-2.5 text-sm font-medium text-foreground hover:bg-muted rounded-md transition-colors">
        <span>{item.label}</span>
        <ChevronDown className={`h-4 w-4 transition-transform ${isOpen ? 'rotate-180' : ''}`} />
      </CollapsibleTrigger>
      <CollapsibleContent className="space-y-0.5 mt-1 mr-4">
        {item.children?.map((child) => {
          const hasSubChildren = child.children && child.children.length > 0;
          if (hasSubChildren) {
            return <MobileNavSection key={child.id} item={child} onClose={onClose} />;
          }
          return (
            <Link
              key={child.id}
              to={child.href || '#'}
              onClick={onClose}
              className="block py-2 px-3 text-sm text-muted-foreground hover:text-foreground hover:bg-muted/50 rounded transition-colors"
            >
              {child.label}
            </Link>
          );
        })}
      </CollapsibleContent>
    </Collapsible>
  );
}
