<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-10" @close="$emit('update:open', false)">

      <!-- Backdrop -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
        leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
        <TransitionChild
          as="template"
          enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 scale-95" enter-to="opacity-100 translate-y-0 scale-100"
          leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 scale-100" leave-to="opacity-0 translate-y-4 scale-95"
        >
          <DialogPanel class="relative w-full max-w-lg overflow-hidden rounded-2xl shadow-2xl">

            <!-- Gradient header strip -->
            <div class="bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900 px-7 pt-7 pb-6 border-b border-white/10">
              <!-- Close button -->
              <button
                type="button"
                @click="close"
                class="absolute top-4 right-4 flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-zinc-400 hover:bg-white/20 hover:text-white transition"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>

              <!-- Icon + Title -->
              <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/20 ring-1 ring-amber-500/40">
                  <svg class="h-6 w-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                  </svg>
                </div>
                <div>
                  <DialogTitle class="text-2xl font-bold tracking-tight text-white">Edit Category</DialogTitle>
                  <p class="mt-0.5 text-base text-zinc-400">Update the category details below.</p>
                </div>
              </div>
            </div>

            <!-- Form body -->
            <div class="bg-zinc-900 px-7 py-6">
              <form @submit.prevent="submit">
                <div class="space-y-5">

                  <!-- Category Name -->
                  <div>
                    <label class="mb-1.5 block text-base font-semibold uppercase tracking-widest text-zinc-400">
                      Category Name
                    </label>
                    <input
                      v-model="form.name"
                      type="text"
                      required
                      placeholder="e.g. Starters"
                      class="w-full rounded-xl border border-white/10 bg-zinc-800 px-4 py-3 text-base font-medium text-white placeholder-zinc-500 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30"
                    />
                    <p v-if="form.errors.name" class="mt-1.5 text-sm text-red-400">{{ form.errors.name }}</p>
                  </div>

                  <!-- Category Type -->
                  <div>
                    <label class="mb-1.5 block text-base font-semibold uppercase tracking-widest text-zinc-400">
                      Category Type
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                      <label
                        :class="[
                          'flex cursor-pointer items-center justify-center gap-2 rounded-xl border px-4 py-3 text-base font-semibold transition',
                          form.category_type === '0' || form.category_type === 0
                            ? 'border-amber-500 bg-amber-500/15 text-amber-400'
                            : 'border-white/10 bg-zinc-800 text-zinc-400 hover:border-zinc-600'
                        ]"
                      >
                        <input type="radio" v-model="form.category_type" value="0" class="hidden" />
                        🍽️ Food
                      </label>
                      <label
                        :class="[
                          'flex cursor-pointer items-center justify-center gap-2 rounded-xl border px-4 py-3 text-base font-semibold transition',
                          form.category_type === '1' || form.category_type === 1
                            ? 'border-amber-500 bg-amber-500/15 text-amber-400'
                            : 'border-white/10 bg-zinc-800 text-zinc-400 hover:border-zinc-600'
                        ]"
                      >
                        <input type="radio" v-model="form.category_type" value="1" class="hidden" />
                        🍹 Bar
                      </label>
                    </div>
                    <p v-if="form.errors.category_type" class="mt-1.5 text-sm text-red-400">{{ form.errors.category_type }}</p>
                  </div>

                  <!-- Parent Category -->
                  <div>
                    <label class="mb-1.5 block text-base font-semibold uppercase tracking-widest text-zinc-400">
                      Parent Category
                    </label>
                    <select
                      v-model="form.parent_id"
                      class="w-full rounded-xl border border-white/10 bg-zinc-800 px-4 py-3 text-base font-medium text-white transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30"
                    >
                      <option value="" class="bg-zinc-800">— No Parent —</option>
                      <option v-for="c in filteredCategories" :key="c.id" :value="c.id" class="bg-zinc-800">
                        {{ c.hierarchy_string ? `${c.hierarchy_string} › ${c.name}` : c.name }}
                      </option>
                    </select>
                    <p v-if="form.errors.parent_id" class="mt-1.5 text-sm text-red-400">{{ form.errors.parent_id }}</p>
                  </div>

                  <!-- Image -->
                  <div>
                    <label class="mb-1.5 block text-base font-semibold uppercase tracking-widest text-zinc-400">
                      Category Image
                    </label>

                    <!-- Current image preview -->
                    <div v-if="currentImageUrl && !form.remove_image" class="mb-3 flex items-center gap-4 rounded-xl border border-white/10 bg-zinc-800 p-3">
                      <img :src="currentImageUrl" class="h-16 w-16 rounded-lg object-cover ring-1 ring-white/10" />
                      <div>
                        <p class="text-base font-medium text-zinc-300">Current image</p>
                        <p class="text-sm text-zinc-500">Upload a new file to replace it</p>
                      </div>
                    </div>
                    <div v-else class="mb-3 flex items-center gap-3 rounded-xl border border-dashed border-white/10 bg-zinc-800/50 p-4 text-zinc-500">
                      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                      </svg>
                      <span class="text-base">No image set</span>
                    </div>

                    <input
                      type="file"
                      accept="image/*"
                      @change="onFileChange"
                      class="w-full rounded-xl border border-white/10 bg-zinc-800 px-3 py-2.5 text-base text-zinc-300 transition file:mr-3 file:rounded-lg file:border-0 file:bg-amber-500 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-zinc-900 hover:file:bg-amber-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30"
                    />
                    <p v-if="form.errors.image" class="mt-1.5 text-sm text-red-400">{{ form.errors.image }}</p>

                    <label class="mt-3 inline-flex cursor-pointer items-center gap-2.5">
                      <div
                        @click="form.remove_image = !form.image && !form.remove_image"
                        :class="[
                          'relative h-5 w-9 rounded-full transition',
                          form.remove_image ? 'bg-amber-500' : 'bg-zinc-700'
                        ]"
                      >
                        <input type="checkbox" v-model="form.remove_image" :disabled="!!form.image" class="sr-only" />
                        <span :class="['absolute top-0.5 left-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform', form.remove_image ? 'translate-x-4' : 'translate-x-0']" />
                      </div>
                      <span class="text-base text-zinc-400">Remove current image</span>
                    </label>
                  </div>

                </div>

                <!-- Action buttons -->
                <div class="mt-7 flex items-center justify-end gap-3 border-t border-white/10 pt-6">
                  <button
                    type="button"
                    @click="close"
                    class="rounded-xl border border-white/10 bg-zinc-800 px-5 py-2.5 text-base font-semibold text-zinc-300 transition hover:border-zinc-600 hover:text-white active:scale-95"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-6 py-2.5 text-base font-bold text-zinc-900 shadow-lg shadow-amber-500/20 transition hover:bg-amber-400 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed"
                  >
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    {{ form.processing ? 'Saving…' : 'Save Changes' }}
                  </button>
                </div>
              </form>
            </div>

          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from "@headlessui/vue";
