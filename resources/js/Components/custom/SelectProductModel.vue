<template>
   <TransitionRoot as="template" :show="open">
      <Dialog class="relative z-10" @close="closeModal">

         <!-- Backdrop -->
         <TransitionChild
            as="template"
            enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
            leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0"
         >
            <div class="fixed inset-0 bg-black/75 backdrop-blur-sm transition-opacity" />
         </TransitionChild>

         <!-- Full-screen panel -->
         <div class="fixed inset-0 z-10">
            <TransitionChild
               as="template"
               enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 scale-[0.98]" enter-to="opacity-100 translate-y-0 scale-100"
               leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 scale-100" leave-to="opacity-0 translate-y-4 scale-[0.98]"
            >
               <DialogPanel class="select-product-modal bg-zinc-900 w-full h-full flex flex-col">

                  <!-- ── Header / Filters ── -->
                  <div class="flex-shrink-0 bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900 border-b border-white/10 px-5 py-3">
                     <div class="flex items-center gap-3">

                        <!-- Title badge -->
                        <div class="flex items-center gap-2.5 mr-2">
                           <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/20 ring-1 ring-amber-500/40">
                              <i class="ri-restaurant-line text-xl text-amber-400"></i>
                           </div>
                           <span class="text-2xl font-bold tracking-tight text-white hidden sm:block">
                              {{ categoryType === '0' ? 'Food Menu' : categoryType === '1' ? 'Beverages Menu' : 'Food &amp; Beverages' }}
                           </span>
                        </div>

                        <!-- Type filter -->
                        <select
                           v-model="categoryType"
                           @change="onCategoryTypeChange"
                           class="h-11 pl-3 pr-8 text-base font-medium text-white bg-zinc-800 border border-white/10 rounded-xl cursor-pointer focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition"
                        >
                           <option value="" class="bg-zinc-800">All Types</option>
                           <option value="0" class="bg-zinc-800">🍽️ Food</option>
                           <option value="1" class="bg-zinc-800">🍹 Beverages</option>
                        </select>

                        <!-- Category filter -->
                        <select
                           v-model="selectedCategory"
                           @change="() => fetchProducts()"
                           class="h-11 pl-3 pr-8 text-base font-medium text-white bg-zinc-800 border border-white/10 rounded-xl cursor-pointer focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition"
                        >
                           <option value="" class="bg-zinc-800">Category</option>
                           <option
                              v-for="category in allcategoriesFiltered"
                              :key="category.id"
                              :value="category.id"
                              class="bg-zinc-800"
                           >
                              {{ category.hierarchy_string ? category.hierarchy_string + " → " + category.name : category.name }}
                           </option>
                        </select>

                        <!-- Price sort -->
                        <select
                           v-model="sort"
                           @change="() => fetchProducts()"
                           class="h-11 pl-3 pr-8 text-base font-medium text-white bg-zinc-800 border border-white/10 rounded-xl cursor-pointer focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition"
                        >
                           <option value="" class="bg-zinc-800">Price</option>
                           <option value="asc" class="bg-zinc-800">Low → High</option>
                           <option value="desc" class="bg-zinc-800">High → Low</option>
                        </select>

                        <!-- Size filter -->
                        <select
                           v-model="size"
                           @change="() => fetchProducts()"
                           class="h-11 pl-3 pr-8 text-base font-medium text-white bg-zinc-800 border border-white/10 rounded-xl cursor-pointer focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition"
                        >
                           <option value="" class="bg-zinc-800">Size</option>
                           <option
                              v-for="sizeOption in sizes"
                              :key="sizeOption.id"
                              :value="sizeOption.name"
                              class="bg-zinc-800"
                           >
                              {{ sizeOption.name }}
                           </option>
                        </select>

                        <!-- Reset button -->
                        <button
                           @click="resetFilters"
                           class="h-11 w-11 flex-shrink-0 flex items-center justify-center rounded-xl bg-zinc-800 border border-white/10 text-zinc-400 hover:text-amber-400 hover:border-amber-500/50 active:scale-95 transition"
                           title="Reset Filters"
                        >
                           <i class="ri-refresh-line text-2xl"></i>
                        </button>

                        <!-- Search -->
                        <div class="flex-1 min-w-[160px]">
                           <div class="relative">
                              <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-zinc-500 text-2xl"></i>
                              <input
                                 v-model="search"
                                 @input="() => fetchProducts()"
                                 type="text"
                                 placeholder="Search products..."
                                 class="w-full h-11 pl-10 pr-4 text-base font-medium text-white bg-zinc-800 border border-white/10 rounded-xl placeholder-zinc-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition"
                              />
                           </div>
                        </div>

                        <!-- Close button -->
                        <button
                           @click.prevent="closeModal(false)"
                           class="h-11 w-11 flex-shrink-0 flex items-center justify-center rounded-xl bg-white/10 text-zinc-400 hover:bg-white/20 hover:text-white active:scale-95 transition"
                           title="Close"
                        >
                           <i class="ri-close-line text-2xl"></i>
                        </button>
                     </div>
                  </div>

                  <!-- ── Main: Sidebar + Products ── -->
                  <div class="flex-1 flex overflow-hidden">

                     <!-- Left: Category Sidebar -->
                     <div class="w-[156px] flex-shrink-0 bg-zinc-900 border-r border-white/10 flex flex-col">
                        <div class="px-3 py-2.5 border-b border-white/10">
                           <p class="text-sm font-bold text-zinc-500 uppercase tracking-widest">Categories</p>
                        </div>
                        <div class="flex-1 overflow-y-auto category-sidebar py-2 px-2 space-y-1" ref="categoryScroll">

                           <!-- All -->
                           <div
                              class="cursor-pointer"
                              @click="selectedParentCategory = null; selectedCategory = ''; fetchProducts()"
                           >
                              <div
                                 :class="[
                                    'rounded-xl p-2 flex flex-col items-center transition-all duration-200',
                                    !selectedParentCategory
                                       ? 'bg-amber-500/15 ring-1 ring-amber-500/50'
                                       : 'hover:bg-zinc-800'
                                 ]"
                              >
                                 <div class="w-16 h-16 rounded-full bg-zinc-800 flex items-center justify-center mb-1 ring-1 ring-white/10">
                                    <i class="ri-apps-line text-lg" :class="!selectedParentCategory ? 'text-amber-400' : 'text-zinc-400'"></i>
                                 </div>
                                 <p class="text-base font-semibold text-center" :class="!selectedParentCategory ? 'text-amber-400' : 'text-zinc-400'">All</p>
                              </div>
                           </div>

                           <!-- Category items -->
                           <div
                              v-for="category in filteredCategories"
                              :key="category.id"
                              class="cursor-pointer"
                              @click="selectParentCategory(category)"
                           >
                              <div
                                 :class="[
                                    'rounded-xl p-2 flex flex-col items-center transition-all duration-200',
                                    selectedParentCategory?.id === category.id
                                       ? 'bg-amber-500/15 ring-1 ring-amber-500/50'
                                       : 'hover:bg-zinc-800'
                                 ]"
                              >
                                 <img
                                    :src="category.image ? `${category.image}` : '/images/category-placeholder.png'"
                                    alt="category image"
                                    class="w-16 h-16 object-cover rounded-full mb-1 ring-1 ring-white/10"
                                 />
                                 <p
                                    class="text-xl font-semibold text-center leading-tight w-full"
                                    :class="selectedParentCategory?.id === category.id ? 'text-amber-400' : 'text-zinc-400'"
                                 >
                                    {{ category.name }}
                                 </p>
                              </div>
                           </div>
                        </div>
                     </div>

                     <!-- Right: Products Area -->
                     <div class="flex-1 overflow-hidden p-4 bg-zinc-900">
                        <div ref="scrollAreaRef" class="h-full overflow-y-auto scroll-area pr-1">

                           <!-- Loading -->
                           <template v-if="loading && isInitialLoad">
                              <div class="flex flex-col items-center justify-center py-20">
                                 <i class="ri-loader-4-line text-4xl text-zinc-600 animate-spin"></i>
                                 <p class="mt-3 text-base text-zinc-500 font-medium">Loading products…</p>
                              </div>
                           </template>

                           <template v-else>
                              <template v-if="(products.data?.length || 0) > 0">
                                 <!-- Product Grid -->
                                 <div class="grid gap-3 grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 2xl:grid-cols-6 pb-4">
                                    <div
                                       v-for="product in products.data.filter((product) => !(hidePromotions && product.is_promotion))"
                                       :key="product.id"
                                       @click="product.stock_quantity > 0 && selectProduct(product)"
                                       class="relative bg-zinc-800 rounded-2xl border border-white/10 hover:border-amber-500/40 hover:shadow-lg hover:shadow-amber-500/5 transition-all duration-200 cursor-pointer flex flex-col overflow-hidden active:scale-[0.97]"
                                       :class="{
                                          'border-amber-500 bg-amber-500/10 ring-1 ring-amber-500/60': selectedProducts.find((p) => p.id === product.id),
                                          'opacity-40 cursor-not-allowed': product.stock_quantity <= 0
                                       }"
                                    >
                                       <!-- Selected Checkmark -->
                                       <span
                                          v-if="selectedProducts.find((p) => p.id === product.id)"
                                          class="absolute top-2 right-2 flex items-center justify-center w-6 h-6 bg-amber-500 text-zinc-900 text-xs font-bold rounded-full shadow-md z-10"
                                       >
                                          <i class="ri-check-line"></i>
                                       </span>

                                       <!-- Out of Stock Badge -->
                                       <span
                                          v-if="product.stock_quantity <= 0"
                                          class="absolute top-2 left-2 px-2 py-0.5 bg-red-500/90 text-white text-sm font-bold rounded-lg z-10"
                                       >
                                          Out of Stock
                                       </span>

                                       <!-- Product Image -->
                                       <div class="h-[130px] w-full bg-zinc-700 overflow-hidden">
                                          <img
                                             :src="product.image ? `${product.image}` : '/images/placeholder.jpg'"
                                             alt="Product Image"
                                             class="w-full h-full object-cover"
                                          />
                                       </div>

                                       <!-- Product Info -->
                                       <div class="p-3 flex-1 flex flex-col justify-between">
                                          <div>
                                             <h3 class="text-xl font-semibold text-white line-clamp-2 leading-snug">
                                                {{ product.name || "N/A" }}
                                             </h3>
                                             <p class="text-lg text-zinc-500 mt-0.5">
                                                Size: {{ product.size?.name || "N/A" }}
                                             </p>
                                          </div>
                                          <div class="flex items-center justify-between mt-2">
                                             <p class="text-2xl font-bold text-amber-400">
                                                Rs {{ product.selling_price || "N/A" }}
                                             </p>
                                             <div v-if="product.is_promotion">
                                                <p
                                                   @click.stop="viewPromotion(product)"
                                                   class="text-sm font-semibold text-amber-400 bg-amber-500/15 ring-1 ring-amber-500/40 px-2 py-0.5 rounded-lg hover:bg-amber-500/25 transition"
                                                >
                                                   Promo
                                                </p>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </template>

                              <template v-else>
                                 <div class="flex flex-col items-center justify-center py-20">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-800 ring-1 ring-white/10 mb-4">
                                       <i class="ri-shopping-basket-line text-3xl text-zinc-600"></i>
                                    </div>
                                    <p class="text-base text-zinc-500 font-medium">No products available</p>
                                 </div>
                              </template>
                           </template>
                        </div>
                     </div>
                  </div>
                  <!-- /Main Content -->

                  <!-- ── Footer ── -->
                  <div class="flex-shrink-0 flex items-center justify-between px-6 py-4 bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900 border-t border-white/10">
                     <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-amber-500/20 ring-1 ring-amber-500/40 text-amber-400 text-sm font-bold">
                           {{ selectedProducts.length }}
                        </span>
                        <p class="text-base text-zinc-400 font-medium">item(s) selected</p>
                     </div>
                     <div class="flex gap-3">
                        <button
                           class="h-11 px-6 text-base font-semibold rounded-xl border border-white/10 bg-zinc-800 text-zinc-300 hover:border-zinc-600 hover:text-white active:scale-95 transition"
                           @click.prevent="closeModal(false)"
                        >
                           Cancel
                        </button>
                        <button
                           class="h-11 px-7 text-base font-bold rounded-xl bg-amber-500 text-zinc-900 shadow-lg shadow-amber-500/20 hover:bg-amber-400 active:scale-95 transition inline-flex items-center gap-2"
                           @click.prevent="closeModal(true)"
                        >
                           <i class="ri-download-line text-lg"></i>
                           Import
                        </button>
                     </div>
                  </div>

                  <PromotionItemModal
                     :open="itemModalOpen"
                     @update:open="itemModalOpen = $event"
                     :product="selectedPromotion"
                  />

               </DialogPanel>
            </TransitionChild>
         </div>
      </Dialog>
   </TransitionRoot>
