import { Link } from 'react-router-dom';
import { NavigationItem } from '@/data/navigation';
import { ChevronDown } from 'lucide-react';
import { cn } from '@/lib/utils';

interface DesktopDropdownProps {
  item: NavigationItem;
  isOpen: boolean;
  onOpenChange: (open: boolean) => void;
  isActive: (href: string) => boolean;
}

export function DesktopDropdown({ item, isOpen, onOpenChange, isActive }: DesktopDropdownProps) {
  const isAnyChildActive = item.children?.some(child => isActive(child.href || ''));

  if (!item.children || item.children.length === 0) {
    return (
      <Link
        to={item.href || '#'}
        className={cn(
          'text-sm font-semibold transition-colors px-3 py-2 rounded-md',
          isActive(item.href || '')
            ? 'text-primary bg-primary/5'
            : 'text-foreground hover:text-primary hover:bg-muted'
        )}
      >
        {item.label}
      </Link>
    );
  }

  return (
    <div
      className="relative"
      onMouseEnter={() => onOpenChange(true)}
      onMouseLeave={() => onOpenChange(false)}
    >
      <button
        className={cn(
          'flex items-center gap-1 text-sm font-semibold transition-colors px-3 py-2 rounded-md',
          isAnyChildActive
            ? 'text-primary bg-primary/5'
            : 'text-foreground hover:text-primary hover:bg-muted'
        )}
      >
        {item.label}
        <ChevronDown className={`h-3.5 w-3.5 transition-transform ${isOpen ? 'rotate-180' : ''}`} />
      </button>

      {isOpen && (
        <div className="absolute top-full right-0 mt-1 min-w-[220px] border border-border bg-popover shadow-lg rounded-lg z-[60] py-2">
          {item.children.map((child) => (
            <Link
              key={child.id}
              to={child.href || '#'}
              onClick={() => onOpenChange(false)}
              className="block px-4 py-2.5 text-sm text-popover-foreground hover:text-primary hover:bg-muted transition-colors"
            >
              {child.label}
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
