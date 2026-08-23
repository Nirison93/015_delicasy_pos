<template>
  <Head title="Cash Drawer Report" />
  <Banner />
  <div class="min-h-screen bg-slate-50">
    <Header />

    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 py-6 space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
          <Link href="/" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white ring-1 ring-slate-200 text-slate-600 hover:bg-slate-100 transition">
            <i class="ri-arrow-left-s-line text-xl"></i>
          </Link>
          <div>
            <h1 class="text-3xl font-bold text-slate-800 tracking-wide uppercase">Cash Drawer Report</h1>
            <p class="text-lg text-slate-400 font-medium mt-0.5">{{ dateRangeLabel }}</p>
          </div>
        </div>

        <!-- Date filters & Print -->
        <div class="flex flex-wrap items-center gap-2">
          <a href="/pos"
            class="h-12 px-5 inline-flex items-center gap-2 text-lg font-semibold text-white bg-slate-800 rounded-xl hover:bg-slate-700 active:scale-95 transition">
            <i class="fas fa-cash-register text-sm"></i>
            <span>POS</span>
          </a>

          <button @click="printCashDrawerReport" class="h-12 px-5 inline-flex items-center gap-2 text-lg font-semibold text-white bg-emerald-600 ring-1 ring-emerald-700 rounded-xl hover:bg-emerald-700 transition select-none">
            <i class="ri-printer-line"></i> Print Report
          </button>
          <div class="relative">
                <button @click="showQuickFilter = !showQuickFilter"
                  class="h-12 px-5 inline-flex items-center gap-2 text-lg font-semibold text-indigo-700 bg-indigo-50 ring-1 ring-indigo-200 rounded-xl hover:bg-indigo-100 transition select-none">
              <i class="ri-calendar-2-line"></i> Quick Filter
              <i :class="showQuickFilter ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line'"></i>
            </button>
            <div v-if="showQuickFilter" class="fixed inset-0 z-40" @click="showQuickFilter = false"></div>
            <ul v-if="showQuickFilter" class="absolute left-0 top-full mt-2 z-50 w-44 bg-white rounded-xl ring-1 ring-slate-200 shadow-xl overflow-hidden py-1">
              <li v-for="qf in quickFilters" :key="qf.key">
                <button @click="applyQuick(qf.key)" class="w-full text-left px-4 py-3 text-lg font-semibold text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 transition">
                  {{ qf.label }}
                </button>
              </li>
            </ul>
          </div>
          <input v-model="startDate" type="date" class="h-12 px-4 text-lg text-slate-700 bg-white ring-1 ring-slate-200 border-0 rounded-xl focus:ring-2 focus:ring-blue-400 transition" />
          <span class="text-lg text-slate-400 font-semibold">to</span>
          <input v-model="endDate" type="date" class="h-12 px-4 text-lg text-slate-700 bg-white ring-1 ring-slate-200 border-0 rounded-xl focus:ring-2 focus:ring-blue-400 transition" />
          <button @click="filterData" class="h-12 px-5 text-lg font-semibold text-white bg-slate-800 rounded-xl hover:bg-slate-700 active:scale-95 transition">
            <i class="ri-filter-3-line mr-1"></i> Filter
          </button>
          <Link href="/reports/cash-drawer" class="h-12 px-5 flex items-center text-lg font-semibold text-slate-600 bg-white ring-1 ring-slate-200 rounded-xl hover:bg-slate-100 transition">
            <i class="ri-refresh-line mr-1"></i> Reset
          </Link>
        </div>
      </div>

      <!-- Secondary Filters -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm px-5 py-4 flex flex-wrap items-end gap-3">
        <div>
          <label class="block text-sm font-semibold text-slate-500 mb-1">Cashier</label>
          <select v-model="cashierId" class="h-11 px-3 text-md bg-white ring-1 ring-slate-200 border-0 rounded-lg text-slate-700 focus:ring-2 focus:ring-blue-400 transition">
            <option value="">All Cashiers</option>
            <option v-for="c in cashiers" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-500 mb-1">Drawer ID</label>
          <input v-model="drawerId" type="number" min="1" placeholder="e.g. 42"
            class="h-11 w-28 px-3 text-md bg-white ring-1 ring-slate-200 border-0 rounded-lg text-slate-700 focus:ring-2 focus:ring-blue-400 transition" />
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-500 mb-1">Status</label>
          <select v-model="status" class="h-11 px-3 text-md bg-white ring-1 ring-slate-200 border-0 rounded-lg text-slate-700 focus:ring-2 focus:ring-blue-400 transition">
            <option value="">All</option>
            <option value="open">Open</option>
            <option value="closed">Closed</option>
            <option value="pending_approval">Pending Approval</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-500 mb-1">Payment Method</label>
          <select v-model="paymentMethod" class="h-11 px-3 text-md bg-white ring-1 ring-slate-200 border-0 rounded-lg text-slate-700 focus:ring-2 focus:ring-blue-400 transition">
            <option value="">All</option>
            <option value="Cash">Cash</option>
            <option value="Card">Card</option>
            <option value="QR">QR / Online</option>
            <option value="Bank Transfer">Bank Transfer</option>
          </select>
        </div>
        <button @click="filterData" class="h-11 px-5 text-md font-semibold text-white bg-slate-800 rounded-lg hover:bg-slate-700 active:scale-95 transition">
          Apply Filters
        </button>
        <div class="ml-auto flex gap-2">
          <Link href="/reports/payment-method-report" class="h-11 px-4 inline-flex items-center gap-1 text-md font-semibold text-indigo-700 bg-indigo-50 ring-1 ring-indigo-200 rounded-lg hover:bg-indigo-100 transition">
            <i class="ri-bank-card-line"></i> Payment Method Report
          </Link>
          <Link href="/reports/expense-report" class="h-11 px-4 inline-flex items-center gap-1 text-md font-semibold text-rose-700 bg-rose-50 ring-1 ring-rose-200 rounded-lg hover:bg-rose-100 transition">
            <i class="ri-receipt-line"></i> Expense Report
          </Link>
        </div>
      </div>

      <!-- KPI Pills -->
      <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4">
        <div class="rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-5 flex flex-col gap-1 shadow-md">
          <p class="text-base font-semibold text-white/80 uppercase tracking-wider">Total Drawers</p>
          <p class="text-2xl font-bold text-white">{{ statistics.total_drawers ?? 0 }}</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 p-5 flex flex-col gap-1 shadow-md">
          <p class="text-base font-semibold text-white/80 uppercase tracking-wider">Open Drawers</p>
          <p class="text-2xl font-bold text-white">{{ statistics.open_drawers ?? 0 }}</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 p-5 flex flex-col gap-1 shadow-md">
          <p class="text-base font-semibold text-white/80 uppercase tracking-wider">Closed Drawers</p>
          <p class="text-2xl font-bold text-white">{{ statistics.closed_drawers ?? 0 }}</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 p-5 flex flex-col gap-1 shadow-md">
          <p class="text-base font-semibold text-white/80 uppercase tracking-wider">Pending Approval</p>
          <p class="text-2xl font-bold text-white">{{ statistics.pending_approval_count ?? 0 }}</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 p-5 flex flex-col gap-1 shadow-md">
          <p class="text-base font-semibold text-white/80 uppercase tracking-wider">Total Opening</p>
          <p class="text-2xl font-bold text-white">{{ fmt(statistics.total_opening_balance) }} LKR</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-fuchsia-500 to-purple-700 p-5 flex flex-col gap-1 shadow-md">
          <p class="text-base font-semibold text-white/80 uppercase tracking-wider">Total Closing</p>
          <p class="text-2xl font-bold text-white">{{ fmt(statistics.total_closing_balance) }} LKR</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-red-500 to-red-600 p-5 flex flex-col gap-1 shadow-md">
          <p class="text-base font-semibold text-white/80 uppercase tracking-wider">Total Expenses</p>
          <p class="text-2xl font-bold text-white">{{ fmt(statistics.total_expenses) }} LKR</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-slate-600 to-slate-800 p-5 flex flex-col gap-1 shadow-md">
          <p class="text-base font-semibold text-white/80 uppercase tracking-wider">Total Variance</p>
          <p class="text-2xl font-bold text-white">{{ fmt(statistics.total_variance) }} LKR</p>
        </div>
      </div>

      <!-- Payment Method Breakdown -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200">
          <h2 class="text-xl font-bold text-slate-800">Payment Method Breakdown <span class="text-sm font-normal text-slate-500">(closed drawers in range)</span></h2>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 p-5">
          <div v-for="b in paymentBreakdown" :key="b.key" class="bg-slate-50 rounded-xl ring-1 ring-slate-100 p-4">
            <p class="text-sm font-semibold text-slate-500 uppercase">{{ b.label }}</p>
            <p class="text-xl font-bold text-slate-800">{{ fmt(b.value) }}</p>
          </div>
        </div>
      </div>

      <!-- Cash Drawer Table -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
          <h2 class="text-xl font-bold text-slate-800">Cash Drawer History <span class="text-sm font-normal text-slate-500">({{ drawerPaginationInfo }})</span></h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-md whitespace-nowrap">
            <thead class="bg-slate-100 text-slate-600">
              <tr>
                <th class="text-left px-3 py-3">#</th>
                <th class="text-left px-3 py-3">Cashier</th>
                <th class="text-left px-3 py-3">Opened</th>
                <th class="text-left px-3 py-3">Closed</th>
                <th class="text-right px-3 py-3">Opening</th>
                <th class="text-right px-3 py-3">Cash</th>
                <th class="text-right px-3 py-3">Card</th>
                <th class="text-right px-3 py-3">QR</th>
                <th class="text-right px-3 py-3">Bank Tr.</th>
                <th class="text-right px-3 py-3">Cash In</th>
                <th class="text-right px-3 py-3">Cash Out</th>
                <th class="text-right px-3 py-3">Expenses</th>
                <th class="text-right px-3 py-3">Refunds</th>
                <th class="text-right px-3 py-3">Expected</th>
                <th class="text-right px-3 py-3">Actual</th>
                <th class="text-right px-3 py-3">Difference</th>
                <th class="text-center px-3 py-3">Status</th>
                <th class="text-center px-3 py-3">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="(row, idx) in cashDrawersData" :key="row.id" class="hover:bg-slate-50">
                <td class="px-3 py-3 text-slate-600 font-semibold">{{ row.id }}</td>
                <td class="px-3 py-3 text-slate-700">{{ row.openedByUser?.name || "-" }}</td>
                <td class="px-3 py-3 text-slate-500">{{ fmtDate(row.opened_at) }}</td>
                <td class="px-3 py-3 text-slate-500">{{ fmtDate(row.closed_at) }}</td>
                <td class="px-3 py-3 text-right font-semibold text-slate-700">{{ fmt(row.opening_balance) }}</td>
                <template v-if="row.status === 'closed'">
                  <td class="px-3 py-3 text-right text-emerald-600 font-semibold">{{ fmt(row.cash_sales) }}</td>
                  <td class="px-3 py-3 text-right text-slate-600">{{ fmt(row.card_sales) }}</td>
                  <td class="px-3 py-3 text-right text-slate-600">{{ fmt(row.qr_sales) }}</td>
                  <td class="px-3 py-3 text-right text-slate-600">{{ fmt(row.bank_transfer_sales) }}</td>
                  <td class="px-3 py-3 text-right text-slate-600">{{ fmt(row.cash_in) }}</td>
                  <td class="px-3 py-3 text-right text-slate-600">{{ fmt(row.cash_out) }}</td>
                  <td class="px-3 py-3 text-right text-rose-600">{{ fmt(row.total_expenses) }}</td>
                  <td class="px-3 py-3 text-right text-rose-600">{{ fmt(row.cash_refunds) }}</td>
                  <td class="px-3 py-3 text-right font-semibold text-slate-700">{{ fmt(row.expected_cash) }}</td>
                  <td class="px-3 py-3 text-right font-semibold text-slate-700">{{ fmt(row.closing_balance) }}</td>
                  <td class="px-3 py-3 text-right font-semibold" :class="Number(row.variance) >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                    {{ fmt(row.variance) }}
                  </td>
                </template>
                <template v-else>
                  <td colspan="11" class="px-3 py-3 text-center text-slate-300">— drawer still open —</td>
                </template>
                <td class="px-3 py-3 text-center">
                  <span v-if="row.status === 'open'" class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-sm font-bold uppercase">Open</span>
                  <span v-else-if="row.requires_approval && !row.approved_at" class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-sm font-bold uppercase">Pending Approval</span>
                  <span v-else-if="row.requires_approval && row.approved_at" class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-sm font-bold uppercase" :title="'Approved by ' + (row.approvedByUser?.name || '')">Approved</span>
                  <span v-else class="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-sm font-bold uppercase">Closed</span>
                </td>
                <td class="px-3 py-3 text-center">
                  <button v-if="canApprove(row)" @click="approveDrawer(row)"
                    class="px-3 py-1.5 text-sm font-bold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition">
                    Approve
                  </button>
                </td>
              </tr>
              <tr v-if="!cashDrawersData.length">
                <td colspan="18" class="px-4 py-6 text-center text-slate-400">No cash drawer records found.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Cash Drawer Pagination -->
        <div class="px-5 py-4 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center gap-2">
            <label class="text-sm font-semibold text-slate-600">Rows per page:</label>
            <select v-model.number="drawerPerPage" @change="changeDrawersPerPage"
              class="h-9 px-3 text-sm font-medium text-slate-700 bg-white ring-1 ring-slate-200 border-0 rounded-lg focus:ring-2 focus:ring-blue-400 transition">
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
          </div>
          <div class="flex items-center gap-2 flex-wrap justify-center sm:justify-end">
            <button @click="prevDrawersPage" :disabled="!cashDrawers.prev_page_url"
              class="h-9 px-3 inline-flex items-center gap-1 text-sm font-semibold text-white bg-slate-700 rounded-lg hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed transition">
              <i class="ri-arrow-left-s-line"></i> Previous
            </button>
            <div class="flex items-center gap-1">
              <span v-if="cashDrawers.current_page" class="text-sm font-semibold text-slate-600">
                Page {{ cashDrawers.current_page }} of {{ cashDrawers.last_page }}
              </span>
            </div>
            <button @click="nextDrawersPage" :disabled="!cashDrawers.next_page_url"
              class="h-9 px-3 inline-flex items-center gap-1 text-sm font-semibold text-white bg-slate-700 rounded-lg hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed transition">
              Next <i class="ri-arrow-right-s-line"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- User Activity -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200">
          <h2 class="text-xl font-bold text-slate-800">User Activity</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-lg">
            <thead class="bg-slate-100 text-slate-600">
              <tr>
                <th class="text-left px-4 py-3">User</th>
                <th class="text-right px-4 py-3">Count</th>
                <th class="text-right px-4 py-3">Total Opening</th>
                <th class="text-right px-4 py-3">Total Closing</th>
                <th class="text-right px-4 py-3">Total Variance</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="row in varianceByUser" :key="row.user_id" class="hover:bg-slate-50">
                <td class="px-4 py-3 text-slate-700 font-semibold">{{ row.user_name || "Unknown" }}</td>
                <td class="px-4 py-3 text-right text-slate-600">{{ row.count }}</td>
                <td class="px-4 py-3 text-right text-emerald-600 font-semibold">{{ fmt(row.total_opened) }}</td>
                <td class="px-4 py-3 text-right text-slate-700 font-semibold">{{ fmt(row.total_closed) }}</td>
                <td class="px-4 py-3 text-right font-semibold" :class="row.total_variance >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                  {{ fmt(row.total_variance) }}
                </td>
              </tr>
              <tr v-if="!varianceByUser.length">
                <td colspan="5" class="px-4 py-6 text-center text-slate-400">No user activity found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Expenses in this period -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
          <h2 class="text-xl font-bold text-slate-800">Expenses in this Period</h2>
          <Link href="/reports/expense-report" class="text-md font-semibold text-indigo-600 hover:text-indigo-700">View Full Expense Report →</Link>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-lg">
            <thead class="bg-slate-100 text-slate-600">
              <tr>
                <th class="text-left px-4 py-3">Date</th>
                <th class="text-left px-4 py-3">Reason</th>
                <th class="text-left px-4 py-3">Category</th>
                <th class="text-left px-4 py-3">Payment Method</th>
                <th class="text-right px-4 py-3">Amount</th>
                <th class="text-left px-4 py-3">User</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="exp in expensesData" :key="exp.id" class="hover:bg-slate-50">
                <td class="px-4 py-3 text-slate-500">{{ fmtDate(exp.created_at) }}</td>
                <td class="px-4 py-3 text-slate-700 font-semibold">{{ exp.reason }}</td>
                <td class="px-4 py-3 text-slate-600">{{ exp.category || "-" }}</td>
                <td class="px-4 py-3 text-slate-600">{{ exp.payment_method || "-" }}</td>
                <td class="px-4 py-3 text-right text-rose-600 font-semibold">{{ fmt(exp.amount) }}</td>
                <td class="px-4 py-3 text-slate-500">{{ exp.user?.name || exp.user_role || "-" }}</td>
              </tr>
              <tr v-if="!expensesData.length">
                <td colspan="6" class="px-4 py-6 text-center text-slate-400">No expenses recorded in this period.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Header from "@/Components/custom/Header.vue";
