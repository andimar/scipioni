<script setup lang="ts">
const route = useRoute();

const items = [
  { label: 'Dashboard', to: '/', icon: 'i-lucide-layout-dashboard' },
  { label: 'Eventi', to: '/events', icon: 'i-lucide-calendar-range' },
  { label: 'Prenotazioni', to: '/bookings', icon: 'i-lucide-ticket' },
  { label: 'Utenti', to: '/users', icon: 'i-lucide-users' },
];

function isActive(path: string) {
  return route.path === path || (path !== '/' && route.path.startsWith(`${path}/`));
}
</script>

<template>
  <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-stone-200 bg-white/96 px-2 py-2 shadow-[0_-8px_24px_rgba(15,23,42,0.10)] backdrop-blur dark:border-white/8 dark:bg-stone-950/92 lg:hidden">
    <div class="mx-auto grid max-w-7xl grid-cols-4 gap-2">
      <NuxtLink
        v-for="item in items"
        :key="item.to"
        :to="item.to"
        class="flex flex-col items-center justify-center gap-1 rounded-lg px-2 py-2 text-center transition"
        :class="isActive(item.to)
          ? 'bg-ops-600 text-white shadow-sm'
          : 'text-stone-600 hover:bg-stone-100 dark:text-stone-300 dark:hover:bg-white/6'"
      >
        <UIcon :name="item.icon" class="size-5" />
        <span class="text-[11px] font-semibold tracking-[0.08em]">{{ item.label }}</span>
      </NuxtLink>
    </div>
  </nav>
</template>
