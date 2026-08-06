import { useEffect, useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { RefreshCw, X } from "lucide-react";
import { Button } from "@/components/ui/button";

export function PWAUpdateNotification() {
  const [needRefresh, setNeedRefresh] = useState(false);

  useEffect(() => {
    // Listen for service worker updates
    if ("serviceWorker" in navigator) {
      navigator.serviceWorker.ready.then((registration) => {
        registration.addEventListener("updatefound", () => {
          const newWorker = registration.installing;
          if (newWorker) {
            newWorker.addEventListener("statechange", () => {
              if (
                newWorker.state === "installed" &&
                navigator.serviceWorker.controller
              ) {
                setNeedRefresh(true);
              }
            });
          }
        });
      });
    }
  }, []);

  const handleUpdate = () => {
    if ("serviceWorker" in navigator) {
      navigator.serviceWorker.ready.then((registration) => {
        registration.waiting?.postMessage({ type: "SKIP_WAITING" });
        window.location.reload();
      });
    }
  };

  const close = () => setNeedRefresh(false);

  return (
    <AnimatePresence>
      {needRefresh && (
        <motion.div
          initial={{ y: -80, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          exit={{ y: -80, opacity: 0 }}
          transition={{ type: "spring", damping: 20 }}
          className="fixed top-4 left-4 right-4 z-50 md:left-auto md:right-4 md:w-80"
          dir="rtl"
        >
          <div className="bg-primary text-primary-foreground rounded-2xl shadow-2xl p-4">
            <div className="flex items-start gap-3">
              <div className="bg-white/20 rounded-xl p-2 flex-shrink-0">
                <RefreshCw className="h-5 w-5" />
              </div>
              <div className="flex-1">
                <p className="font-bold text-sm mb-1">نسخه جدید آماده است</p>
                <p className="text-primary-foreground/80 text-xs">
                  برای استفاده از آخرین تغییرات، اپ را بارگذاری مجدد کنید.
                </p>
              </div>
              <button onClick={close} className="text-white/70 hover:text-white">
                <X className="h-4 w-4" />
              </button>
            </div>
            <Button
              onClick={handleUpdate}
              size="sm"
              className="w-full mt-3 bg-white text-primary hover:bg-white/90 gap-2"
            >
              <RefreshCw className="h-4 w-4" />
              بارگذاری مجدد
            </Button>
          </div>
        </motion.div>
      )}
    </AnimatePresence>
  );
}
