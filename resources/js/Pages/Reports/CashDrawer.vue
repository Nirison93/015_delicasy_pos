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

      <!-- KPI Pills -->
      <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
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
          <p class="text-base font-semibold text-white/80 uppercase tracking-wider">Total Opening</p>
          <p class="text-2xl font-bold text-white">{{ fmt(statistics.total_opening_balance) }} LKR</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 p-5 flex flex-col gap-1 shadow-md">
          <p class="text-base font-semibold text-white/80 uppercase tracking-wider">Total Closing</p>
          <p class="text-2xl font-bold text-white">{{ fmt(statistics.total_closing_balance) }} LKR</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-slate-600 to-slate-800 p-5 flex flex-col gap-1 shadow-md">
          <p class="text-base font-semibold text-white/80 uppercase tracking-wider">Total Variance</p>
          <p class="text-2xl font-bold text-white">{{ fmt(statistics.total_variance) }} LKR</p>
        </div>
      </div>

      <!-- Cash Drawer Table -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200">
          <h2 class="text-xl font-bold text-slate-800">Cash Drawer History</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-lg">
            <thead class="bg-slate-100 text-slate-600">
              <tr>
                <th class="text-left px-4 py-3">#</th>
                <th class="text-left px-4 py-3">Opened By</th>
                <th class="text-left px-4 py-3">Opened At</th>
                <th class="text-right px-4 py-3">Opening</th>
                <th class="text-left px-4 py-3">Closed By</th>
                <th class="text-left px-4 py-3">Closed At</th>
                <th class="text-right px-4 py-3">Closing</th>
                <th class="text-right px-4 py-3">Variance</th>
                <th class="text-center px-4 py-3">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="(row, idx) in cashDrawers" :key="row.id" class="hover:bg-slate-50">
                <td class="px-4 py-3 text-slate-600 font-semibold">{{ idx + 1 }}</td>
                <td class="px-4 py-3 text-slate-700">{{ row.openedByUser?.name || row.opened_by || "-" }}</td>
                <td class="px-4 py-3 text-slate-500">{{ fmtDate(row.opened_at) }}</td>
                <td class="px-4 py-3 text-right font-semibold text-emerald-600">{{ fmt(row.opening_balance) }}</td>
                <td class="px-4 py-3 text-slate-700">{{ row.closedByUser?.name || row.closed_by || "-" }}</td>
                <td class="px-4 py-3 text-slate-500">{{ fmtDate(row.closed_at) }}</td>
                <td class="px-4 py-3 text-right font-semibold text-slate-700">{{ fmt(row.closing_balance) }}</td>
                <td class="px-4 py-3 text-right font-semibold" :class="variance(row) >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                  {{ fmt(variance(row)) }}
                </td>
                <td class="px-4 py-3 text-center">
                  <span :class="row.status === 'open' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'" class="px-3 py-1 rounded-full text-base font-bold uppercase">
                    {{ row.status }}
                  </span>
                </td>
              </tr>
              <tr v-if="!cashDrawers.length">
                <td colspan="9" class="px-4 py-6 text-center text-slate-400">No cash drawer records found.</td>
              </tr>
            </tbody>
          </table>
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
    </div>
  </div>
</template>

<script setup>
import Header from "@/Components/custom/Header.vue";
import Banner from "@/Components/Banner.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const props = defineProps({
  cashDrawers: { type: Array, default: () => [] },
  statistics: { type: Object, default: () => ({}) },
  varianceByUser: { type: Array, default: () => [] },
  startDate: { type: String, default: "" },
  endDate: { type: String, default: "" },
  companyInfo: { type: Object, default: () => ({}) },
});

const startDate = ref(props.startDate || "");
const endDate = ref(props.endDate || "");
const showQuickFilter = ref(false);

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
  router.get(route("reports.cashDrawer"), { start_date: startDate.value, end_date: endDate.value }, { preserveScroll: true });
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

const variance = (row) => {
  const open = Number(row?.opening_balance || 0);
  const close = Number(row?.closing_balance || 0);
  return close - open;
};