import Banner from "@/Components/Banner.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import axios from "axios";
import { HasRole } from "@/Utils/Permissions";
import { printHtmlInIframe } from "@/Utils/print.js";

const props = defineProps({
  cashDrawers: { type: [Array, Object], default: () => [] },
  statistics: { type: Object, default: () => ({}) },
  varianceByUser: { type: Array, default: () => [] },
  expenses: { type: [Array, Object], default: () => [] },
  cashiers: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  startDate: { type: String, default: "" },
  endDate: { type: String, default: "" },
  companyInfo: { type: Object, default: () => ({}) },
});

const startDate = ref(props.startDate || "");
const endDate = ref(props.endDate || "");
const showQuickFilter = ref(false);
const drawerPerPage = ref(25);
const cashierId = ref(props.filters?.user_id || "");
const drawerId = ref(props.filters?.drawer_id || "");
const status = ref(props.filters?.status || "");
const paymentMethod = ref(props.filters?.payment_method || "");

const quickFilters = [
  { key: "today", label: "Today" },
  { key: "yesterday", label: "Yesterday" },
  { key: "this_week", label: "This Week" },
  { key: "last_week", label: "Last Week" },
  { key: "this_month", label: "This Month" },
  { key: "last_month", label: "Last Month" },
  { key: "this_year", label: "This Year" },
  { key: "last_year", label: "Last Year" },
];

