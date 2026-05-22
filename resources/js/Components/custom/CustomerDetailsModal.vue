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
                <div class="fixed inset-0 bg-black/75 backdrop-blur-sm transition-opacity" />
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
                    <DialogPanel class="relative w-full max-w-lg transform transition-all">
                        <div class="bg-zinc-900 rounded-2xl shadow-2xl border border-white/10 overflow-hidden">

                            <!-- Header -->
                            <div class="flex items-center justify-between px-6 py-5 border-b border-white/10 bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 flex items-center justify-center rounded-xl bg-amber-500/20 ring-1 ring-amber-500/30">
                                        <i class="ri-user-3-line text-xl text-amber-400"></i>
                                    </div>
                                    <div>
                                        <DialogTitle class="text-lg font-bold text-white">Customer Details</DialogTitle>
                                        <p class="text-[12px] text-zinc-400 mt-0.5">Add or update customer information</p>
                                    </div>
                                </div>
                                <button
                                    @click="$emit('update:open', false)"
                                    class="w-10 h-10 flex items-center justify-center rounded-lg text-zinc-400 hover:bg-white/10 hover:text-white transition"
                                >
                                    <i class="ri-close-line text-xl"></i>
                                </button>
                            </div>

                            <!-- Form -->
                            <div class="px-6 py-6 space-y-4">

                                <!-- Customer Name -->
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 text-[14px] font-semibold text-zinc-200">
                                        <i class="ri-user-line text-amber-400 text-base"></i>
                                        Customer Name
                                    </label>
                                    <input
                                        v-model.trim="customerData.name"
                                        type="text"
                                        placeholder="Enter customer name"
                                        class="w-full h-11 px-4 text-[15px] text-white placeholder-zinc-500 bg-zinc-800 ring-1 ring-white/10 border-0 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition"
                                    />
                                </div>

                                <!-- Contact Number & Country Code -->
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 text-[14px] font-semibold text-zinc-200">
                                        <i class="ri-phone-line text-amber-400 text-base"></i>
                                        Contact Number
                                    </label>
                                    <div class="flex gap-3">
                                        <input
                                            v-model="customerData.contactNumber"
                                            type="text"
                                            placeholder="Enter phone number"
                                            class="flex-1 h-11 px-4 text-[15px] text-white placeholder-zinc-500 bg-zinc-800 ring-1 ring-white/10 border-0 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition"
                                        />
                                        <button
                                            @click="$emit('search')"
                                            class="h-11 px-6 bg-amber-500 hover:bg-amber-400 text-zinc-900 text-[15px] font-semibold rounded-lg active:scale-95 transition flex items-center gap-2 flex-shrink-0 shadow-lg shadow-amber-500/20"
                                        >
                                            <i class="ri-search-line text-base"></i>
                                            Search
                                        </button>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 text-[14px] font-semibold text-zinc-200">
                                        <i class="ri-mail-line text-amber-400 text-base"></i>
                                        Email Address
                                    </label>
                                    <input
                                        v-model="customerData.email"
                                        type="email"
                                        placeholder="Enter email address"
                                        class="w-full h-11 px-4 text-[15px] text-white placeholder-zinc-500 bg-zinc-800 ring-1 ring-white/10 border-0 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition"
                                    />
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex gap-3 pt-2">
                                    <button
                                        @click="saveCustomer"
                                        class="flex-1 h-11 bg-amber-500 hover:bg-amber-400 text-zinc-900 text-[15px] font-bold rounded-lg active:scale-[0.98] transition flex items-center justify-center gap-2 shadow-lg shadow-amber-500/20"
                                    >
                                        <i class="ri-save-line text-base"></i>
                                        Save
                                    </button>
                                    <button
                                        @click="$emit('update:open', false)"
                                        class="flex-1 h-11 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-[15px] font-semibold rounded-lg active:scale-[0.98] transition border border-white/10"
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
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from "@headlessui/vue";
import { ref, watch } from 'vue';

const props = defineProps({
    open: Boolean,
    customer: Object
});

const emit = defineEmits(['update:open', 'update:customer', 'search']);

const customerData = ref({
    name: '',
    contactNumber: '',
    email: '',
    ...props.customer
});

watch(() => props.customer, (newVal) => {
    customerData.value = {
        name: '',
        contactNumber: '',
        email: '',
        ...newVal
    };
}, { deep: true });

const saveCustomer = () => {
    emit('update:customer', customerData.value);
    emit('update:open', false);
};
</script>

<style scoped>
/* Date input calendar icon for light theme */
input[type="date"]::-webkit-calendar-picker-indicator {
    opacity: 0.5;
    cursor: pointer;
}
input[type="date"]::-webkit-calendar-picker-indicator:hover {
    opacity: 0.8;
}
</style>
