<style>
/* Search input */
#SupplierTable_filter {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 16px;
}
#SupplierTable_filter label {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 15px;
  font-weight: 600;
  color: #475569;
}
#SupplierTable_filter input[type="search"] {
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
#SupplierTable_filter input[type="search"]:focus {
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
  <Head title="Suppliers" />
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
            <h1 class="text-2xl font-bold text-slate-800 tracking-wide uppercase">Suppliers</h1>
            <p class="text-[13px] text-slate-400 font-medium mt-0.5">Manage product suppliers and contacts</p>
          </div>
          <span class="ml-2 px-3 py-1 bg-slate-800 text-white text-[13px] font-bold rounded-full">
            {{ allsuppliers.length }}
          </span>
        </div>

        <!-- Add Supplier -->
        <button
          @click="() => { if (HasRole(['Admin'])) { isCreateModalOpen = true; } }"
          :disabled="!HasRole(['Admin'])"
          :title="HasRole(['Admin']) ? '' : 'You do not have permission to add suppliers'"
          :class="[
            'flex items-center gap-2 px-6 py-3 rounded-xl text-[15px] font-bold tracking-wide transition active:scale-[0.97]',
            HasRole(['Admin'])
              ? 'bg-slate-800 text-white hover:bg-slate-700'
              : 'bg-slate-300 text-slate-500 cursor-not-allowed'
          ]"
        >
          <i class="ri-add-circle-fill text-lg"></i>
          Add Supplier
        </button>
      </div>

      <!-- Table Card -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm overflow-hidden">

        <!-- Empty state -->
        <template v-if="!allsuppliers || allsuppliers.length === 0">
          <div class="flex flex-col items-center justify-center py-20 gap-4">
            <i class="ri-store-2-line text-6xl text-slate-200"></i>
            <p class="text-[17px] text-slate-400 font-semibold">No suppliers found</p>
          </div>
        </template>

        <template v-else>
          <div class="px-6 pt-6 pb-4">
            <div class="overflow-x-auto">
              <table id="SupplierTable" class="w-full">
                <thead>
                  <tr class="bg-slate-800 text-white">
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider rounded-tl-xl">Name</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Contact</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Image</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Email</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Address</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider rounded-tr-xl">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr
                    v-for="supplier in allsuppliers"
                    :key="supplier.id"
                    class="hover:bg-slate-50 transition-colors"
                  >
                    <!-- Name -->
                    <td class="px-5 py-4">
                      <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-slate-800 flex items-center justify-center text-white text-[13px] font-bold flex-shrink-0">
                          {{ (supplier.name || 'S')[0].toUpperCase() }}
                        </div>
                        <span class="text-[15px] font-bold text-slate-700">{{ supplier.name || 'N/A' }}</span>
                      </div>
                    </td>

                    <!-- Contact -->
                    <td class="px-5 py-4">
                      <span v-if="supplier.contact" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-50 text-slate-600 text-[14px] font-semibold ring-1 ring-slate-200">
                        <i class="ri-phone-line"></i> {{ supplier.contact }}
                      </span>
                      <span v-else class="text-slate-300 text-[13px]">—</span>
                    </td>

                    <!-- Image -->
                    <td class="px-5 py-4">
                      <img
                        :src="supplier.image ? `/${supplier.image}` : '/images/placeholder.jpg'"
                        alt="Supplier"
                        class="w-12 h-12 rounded-xl object-cover ring-1 ring-slate-200"
                      />
                    </td>

                    <!-- Email -->
                    <td class="px-5 py-4 text-[14px] text-slate-500">
                      {{ supplier.email || '—' }}
                    </td>

                    <!-- Address -->
                    <td class="px-5 py-4 text-[14px] text-slate-500 max-w-[200px] truncate">
                      {{ supplier.address || '—' }}
                    </td>

                    <!-- Actions -->
                    <td class="px-5 py-4">
                      <div class="flex items-center gap-2">
                        <button
                          :disabled="!HasRole(['Admin'])"
                          :title="HasRole(['Admin']) ? 'Edit supplier' : 'No permission to edit'"
                          @click="() => { if (HasRole(['Admin'])) openEditModal(supplier); }"
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
                          :title="HasRole(['Admin']) ? 'Delete supplier' : 'No permission to delete'"
                          @click="() => { if (HasRole(['Admin'])) openDeleteModal(supplier); }"
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

  <!-- Modal Components -->
  <SupplierCreateModel
    :suppliers="allsuppliers"
    v-model:open="isCreateModalOpen"
  />
  <SupplierDeleteModel
    :suppliers="allsuppliers"
    :selected-supplier="selectedSupplier"
    v-model:open="isDeleteModalOpen"
  />
  <SupplierUpdateModel
    :suppliers="allsuppliers"
    v-model:open="isEditModalOpen"
    :selected-supplier="selectedSupplier"
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
import SupplierCreateModel from "@/Components/custom/SupplierCreateModel.vue";
import SupplierDeleteModel from "@/Components/custom/SupplierDeleteModel.vue";
import SupplierUpdateModel from "@/Components/custom/SupplierUpdateModel.vue";
import Banner from "@/Components/Banner.vue";
import { HasRole } from "@/Utils/Permissions";

defineProps({
  allsuppliers: Array, // Array of suppliers
  totalSuppliers: Number, // Total count of suppliers
});

const openEditModal = (supplier) => {
  console.log("Opening edit modal for supplier:", supplier);
  selectedSupplier.value = supplier;
  isEditModalOpen.value = true;
};

const openDeleteModal = (supplier) => {
  selectedSupplier.value = supplier;
  isDeleteModalOpen.value = true;
};

const form = useForm({});

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedSupplier = ref(null);




$(document).ready(function () {
  let table = $("#SupplierTable").DataTable({
    dom: "Bfrtip",
    pageLength: 10,
    buttons: [],
    order: [],
    aaSorting: [],
    columnDefs: [
      {
        targets: '_all',
        orderable: false,
      },
      {
        targets: 2,
        searchable: false,
      },
    ],
    language: { search: "" },
    initComplete: function () {
      let $searchInput = $('#SupplierTable_filter input');
      $searchInput.attr('placeholder', 'Search ...');
      $searchInput.off('keyup.DT input.DT');
      $searchInput.on('keypress', function (e) {
        if (e.which === 13) {
          table.search(this.value).draw();
        }
      });
    },
  });
});










</script>
