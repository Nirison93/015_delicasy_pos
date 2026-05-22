<style>
/* Search input */
#EmployeeTable_filter {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 16px;
}
#EmployeeTable_filter label {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 15px;
  font-weight: 600;
  color: #475569;
}
#EmployeeTable_filter input[type="search"] {
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
#EmployeeTable_filter input[type="search"]:focus {
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
  <Head title="Employees" />
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
            <h1 class="text-2xl font-bold text-slate-800 tracking-wide uppercase">Employees</h1>
            <p class="text-[13px] text-slate-400 font-medium mt-0.5">Manage your staff records</p>
          </div>
          <span class="ml-2 px-3 py-1 bg-slate-800 text-white text-[13px] font-bold rounded-full">
            {{ allemployee.length }}
          </span>
        </div>

        <!-- Add Employee -->
        <button
          @click="() => { if (HasRole(['Admin'])) { isCreateModalOpen = true; } }"
          :disabled="!HasRole(['Admin'])"
          :title="HasRole(['Admin']) ? '' : 'You do not have permission to add employees'"
          :class="[
            'flex items-center gap-2 px-6 py-3 rounded-xl text-[15px] font-bold tracking-wide transition active:scale-[0.97]',
            HasRole(['Admin'])
              ? 'bg-slate-800 text-white hover:bg-slate-700'
              : 'bg-slate-300 text-slate-500 cursor-not-allowed'
          ]"
        >
          <i class="ri-add-circle-fill text-lg"></i>
          Add Employee
        </button>
      </div>

      <!-- Table Card -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm overflow-hidden">

        <!-- Empty state -->
        <template v-if="!allemployee || allemployee.length === 0">
          <div class="flex flex-col items-center justify-center py-20 gap-4">
            <i class="ri-team-line text-6xl text-slate-200"></i>
            <p class="text-[17px] text-slate-400 font-semibold">No employees found</p>
          </div>
        </template>

        <template v-else>
          <div class="px-6 pt-6 pb-4">
            <div class="overflow-x-auto">
              <table
                id="EmployeeTable"
                class="w-full"
              >
                <thead>
                  <tr class="bg-slate-800 text-white">
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider rounded-tl-xl w-12">#</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Name</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Employee ID</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Address</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Email</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Phone</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider rounded-tr-xl">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr
                    v-for="(employee, index) in allemployee"
                    :key="employee.id"
                    class="hover:bg-slate-50 transition-colors"
                  >
                    <td class="px-5 py-4 text-[15px] text-slate-400 font-semibold">{{ index + 1 }}</td>
                    <td class="px-5 py-4">
                      <div class="flex items-center gap-3">
                        <div class="w-9 h-9 flex-shrink-0 rounded-full bg-slate-100 flex items-center justify-center">
                          <i class="ri-user-line text-slate-500 text-base"></i>
                        </div>
                        <span class="text-[15px] font-semibold text-slate-800">{{ employee.name }}</span>
                      </div>
                    </td>
                    <td class="px-5 py-4">
                      <span class="inline-flex items-center px-3 py-1 rounded-lg bg-blue-50 text-blue-700 text-[14px] font-bold ring-1 ring-blue-100">
                        {{ employee.employee_id }}
                      </span>
                    </td>
                    <td class="px-5 py-4 text-[15px] text-slate-600 max-w-[180px] truncate">{{ employee.address || '—' }}</td>
                    <td class="px-5 py-4 text-[15px] text-slate-600">{{ employee.email || '—' }}</td>
                    <td class="px-5 py-4 text-[15px] text-slate-600">{{ employee.phone || '—' }}</td>
                    <td class="px-5 py-4">
                      <div class="flex items-center gap-2">

                        <!-- Edit -->
                        <button
                          @click="() => { if (HasRole(['Admin'])) openEditModal(employee); }"
                          :disabled="!HasRole(['Admin'])"
                          :title="HasRole(['Admin']) ? 'Edit employee' : 'No permission'"
                          :class="[
                            'flex items-center gap-1.5 px-4 py-2 rounded-xl text-[13px] font-bold transition active:scale-95',
                            HasRole(['Admin'])
                              ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 hover:bg-emerald-100'
                              : 'bg-slate-100 text-slate-400 cursor-not-allowed'
                          ]"
                        >
                          <i class="ri-pencil-line text-base"></i> Edit
                        </button>

                        <!-- Delete -->
                        <button
                          @click="() => { if (HasRole(['Admin'])) openDeleteModal(employee); }"
                          :disabled="!HasRole(['Admin'])"
                          :title="HasRole(['Admin']) ? 'Delete employee' : 'No permission'"
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

  <!-- Modals -->
  <EmployeeCreateModel
    :employees="allemployee"
    v-model:open="isCreateModalOpen"
  />
  <EmployeeDeleteModel
    :employees="allemployee"
    :selected-employee="selectedEmployee"
    v-model:open="isDeleteModalOpen"
  />
  <EmployeeUpdateModel
    :employees="allemployee"
    v-model:open="isEditModalOpen"
    :selected-employee="selectedEmployee"
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
import EmployeeCreateModel from "@/Components/custom/EmployeeCreateModel.vue";
import EmployeeDeleteModel from "@/Components/custom/EmployeeDeleteModel.vue";
import EmployeeUpdateModel from "@/Components/custom/EmployeeUpdateModel.vue";
import Banner from "@/Components/Banner.vue";
import { HasRole } from "@/Utils/Permissions";

defineProps({
    allemployee: Array,
    totalEmployee: Number,
});


const openEditModal = (employee) => {

  selectedEmployee.value = employee;
  isEditModalOpen.value = true;
};

const openDeleteModal = (employee) => {
  selectedEmployee.value = employee;
  isDeleteModalOpen.value = true;
};

const form = useForm({});

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedEmployee = ref(null);

$(document).ready(function () {
  let table = $("#EmployeeTable").DataTable({
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

