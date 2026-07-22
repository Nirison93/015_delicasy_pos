<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-10" @close="$emit('update:open', false)">

      <!-- Overlay -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
        leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/75 backdrop-blur-sm transition-opacity" />
      </TransitionChild>

      <!-- Modal Content -->
      <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
        <TransitionChild
          as="template"
          enter="ease-out duration-300" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100"
          leave="ease-in duration-200" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95"
        >
          <DialogPanel class="bg-zinc-900 border border-white/10 rounded-3xl shadow-2xl w-full max-w-3xl max-h-[92vh] flex flex-col overflow-hidden">

            <!-- Header -->
            <div class="flex items-center justify-between px-7 py-5 bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900 border-b border-white/10 flex-shrink-0">
              <div class="flex items-center gap-4">
                <div class="w-11 h-11 flex items-center justify-center rounded-2xl bg-amber-500/20 ring-1 ring-amber-500/40">
                  <i class="ri-edit-box-line text-amber-400 text-xl"></i>
                </div>
                <DialogTitle class="text-2xl font-bold text-white">Edit Product</DialogTitle>
              </div>
              <button @click="$emit('update:open', false)"
                class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/10 text-zinc-400 hover:bg-white/20 hover:text-white transition">
                <i class="ri-close-line text-xl"></i>
              </button>
            </div>

            <!-- Scrollable Body -->
            <div class="overflow-y-auto flex-1 px-7 py-6">
              <form @submit.prevent="submit" id="product-update-form">
                <div class="space-y-5">

                  <!-- Product Name -->
                  <div>
                    <label class="block text-base font-semibold text-zinc-300 mb-1.5">Product Name</label>
                    <input v-model="form.name" type="text" required placeholder="Enter Product Name"
                      class="w-full h-12 px-4 text-base bg-zinc-800 border border-white/10 rounded-xl text-white placeholder-zinc-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition" />
                    <span v-if="form.errors.name" class="mt-1 block text-base text-red-400">{{ form.errors.name }}</span>
                  </div>

                  <!-- Category + Supplier -->
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-base font-semibold text-zinc-300 mb-1.5">Category</label>
                      <select required v-model="form.category_id"
                        class="w-full h-12 px-4 text-base bg-zinc-800 border border-white/10 rounded-xl text-white focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition cursor-pointer">
                        <option value="" class="bg-zinc-800">Select a Category</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id" class="bg-zinc-800">
                          {{ category.hierarchy_string ? category.hierarchy_string + ' → ' + category.name : category.name }}
                        </option>
                      </select>
                      <span v-if="form.errors.category_id" class="mt-1 block text-base text-red-400">{{ form.errors.category_id }}</span>
                    </div>
                    <div>
                      <label class="block text-base font-semibold text-zinc-300 mb-1.5">Supplier</label>
                      <select v-model="form.supplier_id"
                        class="w-full h-12 px-4 text-base bg-zinc-800 border border-white/10 rounded-xl text-white focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition cursor-pointer">
                        <option value="" class="bg-zinc-800">Select a Supplier</option>
                        <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id" class="bg-zinc-800">{{ supplier.name }}</option>
                      </select>
                      <span v-if="form.errors.supplier_id" class="mt-1 block text-base text-red-400">{{ form.errors.supplier_id }}</span>
                    </div>
                  </div>

                  <!-- Size + Base -->
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-base font-semibold text-zinc-300 mb-1.5">Size</label>
                      <select v-model="form.size_id"
                        class="w-full h-12 px-4 text-base bg-zinc-800 border border-white/10 rounded-xl text-white focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition cursor-pointer">
                        <option value="" class="bg-zinc-800">Select a Size</option>
                        <option v-for="size in sizes" :key="size.id" :value="size.id" class="bg-zinc-800">{{ size.name }}</option>
                      </select>
                      <span v-if="form.errors.size_id" class="mt-1 block text-base text-red-400">{{ form.errors.size_id }}</span>
                    </div>
                    <div>
                      <label class="block text-base font-semibold text-zinc-300 mb-1.5">Base</label>
                      <select v-model="form.color_id"
                        class="w-full h-12 px-4 text-base bg-zinc-800 border border-white/10 rounded-xl text-white focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition cursor-pointer">
                        <option value="" class="bg-zinc-800">Select a Base</option>
                        <option v-for="color in colors" :key="color.id" :value="color.id" class="bg-zinc-800">{{ color.name }}</option>
                      </select>
                      <span v-if="form.errors.color_id" class="mt-1 block text-base text-red-400">{{ form.errors.color_id }}</span>
                    </div>
                  </div>

                  <!-- Cost Price + Selling Price -->
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-base font-semibold text-zinc-300 mb-1.5">Cost Price</label>
                      <input type="number" step="0.01" v-model="form.cost_price" required placeholder="Enter cost price"
                        class="w-full h-12 px-4 text-base bg-zinc-800 border border-white/10 rounded-xl text-white placeholder-zinc-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition" />
                      <span v-if="form.errors.cost_price" class="mt-1 block text-base text-red-400">{{ form.errors.cost_price }}</span>
                    </div>
                    <div>
                      <label class="block text-base font-semibold text-zinc-300 mb-1.5">Selling Price</label>
                      <input type="text" v-model="form.selling_price" required placeholder="Enter selling price" @blur="updateDiscountedPrice"
                        class="w-full h-12 px-4 text-base bg-zinc-800 border border-white/10 rounded-xl text-white placeholder-zinc-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition" />
                      <span v-if="form.errors.selling_price" class="mt-1 block text-base text-red-400">{{ form.errors.selling_price }}</span>
                    </div>
                  </div>

                  <!-- Discount + Discounted Price -->
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-base font-semibold text-zinc-300 mb-1.5">Discount (%)</label>
                      <input type="text" v-model="form.discount" @blur="updateDiscountedPrice" placeholder="Enter discount percentage"
                        class="w-full h-12 px-4 text-base bg-zinc-800 border border-white/10 rounded-xl text-white placeholder-zinc-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition" />
                      <span v-if="form.errors.discount" class="mt-1 block text-base text-red-400">{{ form.errors.discount }}</span>
                    </div>
                    <div>
                      <label class="block text-base font-semibold text-zinc-300 mb-1.5">Discounted Price</label>
                      <input type="text" v-model="form.discounted_price" @blur="updateDiscount" placeholder="Auto-calculated"
                        class="w-full h-12 px-4 text-base bg-zinc-800 border border-white/10 rounded-xl text-white placeholder-zinc-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition" />
                      <span v-if="form.errors.discounted_price" class="mt-1 block text-base text-red-400">{{ form.errors.discounted_price }}</span>
                    </div>
                  </div>

                  <!-- Stock Quantity -->
                  <div>
                    <label class="block text-base font-semibold text-zinc-300 mb-1.5">Stock Quantity</label>
                    <input type="number" v-model="form.stock_quantity" required placeholder="Enter stock quantity"
                      class="w-full h-12 px-4 text-base bg-zinc-800 border border-white/10 rounded-xl text-white placeholder-zinc-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition" />
                    <span v-if="form.errors.stock_quantity" class="mt-1 block text-base text-red-400">{{ form.errors.stock_quantity }}</span>
                  </div>

                  <!-- Description -->
                  <div>
                    <label class="block text-base font-semibold text-zinc-300 mb-1.5">Description</label>
                    <textarea v-model="form.description" placeholder="Enter Description" rows="2"
                      class="w-full px-4 py-3 text-base bg-zinc-800 border border-white/10 rounded-xl text-white placeholder-zinc-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition resize-none"></textarea>
                    <span v-if="form.errors.description" class="mt-1 block text-base text-red-400">{{ form.errors.description }}</span>
                  </div>

                  <!-- Image -->
                  <div class="p-4 bg-zinc-800/50 border border-white/10 rounded-2xl">
                    <label class="block text-base font-semibold text-zinc-300 mb-3">Product Image</label>
                    <div v-if="form.image" class="mb-3">
                      <p class="text-sm text-zinc-500 mb-2">Current image</p>
                      <img :src="form.image"
                        alt="Product Image"
                        class="w-20 h-20 object-cover rounded-xl ring-1 ring-white/10" />
                    </div>
                    <p v-else-if="selectedProduct" class="text-sm text-zinc-500 mb-3">No image uploaded</p>
                    <input type="file" @change="handleImageUpload"
                      class="w-full text-base text-zinc-300 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-amber-500/15 file:text-amber-400 hover:file:bg-amber-500/25 file:transition cursor-pointer" />
                    <span v-if="form.errors.image" class="mt-1 block text-base text-red-400">{{ form.errors.image }}</span>
                  </div>

                </div>
              </form>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-3 px-7 py-5 bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900 border-t border-white/10 flex-shrink-0">
              <button type="button" @click="$emit('update:open', false)"
                class="h-12 px-6 text-base font-semibold rounded-xl border border-white/10 bg-zinc-800 text-zinc-300 hover:bg-zinc-700 hover:text-white transition">
                Cancel
              </button>
              <button type="submit" form="product-update-form" :disabled="form.processing"
                class="h-12 px-8 text-base font-bold rounded-xl bg-amber-500 text-zinc-900 hover:bg-amber-400 shadow-lg shadow-amber-500/20 active:scale-[0.97] transition flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                <i v-if="form.processing" class="ri-loader-4-line animate-spin text-lg"></i>
                <i v-else class="ri-save-line text-lg"></i>
                Save Changes
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
import { ref, watch, computed } from "vue";
import { useForm } from "@inertiajs/vue3";

