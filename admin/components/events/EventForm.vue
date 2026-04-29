<script setup lang="ts">
import type { AdminEvent, AdminEventCategory, AdminEventPayload, AdminFieldErrors } from '~/types/events';

const props = defineProps<{
  title: string;
  subtitle: string;
  submitLabel: string;
  event?: AdminEvent | null;
  categories: AdminEventCategory[];
  loading?: boolean;
  deleting?: boolean;
  errors?: AdminFieldErrors;
}>();

const emit = defineEmits<{
  submit: [payload: AdminEventPayload];
  delete: [];
}>();

function payloadFromEvent(event?: AdminEvent | null): AdminEventPayload {
  return {
    category_id: event?.category_id ?? null,
    title: event?.title ?? '',
    subtitle: event?.subtitle ?? '',
    short_description: event?.short_description ?? '',
    full_description: event?.full_description ?? '',
    cover_image_path: event?.cover_image_path ?? '',
    venue_name: event?.venue_name ?? 'Magazzino Scipioni',
    venue_address: event?.venue_address ?? '',
    starts_at: event?.starts_at ? event.starts_at.slice(0, 16) : '',
    ends_at: event?.ends_at ? event.ends_at.slice(0, 16) : '',
    capacity: event?.capacity ?? 0,
    price: event ? Number(event.price) : 0,
    booking_status: event?.booking_status ?? 'open',
    status: event?.status ?? 'draft',
    requires_approval: event?.requires_approval ?? false,
    is_featured: event?.is_featured ?? false,
  };
}

const form = reactive<AdminEventPayload>(payloadFromEvent(props.event));

watch(
  () => props.event,
  (event) => {
    Object.assign(form, payloadFromEvent(event));
  },
  { immediate: true }
);

const categoryOptions = computed(() => [
  { label: 'Nessuna categoria', value: null },
  ...props.categories.map((category) => ({
    label: category.name,
    value: category.id,
  })),
]);

const statusOptions = [
  { label: 'Bozza', value: 'draft' },
  { label: 'Pubblicato', value: 'published' },
  { label: 'Archiviato', value: 'archived' },
];

const bookingStatusOptions = [
  { label: 'Aperte', value: 'open' },
  { label: 'Chiuse', value: 'closed' },
  { label: 'Lista attesa', value: 'waitlist' },
];

const coverPreviewUrl = computed(() => {
  if (form.cover_image_path && form.cover_image_path !== props.event?.cover_image_path) {
    return form.cover_image_path;
  }

  return props.event?.cover_image_url ?? form.cover_image_path ?? '';
});

function onSubmit() {
  emit('submit', {
    ...form,
    category_id: form.category_id ? Number(form.category_id) : null,
    capacity: Number(form.capacity),
    price: Number(form.price),
  });
}
</script>

