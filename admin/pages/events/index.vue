<script setup lang="ts">
definePageMeta({
  middleware: ['auth'],
});

type AdminEvent = {
  id: number;
  title: string;
  slug: string;
  subtitle: string | null;
  cover_image_path: string | null;
  cover_image_url: string | null;
  starts_at: string | null;
  ends_at: string | null;
  price: string;
  status: string;
  booking_status: string;
  bookings_count: number;
  category: {
    id: number;
    name: string;
    slug: string;
  } | null;
};

const route = useRoute();
const router = useRouter();
const toast = useToast();
const { request } = useAdminApi();
const search = ref(typeof route.query.search === 'string' ? route.query.search : '');
const status = ref(typeof route.query.status === 'string' ? route.query.status : '');
const page = ref(Number(typeof route.query.page === 'string' ? route.query.page : '1') || 1);
const deletingEventId = ref<number | null>(null);

const { data, pending, refresh } = await useAsyncData('admin-events', () =>
  request<{
    data: AdminEvent[];
    meta: {
      total: number;
      current_page: number;
      last_page: number;
      from: number | null;
      to: number | null;
    };
  }>('/events', {
    query: {
      search: search.value || undefined,
      status: status.value || undefined,
      page: page.value,
    },
  }),
  {
    server: false,
    watch: [search, status, page],
  }
);

const statusOptions = [
  { label: 'Tutti gli stati', value: '' },
  { label: 'Published', value: 'published' },
  { label: 'Draft', value: 'draft' },
  { label: 'Archived', value: 'archived' },
];

const events = computed(() => data.value?.data ?? []);

const summary = computed(() => {
  const list = events.value;

  return {
    total: data.value?.meta.total ?? 0,
    published: list.filter((event) => event.status === 'published').length,
    draft: list.filter((event) => event.status === 'draft').length,
    bookings: list.reduce((sum, event) => sum + event.bookings_count, 0),
  };
});

async function applyFilters() {
  page.value = 1;
  await router.replace({
    query: {
      ...route.query,
      search: search.value || undefined,
      status: status.value || undefined,
      page: undefined,
    },
  });

  await refresh();
}

async function changePage(nextPage: number) {
  page.value = nextPage;
  await router.replace({
    query: {
      ...route.query,
      search: search.value || undefined,
      status: status.value || undefined,
      page: nextPage > 1 ? String(nextPage) : undefined,
    },
  });
}

function statusColor(value: string) {
  if (value === 'published') return 'success';
  if (value === 'draft') return 'warning';
  return 'neutral';
}

function bookingStatusLabel(value: string) {
  if (value === 'open') return 'Aperte';
  if (value === 'closed') return 'Chiuse';
  return value || 'N/D';
}

function formatDate(value: string | null) {
  return value ? new Date(value).toLocaleString('it-IT', { dateStyle: 'short', timeStyle: 'short' }) : 'N/D';
}

async function deleteEvent(event: AdminEvent) {
  if (!window.confirm(`Eliminare definitivamente "${event.title}"?`)) {
    return;
  }

  deletingEventId.value = event.id;

  try {
    await request(`/events/${event.id}`, {
      method: 'DELETE',
    });

    toast.add({
      title: 'Evento eliminato',
      description: 'La lista e\' stata aggiornata.',
      color: 'success',
    });

    await refresh();
  } catch (error: any) {
    const message = error?.data?.errors?.event?.[0]
      ?? error?.data?.message
      ?? 'Eliminazione non riuscita.';

    toast.add({
      title: 'Eliminazione non riuscita',
      description: message,
      color: 'error',
    });
  } finally {
    deletingEventId.value = null;
  }
}
</script>

