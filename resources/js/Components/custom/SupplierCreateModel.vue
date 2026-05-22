<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-50" @close="handleClose">
      <!-- Overlay with blur -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" />
      </TransitionChild>

      <!-- Panel -->
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
          <DialogPanel class="relative w-full max-w-lg transform transition-all">
            <!-- Card -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl shadow-2xl border border-slate-700/50 overflow-hidden">
              
              <!-- Header -->
              <div class="relative bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 px-8 py-6">
                <button
                  @click="handleClose"
                  class="absolute top-4 right-4 p-2 text-white/80 hover:text-white hover:bg-white/20 rounded-xl transition-all duration-200 touch-manipulation"
                >
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>

                <div class="flex items-center gap-4">
                  <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-sm">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                  </div>
                  <div>
                    <DialogTitle class="text-2xl font-bold text-white">Add Supplier</DialogTitle>
                    <p class="text-blue-100 text-sm mt-1">Create a new supplier</p>
                  </div>
                </div>
              </div>

              <!-- Form -->
              <div class="p-8 space-y-6 text-left">
                <div v-for="field in fields" :key="field.name" class="space-y-2">
                  <label class="block text-lg font-semibold text-slate-200">{{ field.label }}</label>
                  
                  <input
                    v-if="field.type !== 'file'"
                    v-model="form[field.name]"
                    :type="field.type"
                    :placeholder="field.placeholder"
                    class="w-full px-6 py-3 bg-slate-800/50 border border-slate-700 rounded-2xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                  />

                  <!-- File input -->
                  <input
                    v-if="field.type === 'file'"
                    type="file"
                    accept="image/*"
                    @change="handleImageUpload"
                    class="w-full px-6 py-3 bg-slate-800/50 border border-slate-700 rounded-2xl text-white file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-purple-600 file:text-white hover:file:bg-purple-700 cursor-pointer"
                  />

                  <!-- Error -->
                  <div v-if="form.errors[field.name]" class="text-red-400 text-sm mt-1">
                    {{ form.errors[field.name] }}
                  </div>

                  <!-- Image preview -->
                  <div v-if="field.name === 'image' && form.image" class="mt-2">
                    <img :src="URL.createObjectURL(form.image)" class="w-28 h-28 object-cover rounded-xl border border-slate-700 shadow" />
                  </div>
                </div>

                <!-- Buttons -->
                <div class="flex gap-4 pt-6 justify-center">
                  <button
                    @click="handleSubmit"
                    :disabled="isSubmitting"
                    class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold py-3 px-6 rounded-2xl transition-all duration-200 transform active:scale-95 flex items-center justify-center gap-2"
                  >
                    <div v-if="isSubmitting" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                    <span v-else>Save Supplier</span>
                  </button>
                 <button
  @click="handleClose"
  class="px-6 py-3 bg-slate-700 hover:bg-slate-600 text-slate-200 font-semibold rounded-2xl transition-all duration-200"
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
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from "@headlessui/vue";

const emit = defineEmits(["update:open"]);
defineProps({ open: { type: Boolean, required: true } });

const form = useForm({
  name: "",
  contact: "",
  email: "",
  address: "",
  image: null,
});

const isSubmitting = ref(false);

const fields = [
  { name: "name", label: "Supplier Name", type: "text", placeholder: "Enter name..." },
  { name: "contact", label: "Contact", type: "tel", placeholder: "Enter 10-digit contact..." },
  { name: "email", label: "Email", type: "email", placeholder: "Enter email (optional)..." },
  { name: "address", label: "Address", type: "text", placeholder: "Enter address..." },
  { name: "image", label: "Supplier Image", type: "file" },
];

const handleImageUpload = (event) => {
  form.image = event.target.files?.[0] || null;
};

 

const handleSubmit = () => {
   
  form.clearErrors();

  if (!form.name?.trim()) {
    form.setError("name", "Supplier name is required.");
  }

  if (Object.keys(form.errors).length) return;

  isSubmitting.value = true;
form.post("/suppliers", {
  forceFormData: true,
  onSuccess: () => {
    form.reset();        // reset form
    isSubmitting.value = false;
    emit("update:open", false); // <-- this closes the modal
  },
  onFinish: () => (isSubmitting.value = false),
});

};

const handleClose = () => {
   
  form.reset();
  form.clearErrors();
  isSubmitting.value = false;
  emit("update:open", false);
};
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