<template>
  <form class="grid gap-4" @submit.prevent="onSubmit">
    <section class="surface rounded-lg">
      <div class="flex flex-col gap-3 border-b border-stone-200 px-4 py-3 dark:border-white/8 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
          <div class="flex flex-wrap items-center gap-2">
            <h2 class="truncate text-base font-semibold text-stone-950 dark:text-white">
              {{ title }}
            </h2>
            <UBadge v-if="props.event" color="neutral" variant="soft">
              #{{ props.event.id }}
            </UBadge>
            <UBadge v-if="props.event" color="primary" variant="soft">
              {{ props.event.status }}
            </UBadge>
          </div>
          <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">
            {{ subtitle }}
          </p>
        </div>

        <div class="flex flex-wrap gap-2">
          <UButton to="/events" color="neutral" variant="outline" icon="i-lucide-arrow-left">
            Lista
          </UButton>
          <UButton type="submit" icon="i-lucide-save" :loading="loading">
            {{ submitLabel }}
          </UButton>
          <UButton
            v-if="props.event"
            type="button"
            color="error"
            variant="outline"
            icon="i-lucide-trash-2"
            :loading="deleting"
            @click="emit('delete')"
          >
            Elimina
          </UButton>
        </div>
      </div>

      <UAlert
        v-if="errors?.general"
        color="error"
        variant="soft"
        icon="i-lucide-circle-alert"
        :title="errors.general"
        class="m-4"
      />

      <div class="grid gap-4 p-4 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="grid gap-4">
          <div class="field-panel rounded-lg p-4">
            <div class="mb-3 flex items-center gap-2">
              <UIcon name="i-lucide-file-text" class="size-4 text-ops-600 dark:text-ops-100" />
              <h3 class="text-sm font-semibold text-stone-950 dark:text-white">
                Contenuto
              </h3>
            </div>

            <div class="grid gap-3">
              <UFormField label="Titolo" required :error="errors?.title">
                <UInput v-model="form.title" class="w-full" />
              </UFormField>

              <UFormField label="Sottotitolo" :error="errors?.subtitle">
                <UInput v-model="form.subtitle" class="w-full" />
              </UFormField>

              <div class="grid gap-3 md:grid-cols-2">
                <UFormField label="Categoria" :error="errors?.category_id">
                  <USelect v-model="form.category_id" :items="categoryOptions" class="w-full" />
                </UFormField>

                <UFormField label="Stato contenuto" required :error="errors?.status">
                  <USelect v-model="form.status" :items="statusOptions" class="w-full" />
                </UFormField>
              </div>

              <UFormField label="Abstract breve" :error="errors?.short_description">
                <UTextarea v-model="form.short_description" :rows="3" class="w-full" />
              </UFormField>

              <UFormField label="Descrizione completa" required :error="errors?.full_description">
                <UTextarea v-model="form.full_description" :rows="7" class="w-full" />
              </UFormField>
            </div>
          </div>

          <div class="field-panel rounded-lg p-4">
            <div class="mb-3 flex items-center gap-2">
              <UIcon name="i-lucide-calendar-clock" class="size-4 text-ops-600 dark:text-ops-100" />
              <h3 class="text-sm font-semibold text-stone-950 dark:text-white">
                Pianificazione
              </h3>
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
              <UFormField label="Inizio" required :error="errors?.starts_at">
                <UInput v-model="form.starts_at" type="datetime-local" class="w-full" />
              </UFormField>

              <UFormField label="Fine" :error="errors?.ends_at">
                <UInput v-model="form.ends_at" type="datetime-local" class="w-full" />
              </UFormField>

              <UFormField label="Capienza" required :error="errors?.capacity">
                <UInput v-model="form.capacity" type="number" min="0" class="w-full" />
              </UFormField>

              <UFormField label="Prezzo" required :error="errors?.price">
                <UInput v-model="form.price" type="number" min="0" step="0.01" class="w-full" />
              </UFormField>

              <UFormField label="Stato prenotazioni" required :error="errors?.booking_status" class="md:col-span-2">
                <USelect v-model="form.booking_status" :items="bookingStatusOptions" class="w-full" />
              </UFormField>
            </div>
          </div>

          <div class="field-panel rounded-lg p-4">
            <div class="mb-3 flex items-center gap-2">
              <UIcon name="i-lucide-map-pin" class="size-4 text-ops-600 dark:text-ops-100" />
              <h3 class="text-sm font-semibold text-stone-950 dark:text-white">
                Luogo e opzioni
              </h3>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
              <UFormField label="Location" required :error="errors?.venue_name">
                <UInput v-model="form.venue_name" class="w-full" />
              </UFormField>

              <UFormField label="Indirizzo" :error="errors?.venue_address">
                <UInput v-model="form.venue_address" class="w-full" />
              </UFormField>

              <div class="flex flex-wrap gap-5 md:col-span-2">
                <UCheckbox v-model="form.requires_approval" label="Richiede approvazione staff" />
                <UCheckbox v-model="form.is_featured" label="In evidenza" />
              </div>
            </div>
          </div>
        </div>

        <aside class="grid content-start gap-4">
          <div class="field-panel rounded-lg p-4">
            <div class="mb-3 flex items-center gap-2">
              <UIcon name="i-lucide-image" class="size-4 text-ops-600 dark:text-ops-100" />
              <h3 class="text-sm font-semibold text-stone-950 dark:text-white">
                Cover
              </h3>
            </div>

            <div class="aspect-[4/3] overflow-hidden rounded-md border border-stone-200 bg-stone-100 dark:border-white/8 dark:bg-white/6">
              <img
                v-if="coverPreviewUrl"
                :src="coverPreviewUrl"
                alt="Cover evento"
                class="h-full w-full object-cover"
              >
              <div v-else class="flex h-full flex-col items-center justify-center gap-2 text-stone-500 dark:text-stone-400">
                <UIcon name="i-lucide-image-off" class="size-7" />
                <span class="text-xs font-medium">Nessuna cover</span>
              </div>
            </div>

            <UFormField label="URL o path immagine" :error="errors?.cover_image_path" class="mt-3">
              <UInput
                v-model="form.cover_image_path"
                class="w-full"
                placeholder="https://... oppure /storage/..."
              />
            </UFormField>
          </div>

          <div v-if="props.event" class="field-panel rounded-lg p-4">
            <h3 class="text-sm font-semibold text-stone-950 dark:text-white">
              Stato scheda
            </h3>
            <dl class="mt-3 grid gap-3 text-sm">
              <div>
                <dt class="text-xs text-stone-500 dark:text-stone-400">Slug</dt>
                <dd class="mt-0.5 break-all font-medium text-stone-900 dark:text-stone-100">{{ props.event.slug }}</dd>
              </div>
              <div>
                <dt class="text-xs text-stone-500 dark:text-stone-400">Prenotazioni</dt>
                <dd class="mt-0.5 font-medium text-stone-900 dark:text-stone-100">{{ props.event.bookings_count }}</dd>
              </div>
            </dl>
          </div>
        </aside>
      </div>
    </section>
  </form>
</template>