// Emit event to toggle modal visibility
const emit = defineEmits(["update:open"]);

// Play click sound function
;

// Define props
const { open, categories, colors, suppliers, sizes, selectedProduct } =
  defineProps({
    open: {
      type: Boolean,
      required: true,
    },
    categories: {
      type: Array,
      required: true,
    },
    colors: {
      type: Array,
      required: true,
    },
    suppliers: {
      type: Array,
      required: true,
    },
    sizes: {
      type: Array,
      required: true,
    },
    selectedProduct: {
      type: Object,
      default: null,
    },
  });

// UseForm for form state
const form = useForm({
  category_id: "",
  supplier_id: "",
  stock_quantity: "",
  name: "",
  code: "",
  size_id: "",
  color_id: "",
  cost_price: null,
  discount: 0,
  selling_price: null,
  discounted_price: null,
  barcode: "",
  image: null,
  description: "",
});

// Handle file upload for images
const handleImageUpload = (event) => {
  form.image = event.target.files[0];
};

function limitToTwoDecimals(value) {
  if (value === null || value === undefined) return value;
  const strValue = value.toString();
  const match = strValue.match(/^(\d+)(\.\d{0,2})?/); // Match up to 2 decimal places
  return match ? parseFloat(match[0]) : value;
}

// Function to update discounted price based on selling price and discount
function updateDiscountedPrice() {
  if (form.selling_price && form.discount) {
    const discountAmount = (form.selling_price * form.discount) / 100;
    form.discounted_price = limitToTwoDecimals(
      form.selling_price - discountAmount
    );
  }
}

