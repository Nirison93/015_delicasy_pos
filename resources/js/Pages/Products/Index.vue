<style scoped>
/* Animations */
@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Pagination */
.pagination-btn {
  padding: 10px 16px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 1.5px solid #e2e8f0;
  color: #64748b;
  background: #fff;
  touch-action: manipulation;
  user-select: none;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
.pagination-btn:hover:not(.pagination-disabled) {
  background: #f8fafc;
  border-color: #cbd5e1;
  color: #1e293b;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}
.pagination-active {
  background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%) !important;
  border-color: #1e40af !important;
  color: #fff !important;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25) !important;
}
.pagination-disabled {
  opacity: 0.5;
  cursor: default;
  pointer-events: none;
}

/* Product card animations */
.product-card {
  animation: slideInUp 0.5s ease-out both;
}

.product-card:nth-child(1) { animation-delay: 0.05s; }
.product-card:nth-child(2) { animation-delay: 0.1s; }
.product-card:nth-child(3) { animation-delay: 0.15s; }
.product-card:nth-child(4) { animation-delay: 0.2s; }
.product-card:nth-child(5) { animation-delay: 0.25s; }
.product-card:nth-child(6) { animation-delay: 0.3s; }
</style>

<template>
  <Head title="Products" />
  <Banner />

  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-slate-50">
    <Header />

    <main class="mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-10 py-8">

      <!-- ── Page header bar ── -->
      <div class="mb-8 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 p-8 shadow-lg">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
          <!-- Left: back + title -->
          <div class="flex items-center gap-4">
            <Link href="/" class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm hover:bg-white/30 transition-all duration-300 active:scale-95 shadow-lg">
              <img src="/images/back-arrow.png" class="h-6 w-6" alt="Back" />
            </Link>
            <div>
              <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-white">Food List</h1>
              <p class="text-sm sm:text-[15px] text-blue-100 mt-1">
                <span class="font-semibold text-white">{{ totalProducts }}</span> Total Foods
              </p>
            </div>
          </div>

          <!-- Right: Action buttons -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <Link
              href="/add_promotion"
              :class="[
                'inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3 text-[14px] font-semibold text-white transition-all duration-300 active:scale-95 shadow-lg hover:shadow-xl',
                HasRole(['Admin'])
                  ? 'bg-white/20 backdrop-blur-sm hover:bg-white/30'
                  : 'bg-white/10 cursor-not-allowed opacity-50'
              ]"
              :title="HasRole(['Admin']) ? '' : 'You do not have permission'"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
              Promotion
            </Link>

            <p
              @click="() => { if (HasRole(['Admin'])) { isCreateModalOpen = true; } }"
              :class="[
                'inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3 text-[14px] font-semibold text-white transition-all duration-300 active:scale-95 cursor-pointer shadow-lg hover:shadow-xl',
                HasRole(['Admin'])
                  ? 'bg-gradient-to-r from-emerald-400 to-emerald-500 hover:from-emerald-500 hover:to-emerald-600'
                  : 'bg-emerald-300 cursor-not-allowed opacity-50'
              ]"
              :title="HasRole(['Admin']) ? '' : 'You do not have permission to add more Products'"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
              Add Product
            </p>
          </div>
        </div>
      </div>

      <!-- ── Search & Filters bar ── -->
      <div class="mb-8 space-y-4 lg:space-y-0">
        <!-- Top Row: Toggle + Search -->
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <!-- Food / Bar toggle -->
          <div class="inline-flex items-center gap-1 rounded-xl bg-white p-1.5 shadow-md ring-1 ring-slate-200/50">
            <button
              @click="setCategoryType('0')"
              :class="[
                'flex items-center gap-2 rounded-lg px-5 py-2.5 text-[14px] font-semibold transition-all duration-300 active:scale-95',
                categoryType === '0'
                  ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-md'
                  : 'text-slate-500 hover:text-slate-700'
              ]"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 19.5l-.75-5.25M9 19.5l.75-5.25M8.25 19.5h7.5" />
              </svg>
              Food Item
            </button>
            <button
              @click="setCategoryType('1')"
              :class="[
                'flex items-center gap-2 rounded-lg px-5 py-2.5 text-[14px] font-semibold transition-all duration-300 active:scale-95',
                categoryType === '1'
                  ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-md'
                  : 'text-slate-500 hover:text-slate-700'
              ]"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15M14.25 3.104c.251.023.501.05.75.082M19.8 15a2.25 2.25 0 0 1-2.025 2.25H6.225A2.25 2.25 0 0 1 4.2 15m15.6 0-1.5-4.5" />
              </svg>
              Beverages
            </button>
          </div>

          <!-- Search -->
          <div class="w-full lg:max-w-md">
            <div class="relative group">
              <input
                v-model="search"
                @input="performSearch"
                type="text"
                placeholder="Search products..."
                class="w-full rounded-xl border-2 border-slate-200 bg-white px-5 py-3 pl-12 text-[15px] text-slate-700 placeholder-slate-400 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200 shadow-md"
              />
              <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.5 5.5a7.5 7.5 0 0 0 10.5 10.5Z" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Bottom Row: Filters -->
        <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3 items-start sm:items-center">
          <select
            v-model="selectedCategory"
            @change="applyFilters"
            class="w-full sm:w-auto rounded-xl border-2 border-slate-200 bg-white px-5 py-3 text-[15px] text-slate-600 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200 cursor-pointer appearance-none shadow-md hover:border-slate-300 font-medium"
          >
            <option value="">Filter by Category</option>
            <option
              v-for="category in props.allcategories"
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

          <select
            v-model="sort"
            @change="applyFilters"
            class="w-full sm:w-auto rounded-xl border-2 border-slate-200 bg-white px-5 py-3 text-[15px] text-slate-600 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200 cursor-pointer appearance-none shadow-md hover:border-slate-300 font-medium"
          >
            <option value="">Sort by Price</option>
            <option value="asc">Low to High</option>
            <option value="desc">High to Low</option>
          </select>

          <select
            v-model="size"
            @change="applyFilters"
            class="w-full sm:w-auto rounded-xl border-2 border-slate-200 bg-white px-5 py-3 text-[15px] text-slate-600 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200 cursor-pointer appearance-none shadow-md hover:border-slate-300 font-medium"
          >
            <option value="">Select Size</option>
            <option
              v-for="sizeOption in props.sizes"
              :key="sizeOption.id"
              :value="sizeOption.name"
            >
              {{ sizeOption.name }}
            </option>
          </select>

          <Link
            href="/products"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-slate-100 to-slate-200 px-5 py-3 text-[15px] font-semibold text-slate-700 transition-all duration-300 hover:from-slate-200 hover:to-slate-300 active:scale-95 shadow-md hover:shadow-lg"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.992 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
            </svg>
            Reset
          </Link>
        </div>
      </div>

      <!-- ── Product cards grid ── -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 2xl:grid-cols-6">
        <template v-if="products.data.length > 0">
          <div
            v-for="product in products.data"
            :key="product.id"
            class="product-card group relative overflow-hidden rounded-2xl bg-white shadow-md hover:shadow-2xl ring-1 ring-slate-200/50 transition-all duration-300 hover:-translate-y-2 hover:ring-blue-200/50 flex flex-col"
          >
            <!-- Product image -->
            <div
              @click="() => { openViewModal(product); }"
              class="cursor-pointer relative overflow-hidden bg-gradient-to-br from-slate-100 to-slate-200 flex-shrink-0"
            >
              <img
                v-if="product.image" :src="product.image" :alt="product.name || 'Product Image'"
                class="h-52 w-full object-cover transition-transform duration-500 group-hover:scale-125"
                loading="lazy"
              />
              <div v-else class="h-52 w-full bg-gradient-to-br from-slate-200 to-slate-300 flex items-center justify-center">
                <span class="text-slate-400 text-sm font-medium">No image</span>
              </div>
              
              <!-- Price overlay -->
              <div class="absolute top-3 right-3 rounded-full bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-2 shadow-lg">
                <span class="text-[13px] font-bold text-white">Rs. {{ product.selling_price || "N/A" }}</span>
              </div>
              
              <!-- Gradient overlay on hover -->
              <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              
              <!-- View icon on hover -->
              <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white/90 backdrop-blur-sm shadow-lg">
                  <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                  </svg>
                </div>
              </div>
            </div>

            <!-- Product info -->
            <div class="px-4 pt-3 pb-4 flex flex-col flex-grow">
              <!-- Name -->
              <p class="text-[14px] font-semibold text-slate-800 leading-snug mb-2 line-clamp-2 group-hover:text-blue-600 transition">{{ product.name || "N/A" }}</p>

              <!-- Size tag -->
              <div class="mb-3 flex items-center gap-1">
                <span class="inline-flex items-center gap-1 rounded-lg bg-gradient-to-r from-slate-100 to-slate-50 px-3 py-1.5 text-[11px] font-semibold text-slate-600 border border-slate-200">
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                  </svg>
                  {{ product.size?.name || "N/A" }}
                </span>
              </div>

              <!-- Action buttons -->
              <div class="mt-auto flex items-center gap-2 border-t border-slate-100 pt-3">
                <!-- Duplicate -->
                <button
                  :disabled="!HasRole(['Admin'])"
                  @click="() => { if (HasRole(['Admin'])) { openDuplicateModal(product); } }"
                  :class="[
                    'flex h-9 w-9 items-center justify-center rounded-lg transition-all duration-200 active:scale-90 font-semibold',
                    HasRole(['Admin'])
                      ? 'text-slate-500 hover:bg-slate-100 hover:text-slate-700 hover:shadow-md'
                      : 'text-slate-200 cursor-not-allowed'
                  ]"
                  title="Duplicate"
                >
                  <i class="ri-file-copy-2-line text-[16px]"></i>
                </button>

                <!-- Edit -->
                <button
                  :disabled="!HasRole(['Admin'])"
                  @click="() => { if (HasRole(['Admin'])) { openEditModal(product); } }"
                  :class="[
                    'flex h-9 w-9 items-center justify-center rounded-lg transition-all duration-200 active:scale-90 font-semibold',
                    HasRole(['Admin'])
                      ? 'text-slate-500 hover:bg-blue-100 hover:text-blue-600 hover:shadow-md'
                      : 'text-slate-200 cursor-not-allowed'
                  ]"
                  title="Edit"
                >
                  <i class="ri-pencil-line text-[16px]"></i>
                </button>

                <!-- Spacer -->
                <div class="flex-1"></div>

                <!-- Delete -->
                <button
                  :disabled="!HasRole(['Admin'])"
                  @click="() => { if (HasRole(['Admin'])) { openDeleteModal(product); } }"
                  :class="[
                    'flex h-9 w-9 items-center justify-center rounded-lg transition-all duration-200 active:scale-90 font-semibold',
                    HasRole(['Admin'])
                      ? 'text-slate-500 hover:bg-red-100 hover:text-red-600 hover:shadow-md'
                      : 'text-slate-200 cursor-not-allowed'
                  ]"
                  title="Delete"
                >
                  <i class="ri-delete-bin-line text-[16px]"></i>
                </button>
              </div>
            </div>
          </div>
        </template>

        <!-- Empty state -->
        <template v-else>
          <div class="col-span-full flex flex-col items-center justify-center rounded-2xl bg-gradient-to-br from-white to-slate-50 py-20 shadow-lg ring-1 ring-slate-200">
            <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-blue-100 to-indigo-100">
              <svg class="h-10 w-10 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
              </svg>
            </div>
            <p class="text-lg font-bold text-slate-700">No Products Available</p>
            <p class="mt-2 text-[15px] text-slate-500">Add your first product to get started.</p>
          </div>
        </template>
      </div>

      <!-- ── Pagination ── -->
      <div class="mt-12 flex items-center justify-center gap-2 flex-wrap">
        <!-- Prev Button -->
        <Link
          v-if="products.links[0]"
          @click.prevent="navigateTo(products.links[0].url)"
          :class="[
            'pagination-btn',
            { 'pagination-disabled': !products.links[0].url },
          ]"
        >
          ← Previous
        </Link>

        <!-- Pagination Links -->
        <Link
          v-for="(link, index) in products.links.slice(1, products.links.length - 1)"
          :key="link.label"
          @click.prevent="navigateTo(link.url)"
          :class="['pagination-btn', { 'pagination-active': link.active }]"
          v-html="link.label"
        ></Link>

        <!-- Next Button -->
        <Link
          v-if="products.links[products.links.length - 1]"
          @click.prevent="navigateTo(products.links[products.links.length - 1].url)"
          :class="[
            'pagination-btn',
            { 'pagination-disabled': !products.links[products.links.length - 1].url },
          ]"
        >
          Next →
        </Link>
      </div>

    </main>
  </div>

  <ProductCreateModel
    :categories="allcategories"
    :colors="colors"
    :sizes="sizes"
    :suppliers="suppliers"
    v-model:open="isCreateModalOpen"
  />
  <ProductUpdateModel
    :categories="allcategories"
    :colors="colors"
    :suppliers="suppliers"
    :sizes="sizes"
    v-model:open="isEditModalOpen"
    :selected-product="selectedProduct"
  />

  <ProductDuplicateModel
    :categories="allcategories"
    :colors="colors"
    :suppliers="suppliers"
    :sizes="sizes"
    v-model:open="isDuplicateModalOpen"
    :selected-product="selectedProduct"
  />

  <ProductViewModel
    :categories="allcategories"
    :colors="colors"
    :sizes="sizes"
    v-model:open="isViewModalOpen"
    :selected-product="selectedProduct"
  />
  <ProductDeleteModel
    v-model:open="isDeleteModalOpen"
    :selected-product="selectedProduct"
    @delete="deleteProduct"
  />
  <Footer />
