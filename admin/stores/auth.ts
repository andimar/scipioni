type AdminSession = {
  id: number;
  name: string;
  email: string;
  role?: string;
  is_active?: boolean;
};

export const useAuthStore = defineStore('admin-auth', {
  state: () => ({
    initialized: false,
    loading: false,
    user: null as AdminSession | null,
  }),
  getters: {
    isAuthenticated: (state) => Boolean(state.user),
    userName: (state) => state.user?.name ?? 'Staff',
    isAdmin: (state) => state.user?.role === 'admin',
    roleLabel: (state) => state.user?.role === 'admin' ? 'Amministratore' : 'Gestore',
  },
  actions: {
    async bootstrap() {
      if (this.initialized || this.loading) {
        return;
      }

      this.loading = true;
      const { request } = useAdminApi();

      try {
        const session = await request<AdminSession>('/me');
        this.user = session;
      } catch {
        this.user = null;
      } finally {
        this.initialized = true;
        this.loading = false;
      }
    },
    async login(payload: { email: string; password: string }) {
      const { request } = useAdminApi();
      this.loading = true;

      try {
        const session = await request<AdminSession>('/auth/login', {
          method: 'POST',
          body: {
            email: payload.email,
            password: payload.password,
          },
        });
        this.user = session;
        this.initialized = true;
      } finally {
        this.loading = false;
      }
    },
    async logout() {
      const { request } = useAdminApi();

      try {
        await request('/auth/logout', {
          method: 'POST',
        });
      } catch {
        // Ignore logout transport errors and clear local state anyway.
      }

      this.user = null;
      this.initialized = true;
    },
  },
});