import { ref, computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";

const emit = defineEmits(["update:open"]);
const props = defineProps({
  open: { type: Boolean, required: true },
  categories: { type: Array, required: true },
  selectedCategory: { type: Object, default: null },
});

const form = useForm({
  _method: "PUT",
  name: "",
  parent_id: "",
  image: null,          // new file (optional)
  remove_image: false,
   category_type: "0",   // checkbox to remove current image
});

const currentImageUrl = ref(null);

// Only allow selecting a parent that's not the current record
const filteredCategories = computed(() =>
  props.categories.filter(c => c.id !== props.selectedCategory?.id)
);

// Seed form when opening with a selected category
watch(
  () => props.selectedCategory,
  (val) => {
    if (!val) return;
    form.name = val.name ?? "";
    form.parent_id = val.parent?.id ?? "";
    form.image = null; 
    form.category_type = val.category_type?.toString() ?? "0";

    // If backend maps full URL via Storage::url(...), val.image is already like `/storage/...`
    currentImageUrl.value = val.image || null;
  },
  { immediate: true }
);

const onFileChange = (e) => {
  const file = e.target.files?.[0] || null;
  form.image = file;
  if (file) {
    currentImageUrl.value = URL.createObjectURL(file);
    form.remove_image = false;
  }
};

const close = () => emit("update:open", false);

const submit = () => {
  if (!props.selectedCategory?.id) return;
  form
    .transform((data) => {
      // Don't send the image field at all when no new file was chosen —
      // the backend will then leave the existing image untouched.
      const payload = { ...data };
      if (!payload.image) delete payload.image;
      return payload;
    })
    .post(route("categories.update", props.selectedCategory.id), {
      forceFormData: true, // important for files
      onSuccess: close,
      preserveScroll: true,
    });
};
</script>