const printCashDrawerReport = () => {
  try {
    const f = (val) => {
      const n = Number(val || 0);
      return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    const drawerRows = props.cashDrawers.map((row, idx) => {
      const var_val = variance(row);
      const varClass = var_val >= 0 ? '' : 'negative';
      const openedByName = row.openedByUser?.name || row.opened_by || "-";
      const closedByName = row.closedByUser?.name || row.closed_by || "-";
      const openedTime = row.opened_at ? new Date(row.opened_at).toLocaleString(undefined, {month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit'}) : "-";
      const closedTime = row.closed_at ? new Date(row.closed_at).toLocaleString(undefined, {month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit'}) : "-";
      return `<tr><td>${idx + 1}</td><td>${openedByName}</td><td>${openedTime}</td><td>${f(row.opening_balance)}</td><td>${closedByName}</td><td>${closedTime}</td><td>${f(row.closing_balance)}</td><td class="${varClass}">${f(var_val)}</td></tr>`;
    }).join('');

    const reportHTML = `<!doctype html>
<html>
<head>
<meta charset="utf-8" />
<title>Cash Drawer Report</title>

<style>
@page {
    size: 80mm auto;
    margin: 0;
}

@media print {
    body {
        margin: 0;
        padding: 0;
        -webkit-print-color-adjust: exact !important;
        width: 80mm;
    }

    * {
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
}

body {
    background: #fff;
    font-size: 18px;
    font-family: 'Courier New', monospace;
    margin: 0;
    padding: 10px 6px;
    color: #000 !important;
    width: 80mm;
    box-sizing: border-box;
    font-weight: 600;
}

h1 {
    text-align: center;
    margin: 0 0 8px 0;
    font-size: 24px;
    font-weight: 900;
}

h2 {
    font-size: 18px;
    font-weight: 800;
    margin: 8px 0 4px 0;
    border-bottom: 2px solid #000;
    padding-bottom: 4px;
}

.header-row {
    display: flex;
    justify-content: space-between;
    font-size: 16px;
    margin: 4px 0;
}

.summary-box {
    border: 1px solid #000;
    padding: 4px 5px;
    margin: 4px 0;
    font-size: 14px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
    padding: 3px 0;
    font-weight: 700;
}

.footer {
    text-align: center;
    margin-top: 8px;
    font-size: 13px;
    padding-top: 4px;
    border-top: 1px solid #000;
}
</style>
</head>

<body>

<h1>CASH DRAWER REPORT</h1>

<div class="header-row">
    <span><b>From:</b> ${startDate.value || 'All'}</span>
</div>

<div class="header-row">
    <span><b>To:</b> ${endDate.value || 'All'}</span>
</div>

<div class="header-row">
    <span><b>Date:</b> ${new Date().toLocaleDateString()}</span>
</div>

<h2>SUMMARY</h2>

<div class="summary-box">

    <div class="summary-row">
        <span>Total:</span>
        <span>${props.statistics.total_drawers ?? 0}</span>
    </div>

    <div class="summary-row">
        <span>Open:</span>
        <span>${props.statistics.open_drawers ?? 0}</span>
    </div>

    <div class="summary-row">
        <span>Closed:</span>
        <span>${props.statistics.closed_drawers ?? 0}</span>
    </div>

    <div style="border-top:1px solid #999; margin-top:2px;"></div>

    <div class="summary-row">
        <span>Opening:</span>
        <span>${f(props.statistics.total_opening_balance)}</span>
    </div>

    <div class="summary-row">
        <span>Closing:</span>
        <span>${f(props.statistics.total_closing_balance)}</span>
    </div>

    <div class="summary-row">
        <span>Variance:</span>
        <span>${f(props.statistics.total_variance)}</span>
    </div>

</div>

<div class="footer">
    <div>${new Date().toLocaleString()}</div>
    <div>${props.companyInfo?.name || 'Delicasy POS'}</div>
</div>

</body>
</html>`;

    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    document.body.appendChild(iframe);
    iframe.contentDocument.open();
    iframe.contentDocument.write(reportHTML);
    iframe.contentDocument.close();
    iframe.onload = () => {
      iframe.contentWindow.print();
      setTimeout(() => { document.body.removeChild(iframe); }, 250);
    };
  } catch (err) {
    console.error('Cash drawer print error:', err);
    alert('Failed to print the report.');
  }
};
</script>