</template>

<script setup>
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from "@headlessui/vue";
import { onMounted, onBeforeUnmount, ref, watch, computed, nextTick } from "vue";
import { useForm } from "@inertiajs/vue3";
import PromotionItemModal from "@/Components/custom/PromotionItemModal.vue";

const itemModalOpen = ref(false);

const products = ref({ data: [] });
const loading = ref(false);
const isInitialLoad = ref(true);
const isAppending = ref(false);

const search = ref("");
const selectedCategory = ref("");
const categoryType = ref(""); // NEW: Category type filter
const sort = ref("");
const size = ref("");

const selectedParentCategory = ref(null);
const parentCategories = ref([]);
const allcategoriesFiltered = ref([]);

const selectedPromotion = ref(null);
const selectedProducts = ref([]);

const nextPageUrl = ref(null);
const scrollAreaRef = ref(null);
const categoryScroll = ref(null);
let observer = null;

const selectProduct = (product) => {
  const index = selectedProducts.value.findIndex((p) => p.id === product.id);
  if (index === -1) {
    selectedProducts.value.push(product);
  } else {
    selectedProducts.value.splice(index, 1);
  }
};

const viewPromotion = (product) => {
  selectedPromotion.value = product;
  itemModalOpen.value = true;
};

const resetFilters = () => {
  search.value = "";
  selectedCategory.value = "";
  categoryType.value = "";
  sort.value = "";
  size.value = "";
  selectedParentCategory.value = null;
  fetchProducts();
  fetchParentCategories();
};

 
const emit = defineEmits(["update:open", "selected-products"]);

