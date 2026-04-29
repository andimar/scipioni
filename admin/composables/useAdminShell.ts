export function useAdminShell() {
  const sidebarOpen = useState('admin-sidebar-open', () => false);

  function openSidebar() {
    sidebarOpen.value = true;
  }

  function closeSidebar() {
    sidebarOpen.value = false;
  }

  function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value;
  }

  return {
    sidebarOpen,
    openSidebar,
    closeSidebar,
    toggleSidebar,
  };
}
