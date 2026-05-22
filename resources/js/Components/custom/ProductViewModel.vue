<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-50" @close="$emit('update:open', false)">

      <!-- Overlay -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
        leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/75 backdrop-blur-sm transition-opacity" />
      </TransitionChild>

      <!-- Modal Container -->
      <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <TransitionChild
          as="template"
          enter="ease-out duration-300" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100"
          leave="ease-in duration-200" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95"
        >
          <DialogPanel class="bg-zinc-900 border border-white/10 rounded-3xl shadow-2xl w-full max-w-4xl max-h-[92vh] flex flex-col overflow-hidden">

            <!-- Header -->
            <div class="flex items-center justify-between px-7 py-5 bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900 border-b border-white/10 flex-shrink-0">
              <div class="flex items-center gap-4">
                <div class="w-11 h-11 flex items-center justify-center rounded-2xl bg-amber-500/20 ring-1 ring-amber-500/40">
                  <i class="ri-eye-line text-amber-400 text-2xl"></i>
                </div>
                <div>
                  <DialogTitle class="text-3xl font-bold text-white">Product Details</DialogTitle>
                  <p class="text-base text-zinc-400 mt-0.5">Viewing full product information</p>
                </div>
              </div>
              <button @click="$emit('update:open', false)"
                class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/10 text-zinc-400 hover:bg-white/20 hover:text-white transition">
                <i class="ri-close-line text-2xl"></i>
              </button>
            </div>

            <!-- Body -->
            <div class="overflow-y-auto flex-1 px-7 py-6">
              <div class="flex flex-col lg:flex-row gap-7">

                <!-- Left: Product Image -->
                <div class="lg:w-2/5 flex-shrink-0">
                  <div class="relative rounded-2xl overflow-hidden bg-zinc-800 ring-1 ring-white/10">
                    <img
                      :src="selectedProduct.image ? `/${selectedProduct.image}` : '/images/placeholder.jpg'"
                      alt="Product Image"
                      class="w-full h-72 lg:h-full object-cover"
                    />
                    <!-- Discount badge -->
                    <div v-if="selectedProduct.discount && selectedProduct.discount > 0"
                      class="absolute top-3 right-3 bg-red-500 text-white text-base font-bold px-3 py-1.5 rounded-xl shadow-lg">
                      {{ selectedProduct.discount }}% OFF
                    </div>
                  </div>
                </div>

                <!-- Right: Details -->
                <div class="flex-1 flex flex-col gap-5">

                  <!-- Name + Category -->
                  <div>
                    <h2 class="text-4xl font-bold text-white leading-tight break-words">
                      {{ selectedProduct.name }}
                    </h2>
                    <div class="mt-2 flex items-center gap-2 flex-wrap">
                      <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500/15 px-3 py-1.5 text-base font-semibold text-amber-400 ring-1 ring-amber-500/30">
                        <i class="ri-price-tag-3-line text-sm"></i>
                        {{ selectedProduct.category?.name ?? "No Category" }}
                      </span>
                      <span class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-700 px-3 py-1.5 text-base font-semibold text-zinc-300 ring-1 ring-white/10">
                        <i class="ri-rulers-line text-sm"></i>
                        {{ selectedProduct.size?.name ?? "N/A" }}
                      </span>
                    </div>
                  </div>

                  <!-- Description -->
                  <div v-if="selectedProduct.description" class="rounded-xl bg-zinc-800 border border-white/10 px-4 py-3">
                    <p class="text-base font-semibold text-zinc-400 mb-1">Description</p>
                    <p class="text-lg text-zinc-200 leading-relaxed">{{ selectedProduct.description }}</p>
                  </div>

                  <!-- Info grid -->
                  <div class="grid grid-cols-2 gap-3">

                    <!-- Code -->
                    <div class="rounded-xl bg-zinc-800 border border-white/10 px-4 py-3">
                      <p class="text-sm font-semibold text-zinc-500 uppercase tracking-wide mb-1">Code</p>
                      <p class="text-xl font-bold text-white">{{ selectedProduct.code ?? "N/A" }}</p>
                    </div>

                    <!-- Stock Quantity -->
                    <div class="rounded-xl bg-zinc-800 border border-white/10 px-4 py-3">
                      <p class="text-sm font-semibold text-zinc-500 uppercase tracking-wide mb-1">Stock Qty</p>
                      <p class="text-xl font-bold text-white">{{ selectedProduct.stock_quantity ?? "N/A" }}</p>
                    </div>

                    <!-- Selling Price -->
                    <div class="rounded-xl bg-amber-500/10 border border-amber-500/30 px-4 py-3">
                      <p class="text-sm font-semibold text-amber-500/70 uppercase tracking-wide mb-1">Selling Price</p>
                      <p class="text-xl font-bold text-amber-400">Rs. {{ selectedProduct.selling_price ?? "N/A" }}</p>
                    </div>

                    <!-- Discounted Price (only when discount > 0) -->
                    <div v-if="selectedProduct.discount && selectedProduct.discount > 0"
                      class="rounded-xl bg-red-500/10 border border-red-500/30 px-4 py-3">
                      <p class="text-sm font-semibold text-red-400/70 uppercase tracking-wide mb-1">After Discount</p>
                      <p class="text-xl font-bold text-red-400">
                        Rs. {{
                          (
                            selectedProduct.selling_price -
                            (selectedProduct.selling_price * selectedProduct.discount) / 100
                          ).toFixed(2)
                        }}
                      </p>
                    </div>

                    <!-- Cost Price -->
                    <div v-if="selectedProduct.cost_price" class="rounded-xl bg-zinc-800 border border-white/10 px-4 py-3">
                      <p class="text-sm font-semibold text-zinc-500 uppercase tracking-wide mb-1">Cost Price</p>
                      <p class="text-xl font-bold text-white">Rs. {{ selectedProduct.cost_price }}</p>
                    </div>

                    <!-- Base / Color -->
                    <div v-if="selectedProduct.color?.name" class="rounded-xl bg-zinc-800 border border-white/10 px-4 py-3">
                      <p class="text-sm font-semibold text-zinc-500 uppercase tracking-wide mb-1">Base</p>
                      <p class="text-xl font-bold text-white">{{ selectedProduct.color.name }}</p>
                    </div>

                  </div>

                  <!-- Created date footer -->
                  <div class="flex items-center gap-2 pt-1">
                    <i class="ri-calendar-line text-zinc-500 text-base"></i>
                    <span class="text-base text-zinc-500">Created on</span>
                    <span class="text-base font-semibold text-zinc-300">{{ formattedDate }}</span>
                  </div>

                </div>
              </div>
            </div>

            <!-- Hidden print container (unchanged) -->
            <div class="hidden print-container" id="printContainer">
              <div class="flex items-center justify-center w-full space-x-4">
                <p class="text-md font-bold text-black">{{ selectedProduct.category?.name || "N/A" }}</p>
                <p class="text-md font-bold text-black">{{ selectedProduct?.selling_price ?? "N/A" }} LKR</p>
              </div>
              <svg id="barcodePrint"></svg>
              <p style="color:#000;text-align:center;width:100%;padding-bottom:5px">{{ selectedProduct?.code ?? "N/A" }}</p>
              <p style="color:#000;text-align:center;width:100%">[{{ selectedProduct.size?.name || "N/A" }}] - {{ selectedProduct.color?.name || "N/A" }}</p>
              <p style="color:#000;text-align:center;width:100%">{{ selectedProduct?.name ?? "N/A" }}</p>
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
import dayjs from "dayjs";
import { HasRole } from "@/Utils/Permissions";