const { open, allcategories, colors, sizes, hidePromotions, menuType } = defineProps({
  open: { type: Boolean, required: true },
  allcategories: Array,
  colors: Array,
  sizes: Array,
  hidePromotions: { type: Boolean, required: false, default: false },
  menuType: { type: String, required: false, default: "" },
});

allcategoriesFiltered.value = allcategories ?? [];

const form = useForm({});

const closeModal = (triggerImport = false) => {
   
  emit("update:open", false);

  if (triggerImport) {
    emit("selected-products", selectedProducts.value);
  }
  selectedProducts.value = [];
};

// When category type changes, reset selections and fetch
const onCategoryTypeChange = () => {
  selectedCategory.value = "";
  selectedParentCategory.value = null;
  fetchParentCategories();
  fetchProducts();
};

const fetchProducts = async (url = "/api/products", append = false) => {
  if (append) {
    isAppending.value = true;
  } else {
    loading.value = true;
    isInitialLoad.value = true;
  }

  try {
    const response = await axios.post(url, {
      search: search.value,
      selectedCategory: selectedCategory.value,
      category_type: categoryType.value, // Send category type
      sort: sort.value,
      size: size.value,
    });

    const payload = response.data.products ?? response.data;
    nextPageUrl.value = payload.next_page_url ?? null;

    if (append) {
      const current = products.value.data || [];
      const incoming = payload.data || [];
      products.value = { ...payload, data: [...current, ...incoming] };
    } else {
      products.value = payload;
      if (scrollAreaRef.value) {
        scrollAreaRef.value.scrollTop = 0;
      }
    }
  } catch (error) {
    console.error("Error fetching products:", error);
  } finally {
    loading.value = false;
    isInitialLoad.value = false;
    isAppending.value = false;
  }
};

