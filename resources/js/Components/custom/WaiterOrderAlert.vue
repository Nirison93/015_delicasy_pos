<template>
  <!-- Waiter Orders Alert Button (top right in POS) -->
  <div class="relative">
    <button
      @click="toggleOrders"
      class="relative flex items-center gap-2 px-4 py-2.5 bg-blue-500/15 rounded-xl border border-blue-500/40 text-blue-400 hover:bg-blue-500/25 hover:text-blue-300 transition text-[14px] font-semibold"
      :class="{ 'ring-anim': hasPendingOrders }"
    >
      <i class="ri-bell-line text-lg" :class="hasPendingOrders ? 'pending-bell-icon' : ''"></i>
      Waiter Orders
      <span
        v-if="hasPendingOrders"
        class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 flex items-center justify-center bg-red-500 text-white text-[11px] font-extrabold rounded-full px-1 ring-2 ring-zinc-950"
      >{{ pendingOrders.length }}</span>
    </button>

    <!-- Dropdown Panel -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 scale-95 translate-y-2"
      enter-to-class="opacity-100 scale-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 scale-100 translate-y-0"
      leave-to-class="opacity-0 scale-95 translate-y-2"
    >
      <div
        v-if="showOrders"
        class="absolute right-0 mt-2 w-96 bg-zinc-900 rounded-xl border border-white/10 shadow-2xl z-50 overflow-hidden"
      >
        <!-- Header -->
        <div class="bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900 px-5 py-4 border-b border-white/10">
          <h3 class="font-bold text-white flex items-center gap-2">
            <i class="ri-bell-line text-blue-400"></i>
            Pending Waiter Orders
          </h3>
        </div>

        <!-- Orders List -->
        <div class="max-h-96 overflow-y-auto">
          <div v-if="pendingOrders.length === 0" class="p-6 text-center text-zinc-400">
            <p class="text-lg">No pending waiter orders</p>
          </div>

          <div v-else class="space-y-2 p-4">
            <div
              v-for="order in pendingOrders"
              :key="order.id"
              class="p-4 bg-zinc-800 rounded-lg border border-blue-500/30 hover:border-blue-500/50 transition"
            >
              <!-- Order Info -->
              <div class="mb-3">
                <div class="flex items-center justify-between mb-2">
                  <span class="font-bold text-blue-400 text-lg">{{ order.order_id }}</span>
                  <span class="text-sm px-2 py-1 bg-blue-500/20 text-blue-300 rounded-full">
                    {{ formatTimeAgo(order.created_at) }}
                  </span>
                </div>
                <p class="text-base text-zinc-300">
                  <i class="ri-user-line"></i>
                  {{ order.waiter?.name || 'Unknown' }} - Table {{ order.table?.number || 'Unknown' }}
                </p>
              </div>

              <!-- Items -->
              <div class="mb-3 p-3 bg-zinc-700/50 rounded border border-white/5 text-base">
                <div v-if="order.products" class="space-y-1 max-h-24 overflow-y-auto">
                  <div v-if="getProductArray('food', order).length > 0">
                    <p class="text-orange-400 font-semibold text-sm mb-1">Food Items:</p>
                    <div v-for="item in getProductArray('food', order)" :key="`food-${item.id}`" class="text-zinc-300 text-sm ml-2">
                      • {{ item.name }} (x{{ item.quantity }})
                    </div>
                  </div>
                  <div v-if="getProductArray('bar', order).length > 0">
                    <p class="text-red-400 font-semibold text-sm mb-1 mt-2">Bar Items:</p>
                    <div v-for="item in getProductArray('bar', order)" :key="`bar-${item.id}`" class="text-zinc-300 text-sm ml-2">
                      • {{ item.name }} (x{{ item.quantity }})
                    </div>
                  </div>
                </div>
                <div v-else class="text-zinc-400 text-sm">
                  No items
                </div>
              </div>

              <!-- Actions -->
              <div class="flex gap-2">
                <button
                  @click="viewOrder(order)"
                  :disabled="isProcessing"
                  class="flex-1 py-2 bg-blue-500/20 hover:bg-blue-500/30 disabled:opacity-50 text-blue-400 text-sm font-semibold rounded transition flex items-center justify-center gap-1"
                >
                  <i class="ri-eye-line"></i>
                  Order View
                </button>
                <button
                  @click="rejectOrder(order.order_id)"
                  :disabled="isProcessing"
                  class="flex-1 py-2 bg-red-500/20 hover:bg-red-500/30 disabled:opacity-50 text-red-400 text-sm font-semibold rounded transition flex items-center justify-center gap-1"
                >
                  <i class="ri-close-line"></i>
                  Reject
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const showOrders = ref(false);
const pendingOrders = ref([]);
const isProcessing = ref(false);
let pollInterval = null;

