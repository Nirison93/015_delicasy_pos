<template>
  <Head title="Expense Report" />
  <Banner />
  <div class="min-h-screen bg-slate-50">
    <Header />

    <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-10 py-6 space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
          <Link href="/reports/cash-drawer" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white ring-1 ring-slate-200 text-slate-600 hover:bg-slate-100 transition">
            <i class="ri-arrow-left-s-line text-xl"></i>
          </Link>
          <div>
            <h1 class="text-3xl font-bold text-slate-800 tracking-wide uppercase">Expense Report</h1>
            <p class="text-lg text-slate-400 font-medium mt-0.5">{{ dateRangeLabel }}</p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <button @click="printReport" class="h-12 px-5 inline-flex items-center gap-2 text-lg font-semibold text-white bg-emerald-600 ring-1 ring-emerald-700 rounded-xl hover:bg-emerald-700 transition select-none">
            <i class="ri-printer-line"></i> Print
          </button>
          <div class="relative">
            <button @click="showQuickFilter = !showQuickFilter"
              class="h-12 px-5 inline-flex items-center gap-2 text-lg font-semibold text-indigo-700 bg-indigo-50 ring-1 ring-indigo-200 rounded-xl hover:bg-indigo-100 transition select-none">
              <i class="ri-calendar-2-line"></i> Quick Filter
              <i :class="showQuickFilter ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line'"></i>
            </button>
            <div v-if="showQuickFilter" class="fixed inset-0 z-40" @click="showQuickFilter = false"></div>
            <ul v-if="showQuickFilter" class="absolute right-0 top-full mt-2 z-50 w-44 bg-white rounded-xl ring-1 ring-slate-200 shadow-xl overflow-hidden py-1">
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
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm px-5 py-4 flex flex-wrap items-end gap-3">
        <div>
          <label class="block text-sm font-semibold text-slate-500 mb-1">Cashier</label>
          <select v-model="userId" class="h-11 px-3 text-md bg-white ring-1 ring-slate-200 border-0 rounded-lg text-slate-700 focus:ring-2 focus:ring-blue-400 transition">
            <option value="">All Cashiers</option>
            <option v-for="c in cashiers" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-500 mb-1">Category</label>
          <select v-model="category" class="h-11 px-3 text-md bg-white ring-1 ring-slate-200 border-0 rounded-lg text-slate-700 focus:ring-2 focus:ring-blue-400 transition">
            <option value="">All Categories</option>
            <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
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
            <option value="Other">Other</option>
          </select>
        </div>
        <button @click="filterData" class="h-11 px-5 text-md font-semibold text-white bg-slate-800 rounded-lg hover:bg-slate-700 active:scale-95 transition">
          Apply Filters
        </button>
        <Link href="/reports/expense-report" class="h-11 px-5 flex items-center text-md font-semibold text-slate-600 bg-white ring-1 ring-slate-200 rounded-lg hover:bg-slate-100 transition">
          Reset
        </Link>
      </div>

      <!-- KPI -->
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div class="rounded-2xl bg-gradient-to-br from-red-500 to-rose-600 p-5 flex flex-col gap-1 shadow-md">
          <p class="text-base font-semibold text-white/80 uppercase tracking-wider">Total Expenses</p>
          <p class="text-2xl font-bold text-white">{{ fmt(totalExpenses) }} LKR</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-slate-600 to-slate-800 p-5 flex flex-col gap-1 shadow-md">
          <p class="text-base font-semibold text-white/80 uppercase tracking-wider">Count</p>
          <p class="text-2xl font-bold text-white">{{ totalCount }}</p>
        </div>
        <div v-for="(val, method) in totalsByPaymentMethod" :key="method" class="rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 p-5 flex flex-col gap-1 shadow-md">
          <p class="text-base font-semibold text-white/80 uppercase tracking-wider">{{ method }} Expenses</p>
          <p class="text-2xl font-bold text-white">{{ fmt(val) }} LKR</p>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200">
          <h2 class="text-xl font-bold text-slate-800">Expenses</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-lg">
            <thead class="bg-slate-100 text-slate-600">
              <tr>
                <th class="text-left px-4 py-3">Date</th>
                <th class="text-left px-4 py-3">Reference</th>
                <th class="text-left px-4 py-3">Category</th>
                <th class="text-left px-4 py-3">Description</th>
                <th class="text-left px-4 py-3">Payment Method</th>
                <th class="text-right px-4 py-3">Amount</th>
                <th class="text-left px-4 py-3">User</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="exp in expensesData" :key="exp.id" class="hover:bg-slate-50">
                <td class="px-4 py-3 text-slate-500">{{ fmtDate(exp.created_at) }}</td>
                <td class="px-4 py-3 text-slate-500">{{ exp.cash_drawer_id ? 'Drawer #' + exp.cash_drawer_id : '-' }}</td>
                <td class="px-4 py-3 text-slate-600">{{ exp.category || "-" }}</td>
                <td class="px-4 py-3 text-slate-700 font-semibold">{{ exp.reason }}</td>
                <td class="px-4 py-3 text-slate-600">{{ exp.payment_method || "-" }}</td>
                <td class="px-4 py-3 text-right text-rose-600 font-semibold">{{ fmt(exp.amount) }}</td>
                <td class="px-4 py-3 text-slate-500">{{ exp.user?.name || exp.user_role || "-" }}</td>
              </tr>
              <tr v-if="!expensesData.length">
                <td colspan="7" class="px-4 py-6 text-center text-slate-400">No expenses found.</td>
              </tr>
            </tbody>
            <tfoot v-if="expensesData.length" class="bg-slate-50 font-bold">
              <tr>
                <td colspan="5" class="px-4 py-3 text-right text-slate-800">Total</td>
                <td class="px-4 py-3 text-right text-rose-700">{{ fmt(totalExpenses) }}</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- Pagination -->
        <div class="px-5 py-4 border-t border-slate-200 flex items-center justify-end gap-2" v-if="expenses.current_page">
          <button @click="goToPage(expenses.current_page - 1)" :disabled="!expenses.prev_page_url"
            class="h-9 px-3 inline-flex items-center gap-1 text-sm font-semibold text-white bg-slate-700 rounded-lg hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed transition">
            <i class="ri-arrow-left-s-line"></i> Previous
          </button>
          <span class="text-sm font-semibold text-slate-600">Page {{ expenses.current_page }} of {{ expenses.last_page }}</span>
          <button @click="goToPage(expenses.current_page + 1)" :disabled="!expenses.next_page_url"
            class="h-9 px-3 inline-flex items-center gap-1 text-sm font-semibold text-white bg-slate-700 rounded-lg hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed transition">
            Next <i class="ri-arrow-right-s-line"></i>
          </button>
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
import { printHtmlInIframe } from "@/Utils/print.js";