const fetchParentCategories = async (url = "/api/top-categories") => {
  try {
    const response = await axios.post(url, {
      category_type: categoryType.value, // Send category type
    });
    parentCategories.value = response.data.categories;
  } catch (error) {
    console.error("Error fetching categories:", error);
  }
};

const filteredCategories = computed(() => {
   if (!allcategories) return [];
   return allcategories.filter(category => {
      if (!categoryType.value) return true;
      return category.category_type === parseInt(categoryType.value);
   });
});

const selectParentCategory = (category) => {
   selectedParentCategory.value = category;
   selectedCategory.value = category.id;
   // Don't clear allcategoriesFiltered
   fetchProducts();
   
   // Scroll the selected category into view
   nextTick(() => {
      const element = categoryScroll.value.querySelector(`[data-category-id="${category.id}"]`);
      if (element) {
         element.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
      }
   });
};

const setupObserver = () => {
  if (!scrollAreaRef.value) return;

  observer = new IntersectionObserver(
    (entries) => {
      const entry = entries[0];
      if (entry.isIntersecting && nextPageUrl.value && !isAppending.value) {
        fetchProducts(nextPageUrl.value, true);
      }
    },
    {
      root: scrollAreaRef.value,
      rootMargin: "0px 0px 200px 0px",
      threshold: 0.01,
    }
  );
};

