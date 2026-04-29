<script setup lang="ts">
import type {
  AdminCustomerUser,
  AdminStaffUser,
  CustomerUserPayload,
  StaffUserPayload,
  UserFieldErrors,
} from '~/types/users';

definePageMeta({
  middleware: ['auth'],
});

const auth = useAuthStore();
const toast = useToast();
const { request } = useAdminApi();

const activeTab = ref<'customers' | 'staff'>('customers');
const search = ref('');
const customerStatus = ref('');
const savingCustomerId = ref<number | null>(null);
const savingStaffId = ref<number | null>(null);
const creatingStaff = ref(false);
const customerErrors = ref<UserFieldErrors>({});
const staffErrors = ref<UserFieldErrors>({});

const { data: customersData, pending: customersPending, refresh: refreshCustomers } = await useAsyncData(
  'admin-users-customers',
  () => request<{
    data: AdminCustomerUser[];
    meta: {
      total: number;
      current_page: number;
      last_page: number;
      from: number | null;
      to: number | null;
    };
  }>('/users', {
    query: {
      search: search.value || undefined,
      status: customerStatus.value || undefined,
    },
  }),
  {
    server: false,
    watch: [search, customerStatus],
  }
);

const { data: staffData, pending: staffPending, refresh: refreshStaff } = await useAsyncData(
  'admin-users-staff',
  () => request<{ data: AdminStaffUser[] }>('/admin-users', {
    query: {
      search: search.value || undefined,
    },
  }),
  {
    server: false,
    watch: [search],
  }
);

const customers = computed(() => customersData.value?.data ?? []);
const staffUsers = computed(() => staffData.value?.data ?? []);

const customerDrafts = reactive<Record<number, CustomerUserPayload>>({});
const staffDrafts = reactive<Record<number, StaffUserPayload>>({});
const newStaff = reactive<StaffUserPayload>({
  name: '',
  email: '',
  password: '',
  role: 'staff',
  is_active: true,
});

watch(customers, (users) => {
  for (const user of users) {
    customerDrafts[user.id] = {
      first_name: user.first_name,
      last_name: user.last_name,
      email: user.email,
      phone: user.phone ?? '',
      is_active: user.is_active,
    };
  }
}, { immediate: true });

watch(staffUsers, (users) => {
  for (const user of users) {
    staffDrafts[user.id] = {
      name: user.name,
      email: user.email,
      password: '',
      role: user.role === 'admin' ? 'admin' : 'staff',
      is_active: user.is_active,
    };
  }
}, { immediate: true });

const statusOptions = [
  { label: 'Tutti', value: '' },
  { label: 'Attivi', value: 'active' },
  { label: 'Disattivati', value: 'inactive' },
];

const roleOptions = [
  { label: 'Amministratore', value: 'admin' },
  { label: 'Gestore', value: 'staff' },
];

const summary = computed(() => ({
  customers: customersData.value?.meta.total ?? 0,
  activeCustomers: customers.value.filter((user) => user.is_active).length,
  staff: staffUsers.value.length,
  admins: staffUsers.value.filter((user) => user.role === 'admin').length,
}));

function roleLabel(role: string) {
  return role === 'admin' ? 'Amministratore' : 'Gestore';
}

async function refreshAll() {
  await Promise.all([
    refreshCustomers(),
    refreshStaff(),
  ]);
}

function formatDate(value: string | null) {
  return value ? new Date(value).toLocaleDateString('it-IT') : 'N/D';
}

function responseErrors(error: any, fallback: string): UserFieldErrors {
  const errors = error?.data?.errors as Record<string, string[] | string> | undefined;

  if (!errors) {
    return { general: error?.data?.message ?? fallback };
  }

  return Object.fromEntries(
    Object.entries(errors).map(([key, value]) => [key, Array.isArray(value) ? value[0] : value])
  );
}

