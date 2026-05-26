<style scoped>
/* DataTable search filter */
#CategoryTable_filter {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  margin-bottom: 0;
}
#CategoryTable_filter label {
  font-size: 0;
  display: flex;
  align-items: center;
  width: 100%;
}
#CategoryTable_filter input[type="search"] {
  font-weight: 400;
  padding: 12px 18px;
  font-size: 15px;
  color: #1e293b;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  background: #f8fafc;
  outline: none;
  width: 100%;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
#CategoryTable_filter input[type="search"]:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
}

/* Pagination */
.dataTables_wrapper .dataTables_paginate {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 16px;
  gap: 4px;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
  padding: 10px 16px;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s ease;
  border: 1px solid #e2e8f0;
  color: #475569;
  background: #fff;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
  background: #eff6ff;
  border-color: #93c5fd;
  color: #2563eb;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current {
  background: #3b82f6;
  border-color: #3b82f6;
  color: #fff;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
  opacity: 0.4;
  cursor: default;
}

/* Info text */
.dataTables_wrapper .dataTables_info {
  font-size: 14px;
  color: #94a3b8;
  padding-top: 12px;
}

.dataTables_wrapper {
  margin-bottom: 0;
}
</style>

<template>
  <Head title="Categories" />
  <Banner />

  <div class="min-h-screen bg-slate-50">
    <Header />

    <main class="mx-auto max-w-[1600px] px-6 py-8 lg:px-10">
      <!-- ── Page header bar ── -->
      <div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
        <!-- Left: back + title -->
        <div class="flex items-center gap-4">
          <Link href="/" class="flex h-12 w-12 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-slate-200 transition hover:shadow-md active:scale-95">
            <img src="/images/back-arrow.png" class="h-6 w-6" alt="Back" />
          </Link>
          <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-800">Categories</h1>
            <p class="text-base text-slate-400">
              <span class="font-semibold text-slate-600">{{ totalCategories }}</span> total categories
            </p>
          </div>
        </div>

        <!-- Centre: Food / Bar toggle -->
        <div class="flex items-center gap-1 rounded-xl bg-slate-200 p-1">
          <button
            @click="setCategoryType('0')"
            :class="[
              'flex items-center gap-2 rounded-lg px-5 py-2.5 text-[14px] font-semibold transition-all duration-200 active:scale-95',
              activeCategoryType === '0'
                ? 'bg-white text-emerald-700 shadow-sm'
                : 'text-slate-500 hover:text-slate-700'
            ]"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 19.5l-.75-5.25M9 19.5l.75-5.25M8.25 19.5h7.5" />
            </svg>
            Food Category
          </button>
          <button
            @click="setCategoryType('1')"
            :class="[
              'flex items-center gap-2 rounded-lg px-5 py-2.5 text-[14px] font-semibold transition-all duration-200 active:scale-95',
              activeCategoryType === '1'
                ? 'bg-white text-amber-700 shadow-sm'
                : 'text-slate-500 hover:text-slate-700'
            ]"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15M14.25 3.104c.251.023.501.05.75.082M19.8 15a2.25 2.25 0 0 1-2.025 2.25H6.225A2.25 2.25 0 0 1 4.2 15m15.6 0-1.5-4.5" />
            </svg>
            Beverages Category
          </button>
        </div>

        <!-- Right: Add button -->
        <button
          @click="() => { if (HasRole(['Admin'])) { isCreateModalOpen = true; } }"
          :class="[
            'inline-flex items-center gap-2 rounded-xl px-7 py-3.5 text-base font-semibold text-white shadow-sm transition active:scale-95',
            HasRole(['Admin'])
              ? 'bg-blue-600 hover:bg-blue-700 shadow-blue-200'
              : 'bg-blue-400 cursor-not-allowed opacity-60'
          ]"
          :title="HasRole(['Admin']) ? '' : 'You do not have permission to add categories'">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Add Category
        </button>
      </div>

      <!-- ── Table card ── -->
      <template v-if="allcategories && allcategories.length > 0">
        <div class="overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-slate-200/60">
          <div class="overflow-x-auto">
            <table id="CategoryTable" class="w-full text-left text-[15px]">
              <thead>
                <tr class="border-b border-slate-200 bg-slate-800 text-lg font-semibold tracking-wide text-white">
                  <th class="px-6 py-5">Name</th>
                  <th class="px-6 py-5">Parent Hierarchy</th>
                  <th class="px-6 py-5">Image</th>
                  <th class="px-6 py-5 text-center">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="category in allcategories"
                  :key="category.id"
                  class="transition-colors hover:bg-slate-50"
                >
                  <!-- Name + badge -->
                  <td class="px-6 py-4">
                    <span class="text-[15px] font-semibold text-slate-800">{{ category.name || "N/A" }}</span>
                    <span
                      :class="[
                        'ml-2 inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold',
                        category.category_type == 0
                          ? 'bg-emerald-100 text-emerald-700'
                          : 'bg-blue-100 text-blue-700'
                      ]"
                    >
                      {{ category.category_type == 0 ? 'Food' : 'Beverages' }}
                    </span>
                  </td>

                  <!-- Hierarchy -->
                  <td class="px-6 py-4 text-[15px] text-slate-500">
                    {{ category.hierarchy_string || "N/A" }}
                  </td>

                  <!-- Image -->
                  <td class="px-6 py-4">
                    <img v-if="category.image" :src="category.image" class="h-11 w-11 rounded-lg object-cover ring-1 ring-slate-200" />
                    <span v-else class="text-[13px] text-slate-300">No Image</span>
                  </td>

                  <!-- Actions -->
                  <td class="px-6 py-4">
                    <div class="flex items-center justify-center gap-2">
                      <!-- Edit -->
                      <button
                        @click="() => { if (HasRole(['Admin'])) openEditModal(category); }"
                        :disabled="!HasRole(['Admin'])"
                        :class="[
                          'inline-flex items-center gap-1.5 rounded-lg px-5 py-2.5 text-[14px] font-semibold transition active:scale-95',
                          HasRole(['Admin'])
                            ? 'bg-slate-800 text-white hover:bg-slate-900'
                            : 'bg-slate-200 text-slate-400 cursor-not-allowed'
                        ]"
                        :title="HasRole(['Admin']) ? '' : 'You do not have permission to edit'"
                      >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                        Edit
                      </button>

                      <!-- Delete -->
                      <button
                        @click="() => { if (HasRole(['Admin'])) openDeleteModal(category); }"
                        :disabled="!HasRole(['Admin'])"
                        :class="[
                          'inline-flex items-center gap-1.5 rounded-lg px-5 py-2.5 text-[14px] font-semibold transition active:scale-95',
                          HasRole(['Admin'])
                            ? 'bg-red-500 text-white hover:bg-red-600'
                            : 'bg-slate-200 text-slate-400 cursor-not-allowed'
                        ]"
                        :title="HasRole(['Admin']) ? '' : 'You do not have permission to delete'"
                      >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        Delete
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>

      <!-- Empty state -->
      <template v-else>
        <div class="flex flex-col items-center justify-center rounded-2xl bg-white py-20 shadow-sm ring-1 ring-slate-200">
          <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
            <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
            </svg>
          </div>
          <p class="text-lg font-semibold text-slate-500">No Categories Available</p>
          <p class="mt-1 text-base text-slate-400">Create your first category to get started.</p>
        </div>
      </template>
    </main>
  </div>

  <CategoryCreateModel
    :categories="allcategories"
    v-model:open="isCreateModalOpen"
  />
  <CategoryEditModel
    :categories="allcategories"
    :selected-category="selectedCategory"
    v-model:open="isEditModalOpen"
  />
  <CategoryDeleteModel
    :categories="allcategories"
    :selected-category="selectedCategory"
    v-model:open="isDeleteModalOpen"
  />
  <Footer />
