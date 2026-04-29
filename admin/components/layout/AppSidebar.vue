<script setup lang="ts">
const route = useRoute();
const auth = useAuthStore();

defineProps<{
  mobile?: boolean;
}>();

const emit = defineEmits<{
  close: [];
}>();

const sections = [
  {
    label: 'Console',
    items: [
      { label: 'Dashboard', to: '/', icon: 'i-lucide-layout-dashboard' },
      { label: 'Eventi', to: '/events', icon: 'i-lucide-calendar-range' },
      { label: 'Prenotazioni', to: '/bookings', icon: 'i-lucide-ticket' },
      { label: 'Utenti', to: '/users', icon: 'i-lucide-users' },
    ],
  },
  {
    label: 'Azioni',
    items: [
      { label: 'Nuovo evento', to: '/events/create', icon: 'i-lucide-plus' },
    ],
  },
];

function isActive(path: string) {
  return route.path === path || (path !== '/' && route.path.startsWith(`${path}/`));
}

function closeSidebar() {
  emit('close');
}
</script>

<template>
  <aside class="admin-sidebar flex h-full flex-col">
    <div class="flex h-16 items-center justify-between border-b border-stone-200 px-4 dark:border-white/8">
      <NuxtLink to="/" class="flex min-w-0 items-center gap-3" @click="closeSidebar">
        <img src="/brand/logo.svg" alt="Magazzino Scipioni" class="h-8 w-auto max-w-[150px] dark:brightness-0 dark:invert">
      </NuxtLink>

      <UButton
        v-if="mobile"
        color="neutral"
        variant="ghost"
        icon="i-lucide-x"
        class="lg:hidden"
        @click="closeSidebar"
      />
    </div>

    <div class="border-b border-stone-200 px-4 py-3 dark:border-white/8">
      <p class="text-xs font-medium uppercase tracking-wide text-stone-500 dark:text-stone-400">
        Staff Console
      </p>
      <p class="mt-1 truncate text-sm font-semibold text-stone-950 dark:text-white">
        {{ auth.userName }}
      </p>
      <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">
        {{ auth.roleLabel }}
      </p>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4">
      <section v-for="section in sections" :key="section.label" class="mb-5">
        <p class="px-2 text-[11px] font-semibold uppercase tracking-wide text-stone-500 dark:text-stone-500">
          {{ section.label }}
        </p>

        <div class="mt-2 space-y-1">
          <NuxtLink
            v-for="item in section.items"
            :key="item.to"
            :to="item.to"
            class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition"
            :class="isActive(item.to)
              ? 'bg-ops-50 text-ops-700 ring-1 ring-ops-100 dark:bg-ops-500/12 dark:text-ops-100 dark:ring-ops-500/20'
              : 'text-stone-600 hover:bg-stone-100 hover:text-stone-950 dark:text-stone-300 dark:hover:bg-white/6 dark:hover:text-white'"
            @click="closeSidebar"
          >
            <UIcon :name="item.icon" class="size-4 shrink-0" />
            <span class="truncate">{{ item.label }}</span>
          </NuxtLink>
        </div>
      </section>
    </nav>

    <div class="border-t border-stone-200 px-4 py-3 text-xs text-stone-500 dark:border-white/8 dark:text-stone-400">
      Laravel API + Nuxt Admin
    </div>
  </aside>
</template>
