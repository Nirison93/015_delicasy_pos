<template>
  <Link :href="href" class="group">
    <div
      class="dash-card"
      :class="'bg-gradient-to-br ' + gradient"
    >
      <!-- glow ring on press -->
      <span class="absolute inset-0 rounded-2xl ring-0 ring-white/30 transition-all duration-200 group-active:ring-4" />

      <div class="relative z-10 flex flex-col items-center gap-4">
        <div class="icon-bubble">
          <img :src="icon" :alt="label" class="h-14 w-14 object-contain drop-shadow" />
        </div>
        <span class="text-center text-[15px] font-semibold leading-tight tracking-wide text-white drop-shadow-sm">
          {{ label }}
        </span>
      </div>

      <!-- decorative circles -->
      <div class="pointer-events-none absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/[0.07]" />
      <div class="pointer-events-none absolute -bottom-4 -left-4 h-20 w-20 rounded-full bg-white/[0.05]" />
    </div>
  </Link>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
  href: String,
  icon: String,
  label: String,
  gradient: String,
});
</script>

<style scoped>
.dash-card {
  @apply relative overflow-hidden rounded-2xl p-6 shadow-lg
         transition-all duration-200 ease-out
         hover:shadow-xl hover:-translate-y-1
         active:scale-[0.97] active:shadow-md
         select-none cursor-pointer
         flex items-center justify-center min-h-[170px];
}

.icon-bubble {
  @apply flex h-20 w-20 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm
         shadow-inner transition-transform duration-200 group-hover:scale-110;
}
</style>
