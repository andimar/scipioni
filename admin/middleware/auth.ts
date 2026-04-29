export default defineNuxtRouteMiddleware(async (to) => {
  if (to.path === '/login') {
    return;
  }

  if (import.meta.server) {
    return;
  }

  const auth = useAuthStore();

  if (!auth.initialized) {
    await auth.bootstrap();
  }

  if (!auth.isAuthenticated) {
    return navigateTo('/login');
  }
});
