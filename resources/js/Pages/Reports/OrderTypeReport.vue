<template>
  <Head title="Order Type Report" />
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
            <h1 class="text-2xl font-bold text-slate-800 tracking-wide uppercase">Order Type Report</h1>
            <p class="text-sm text-slate-400 font-medium mt-0.5">{{ dateRangeLabel }}</p>
          </div>
        </div>

        <!-- Date filters -->
        <div class="flex flex-wrap items-center gap-2">
          <div class="relative">
            <button @click="showQuickFilter = !showQuickFilter"
                    class="h-10 px-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-700 bg-indigo-50 ring-1 ring-indigo-200 rounded-xl hover:bg-indigo-100 transition select-none">
              <i class="ri-calendar-2-line"></i> Quick Filter
              <i :class="showQuickFilter ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line'"></i>
            </button>
            <div v-if="showQuickFilter" class="fixed inset-0 z-40" @click="showQuickFilter = false"></div>
            <ul v-if="showQuickFilter" class="absolute left-0 top-full mt-2 z-50 w-44 bg-white rounded-xl ring-1 ring-slate-200 shadow-xl overflow-hidden py-1">
              <li v-for="qf in quickFilters" :key="qf.key">
                <button @click="applyQuick(qf.key)" class="w-full text-left px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 transition">{{ qf.label }}</button>
              </li>
            </ul>
          </div>
          <input v-model="startDate" type="date" class="h-10 px-3 text-sm text-slate-700 bg-white ring-1 ring-slate-200 border-0 rounded-xl focus:ring-2 focus:ring-blue-400 transition" />
          <span class="text-sm text-slate-400 font-semibold">to</span>
          <input v-model="endDate" type="date" class="h-10 px-3 text-sm text-slate-700 bg-white ring-1 ring-slate-200 border-0 rounded-xl focus:ring-2 focus:ring-blue-400 transition" />
          <button @click="filterData" class="h-10 px-4 text-sm font-semibold text-white bg-slate-800 rounded-xl hover:bg-slate-700 active:scale-95 transition">
            <i class="ri-filter-3-line mr-1"></i> Filter
          </button>
          <Link href="/reports/order-type-report" class="h-10 px-4 flex items-center text-sm font-semibold text-slate-600 bg-white ring-1 ring-slate-200 rounded-xl hover:bg-slate-100 transition">
            <i class="ri-refresh-line mr-1"></i> Reset
          </Link>
        </div>
      </div>

      <!-- KPI Pills -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="rounded-2xl bg-gradient-to-br from-sky-500 to-blue-700 p-4 flex flex-col gap-1 shadow-md">
          <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Order Types</p>
          <p class="text-2xl font-bold text-white">{{ rows.length }}</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-4 flex flex-col gap-1 shadow-md">
          <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Total Revenue (LKR)</p>
          <p class="text-2xl font-bold text-white">{{ fmt(grandTotal) }}</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-violet-500 to-purple-700 p-4 flex flex-col gap-1 shadow-md">
          <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Transactions</p>
          <p class="text-2xl font-bold text-white">{{ grandTransactions.toLocaleString() }}</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-rose-500 to-red-700 p-4 flex flex-col gap-1 shadow-md">
          <p class="text-xs font-semibold text-white/70 uppercase tracking-wider">Total Profit (LKR)</p>
          <p class="text-2xl font-bold text-white">{{ fmt(grandProfit) }}</p>
        </div>
      </div>

      <!-- Chart + Table -->
      <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        <!-- Doughnut Chart -->
        <div class="lg:col-span-2 bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm p-5 flex flex-col">
          <h2 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i class="ri-donut-chart-line text-sky-500"></i> Revenue by Order Type
          </h2>
          <div class="flex-1 flex items-center justify-center" style="min-height:280px">
            <Doughnut :data="chartData" :options="chartOptions" />
          </div>
        </div>

        <!-- Table -->
        <div class="lg:col-span-3 bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm p-5">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
              <i class="ri-table-2 text-sky-500"></i> Order Type Breakdown
            </h2>
            <div class="flex items-center gap-2">
              <button @click="downloadPDF" class="h-9 px-3 inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-rose-600 rounded-xl hover:bg-rose-700 active:scale-95 transition">
                <i class="ri-file-pdf-line"></i> PDF
              </button>
              <button @click="downloadExcel" class="h-9 px-3 inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 active:scale-95 transition">
                <i class="ri-file-excel-2-line"></i> Excel
              </button>
            </div>
          </div>

          <div class="overflow-x-auto rounded-xl ring-1 ring-slate-200">
            <table class="w-full text-sm text-slate-700 bg-white">
              <thead>
                <tr class="bg-slate-800 text-white text-xs">
                  <th class="p-3 text-left font-semibold">#</th>
                  <th class="p-3 text-left font-semibold">Order Type</th>
                  <th class="p-3 text-right font-semibold">Transactions</th>
                  <th class="p-3 text-right font-semibold">Revenue (LKR)</th>
                  <th class="p-3 text-right font-semibold">Discount (LKR)</th>
                  <th class="p-3 text-right font-semibold">Profit (LKR)</th>
                  <th class="p-3 text-right font-semibold">Share %</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="(r, i) in rows" :key="i" class="hover:bg-slate-50 transition">
                  <td class="p-3 text-slate-400 text-center">{{ i + 1 }}</td>
                  <td class="p-3 font-semibold capitalize">{{ r.order_type }}</td>
                  <td class="p-3 text-right">{{ r.transactions.toLocaleString() }}</td>
                  <td class="p-3 text-right font-semibold">{{ fmt(r.total) }}</td>
                  <td class="p-3 text-right text-amber-600">{{ fmt(r.discount) }}</td>
                  <td class="p-3 text-right text-emerald-600">{{ fmt(r.profit) }}</td>
                  <td class="p-3 text-right">
                    <span class="inline-flex items-center gap-1">
                      <span class="w-14 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <span class="block h-1.5 bg-sky-500 rounded-full" :style="{ width: share(r.total) + '%' }"></span>
                      </span>
                      {{ share(r.total).toFixed(1) }}%
                    </span>
                  </td>
                </tr>
              </tbody>
              <tfoot class="bg-slate-50 font-bold text-sm border-t-2 border-slate-200">
                <tr>
                  <td class="p-3 text-right" colspan="2">Totals</td>
                  <td class="p-3 text-right">{{ grandTransactions.toLocaleString() }}</td>
                  <td class="p-3 text-right">{{ fmt(grandTotal) }}</td>
                  <td class="p-3 text-right text-amber-600">{{ fmt(grandDiscount) }}</td>
                  <td class="p-3 text-right text-emerald-600">{{ fmt(grandProfit) }}</td>
                  <td class="p-3 text-right">100%</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
  <Footer />