const fmt2 = (d) => {
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${y}-${m}-${day}`;
};

const applyQuick = (period) => {
  const t = new Date();
  let s = "", e = "";
  switch (period) {
    case "today":      s = e = fmt2(t); break;
    case "yesterday":  { const y = new Date(t); y.setDate(t.getDate() - 1); s = e = fmt2(y); break; }
    case "this_week":  { const d = t.getDay(), di = d === 0 ? -6 : 1 - d; const m = new Date(t); m.setDate(t.getDate() + di); const n = new Date(m); n.setDate(m.getDate() + 6); s = fmt2(m); e = fmt2(n); break; }
    case "last_week":  { const d = t.getDay(), di = d === 0 ? -6 : 1 - d; const m = new Date(t); m.setDate(t.getDate() + di - 7); const n = new Date(m); n.setDate(m.getDate() + 6); s = fmt2(m); e = fmt2(n); break; }
    case "this_month": s = fmt2(new Date(t.getFullYear(), t.getMonth(), 1)); e = fmt2(new Date(t.getFullYear(), t.getMonth() + 1, 0)); break;
    case "last_month": s = fmt2(new Date(t.getFullYear(), t.getMonth() - 1, 1)); e = fmt2(new Date(t.getFullYear(), t.getMonth(), 0)); break;
    case "this_year":  s = fmt2(new Date(t.getFullYear(), 0, 1)); e = fmt2(new Date(t.getFullYear(), 11, 31)); break;
    case "last_year":  s = fmt2(new Date(t.getFullYear() - 1, 0, 1)); e = fmt2(new Date(t.getFullYear() - 1, 11, 31)); break;
  }
  startDate.value = s;
  endDate.value = e;
  showQuickFilter.value = false;
  filterData();
};

const baseFilterParams = () => ({
  start_date: startDate.value,
  end_date: endDate.value,
  user_id: cashierId.value || undefined,
  drawer_id: drawerId.value || undefined,
  status: status.value || undefined,
  payment_method: paymentMethod.value || undefined,
});

const filterData = () => {
  router.get(route("reports.cashDrawer"), { ...baseFilterParams(), page: 1 }, { preserveScroll: true });
};

const cashDrawersData = computed(() => normalizeRows(props.cashDrawers));
const expensesData = computed(() => normalizeRows(props.expenses));

const drawerPaginationInfo = computed(() => {
  if (!props.cashDrawers || !props.cashDrawers.current_page) return "";
  const perPage = props.cashDrawers.per_page || 25;
  const from = (props.cashDrawers.current_page - 1) * perPage + 1;
  const to = Math.min(props.cashDrawers.current_page * perPage, props.cashDrawers.total);
  return `${from}-${to} of ${props.cashDrawers.total}`;
});

const nextDrawersPage = () => {
  if (props.cashDrawers && props.cashDrawers.next_page_url) {
    router.get(route("reports.cashDrawer"), { ...baseFilterParams(), page: props.cashDrawers.current_page + 1, per_page: drawerPerPage.value }, { preserveScroll: true });
  }
};

const prevDrawersPage = () => {
  if (props.cashDrawers && props.cashDrawers.prev_page_url) {
    router.get(route("reports.cashDrawer"), { ...baseFilterParams(), page: props.cashDrawers.current_page - 1, per_page: drawerPerPage.value }, { preserveScroll: true });
  }
};

const changeDrawersPerPage = () => {
  router.get(route("reports.cashDrawer"), { ...baseFilterParams(), page: 1, per_page: drawerPerPage.value }, { preserveScroll: true });
};

const dateRangeLabel = computed(() => {
  if (!startDate.value && !endDate.value) return "All Time";
  if (startDate.value && endDate.value) return `${startDate.value} to ${endDate.value}`;
  if (startDate.value) return `From ${startDate.value}`;
  return `Up to ${endDate.value}`;
});

const fmt = (val) => {
  const n = Number(val || 0);
  return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const fmtDate = (val) => {
  if (!val) return "-";
  const d = new Date(val);
  return isNaN(d.getTime()) ? "-" : d.toLocaleString();
};

const normalizeRows = (value) => {
  if (Array.isArray(value)) return value;
  if (value && Array.isArray(value.data)) return value.data;
  return [];
};

const escapeHtml = (value) => String(value ?? '')
  .replace(/&/g, '&amp;')
  .replace(/</g, '&lt;')
  .replace(/>/g, '&gt;')
  .replace(/"/g, '&quot;')
  .replace(/'/g, '&#39;');

const paymentBreakdown = computed(() => {
  const t = props.statistics?.payment_method_totals || {};
  return [
    { key: 'cash', label: 'Cash', value: t.cash },
    { key: 'card', label: 'Card', value: t.card },
    { key: 'qr', label: 'QR / Online', value: t.qr },
    { key: 'bank_transfer', label: 'Bank Transfer', value: t.bank_transfer },
    { key: 'other', label: 'Other', value: t.other },
  ];
});

const canApprove = (row) => row.status === 'closed' && row.requires_approval && !row.approved_at && HasRole(['Admin', 'Manager']);

const approveDrawer = async (row) => {
  try {
    await axios.post(`/cash-drawer/${row.id}/approve`);
    router.reload({ preserveScroll: true });
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to approve this drawer.');
  }
};

const printCashDrawerReport = () => {
  try {
    const f = fmt;

    const drawerRows = cashDrawersData.value.map((row) => {
      const varClass = Number(row.variance) >= 0 ? '' : 'negative';
      const openedByName = escapeHtml(row.openedByUser?.name || "-");
      const openedTime = row.opened_at ? new Date(row.opened_at).toLocaleString(undefined, { month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' }) : "-";
      const closedTime = row.closed_at ? new Date(row.closed_at).toLocaleString(undefined, { month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' }) : "-";
      return `<tr><td>${row.id}</td><td>${openedByName}</td><td>${escapeHtml(openedTime)}</td><td>${f(row.opening_balance)}</td><td>${escapeHtml(closedTime)}</td><td>${f(row.closing_balance)}</td><td class="${varClass}">${f(row.variance)}</td></tr>`;
    }).join('');

    let expenseHtml = '';
    if (expensesData.value.length > 0) {
      expenseHtml = '<h2>EXPENSES DETAILS</h2><table style="width:100%; border-collapse:collapse; margin:4px 0; font-size:14px;"><thead><tr style="font-weight:800; border-bottom:1px solid #999;"><th style="width:20%; text-align:left; padding:2px;">Date</th><th style="width:35%; text-align:left; padding:2px;">Reason</th><th style="width:20%; text-align:right; padding:2px;">Amount</th><th style="width:25%; text-align:left; padding:2px; font-size:12px;">Method / User</th></tr></thead><tbody>';
      expensesData.value.forEach((exp) => {
        const expDate = exp.created_at ? new Date(exp.created_at).toLocaleString(undefined, { month: 'short', day: '2-digit' }) : '-';
        expenseHtml += `<tr style="border-bottom:1px solid #ddd;"><td style="padding:2px; text-align:left;">${expDate}</td><td style="padding:2px; text-align:left;">${escapeHtml(exp.reason || '-')}</td><td style="padding:2px; text-align:right;">${f(exp.amount)}</td><td style="padding:2px; text-align:left; font-size:12px;">${escapeHtml(exp.payment_method || '-')} / ${escapeHtml(exp.user?.name || exp.user_role || 'Unknown')}</td></tr>`;
      });
      expenseHtml += '</tbody></table>';
    }

    const pb = paymentBreakdown.value;
    const paymentHtml = `<h2>PAYMENT METHOD BREAKDOWN</h2><div class="summary-box">${pb.map(b => `<div class="summary-row"><span>${b.label}:</span><span>${f(b.value)}</span></div>`).join('')}</div>`;

    const reportHTML = `<!doctype html>
<html>
<head>
<meta charset="utf-8" />
<title>Cash Drawer Report</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
@page { size: 80mm auto; margin: 0; }
@media print {
    body { margin: 0; padding: 0; -webkit-print-color-adjust: exact !important; width: 80mm; }
    * { -webkit-print-color-adjust: exact !important; color-adjust: exact !important; }
}
body { background: #fff; font-size: 18px; font-family: 'Courier New', monospace; margin: 0; padding: 10px 6px; color: #000 !important; width: 80mm; box-sizing: border-box; font-weight: 600; }
h1 { text-align: center; margin: 0 0 8px 0; font-size: 24px; font-weight: 900; }
h2 { font-size: 18px; font-weight: 800; margin: 8px 0 4px 0; border-bottom: 2px solid #000; padding-bottom: 4px; }
.header-row { display: flex; justify-content: space-between; font-size: 16px; margin: 4px 0; }
.summary-box { border: 1px solid #000; padding: 4px 5px; margin: 4px 0; font-size: 14px; }
.summary-row { display: flex; justify-content: space-between; font-size: 14px; padding: 3px 0; font-weight: 700; }
.negative { color: #000; }
.footer { text-align: center; margin-top: 8px; font-size: 13px; padding-top: 4px; border-top: 1px solid #000; }
table { width: 100%; border-collapse: collapse; font-size: 12px; }
td { padding: 2px; }
</style>
</head>
<body>
<h1>CASH DRAWER REPORT</h1>
<div class="header-row"><span><b>From:</b> ${startDate.value || 'All'}</span></div>
<div class="header-row"><span><b>To:</b> ${endDate.value || 'All'}</span></div>
<div class="header-row"><span><b>Date:</b> ${new Date().toLocaleDateString()}</span></div>
<h2>SUMMARY</h2>
<div class="summary-box">
    <div class="summary-row"><span>Total:</span><span>${props.statistics.total_drawers ?? 0}</span></div>
    <div class="summary-row"><span>Open:</span><span>${props.statistics.open_drawers ?? 0}</span></div>
    <div class="summary-row"><span>Closed:</span><span>${props.statistics.closed_drawers ?? 0}</span></div>
    <div class="summary-row"><span>Pending Approval:</span><span>${props.statistics.pending_approval_count ?? 0}</span></div>
    <div style="border-top:1px solid #999; margin-top:2px;"></div>
    <div class="summary-row"><span>Opening:</span><span>${f(props.statistics.total_opening_balance)}</span></div>
    <div class="summary-row"><span>Closing:</span><span>${f(props.statistics.total_closing_balance)}</span></div>
    <div class="summary-row"><span>Expenses:</span><span>${f(props.statistics.total_expenses)}</span></div>
    <div class="summary-row"><span>Variance:</span><span>${f(props.statistics.total_variance)}</span></div>
</div>
${paymentHtml}
${expenseHtml}
<div class="footer">
    <div>${new Date().toLocaleString()}</div>
    <div>${props.companyInfo?.name || 'Delicasy POS'}</div>
</div>
</body>
</html>`;

    printHtmlInIframe(reportHTML);
  } catch (err) {
    console.error('Cash drawer print error:', err);
    alert('Failed to print the report.');
  }
};
</script>
