<style>
/* Search input */
#OwnerTable_filter {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 16px;
}
#OwnerTable_filter label {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 15px;
  font-weight: 600;
  color: #475569;
}
#OwnerTable_filter input[type="search"] {
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
#OwnerTable_filter input[type="search"]:focus {
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
  <Head title="Owners Discounts" />
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
            <h1 class="text-2xl font-bold text-slate-800 tracking-wide uppercase">Owners Discounts</h1>
            <p class="text-[13px] text-slate-400 font-medium mt-0.5">Track and manage owner discount allocations</p>
          </div>
          <span class="ml-2 px-3 py-1 bg-slate-800 text-white text-[13px] font-bold rounded-full">
            {{ allOwners.length }}
          </span>
        </div>

        <!-- Add Owner -->
        <button
          @click="() => { if (HasRole(['Admin'])) { isCreateModalOpen = true; } }"
          :disabled="!HasRole(['Admin'])"
          :title="HasRole(['Admin']) ? '' : 'You do not have permission to add owners'"
          :class="[
            'flex items-center gap-2 px-6 py-3 rounded-xl text-[15px] font-bold tracking-wide transition active:scale-[0.97]',
            HasRole(['Admin'])
              ? 'bg-slate-800 text-white hover:bg-slate-700'
              : 'bg-slate-300 text-slate-500 cursor-not-allowed'
          ]"
        >
          <i class="ri-add-circle-fill text-lg"></i>
          Add Owner
        </button>
      </div>

      <!-- Table Card -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm overflow-hidden">

        <!-- Empty state -->
        <template v-if="!allOwners || allOwners.length === 0">
          <div class="flex flex-col items-center justify-center py-20 gap-4">
            <i class="ri-vip-crown-line text-6xl text-slate-200"></i>
            <p class="text-[17px] text-slate-400 font-semibold">No owners found</p>
          </div>
        </template>

        <template v-else>
          <div class="px-6 pt-6 pb-4">
            <div class="overflow-x-auto">
              <table id="OwnerTable" class="w-full">
                <thead>
                  <tr class="bg-slate-800 text-white">
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider rounded-tl-xl w-12">#</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Name</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Code</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Month</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Discount</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Current</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider">Balance</th>
                    <th class="px-5 py-4 text-left text-[13px] font-bold uppercase tracking-wider rounded-tr-xl">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr
                    v-for="(owner, index) in allOwners"
                    :key="owner.id"
                    class="hover:bg-slate-50 transition-colors"
                  >
                    <td class="px-5 py-4 text-[15px] text-slate-400 font-semibold">{{ index + 1 }}</td>

                    <td class="px-5 py-4">
                      <div class="flex items-center gap-3">
                        <div class="w-9 h-9 flex-shrink-0 rounded-full bg-slate-100 flex items-center justify-center">
                          <i class="ri-vip-crown-line text-slate-500 text-base"></i>
                        </div>
                        <span class="text-[15px] font-semibold text-slate-800">{{ owner.name }}</span>
                      </div>
                    </td>

                    <td class="px-5 py-4">
                      <span class="inline-flex items-center px-3 py-1 rounded-lg bg-blue-50 text-blue-700 text-[14px] font-bold ring-1 ring-blue-100">
                        {{ owner.code }}
                      </span>
                    </td>

                    <td class="px-5 py-4 text-[15px] text-slate-600">{{ owner.items?.[0]?.month || '—' }}</td>

                    <td class="px-5 py-4 text-[15px] font-semibold text-slate-700">{{ toLkr(owner.items?.[0]?.discount_value) }}</td>

                    <td class="px-5 py-4 text-[15px] font-semibold text-slate-700">{{ toLkr(owner.items?.[0]?.current_discount) }}</td>

                    <td class="px-5 py-4">
                      <span
                        :class="balance(owner) > 0
                          ? 'inline-flex items-center px-3 py-1 rounded-lg text-[14px] font-bold text-emerald-700 bg-emerald-50 ring-1 ring-emerald-200'
                          : 'inline-flex items-center px-3 py-1 rounded-lg text-[14px] font-bold text-slate-500 bg-slate-100 ring-1 ring-slate-200'"
                      >
                        {{ toLkr(balance(owner)) }}
                      </span>
                    </td>

                    <td class="px-5 py-4">
                      <div class="flex items-center gap-2">
                        <span
                          :class="owner.status === 'active'
                            ? 'inline-flex items-center px-3 py-1 rounded-lg text-[13px] font-bold text-emerald-700 bg-emerald-50 ring-1 ring-emerald-200'
                            : 'inline-flex items-center px-3 py-1 rounded-lg text-[13px] font-bold text-slate-500 bg-slate-100 ring-1 ring-slate-200'"
                        >
                          <i :class="owner.status === 'active' ? 'ri-checkbox-circle-line mr-1' : 'ri-forbid-line mr-1'"></i>
                          {{ owner.status }}
                        </span>
                        <button
                          class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-[13px] font-bold bg-blue-50 text-blue-700 ring-1 ring-blue-200 hover:bg-blue-100 transition active:scale-95"
                          @click="openOwnerMonthHistory(owner)"
                        >
                          <i class="ri-history-line text-base"></i> History
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

  <!-- Owner Month History Modal -->
  <div
    v-if="ownerHistoryModal.open"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
  >
    <div class="w-full max-w-3xl bg-white rounded-2xl shadow-2xl ring-1 ring-slate-200 overflow-hidden">

      <!-- Modal Header -->
      <div class="flex items-center justify-between px-7 py-5 bg-slate-800">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/10">
            <i class="ri-history-line text-white text-xl"></i>
          </div>
          <div>
            <h3 class="text-[18px] font-bold text-white tracking-wide">
              {{ ownerHistoryModal.owner?.name }}
              <span class="ml-2 px-2 py-0.5 text-[13px] font-bold bg-white/15 rounded-lg">
                {{ ownerHistoryModal.owner?.code }}
              </span>
            </h3>
            <p class="text-[12px] text-slate-400 mt-0.5">Month-wise discount history</p>
          </div>
        </div>
        <button
          @click="ownerHistoryModal.open = false"
          class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-300 hover:text-white hover:bg-white/10 transition"
        >
          <i class="ri-close-line text-xl"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="px-6 py-5 overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-slate-100">
              <th class="px-4 py-3 text-left text-[12px] font-bold text-slate-500 uppercase tracking-wider rounded-tl-xl">#</th>
              <th class="px-4 py-3 text-left text-[12px] font-bold text-slate-500 uppercase tracking-wider">Month</th>
              <th class="px-4 py-3 text-left text-[12px] font-bold text-slate-500 uppercase tracking-wider">Discount</th>
              <th class="px-4 py-3 text-left text-[12px] font-bold text-slate-500 uppercase tracking-wider">Current</th>
              <th class="px-4 py-3 text-left text-[12px] font-bold text-slate-500 uppercase tracking-wider">Balance</th>
              <th class="px-4 py-3 text-left text-[12px] font-bold text-slate-500 uppercase tracking-wider rounded-tr-xl">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="(it, idx) in ownerHistoryModal.items"
              :key="`${it.owner_id}-${it.month}-${idx}`"
              class="hover:bg-slate-50 transition-colors"
            >
              <td class="px-4 py-3 text-[14px] text-slate-400 font-semibold">{{ idx + 1 }}</td>
              <td class="px-4 py-3 text-[15px] font-semibold text-slate-700">{{ it.month }}</td>
              <td class="px-4 py-3 text-[15px] text-slate-700">{{ toLkr(it.discount_value) }}</td>
              <td class="px-4 py-3 text-[15px] text-slate-700">{{ toLkr(it.current_discount) }}</td>
              <td class="px-4 py-3">
                <span
                  :class="(n(it.discount_value) - n(it.current_discount)) > 0
                    ? 'inline-flex items-center px-3 py-1 rounded-lg text-[13px] font-bold text-emerald-700 bg-emerald-50 ring-1 ring-emerald-200'
                    : 'inline-flex items-center px-3 py-1 rounded-lg text-[13px] font-bold text-slate-500 bg-slate-100 ring-1 ring-slate-200'"
                >
                  {{ toLkr(n(it.discount_value) - n(it.current_discount)) }}
                </span>
              </td>
              <td class="px-4 py-3">
                <span
                  :class="it.status === 'active'
                    ? 'inline-flex items-center px-3 py-1 rounded-lg text-[13px] font-bold text-emerald-700 bg-emerald-50 ring-1 ring-emerald-200'
                    : 'inline-flex items-center px-3 py-1 rounded-lg text-[13px] font-bold text-slate-500 bg-slate-100 ring-1 ring-slate-200'"
                >
                  {{ it.status }}
                </span>
              </td>
            </tr>

            <tr v-if="!ownerHistoryModal.items?.length">
              <td colspan="6" class="px-4 py-12 text-center">
                <i class="ri-inbox-line text-4xl text-slate-200 block mb-2"></i>
                <span class="text-[15px] text-slate-400 font-semibold">No history available</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Modal Footer -->
      <div class="flex items-center justify-end px-7 py-5 border-t border-slate-100 bg-slate-50">
        <button
          class="px-6 py-2.5 rounded-xl text-[14px] font-bold text-white bg-slate-800 hover:bg-slate-700 transition active:scale-95"
          @click="ownerHistoryModal.open = false"
        >
          Close
        </button>
      </div>

    </div>
  </div>