async function updateCustomer(user: AdminCustomerUser) {
  if (!auth.isAdmin) {
    return;
  }

  savingCustomerId.value = user.id;
  customerErrors.value = {};

  try {
    await request(`/users/${user.id}`, {
      method: 'PUT',
      body: customerDrafts[user.id],
    });

    toast.add({
      title: 'Utente aggiornato',
      description: 'Il profilo cliente e\' stato salvato.',
      color: 'success',
    });

    await refreshCustomers();
  } catch (error: any) {
    customerErrors.value = responseErrors(error, 'Aggiornamento utente non riuscito.');
  } finally {
    savingCustomerId.value = null;
  }
}

async function createStaff() {
  if (!auth.isAdmin) {
    return;
  }

  creatingStaff.value = true;
  staffErrors.value = {};

  try {
    await request('/admin-users', {
      method: 'POST',
      body: newStaff,
    });

    Object.assign(newStaff, {
      name: '',
      email: '',
      password: '',
      role: 'staff',
      is_active: true,
    });

    toast.add({
      title: 'Account staff creato',
      description: 'Il nuovo account puo accedere al backoffice.',
      color: 'success',
    });

    await refreshStaff();
  } catch (error: any) {
    staffErrors.value = responseErrors(error, 'Creazione account staff non riuscita.');
  } finally {
    creatingStaff.value = false;
  }
}

async function updateStaff(user: AdminStaffUser) {
  if (!auth.isAdmin) {
    return;
  }

  savingStaffId.value = user.id;
  staffErrors.value = {};

  try {
    await request(`/admin-users/${user.id}`, {
      method: 'PUT',
      body: staffDrafts[user.id],
    });

    toast.add({
      title: 'Account staff aggiornato',
      description: 'Permessi e stato account sono stati salvati.',
      color: 'success',
    });

    await refreshStaff();
  } catch (error: any) {
    staffErrors.value = responseErrors(error, 'Aggiornamento account staff non riuscito.');
  } finally {
    savingStaffId.value = null;
  }
}
</script>

