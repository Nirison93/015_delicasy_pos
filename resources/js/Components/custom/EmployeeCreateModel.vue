<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-50" @close="$emit('update:open', false)">

      <!-- Backdrop -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
        leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" />
      </TransitionChild>

      <!-- Panel -->
      <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
        <TransitionChild
          as="template"
          enter="ease-out duration-300" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100"
          leave="ease-in duration-200" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95"
        >
          <DialogPanel class="w-full max-w-lg bg-white rounded-2xl shadow-2xl ring-1 ring-slate-200 overflow-hidden">

            <!-- Header -->
            <div class="flex items-center justify-between px-7 py-5 bg-slate-800">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/10">
                  <i class="ri-user-add-line text-white text-xl"></i>
                </div>
                <DialogTitle class="text-[18px] font-bold text-white tracking-wide">Add Employee</DialogTitle>
              </div>
              <button
                @click="$emit('update:open', false)"
                class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-300 hover:text-white hover:bg-white/10 transition"
              >
                <i class="ri-close-line text-xl"></i>
              </button>
            </div>

            <!-- Form Body -->
            <form @submit.prevent="submit">
              <div class="px-7 py-6 space-y-5">

                <!-- Name -->
                <div>
                  <label class="block text-[13px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Employee Name <span class="text-red-400">*</span></label>
                  <input
                    v-model="form.name"
                    type="text"
                    id="name"
                    required
                    placeholder="Enter full name"
                    class="w-full h-12 px-4 text-[15px] text-slate-800 bg-slate-50 rounded-xl ring-1 ring-slate-200 focus:ring-2 focus:ring-blue-400 focus:bg-white outline-none transition"
                  />
                  <span v-if="form.errors.name" class="block mt-1.5 text-[13px] text-red-500 font-medium">
                    {{ form.errors.name }}
                  </span>
                </div>

                <!-- Address -->
                <div>
                  <label class="block text-[13px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Address</label>
                  <input
                    v-model="form.address"
                    type="text"
                    id="address"
                    placeholder="Enter address"
                    class="w-full h-12 px-4 text-[15px] text-slate-800 bg-slate-50 rounded-xl ring-1 ring-slate-200 focus:ring-2 focus:ring-blue-400 focus:bg-white outline-none transition"
                  />
                  <span v-if="form.errors.address" class="block mt-1.5 text-[13px] text-red-500 font-medium">
                    {{ form.errors.address }}
                  </span>
                </div>

                <!-- Email -->
                <div>
                  <label class="block text-[13px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Email</label>
                  <input
                    v-model="form.email"
                    type="email"
                    id="email"
                    placeholder="Enter email address"
                    class="w-full h-12 px-4 text-[15px] text-slate-800 bg-slate-50 rounded-xl ring-1 ring-slate-200 focus:ring-2 focus:ring-blue-400 focus:bg-white outline-none transition"
                  />
                  <span v-if="form.errors.email" class="block mt-1.5 text-[13px] text-red-500 font-medium">
                    {{ form.errors.email }}
                  </span>
                </div>

                <!-- Phone -->
                <div>
                  <label class="block text-[13px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Phone</label>
                  <input
                    v-model="form.phone"
                    type="text"
                    id="phone"
                    placeholder="Enter phone number"
                    class="w-full h-12 px-4 text-[15px] text-slate-800 bg-slate-50 rounded-xl ring-1 ring-slate-200 focus:ring-2 focus:ring-blue-400 focus:bg-white outline-none transition"
                  />
                  <span v-if="form.errors.phone" class="block mt-1.5 text-[13px] text-red-500 font-medium">
                    {{ form.errors.phone }}
                  </span>
                </div>

              </div>

              <!-- Footer Buttons -->
              <div class="flex items-center justify-end gap-3 px-7 py-5 border-t border-slate-100 bg-slate-50">
                <button
                  type="button"
                  @click="$emit('update:open', false)"
                  class="px-6 py-2.5 rounded-xl text-[14px] font-semibold text-slate-600 bg-white ring-1 ring-slate-200 hover:bg-slate-100 transition"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="px-7 py-2.5 rounded-xl text-[14px] font-bold text-white bg-slate-800 hover:bg-slate-700 transition active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed"
                >
                  <i class="ri-save-line mr-1.5"></i> Save Employee
                </button>
              </div>
            </form>

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
  import { ref } from "vue";
  import { useForm } from "@inertiajs/vue3";



  const emit = defineEmits(["update:open"]);

  // The `open` prop controls the visibility of the modal
  defineProps({
    open: {
      type: Boolean,
      required: true,
    },
    employees: {
      type: Array,
      required: true,
    },
  });

  const form = useForm({
    name: "",
    employee_id: "",
    address: "",
    email: "",
    phone: "",


  });

  const submit = () => {
    form.post("/employees", {
      onSuccess: () => {
        form.reset();
        emit("update:open", false);
      },
    });
  };
  </script>
