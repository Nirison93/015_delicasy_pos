<script setup>
import { ref, watch } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";

const { companyInfo } = defineProps({
  companyInfo: Object,
});

const previewLogoUrl = ref(null);

const form = useForm({
  _method: "put",   // important if your route is PUT/PATCH
  name: "",
  address: "",
  phone: "",
  email: "",
  website: "",
  logo: null,       // must be File or null (never a string URL)
});

// Only update preview with string URL; do NOT assign it to form.logo
watch(
  () => companyInfo,
  (ci) => {
    if (ci) {
      form.name = ci.name || "";
      form.address = ci.address || "";
      form.phone = ci.phone || "";
      form.email = ci.email || "";
      form.website = ci.website || "";
      form.logo = null; // keep null unless a new file is chosen
      previewLogoUrl.value = ci.logo ? `${ci.logo}` : null;
    } else {
      form.reset();
      previewLogoUrl.value = null;
    }
  },
  { immediate: true }
);

const handleImageUpload = (e) => {
  const file = e.target.files?.[0] || null;
  form.logo = file;                            // File for submit
  previewLogoUrl.value = file ? URL.createObjectURL(file) : previewLogoUrl.value; // temp preview
};

const submit = () => {
  if (!companyInfo) return;

  // Remove 'logo' from payload if no new file selected
  form.transform((data) => {
    const d = { ...data };
    if (!(d.logo instanceof File)) delete d.logo;
    return d;
  });

  form.post(`/company-info/${companyInfo.id}`, {
    forceFormData: true, // make sure it's multipart (safe to include)
    onSuccess: () => {
      // toast or banner already handled server-side
    },
  });
};
</script>

