<template>
  <Head title="Payment Method Report" />
  <Banner />
  <div class="min-h-screen bg-slate-50">
    <Header />

    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-10 py-6 space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
          <Link href="/reports/cash-drawer" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white ring-1 ring-slate-200 text-slate-600 hover:bg-slate-100 transition">
            <i class="ri-arrow-left-s-line text-xl"></i>
          </Link>
          <div>
            <h1 class="text-3xl font-bold text-slate-800 tracking-wide uppercase">Payment Method Report</h1>
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
          <button @click="filterData" class="h-12 px-5 text-lg font-semibold text-white bg-slate-800 rounded-xl hover:bg-slate-700 active:scale-95 transition">
            <i class="ri-filter-3-line mr-1"></i> Filter
          </button>
        </div>
      </div>

      <!-- KPI Pills -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div v-for="(b, idx) in buckets" :key="b.method" class="rounded-2xl p-5 flex flex-col gap-1 shadow-md" :class="pillGradients[idx % pillGradients.length]">
          <p class="text-base font-semibold text-white/80 uppercase tracking-wider">{{ b.method }}</p>
          <p class="text-2xl font-bold text-white">{{ fmt(b.total) }} LKR</p>
          <p class="text-sm text-white/70">{{ b.transactions }} txns · {{ b.percent }}%</p>
        </div>
      </div>

      <!-- Grand Total -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm p-6 flex items-center justify-between">
        <span class="text-2xl font-bold text-slate-800">Grand Total Sales</span>
        <span class="text-3xl font-extrabold text-emerald-600">{{ fmt(grandTotal) }} LKR</span>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200">
          <h2 class="text-xl font-bold text-slate-800">Breakdown</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-lg">
            <thead class="bg-slate-100 text-slate-600">
              <tr>
                <th class="text-left px-4 py-3">Payment Method</th>
                <th class="text-right px-4 py-3">Transactions</th>
                <th class="text-right px-4 py-3">Total</th>
                <th class="text-right px-4 py-3">% of Grand Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="b in buckets" :key="b.method" class="hover:bg-slate-50">
                <td class="px-4 py-3 text-slate-700 font-semibold">{{ b.method }}</td>
                <td class="px-4 py-3 text-right text-slate-600">{{ b.transactions }}</td>
                <td class="px-4 py-3 text-right font-semibold text-slate-800">{{ fmt(b.total) }}</td>
                <td class="px-4 py-3 text-right text-slate-600">{{ b.percent }}%</td>
              </tr>
              <tr class="bg-slate-50 font-bold">
                <td class="px-4 py-3 text-slate-800">Grand Total</td>
                <td class="px-4 py-3 text-right text-slate-800">{{ buckets.reduce((s, b) => s + b.transactions, 0) }}</td>
                <td class="px-4 py-3 text-right text-emerald-700">{{ fmt(grandTotal) }}</td>
                <td class="px-4 py-3 text-right text-slate-800">100%</td>
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
import { printHtmlInIframe } from "@/Utils/print.js";

const props = defineProps({
  buckets: { type: Array, default: () => [] },
  grandTotal: { type: Number, default: 0 },
  startDate: { type: String, default: "" },
  endDate: { type: String, default: "" },
  companyInfo: { type: Object, default: () => ({}) },
});

const startDate = ref(props.startDate || "");
const endDate = ref(props.endDate || "");
const showQuickFilter = ref(false);

const pillGradients = [
  "bg-gradient-to-br from-emerald-500 to-teal-600",
  "bg-gradient-to-br from-sky-500 to-blue-600",
  "bg-gradient-to-br from-violet-500 to-purple-600",
  "bg-gradient-to-br from-amber-500 to-orange-600",
  "bg-gradient-to-br from-slate-600 to-slate-800",
];

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

const filterData = () => {
  router.get(route("reports.paymentMethodReport"), { start_date: startDate.value, end_date: endDate.value }, { preserveScroll: true });
};

const dateRangeLabel = computed(() => {
  if (!startDate.value && !endDate.value) return "All Time";
  if (startDate.value && endDate.value) return `${startDate.value} to ${endDate.value}`;
  if (startDate.value) return `From ${startDate.value}`;
  return `Up to ${endDate.value}`;
});

const fmt = (val) => Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const printReport = () => {
  const rows = props.buckets.map(b => `<tr><td>${b.method}</td><td style="text-align:right">${b.transactions}</td><td style="text-align:right">${fmt(b.total)}</td><td style="text-align:right">${b.percent}%</td></tr>`).join('');
  const html = `<!doctype html><html><head><meta charset="utf-8" /><title>Payment Method Report</title>
  <style>
    @page { size: A4; margin: 15mm; }
    body { font-family: Arial, sans-serif; color:#000; }
    h1 { text-align:center; margin-bottom:4px; }
    .sub { text-align:center; color:#555; margin-top:0; margin-bottom:20px; }
    table { width:100%; border-collapse: collapse; }
    th, td { padding:8px 10px; border-bottom:1px solid #ddd; font-size:14px; text-align:left; }
    tfoot td { font-weight:800; border-top: 2px solid #000; }
  </style></head><body>
    ${props.companyInfo?.name ? `<h1>${props.companyInfo.name}</h1>` : "<h1>Payment Method Report</h1>"}
    <p class="sub">${startDate.value || 'All'} to ${endDate.value || 'All'}</p>
    <table>
      <thead><tr><th>Method</th><th style="text-align:right">Transactions</th><th style="text-align:right">Total</th><th style="text-align:right">%</th></tr></thead>
      <tbody>${rows}</tbody>
      <tfoot><tr><td>Grand Total</td><td></td><td style="text-align:right">${fmt(props.grandTotal)}</td><td style="text-align:right">100%</td></tr></tfoot>
    </table>
  </body></html>`;
  printHtmlInIframe(html);
};
</script>
