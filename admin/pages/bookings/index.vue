<script setup lang="ts">
definePageMeta({
  middleware: ['auth'],
});

type AdminBooking = {
  id: number;
  status: string;
  seats_reserved: number;
  customer_notes: string | null;
  internal_notes: string | null;
  confirmed_at: string | null;
  created_at: string;
  event: {
    id: number;
    title: string;
    slug: string;
    cover_image_url: string | null;
    starts_at: string | null;
    category: {
      id: number;
      name: string;
    } | null;
  } | null;
  user: {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    phone: string | null;
  } | null;
};

const route = useRoute();
const router = useRouter();
const toast = useToast();
const { request } = useAdminApi();
const status = ref(typeof route.query.status === 'string' ? route.query.status : '');
const page = ref(Number(typeof route.query.page === 'string' ? route.query.page : '1') || 1);
const updatingBookingId = ref<number | null>(null);

const { data, pending, refresh } = await useAsyncData('admin-bookings', () =>
  request<{
    data: AdminBooking[];
    meta: {
      total: number;
      current_page: number;
      last_page: number;
      from: number | null;
      to: number | null;
    };
  }>('/bookings', {
    query: {
      status: status.value || undefined,
      page: page.value,
    },
  }),
  {
    server: false,
    watch: [status, page],
  }
);

const statusOptions = [
  { label: 'Tutti gli stati', value: '' },
  { label: 'Confirmed', value: 'confirmed' },
  { label: 'Requested', value: 'requested' },
  { label: 'Waitlist', value: 'waitlist' },
  { label: 'Cancelled', value: 'cancelled' },
];

const bookings = computed(() => data.value?.data ?? []);

const summary = computed(() => {
  const list = bookings.value;

  return {
    total: data.value?.meta.total ?? 0,
    confirmed: list.filter((booking) => booking.status === 'confirmed').length,
    requested: list.filter((booking) => booking.status === 'requested').length,
    seats: list.reduce((sum, booking) => sum + booking.seats_reserved, 0),
  };
});

async function applyFilters() {
  page.value = 1;
  await router.replace({
    query: {
      ...route.query,
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
      status: status.value || undefined,
      page: nextPage > 1 ? String(nextPage) : undefined,
    },
  });
}

function statusColor(value: string) {
  if (value === 'confirmed') return 'success';
  if (value === 'requested') return 'warning';
  if (value === 'waitlist') return 'neutral';
  return 'error';
}

function formatDate(value: string | null) {
  return value ? new Date(value).toLocaleString('it-IT', { dateStyle: 'short', timeStyle: 'short' }) : 'N/D';
}

async function updateStatus(bookingId: number, nextStatus: string, internalNotes?: string | null) {
  updatingBookingId.value = bookingId;

  try {
    await request(`/bookings/${bookingId}`, {
      method: 'PATCH',
      body: {
        status: nextStatus,
        internal_notes: internalNotes ?? null,
      },
    });

    toast.add({
      title: 'Prenotazione aggiornata',
      description: 'Lo stato della prenotazione e\' stato aggiornato.',
      color: 'success',
    });

    await refresh();
  } catch {
    toast.add({
      title: 'Aggiornamento non riuscito',
      description: 'Non sono riuscito ad aggiornare lo stato della prenotazione.',
      color: 'error',
    });
  } finally {
    updatingBookingId.value = null;
  }
}
</script>

