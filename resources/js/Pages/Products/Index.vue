<style scoped>
/* Pagination */
.pagination-btn {
  padding: 10px 16px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s ease;
  border: 1px solid #e2e8f0;
  color: #475569;
  background: #fff;
  touch-action: manipulation;
  user-select: none;
}
.pagination-btn:hover {
  background: #f1f5f9;
  border-color: #94a3b8;
  color: #1e293b;
}
.pagination-active {
  background: #1e293b !important;
  border-color: #1e293b !important;
  color: #fff !important;
}
.pagination-disabled {
  opacity: 0.4;
  cursor: default;
  pointer-events: none;
}
</style>

<template>
  <Head title="Products" />
  <Banner />

  <div class="min-h-screen bg-slate-50">
    <Header />

    <main class="mx-auto max-w-[1600px] px-6 py-8 lg:px-10">

      <!-- ── Page header bar ── -->
      <div class="mb-6 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
        <!-- Left: back + title -->
        <div class="flex items-center gap-4">
          <Link href="/" class="flex h-12 w-12 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-slate-200 transition hover:shadow-md active:scale-95">
            <img src="/images/back-arrow.png" class="h-6 w-6" alt="Back" />
          </Link>
          <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-800">Products</h1>
            <p class="text-[15px] text-slate-400">
              <span class="font-semibold text-slate-600">{{ totalProducts }}</span> total products
            </p>
          </div>
        </div>

        <!-- Right: Action buttons -->
        <div class="flex items-center gap-3">
          <Link
            href="/add_promotion"
            :class="[
              'inline-flex items-center gap-2 rounded-xl px-6 py-3.5 text-[15px] font-semibold text-white shadow-sm transition active:scale-95',
              HasRole(['Admin'])
                ? 'bg-slate-800 hover:bg-slate-900'
                : 'bg-slate-400 cursor-not-allowed opacity-60'
            ]"
            :title="HasRole(['Admin']) ? '' : 'You do not have permission'"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add Promotion
          </Link>

          <p
            @click="() => { if (HasRole(['Admin'])) { isCreateModalOpen = true; } }"
            :class="[
              'inline-flex items-center gap-2 rounded-xl px-6 py-3.5 text-[15px] font-semibold text-white shadow-sm transition active:scale-95 cursor-pointer',
              HasRole(['Admin'])
                ? 'bg-blue-600 hover:bg-blue-700'
                : 'bg-blue-400 cursor-not-allowed opacity-60'
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

      <!-- ── Search & Filters bar ── -->
      <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <!-- Left: Toggle buttons + Search -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <!-- Food / Bar toggle -->
          <div class="flex items-center gap-1 rounded-xl bg-slate-200 p-1">
            <button
              @click="setCategoryType('0')"
              :class="[
                'flex items-center gap-2 rounded-lg px-5 py-2.5 text-[14px] font-semibold transition-all duration-200 active:scale-95',
                categoryType === '0'
                  ? 'bg-white text-emerald-700 shadow-sm'
                  : 'text-slate-500 hover:text-slate-700'
              ]"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 19.5l-.75-5.25M9 19.5l.75-5.25M8.25 19.5h7.5" />
              </svg>
              Food Item
            </button>
            <button
              @click="setCategoryType('1')"
              :class="[
                'flex items-center gap-2 rounded-lg px-5 py-2.5 text-[14px] font-semibold transition-all duration-200 active:scale-95',
                categoryType === '1'
                  ? 'bg-white text-amber-700 shadow-sm'
                  : 'text-slate-500 hover:text-slate-700'
              ]"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15M14.25 3.104c.251.023.501.05.75.082M19.8 15a2.25 2.25 0 0 1-2.025 2.25H6.225A2.25 2.25 0 0 1 4.2 15m15.6 0-1.5-4.5" />
              </svg>
              Bar Item
            </button>
          </div>

          <!-- Search -->
          <div class="w-full sm:w-80">
          <input
            v-model="search"
            @input="performSearch"
            type="text"
            placeholder="Search products..."
            class="w-full rounded-xl border border-slate-200 bg-white px-5 py-3.5 text-[15px] text-slate-700 placeholder-slate-400 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200"
          />
          </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap items-center gap-3">
          <select
            v-model="selectedCategory"
            @change="applyFilters"
            class="rounded-xl border border-slate-200 bg-white px-5 py-3.5 text-[15px] text-slate-600 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200 cursor-pointer appearance-none"
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
            class="rounded-xl border border-slate-200 bg-white px-5 py-3.5 text-[15px] text-slate-600 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200 cursor-pointer appearance-none"
          >
            <option value="">Filter by Price</option>
            <option value="asc">Ascending</option>
            <option value="desc">Descending</option>
          </select>

          <select
            v-model="size"
            @change="applyFilters"
            class="rounded-xl border border-slate-200 bg-white px-5 py-3.5 text-[15px] text-slate-600 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200 cursor-pointer appearance-none"
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
            class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-5 py-3.5 text-[15px] font-medium text-slate-600 transition hover:bg-slate-200 active:scale-95"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.992 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
            </svg>
            Reset
          </Link>
        </div>
      </div>

      <!-- ── Product cards grid ── -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <template v-if="products.data.length > 0">
          <div
            v-for="product in products.data"
            :key="product.id"
            class="group relative overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/60 transition-all duration-300 hover:shadow-lg hover:-translate-y-1"
          >
            <!-- Product image -->
            <div
              @click="() => { openViewModal(product); }"
              class="cursor-pointer relative overflow-hidden bg-slate-100"
            >
              <img
                v-if="product.image"
                :src="getImageUrl(product.image)"
                :alt="product.name || 'Product Image'"
                class="h-52 w-full object-cover transition-transform duration-500 group-hover:scale-110"
                loading="lazy"
              />
              <div v-else class="h-52 w-full bg-slate-200 flex items-center justify-center">
                <span class="text-slate-400 text-sm">No image</span>
              </div>
              <!-- Price overlay -->
              <div class="absolute top-3 right-3 rounded-full bg-white/90 backdrop-blur-sm px-3.5 py-1.5 shadow-sm">
                <span class="text-[14px] font-bold text-slate-800">Rs. {{ product.selling_price || "N/A" }}</span>
              </div>
              <!-- Gradient overlay on hover -->
              <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </div>

            <!-- Product info -->
            <div class="px-5 pt-4 pb-5">
              <!-- Name -->
              <p class="text-[15px] font-semibold text-slate-800 leading-snug mb-1 line-clamp-2">{{ product.name || "N/A" }}</p>

              <!-- Size tag -->
              <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-[12px] font-medium text-slate-500">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                </svg>
                {{ product.size?.name || "N/A" }}
              </span>

              <!-- Action buttons -->
              <div class="mt-4 flex items-center gap-2 border-t border-slate-100 pt-4">
                <!-- Duplicate -->
                <button
                  :disabled="!HasRole(['Admin'])"
                  @click="() => { if (HasRole(['Admin'])) { openDuplicateModal(product); } }"
                  :class="[
                    'flex h-9 w-9 items-center justify-center rounded-lg transition-all duration-200 active:scale-90',
                    HasRole(['Admin'])
                      ? 'text-slate-400 hover:bg-slate-800 hover:text-white'
                      : 'text-slate-200 cursor-not-allowed'
                  ]"
                  title="Duplicate"
                >
                  <i class="ri-file-copy-2-line text-[15px]"></i>
                </button>

                <!-- Edit -->
                <button
                  :disabled="!HasRole(['Admin'])"
                  @click="() => { if (HasRole(['Admin'])) { openEditModal(product); } }"
                  :class="[
                    'flex h-9 w-9 items-center justify-center rounded-lg transition-all duration-200 active:scale-90',
                    HasRole(['Admin'])
                      ? 'text-slate-400 hover:bg-blue-600 hover:text-white'
                      : 'text-slate-200 cursor-not-allowed'
                  ]"
                  title="Edit"
                >
                  <i class="ri-pencil-line text-[15px]"></i>
                </button>

                <!-- Spacer -->
                <div class="flex-1"></div>

                <!-- Delete -->
                <button
                  :disabled="!HasRole(['Admin'])"
                  @click="() => { if (HasRole(['Admin'])) { openDeleteModal(product); } }"
                  :class="[
                    'flex h-9 w-9 items-center justify-center rounded-lg transition-all duration-200 active:scale-90',
                    HasRole(['Admin'])
                      ? 'text-slate-400 hover:bg-red-500 hover:text-white'
                      : 'text-slate-200 cursor-not-allowed'
                  ]"
                  title="Delete"
                >
                  <i class="ri-delete-bin-line text-[15px]"></i>
                </button>
              </div>
            </div>
          </div>
        </template>

        <!-- Empty state -->
        <template v-else>
          <div class="col-span-full flex flex-col items-center justify-center rounded-2xl bg-white py-20 shadow-sm ring-1 ring-slate-200">
            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
              <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
              </svg>
            </div>
            <p class="text-lg font-semibold text-slate-500">No Products Available</p>
            <p class="mt-1 text-[15px] text-slate-400">Add your first product to get started.</p>
          </div>
        </template>
      </div>

      <!-- ── Pagination ── -->
      <div class="mt-8 flex items-center justify-center gap-2">
        <!-- Prev Button -->
        <Link
          v-if="products.links[0]"
          @click.prevent="navigateTo(products.links[0].url)"
          :class="[
            'pagination-btn',
            { 'pagination-disabled': !products.links[0].url },
          ]"
        >
          Previous
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
          Next
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

// Get correct image URL from database path
const getImageUrl = (imagePath) => {
  if (!imagePath) {
    return null;
  }
  // If path already starts with /, use it as is
  if (imagePath.startsWith('/')) {
    return imagePath;
  }
  // Otherwise prepend /
  return `/${imagePath}`;
};
</script>


