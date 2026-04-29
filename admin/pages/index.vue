<script setup lang="ts">
import MetricCard from '~/components/dashboard/MetricCard.vue';

definePageMeta({
  middleware: ['auth'],
});

type DashboardMetric = {
  label: string;
  value: number;
  detail: string;
  icon: string;
};

const auth = useAuthStore();
const { request } = useAdminApi();
const { data, pending, refresh } = await useAsyncData('admin-dashboard', () =>
  request<{
    metrics: DashboardMetric[];
  }>('/dashboard'),
  {
    server: false,
  }
);

const metrics = computed(() => data.value?.metrics ?? []);

const quickLinks = [
  {
    label: 'Eventi',
    detail: 'Catalogo, immagini, stato pubblicazione e disponibilita.',
    to: '/events',
    icon: 'i-lucide-calendar-range',
  },
  {
    label: 'Prenotazioni',
    detail: 'Richieste, conferme, waitlist e cancellazioni.',
    to: '/bookings',
    icon: 'i-lucide-ticket',
  },
  {
    label: 'Utenti',
    detail: 'Clienti app e account staff autorizzati.',
    to: '/users',
    icon: 'i-lucide-users',
  },
  {
    label: 'Nuovo evento',
    detail: 'Crea una scheda evento completa per app e backoffice.',
    to: '/events/create',
    icon: 'i-lucide-plus',
  },
];

const systemRows = computed(() => [
  { label: 'Sessione', value: auth.roleLabel, tone: auth.isAdmin ? 'success' : 'neutral' },
  { label: 'Origine dati', value: 'Laravel Admin API', tone: 'neutral' },
  { label: 'UI', value: 'Nuxt container dedicato', tone: 'neutral' },
]);
</script>

<template>
  <div class="grid gap-4">
    <section class="surface rounded-lg p-4">
      <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-stone-500 dark:text-stone-400">
            Dashboard
          </p>
          <h1 class="mt-1 text-xl font-semibold tracking-tight text-stone-950 dark:text-white">
            Console operativa
          </h1>
          <p class="mt-1 text-sm text-stone-600 dark:text-stone-400">
            Accesso rapido ai moduli, metriche essenziali e stato sessione.
          </p>
        </div>

        <div class="flex flex-wrap gap-2">
          <UButton color="neutral" variant="outline" icon="i-lucide-rotate-cw" :loading="pending" @click="refresh()">
            Aggiorna
          </UButton>
          <UButton to="/events/create" icon="i-lucide-plus">
            Nuovo evento
          </UButton>
        </div>
      </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
      <MetricCard
        v-for="metric in metrics"
        :key="metric.label"
        :label="metric.label"
        :value="String(metric.value)"
        :detail="metric.detail"
        :icon="metric.icon"
      />
    </section>

    <section class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_360px]">
      <div class="surface rounded-lg">
        <div class="border-b border-stone-200 px-4 py-3 dark:border-white/8">
          <h2 class="text-sm font-semibold text-stone-950 dark:text-white">
            Aree di lavoro
          </h2>
        </div>

        <div class="divide-y divide-stone-200 dark:divide-white/8">
          <NuxtLink
            v-for="link in quickLinks"
            :key="link.to"
            :to="link.to"
            class="grid gap-3 px-4 py-3 transition hover:bg-stone-50 dark:hover:bg-white/5 md:grid-cols-[36px_minmax(0,1fr)_auto] md:items-center"
          >
            <span class="flex size-9 items-center justify-center rounded-lg bg-ops-50 text-ops-700 dark:bg-ops-500/12 dark:text-ops-100">
              <UIcon :name="link.icon" class="size-4" />
            </span>
            <span class="min-w-0">
              <span class="block text-sm font-semibold text-stone-950 dark:text-white">{{ link.label }}</span>
              <span class="mt-0.5 block text-xs leading-5 text-stone-500 dark:text-stone-400">{{ link.detail }}</span>
            </span>
            <UIcon name="i-lucide-chevron-right" class="hidden size-4 text-stone-400 md:block" />
          </NuxtLink>
        </div>
      </div>

      <aside class="surface rounded-lg">
        <div class="border-b border-stone-200 px-4 py-3 dark:border-white/8">
          <h2 class="text-sm font-semibold text-stone-950 dark:text-white">
            Stato backoffice
          </h2>
        </div>

        <div class="divide-y divide-stone-200 dark:divide-white/8">
          <div
            v-for="row in systemRows"
            :key="row.label"
            class="flex items-center justify-between gap-3 px-4 py-3"
          >
            <span class="text-sm text-stone-600 dark:text-stone-400">{{ row.label }}</span>
            <UBadge :color="row.tone" variant="soft">
              {{ row.value }}
            </UBadge>
          </div>
        </div>
      </aside>
    </section>
  </div>
</template>
