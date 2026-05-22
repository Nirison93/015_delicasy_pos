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
                            Create New Base
                        </DialogTitle>

                        <form @submit.prevent="submit">
                            <div class="space-y-4">
                                <!-- Base Name Input -->
                                <div>
                                    <label for="name" class="block text-lg font-medium text-gray-700">
                                        Base Name
                                    </label>
                                    <input
                                        type="text"
                                        id="name"
                                        v-model="form.name"
                                        class="mt-2 block w-full px-4 py-2 text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.name }"
                                        required
                                    />
                                    <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.name }}
                                    </p>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-8 flex justify-end space-x-4">
                                <button
                                    type="button"
                                    class="px-6 py-2 text-[15px] font-medium text-white bg-red-600 rounded-lg shadow-md hover:bg-red-700 hover:shadow-lg transition-all duration-200"
                                    @click="closeModal"
                                >
                                    Close
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-2 text-[15px] font-medium text-white bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 hover:shadow-lg transition-all duration-200"
                                >
                                    {{ form.processing ? 'Saving...' : 'Save' }}
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
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from "@headlessui/vue";
import { useForm } from "@inertiajs/vue3";

const emit = defineEmits(["update:open"]);

defineProps({
    open: {
        type: Boolean,
        required: true,
    },
    colors: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    name: "",
});

const closeModal = () => {
    form.reset();
    emit("update:open", false);
};

const submit = () => {
    form.post("/colors", {
        onSuccess: () => {
            closeModal();
        },
    });
};
</script>
