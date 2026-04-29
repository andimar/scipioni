<script setup lang="ts">
defineProps<{
  label: string;
  value: string;
  detail: string;
  icon: string;
}>();
</script>

<template>
  <div class="surface rounded-lg p-4">
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <p class="truncate text-[11px] font-semibold uppercase tracking-wide text-stone-500 dark:text-stone-400">
          {{ label }}
        </p>
        <p class="mt-2 text-2xl font-semibold tracking-tight text-stone-950 dark:text-white">
          {{ value }}
        </p>
        <p class="mt-1 line-clamp-2 text-xs leading-5 text-stone-500 dark:text-stone-400">
          {{ detail }}
        </p>
      </div>

      <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-ops-50 text-ops-700 dark:bg-ops-500/12 dark:text-ops-100">
        <UIcon :name="icon" class="size-4" />
      </div>
    </div>
  </div>
</template>