</template>

<script setup>
import { ref } from "vue";
import { Head } from "@inertiajs/vue3";
import { Link, useForm, router } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import { defineProps, onMounted } from "vue";
import ProductCreateModel from "@/Components/custom/ProductCreateModel.vue";

import ProductDuplicateModel from "@/Components/custom/ProductDuplicateModel.vue";
import ProductUpdateModel from "@/Components/custom/ProductUpdateModel.vue";
import ProductViewModel from "@/Components/custom/ProductViewModel.vue";
import ProductDeleteModel from "@/Components/custom/ProductDeleteModel.vue";
import { debounce } from "lodash";
import { HasRole } from "@/Utils/Permissions";

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDuplicateModalOpen = ref(false);
const isViewModalOpen = ref(false);
const selectedProduct = ref(null);
const isDeleteModalOpen = ref(false);

const emit = defineEmits(["update:open"]);

const openEditModal = (product) => {
  selectedProduct.value = product; // Set the selected product
  isEditModalOpen.value = true; // Open the edit modal
};

const openDuplicateModal = (product) => {
  selectedProduct.value = product; // Set the selected product
  isDuplicateModalOpen.value = true; // Open the edit modal
};

const openViewModal = (product) => {
  selectedProduct.value = product; // Set the selected product
  isViewModalOpen.value = true; // Open the view modal
};