<template>
  <div class="grid gap-4">
    <section class="surface rounded-lg p-4">
      <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-stone-500 dark:text-stone-400">
            Amministrazione
          </p>
          <h1 class="mt-1 text-xl font-semibold tracking-tight text-stone-950 dark:text-white">
            Utenti app e account staff
          </h1>
          <p class="mt-1 text-sm text-stone-600 dark:text-stone-400">
            I clienti restano profili app. Il backoffice accetta solo account staff autorizzati.
          </p>
        </div>

        <div class="flex flex-wrap gap-2">
          <UButton
            color="neutral"
            variant="outline"
            icon="i-lucide-rotate-cw"
            :loading="customersPending || staffPending"
            @click="refreshAll"
          >
            Aggiorna
          </UButton>
          <UBadge :color="auth.isAdmin ? 'success' : 'neutral'" variant="soft" class="px-3 py-2">
            {{ auth.roleLabel }}
          </UBadge>
        </div>
      </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
      <div class="surface rounded-lg p-4">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-stone-500">Clienti</p>
        <p class="mt-2 text-2xl font-semibold text-stone-950 dark:text-white">{{ summary.customers }}</p>
      </div>
      <div class="surface rounded-lg p-4">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-stone-500">Clienti attivi</p>
        <p class="mt-2 text-2xl font-semibold text-stone-950 dark:text-white">{{ summary.activeCustomers }}</p>
      </div>
      <div class="surface rounded-lg p-4">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-stone-500">Staff</p>
        <p class="mt-2 text-2xl font-semibold text-stone-950 dark:text-white">{{ summary.staff }}</p>
      </div>
      <div class="surface rounded-lg p-4">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-stone-500">Admin</p>
        <p class="mt-2 text-2xl font-semibold text-stone-950 dark:text-white">{{ summary.admins }}</p>
      </div>
    </section>

    <section class="surface rounded-lg p-3">
      <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
      <div class="flex rounded-md border border-stone-200 bg-stone-50 p-1 dark:border-white/8 dark:bg-white/5">
        <button
          type="button"
          class="rounded px-4 py-2 text-sm font-semibold transition"
          :class="activeTab === 'customers' ? 'bg-ops-600 text-white shadow-sm dark:bg-ops-500 dark:text-ops-950' : 'text-stone-600 hover:text-stone-950 dark:text-stone-300 dark:hover:text-white'"
          @click="activeTab = 'customers'"
        >
          Clienti app
        </button>
        <button
          type="button"
          class="rounded px-4 py-2 text-sm font-semibold transition"
          :class="activeTab === 'staff' ? 'bg-ops-600 text-white shadow-sm dark:bg-ops-500 dark:text-ops-950' : 'text-stone-600 hover:text-stone-950 dark:text-stone-300 dark:hover:text-white'"
          @click="activeTab = 'staff'"
        >
          Staff
        </button>
      </div>

      <div class="grid gap-3 lg:grid-cols-[280px_180px]">
        <UInput v-model="search" icon="i-lucide-search" placeholder="Cerca utenti" />
        <USelect v-if="activeTab === 'customers'" v-model="customerStatus" :items="statusOptions" />
      </div>
      </div>
    </section>

    <UAlert
      v-if="!auth.isAdmin"
      color="neutral"
      variant="soft"
      icon="i-lucide-lock"
      title="Accesso in sola lettura"
      description="Il ruolo gestore puo consultare gli utenti, ma non modificare profili o account amministrativi."
    />

    <UAlert
      v-if="customerErrors.general || staffErrors.general"
      color="error"
      variant="soft"
      icon="i-lucide-circle-alert"
      :title="customerErrors.general || staffErrors.general"
    />

    <section v-if="activeTab === 'customers'" class="surface overflow-hidden rounded-lg">
      <div class="flex items-center justify-between gap-3 border-b border-stone-200 px-4 py-3 dark:border-white/8">
        <h2 class="text-sm font-semibold text-stone-950 dark:text-white">
          Clienti app
        </h2>
        <UBadge color="neutral" variant="soft">
          {{ customersData?.meta.total ?? 0 }} record
        </UBadge>
      </div>

      <div class="divide-y divide-stone-200 dark:divide-white/8">
        <div v-for="user in customers" :key="user.id" class="p-4">
          <div class="grid gap-3 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-end">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[1fr_1fr_1.35fr_1fr_130px]">
              <UFormField label="Nome" :error="customerErrors.first_name">
                <UInput v-model="customerDrafts[user.id].first_name" :disabled="!auth.isAdmin" />
              </UFormField>
              <UFormField label="Cognome" :error="customerErrors.last_name">
                <UInput v-model="customerDrafts[user.id].last_name" :disabled="!auth.isAdmin" />
              </UFormField>
              <UFormField label="Email" :error="customerErrors.email">
                <UInput v-model="customerDrafts[user.id].email" :disabled="!auth.isAdmin" />
              </UFormField>
              <UFormField label="Telefono" :error="customerErrors.phone">
                <UInput v-model="customerDrafts[user.id].phone" :disabled="!auth.isAdmin" />
              </UFormField>
              <UFormField label="Stato">
                <USwitch v-model="customerDrafts[user.id].is_active" :disabled="!auth.isAdmin" label="Attivo" />
              </UFormField>
            </div>

            <div class="flex flex-wrap items-center gap-2 xl:justify-end">
              <UBadge color="neutral" variant="soft">
                {{ user.bookings_count }} pren.
              </UBadge>
              <UBadge color="neutral" variant="outline">
                {{ formatDate(user.created_at) }}
              </UBadge>
              <UButton
                v-if="auth.isAdmin"
                icon="i-lucide-save"
                :loading="savingCustomerId === user.id"
                @click="updateCustomer(user)"
              >
                Salva
              </UButton>
            </div>
          </div>
        </div>
      </div>

      <div v-if="!customersPending && customers.length === 0" class="px-4 py-10 text-center">
        <p class="text-sm font-semibold text-stone-900 dark:text-white">Nessun cliente trovato</p>
      </div>
    </section>

    <section v-else class="grid gap-4">
      <div v-if="auth.isAdmin" class="surface rounded-lg p-4">
        <h2 class="text-sm font-semibold text-stone-950 dark:text-white">Nuovo account staff</h2>
        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-[1fr_1fr_1fr_180px_140px_auto] xl:items-end">
          <UFormField label="Nome" :error="staffErrors.name">
            <UInput v-model="newStaff.name" />
          </UFormField>
          <UFormField label="Email" :error="staffErrors.email">
            <UInput v-model="newStaff.email" type="email" />
          </UFormField>
          <UFormField label="Password" :error="staffErrors.password">
            <UInput v-model="newStaff.password" type="password" />
          </UFormField>
          <UFormField label="Ruolo" :error="staffErrors.role">
            <USelect v-model="newStaff.role" :items="roleOptions" />
          </UFormField>
          <UFormField label="Stato">
            <USwitch v-model="newStaff.is_active" label="Attivo" />
          </UFormField>
          <UButton icon="i-lucide-plus" :loading="creatingStaff" @click="createStaff">
            Crea
          </UButton>
        </div>
      </div>

      <div class="surface overflow-hidden rounded-lg">
        <div class="flex items-center justify-between gap-3 border-b border-stone-200 px-4 py-3 dark:border-white/8">
          <h2 class="text-sm font-semibold text-stone-950 dark:text-white">
            Account staff
          </h2>
          <UBadge color="neutral" variant="soft">
            {{ staffUsers.length }} record
          </UBadge>
        </div>

        <div class="divide-y divide-stone-200 dark:divide-white/8">
          <div v-for="user in staffUsers" :key="user.id" class="p-4">
            <div class="grid gap-3 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-end">
              <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[1fr_1fr_180px_140px]">
                <UFormField label="Nome" :error="staffErrors.name">
                  <UInput v-model="staffDrafts[user.id].name" :disabled="!auth.isAdmin" />
                </UFormField>
                <UFormField label="Email" :error="staffErrors.email">
                  <UInput v-model="staffDrafts[user.id].email" :disabled="!auth.isAdmin" />
                </UFormField>
                <UFormField label="Ruolo" :error="staffErrors.role">
                  <USelect v-model="staffDrafts[user.id].role" :items="roleOptions" :disabled="!auth.isAdmin" />
                </UFormField>
                <UFormField label="Stato">
                  <USwitch v-model="staffDrafts[user.id].is_active" :disabled="!auth.isAdmin" label="Attivo" />
                </UFormField>
                <UFormField v-if="auth.isAdmin" label="Nuova password" :error="staffErrors.password" class="md:col-span-2 xl:col-span-4">
                  <UInput v-model="staffDrafts[user.id].password" type="password" placeholder="Lascia vuoto per non modificarla" />
                </UFormField>
              </div>

              <div class="flex flex-wrap items-center gap-2 xl:justify-end">
                <UBadge :color="user.role === 'admin' ? 'success' : 'neutral'" variant="soft">
                  {{ roleLabel(user.role) }}
                </UBadge>
                <UBadge color="neutral" variant="outline">
                  {{ formatDate(user.created_at) }}
                </UBadge>
                <UButton
                  v-if="auth.isAdmin"
                  icon="i-lucide-save"
                  :loading="savingStaffId === user.id"
                  @click="updateStaff(user)"
                >
                  Salva
                </UButton>
              </div>
            </div>
          </div>
        </div>

        <div v-if="!staffPending && staffUsers.length === 0" class="px-4 py-10 text-center">
          <p class="text-sm font-semibold text-stone-900 dark:text-white">Nessun account staff trovato</p>
        </div>
      </div>
    </section>
  </div>
</template>