const hasPendingOrders = computed(() => pendingOrders.value.length > 0);

const toggleOrders = () => {
  showOrders.value = !showOrders.value;
  if (showOrders.value) {
    fetchPendingOrders();
  }
};

const fetchPendingOrders = async () => {
  try {
    const response = await axios.get('/pos/waiter-orders');
    if (response.data && response.data.orders) {
      pendingOrders.value = response.data.orders;
      console.log('Fetched waiter orders:', response.data.orders);
    }
  } catch (error) {
    console.error('Error fetching waiter orders:', error);
  }
};

const confirmOrder = async (orderId) => {
  if (!confirm('Confirm this order?')) return;

  isProcessing.value = true;
  try {
    await axios.post(`/pos/waiter-orders/${orderId}/confirm`, {
      action: 'confirm',
    });
    await fetchPendingOrders();
  } catch (error) {
    console.error('Error confirming order:', error);
    alert('Failed to confirm order');
  } finally {
    isProcessing.value = false;
  }
};

const rejectOrder = async (orderId) => {
  const reason = prompt('Reason for rejection:');
  if (!reason) return;

  isProcessing.value = true;
  try {
    await axios.post(`/pos/waiter-orders/${orderId}/confirm`, {
      action: 'reject',
      reason: reason,
    });
    await fetchPendingOrders();
  } catch (error) {
    console.error('Error rejecting order:', error);
    alert('Failed to reject order');
  } finally {
    isProcessing.value = false;
  }
};

const viewOrder = async (order) => {
  try {
    // Extract product IDs from waiter order
    const foodItems = order.products?.food || [];
    const barItems = order.products?.bar || [];
    const allItems = [...foodItems, ...barItems];
    const productIds = allItems.map(item => item.id);
    
    if (productIds.length === 0) {
      alert('Order has no items');
      return;
    }
    
    // Fetch full product details from database
    const response = await axios.post('/pos/get-products-by-ids', {
      product_ids: productIds,
    });
    
    if (!response.data.products) {
      alert('Failed to load product details');
      return;
    }
    
    // Create product map for easy lookup
    const productMap = {};
    response.data.products.forEach(product => {
      productMap[product.id] = product;
    });
    
    // Merge waiter order quantities with full product details
    const enrichedFoodItems = foodItems.map(item => ({
      ...productMap[item.id],
      quantity: item.quantity,
      apply_discount: false,
    })).filter(item => item.id); // Filter out any missing products
    
    const enrichedBarItems = barItems.map(item => ({
      ...productMap[item.id],
      quantity: item.quantity,
      apply_discount: false,
    })).filter(item => item.id);
    
    // Store the waiter order data in sessionStorage
    sessionStorage.setItem('waiterOrderData', JSON.stringify({
      tableId: order.table_id,
      tableNumber: order.table?.number,
      orderId: order.order_id,
      products: {
        food: enrichedFoodItems,
        bar: enrichedBarItems,
      },
      waiterId: order.waiter_id,
      waiterName: order.waiter?.name,
    }));
    
    // Scroll to top
    window.scrollTo(0, 0);
    
    // Close the notification panel
    showOrders.value = false;
    
    // Emit event to parent to select the table and load the order
    window.dispatchEvent(new CustomEvent('loadWaiterOrder', {
      detail: {
        tableId: order.table_id,
        products: {
          food: enrichedFoodItems,
          bar: enrichedBarItems,
        },
        orderId: order.order_id,
      }
    }));
  } catch (error) {
    console.error('Error loading order:', error);
    alert('Failed to load order. Please try again.');
  }
};

const formatTimeAgo = (dateString) => {
  const date = new Date(dateString);
  const now = new Date();
  const seconds = Math.floor((now - date) / 1000);
  
  if (seconds < 60) return 'just now';
  if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
  return `${Math.floor(seconds / 3600)}h ago`;
};

const getProductArray = (category, order) => {
  try {
    if (!order.products) return [];
    
    // Handle case where products might be a string (shouldn't happen but just in case)
    const products = typeof order.products === 'string' 
      ? JSON.parse(order.products) 
      : order.products;
    
    return products[category] || [];
  } catch (error) {
    console.error(`Error parsing products for ${category}:`, error);
    return [];
  }
};

onMounted(() => {
  // Fetch orders immediately when component mounts
  fetchPendingOrders();
  
  // Poll for new orders every 3 seconds continuously
  pollInterval = setInterval(() => {
    fetchPendingOrders();
  }, 3000);
});

onUnmounted(() => {
  if (pollInterval) {
    clearInterval(pollInterval);
  }
});
</script>

<style scoped>
@keyframes ring-anim {
  0% {
    box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
  }
  50% {
    box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
  }
}

.ring-anim {
  animation: ring-anim 2s infinite;
}

.pending-bell-icon {
  animation: ring-anim 2s infinite;
}
</style>