</template>

<script setup>
import { ref, computed } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { Doughnut } from "vue-chartjs";
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement } from "chart.js";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import * as XLSX from "xlsx";

ChartJS.register(Title, Tooltip, Legend, ArcElement);

const props = defineProps({
  rows:        { type: Array,  default: () => [] },
  startDate:   { type: String, default: "" },
  endDate:     { type: String, default: "" },
  companyInfo: { type: Object, default: () => ({}) },
});

const startDate = ref(props.startDate);
const endDate   = ref(props.endDate);
const showQuickFilter = ref(false);

const fmt  = (n) => Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const safe = (s) => String(s || "all").replace(/[^\dA-Za-z-]/g, "_");

const grandTotal        = computed(() => props.rows.reduce((s, r) => s + r.total, 0));
const grandTransactions = computed(() => props.rows.reduce((s, r) => s + r.transactions, 0));
const grandDiscount     = computed(() => props.rows.reduce((s, r) => s + r.discount, 0));
const grandProfit       = computed(() => props.rows.reduce((s, r) => s + r.profit, 0));
const share = (total)   => grandTotal.value ? (total / grandTotal.value) * 100 : 0;

const dateRangeLabel = computed(() => {
  if (props.startDate && props.endDate) return `${props.startDate}  →  ${props.endDate}`;
  return "All time";
});

const quickFilters = [
  { key: "today", label: "Today" }, { key: "yesterday", label: "Yesterday" },
  { key: "this_week", label: "This Week" }, { key: "last_week", label: "Last Week" },
  { key: "this_month", label: "This Month" }, { key: "last_month", label: "Last Month" },
  { key: "this_year", label: "This Year" }, { key: "last_year", label: "Last Year" },
];

const applyQuick = (period) => {
  const t = new Date(); t.setHours(0,0,0,0);
  const fmt2 = (d) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
  let s, e;
  switch (period) {
    case "today":      s = e = fmt2(t); break;
    case "yesterday":  { const y=new Date(t); y.setDate(t.getDate()-1); s=e=fmt2(y); break; }
    case "this_week":  { const d=t.getDay(), di=d===0?-6:1-d; const m=new Date(t); m.setDate(t.getDate()+di); const n=new Date(m); n.setDate(m.getDate()+6); s=fmt2(m); e=fmt2(n); break; }
    case "last_week":  { const d=t.getDay(), di=d===0?-6:1-d; const m=new Date(t); m.setDate(t.getDate()+di-7); const n=new Date(m); n.setDate(m.getDate()+6); s=fmt2(m); e=fmt2(n); break; }
    case "this_month": s=fmt2(new Date(t.getFullYear(),t.getMonth(),1)); e=fmt2(new Date(t.getFullYear(),t.getMonth()+1,0)); break;
    case "last_month": s=fmt2(new Date(t.getFullYear(),t.getMonth()-1,1)); e=fmt2(new Date(t.getFullYear(),t.getMonth(),0)); break;
    case "this_year":  s=fmt2(new Date(t.getFullYear(),0,1)); e=fmt2(new Date(t.getFullYear(),11,31)); break;
    case "last_year":  s=fmt2(new Date(t.getFullYear()-1,0,1)); e=fmt2(new Date(t.getFullYear()-1,11,31)); break;
  }
  startDate.value = s; endDate.value = e;
  showQuickFilter.value = false;
  filterData();
};

