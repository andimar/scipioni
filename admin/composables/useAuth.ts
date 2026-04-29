export function useAuth() {
  const auth = useAuthStore();

  return {
    auth,
    ensureSession: () => auth.bootstrap(),
  };
}
