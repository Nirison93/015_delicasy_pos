<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-50" @close="$emit('update:open', false)">

      <!-- Backdrop -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
        leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <TransitionChild
          as="template"
          enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 scale-95" enter-to="opacity-100 translate-y-0 scale-100"
          leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 scale-100" leave-to="opacity-0 translate-y-4 scale-95"
        >
          <DialogPanel class="relative w-full max-w-lg overflow-hidden rounded-2xl shadow-2xl">

            <!-- Gradient header strip -->
            <div class="bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900 px-7 pt-7 pb-6 border-b border-white/10">
              <button
                type="button"
                @click="handleClose"
                class="absolute top-4 right-4 flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-zinc-400 hover:bg-white/20 hover:text-white transition"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>

              <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/20 ring-1 ring-emerald-500/40">
                  <svg class="h-6 w-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                  </svg>
                </div>
                <div>
                  <DialogTitle class="text-2xl font-bold tracking-tight text-white">Add Category</DialogTitle>
                  <p class="mt-0.5 text-base text-zinc-400">Create a new product category</p>
                </div>
              </div>
            </div>

            <!-- Form body -->
            <div class="bg-zinc-900 px-7 py-6">
              <div class="space-y-5">

                <!-- Category Name -->
                <div>
                  <label class="mb-1.5 block text-base font-semibold uppercase tracking-widest text-zinc-400">
                    Category Name
                  </label>
                  <input
                    v-model="form.name"
                    type="text"
                    placeholder="Enter category name..."
                    required
                    class="w-full rounded-xl border border-white/10 bg-zinc-800 px-5 py-4 text-lg font-medium text-white placeholder-zinc-500 transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
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
                        'flex cursor-pointer items-center justify-center gap-2 rounded-xl border px-5 py-4 text-base font-semibold transition',
                        form.category_type === '0' || form.category_type === 0
                          ? 'border-emerald-500 bg-emerald-500/15 text-emerald-400'
                          : 'border-white/10 bg-zinc-800 text-zinc-400 hover:border-zinc-600'
                      ]"
                    >
                      <input type="radio" v-model="form.category_type" value="0" class="hidden" />
                      🍽️ Food
                    </label>
                    <label
                      :class="[
                        'flex cursor-pointer items-center justify-center gap-2 rounded-xl border px-5 py-4 text-base font-semibold transition',
                        form.category_type === '1' || form.category_type === 1
                          ? 'border-emerald-500 bg-emerald-500/15 text-emerald-400'
                          : 'border-white/10 bg-zinc-800 text-zinc-400 hover:border-zinc-600'
                      ]"
                    >
                      <input type="radio" v-model="form.category_type" value="1" class="hidden" />
                      🍹 Beverages
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
                    class="w-full rounded-xl border border-white/10 bg-zinc-800 px-5 py-4 text-lg font-medium text-white transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
                  >
                    <option value="" class="bg-zinc-800">— No Parent —</option>
                    <option
                      v-for="category in filteredCategories"
                      :key="category.id"
                      :value="category.id"
                      class="bg-zinc-800"
                    >
                      {{
                        category.hierarchy_string
                          ? category.hierarchy_string + " → " + category.name
                          : category.name
                      }}
                    </option>
                  </select>
                  <p v-if="form.errors.parent_id" class="mt-1.5 text-sm text-red-400">{{ form.errors.parent_id }}</p>
                </div>

                <!-- Category Image -->
                <div>
                  <label class="mb-1.5 block text-base font-semibold uppercase tracking-widest text-zinc-400">
                    Category Image
                  </label>
                  <input
                    type="file"
                    accept="image/*"
                    @change="e => form.image = e.target.files[0]"
                    class="w-full rounded-xl border border-white/10 bg-zinc-800 px-4 py-3.5 text-lg text-zinc-300 transition file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-500 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-zinc-900 hover:file:bg-emerald-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
                  />
                  <p v-if="form.errors.image" class="mt-1.5 text-sm text-red-400">{{ form.errors.image }}</p>

                  <div v-if="form.image" class="mt-4 flex items-center gap-4 rounded-xl border border-white/10 bg-zinc-800 p-3">
                    <img
                      :src="URL.createObjectURL(form.image)"
                      alt="Preview"
                      class="h-16 w-16 rounded-lg object-cover ring-1 ring-white/10"
                    />
                    <div>
                      <p class="text-base font-medium text-zinc-300">Selected image</p>
                      <p class="text-sm text-zinc-500">Ready to upload</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="mt-7 flex items-center justify-end gap-3 border-t border-white/10 pt-6">
                <button
                  @click="handleClose"
                  class="rounded-xl border border-white/10 bg-zinc-800 px-6 py-3 text-lg font-semibold text-zinc-300 transition hover:border-zinc-600 hover:text-white active:scale-95"
                  type="button"
                >
                  Cancel
                </button>
                <button
                  @click="handleSubmit"
                  :disabled="isSubmitting"
                  class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-7 py-3 text-lg font-bold text-zinc-900 shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-400 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed"
                  type="button"
                >
                  <svg v-if="isSubmitting" class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                  </svg>
                  {{ isSubmitting ? 'Creating...' : 'Create Category' }}
                </button>
              </div>
            </div>

          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { ref, reactive, computed } from "vue";
import { useForm } from "@inertiajs/vue3";

 

// Component props
const props = defineProps({
  open: {
    type: Boolean,
    required: true,
  },
  categories: {
    type: Array,
    required: true,
  },
});

// Component emits
const emit = defineEmits(["update:open"]);

// Form state
const form = useForm({
  name: "",
  parent_id: "",
  image: null,
  category_type: "0",
});

// Loading state
const isSubmitting = ref(false);

// Filter categories by type (only show parents of same type)
const filteredCategories = computed(() =>
  props.categories.filter(c => c.category_type === form.category_type || c.category_type === parseInt(form.category_type))
);

// Handle form submission
const handleSubmit = async () => {
  if (isSubmitting.value) return;
 
  // Basic validation
  if (!form.name.trim()) {
    form.setError('name', 'Category name is required');
    return;
  }

  isSubmitting.value = true;

  form.post(route('categories.store'), {
    forceFormData: true,
    onSuccess: () => {
      form.reset();
      isSubmitting.value = false;
      emit("update:open", false);
    },
    onError: () => {
      isSubmitting.value = false;
    },
    onFinish: () => {
      isSubmitting.value = false;
    }
  });
};


// Add handleClose method
const handleClose = () => {

  form.reset();
  emit("update:open", false);
};
</script>

<style scoped>
/* Custom scrollbar */
select::-webkit-scrollbar {
  width: 8px;
}
select::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}
select::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}
select::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Focus ring */
input:focus,
select:focus {
  box-shadow: 0 0 0 3px rgba(100, 116, 139, 0.12);
}

/* Spin animation */
@keyframes spin {
  to { transform: rotate(360deg); }
}
.animate-spin {
  animation: spin 1s linear infinite;
}
</style>
