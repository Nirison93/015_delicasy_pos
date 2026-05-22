<template>
  <header class="w-full bg-slate-900 border-b border-slate-700/60 shadow-lg">
    <div class="mx-auto max-w-[1600px] flex items-center justify-between px-6 py-2 lg:px-10">

      <!-- Left — Logo + Business Name -->
      <Link
        href="/"
        class="flex items-center gap-3 flex-shrink-0 group"
        :title="businessName"
      >
        <!-- <img
          :src="companyInfo && companyInfo.logo ? companyInfo.logo : '/images/billlog.png'"
          class="h-8 w-auto object-contain transition group-hover:opacity-80"
          alt="Logo"
        /> -->
        <span class="hidden sm:block text-[15px] font-bold text-white tracking-wide uppercase leading-none group-hover:text-slate-300 transition">
          {{ businessName }}
        </span>
      </Link>

      <!-- Right — Date/Time · User · Logout -->
      <div class="flex items-center gap-3">

        <!-- Live Date & Time -->
        <div class="hidden md:flex flex-col items-end leading-tight mr-1">
          <span class="text-[13px] font-semibold text-white tabular-nums">{{ clock.time }}</span>
          <span class="text-[11px] text-slate-400">{{ clock.date }}</span>
        </div>

        <!-- Divider -->
        <div class="h-7 w-px bg-slate-700"></div>

        <!-- User info -->
        <div class="flex items-center gap-2">
          <div class="h-7 w-7 rounded-full bg-indigo-600 flex items-center justify-center flex-shrink-0">
            <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
            </svg>
          </div>
          <div class="hidden sm:block leading-tight">
            <p class="text-[13px] font-semibold text-white">{{ $page.props.auth.user.name }}</p>
            <p class="text-[11px] text-indigo-400 font-medium">{{ $page.props.auth.user.role_type }}</p>
          </div>
        </div>

        <!-- Divider -->
        <div class="h-7 w-px bg-slate-700"></div>

        <!-- Logout -->
        <form @submit.prevent="logout">
          <button
            type="submit"
            class="inline-flex items-center gap-1.5 rounded-lg bg-slate-700 px-3 py-1.5 text-[13px] font-medium text-slate-200 transition hover:bg-red-600 hover:text-white active:scale-95 touch-manipulation"
            aria-label="Logout"
          >
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
            </svg>
            Logout
          </button>
        </form>

      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { usePage, router, Link } from "@inertiajs/vue3";

const page = usePage();
const companyInfo = computed(() => page.props.companyInfo);
const businessName = computed(() =>
  companyInfo.value?.name || "Hotel System"
);

// Live clock
const clock = ref({ time: "", date: "" });
let clockTimer = null;

const updateClock = () => {
  const now = new Date();
  clock.value.time = now.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit", second: "2-digit" });
  clock.value.date = now.toLocaleDateString([], { weekday: "short", year: "numeric", month: "short", day: "numeric" });
};

onMounted(() => {
  updateClock();
  clockTimer = setInterval(updateClock, 1000);
});

onUnmounted(() => {
  clearInterval(clockTimer);
});

const logout = () => {
  router.post(route("logout"));
};
</script>
