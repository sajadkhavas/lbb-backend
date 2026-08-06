import { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { Download, X, Smartphone } from "lucide-react";
import { Button } from "@/components/ui/button";

interface BeforeInstallPromptEvent extends Event {
  prompt: () => Promise<void>;
  userChoice: Promise<{ outcome: "accepted" | "dismissed" }>;
}

export function PWAInstallPrompt() {
  const [deferredPrompt, setDeferredPrompt] = useState<BeforeInstallPromptEvent | null>(null);
  const [showPrompt, setShowPrompt] = useState(false);
  const [isIOS, setIsIOS] = useState(false);
  const [isInstalled, setIsInstalled] = useState(false);

  useEffect(() => {
    // Check if already installed
    const isStandalone =
      window.matchMedia("(display-mode: standalone)").matches ||
      (window.navigator as any).standalone === true;

    if (isStandalone) {
      setIsInstalled(true);
      return;
    }

    // Detect iOS
    const ios = /iphone|ipad|ipod/.test(window.navigator.userAgent.toLowerCase());
    setIsIOS(ios);

    // Check if dismissed before
    const dismissed = localStorage.getItem("pwa-prompt-dismissed");
    if (dismissed) return;

    // Listen for install prompt (Android/Desktop)
    const handler = (e: Event) => {
      e.preventDefault();
      setDeferredPrompt(e as BeforeInstallPromptEvent);
      // Show after 5 seconds
      setTimeout(() => setShowPrompt(true), 5000);
    };

    window.addEventListener("beforeinstallprompt", handler);

    // Show iOS prompt after 8 seconds
    if (ios) {
      setTimeout(() => setShowPrompt(true), 8000);
    }

    return () => window.removeEventListener("beforeinstallprompt", handler);
  }, []);

  const handleInstall = async () => {
    if (deferredPrompt) {
      await deferredPrompt.prompt();
      const { outcome } = await deferredPrompt.userChoice;
      if (outcome === "accepted") {
        setShowPrompt(false);
        setDeferredPrompt(null);
      }
    }
  };

  const handleDismiss = () => {
    setShowPrompt(false);
    localStorage.setItem("pwa-prompt-dismissed", "true");
  };

  if (isInstalled || !showPrompt) return null;

  return (
    <AnimatePresence>
      {showPrompt && (
        <motion.div
          initial={{ y: 100, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          exit={{ y: 100, opacity: 0 }}
          transition={{ type: "spring", damping: 20 }}
          className="fixed bottom-4 left-4 right-4 z-50 md:left-auto md:right-4 md:w-96"
          dir="rtl"
        >
          <div className="bg-card border border-border rounded-2xl shadow-2xl p-4">
            <div className="flex items-start gap-3">
              <div className="bg-primary/10 rounded-xl p-2.5 flex-shrink-0">
                <Smartphone className="h-6 w-6 text-primary" />
              </div>
              <div className="flex-1 min-w-0">
                <h3 className="font-bold text-foreground text-sm mb-1">
                  نصب اپ پارس ابزار دقیق
                </h3>
                {isIOS ? (
                  <p className="text-muted-foreground text-xs leading-relaxed">
                    برای نصب: دکمه{" "}
                    <span className="font-semibold text-foreground">Share</span>{" "}
                    را بزنید، سپس{" "}
                    <span className="font-semibold text-foreground">
                      Add to Home Screen
                    </span>{" "}
                    را انتخاب کنید.
                  </p>
                ) : (
                  <p className="text-muted-foreground text-xs leading-relaxed">
                    اپ را روی دستگاه خود نصب کنید و بدون مرورگر استفاده کنید.
                  </p>
                )}
              </div>
              <button
                onClick={handleDismiss}
                className="text-muted-foreground hover:text-foreground flex-shrink-0 mt-0.5"
              >
                <X className="h-4 w-4" />
              </button>
            </div>

            {!isIOS && deferredPrompt && (
              <div className="flex gap-2 mt-3">
                <Button
                  onClick={handleInstall}
                  size="sm"
                  className="flex-1 gap-2"
                >
                  <Download className="h-4 w-4" />
                  نصب رایگان
                </Button>
                <Button
                  onClick={handleDismiss}
                  size="sm"
                  variant="ghost"
                  className="flex-shrink-0"
                >
                  بعداً
                </Button>
              </div>
            )}
          </div>
        </motion.div>
      )}
    </AnimatePresence>
  );
}
