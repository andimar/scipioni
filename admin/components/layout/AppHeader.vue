<script setup lang="ts">
const auth = useAuthStore();
const route = useRoute();
const colorMode = useColorMode();
const { toggleSidebar } = useAdminShell();

const titles: Record<string, { title: string; section: string }> = {
  '/': { title: 'Dashboard', section: 'Console' },
  '/events': { title: 'Eventi', section: 'Catalogo' },
  '/events/create': { title: 'Nuovo evento', section: 'Catalogo' },
  '/bookings': { title: 'Prenotazioni', section: 'Operazioni' },
  '/users': { title: 'Utenti', section: 'Amministrazione' },
};

const page = computed(() => {
  if (route.path.startsWith('/events/') && route.path !== '/events/create') {
    return { title: 'Scheda evento', section: 'Catalogo' };
  }

  return titles[route.path] ?? { title: 'Backoffice', section: 'Staff' };
});

const isDark = computed(() => colorMode.value === 'dark');

function toggleTheme() {
  colorMode.preference = isDark.value ? 'light' : 'dark';
}

async function logout() {
  await auth.logout();
  await navigateTo('/login');
}
</script>

<template>
  <header class="admin-header sticky top-0 z-20 border-b px-4 py-3 backdrop-blur sm:px-5">
    <div class="flex items-center justify-between gap-4">
      <div class="flex min-w-0 items-center gap-3">
        <UButton color="neutral" variant="outline" icon="i-lucide-panel-left" class="lg:hidden" @click="toggleSidebar">
          Menu
        </UButton>

        <div class="min-w-0">
          <p class="text-xs font-medium text-stone-500 dark:text-stone-400">
            {{ page.section }}
          </p>
          <h1 class="truncate text-lg font-semibold text-stone-950 dark:text-white">
            {{ page.title }}
          </h1>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <UButton
          color="neutral"
          variant="outline"
          :icon="isDark ? 'i-lucide-sun-medium' : 'i-lucide-moon'"
          @click="toggleTheme"
        />

        <div class="hidden items-center gap-2 rounded-lg border border-stone-200 bg-white px-2.5 py-1.5 dark:border-white/8 dark:bg-white/5 md:flex">
          <span class="flex size-7 items-center justify-center rounded-md bg-stone-900 text-xs font-semibold text-white dark:bg-white dark:text-stone-950">
            {{ auth.userName.slice(0, 1).toUpperCase() }}
          </span>
          <div class="min-w-0">
            <p class="truncate text-xs font-semibold text-stone-950 dark:text-white">
              {{ auth.userName }}
            </p>
            <p class="truncate text-[11px] text-stone-500 dark:text-stone-400">
              {{ auth.roleLabel }}
            </p>
          </div>
        </div>

        <UButton color="neutral" variant="outline" icon="i-lucide-log-out" @click="logout">
          Esci
        </UButton>
      </div>
    </div>
  </header>
</template>
