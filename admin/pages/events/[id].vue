<script setup lang="ts">
import type { AdminEvent, AdminEventCategory, AdminEventPayload, AdminFieldErrors } from '~/types/events';
import EventForm from '~/components/events/EventForm.vue';

definePageMeta({
  middleware: ['auth'],
});

const toast = useToast();
const route = useRoute();
const { request } = useAdminApi();
const saving = ref(false);
const deleting = ref(false);
const errors = ref<AdminFieldErrors>({});
const event = ref<AdminEvent | null>(null);
const categories = ref<AdminEventCategory[]>([]);
const eventPending = ref(true);
const eventError = ref('');
const eventId = computed(() => {
  const value = route.params.id;
  return Array.isArray(value) ? value[0] : String(value ?? '');
});

async function loadPage() {
  if (!eventId.value) {
    event.value = null;
    eventError.value = 'Identificativo evento non valido.';
    eventPending.value = false;
    return;
  }

  eventPending.value = true;
  eventError.value = '';

  try {
    const [categoriesResponse, eventResponse] = await Promise.all([
      request<{ data: AdminEventCategory[] }>('/event-categories'),
      request<{ data: AdminEvent }>(`/events/${eventId.value}`),
    ]);

    categories.value = categoriesResponse.data;
    event.value = eventResponse.data;
  } catch (error: any) {
    event.value = null;
    eventError.value = error?.data?.message ?? 'Non sono riuscito a leggere i dati dell\'evento.';
  } finally {
    eventPending.value = false;
  }
}

watch(eventId, () => {
  loadPage();
}, { immediate: true });

async function submit(payload: AdminEventPayload) {
  saving.value = true;
  errors.value = {};

  try {
    await request(`/events/${eventId.value}`, {
      method: 'PUT',
      body: payload,
    });

    toast.add({
      title: 'Evento aggiornato',
      description: 'Le modifiche sono state salvate correttamente.',
      color: 'success',
    });

    await loadPage();
  } catch (error: any) {
    const responseErrors = error?.data?.errors as Record<string, string[] | string> | undefined;
    errors.value = responseErrors
      ? Object.fromEntries(
          Object.entries(responseErrors).map(([key, value]) => [key, Array.isArray(value) ? value[0] : value])
        ) as AdminFieldErrors
      : { general: 'Controlla i campi del form e riprova.' };

    toast.add({
      title: 'Aggiornamento non riuscito',
      description: 'Controlla i campi del form e riprova.',
      color: 'error',
    });
  } finally {
    saving.value = false;
  }
}

async function deleteEvent() {
  if (!event.value) {
    return;
  }

  if (!window.confirm(`Eliminare definitivamente "${event.value.title}"?`)) {
    return;
  }

  deleting.value = true;
  errors.value = {};

  try {
    await request(`/events/${event.value.id}`, {
      method: 'DELETE',
    });

    toast.add({
      title: 'Evento eliminato',
      description: 'La scheda evento e\' stata rimossa.',
      color: 'success',
    });

    await navigateTo('/events');
  } catch (error: any) {
    const responseErrors = error?.data?.errors as Record<string, string[] | string> | undefined;
    errors.value = responseErrors
      ? Object.fromEntries(
          Object.entries(responseErrors).map(([key, value]) => [key, Array.isArray(value) ? value[0] : value])
        ) as AdminFieldErrors
      : { general: error?.data?.message ?? 'Eliminazione non riuscita.' };

    toast.add({
      title: 'Eliminazione non riuscita',
      description: errors.value.general ?? 'Controlla lo stato dell\'evento e riprova.',
      color: 'error',
    });
  } finally {
    deleting.value = false;
  }
}
</script>

<template>
  <div class="grid gap-4">
    <div v-if="eventPending" class="grid gap-4">
      <section class="surface rounded-lg p-4">
        <div class="h-4 w-28 animate-pulse rounded bg-stone-200 dark:bg-white/10" />
        <div class="mt-3 h-7 w-64 animate-pulse rounded bg-stone-200 dark:bg-white/10" />
        <div class="mt-3 h-4 w-full max-w-xl animate-pulse rounded bg-stone-100 dark:bg-white/8" />
      </section>

      <section class="surface rounded-lg p-4">
        <div class="grid gap-3 lg:grid-cols-2">
          <div v-for="index in 8" :key="index" class="h-12 animate-pulse rounded-md bg-stone-100 dark:bg-white/8" />
        </div>
      </section>
    </div>

    <section
      v-else-if="eventError || !event"
      class="rounded-lg border border-red-200 bg-red-50/80 p-4 dark:border-red-400/20 dark:bg-red-400/10"
    >
      <div class="flex items-start gap-4">
        <span class="flex size-10 items-center justify-center rounded-lg bg-red-100 text-red-600 dark:bg-red-400/15 dark:text-red-200">
          <UIcon name="i-lucide-circle-alert" class="size-5" />
        </span>
        <div>
          <h1 class="text-base font-semibold text-red-900 dark:text-red-100">
            Scheda evento non caricata
          </h1>
          <p class="mt-2 text-sm leading-6 text-red-700 dark:text-red-200">
            {{ eventError || "Non sono riuscito a leggere i dati dell'evento. Riprova il caricamento o torna alla lista eventi." }}
          </p>
          <div class="mt-4 flex flex-wrap gap-3">
            <UButton color="error" variant="solid" icon="i-lucide-rotate-cw" @click="loadPage()">
              Riprova
            </UButton>
            <UButton to="/events" color="neutral" variant="outline">
              Torna alla lista
            </UButton>
          </div>
        </div>
      </div>
    </section>

    <EventForm
      v-else
      :key="event.id"
      :event="event"
      title="Scheda evento"
      subtitle="Qui il team puo aggiornare copy, calendario, cover e disponibilita dell'evento."
      submit-label="Aggiorna evento"
      :categories="categories"
      :loading="saving"
      :deleting="deleting"
      :errors="errors"
      @submit="submit"
      @delete="deleteEvent"
    />
  </div>
</template>