;

// Extend Day.js for ordinal formatting
import advancedFormat from "dayjs/plugin/advancedFormat";
dayjs.extend(advancedFormat);

const emit = defineEmits(["update:open"]);

// The `open` prop controls the visibility of the modal
const { selectedProduct } = defineProps({
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
  sizes: {
    type: Array,
    required: true,
  },
  selectedProduct: {
    type: Object,
    default: null, // Ensure it defaults to null
  },
});

// Computed property to format the date
const formattedDate = computed(() =>
  selectedProduct && selectedProduct.created_at
    ? dayjs(selectedProduct.created_at).format("Do MMMM YYYY")
    : ""
);

function generateAndPrintBarcode() {
  const input = document.getElementById("barcodeInput").value;
  const barcodePrintElement = document.getElementById("barcodePrint");

  if (input.trim() === "") {
    alert("Please enter text to generate and print a barcode.");
    return;
  }

  JsBarcode(barcodePrintElement, input, {
    format: "CODE128", // Code 128 is compact and ideal for small labels
    lineColor: "#000", // Black lines for high contrast
    width: 1.3, // Narrower lines to fit more content within the label
    height: 60, // Shorter height to fit within the 30mm space
    displayValue: false, // Disable text display if it overlaps with the barcode
    margin: 0, // Remove default margins to maximize space usage
  });

  // JsBarcode(barcodePrintElement, input, {
  //   format: "CODE128",
  //   // format: "EAN13",
  //   lineColor: "#000",
  //   width: 1.25,
  //   height: 100,
  //   displayValue: false,
  // });

  const printContents = document.getElementById("printContainer").innerHTML;
  const originalContents = document.body.innerHTML;

  document.body.innerHTML = printContents;
  window.print();
  document.body.innerHTML = originalContents;

  location.reload();
}
</script>



<style>
@media print {
  #barcodePrint {
    display: block;
    /* Ensure the SVG behaves like a block-level element */
    margin: 0 auto;
    /* Horizontally center using auto margins */
    /* margin-top: 10px; */
  }

  .print-container {
    display: flex;
    justify-content: center;
    /* Horizontally center content inside the container */
    align-items: center;
    /* Vertically center content inside the container */
    height: 100%;
    /* Ensure container takes full height for vertical centering */
    text-align: center;
    /* Center text within the container */
  }
}
</style>