const openDeleteModal = (product) => {
  selectedProduct.value = product;
  isDeleteModalOpen.value = true;
};

const props = defineProps({
  products: Object,
  categories: Array,
  suppliers: Array,
  colors: Array,
  sizes: Array,
  allcategories: Array,
  totalProducts: Number,
  search: String,
  sort: String,
  color: String,
  size: String,
  stockStatus: String,
  selectedCategory: String,
  categoryType: String,
});

const search = ref(props.search || "");
const sort = ref(props.sort || "");
const color = ref(props.color || "");
const size = ref(props.size || "");
const suppliers = ref(props.suppliers || "");
const stockStatus = ref(props.stockStatus || "");
const selectedCategory = ref(props.selectedCategory || "");
const categoryType = ref(props.categoryType ?? '0');

const setCategoryType = (type) => {
  categoryType.value = type;
  selectedCategory.value = '';
  applyFilters();
};

const performSearch = debounce(() => {
  applyFilters();
}, 500);

const applyFilters = (page) => {
  router.get(
    route("products.index"),
    {
      search: search.value,
      sort: sort.value,
      color: color.value,
      size: size.value,
      stockStatus: stockStatus.value,
      selectedCategory: selectedCategory.value,
      category_type: categoryType.value,
    },
    { preserveState: true }
  );
};

onMounted(() => {
  // console.log("Products:", props.products);
  // console.table(props.products);
});
const showModal = ref(false);
const form = useForm({});

const openModal = (id) => {
  productToDelete.value = id;
  showModal.value = true;
};
const deleteProduct = (id) => {
  const form = useForm({});
  form.delete(`/products/${id}`, {
    onSuccess: () => {
      isDeleteModalOpen.value = false; // Close the modal on success
    },
    onError: (errors) => {
      console.error("Delete failed:", errors);
    },
  });
};
const navigateTo = (url) => {
  if (!url) return; // Avoid null or undefined URLs

  // Extract the `page` parameter from the URL
  const urlParams = new URLSearchParams(
    new URL(url, window.location.origin).search
  );
  const page = urlParams.get("page");

  // Use Inertia's router.get with current filters
  router.get(
    route("products.index"),
    {
      page, // Add the page parameter
      search: search.value,
      sort: sort.value,
      color: color.value,
      size: size.value,
      stockStatus: stockStatus.value,
      selectedCategory: selectedCategory.value,
      category_type: categoryType.value,
    },
    {
      preserveState: true, // Maintain the current state
      preserveScroll: true, // Prevent scroll reset
    }
  );
};

</script>