<template>
  <div class="grid gap-4">
    <section class="surface rounded-lg p-4">
      <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-stone-500 dark:text-stone-400">
            Catalogo
          </p>
          <h1 class="mt-1 text-xl font-semibold tracking-tight text-stone-950 dark:text-white">
            Eventi
          </h1>
          <p class="mt-1 text-sm text-stone-600 dark:text-stone-400">
            Gestione contenuti, stato pubblicazione, immagini e prenotazioni.
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
      <div class="surface rounded-lg p-4">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-stone-500">Totale</p>
        <p class="mt-2 text-2xl font-semibold text-stone-950 dark:text-white">{{ summary.total }}</p>
      </div>
      <div class="surface rounded-lg p-4">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-stone-500">Published</p>
        <p class="mt-2 text-2xl font-semibold text-stone-950 dark:text-white">{{ summary.published }}</p>
      </div>
      <div class="surface rounded-lg p-4">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-stone-500">Draft</p>
        <p class="mt-2 text-2xl font-semibold text-stone-950 dark:text-white">{{ summary.draft }}</p>
      </div>
      <div class="surface rounded-lg p-4">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-stone-500">Prenotazioni pagina</p>
        <p class="mt-2 text-2xl font-semibold text-stone-950 dark:text-white">{{ summary.bookings }}</p>
      </div>
    </section>

    <section class="surface rounded-lg p-3">
      <div class="grid gap-3 xl:grid-cols-[minmax(0,1fr)_220px_auto]">
        <UInput v-model="search" icon="i-lucide-search" placeholder="Cerca titolo o sottotitolo" />
        <USelect v-model="status" :items="statusOptions" />
        <UButton icon="i-lucide-sliders-horizontal" @click="applyFilters">
          Applica filtri
        </UButton>
      </div>
    </section>

    <section class="surface overflow-hidden rounded-lg">
      <div class="flex items-center justify-between gap-3 border-b border-stone-200 px-4 py-3 dark:border-white/8">
        <h2 class="text-sm font-semibold text-stone-950 dark:text-white">
          Elenco eventi
        </h2>
        <UBadge color="neutral" variant="soft">
          {{ data?.meta.total ?? 0 }} record
        </UBadge>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-stone-200 text-sm dark:divide-white/8">
          <thead class="bg-stone-50 text-left text-[11px] uppercase tracking-wide text-stone-500 dark:bg-white/4 dark:text-stone-400">
            <tr>
              <th class="px-4 py-2 font-semibold">Evento</th>
              <th class="px-4 py-2 font-semibold">Categoria</th>
              <th class="px-4 py-2 font-semibold">Data</th>
              <th class="px-4 py-2 font-semibold">Stato</th>
              <th class="px-4 py-2 text-right font-semibold">Pren.</th>
              <th class="px-4 py-2 text-right font-semibold">Azioni</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone-200 dark:divide-white/8">
            <tr v-for="event in events" :key="event.id" class="hover:bg-stone-50 dark:hover:bg-white/4">
              <td class="min-w-[340px] px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="h-12 w-16 shrink-0 overflow-hidden rounded-md border border-stone-200 bg-stone-100 dark:border-white/8 dark:bg-white/6">
                    <img
                      v-if="event.cover_image_url"
                      :src="event.cover_image_url"
                      alt="Cover evento"
                      class="h-full w-full object-cover"
                    >
                    <div v-else class="flex h-full items-center justify-center text-stone-400">
                      <UIcon name="i-lucide-image" class="size-4" />
                    </div>
                  </div>
                  <div class="min-w-0">
                    <NuxtLink :to="`/events/${event.id}`" class="font-semibold text-stone-950 hover:text-ops-700 dark:text-white dark:hover:text-ops-100">
                      {{ event.title }}
                    </NuxtLink>
                    <p class="mt-0.5 truncate text-xs text-stone-500 dark:text-stone-400">
                      {{ event.subtitle || event.slug }}
                    </p>
                  </div>
                </div>
              </td>
              <td class="whitespace-nowrap px-4 py-3 text-stone-600 dark:text-stone-300">
                {{ event.category?.name ?? 'Senza categoria' }}
              </td>
              <td class="whitespace-nowrap px-4 py-3 text-stone-600 dark:text-stone-300">
                {{ formatDate(event.starts_at) }}
              </td>
              <td class="whitespace-nowrap px-4 py-3">
                <div class="flex flex-wrap gap-2">
                  <UBadge :color="statusColor(event.status)" variant="soft">
                    {{ event.status }}
                  </UBadge>
                  <UBadge color="neutral" variant="outline">
                    {{ bookingStatusLabel(event.booking_status) }}
                  </UBadge>
                </div>
              </td>
              <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-stone-900 dark:text-stone-100">
                {{ event.bookings_count }}
              </td>
              <td class="whitespace-nowrap px-4 py-3">
                <div class="flex justify-end gap-2">
                  <UButton size="sm" color="neutral" variant="outline" :to="`/events/${event.id}`">
                    Apri
                  </UButton>
                  <UButton
                    size="sm"
                    color="error"
                    variant="outline"
                    icon="i-lucide-trash-2"
                    aria-label="Elimina evento"
                    :loading="deletingEventId === event.id"
                    @click="deleteEvent(event)"
                  />
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="!pending && events.length === 0" class="px-4 py-10 text-center">
        <p class="text-sm font-semibold text-stone-900 dark:text-white">Nessun evento trovato</p>
        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Cambia filtri o crea un nuovo evento.</p>
      </div>

      <div v-if="data?.meta.last_page && data.meta.last_page > 1" class="flex flex-col gap-3 border-t border-stone-200 px-4 py-3 dark:border-white/8 md:flex-row md:items-center md:justify-between">
        <p class="text-sm text-stone-500 dark:text-stone-400">
          {{ data.meta.from ?? 0 }}-{{ data.meta.to ?? 0 }} di {{ data.meta.total }} eventi
        </p>
        <div class="flex gap-2">
          <UButton color="neutral" variant="outline" :disabled="data.meta.current_page <= 1" @click="changePage(data.meta.current_page - 1)">
            Precedente
          </UButton>
          <UButton color="neutral" variant="outline" :disabled="data.meta.current_page >= data.meta.last_page" @click="changePage(data.meta.current_page + 1)">
            Successiva
          </UButton>
        </div>
      </div>
    </section>
  </div>
</template>