</template>

<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import CategoryCreateModel from "@/Components/custom/CategoryCreateModel.vue";
import CategoryEditModel from "@/Components/custom/CategoryEditModel.vue";
import CategoryDeleteModel from "@/Components/custom/CategoryDeleteModel.vue";
import Banner from "@/Components/Banner.vue";
import { HasRole } from "@/Utils/Permissions";

const props = defineProps({
  allcategories: Array,
  totalCategories: Number,
  categoryType: String,
});

const activeCategoryType = ref(props.categoryType ?? '0');

const setCategoryType = (type) => {
  activeCategoryType.value = type;
  router.get(route('categories.index'), { category_type: type }, { preserveState: false });
};

const openEditModal = (category) => {
  selectedCategory.value = category;
  isEditModalOpen.value = true;
};

const openDeleteModal = (category) => {
  selectedCategory.value = category;
  isDeleteModalOpen.value = true;
};

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedCategory = ref(null);

$(document).ready(function () {

  // Initialize DataTable
  function initCategoryTable() {
    let table = $("#CategoryTable").DataTable({
      dom: "Bfrtip",
      pageLength: 10,
      buttons: [],

      // ✅ Sort by ID column (0th column) in descending order
      order: [[0, "desc"]],

      columnDefs: [
        {
          targets: 2, // Disable search for Image column
          searchable: false,
        },
      ],

      initComplete: function () {
        let searchInput = $("div.dataTables_filter input");
        searchInput.attr("placeholder", "Search ...");

        // Trigger search on Enter key
        searchInput.on("keypress", function (e) {
          if (e.which === 13) {
            table.search(this.value).draw();
          }
        });
      },

      language: {
        search: "",
      },
    });

    return table;
  }

  // ✅ Call the function to initialize the table
  initCategoryTable();

});

</script>
