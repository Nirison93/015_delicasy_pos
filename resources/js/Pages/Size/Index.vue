<style>
/* Search input */
#SizeTable_filter {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 16px;
}
#SizeTable_filter label {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 15px;
  font-weight: 600;
  color: #475569;
}
#SizeTable_filter input[type="search"] {
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
#SizeTable_filter input[type="search"]:focus {
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
  <Head title="Sizes" />
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
            <h1 class="text-2xl font-bold text-slate-800 tracking-wide uppercase">Sizes</h1>
            <p class="text-[13px] text-slate-400 font-medium mt-0.5">Manage product size options</p>
          </div>
          <span class="ml-2 px-3 py-1 bg-slate-800 text-white text-[13px] font-bold rounded-full">
            {{ allsize.length }}
          </span>
        </div>

        <!-- Add Size -->
        <button
          @click="() => { if (HasRole(['Admin'])) { isCreateModalOpen = true; } }"
          :disabled="!HasRole(['Admin'])"
          :title="HasRole(['Admin']) ? '' : 'You do not have permission to add sizes'"
          :class="[
            'flex items-center gap-2 px-6 py-3 rounded-xl text-[15px] font-bold tracking-wide transition active:scale-[0.97]',
            HasRole(['Admin'])
              ? 'bg-slate-800 text-white hover:bg-slate-700'
              : 'bg-slate-300 text-slate-500 cursor-not-allowed'
          ]"
        >
          <i class="ri-add-circle-fill text-lg"></i>
          Add Size
        </button>
      </div>

      <!-- Table Card -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm overflow-hidden">

        <!-- Empty state -->
        <template v-if="!allsize || allsize.length === 0">
          <div class="flex flex-col items-center justify-center py-20 gap-4">
            <i class="ri-restaurant-line text-6xl text-slate-200"></i>
            <p class="text-[17px] text-slate-400 font-semibold">No sizes found</p>
          </div>
        </template>

        <template v-else>
          <div class="px-6 pt-6 pb-4">
            <div class="overflow-x-auto">
              <table id="SizeTable" class="w-full">
                <thead>
                  <tr class="bg-slate-800 text-white">
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider rounded-tl-xl w-12">#</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Name</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider rounded-tr-xl">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr
                    v-for="(size, index) in allsize"
                    :key="size.id"
                    class="hover:bg-slate-50 transition-colors"
                  >
                    <td class="px-5 py-4 text-[15px] text-slate-400 font-semibold">{{ index + 1 }}</td>

                    <!-- Name -->
                    <td class="px-5 py-4">
                      <span class="inline-flex items-center px-3 py-1 rounded-lg bg-sky-50 text-sky-700 text-[15px] font-bold ring-1 ring-sky-200">
                        <i class="ri-restaurant-line mr-1.5"></i>
                        {{ size.name }}
                      </span>
                    </td>

                    <!-- Actions -->
                    <td class="px-5 py-4">
                      <div class="flex items-center gap-2">
                        <button
                          :disabled="!HasRole(['Admin'])"
                          :title="HasRole(['Admin']) ? 'Edit size' : 'No permission to edit'"
                          @click="() => { if (HasRole(['Admin'])) openEditModal(size); }"
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
                          :title="HasRole(['Admin']) ? 'Delete size' : 'No permission to delete'"
                          @click="() => { if (HasRole(['Admin'])) openDeleteModal(size); }"
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
  <SizeCreateModel
    :sizes="allsize"
    v-model:open="isCreateModalOpen"
  />
  <SizeUpdateModel
    :sizes="allsize"
    v-model:open="isEditModalOpen"
    :selected-size="selectedSize"
  />
  <SizeDeleteModel
    :sizes="allsize"
    :selected-size="selectedSize"
    v-model:open="isDeleteModalOpen"
  />
  <Footer />
</template>

<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import { Head } from '@inertiajs/vue3';
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import SizeCreateModel from "@/Components/custom/SizeCreateModel.vue";
import SizeDeleteModel from "@/Components/custom/SizeDeleteModel.vue";
import SizeUpdateModel from "@/Components/custom/SizeUpdateModel.vue";
import Banner from "@/Components/Banner.vue";
import { HasRole } from "@/Utils/Permissions";

defineProps({
  allsize: Array,
  totalSize: Number,
});


const openEditModal = (size) => {
  console.log("Opening edit modal for size:", size);
  selectedSize.value = size;
  isEditModalOpen.value = true;
};

const openDeleteModal = (size) => {
  selectedSize.value = size;
  isDeleteModalOpen.value = true;
};

const form = useForm({});

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedSize = ref(null);

$(document).ready(function () {
  let table = $("#SizeTable").DataTable({
    dom: "Bfrtip",
    pageLength: 10,
    buttons: [],
    columnDefs: [

      {
        targets: [2],
        searchable: false,
        orderable: false,
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

