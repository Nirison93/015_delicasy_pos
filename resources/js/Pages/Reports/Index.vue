<template>
  <Head title="Reports" />
  <Banner />
  <div class="reports-page min-h-screen bg-slate-50">
    <Header />

    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 py-6">

      <!-- Page header + date filters -->
      <div class="flex flex-col gap-4 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center gap-3">
            <Link href="/" class="w-11 h-11 flex items-center justify-center rounded-xl bg-white ring-1 ring-slate-200 text-slate-600 hover:bg-slate-100 transition">
              <i class="ri-arrow-left-s-line text-2xl"></i>
            </Link>
            <h1 class="text-3xl font-bold text-slate-800 tracking-wide uppercase">Reports</h1>
          </div>

          <div class="flex flex-wrap items-center gap-3">

            <!-- Quick Filter Dropdown -->
            <div class="relative">
              <button @click="showQuickFilter = !showQuickFilter"
                      class="h-11 px-5 inline-flex items-center gap-2 text-xl font-semibold text-indigo-700 bg-indigo-50 ring-1 ring-indigo-200 rounded-xl hover:bg-indigo-100 transition select-none">
                <i class="ri-calendar-2-line"></i>
                Quick Filter
                <i :class="showQuickFilter ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line'"></i>
              </button>
              <!-- Backdrop -->
              <div v-if="showQuickFilter" class="fixed inset-0 z-40" @click="showQuickFilter = false"></div>
              <!-- Menu -->
              <ul v-if="showQuickFilter"
                  class="absolute left-0 top-full mt-2 z-50 w-48 bg-white rounded-xl ring-1 ring-slate-200 shadow-xl overflow-hidden py-1">
                <li v-for="qf in quickFilterOptions" :key="qf.key">
                  <button @click="applyQuickFilter(qf.key)"
                          class="w-full text-left px-4 py-2.5 text-base font-semibold text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 transition">
                    {{ qf.label }}
                  </button>
                </li>
              </ul>
            </div>

            <input v-model="startDate" type="date"
                   class="h-11 px-4 text-xl font-medium text-slate-700 bg-white ring-1 ring-slate-200 border-0 rounded-xl focus:ring-2 focus:ring-blue-400 transition" />
            <span class="text-xl font-semibold text-slate-400">to</span>
            <input v-model="endDate" type="date"
                   class="h-11 px-4 text-xl font-medium text-slate-700 bg-white ring-1 ring-slate-200 border-0 rounded-xl focus:ring-2 focus:ring-blue-400 transition" />
            <button @click="filterData"
                    class="h-11 px-5 text-xl font-semibold text-white bg-slate-800 rounded-xl hover:bg-slate-700 active:scale-95 transition">
              <i class="ri-filter-3-line mr-1"></i> Filter
            </button>
            <Link href="/reports"
                  class="h-11 px-5 flex items-center text-xl font-semibold text-slate-600 bg-white ring-1 ring-slate-200 rounded-xl hover:bg-slate-100 transition">
              <i class="ri-refresh-line mr-1"></i> Reset
            </Link>
          </div>
        </div>
      </div>

      <!-- KPI Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        <!-- Total Sales Amount -->
        <div class="rounded-2xl bg-gradient-to-br from-orange-500 to-amber-500 p-5 flex flex-col items-center text-center gap-2 shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
          <div class="w-11 h-11 rounded-full bg-white/20 backdrop-blur flex items-center justify-center">
            <i class="ri-money-dollar-circle-line text-2xl text-white"></i>
          </div>
          <p class="text-base font-semibold text-white/80 uppercase tracking-wider">Total Sales Amount</p>
          <p class="text-xl font-bold text-white">
            {{ finalSalesAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} <span class="text-sm text-white/70">LKR</span>
          </p>
        </div>
        <!-- Net Profit -->
        <div class="rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 p-5 flex flex-col items-center text-center gap-2 shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
          <div class="w-11 h-11 rounded-full bg-white/20 backdrop-blur flex items-center justify-center">
            <i class="ri-line-chart-line text-2xl text-white"></i>
          </div>
          <p class="text-base font-semibold text-white/80 uppercase tracking-wider">Net Profit</p>
          <p class="text-xl font-bold text-white">{{ salesProfitTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} <span class="text-sm text-white/70">LKR</span></p>
        </div>
        <!-- Total Discount -->
        <div class="rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-500 p-5 flex flex-col items-center text-center gap-2 shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
          <div class="w-11 h-11 rounded-full bg-white/20 backdrop-blur flex items-center justify-center">
            <i class="ri-percent-line text-2xl text-white"></i>
          </div>
          <p class="text-base font-semibold text-white/80 uppercase tracking-wider">Total Discount</p>
          <p class="text-xl font-bold text-white">{{ totalDiscounts.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} <span class="text-sm text-white/70">LKR</span></p>
        </div>
        <!-- Transactions -->
        <div class="rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 p-5 flex flex-col items-center text-center gap-2 shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
          <div class="w-11 h-11 rounded-full bg-white/20 backdrop-blur flex items-center justify-center">
            <i class="ri-exchange-line text-2xl text-white"></i>
          </div>
          <p class="text-base font-semibold text-white/80 uppercase tracking-wider">Transactions</p>
          <p class="text-xl font-bold text-white">{{ totalTransactions }}</p>
        </div>
        <!-- Total Customers -->
        <div class="rounded-2xl bg-gradient-to-br from-pink-500 to-rose-500 p-5 flex flex-col items-center text-center gap-2 shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
          <div class="w-11 h-11 rounded-full bg-white/20 backdrop-blur flex items-center justify-center">
            <i class="ri-team-line text-2xl text-white"></i>
          </div>
          <p class="text-base font-semibold text-white/80 uppercase tracking-wider">Total Customers</p>
          <p class="text-xl font-bold text-white">{{ totalCustomer }}</p>
        </div>
      </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm p-4 sm:p-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <h2 class="text-lg font-bold text-xl text-slate-800 flex items-center gap-2">
          <i class="ri-file-list-3-line text-2xl text-slate-500"></i> Sales Table
        </h2>
        <div class="flex items-center gap-2">
          <button @click="downloadSalesTablePDF"
            class="h-10 px-4 inline-flex items-center gap-2 text-xl font-semibold text-white bg-rose-600 rounded-xl hover:bg-rose-700 active:scale-95 transition shadow-sm">
            <i class="ri-file-pdf-line"></i> Download PDF
          </button>
          <button @click="downloadSalesTableExcel"
            class="h-10 px-4 inline-flex items-center gap-2 text-xl font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 active:scale-95 transition shadow-sm">
            <i class="ri-file-excel-2-line"></i> Download Excel
          </button>
        </div>
      </div>

      <!-- Summary pills -->
      <div class="flex flex-wrap gap-2 mb-4">
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-xl font-semibold ring-1 ring-emerald-200">
          <i class="ri-shopping-bag-line"></i> Qty: {{ salesTotalQty.toLocaleString() }}
        </span>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-xl font-semibold ring-1 ring-blue-200">
          <i class="ri-money-dollar-circle-line"></i> Selling: {{ finalSalesAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} LKR
        </span>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 text-xl font-semibold ring-1 ring-amber-200">
          <i class="ri-percent-line"></i> Discounts: {{ totalDiscounts.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} LKR
        </span>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 text-xl font-semibold ring-1 ring-rose-200">
          <i class="ri-line-chart-line"></i> Profit: {{ salesProfitTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} LKR
        </span>
      </div>

      <div class="overflow-x-auto overflow-y-auto max-h-[480px] rounded-xl ring-1 ring-slate-200">
        <table id="salesTbl" class="sales-table w-full text-slate-700 bg-white min-w-[1100px]">
          <colgroup>
            <col style="width:48px" />
            <col style="width:110px" />
            <col style="width:170px" />
            <col style="width:200px" />
            <col style="width:70px" />
            <col style="width:140px" />
            <col style="width:120px" />
            <col style="width:160px" />
            <col style="width:160px" />
            <col style="width:140px" />
            <col style="width:160px" />
            <col style="width:140px" />
          </colgroup>

          <thead class="sticky top-0 z-10">
            <tr class="bg-slate-800 text-white text-base">
              <th class="p-3 text-left font-semibold">#</th>
              <th class="p-3 text-left font-semibold">Date</th>
              <th class="p-3 text-left font-semibold">Order Number</th>
              <th class="p-3 text-left font-semibold">Customer</th>
              <th class="p-3 text-center font-semibold">Dish Qty</th>
              <th class="p-3 num font-semibold">Total Price</th>
              <th class="p-3 num font-semibold">Service (%)</th>
              <th class="p-3 num font-semibold">Price + Service</th>
              <th class="p-3 num font-semibold">Cust. Discount</th>
              <th class="p-3 num font-semibold">Owner</th>
              <th class="p-3 num font-semibold">Owner Disc.</th>
              <th class="p-3 num font-semibold">Profit</th>
            </tr>
          </thead>

          <tbody class="text-sm font-medium divide-y divide-slate-100">
            <tr v-for="(s, i) in sales" :key="s.id ?? i" class="transition hover:bg-slate-50">
              <td class="p-3 text-center text-slate-400">{{ i + 1 }}</td>
              <td class="p-3 whitespace-nowrap text-center">{{ formatDate(s.sale_date) }}</td>
              <td class="p-3 text-center">{{ s.order_id ? s.order_id : 'Service -' }} {{ s.service_name }}</td>
              <td class="p-3">{{ s.customer?.name ?? 'N/A' }}</td>
              <td class="p-3 text-center">{{ saleQty(s) }}</td>
              <td class="p-3 num text-center">{{ toMoney(Number(s.total_amount || 0)) }}</td>
              <td class="p-3 num text-center">{{ Number(s.service_charge || 0).toFixed(2) }}%</td>
              <td class="p-3 num text-right">{{ toMoney(priceWithService(s)) }}</td>
              <td class="p-3 num text-center">{{ toMoney(customerDiscountAmount(s)) }}</td>
              <td class="p-3 text-right">{{ (s.owner_discount_value && s.owner_discount_value != 0) ? (s.owner?.name ?? '—') : '—' }}</td>
              <td class="p-3 num text-center">{{ (s.owner_discount_value && s.owner_discount_value != 0) ? toMoney(s.owner_discount_value) : '—' }}</td>
              <td class="p-3 num text-center">
                {{ (Number(s.total_amount ?? 0) - Number(s.total_cost ?? 0)).toFixed(2) }}
              </td>
            </tr>
          </tbody>

          <tfoot class="bg-slate-50 text-sm font-bold text-slate-700 border-t-2 border-slate-200">
            <tr>
              <td class="p-3 text-right" colspan="4">Totals:</td>
              <td class="p-3 text-center">{{ salesTotalQty.toLocaleString() }}</td>
              <td class="p-3 num text-center">{{ salesGrossTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
              <td class="p-3 num text-center">—</td>
              <td class="p-3 num text-center">{{ salesWithServiceTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
              <td class="p-3 num text-center">{{ salesCustomerDiscountTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
              <td class="p-3 num text-center">—</td>
              <td class="p-3 num text-center">{{ salesOwnerDiscountTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
              <td class="p-3 num text-center">{{ salesProfitTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <!-- Employee Sales Chart -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm p-5 flex flex-col min-h-[420px]">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-base text-xl font-bold text-slate-800 flex items-center gap-2">
            <i class="ri-team-line text-xl text-slate-500"></i> Employee Sales
          </h2>
          <button @click="downloadEmployeeSalesPDF"
            class="h-8 px-3 inline-flex items-center gap-1 text-sm font-semibold text-white bg-slate-700 rounded-lg hover:bg-slate-600 active:scale-95 transition">
            <i class="ri-download-line"></i> PDF
          </button>
        </div>
        <div class="chart-container flex-1 flex items-center justify-center">
          <Doughnut :data="chartData4" :options="chartOptions4" />
        </div>
      </div>

      <!-- Product Sales Chart -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm p-5 flex flex-col min-h-[420px]">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-base text-xl font-bold text-slate-800 flex items-center gap-2">
            <i class="ri-pie-chart-line text-xl text-slate-500"></i> Product Sales
          </h2>
          <button @click="downloadProductQtyPDF"
            class="h-8 px-3 inline-flex items-center gap-1 text-sm font-semibold text-white bg-slate-700 rounded-lg hover:bg-slate-600 active:scale-95 transition">
            <i class="ri-download-line"></i> PDF
          </button>
        </div>
        <div class="chart-container flex-1 flex items-center justify-center">
          <Pie :data="chartData" :options="chartOptions" />
        </div>
      </div>

      <!-- Payment Methods Chart -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm p-5 flex flex-col min-h-[420px]">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-base text-xl font-bold text-slate-800 flex items-center gap-2">
            <i class="ri-bank-card-line text-xl text-slate-500"></i> Payment Methods
          </h2>
          <button @click="downloadPaymentMethodPDF"
            class="h-8 px-3 inline-flex items-center gap-1 text-sm font-semibold text-white bg-slate-700 rounded-lg hover:bg-slate-600 active:scale-95 transition">
            <i class="ri-download-line"></i> PDF
          </button>
        </div>
        <div class="chart-container flex-1 flex items-center justify-center">
          <Doughnut :data="chartData1" :options="chartOptions1" />
        </div>
      </div>
    </div>

    <!-- Top Products Stock Table -->
    <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm p-4 sm:p-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
          <i class="ri-bar-chart-box-line text-xl text-slate-500"></i> Top Products Stock Table
        </h2>
        <button @click="downloadStockTablePDF"
          class="h-10 px-4 inline-flex items-center gap-2 text-xl font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 active:scale-95 transition shadow-sm">
          <i class="ri-file-pdf-line"></i> Download PDF
        </button>
      </div>

      <!-- Summary pills -->
      <div class="flex flex-wrap gap-2 mb-4">
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-xl font-semibold ring-1 ring-emerald-200">
          <i class="ri-shopping-bag-line"></i> Total Sales Qty: {{ totalSalesQty.toLocaleString() }}
        </span>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-xl font-semibold ring-1 ring-blue-200">
          <i class="ri-line-chart-line"></i> Total Profit: {{ grandTotalProfit.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} LKR
        </span>
      </div>

      <div class="overflow-x-auto max-h-[420px] rounded-xl ring-1 ring-slate-200">
        <table id="stockQtyTbl" class="w-full text-slate-700 bg-white table-auto min-w-[800px]">
          <thead class="sticky top-0 z-10">
            <tr class="bg-slate-800 text-white text-base">
              <th class="p-3 text-left font-semibold">#</th>
              <th class="p-3 text-left font-semibold">Product</th>
              <th class="p-3 text-center font-semibold">Sales Qty</th>
              <th class="p-3 text-center font-semibold">Total Sales Value (LKR)</th>
              <th class="p-3 text-center font-semibold">Price (LKR)</th>
              <th class="p-3 text-center font-semibold">Discount</th>
              <th class="p-3 text-center font-semibold">Price After Discount</th>
              <th class="p-3 text-center font-semibold">Profit</th>
            </tr>
          </thead>
          <tbody class="text-sm font-medium divide-y divide-slate-100">
            <tr v-for="(p, i) in products" :key="p.id ?? i" class="transition hover:bg-slate-50">
              <td class="p-3 text-center text-slate-400">{{ i + 1 }}</td>
              <td class="p-3 font-bold">{{ p.name || 'N/A' }}</td>
              <td class="p-3 text-center">{{ Number(p.sales_qty || 0) }}</td>
              <td class="p-3 text-center">{{ (Number(p.sales_qty || 0) * Number(p.selling_price || 0)).toFixed(2) }}</td>
              <td class="p-3 text-center">{{ Number(p.selling_price || 0).toFixed(2) }}</td>
              <td class="p-3 text-center">
                <span v-if="Number(p.discount || 0) <= 100">{{ Number(p.discount || 0) }}%</span>
                <span v-else>Rs. {{ Number(p.discount).toFixed(2) }}</span>
              </td>
              <td class="p-3 text-center">{{ priceAfterDiscount(p).toFixed(2) }}</td>
              <td class="p-3 text-center">{{ totalProfit(p).toFixed(2) }} LKR</td>
            </tr>
          </tbody>
          <tfoot class="bg-slate-50 text-sm font-bold text-slate-700 border-t-2 border-slate-200">
            <tr>
              <td class="p-3 text-right" colspan="2">Totals:</td>
              <td class="p-3 text-right">{{ totalSalesQty.toLocaleString() }}</td>
              <td class="p-3 text-right"></td>
              <td class="p-3 text-right"></td>
              <td class="p-3 text-right"></td>
              <td class="p-3 text-right"></td>
              <td class="p-3 text-right">{{ grandTotalProfit.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} LKR</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

  </div>
  </div>
  <Footer />
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Doughnut, Pie } from "vue-chartjs";
import { Link, router, Head } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import jsPDF from "jspdf";
import * as XLSX from "xlsx";
import autoTable from "jspdf-autotable";

import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  CategoryScale,
  LinearScale,
  BarElement,
} from "chart.js";

ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale, LinearScale, BarElement);

// Props
const props = defineProps({
  products: { type: Array, required: true },
  sales: { type: Array, required: true },
  ownersList: { type: Array, required: true },
  totalSaleAmount: { type: Number, required: true },
  averageTransactionValue: { type: Number, required: true },
  netProfit: { type: Number, required: true },
  totalTransactions: { type: Number, required: true },
  totalDiscountLkr: { type: Number, required: true },
  totalCustomDiscountLkr: { type: Number, required: true },
  totalCustomer: { type: Number, required: true },
  startDate: { type: String, default: "" },
  endDate: { type: String, default: "" },
  categorySales: { type: Object, required: true },
  employeeSalesSummary: { type: Object, required: true },
  stockTransactionsReturn: { type: Array, default: () => [] },
  companyInfo: { type: Object, default: () => ({}) },
});

// State
const startDate = ref(props.startDate);
const endDate = ref(props.endDate);
const products = ref(props.products);
const sales = ref(props.sales);

// ---------- Shared helpers ----------
const safe = (s) => String(s).replace(/[^\dA-Za-z-]/g, "_");

const toMoney = (n) => (Number(n || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const formatDate = (d) => (d ? new Date(d).toLocaleDateString() : "");

// Sales table calculations
const saleQty = (s) => (Array.isArray(s.sale_items) ? s.sale_items.reduce((n, it) => n + Number(it.quantity || 0), 0) : 0);

const customerDiscountAmount = (s) => {
  const gross = Number(s.total_amount || 0);
  const val = Number(s.custom_discount || 0);
  const type = s.custom_discount_type || "fixed";
  return type === "percent" ? (gross * val) / 100 : val;
};

const priceWithService = (s) => {
  const gross = Number(s.total_amount || 0);
  const svc = (gross * Number(s.service_charge || 0)) / 100;
  return gross + svc;
};

const saleProfit = (s) => {
  const gross = Number(s.total_amount || 0);
  const svc = (gross * Number(s.service_charge || 0)) / 100;
  const customerDisc = customerDiscountAmount(s);
  const ownerDisc = Number(s.owner_discount_value || 0);
  const finalPrice = Math.max(0, gross + svc - customerDisc - ownerDisc);
  const cost = Number(s.total_cost || 0);
  return finalPrice - cost;
};

// Sales totals
const salesTotalQty = computed(() => (sales.value || []).reduce((a, s) => a + saleQty(s), 0));

const salesGrossTotal = computed(() => 
  (sales.value || []).reduce((a, s) => a + Number(s.total_amount || 0), 0)
);

const salesWithServiceTotal = computed(() => 
  (sales.value || []).reduce((a, s) => a + priceWithService(s), 0)
);

const salesCustomerDiscountTotal = computed(() => 
  (sales.value || []).reduce((a, s) => a + customerDiscountAmount(s), 0)
);

const salesOwnerDiscountTotal = computed(() => 
  (sales.value || []).reduce((sum, s) => sum + Number(s.owner_discount_value || 0), 0)
);

const totalDiscounts = computed(() => 
  salesCustomerDiscountTotal.value + salesOwnerDiscountTotal.value
);

const finalSalesAmount = computed(() => 
  Math.max(0, salesWithServiceTotal.value - salesCustomerDiscountTotal.value - salesOwnerDiscountTotal.value)
);

// Note: This mirrors the table's Profit column: (total_amount - total_cost)
const salesProfitTotal = computed(() =>
  (sales.value || []).reduce(
    (sum, s) => sum + (Number(s.total_amount ?? 0) - Number(s.total_cost ?? 0)),
    0
  )
);

// Product table calculations
const priceAfterDiscount = (product) => {
  const price = Number(product.selling_price || 0);
  const discount = Number(product.discount || 0);
  return discount <= 100 ? price * (1 - discount / 100) : price - discount;
};

const profitPerUnit = (product) => priceAfterDiscount(product) - Number(product.cost_price || 0);
const totalProfit = (product) => profitPerUnit(product) * Number(product.sales_qty || 0);

const totalSalesQty = computed(() => products.value.reduce((s, p) => s + Number(p.sales_qty || 0), 0));
const grandTotalProfit = computed(() => products.value.reduce((s, p) => s + totalProfit(p), 0));

// Date filter
const filterData = () => {
  if (startDate.value && endDate.value && new Date(startDate.value) > new Date(endDate.value)) {
    alert("Start date cannot be greater than end date.");
    return;
  }
  router.get(
    route("reports.index"),
    { start_date: startDate.value, end_date: endDate.value },
    { preserveScroll: true, preserveState: false }
  );
};

// Quick Filters
const showQuickFilter = ref(false);

const quickFilterOptions = [
  { key: 'today',      label: 'Today' },
  { key: 'yesterday',  label: 'Yesterday' },
  { key: 'this_week',  label: 'This Week' },
  { key: 'last_week',  label: 'Last Week' },
  { key: 'this_month', label: 'This Month' },
  { key: 'last_month', label: 'Last Month' },
  { key: 'this_year',  label: 'This Year' },
  { key: 'last_year',  label: 'Last Year' },
];

const applyQuickFilter = (period) => {
  const now = new Date();
  const t = new Date(now.getFullYear(), now.getMonth(), now.getDate());
  const fmt = (d) => {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
  };

  let start, end;
  switch (period) {
    case 'today':
      start = end = fmt(t);
      break;
    case 'yesterday': {
      const y = new Date(t);
      y.setDate(t.getDate() - 1);
      start = end = fmt(y);
      break;
    }
    case 'this_week': {
      const dow = t.getDay();
      const diff = dow === 0 ? -6 : 1 - dow;
      const mon = new Date(t); mon.setDate(t.getDate() + diff);
      const sun = new Date(mon); sun.setDate(mon.getDate() + 6);
      start = fmt(mon); end = fmt(sun);
      break;
    }
    case 'last_week': {
      const dow = t.getDay();
      const diff = dow === 0 ? -6 : 1 - dow;
      const mon = new Date(t); mon.setDate(t.getDate() + diff - 7);
      const sun = new Date(mon); sun.setDate(mon.getDate() + 6);
      start = fmt(mon); end = fmt(sun);
      break;
    }
    case 'this_month':
      start = fmt(new Date(t.getFullYear(), t.getMonth(), 1));
      end   = fmt(new Date(t.getFullYear(), t.getMonth() + 1, 0));
      break;
    case 'last_month':
      start = fmt(new Date(t.getFullYear(), t.getMonth() - 1, 1));
      end   = fmt(new Date(t.getFullYear(), t.getMonth(), 0));
      break;
    case 'this_year':
      start = fmt(new Date(t.getFullYear(), 0, 1));
      end   = fmt(new Date(t.getFullYear(), 11, 31));
      break;
    case 'last_year':
      start = fmt(new Date(t.getFullYear() - 1, 0, 1));
      end   = fmt(new Date(t.getFullYear() - 1, 11, 31));
      break;
  }

  startDate.value = start;
  endDate.value   = end;
  showQuickFilter.value = false;
  filterData();
};

// Charts
const sortDescending = (data) => 
  Object.entries(data).sort((a, b) => b[1] - a[1]).reduce((acc, [k, v]) => ((acc[k] = v), acc), {});

const productQuantities = computed(() => {
  const quantities = {};
  (props.sales || []).forEach((sale) => {
    (sale.sale_items || []).forEach((item) => {
      const name = item.product && item.product.name ? item.product.name : "N/A";
      quantities[name] = (quantities[name] || 0) + Number(item.quantity || 0);
    });
  });
  return sortDescending(quantities);
});

const chartData = computed(() => ({
  labels: Object.keys(productQuantities.value),
  datasets: [{
    data: Object.values(productQuantities.value),
    backgroundColor: [
      "#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0", "#9966FF", "#28a745",
      "#ffc107", "#17a2b8", "#e83e8c", "#fd7e14", "#6610f2", "#6f42c1",
      "#dc3545", "#adb5bd", "#20c997", "#ffc93c", "#6a0572", "#8ac926",
      "#ff595e", "#198754"
    ],
  }],
}));

const chartOptions = { 
  responsive: true, 
  maintainAspectRatio: false,
  plugins: { 
    legend: { display: true, position: "bottom" } 
  } 
};

const paymentMethodTotals = computed(() => {
  const totals = {};
  (props.sales || []).forEach((s) => {
    const m = s.payment_method || "N/A";
    totals[m] = (totals[m] || 0) + (parseFloat(s.total_amount) || 0);
  });
  return sortDescending(totals);
});

const chartData1 = computed(() => ({
  labels: Object.keys(paymentMethodTotals.value),
  datasets: [{
    data: Object.values(paymentMethodTotals.value),
    backgroundColor: [
      "#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0", "#9966FF", "#28a745",
      "#ffc107", "#17a2b8", "#e83e8c", "#fd7e14", "#6610f2", "#6f42c1",
      "#dc3545", "#adb5bd", "#20c997", "#ffc93c", "#6a0572", "#8ac926",
      "#198754"
    ],
  }],
}));

const chartOptions1 = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: true, position: "bottom" },
    tooltip: { callbacks: { label: (c) => `LKR ${(+c.raw || 0).toLocaleString()}` } },
  },
};

const sortedEmployeeSales = computed(() =>
  Object.fromEntries(
    Object.entries(props.employeeSalesSummary).sort(([, a], [, b]) => 
      b["Total Sales Amount"] - a["Total Sales Amount"]
    )
  )
);

const chartData4 = computed(() => ({
  labels: Object.keys(sortedEmployeeSales.value),
  datasets: [{
    data: Object.values(sortedEmployeeSales.value).map(entry => entry["Total Sales Amount"] || 0),
    backgroundColor: [
      "#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0", "#9966FF", "#28a745",
      "#ffc107", "#17a2b8", "#e83e8c", "#fd7e14", "#6610f2", "#6f42c1",
      "#dc3545", "#adb5bd", "#20c997", "#ffc93c", "#6a0572", "#8ac926",
      "#ff595e", "#198754"
    ],
  }],
}));

const chartOptions4 = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: true, position: "bottom" },
    tooltip: { callbacks: { label: (c) => `LKR ${(+c.raw).toLocaleString()}` } },
  },
};

// Date range label for PDFs/filenames
const dateRangeLabel = computed(() => {
  const s = startDate.value ? new Date(startDate.value).toLocaleDateString() : "All";
  const e = endDate.value ? new Date(endDate.value).toLocaleDateString() : "All";
  return `${s} — ${e}`;
});

// ---------- XLSX: Sales Table Export ----------
const downloadSalesTableExcel = () => {
  const header = [
    "#","Date","Order Number","Customer","Dish Qty",
    "Total Price","Service Charge (%)","Price with Service",
    "Customer Discounts","Owner","Owner Discount","Profit"
  ];

  const rows = (sales.value || []).map((s, i) => {
    const qty = saleQty(s);
    const total = Number(s.total_amount || 0);
    const svcPct = Number(s.service_charge || 0);
    const priceWSvc = priceWithService(s);
    const custDisc = customerDiscountAmount(s);
    const owner = (s.owner_discount_value && s.owner_discount_value != 0) ? (s.owner?.name ?? "—") : "—";
    const ownerDisc = Number(s.owner_discount_value || 0);
    // Match the table Profit column (gross: total - cost)
    const profit = Number(s.total_amount ?? 0) - Number(s.total_cost ?? 0);

    return [
      i + 1,
      formatDate(s.sale_date),
      s.order_id ? s.order_id : `Service - ${s.service_name || ""}`,
      s.customer?.name ?? "N/A",
      qty,
      total,
      svcPct,            // numeric; header clarifies it's %
      priceWSvc,
      custDisc,
      owner,
      ownerDisc,
      profit,
    ];
  });

  
  const aoa = [header, ...rows];

  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.aoa_to_sheet(aoa);

  // Auto column widths
  const widths = header.map((h, c) => {
    const contentLens = aoa.map(r => (r[c] == null ? 0 : String(r[c]).length));
    const maxLen = Math.max(h.length, ...contentLens);
    return { wch: Math.min(Math.max(maxLen + 2, 12), 40) };
  });
  ws['!cols'] = widths;

  // Number formats for money/qty/percent-like
  const formatCols = {
    qty: 4,
    money: [5, 7, 8, 10, 11],
    percent: 6,
  };
  const range = XLSX.utils.decode_range(ws['!ref']);

  // Apply formats to data rows (skip header at R=0)
  for (let R = 1; R <= range.e.r; R++) {
    // qty
    const qCell = ws[XLSX.utils.encode_cell({ r: R, c: formatCols.qty })];
    if (qCell && typeof qCell.v === "number") qCell.z = "0";

    // money columns
    for (const C of formatCols.money) {
      const cell = ws[XLSX.utils.encode_cell({ r: R, c: C })];
      if (cell && typeof cell.v === "number") cell.z = "0.00";
    }

    // percent column (kept as number like 5 -> 5.00)
    const pCell = ws[XLSX.utils.encode_cell({ r: R, c: formatCols.percent })];
    if (pCell && typeof pCell.v === "number") pCell.z = "0.00";
  }

  XLSX.utils.book_append_sheet(wb, ws, "Sales");
  XLSX.writeFile(wb, `Sales_Report_${safe(dateRangeLabel.value)}.xlsx`);
};

// ---------- PDF/CSV Exports ----------
const downloadEmployeeSalesPDF = () => {
  const doc = new jsPDF();
  doc.text("Top Employee Sales", 14, 10);
  const rows = Object.entries(sortedEmployeeSales.value).map(([employee, entry]) => [
    employee,
    (entry["Total Sales Amount"] || 0).toLocaleString()
  ]);
  autoTable(doc, { 
    head: [["Employee", "Total Sales Amount"]], 
    body: rows, 
    startY: 20 
  });
  doc.save("EmployeeSales.pdf");
};

const downloadProductQtyPDF = () => {
  const doc = new jsPDF();
  doc.text("Product Quantities", 14, 10);
  const rows = Object.entries(productQuantities.value).map(([product, qty]) => [product, qty]);
  autoTable(doc, { 
    head: [["Product Name", "Quantity"]], 
    body: rows, 
    startY: 20 
  });
  doc.save("ProductQuantities.pdf");
};

const downloadPaymentMethodPDF = () => {
  const doc = new jsPDF();
  doc.text("Payment Method Totals", 14, 10);
  const rows = Object.entries(paymentMethodTotals.value).map(([m, t]) => [
    m,
    `LKR ${Number(t || 0).toLocaleString()}`
  ]);
  autoTable(doc, { 
    head: [["Payment Method", "Total Amount"]], 
    body: rows, 
    startY: 20 
  });
  doc.save("PaymentMethodTotals.pdf");
};

const downloadSalesTableCSV = () => {
  const header = [
    "#","Date","Order Number","Customer","Owner","Qty",
    "Total Price (LKR)","Service Charge (%)","Customer Discounts (LKR)",
    "Owner Discount (LKR)","Profit (LKR)"
  ];
  const escapeCsv = (v) => {
    const s = String(v ?? "");
    return /[",\n]/.test(s) ? `"${s.replace(/"/g, '""')}"` : s;
  };
  const rows = (sales.value || []).map((s, i) => [
    i + 1,
    formatDate(s.sale_date),
    s.order_id ? s.order_id : `Service - ${s.service_name || ""}`,
    s.customer?.name ?? "N/A",
    (s.owner_discount_value && s.owner_discount_value != 0) ? (s.owner?.name ?? "—") : "—",
    saleQty(s),
    (+s.total_amount || 0).toFixed(2),
    (+s.service_charge || 0).toFixed(2),
    customerDiscountAmount(s).toFixed(2),
    (+s.owner_discount_value || 0).toFixed(2),
    saleProfit(s).toFixed(2),
  ]);
  const csv = [header, ...rows].map(r => r.map(escapeCsv).join(",")).join("\n");
  const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `Sales_Report_${safe(dateRangeLabel.value)}.csv`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
};

const downloadSalesTablePDF = () => {
  const doc = new jsPDF("l", "mm", "a4");  // landscape A4
  const pageW = doc.internal.pageSize.getWidth();
  const pageH = doc.internal.pageSize.getHeight();
  const margin = 14;

  // ── Company / header block ──────────────────────────────────────────
  const company = props.companyInfo || {};
  const companyName = company.name || "Hotel Report";
  const companyPhone = company.phone || "";
  const companyAddress = company.address || "";

  // Dark accent bar across top
  doc.setFillColor(15, 23, 42);          // slate-900
  doc.rect(0, 0, pageW, 22, "F");

  // Company name
  doc.setTextColor(255, 255, 255);
  doc.setFontSize(16);
  doc.setFont("helvetica", "bold");
  doc.text(companyName.toUpperCase(), margin, 10);

  // Phone + Address on the same bar (right-aligned)
  doc.setFontSize(8);
  doc.setFont("helvetica", "normal");
  const contactLine = [companyPhone, companyAddress].filter(Boolean).join("   |   ");
  doc.text(contactLine, pageW - margin, 10, { align: "right" });

  // Tag line / sub-info
  doc.setFontSize(8);
  doc.text("Sales Report", margin, 17);
  doc.text(`Generated: ${new Date().toLocaleString()}`, pageW - margin, 17, { align: "right" });

  // ── Report title + date range ────────────────────────────────────────
  doc.setTextColor(15, 23, 42);
  doc.setFontSize(13);
  doc.setFont("helvetica", "bold");
  doc.text("Sales Transactions Report", margin, 32);

  doc.setFont("helvetica", "normal");
  doc.setFontSize(9);
  doc.setTextColor(100, 116, 139);  // slate-500
  const rangeText = dateRangeLabel.value ? `Period: ${dateRangeLabel.value}` : "Period: All time";
  doc.text(rangeText, margin, 38);

  // Thin accent rule below title
  doc.setDrawColor(226, 232, 240);   // slate-200
  doc.setLineWidth(0.4);
  doc.line(margin, 41, pageW - margin, 41);

  // ── Summary pills (key totals) ────────────────────────────────────────
  const pills = [
    { label: "Transactions", value: (sales.value || []).length.toLocaleString() },
    { label: "Total Qty",    value: salesTotalQty.value.toLocaleString() },
    { label: "Gross (LKR)", value: salesGrossTotal.value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) },
    { label: "Discounts (LKR)", value: totalDiscounts.value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) },
    { label: "Net Profit (LKR)", value: salesProfitTotal.value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) },
  ];
  const pillW = (pageW - margin * 2) / pills.length;
  pills.forEach((p, idx) => {
    const px = margin + idx * pillW;
    doc.setFillColor(241, 245, 249);   // slate-100
    doc.roundedRect(px, 44, pillW - 3, 14, 2, 2, "F");
    doc.setFontSize(7);
    doc.setFont("helvetica", "normal");
    doc.setTextColor(100, 116, 139);
    doc.text(p.label, px + (pillW - 3) / 2, 50, { align: "center" });
    doc.setFontSize(8.5);
    doc.setFont("helvetica", "bold");
    doc.setTextColor(15, 23, 42);
    doc.text(p.value, px + (pillW - 3) / 2, 55.5, { align: "center" });
  });

  // ── Table ─────────────────────────────────────────────────────────────
  const head = [[
    "#", "Date", "Order", "Customer",
    "Qty", "Total (LKR)", "Svc %", "Price+Svc",
    "Cust.Disc", "Owner", "Owner Disc", "Profit (LKR)",
  ]];

  const body = (sales.value || []).map((s, i) => [
    i + 1,
    formatDate(s.sale_date),
    s.order_id ? String(s.order_id) : `Svc-${s.service_name || ""}`,
    s.customer?.name ?? "N/A",
    saleQty(s),
    (+s.total_amount || 0).toFixed(2),
    `${(+s.service_charge || 0).toFixed(2)}%`,
    priceWithService(s).toFixed(2),
    customerDiscountAmount(s).toFixed(2),
    (s.owner_discount_value && s.owner_discount_value != 0) ? (s.owner?.name ?? "—") : "—",
    (s.owner_discount_value && s.owner_discount_value != 0) ? (+s.owner_discount_value || 0).toFixed(2) : "—",
    (Number(s.total_amount ?? 0) - Number(s.total_cost ?? 0)).toFixed(2),
  ]);

  // Totals foot row
  const foot = [[
    "", "", "", "TOTALS",
    salesTotalQty.value.toLocaleString(),
    salesGrossTotal.value.toFixed(2),
    "—",
    salesWithServiceTotal.value.toFixed(2),
    salesCustomerDiscountTotal.value.toFixed(2),
    "—",
    salesOwnerDiscountTotal.value.toFixed(2),
    salesProfitTotal.value.toFixed(2),
  ]];

  autoTable(doc, {
    head,
    body,
    foot,
    startY: 62,
    margin: { left: margin, right: margin },
    theme: "grid",
    styles: {
      fontSize: 7.5,
      cellPadding: 2.5,
      valign: "middle",
      lineColor: [226, 232, 240],
      lineWidth: 0.3,
    },
    headStyles: {
      fillColor: [15, 23, 42],
      textColor: 255,
      fontStyle: "bold",
      fontSize: 8,
      halign: "center",
    },
    footStyles: {
      fillColor: [30, 41, 59],   // slate-800
      textColor: 255,
      fontStyle: "bold",
      fontSize: 8,
    },
    alternateRowStyles: { fillColor: [248, 250, 252] },  // slate-50
    columnStyles: {
      0:  { cellWidth: 8,  halign: "center" },
      1:  { cellWidth: 24, halign: "center" },
      2:  { cellWidth: 28 },
      3:  { cellWidth: 36 },
      4:  { cellWidth: 12, halign: "right" },
      5:  { cellWidth: 24, halign: "right" },
      6:  { cellWidth: 16, halign: "right" },
      7:  { cellWidth: 26, halign: "right" },
      8:  { cellWidth: 22, halign: "right" },
      9:  { cellWidth: 26 },
      10: { cellWidth: 22, halign: "right" },
      11: { cellWidth: 26, halign: "right" },
    },
    // Page numbers in footer
    didDrawPage: (data) => {
      const pageCount = doc.internal.getNumberOfPages();
      const current   = doc.internal.getCurrentPageInfo().pageNumber;
      doc.setFontSize(7);
      doc.setTextColor(148, 163, 184);  // slate-400
      doc.setFont("helvetica", "normal");
      doc.text(
        `Page ${current} of ${pageCount}`,
        pageW - margin,
        pageH - 6,
        { align: "right" }
      );
      doc.text(
        companyName,
        margin,
        pageH - 6
      );
    },
  });

  doc.save(`Sales_Report_${safe(dateRangeLabel.value)}.pdf`);
};

const downloadStockTablePDF = () => {
  const doc = new jsPDF("l", "mm", "a4");

  const rows = products.value.map((p, i) => [
    i + 1,
    p.name || "N/A",
    Number(p.sales_qty || 0).toString(),
    (Number(p.sales_qty || 0) * Number(p.selling_price || 0)).toFixed(2),
    Number(p.selling_price || 0).toFixed(2),
    Number(p.discount || 0) <= 100 ? `${Number(p.discount || 0)}%` : `Rs. ${Number(p.discount).toFixed(2)}`,
    priceAfterDiscount(p).toFixed(2),
    totalProfit(p).toFixed(2),
  ]);

  doc.setFontSize(16);
  doc.text("Top Products Stock Report", 14, 12);
  doc.setFontSize(10);
  doc.text(`Date range: ${dateRangeLabel.value} • Generated: ${new Date().toLocaleString()}`, 14, 18);

  const head = [[
    "#", "Product", "Sales Qty", "Total Sales Value (LKR)", 
    "Price (LKR)", "Discount", "Price After Discount", "Profit"
  ]];

  autoTable(doc, {
    head,
    body: rows,
    startY: 24,
    theme: "striped",
    styles: { fontSize: 9 },
    headStyles: { fillColor: [33, 102, 197], textColor: 255 },
    columnStyles: {
      0: { cellWidth: 10 },
      1: { cellWidth: 60 },
      2: { cellWidth: 22, halign: "right" },
      3: { cellWidth: 34, halign: "right" },
      4: { cellWidth: 28, halign: "right" },
      5: { cellWidth: 28, halign: "center" },
      6: { cellWidth: 34, halign: "right" },
      7: { cellWidth: 34, halign: "right" },
    },
    margin: { top: 18, left: 8, right: 8 },
  });

  

  autoTable(doc, {
    body: [totalsRow],
    startY: doc.lastAutoTable ? doc.lastAutoTable.finalY + 2 : 24,
    theme: "plain",
    styles: { fontSize: 10, fontStyle: "bold" },
    columnStyles: {
      0: { cellWidth: 10 },
      1: { cellWidth: 60, halign: "right" },
      2: { cellWidth: 22, halign: "right" },
      3: { cellWidth: 34 },
      4: { cellWidth: 28 },
      5: { cellWidth: 28 },
      6: { cellWidth: 34 },
      7: { cellWidth: 34, halign: "right" },
    },
    margin: { left: 8, right: 8 },
  });

  doc.save(`Top_Products_Stock_${safe(dateRangeLabel.value)}.pdf`);
};

// DataTables init
onMounted(() => {
  const jq = window.$;
  const $stock = jq && jq("#stockQtyTbl");
  if ($stock && jq.fn && jq.fn.dataTable) {
    if (jq.fn.dataTable.isDataTable($stock)) $stock.DataTable().destroy();
    const dt = $stock.DataTable({
      dom: "Bfrtip",
      paging: false,
      buttons: [],
      columnDefs: [{ targets: 0, searchable: false, orderable: false }],
      initComplete: function () {
        const $input = jq("div.dataTables_filter input");
        $input.attr("placeholder", "Search stock...");
        $input.on("keypress", function (e) {
          if (e.which == 13) dt.search(this.value).draw();
        });
      },
      language: { search: "" },
    });
  }
});
</script>

<style>
/* 2x font-size scale for the entire reports page */
.reports-page .text-xs   { font-size: 1.0rem   !important; }
.reports-page .text-sm   { font-size: 1.5rem  !important; }
.reports-page .text-base { font-size: 1.2rem     !important; }
.reports-page .text-lg   { font-size: 1.25rem  !important; }
.reports-page .text-xl   { font-size: 1.5rem   !important; }
.reports-page .text-2xl  { font-size: 2rem     !important; }
.reports-page .text-3xl  { font-size: 1.75rem  !important; }
.reports-page .text-4xl  { font-size: 2.5rem   !important; }
.reports-page .text-5xl  { font-size: 3rem     !important; }
.reports-page .text-6xl  { font-size: 3.5rem   !important; }

/* DataTables overrides for modern look */
.dataTables_wrapper .dataTables_paginate {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 16px;
  gap: 4px;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 500;
  color: #475569;
  cursor: pointer;
  transition: all 0.15s ease;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
  background: #f1f5f9;
  color: #1e293b;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
  background: #1e293b;
  color: #fff;
}

#salesTbl_filter,
#stockQtyTbl_filter,
#expenseTbl_filter {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 12px;
  float: left;
}

#salesTbl_filter label,
#stockQtyTbl_filter label,
#expenseTbl_filter label {
  font-size: 15px;
  color: #475569;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
}

#salesTbl_filter input[type="search"],
#stockQtyTbl_filter input[type="search"],
#expenseTbl_filter input[type="search"] {
  font-weight: 400;
  padding: 8px 14px;
  font-size: 15px;
  color: #334155;
  border: none;
  border-radius: 10px;
  background: #fff;
  outline: none;
  box-shadow: inset 0 0 0 1px #e2e8f0;
  transition: all 0.2s ease;
}

#salesTbl_filter input[type="search"]:focus,
#stockQtyTbl_filter input[type="search"]:focus,
#expenseTbl_filter input[type="search"]:focus {
  box-shadow: inset 0 0 0 2px #60a5fa;
}

.dataTables_wrapper {
  margin-bottom: 8px;
}

.dataTables_wrapper .dataTables_info {
  font-size: 15px;
  color: #64748b;
  padding-top: 12px;
}
</style>

<style scoped>
.chart-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
  position: relative;
}

thead {
  position: sticky;
  top: 0;
  z-index: 10;
}

.num {
  text-align: right;
}

/* Spacing between report sections */
.reports-page .max-w-\[1600px\] > div + div {
  margin-top: 1rem;
}
</style>