onMounted(() => {
  fetchProducts();
  fetchParentCategories();
  setTimeout(() => setupObserver(), 0);
});

onBeforeUnmount(() => {
  if (observer) {
    observer.disconnect();
    observer = null;
  }
});

watch(
  () => open,
  (val) => {
    if (val) {
      // Apply the menuType filter when the modal opens
      categoryType.value = menuType ?? "";
      onCategoryTypeChange();
      setTimeout(() => {
        if (observer) observer.disconnect();
        setupObserver();
      }, 0);
    } else if (observer) {
      observer.disconnect();
      observer = null;
    }
  }
);

// Add new watch for categoryType
watch(
  () => categoryType.value,
  (newValue) => {
    if (allcategories) {
      allcategoriesFiltered.value = allcategories.filter(category => {
        // If no category type is selected, show all categories
        if (!newValue) return true;
        // Filter categories based on category_type
        return category.category_type === parseInt(newValue);
      });
    }
  },
  { immediate: true }
);

const scrollCategories = (direction) => {
   const container = categoryScroll.value;
   const scrollAmount = 200;
   
   if (direction === 'left') {
      container.scrollBy({ top: -scrollAmount, behavior: 'smooth' });
   } else {
      container.scrollBy({ top: scrollAmount, behavior: 'smooth' });
   }
};
</script>

<style scoped>
/* Scroll area for products */
.scroll-area {
  scrollbar-width: thin;
  scrollbar-color: rgb(63 63 70) transparent;
}

.scroll-area::-webkit-scrollbar {
  width: 5px;
}

.scroll-area::-webkit-scrollbar-track {
  background: transparent;
}

.scroll-area::-webkit-scrollbar-thumb {
  background-color: rgb(63 63 70);
  border-radius: 3px;
}

.scroll-area::-webkit-scrollbar-thumb:hover {
  background-color: rgb(82 82 91);
}

/* Category sidebar scroll */
.category-sidebar {
  scrollbar-width: thin;
  scrollbar-color: rgb(63 63 70) transparent;
}

.category-sidebar::-webkit-scrollbar {
  width: 3px;
}

.category-sidebar::-webkit-scrollbar-track {
  background: transparent;
}

.category-sidebar::-webkit-scrollbar-thumb {
  background-color: rgb(63 63 70);
  border-radius: 2px;
}

.category-sidebar::-webkit-scrollbar-thumb:hover {
  background-color: rgb(82 82 91);
}

/* Hide scrollbar utility */
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

/* Line clamp utility */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Spin animation for loader */
@keyframes spin {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}
.animate-spin {
  animation: spin 1s linear infinite;
}
</style>