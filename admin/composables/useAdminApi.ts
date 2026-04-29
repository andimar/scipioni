export function useAdminApi() {
  const config = useRuntimeConfig();

  async function request<T>(path: string, options: Parameters<typeof $fetch<T>>[1] = {}) {
    const baseURL = import.meta.server ? config.adminApiBase : config.public.adminApiBase;
    const headers = import.meta.server
      ? {
          accept: 'application/json',
          ...useRequestHeaders(['cookie']),
          ...(options.headers ?? {}),
        }
      : {
          accept: 'application/json',
          ...(options.headers ?? {}),
        };

    return await $fetch<T>(path, {
      baseURL,
      credentials: 'include',
      headers,
      ...options,
    });
  }

  return {
    request,
  };
}
