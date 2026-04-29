<script setup lang="ts">
import type { AdminEventCategory, AdminEventPayload, AdminFieldErrors } from '~/types/events';
import EventForm from '~/components/events/EventForm.vue';

definePageMeta({
  middleware: ['auth'],
});

const toast = useToast();
const { request } = useAdminApi();
const saving = ref(false);
const errors = ref<AdminFieldErrors>({});

const { data: categoriesData } = await useAsyncData('admin-event-categories', () =>
  request<{ data: AdminEventCategory[] }>('/event-categories')
,
  {
    server: false,
  }
);

async function submit(payload: AdminEventPayload) {
  saving.value = true;
  errors.value = {};

  try {
    const response = await request<{ data: { id: number } }>('/events', {
      method: 'POST',
      body: payload,
    });

    toast.add({
      title: 'Evento creato',
      description: 'La scheda evento e\' stata salvata correttamente.',
      color: 'success',
    });

    await navigateTo(`/events/${response.data.id}`);
  } catch (error: any) {
    const responseErrors = error?.data?.errors as Record<string, string[] | string> | undefined;
    errors.value = responseErrors
      ? Object.fromEntries(
          Object.entries(responseErrors).map(([key, value]) => [key, Array.isArray(value) ? value[0] : value])
        ) as AdminFieldErrors
      : { general: 'Controlla i campi del form e riprova.' };

    toast.add({
      title: 'Salvataggio non riuscito',
      description: 'Controlla i campi del form e riprova.',
      color: 'error',
    });
  } finally {
    saving.value = false;
  }
}
</script>

<template>
  <EventForm
    title="Nuovo evento"
    subtitle="Creazione rapida di un contenuto eventi con i campi indispensabili per staff e app."
    submit-label="Salva evento"
    :categories="categoriesData?.data ?? []"
    :loading="saving"
    :errors="errors"
    @submit="submit"
  />
</template>
