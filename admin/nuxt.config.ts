export default defineNuxtConfig({
  devtools: {
    enabled: true,
  },
  ssr: true,
  css: ['~/assets/css/main.css'],
  modules: ['@nuxt/ui', '@pinia/nuxt'],
  runtimeConfig: {
    adminApiBase: process.env.NUXT_ADMIN_API_BASE || 'http://nginx/api/admin',
    public: {
      adminApiBase: process.env.NUXT_PUBLIC_ADMIN_API_BASE || 'http://localhost:8080/api/admin',
      adminAppName: process.env.NUXT_PUBLIC_ADMIN_APP_NAME || 'Scipioni Staff Console',
    },
  },
  compatibilityDate: '2026-04-26',
  nitro: {
    preset: 'node-server',
  },
  typescript: {
    strict: true,
  },
});
