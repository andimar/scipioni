<script setup lang="ts">
import AppHeader from '~/components/layout/AppHeader.vue';
import AppMobileNav from '~/components/layout/AppMobileNav.vue';
import AppSidebar from '~/components/layout/AppSidebar.vue';

const { sidebarOpen, closeSidebar } = useAdminShell();
</script>

<template>
  <div class="admin-shell min-h-screen">
    <div class="hidden lg:grid lg:min-h-screen lg:grid-cols-[264px_minmax(0,1fr)]">
      <div class="border-r border-stone-200 dark:border-white/8">
        <AppSidebar />
      </div>

      <div class="min-w-0">
        <AppHeader />
        <main class="mx-auto flex w-full max-w-[1440px] flex-col gap-4 px-5 py-5">
          <slot />
        </main>
      </div>
    </div>

    <div class="lg:hidden">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="sidebarOpen"
          class="fixed inset-0 z-30 bg-stone-950/55 backdrop-blur-sm"
          @click="closeSidebar"
        />
      </Transition>

      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="-translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-x-0"
        leave-to-class="-translate-x-full"
      >
        <div
          v-if="sidebarOpen"
          class="fixed inset-y-0 left-0 z-40 w-[86vw] max-w-[300px] border-r border-stone-200 dark:border-white/8"
        >
          <AppSidebar mobile @close="closeSidebar" />
        </div>
      </Transition>

      <AppHeader />

      <main class="mx-auto flex w-full max-w-[1440px] flex-col gap-4 px-4 py-4 pb-24 sm:px-5">
        <slot />
      </main>

      <AppMobileNav />
    </div>
  </div>
</template>
