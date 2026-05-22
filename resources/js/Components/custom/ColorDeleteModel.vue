<template>
    <TransitionRoot as="template" :show="open">
        <Dialog class="relative z-10" @close="$emit('update:open', false)">
            <!-- Modal Overlay -->
            <TransitionChild
                as="template"
                enter="ease-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-200"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" />
            </TransitionChild>

            <!-- Modal Content -->
            <div class="fixed inset-0 z-10 flex items-center justify-center m-[1.25rem]">
                <TransitionChild
                    as="template"
                    enter="ease-out duration-300"
                    enter-from="opacity-0 scale-95"
                    enter-to="opacity-100 scale-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100 scale-100"
                    leave-to="opacity-0 scale-95"
                >
                    <DialogPanel class="bg-white border-4 border-black rounded-[10px] shadow-xl w-full max-w-md p-6 text-center">
                        <DialogTitle as="h3" class="text-2xl font-bold text-gray-900 mb-6">
                            Delete Base
                        </DialogTitle>

                        <!-- Confirmation Message -->
                        <p class="text-lg text-center text-gray-700 mb-8">
                            Are you sure you want to delete this Base? This action cannot be undone.
                        </p>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-4">
                            <button
                                type="button"
                                class="px-6 py-2 text-[15px] font-medium text-white bg-red-600 rounded-lg shadow-md hover:bg-red-700 hover:shadow-lg transition-all duration-200"
                                
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="px-6 py-2 text-[15px] font-medium text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 hover:shadow-lg transition-all duration-200"
                                @click.prevent="() => {   deleteItem(); }"
                            >
                                Delete
                            </button>
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
  import { ref } from "vue";
  import { useForm } from "@inertiajs/vue3";


  ;



  const emit = defineEmits(["update:open"]);

  // Props for the modal
  const { open, selectedColor } = defineProps({
    open: {
      type: Boolean,
      required: true,
    },
    selectedColor: {
      type: Object,
      default: null, // Ensure it defaults to null
    },
  });

  // Form for handling deletion
  const form = useForm({});

  // Delete the selected category
  const deleteItem = () => {
    if (!selectedColor?.id) return;

    form.delete(`/colors/${selectedColor.id}`, {
      onSuccess: () => {
        emit("update:open", false); // Close the modal on success
      },
      onError: (errors) => {
        console.error("Delete failed:", errors);
      },
    });
  };
  </script>