const filterData = () => {
  router.get(route("reports.orderTypeReport"), { start_date: startDate.value, end_date: endDate.value }, { preserveScroll: true });
};

const COLORS = ["#0ea5e9","#8b5cf6","#10b981","#f59e0b","#ef4444","#3b82f6","#ec4899","#14b8a6","#f97316","#6366f1"];
const chartData = computed(() => ({
  labels: props.rows.map(r => r.order_type),
  datasets: [{ data: props.rows.map(r => r.total), backgroundColor: props.rows.map((_, i) => COLORS[i % COLORS.length]), borderWidth: 2, borderColor: "#fff" }],
}));
const chartOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: "bottom", labels: { boxWidth: 12, padding: 16 } }, tooltip: { callbacks: { label: (c) => ` LKR ${Number(c.raw).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}` } } } };

const downloadPDF = () => {
  const doc = new jsPDF("l", "mm", "a4");
  const pW = doc.internal.pageSize.getWidth();
  const company = props.companyInfo || {};
  doc.setFillColor(3, 105, 161);
  doc.rect(0, 0, pW, 22, "F");
  doc.setTextColor(255,255,255); doc.setFontSize(14); doc.setFont("helvetica","bold");
  doc.text((company.name || "Hotel System").toUpperCase(), 14, 10);
  doc.setFontSize(8); doc.setFont("helvetica","normal");
  doc.text([company.phone, company.address].filter(Boolean).join("  |  "), pW - 14, 10, { align: "right" });
  doc.text("Order Type Report", 14, 17);
  doc.text(`Generated: ${new Date().toLocaleString()}`, pW - 14, 17, { align: "right" });

  doc.setTextColor(30,30,30); doc.setFontSize(12); doc.setFont("helvetica","bold");
  doc.text("Order Type Report", 14, 32);
  doc.setFontSize(9); doc.setFont("helvetica","normal"); doc.setTextColor(100,116,139);
  doc.text(dateRangeLabel.value ? `Period: ${dateRangeLabel.value}` : "Period: All time", 14, 38);
  doc.setDrawColor(226,232,240); doc.setLineWidth(0.4); doc.line(14, 41, pW - 14, 41);

  autoTable(doc, {
    startY: 46,
    margin: { left: 14, right: 14 },
    head: [["#","Order Type","Transactions","Revenue (LKR)","Discount (LKR)","Profit (LKR)","Share %"]],
    body: props.rows.map((r, i) => [i+1, r.order_type, r.transactions.toLocaleString(), fmt(r.total), fmt(r.discount), fmt(r.profit), share(r.total).toFixed(1)+"%"]),
    foot: [["","Totals", grandTransactions.value.toLocaleString(), fmt(grandTotal.value), fmt(grandDiscount.value), fmt(grandProfit.value), "100%"]],
    theme: "grid",
    headStyles: { fillColor: [3,105,161], textColor: 255, fontStyle: "bold", fontSize: 8 },
    footStyles: { fillColor: [30,41,59], textColor: 255, fontStyle: "bold" },
    alternateRowStyles: { fillColor: [248,250,252] },
    styles: { fontSize: 8, cellPadding: 3 },
    columnStyles: { 0:{cellWidth:10,halign:"center"}, 2:{halign:"right"}, 3:{halign:"right"}, 4:{halign:"right"}, 5:{halign:"right"}, 6:{halign:"right"} },
    didDrawPage: (d) => {
      const pg = doc.internal.getCurrentPageInfo().pageNumber;
      const total = doc.internal.getNumberOfPages();
      doc.setFontSize(7); doc.setTextColor(148,163,184);
      doc.text(`Page ${pg} of ${total}`, pW-14, doc.internal.pageSize.getHeight()-6, { align:"right" });
    },
  });
  doc.save(`OrderTypeReport_${safe(dateRangeLabel.value)}.pdf`);
};

const downloadExcel = () => {
  const header = ["#","Order Type","Transactions","Revenue (LKR)","Discount (LKR)","Profit (LKR)","Share %"];
  const data = props.rows.map((r, i) => [i+1, r.order_type, r.transactions, r.total, r.discount, r.profit, share(r.total).toFixed(1)+"%"]);
  data.push(["","Totals", grandTransactions.value, grandTotal.value, grandDiscount.value, grandProfit.value, "100%"]);
  const ws = XLSX.utils.aoa_to_sheet([header, ...data]);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, "Order Type Report");
  XLSX.writeFile(wb, `OrderTypeReport_${safe(dateRangeLabel.value)}.xlsx`);
};
</script>
