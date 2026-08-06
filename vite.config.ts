import path from "path";
import react from "@vitejs/plugin-react-swc";
import { defineConfig } from "vite";
import { VitePWA } from "vite-plugin-pwa";
import { componentTagger } from "lovable-tagger";

/**
 * Legacy frontend snapshot.
 *
 * The production Winimi frontend is maintained in `sajadkhavas/cooci`.
 * This build is retained temporarily while custom Filament/build dependencies
 * are audited. It must never cache authenticated, checkout or payment APIs.
 */
export default defineConfig(({ mode }) => ({
  server: {
    host: "::",
    port: 8080,
  },
  plugins: [
    react(),
    mode === "development" && componentTagger(),
    VitePWA({
      registerType: "autoUpdate",
      includeAssets: ["favicon.ico", "robots.txt", "icons/**/*"],
      manifest: {
        name: "وینیمی بیکری — نسخه قدیمی فرانت",
        short_name: "وینیمی",
        description:
          "نسخه قدیمی نگهداری‌شده فقط برای بررسی وابستگی؛ فرانت اصلی در مخزن cooci قرار دارد.",
        theme_color: "#204f3d",
        background_color: "#f8f2e8",
        display: "standalone",
        scope: "/",
        start_url: "/",
        lang: "fa-IR",
        dir: "rtl",
        icons: [
          {
            src: "/icons/icon-192x192.png",
            sizes: "192x192",
            type: "image/png",
            purpose: "any",
          },
          {
            src: "/icons/icon-512x512.png",
            sizes: "512x512",
            type: "image/png",
            purpose: "any",
          },
        ],
      },
      workbox: {
        globPatterns: ["**/*.{js,css,html,ico,png,jpg,jpeg,svg,woff,woff2}"],
        navigateFallback: "/index.html",
        navigateFallbackDenylist: [/^\/api\//, /^\/admin(?:\/|$)/],
        runtimeCaching: [],
        cleanupOutdatedCaches: true,
        skipWaiting: true,
        clientsClaim: true,
      },
      devOptions: { enabled: false },
    }),
  ].filter(Boolean),
  resolve: {
    alias: { "@": path.resolve(__dirname, "./src") },
  },
}));