// Function to update discount based on selling price and discounted price
function updateDiscount() {
  if (form.selling_price && form.discounted_price) {
    const discountAmount = form.selling_price - form.discounted_price;
    form.discount = limitToTwoDecimals(
      (discountAmount / form.selling_price) * 100
    );
  }
}
// Utility function to limit to 2 decimal points
// function limitToTwoDecimals(value) {
//   if (value === null || value === undefined) return value;
//   const strValue = value.toString();
//   const match = strValue.match(/^(\d+)(\.\d{0,2})?/); // Match up to 2 decimal places
//   return match ? parseFloat(match[0]) : value;
// }

// // Computed property for dynamically calculating the discounted price
// const discountedPrice = computed(() => {
//   if (form.selling_price && form.discount) {
//     const discountAmount = (form.selling_price * form.discount) / 100;
//     return limitToTwoDecimals(form.selling_price - discountAmount);
//   }
//   return form.selling_price || 0;
// });

// // Watch the computed discounted price and update the form's discounted_price field
// watch(discountedPrice, (newValue) => {
//   form.discounted_price = limitToTwoDecimals(newValue);
// });

// // Watch the discounted_price field to dynamically calculate the discount percentage
// watch(
//   () => form.discounted_price,
//   (newDiscountedPrice) => {
//     if (form.selling_price && newDiscountedPrice) {
//       const discountAmount =
//         form.selling_price - parseFloat(newDiscountedPrice);
//       form.discount = limitToTwoDecimals(
//         (discountAmount / form.selling_price) * 100
//       );
//     }
//   }
// );

// Watch for changes in selectedProduct and populate form
watch(
  () => selectedProduct,
  (newValue) => {
    if (newValue) {
      form.category_id = newValue.category_id || "";
      form.name = newValue.name || "";
      form.code = newValue.code || "";
      form.supplier_id = newValue.supplier_id || "";
      form.stock_quantity = newValue.stock_quantity || null;
      form.size_id = newValue.size_id || "";
      form.color_id = newValue.color_id || "";
      form.cost_price = newValue.cost_price || null;
      form.discount = newValue.discount || 0;
      form.selling_price = newValue.selling_price || null;
      form.discounted_price = newValue.discounted_price || null;
      form.barcode = newValue.barcode || "";
      form.image = newValue.image || null;
      form.description = newValue.description || "";
    }
  },
  { immediate: true }
);

// Submit the form
const submit = () => {
  form.post(`/products/${selectedProduct.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      console.log("Product updated successfully!");
      form.reset(); // Reset form fields after successful submission
      emit("update:open", false);
    },
    onError: (errors) => {
      console.error("Form submission failed:", errors);
    },
  });
};
</script>
