<template>
  <Head title="Add Promotions" />
  <Banner />

  <div class="min-h-screen bg-slate-50">
    <Header />

    <main class="mx-auto max-w-[1600px] px-6 py-8 lg:px-10">

      <!-- ── Page header bar ── -->
      <div class="mb-6 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
        <!-- Left: back + title -->
        <div class="flex items-center gap-4">
          <Link
            href="/products"
            class="flex h-12 w-12 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-slate-200 transition hover:shadow-md active:scale-95"
          >
            <img src="/images/back-arrow.png" class="h-6 w-6" alt="Back" />
          </Link>
          <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-800">Add Promotion</h1>
            <p class="text-[15px] text-slate-400">
              Products / <span class="font-semibold text-slate-600">Add Promotions</span>
            </p>
          </div>
        </div>

        <!-- Right: Add Products button -->
        <button
          @click="isSelectModalOpen = true"
          class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-3.5 text-[15px] font-semibold text-white shadow-sm transition hover:bg-blue-700 active:scale-95"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Add Products
        </button>
      </div>

      <!-- ── Two-column layout ── -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        <!-- ── Selected Products card ── -->
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
          <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-slate-800">Selected Products</h2>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-[13px] font-semibold text-slate-600">
              {{ form.products.length }} item{{ form.products.length !== 1 ? 's' : '' }}
            </span>
          </div>

          <!-- Empty state -->
          <div v-if="form.products.length === 0" class="flex flex-col items-center justify-center py-20">
            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
              <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
              </svg>
            </div>
            <p class="text-[15px] font-semibold text-slate-500">No products selected</p>
            <p class="mt-1 text-[14px] text-slate-400">Click "Add Products" to get started.</p>
          </div>

          <!-- Products list -->
          <div v-else class="divide-y divide-slate-100">
            <div
              v-for="item in form.products"
              :key="item.id"
              class="flex items-center gap-4 px-6 py-4"
            >
              <img
                :src="item.image ? `/${item.image}` : '/images/placeholder.jpg'"
                alt="Product"
                class="h-14 w-14 rounded-xl object-cover ring-1 ring-slate-200 flex-shrink-0"
              />
              <div class="flex-1 min-w-0">
                <p class="text-[15px] font-semibold text-slate-800 truncate">{{ item.name }}</p>
                <p class="text-[13px] text-slate-400 mt-0.5">Rs. {{ item.selling_price }}</p>
              </div>
              <!-- Qty controls -->
              <div class="flex items-center gap-2">
                <button
                  @click="decrementQuantity(item.id)"
                  class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition hover:bg-slate-800 hover:text-white active:scale-90"
                >
                  <i class="ri-subtract-line text-sm"></i>
                </button>
                <input
                  type="number"
                  v-model="item.quantity"
                  min="0"
                  class="h-8 w-16 rounded-lg border border-slate-200 bg-slate-50 text-center text-[14px] font-semibold text-slate-800 outline-none focus:border-slate-400 focus:ring-2 focus:ring-slate-200"
                />
                <button
                  @click="incrementQuantity(item.id)"
                  class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition hover:bg-slate-800 hover:text-white active:scale-90"
                >
                  <i class="ri-add-line text-sm"></i>
                </button>
              </div>
              <!-- Remove -->
              <button
                @click="removeProduct(item.id)"
                class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-500 active:scale-90"
              >
                <i class="ri-close-line text-lg"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- ── Product Details form card ── -->
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
          <div class="px-6 py-5 border-b border-slate-100">
            <h2 class="text-xl font-semibold text-slate-800">Product Details</h2>
            <p class="text-[13px] text-slate-400 mt-0.5">Fill in the details for the promotional product</p>
          </div>

          <div class="px-6 py-6 space-y-5">

            <!-- Category -->
            <div>
              <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Category Name</label>
              <select
                required
                v-model="form.category_id"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-[15px] text-slate-700 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200"
              >
                <option value="">Select a Category</option>
                <option
                  v-for="category in allcategories"
                  :key="category.id"
                  :value="category.id"
                >
                  {{ category.hierarchy_string ? category.hierarchy_string + " → " + category.name : category.name }}
                </option>
              </select>
              <span v-if="form.errors.category_id" class="mt-1.5 block text-[13px] text-red-500">{{ form.errors.category_id }}</span>
            </div>

            <!-- Product Name -->
            <div>
              <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Product Name</label>
              <input
                v-model="form.name"
                type="text"
                required
                placeholder="Enter product name"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-[15px] text-slate-700 placeholder-slate-400 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200"
              />
              <span v-if="form.errors.name" class="mt-1.5 block text-[13px] text-red-500">{{ form.errors.name }}</span>
            </div>

            <!-- Size & Base -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Size</label>
                <select
                  v-model="form.size_id"
                  class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-[15px] text-slate-700 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200"
                >
                  <option value="">Select a Size</option>
                  <option v-for="size in sizes" :key="size.id" :value="size.id">{{ size.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Base</label>
                <select
                  v-model="form.color_id"
                  class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-[15px] text-slate-700 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200"
                >
                  <option value="">Select a Base</option>
                  <option v-for="color in colors" :key="color.id" :value="color.id">{{ color.name }}</option>
                </select>
              </div>
            </div>

            <!-- Cost Price & Stock Quantity -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Cost Price</label>
                <input
                  type="number"
                  step="0.01"
                  v-model="form.cost_price"
                  placeholder="0.00"
                  required
                  class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-[15px] text-slate-700 placeholder-slate-400 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200"
                />
                <span v-if="form.errors.cost_price" class="mt-1.5 block text-[13px] text-red-500">{{ form.errors.cost_price }}</span>
              </div>
              <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Stock Quantity</label>
                <input
                  type="number"
                  v-model="form.stock_quantity"
                  placeholder="0"
                  required
                  class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-[15px] text-slate-700 placeholder-slate-400 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200"
                />
                <span v-if="form.errors.stock_quantity" class="mt-1.5 block text-[13px] text-red-500">{{ form.errors.stock_quantity }}</span>
              </div>
            </div>

            <!-- Selling Price, Discount, Discounted Price -->
            <div class="grid grid-cols-3 gap-4">
              <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Selling Price</label>
                <input
                  type="text"
                  v-model="form.selling_price"
                  @blur="updateDiscountedPrice"
                  placeholder="0.00"
                  required
                  class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-[15px] text-slate-700 placeholder-slate-400 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200"
                />
                <span v-if="form.errors.selling_price" class="mt-1.5 block text-[13px] text-red-500">{{ form.errors.selling_price }}</span>
              </div>
              <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Discount (%)</label>
                <input
                  type="text"
                  v-model="form.discount"
                  @blur="updateDiscountedPrice"
                  placeholder="0"
                  class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-[15px] text-slate-700 placeholder-slate-400 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200"
                />
              </div>
              <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Discounted Price</label>
                <input
                  type="text"
                  v-model="form.discounted_price"
                  @blur="updateDiscount"
                  placeholder="0.00"
                  class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-[15px] text-slate-700 placeholder-slate-400 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200"
                />
              </div>
            </div>

            <!-- Description -->
            <div>
              <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Description</label>
              <textarea
                v-model="form.description"
                placeholder="Enter description"
                rows="3"
                class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-[15px] text-slate-700 placeholder-slate-400 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200"
              ></textarea>
              <span v-if="form.errors.description" class="mt-1.5 block text-[13px] text-red-500">{{ form.errors.description }}</span>
            </div>

            <!-- Image -->
            <div>
              <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Product Image</label>
              <input
                type="file"
                @change="handleImageUpload"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-[15px] text-slate-600 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-800 file:px-4 file:py-2 file:text-[13px] file:font-semibold file:text-white file:cursor-pointer hover:file:bg-slate-700"
              />
              <span v-if="form.errors.image" class="mt-1.5 block text-[13px] text-red-500">{{ form.errors.image }}</span>
            </div>

            <!-- Save / hint -->
            <div class="pt-2">
              <button
                v-if="form.products.length !== 0"
                @click.prevent="submitForm"
                :class="[
                  'inline-flex w-full items-center justify-center gap-2 rounded-xl px-8 py-3.5 text-[15px] font-semibold text-white shadow-sm transition active:scale-95',
                  HasRole(['Admin'])
                    ? 'bg-blue-600 hover:bg-blue-700'
                    : 'cursor-not-allowed bg-blue-400 opacity-60'
                ]"
                :title="HasRole(['Admin']) ? '' : 'You do not have permission to add more Products'"
              >
                <i class="ri-add-circle-fill text-lg"></i>
                Save Promotion
              </button>
              <p v-else class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-3.5 text-center text-[14px] text-amber-700">
                Please add at least one product to save the promotion.
              </p>
            </div>

          </div>
        </div>

      </div>
    </main>
  </div>

  <SelectProductModel
    v-model:open="isSelectModalOpen"
    :allcategories="allcategories"
    :colors="colors"
    :sizes="sizes"
    @selected-products="handleSelectedProducts"
    :hidePromotions="true"
  />
  <Footer />
</template>

<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import { HasRole } from "@/Utils/Permissions";
import SelectProductModel from "@/Components/custom/SelectProductModel.vue";

const isSelectModalOpen = ref(false);
const products = ref([]);

const props = defineProps({
  allcategories: Array,
  colors: Array,
  sizes: Array,
});

const form = useForm({
  category_id: "",
  supplier_id: "",
  name: "",
  code: "",
  size_id: "",
  color_id: "",
  cost_price: null,
  discount: 0,
  discounted_price: null,
  selling_price: null,
  stock_quantity: null,
  barcode: "",
  image: null, // For file upload
  description: "",
  products: [],
});

const handleSelectedProducts = (selectedProducts) => {
  selectedProducts.forEach((fetchedProduct) => {
    const existingProduct = form.products.find(
      (item) => item.id === fetchedProduct.id
    );

    if (existingProduct) {
      // If the product exists, increment its quantity
      existingProduct.quantity += 1;
    } else {
      // If the product doesn't exist, add it with a default quantity
      form.products.push({
        ...fetchedProduct,
        quantity: 1,
        apply_discount: false, // Default additional attribute
      });
    }

    if (fetchedProduct.cost_price) {
      form.cost_price = (Number(form.cost_price || 0) + Number(fetchedProduct.cost_price)).toFixed(2);
    }
  });
};

const incrementQuantity = (id) => {
  const product = form.products.find((item) => item.id === id);
  if (product) {
    product.quantity += 1;
  }
};

const decrementQuantity = (id) => {
  const product = form.products.find((item) => item.id === id);
  if (product && product.quantity > 1) {
    product.quantity -= 1;
  }
};

const removeProduct = (id) => {
  form.products = form.products.filter((item) => item.id !== id);
};

const updateDiscountedPrice = () => {
  const price = parseFloat(form.selling_price);
  const discount = parseFloat(form.discount);
  if (!isNaN(price) && !isNaN(discount)) {
    form.discounted_price = (price - (price * discount) / 100).toFixed(2);
  }
};

const updateDiscount = () => {
  const price = parseFloat(form.selling_price);
  const discounted = parseFloat(form.discounted_price);
  if (!isNaN(price) && !isNaN(discounted) && price > 0) {
    form.discount = (((price - discounted) / price) * 100).toFixed(2);
  }
};

const handleImageUpload = (event) => {
  form.image = event.target.files[0] ?? null;
};

const submitForm = () => {
  form.post("/submit_promotion", {
    onSuccess: () => {
      console.log("Promotion created successfully!");
      form.reset();
    },
    onError: (errors) => {
      console.error("Form submission failed:", errors);
    },
  });
};
</script>