</template>


<script setup>
import { ref, onMounted } from "vue";
import { Link, Head } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import { HasRole } from "@/Utils/Permissions";

defineProps({
  allOwners: Array,
  totalOwners: Number,
});

const isCreateModalOpen = ref(false);




// Safely parse number (null/undefined/'' -> 0)
const n = (v) => Number(v ?? 0) || 0;

// LKR display (no decimals? change toFixed(2) if needed)
const toLkr = (v) => {
  const val = n(v);
  return val.toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

// Balance calculator: (discount_value - current_discount), clamp to >= 0
const balance = (owner) => {
  const item = owner?.items?.[0] || {};
  const dv = n(item.discount_value);
  const cd = n(item.current_discount);
  const remain = dv - cd;
  return remain > 0 ? remain : 0;
};




// ---------- modal state ----------
const ownerHistoryModal = ref({
  open: false,
  owner: null,
  items: [], // full month history for this owner
});

// ---------- open handler ----------
const openOwnerMonthHistory = (owner) => {
  // Expecting owner.items to already be loaded (you used with('items'))
  // If not sorted, sort here (desc by month)
  const items = Array.isArray(owner.items)
    ? [...owner.items].sort((a, b) => (a.month < b.month ? 1 : a.month > b.month ? -1 : 0))
    : [];

  ownerHistoryModal.value = {
    open: true,
    owner,
    items,
  };
};



onMounted(() => {
  $("#OwnerTable").DataTable({
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
    ],
    initComplete: function () {
      const searchInput = $("div.dataTables_filter input");
      searchInput.attr("placeholder", "Search ...");
      searchInput.off("keyup");
      searchInput.on("keypress", function (e) {});
    },
    language: { search: "" },
  });
});
</script>