const props = defineProps({
  expenses: { type: [Array, Object], default: () => [] },
  totalExpenses: { type: Number, default: 0 },
  totalCount: { type: Number, default: 0 },
  totalsByPaymentMethod: { type: Object, default: () => ({}) },
  categories: { type: Array, default: () => [] },
  cashiers: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  startDate: { type: String, default: "" },
  endDate: { type: String, default: "" },
  companyInfo: { type: Object, default: () => ({}) },
});

const startDate = ref(props.startDate || "");
const endDate = ref(props.endDate || "");
const showQuickFilter = ref(false);
const userId = ref(props.filters?.user_id || "");
const category = ref(props.filters?.category || "");
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

const baseParams = () => ({
  start_date: startDate.value,
  end_date: endDate.value,
  user_id: userId.value || undefined,
  category: category.value || undefined,
  payment_method: paymentMethod.value || undefined,
});

const filterData = () => {
  router.get(route("reports.expenseReport"), { ...baseParams(), page: 1 }, { preserveScroll: true });
};

const goToPage = (page) => {
  router.get(route("reports.expenseReport"), { ...baseParams(), page }, { preserveScroll: true });
};

const expensesData = computed(() => Array.isArray(props.expenses) ? props.expenses : (props.expenses?.data ?? []));

const dateRangeLabel = computed(() => {
  if (!startDate.value && !endDate.value) return "All Time";
  if (startDate.value && endDate.value) return `${startDate.value} to ${endDate.value}`;
  if (startDate.value) return `From ${startDate.value}`;
  return `Up to ${endDate.value}`;
});

const fmt = (val) => Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const fmtDate = (val) => {
  if (!val) return "-";
  const d = new Date(val);
  return isNaN(d.getTime()) ? "-" : d.toLocaleString();
};

const escapeHtml = (value) => String(value ?? '')
  .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');

const printReport = () => {
  const rows = expensesData.value.map(exp => `<tr>
    <td>${escapeHtml(fmtDate(exp.created_at))}</td>
    <td>${exp.cash_drawer_id ? 'Drawer #' + exp.cash_drawer_id : '-'}</td>
    <td>${escapeHtml(exp.category || '-')}</td>
    <td>${escapeHtml(exp.reason)}</td>
    <td>${escapeHtml(exp.payment_method || '-')}</td>
    <td style="text-align:right">${fmt(exp.amount)}</td>
    <td>${escapeHtml(exp.user?.name || exp.user_role || '-')}</td>
  </tr>`).join('');

  const methodRows = Object.entries(props.totalsByPaymentMethod)
    .map(([method, val]) => `<tr><td>${escapeHtml(method)} Expenses</td><td style="text-align:right">${fmt(val)}</td></tr>`).join('');

  const html = `<!doctype html><html><head><meta charset="utf-8" /><title>Expense Report</title>
  <style>
    @page { size: A4; margin: 15mm; }
    body { font-family: Arial, sans-serif; color:#000; }
    h1 { text-align:center; margin-bottom:4px; }
    .sub { text-align:center; color:#555; margin-top:0; margin-bottom:20px; }
    table { width:100%; border-collapse: collapse; margin-bottom: 20px; }
    th, td { padding:6px 8px; border-bottom:1px solid #ddd; font-size:13px; text-align:left; }
    tfoot td { font-weight:800; border-top: 2px solid #000; }
    h2 { font-size: 16px; margin-top: 20px; }
  </style></head><body>
    ${props.companyInfo?.name ? `<h1>${props.companyInfo.name}</h1>` : "<h1>Expense Report</h1>"}
    <p class="sub">${startDate.value || 'All'} to ${endDate.value || 'All'}</p>
    <table>
      <thead><tr><th>Date</th><th>Reference</th><th>Category</th><th>Description</th><th>Payment Method</th><th style="text-align:right">Amount</th><th>User</th></tr></thead>
      <tbody>${rows}</tbody>
      <tfoot><tr><td colspan="5">Total Expenses</td><td style="text-align:right">${fmt(props.totalExpenses)}</td><td></td></tr></tfoot>
    </table>
    <h2>Totals by Payment Method</h2>
    <table>${methodRows}</table>
  </body></html>`;
  printHtmlInIframe(html);
};
</script>
