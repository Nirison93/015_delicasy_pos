<style>
/* Search input */
#CustomerTable_filter {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 16px;
}
#CustomerTable_filter label {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 15px;
  font-weight: 600;
  color: #475569;
}
#CustomerTable_filter input[type="search"] {
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
#CustomerTable_filter input[type="search"]:focus {
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
  <Head title="Customers" />
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
            <h1 class="text-2xl font-bold text-slate-800 tracking-wide uppercase">Customers</h1>
            <p class="text-[13px] text-slate-400 font-medium mt-0.5">View and manage registered customers</p>
          </div>
          <span class="ml-2 px-3 py-1 bg-slate-800 text-white text-[13px] font-bold rounded-full">
            {{ allcustomers.length }}
          </span>
        </div>
      </div>

      <!-- Table Card -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm overflow-hidden">

        <!-- Empty state -->
        <template v-if="!allcustomers || allcustomers.length === 0">
          <div class="flex flex-col items-center justify-center py-20 gap-4">
            <i class="ri-group-line text-6xl text-slate-200"></i>
            <p class="text-[17px] text-slate-400 font-semibold">No customers found</p>
          </div>
        </template>

        <template v-else>
          <div class="px-6 pt-6 pb-4">
            <div class="overflow-x-auto">
              <table id="CustomerTable" class="w-full">
                <thead>
                  <tr class="bg-slate-800 text-white">
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider rounded-tl-xl">Name</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Contact</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Email</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Birthday</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">L/Points</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider rounded-tr-xl">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr
                    v-for="customer in allcustomers"
                    :key="customer.id"
                    class="hover:bg-slate-50 transition-colors"
                  >
                    <!-- Name -->
                    <td class="px-5 py-4">
                      <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-slate-800 flex items-center justify-center text-white text-[13px] font-bold flex-shrink-0">
                          {{ (customer.name || 'N')[0].toUpperCase() }}
                        </div>
                        <span class="text-[15px] font-bold text-slate-700">{{ customer.name || 'N/A' }}</span>
                      </div>
                    </td>

                    <!-- Contact -->
                    <td class="px-5 py-4">
                      <span v-if="customer.phone" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-50 text-slate-600 text-[14px] font-semibold ring-1 ring-slate-200">
                        <i class="ri-phone-line"></i> {{ customer.phone }}
                      </span>
                      <span v-else class="text-slate-300 text-[13px]">—</span>
                    </td>

                    <!-- Email -->
                    <td class="px-5 py-4 text-[14px] text-slate-500">
                      {{ customer.email || '—' }}
                    </td>

                    <!-- Birthday -->
                    <td class="px-5 py-4">
                      <span v-if="customer.bdate" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-pink-50 text-pink-700 text-[13px] font-semibold ring-1 ring-pink-200">
                        <i class="ri-cake-line"></i> {{ customer.bdate }}
                      </span>
                      <span v-else class="text-slate-300 text-[13px]">—</span>
                    </td>

                    <!-- Loyalty Points -->
                    <td class="px-5 py-4">
                      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-[14px] font-bold ring-1 ring-indigo-200">
                        <i class="ri-star-line"></i>
                        {{ customer.loyalty_points ?? 0 }}
                      </span>
                    </td>

                    <!-- Actions -->
                    <td class="px-5 py-4">
                      <div class="flex items-center gap-2">
                        <button
                          :disabled="!HasRole(['Admin'])"
                          :title="HasRole(['Admin']) ? 'Edit customer' : 'No permission to edit'"
                          @click="() => { if (HasRole(['Admin'])) openEditModal(customer); }"
                          :class="[
                            'flex items-center gap-1.5 px-4 py-2 rounded-xl text-[13px] font-bold transition active:scale-95',
                            HasRole(['Admin'])
                              ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 hover:bg-emerald-100'
                              : 'bg-slate-100 text-slate-400 cursor-not-allowed'
                          ]"
                        >
                          <i class="ri-pencil-line text-base"></i> Edit
                        </button>
                        <button
                          :disabled="!HasRole(['Admin'])"
                          :title="HasRole(['Admin']) ? 'Delete customer' : 'No permission to delete'"
                          @click="() => { if (HasRole(['Admin'])) openDeleteModal(customer); }"
                          :class="[
                            'flex items-center gap-1.5 px-4 py-2 rounded-xl text-[13px] font-bold transition active:scale-95',
                            HasRole(['Admin'])
                              ? 'bg-red-50 text-red-600 ring-1 ring-red-200 hover:bg-red-100'
                              : 'bg-slate-100 text-slate-400 cursor-not-allowed'
                          ]"
                        >
                          <i class="ri-delete-bin-6-line text-base"></i> Delete
                        </button>
                      </div>
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
  <Footer />

  <CustomerUpdateModel
    :customer="allcustomers"
    :selected-customer="selectedCustomer"
    v-model:open="isEditModalOpen"
  />
  <CustomerDeleteModel
    :customer="allcustomers"
    :selected-customer="selectedCustomer"
    v-model:open="isDeleteModalOpen"
  />
</template>

<script setup>

import { ref } from "vue";
import { Head } from "@inertiajs/vue3";
import { Link, useForm, router } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import CustomerDeleteModel from "@/Components/custom/CustomerDeleteModel.vue";
import CustomerUpdateModel from "@/Components/custom/CustomerUpdateModel.vue";
import Banner from "@/Components/Banner.vue";
import { HasRole } from "@/Utils/Permissions";

defineProps({
  allcustomers: Array,
  totalCustomers: Array,
});






const form = useForm({});

const openEditModal = (customer) => {
  console.log("Opening edit modal for customer:", customer);
  selectedCustomer.value = customer;
  isEditModalOpen.value = true;
};

const openDeleteModal = (customer) => {
  selectedCustomer.value = customer;
  isDeleteModalOpen.value = true;
};

const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedCustomer = ref(null);

$(document).ready(function () {
  let table = $("#CustomerTable").DataTable({
    dom: "Bfrtip",
    pageLength: 10,
    buttons: [],
    order: [],
    aaSorting: [],
    columnDefs: [
      {
        targets: '_all',
        searchable: true,
        orderable: false,
      },
      {
        targets: [4],
        searchable: false,
      },
    ],
    initComplete: function () {
      let searchInput = $("div.dataTables_filter input");
      searchInput.attr("placeholder", "Search ...");
      searchInput.off("keyup");
      searchInput.on("keypress", function (e) {});
    },
    language: {
      search: "",
    },
  });
});
</script>

