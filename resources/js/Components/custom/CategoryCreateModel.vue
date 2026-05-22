<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-50" @close="$emit('update:open', false)">
      <!-- Overlay -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" />
      </TransitionChild>

      <!-- Modal Container -->
      <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95 translate-y-4"
          enter-to="opacity-100 scale-100 translate-y-0"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100 translate-y-0"
          leave-to="opacity-0 scale-95 translate-y-4"
        >
          <DialogPanel class="relative w-full max-w-xl transform transition-all">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">

              <!-- Header -->
              <div class="flex items-center justify-between px-8 pt-8 pb-2">
                <div>
                  <DialogTitle class="text-2xl font-bold text-slate-800">Add Category</DialogTitle>
                  <p class="text-base text-slate-400 mt-1">Create a new product category</p>
                </div>
                <button
                  @click="handleClose"
                  class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-all duration-200 touch-manipulation"
                >
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <!-- Divider -->
              <div class="mx-8 my-4 border-t border-slate-100"></div>

              <!-- Form -->
              <div class="px-8 pb-8 space-y-6">

                <!-- Category Name -->
                <div class="space-y-2">
                  <label class="block text-base font-semibold text-slate-700">
                    Category Name
                  </label>
                  <input
                    v-model="form.name"
                    type="text"
                    placeholder="Enter category name..."
                    required
                    class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-lg placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:border-slate-300 transition-all duration-200 touch-manipulation"
                  />
                  <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
                </div>

                <!-- Category Type -->
                <div class="space-y-2">
                  <label class="block text-base font-semibold text-slate-700">
                    Category Type
                  </label>
                  <div class="relative">
                    <select
                      v-model="form.category_type"
                      class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-lg appearance-none focus:outline-none focus:ring-2 focus:ring-slate-400 focus:border-slate-300 transition-all duration-200 cursor-pointer touch-manipulation"
                    >
                      <option value="0">Food</option>
                      <option value="1">Bar</option>
                    </select>
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none">
                      <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                      </svg>
                    </div>
                  </div>
                  <p v-if="form.errors.category_type" class="text-red-500 text-sm mt-1">{{ form.errors.category_type }}</p>
                </div>

                <!-- Parent Category -->
                <div class="space-y-2">
                  <label class="block text-base font-semibold text-slate-700">
                    Parent Category
                  </label>
                  <div class="relative">
                    <select
                      v-model="form.parent_id"
                      class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-lg appearance-none focus:outline-none focus:ring-2 focus:ring-slate-400 focus:border-slate-300 transition-all duration-200 cursor-pointer touch-manipulation"
                    >
                      <option value="">No Parent Category</option>
                      <option
                        v-for="category in categories"
                        :key="category.id"
                        :value="category.id"
                      >
                        {{
                          category.hierarchy_string
                            ? category.hierarchy_string + " → " + category.name
                            : category.name
                        }}
                      </option>
                    </select>
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none">
                      <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                      </svg>
                    </div>
                  </div>
                  <p v-if="form.errors.parent_id" class="text-red-500 text-sm mt-1">{{ form.errors.parent_id }}</p>
                </div>

                <!-- Category Image -->
                <div class="space-y-2">
                  <label class="block text-base font-semibold text-slate-700">
                    Category Image
                  </label>
                  <input
                    type="file"
                    accept="image/*"
                    @change="e => form.image = e.target.files[0]"
                    class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 text-base file:mr-4 file:py-2 file:px-5 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-800 file:text-white hover:file:bg-slate-700 cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-400 focus:border-slate-300 transition-all duration-200"
                  />
                  <p v-if="form.errors.image" class="text-red-500 text-sm mt-1">{{ form.errors.image }}</p>

                  <!-- Preview -->
                  <div v-if="form.image" class="mt-3">
                    <img
                      :src="URL.createObjectURL(form.image)"
                      alt="Preview"
                      class="w-28 h-28 object-cover rounded-xl border border-slate-200 shadow-sm"
                    />
                  </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-slate-100"></div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-2">
                  <button
                    @click="handleSubmit"
                    :disabled="isSubmitting"
                    class="flex-1 bg-slate-800 hover:bg-slate-900 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold py-4 px-8 rounded-xl text-base transition-all duration-200 active:scale-[0.98] touch-manipulation flex items-center justify-center gap-3"
                    type="button"
                  >
                    <div v-if="isSubmitting" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ isSubmitting ? 'Creating...' : 'Create Category' }}
                  </button>

                  <button
                    @click="handleClose"
                    class="px-8 py-4 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold rounded-xl text-base transition-all duration-200 active:scale-[0.98] touch-manipulation"
                    type="button"
                  >
                    Cancel
                  </button>
                </div>
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
import { ref, reactive } from "vue";
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

 // Handle form submission
const handleSubmit = async () => {
  if (isSubmitting.value) return;
 
  // Basic validation
  if (!form.name.trim()) {
    form.setError('name', 'Category name is required');
    return;
  }

  isSubmitting.value = true;

  form.post("/categories", {
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