<template>
  <Head title="Company Info" />
  <Banner />
  <div class="min-h-screen bg-slate-50">
    <Header />

    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 py-6 space-y-6">

      <!-- Page Header -->
      <div class="flex items-center gap-3">
        <Link href="/" class="w-11 h-11 flex items-center justify-center rounded-xl bg-white ring-1 ring-slate-200 text-slate-600 hover:bg-slate-100 transition">
          <i class="ri-arrow-left-s-line text-2xl"></i>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-slate-800 tracking-wide uppercase">Company Info</h1>
          <p class="text-[13px] text-slate-400 font-medium mt-0.5">Manage your business details and branding</p>
        </div>
      </div>

      <!-- Form Card -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm overflow-hidden">

        <!-- Card Header -->
        <div class="flex items-center gap-3 px-7 py-5 border-b border-slate-100">
          <div class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100">
            <i class="ri-building-2-line text-slate-600 text-xl"></i>
          </div>
          <div>
            <p class="text-[16px] font-bold text-slate-800">Business Details</p>
            <p class="text-[12px] text-slate-400 font-medium">Update your company's information below</p>
          </div>
        </div>

        <form @submit.prevent="submit">
          <div class="px-7 py-6">

            <!-- Two-column grid for text fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

              <!-- Company Name -->
              <div>
                <label for="name" class="block text-[13px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                  Company Name <span class="text-red-400">*</span>
                </label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  placeholder="Enter company name"
                  class="w-full h-12 px-4 text-[15px] text-slate-800 bg-slate-50 rounded-xl ring-1 outline-none transition"
                  :class="form.errors.name ? 'ring-red-400 focus:ring-2 focus:ring-red-400' : 'ring-slate-200 focus:ring-2 focus:ring-blue-400 focus:bg-white'"
                />
                <p v-if="form.errors.name" class="mt-1.5 text-[13px] text-red-500 font-medium">{{ form.errors.name }}</p>
              </div>

              <!-- Company Phone -->
              <div>
                <label for="phone" class="block text-[13px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                  Phone Number
                </label>
                <input
                  id="phone"
                  v-model="form.phone"
                  type="text"
                  placeholder="Enter company phone"
                  class="w-full h-12 px-4 text-[15px] text-slate-800 bg-slate-50 rounded-xl ring-1 outline-none transition"
                  :class="form.errors.phone ? 'ring-red-400 focus:ring-2 focus:ring-red-400' : 'ring-slate-200 focus:ring-2 focus:ring-blue-400 focus:bg-white'"
                />
                <p v-if="form.errors.phone" class="mt-1.5 text-[13px] text-red-500 font-medium">{{ form.errors.phone }}</p>
              </div>

              <!-- Company Email -->
              <div>
                <label for="email" class="block text-[13px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                  Email Address
                </label>
                <input
                  id="email"
                  v-model="form.email"
                  type="text"
                  placeholder="Enter company email"
                  class="w-full h-12 px-4 text-[15px] text-slate-800 bg-slate-50 rounded-xl ring-1 outline-none transition"
                  :class="form.errors.email ? 'ring-red-400 focus:ring-2 focus:ring-red-400' : 'ring-slate-200 focus:ring-2 focus:ring-blue-400 focus:bg-white'"
                />
                <p v-if="form.errors.email" class="mt-1.5 text-[13px] text-red-500 font-medium">{{ form.errors.email }}</p>
              </div>

              <!-- Company Website -->
              <div>
                <label for="website" class="block text-[13px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                  Website
                </label>
                <input
                  id="website"
                  v-model="form.website"
                  type="text"
                  placeholder="Enter company website"
                  class="w-full h-12 px-4 text-[15px] text-slate-800 bg-slate-50 rounded-xl ring-1 outline-none transition"
                  :class="form.errors.website ? 'ring-red-400 focus:ring-2 focus:ring-red-400' : 'ring-slate-200 focus:ring-2 focus:ring-blue-400 focus:bg-white'"
                />
                <p v-if="form.errors.website" class="mt-1.5 text-[13px] text-red-500 font-medium">{{ form.errors.website }}</p>
              </div>

              <!-- Company Address — full width -->
              <div class="md:col-span-2">
                <label for="address" class="block text-[13px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                  Address
                </label>
                <input
                  id="address"
                  v-model="form.address"
                  type="text"
                  placeholder="Enter company address"
                  class="w-full h-12 px-4 text-[15px] text-slate-800 bg-slate-50 rounded-xl ring-1 outline-none transition"
                  :class="form.errors.address ? 'ring-red-400 focus:ring-2 focus:ring-red-400' : 'ring-slate-200 focus:ring-2 focus:ring-blue-400 focus:bg-white'"
                />
                <p v-if="form.errors.address" class="mt-1.5 text-[13px] text-red-500 font-medium">{{ form.errors.address }}</p>
              </div>

            </div>

            <!-- Divider -->
            <div class="my-7 border-t border-slate-100"></div>

            <!-- Logo Section -->
            <div>
              <p class="text-[13px] font-semibold text-slate-500 uppercase tracking-wider mb-4">Company Logo</p>
              <div class="flex items-start gap-6">

                <!-- Preview -->
                <div class="flex-shrink-0 w-32 h-32 rounded-2xl ring-1 ring-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden">
                  <img
                    v-if="previewLogoUrl"
                    :src="previewLogoUrl"
                    alt="Company logo"
                    class="w-full h-full object-contain p-2"
                  />
                  <div v-else class="flex flex-col items-center gap-1.5 text-slate-300">
                    <i class="ri-image-2-line text-4xl"></i>
                    <span class="text-[11px] font-semibold uppercase tracking-wide">No Logo</span>
                  </div>
                </div>

                <!-- Upload -->
                <div class="flex-1">
                  <label
                    for="logo"
                    class="flex flex-col items-center justify-center w-full h-32 rounded-2xl ring-2 ring-dashed ring-slate-200 bg-slate-50 hover:bg-slate-100 hover:ring-blue-300 cursor-pointer transition"
                  >
                    <i class="ri-upload-cloud-2-line text-3xl text-slate-400 mb-1"></i>
                    <span class="text-[14px] font-semibold text-slate-500">Click to upload a new logo</span>
                    <span class="text-[12px] text-slate-400 mt-0.5">PNG, JPG, SVG — max 2 MB</span>
                    <input
                      type="file"
                      id="logo"
                      accept="image/*"
                      @change="handleImageUpload"
                      class="sr-only"
                    />
                  </label>
                  <p v-if="form.errors.logo" class="mt-1.5 text-[13px] text-red-500 font-medium">{{ form.errors.logo }}</p>
                </div>

              </div>
            </div>

          </div>

          <!-- Footer -->
          <div class="flex items-center justify-end gap-3 px-7 py-5 border-t border-slate-100 bg-slate-50">
            <Link
              href="/"
              class="px-6 py-2.5 rounded-xl text-[14px] font-semibold text-slate-600 bg-white ring-1 ring-slate-200 hover:bg-slate-100 transition"
            >
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="flex items-center gap-2 px-7 py-2.5 rounded-xl text-[14px] font-bold text-white bg-slate-800 hover:bg-slate-700 transition active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed"
            >
              <i class="ri-save-line text-base"></i>
              {{ form.processing ? 'Saving...' : 'Update Info' }}
            </button>
          </div>
        </form>

      </div>

    </div>
  </div>

  <Footer />
</template>
