<template>
  <Head title="Waiter" />
  <Banner />
  <div class="waiter-page h-screen flex flex-col overflow-hidden bg-zinc-950">
    <!-- Header -->
    <div class="flex-shrink-0 px-6 py-4 border-b border-white/10 bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Link href="/" class="w-11 h-11 flex items-center justify-center rounded-xl bg-zinc-800 border border-white/10 text-zinc-400 hover:bg-zinc-700 hover:text-white transition">
            <i class="ri-arrow-left-s-line text-2xl"></i>
          </Link>
          <h1 class="text-2xl font-bold text-white tracking-wide uppercase">Waiter</h1>
        </div>
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2 px-4 py-2 bg-zinc-800 rounded-xl border border-white/10">
            <div class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-500/20 ring-1 ring-amber-500/30">
              <i class="ri-user-line text-amber-400 text-base"></i>
            </div>
            <span class="text-[14px] font-semibold text-zinc-200">{{ loggedInUser?.name }}</span>
          </div>
          <Link
            href="/logout"
            method="post"
            as="button"
            class="flex items-center gap-2 px-4 py-2.5 bg-zinc-800 rounded-xl border border-white/10 text-zinc-300 hover:bg-red-500/15 hover:border-red-500/40 hover:text-red-400 transition text-[14px] font-semibold"
          >
            <i class="ri-logout-box-r-line text-lg"></i>
            Logout
          </Link>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="flex gap-4 flex-1 overflow-hidden min-h-0 p-4">
      <!-- Left: Tables -->
      <div class="w-1/3 flex flex-col bg-zinc-900 rounded-2xl border border-white/10 overflow-hidden">
        <div class="p-5 border-b border-white/10">
          <h2 class="text-lg font-bold text-white flex items-center gap-2">
            <i class="ri-layout-grid-line text-amber-400"></i>
            Tables
          </h2>
        </div>
        <div class="overflow-y-auto flex-1 p-4 space-y-3">
          <button
            v-for="table in tables"
            :key="table.id"
            @click="selectTable(table)"
            :class="[
              'w-full p-4 rounded-xl border-2 transition-all duration-200 text-left',
              selectedTable?.id === table.id
                ? 'bg-amber-500/15 border-amber-500 text-white'
                : 'bg-zinc-800 border-white/10 text-zinc-300 hover:border-amber-500/30 hover:bg-zinc-700'
            ]"
          >
            <div class="flex items-center justify-between">
              <span class="font-semibold">Table {{ table.number }}</span>
              <i class="ri-table-2 text-lg"></i>
            </div>
          </button>
        </div>
      </div>

      <!-- Right: Items Selection & Order -->
      <div class="flex-1 flex flex-col gap-4">
        <!-- Category & Item Selection -->
        <div class="flex gap-4 flex-1 overflow-hidden min-h-0">
          <!-- Food Items -->
          <div class="flex-1 flex flex-col bg-zinc-900 rounded-2xl border border-white/10 overflow-hidden">
            <div class="p-5 border-b border-white/10 flex items-center justify-between">
              <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="ri-restaurant-2-line text-orange-400"></i>
                Food Items
              </h2>
              <button
                @click="openFoodModal"
                class="px-3 py-1.5 bg-orange-500 hover:bg-orange-400 text-zinc-900 text-sm font-semibold rounded-lg transition"
              >
                <i class="ri-add-line"></i> Add
              </button>
            </div>
            <div class="overflow-y-auto flex-1 p-4">
              <div v-if="selectedItems.food.length === 0" class="flex items-center justify-center h-full text-zinc-500">
                <p>No food items added yet</p>
              </div>
              <div v-else class="space-y-2">
                <div v-for="(item, idx) in selectedItems.food" :key="`food-${idx}`" class="flex items-center justify-between p-3 bg-zinc-800 rounded-lg border border-white/10">
                  <div class="flex-1">
                    <p class="font-semibold text-white text-sm">{{ item.name }}</p>
                    <p class="text-xs text-zinc-400">Qty: {{ item.quantity }}</p>
                  </div>
                  <button
                    @click="removeItem('food', idx)"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-zinc-400 hover:bg-red-500/15 hover:text-red-400 transition"
                  >
                    <i class="ri-delete-bin-6-line text-lg"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Bar Items -->
          <div class="flex-1 flex flex-col bg-zinc-900 rounded-2xl border border-white/10 overflow-hidden">
            <div class="p-5 border-b border-white/10 flex items-center justify-between">
              <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="ri-wine-line text-red-400"></i>
                Bar Items
              </h2>
              <button
                @click="openBarModal"
                class="px-3 py-1.5 bg-red-500 hover:bg-red-400 text-white text-sm font-semibold rounded-lg transition"
              >
                <i class="ri-add-line"></i> Add
              </button>
            </div>
            <div class="overflow-y-auto flex-1 p-4">
              <div v-if="selectedItems.bar.length === 0" class="flex items-center justify-center h-full text-zinc-500">
                <p>No bar items added yet</p>
              </div>
              <div v-else class="space-y-2">
                <div v-for="(item, idx) in selectedItems.bar" :key="`bar-${idx}`" class="flex items-center justify-between p-3 bg-zinc-800 rounded-lg border border-white/10">
                  <div class="flex-1">
                    <p class="font-semibold text-white text-sm">{{ item.name }}</p>
                    <p class="text-xs text-zinc-400">Qty: {{ item.quantity }}</p>
                  </div>
                  <button
                    @click="removeItem('bar', idx)"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-zinc-400 hover:bg-red-500/15 hover:text-red-400 transition"
                  >
                    <i class="ri-delete-bin-6-line text-lg"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Summary & Submit -->
        <div class="bg-zinc-900 rounded-2xl border border-white/10 p-5">
          <div class="flex items-center justify-between mb-4">
            <div>
              <p class="text-sm text-zinc-400">Selected Table</p>
              <p class="text-xl font-bold text-white">{{ selectedTable ? `Table ${selectedTable.number}` : 'No table selected' }}</p>
            </div>
            <div>
              <p class="text-sm text-zinc-400">Total Items</p>
              <p class="text-2xl font-bold text-amber-400">{{ totalItems }}</p>
            </div>
          </div>
          <button
            @click="submitOrder"
            :disabled="!selectedTable || totalItems === 0 || isSubmitting"
            class="w-full py-3 bg-amber-500 hover:bg-amber-400 disabled:bg-zinc-700 disabled:cursor-not-allowed text-zinc-900 text-lg font-bold rounded-xl transition flex items-center justify-center gap-2 shadow-lg shadow-amber-500/20"
          >
            <i class="ri-send-plane-line"></i>
            {{ isSubmitting ? 'Submitting...' : 'Order Now' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Food Items Modal -->
    <Teleport to="body">
      <div v-if="isFoodModalOpen" class="fixed inset-0 z-[999] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" @click="isFoodModalOpen = false"></div>
        <div class="relative bg-zinc-900 rounded-2xl border border-white/10 shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden">
          <!-- Header -->
          <div class="p-5 border-b border-white/10 flex items-center justify-between bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
              <i class="ri-restaurant-2-line text-orange-400 text-2xl"></i>
              Select Food Items
            </h3>
            <button @click="isFoodModalOpen = false" class="w-10 h-10 flex items-center justify-center rounded-lg text-zinc-400 hover:bg-white/10 hover:text-white transition">
              <i class="ri-close-line text-xl"></i>
            </button>
          </div>
          <!-- Content -->
          <div class="overflow-y-auto flex-1 p-5">
            <div class="grid grid-cols-2 gap-4">
              <div
                v-for="product in foodProducts"
                :key="product.id"
                @click="addItem(product, 'food')"
                class="p-4 bg-zinc-800 rounded-xl border border-white/10 hover:border-orange-500/30 hover:bg-zinc-700 cursor-pointer transition"
              >
                <div class="flex items-start justify-between mb-2">
                  <p class="font-semibold text-white text-sm flex-1">{{ product.name }}</p>
                  <i class="ri-add-circle-fill text-orange-400 text-lg flex-shrink-0"></i>
                </div>
                <p class="text-xs text-zinc-400">Click to add</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Bar Items Modal -->
    <Teleport to="body">
      <div v-if="isBarModalOpen" class="fixed inset-0 z-[999] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" @click="isBarModalOpen = false"></div>
        <div class="relative bg-zinc-900 rounded-2xl border border-white/10 shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden">
          <!-- Header -->
          <div class="p-5 border-b border-white/10 flex items-center justify-between bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
              <i class="ri-wine-line text-red-400 text-2xl"></i>
              Select Bar Items
            </h3>
            <button @click="isBarModalOpen = false" class="w-10 h-10 flex items-center justify-center rounded-lg text-zinc-400 hover:bg-white/10 hover:text-white transition">
              <i class="ri-close-line text-xl"></i>
            </button>
          </div>
          <!-- Content -->
          <div class="overflow-y-auto flex-1 p-5">
            <div class="grid grid-cols-2 gap-4">
              <div
                v-for="product in barProducts"
                :key="product.id"
                @click="addItem(product, 'bar')"
                class="p-4 bg-zinc-800 rounded-xl border border-white/10 hover:border-red-500/30 hover:bg-zinc-700 cursor-pointer transition"
              >
                <div class="flex items-start justify-between mb-2">
                  <p class="font-semibold text-white text-sm flex-1">{{ product.name }}</p>
                  <i class="ri-add-circle-fill text-red-400 text-lg flex-shrink-0"></i>
                </div>
                <p class="text-xs text-zinc-400">Click to add</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Success Notification -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-4"
      >
        <div v-if="showSuccessMsg" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-4 rounded-xl shadow-lg flex items-center gap-2">
          <i class="ri-checkbox-circle-fill text-xl"></i>
          <span class="font-semibold">Order submitted successfully!</span>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import Banner from "@/Components/Banner.vue";
import { ref, computed } from "vue";
import axios from "axios";

const props = defineProps({
  tables: Array,
  allcategories: Array,
  allproducts: Array,
  loggedInUser: Object,
  colors: Array,
  sizes: Array,
});

// State
const selectedTable = ref(null);
const isFoodModalOpen = ref(false);
const isBarModalOpen = ref(false);
const isSubmitting = ref(false);
const showSuccessMsg = ref(false);

const selectedItems = ref({
  food: [],
  bar: [],
});

// Computed properties
const foodProducts = computed(() => {
  return props.allproducts.filter(p => !p.is_beverage).slice(0, 20);
});

const barProducts = computed(() => {
  return props.allproducts.filter(p => p.is_beverage).slice(0, 20);
});

const totalItems = computed(() => {
  const foodQty = selectedItems.value.food.reduce((sum, item) => sum + item.quantity, 0);
  const barQty = selectedItems.value.bar.reduce((sum, item) => sum + item.quantity, 0);
  return foodQty + barQty;
});

// Methods
const selectTable = (table) => {
  selectedTable.value = table;
};

const addItem = (product, category) => {
  const items = selectedItems.value[category];
  const existingItem = items.find(i => i.id === product.id);

  if (existingItem) {
    existingItem.quantity += 1;
  } else {
    items.push({
      id: product.id,
      name: product.name,
      quantity: 1,
    });
  }
};

const removeItem = (category, index) => {
  selectedItems.value[category].splice(index, 1);
};

const openFoodModal = () => {
  isFoodModalOpen.value = true;
};

const openBarModal = () => {
  isBarModalOpen.value = true;
};

const submitOrder = async () => {
  if (!selectedTable.value || totalItems.value === 0) {
    return;
  }

  isSubmitting.value = true;

  try {
    const response = await axios.post('/waiter/submit-order', {
      table_id: selectedTable.value.id,
      products: {
        food: selectedItems.value.food,
        bar: selectedItems.value.bar,
      },
      waiter_id: props.loggedInUser.id,
    });

    if (response.data.success) {
      showSuccessMsg.value = true;
      setTimeout(() => {
        showSuccessMsg.value = false;
      }, 3000);

      // Reset form
      selectedTable.value = null;
      selectedItems.value = { food: [], bar: [] };
    }
  } catch (error) {
    console.error('Error submitting order:', error);
    alert('Failed to submit order. Please try again.');
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<style scoped>
.waiter-page {
  font-family: 'Inter', sans-serif;
}
</style>
