<style>
/* Search input */
#TransitionTable_filter {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 16px;
}
#TransitionTable_filter label {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 15px;
  font-weight: 600;
  color: #475569;
}
#TransitionTable_filter input[type="search"] {
  height: 42px;
  padding: 0 14px;
  font-size: 15px;
  border: none;
  border-radius: 12px;
  background: #f8fafc;
  box-shadow: 0 0 0 1px #e2e8f0;
  outline: none;
  transition: box-shadow 0.2s;
  min-width: 240px;
}
#TransitionTable_filter input[type="search"]:focus {
  box-shadow: 0 0 0 2px #60a5fa;
}

/* Pagination */
.dataTables_wrapper .dataTables_paginate {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
  gap: 6px;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
  padding: 8px 16px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  color: #334155 !important;
  background: #f1f5f9 !important;
  border: none !important;
  cursor: pointer;
  transition: background 0.15s;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
  background: #e2e8f0 !important;
  color: #0f172a !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current,
.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
  background: #1e293b !important;
  color: #fff !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
  opacity: 0.35;
  cursor: default;
}
.dataTables_wrapper .dataTables_info {
  font-size: 14px;
  color: #64748b;
  margin-top: 12px;
  text-align: center;
}
.dataTables_wrapper {
  margin-bottom: 10px;
}
</style>

<template>
  <Head title="Stock Transactions" />
  <Banner />
  <div class="min-h-screen bg-slate-50">
    <Header />

    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 py-6 space-y-6">

      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Link href="/" class="w-11 h-11 flex items-center justify-center rounded-xl bg-white ring-1 ring-slate-200 text-slate-600 hover:bg-slate-100 transition">
            <i class="ri-arrow-left-s-line text-2xl"></i>
          </Link>
          <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-wide uppercase">Stock Transactions</h1>
            <p class="text-[13px] text-slate-400 font-medium mt-0.5">History of all stock movements and adjustments</p>
          </div>
          <span class="ml-2 px-3 py-1 bg-slate-800 text-white text-[13px] font-bold rounded-full">
            {{ totalStockTransactions }}
          </span>
        </div>
      </div>

      <!-- Table Card -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm overflow-hidden">

        <!-- Empty state -->
        <template v-if="!allStockTransactions || allStockTransactions.length === 0">
          <div class="flex flex-col items-center justify-center py-20 gap-4">
            <i class="ri-exchange-box-line text-6xl text-slate-200"></i>
            <p class="text-[17px] text-slate-400 font-semibold">No stock transactions found</p>
          </div>
        </template>

        <template v-else>
          <div class="px-6 pt-6 pb-4">
            <div class="overflow-x-auto">
              <table id="TransitionTable" class="w-full">
                <thead>
                  <tr class="bg-slate-800 text-white">
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider rounded-tl-xl w-12">#</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Product</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Type</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Date</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Quantity</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Supplier</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Reason</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider rounded-tr-xl">Note</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr
                    v-for="(stock, index) in allStockTransactions"
                    :key="stock.id"
                    class="hover:bg-slate-50 transition-colors"
                  >
                    <td class="px-5 py-4 text-[15px] text-slate-400 font-semibold">{{ index + 1 }}</td>

                    <!-- Product -->
                    <td class="px-5 py-4">
                      <span class="text-[15px] font-semibold text-slate-800">{{ stock.product?.name || 'N/A' }}</span>
                    </td>

                    <!-- Transaction Type -->
                    <td class="px-5 py-4">
                      <span
                        :class="{
                          'text-emerald-700 bg-emerald-50 ring-1 ring-emerald-200': stock.transaction_type === 'Added',
                          'text-red-600 bg-red-50 ring-1 ring-red-200': stock.transaction_type === 'Deducted',
                          'text-amber-700 bg-amber-50 ring-1 ring-amber-200': stock.transaction_type === 'Sold',
                          'text-slate-500 bg-slate-100 ring-1 ring-slate-200': stock.transaction_type === 'Deleted' || !stock.transaction_type,
                        }"
                        class="inline-flex items-center px-3 py-1 rounded-lg text-[13px] font-bold"
                      >
                        <i
                          :class="{
                            'ri-add-circle-line mr-1': stock.transaction_type === 'Added',
                            'ri-subtract-line mr-1': stock.transaction_type === 'Deducted',
                            'ri-shopping-cart-line mr-1': stock.transaction_type === 'Sold',
                            'ri-delete-bin-line mr-1': stock.transaction_type === 'Deleted' || !stock.transaction_type,
                          }"
                        ></i>
                        {{ stock.transaction_type || 'N/A' }}
                      </span>
                    </td>

                    <!-- Date -->
                    <td class="px-5 py-4 text-[14px] text-slate-500">{{ stock.transaction_date || '—' }}</td>

                    <!-- Quantity -->
                    <td class="px-5 py-4">
                      <span class="inline-flex items-center px-3 py-1 rounded-lg bg-blue-50 text-blue-700 text-[14px] font-bold ring-1 ring-blue-100">
                        {{ stock.quantity || '0' }}
                      </span>
                    </td>

                    <!-- Supplier -->
                    <td class="px-5 py-4 text-[15px] text-slate-600">{{ stock?.product?.supplier?.name || '—' }}</td>

                    <!-- Reason -->
                    <td class="px-5 py-4 text-[15px] font-semibold text-slate-700">{{ stock.reason || '—' }}</td>

                    <!-- Add Note -->
                    <td class="px-5 py-4">
                      <button
                        class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-[13px] font-bold bg-orange-50 text-orange-700 ring-1 ring-orange-200 hover:bg-orange-100 transition active:scale-95"
                        @click="openEditModal(stock)"
                      >
                        <i class="ri-edit-2-line text-base"></i> Add Note
                      </button>
                    </td>

                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </template>
      </div>

    </div>
  </div>

  <StockUpdateModel
    :stocks="allStockTransactions"
    :selected-stock="selectedStock"
    v-model:open="isEditModalOpen"
  />
  <Footer />
</template>

<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Head, Link } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import StockUpdateModel from "@/Components/custom/StockTransResonModel.vue";
import Banner from "@/Components/Banner.vue";
import { HasRole } from "@/Utils/Permissions";

defineProps({
  allStockTransactions: Array,
  totalStockTransactions: Number,
});
const form = useForm({});

const isEditModalOpen = ref(false);
const selectedStock = ref(null);

const openEditModal = (stock) => {
  selectedStock.value = stock;
  isEditModalOpen.value = true;
};

$(document).ready(function () {
  let table = $("#TransitionTable").DataTable({
    dom: "Bfrtip",
    pageLength: 10,
    buttons: [],
    columnDefs: [
      {
        targets: 2,
        searchable: false,
        orderable: false,
      },
    ],
    initComplete: function () {
      let searchInput = $("div.dataTables_filter input");
      searchInput.attr("placeholder", "Search ...");
      searchInput.on("keypress", function (e) {
        if (e.which == 13) {
          table.search(this.value).draw();
        }
      });
    },
    language: {
      search: "",
    },
  });
});
</script>