<template>
  <div class="grid gap-4">
    <section class="surface rounded-lg p-4">
      <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-stone-500 dark:text-stone-400">
            Operazioni
          </p>
          <h1 class="mt-1 text-xl font-semibold tracking-tight text-stone-950 dark:text-white">
            Prenotazioni
          </h1>
          <p class="mt-1 text-sm text-stone-600 dark:text-stone-400">
            Richieste, conferme, waitlist e cancellazioni con azioni rapide.
          </p>
        </div>

        <UButton color="neutral" variant="outline" icon="i-lucide-rotate-cw" :loading="pending" @click="refresh()">
          Aggiorna
        </UButton>
      </div>

    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
      <div class="surface rounded-lg p-4">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-stone-500">Totale</p>
        <p class="mt-2 text-2xl font-semibold text-stone-950 dark:text-white">{{ summary.total }}</p>
      </div>
      <div class="surface rounded-lg p-4">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-stone-500">Confirmed</p>
        <p class="mt-2 text-2xl font-semibold text-stone-950 dark:text-white">{{ summary.confirmed }}</p>
      </div>
      <div class="surface rounded-lg p-4">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-stone-500">Requested</p>
        <p class="mt-2 text-2xl font-semibold text-stone-950 dark:text-white">{{ summary.requested }}</p>
      </div>
      <div class="surface rounded-lg p-4">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-stone-500">Posti pagina</p>
        <p class="mt-2 text-2xl font-semibold text-stone-950 dark:text-white">{{ summary.seats }}</p>
      </div>
    </section>

    <section class="surface rounded-lg p-3">
      <div class="grid gap-3 sm:grid-cols-[220px_auto]">
        <USelect v-model="status" :items="statusOptions" />
        <UButton icon="i-lucide-sliders-horizontal" @click="applyFilters">
          Applica filtro
        </UButton>
      </div>
    </section>

    <section class="surface overflow-hidden rounded-lg">
      <div class="flex items-center justify-between gap-3 border-b border-stone-200 px-4 py-3 dark:border-white/8">
        <h2 class="text-sm font-semibold text-stone-950 dark:text-white">
          Elenco prenotazioni
        </h2>
        <UBadge color="neutral" variant="soft">
          {{ data?.meta.total ?? 0 }} record
        </UBadge>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-stone-200 text-sm dark:divide-white/8">
          <thead class="bg-stone-50 text-left text-[11px] uppercase tracking-wide text-stone-500 dark:bg-white/4 dark:text-stone-400">
            <tr>
              <th class="px-4 py-2 font-semibold">Cliente</th>
              <th class="px-4 py-2 font-semibold">Evento</th>
              <th class="px-4 py-2 font-semibold">Data</th>
              <th class="px-4 py-2 font-semibold">Stato</th>
              <th class="px-4 py-2 text-right font-semibold">Posti</th>
              <th class="px-4 py-2 text-right font-semibold">Azioni</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone-200 dark:divide-white/8">
            <tr v-for="booking in bookings" :key="booking.id" class="hover:bg-stone-50 dark:hover:bg-white/4">
              <td class="min-w-[240px] px-4 py-3">
                <p class="font-semibold text-stone-950 dark:text-white">
                  {{ booking.user ? `${booking.user.first_name} ${booking.user.last_name}` : 'Utente rimosso' }}
                </p>
                <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">
                  {{ booking.user?.email ?? 'N/D' }}
                </p>
              </td>
              <td class="min-w-[320px] px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="h-12 w-16 shrink-0 overflow-hidden rounded-md border border-stone-200 bg-stone-100 dark:border-white/8 dark:bg-white/6">
                    <img
                      v-if="booking.event?.cover_image_url"
                      :src="booking.event.cover_image_url"
                      alt="Cover evento"
                      class="h-full w-full object-cover"
                    >
                    <div v-else class="flex h-full items-center justify-center text-stone-400">
                      <UIcon name="i-lucide-image" class="size-4" />
                    </div>
                  </div>
                  <div class="min-w-0">
                    <p class="truncate font-medium text-stone-900 dark:text-stone-100">
                      {{ booking.event?.title ?? 'Evento rimosso' }}
                    </p>
                    <p class="mt-0.5 truncate text-xs text-stone-500 dark:text-stone-400">
                      {{ booking.event?.category?.name ?? 'Senza categoria' }}
                    </p>
                  </div>
                </div>
              </td>
              <td class="whitespace-nowrap px-4 py-3 text-stone-600 dark:text-stone-300">
                {{ formatDate(booking.event?.starts_at ?? null) }}
              </td>
              <td class="whitespace-nowrap px-4 py-3">
                <UBadge :color="statusColor(booking.status)" variant="soft">
                  {{ booking.status }}
                </UBadge>
              </td>
              <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-stone-900 dark:text-stone-100">
                {{ booking.seats_reserved }}
              </td>
              <td class="whitespace-nowrap px-4 py-3">
                <div class="flex justify-end gap-2">
                  <UButton size="sm" color="success" variant="outline" :loading="updatingBookingId === booking.id" @click="updateStatus(booking.id, 'confirmed', booking.internal_notes)">
                    Conferma
                  </UButton>
                  <UButton size="sm" color="warning" variant="outline" :loading="updatingBookingId === booking.id" @click="updateStatus(booking.id, 'waitlist', booking.internal_notes)">
                    Waitlist
                  </UButton>
                  <UButton size="sm" color="error" variant="outline" :loading="updatingBookingId === booking.id" @click="updateStatus(booking.id, 'cancelled', booking.internal_notes)">
                    Annulla
                  </UButton>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="!pending && bookings.length === 0" class="px-4 py-10 text-center">
        <p class="text-sm font-semibold text-stone-900 dark:text-white">Nessuna prenotazione trovata</p>
        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Cambia filtro o attendi nuove richieste.</p>
      </div>

      <div v-if="data?.meta.last_page && data.meta.last_page > 1" class="flex flex-col gap-3 border-t border-stone-200 px-4 py-3 dark:border-white/8 md:flex-row md:items-center md:justify-between">
        <p class="text-sm text-stone-500 dark:text-stone-400">
          {{ data.meta.from ?? 0 }}-{{ data.meta.to ?? 0 }} di {{ data.meta.total }} prenotazioni
        </p>
        <div class="flex gap-2">
          <UButton
            color="neutral"
            variant="outline"
            :disabled="data.meta.current_page <= 1"
            @click="changePage(data.meta.current_page - 1)"
          >
            Precedente
          </UButton>
          <UButton
            color="neutral"
            variant="outline"
            :disabled="data.meta.current_page >= data.meta.last_page"
            @click="changePage(data.meta.current_page + 1)"
          >
            Successiva
          </UButton>
        </div>
      </div>
    </section>
  </div>
</template>
