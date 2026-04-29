<script setup lang="ts">
definePageMeta({
  layout: 'auth',
});

const auth = useAuthStore();
const error = ref('');
const form = reactive({
  email: 'admin@magazzinoscipioni.it',
  password: '',
});

if (import.meta.client) {
  if (!auth.initialized) {
    await auth.bootstrap();
  }

  if (auth.isAuthenticated) {
    await navigateTo('/');
  }
}

async function submit() {
  error.value = '';

  try {
    await auth.login(form);
    await navigateTo('/events');
  } catch (err: any) {
    error.value = err?.data?.errors?.email?.[0]
      ?? err?.data?.message
      ?? 'Login non riuscito. Controlla credenziali e sessione admin.';
  }
}
</script>

<template>
  <div class="grid min-h-screen lg:grid-cols-[1.1fr_minmax(420px,520px)]">
    <section class="hidden border-r border-stone-200/70 bg-ops-900 px-8 py-10 text-stone-100 lg:flex lg:flex-col lg:justify-between">
      <div>
        <img
          src="/brand/logo.svg"
          alt="Magazzino Scipioni"
          class="h-10 w-auto max-w-[240px] brightness-0 invert"
        >
        <div class="mt-5 text-[11px] font-semibold uppercase tracking-[0.26em] text-ops-100">
          Magazzino Scipioni
        </div>
        <h1 class="mt-4 max-w-xl text-4xl font-semibold tracking-tight">
          Console staff separata per eventi, prenotazioni e utenti.
        </h1>
        <p class="mt-5 max-w-xl text-base leading-8 text-stone-300">
          Nuxt gestisce l'interfaccia di backoffice in un container dedicato, mentre Laravel resta
          responsabile di API, sessione e regole di accesso.
        </p>
      </div>

      <div class="grid gap-4">
        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
          <p class="text-sm font-medium text-white">Accesso amministrativo</p>
          <p class="mt-2 text-sm leading-6 text-stone-300">
            Solo account staff o amministratori possono entrare nella console.
          </p>
        </div>
      </div>
    </section>

    <section class="flex items-center justify-center px-4 py-8 sm:px-6">
      <UCard class="login-card w-full max-w-md border-stone-200/70 bg-white/90 shadow-xl ring-0">
        <template #header>
          <div>
            <img
              src="/brand/logo.svg"
              alt="Magazzino Scipioni"
              class="mb-4 h-8 w-auto max-w-[180px]"
            >
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">
              Staff Console
            </p>
            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-stone-950">
              Accedi al backoffice
            </h2>
            <p class="mt-2 text-sm leading-6 text-stone-600">
              Usa le credenziali staff abilitate sul backend Laravel.
            </p>
          </div>
        </template>

        <UAlert
          v-if="error"
          color="error"
          variant="soft"
          icon="i-lucide-circle-alert"
          :title="error"
          class="mb-4"
        />

        <form class="login-form space-y-4" @submit.prevent="submit">
          <UFormField label="Email" name="email">
            <UInput
              v-model="form.email"
              type="email"
              size="xl"
              autocomplete="email"
              class="login-input w-full"
            />
          </UFormField>

          <UFormField label="Password" name="password">
            <UInput
              v-model="form.password"
              type="password"
              size="xl"
              autocomplete="current-password"
              class="login-input w-full"
            />
          </UFormField>

          <div class="pt-2">
            <UButton type="submit" size="xl" block class="login-submit" :loading="auth.loading">
              Entra nella console
            </UButton>
          </div>
        </form>
      </UCard>
    </section>
  </div>
</template>
