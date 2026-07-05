<script setup>
import { Head, useForm, usePage } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import { computed, ref, onMounted } from "vue";

const page = usePage();
const companyInfo = computed(() => page.props.companyInfo);

defineProps({
  canResetPassword: Boolean,
  status: String,
});

const showPassword = ref(false);
const isFormVisible = ref(false);

const form = useForm({
  identity: "",
  password: "",
  remember: false,
});

onMounted(() => {
  isFormVisible.value = true;
});

const submit = () => {
  form
    .transform((data) => ({
      ...data,
      remember: form.remember ? "on" : "",
    }))
    .post(route("login"), {
      onFinish: () => form.reset("password"),
    });
};
</script>

<template>
  <Head title="Log in"/>

  <div
    class="flex min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 bg-cover bg-center bg-no-repeat relative"
    :style="{ backgroundImage: 'url(/images/login_img_auth.avif)' }"
  >
    <!-- Dark overlay for readability -->
    <div class="absolute inset-0 bg-black/60"></div>

    <!-- ===================== LEFT SIDE — Image ===================== -->
    <div class="hidden relative overflow-hidden group">
      <img
        src="/images/login_img_auth.avif"
        alt="Login visual"
        class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
      />
      <!-- Dark gradient overlay -->
      <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-900/50 to-transparent"></div>

      <!-- Decorative elements -->
      <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>

      <!-- Bottom text over image -->
      <div class="relative z-10 mt-auto p-10 space-y-4">
        <div class="space-y-2">
          <h2 class="text-6xl font-bold text-white leading-tight drop-shadow-lg">
            Resturent POS System
          </h2>
          <div class="h-1 w-24 bg-gradient-to-r from-emerald-500 to-emerald-400 rounded-full drop-shadow-lg"></div>
        </div>
        <p class="mt-4 max-w-md text-lg text-slate-200/80 drop-shadow">
          Powered by Online Mudalali
        </p>
      </div>
    </div>

    <!-- ===================== RIGHT SIDE — Login Form ===================== -->
    <div
      class="relative z-10 flex w-full flex-col items-center justify-center px-6 py-12 bg-gradient-to-b from-slate-900/50 to-slate-950"
    >
      <div
        class="w-full max-w-2xl space-y-8 transition-all duration-1000"
        :class="isFormVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'"
      >
        <!-- Header -->
        <div class="text-center space-y-4">
          <div
            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500/20 to-emerald-600/10 ring-1 ring-emerald-500/30 transform transition-transform duration-500 hover:scale-110"
          >
            <svg
              class="h-8 w-8 text-emerald-400"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"
              />
            </svg>
          </div>
          <div>
            <h1 class="text-6xl font-extrabold tracking-tight text-white">
              Sign In
            </h1>
            <p class="mt-3 text-xl text-slate-400">
              Access your hotel management system
            </p>
          </div>
        </div>

        <!-- Form card with enhanced styling -->
        <form
          @submit.prevent="submit"
          class="group rounded-2xl border border-white/[0.08] bg-white/[0.03] backdrop-blur-xl p-10 shadow-2xl space-y-7 transition-all duration-300 hover:border-white/[0.12] hover:bg-white/[0.05]"
        >
          <!-- Username -->
          <div class="space-y-3">
            <label
              for="identity"
              class="block text-lg font-semibold text-slate-300 uppercase tracking-wide"
            >
              Username or Email
            </label>
            <div class="relative group/input">
              <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                <svg class="h-5 w-5 text-slate-500 group-focus-within/input:text-emerald-400 transition-colors" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
              </span>
              <TextInput
                id="identity"
                v-model="form.identity"
                type="text"
                class="w-full rounded-xl border border-white/10 bg-white/[0.03] py-6 pl-14 pr-4 text-xl text-white placeholder:text-slate-500 transition-all duration-200 focus:border-emerald-500/50 focus:bg-white/[0.07] focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                required
                autofocus
                placeholder="Enter your username"
                autocomplete="username"
              />
            </div>
            <InputError
              class="mt-1 text-sm text-red-400"
              :message="form.errors.identity"
            />
          </div>

          <!-- Password -->
          <div class="space-y-3">
            <label
              for="password"
              class="block text-lg font-semibold text-slate-300 uppercase tracking-wide"
            >
              Password
            </label>
            <div class="relative group/input">
              <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                <svg class="h-5 w-5 text-slate-500 group-focus-within/input:text-emerald-400 transition-colors" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
              </span>
              <TextInput
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                class="w-full rounded-xl border border-white/10 bg-white/[0.03] py-6 pl-14 pr-16 text-xl text-white placeholder:text-slate-500 transition-all duration-200 focus:border-emerald-500/50 focus:bg-white/[0.07] focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                required
                placeholder="Enter your password"
                autocomplete="current-password"
              />
              <!-- Toggle visibility -->
              <button
                type="button"
                class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-500 hover:text-emerald-400 transition-colors duration-200"
                @click="showPassword = !showPassword"
                tabindex="-1"
              >
                <!-- Eye open -->
                <svg v-if="!showPassword" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                <!-- Eye closed -->
                <svg v-else class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
              </button>
            </div>
            <InputError
              class="mt-1 text-sm text-red-400"
              :message="form.errors.password"
            />
          </div>

          <!-- Remember and Forgot Password -->

          <!-- Submit button -->
          <button
            type="submit"
            :disabled="form.processing"
            class="relative w-full mt-10 overflow-hidden rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 py-7 text-xl font-semibold text-slate-900 shadow-lg shadow-emerald-500/30 transition-all duration-200 hover:from-emerald-400 hover:to-emerald-500 hover:shadow-emerald-500/40 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 focus:ring-offset-slate-900 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50 disabled:scale-100 group"
          >
            <span class="absolute inset-0 origin-right scale-x-0 transform bg-white/10 transition-transform duration-500 group-hover:origin-left group-hover:scale-x-100"></span>
            <span class="relative flex items-center justify-center gap-3">
              <svg
                v-if="form.processing"
                class="h-7 w-7 animate-spin"
                viewBox="0 0 24 24"
                fill="none"
              >
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="4" />
                <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="4" stroke-linecap="round" />
              </svg>
              <span>{{ form.processing ? "Signing in..." : "Sign In" }}</span>
            </span>
          </button>
        </form>

        <!-- Status message -->
        <div v-if="status" class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 p-4 text-base text-emerald-200">
          {{ status }}
        </div>

        <!-- Footer -->
        <p class="text-center text-lg text-slate-600">
          Powered by
          <span class="font-semibold bg-gradient-to-r from-emerald-400 to-emerald-300 bg-clip-text text-transparent">
            ඔන්ලයින් මුදලාලී
          </span>
        </p>
      </div>
    </div>
  </div>
</template>

