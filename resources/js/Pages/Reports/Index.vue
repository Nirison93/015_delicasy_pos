<template>

    <Head title="Reports" />
    <Banner />
    <div class="reports-page min-h-screen bg-[var(--surface-app)]">
        <Header />

        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 py-8">

            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <Link href="/" class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition mb-4">
                            <i class="ri-arrow-left-line text-lg"></i>
                        </Link>
                        <div class="flex items-center gap-2 text-lg font-semibold text-[var(--accent-600)] uppercase tracking-widest mb-1">
                            <i class="ri-bar-chart-2-line"></i>
                            <span>Analytics</span>
                        </div>
                        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Sales Reports</h1>
                        <p class="text-slate-500 mt-1 text-sm">Comprehensive sales analytics, transaction detail and stock performance</p>
                    </div>

                    <div class="hidden sm:flex items-center gap-2   text-lg text-slate-500 bg-white border border-slate-200 rounded-lg px-3 py-2">
                        <i class="ri-calendar-2-line text-[var(--accent-600)]"></i>
                        <span>{{ dateRangeLabel }}</span>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-200 p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        <!-- Quick Filter -->
                        <div class="relative">
                            <label class="block text-lg font-semibold text-slate-500 uppercase tracking-wide mb-2">Quick Filter</label>
                            <button @click="showQuickFilter = !showQuickFilter"
                                class="w-full h-10 px-4 flex items-center justify-between  text-lg text-slate-700 bg-white border border-slate-300 rounded-lg hover:border-slate-400 focus:border-[var(--accent-600)] focus:ring-2 focus:ring-[var(--accent-100)] transition">
                                <span class="flex items-center gap-2"><i class="ri-flashlight-line text-slate-400"></i>Select period</span>
                                <i :class="showQuickFilter ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line'"></i>
                            </button>
                            <!-- Dropdown Menu -->
                            <div v-if="showQuickFilter" class="fixed inset-0 z-40" @click="showQuickFilter = false"></div>
                            <ul v-if="showQuickFilter"
                                class="absolute left-0 top-full mt-2 z-50 w-full bg-white border border-slate-200 rounded-lg shadow-lg overflow-hidden py-1">
                                <li v-for="qf in quickFilterOptions" :key="qf.key">
                                    <button @click="applyQuickFilter(qf.key)"
                                        class="w-full text-left px-4 py-2 text-lg text-slate-600 hover:bg-[var(--accent-50)] hover:text-[var(--accent-700)] transition">
                                        {{ qf.label }}
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <!-- Start Date -->
                        <div>
                            <label class="block text-lg font-semibold text-slate-500 uppercase tracking-wide mb-2">From</label>
                            <input v-model="startDate" type="date"
                                class="w-full h-10 px-4 t  text-lg text-slate-700 bg-white border border-slate-300 rounded-lg hover:border-slate-400 focus:border-[var(--accent-600)] focus:ring-2 focus:ring-[var(--accent-100)] transition" />
                        </div>

                        <!-- End Date -->
                        <div>
                            <label class="block text-lg font-semibold text-slate-500 uppercase tracking-wide mb-2">To</label>
                            <input v-model="endDate" type="date"
                                class="w-full h-10 px-4  text-lg text-slate-700 bg-white border border-slate-300 rounded-lg hover:border-slate-400 focus:border-[var(--accent-600)] focus:ring-2 focus:ring-[var(--accent-100)] transition" />
                        </div>

                        <!-- Filter Button -->
                        <div class="flex items-end">
                            <button @click="filterData"
                                class="w-full h-10 px-4 text-lg font-semibold text-white bg-[var(--accent-600)] rounded-lg hover:bg-[var(--accent-700)] active:scale-[.98] transition flex items-center justify-center gap-2 shadow-sm shadow-[var(--accent-600)]/20">
                                <i class="ri-filter-3-line"></i> Apply Filter
                            </button>
                        </div>

                        <!-- Reset Button -->
                        <div class="flex items-end">
                            <Link href="/reports"
                                class="w-full h-10 px-4 text-lg font-semibold text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition flex items-center justify-center gap-2">
                                <i class="ri-refresh-line"></i> Reset
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
                <div v-for="kpi in kpiCards" :key="kpi.label"
                    class="group relative bg-white rounded-xl shadow-sm ring-1 ring-slate-200 p-5 overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                    <div class="absolute inset-x-0 top-0 h-0.5" :style="{ background: kpi.color }"></div>
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="text-lg font-semibold text-slate-500 uppercase tracking-wide mb-1.5">{{ kpi.label }}</p>
                            <p class="text-xl font-bold text-slate-900 truncate" :style="{ color: kpi.textColor }">{{ kpi.value }}</p>
                            <p class="text-[11px] text-slate-900 mt-1">{{ kpi.suffix }}</p>
                        </div>
                        <div class="w-10 h-10 shrink-0 rounded-lg flex items-center justify-center" :style="{ background: kpi.bg }">
                            <i :class="kpi.icon" class="text-lg" :style="{ color: kpi.textColor }"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cash Drawer Summary -->
            <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-200 px-6 py-4 mb-8 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <i class="ri-safe-2-line text-emerald-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-500">Cash Drawer (this period)</p>
                        <p class="text-lg font-bold text-slate-900">
                            {{ cashDrawerSummary.total_drawers ?? 0 }} drawers
                            <span class="mx-1 text-slate-300">|</span>
                            Variance {{ Number(cashDrawerSummary.total_variance || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} LKR
                            <span v-if="cashDrawerSummary.pending_approval_count" class="ml-1 text-amber-600">
                                ({{ cashDrawerSummary.pending_approval_count }} pending approval)
                            </span>
                        </p>
                    </div>
                </div>
                <Link href="/reports/cash-drawer" class="text-lg font-semibold text-indigo-600 hover:text-indigo-700">View Full Cash Drawer Report →</Link>
            </div>

            <!-- Sales Table -->
            <div class="bg-white rounded-xl shadow-md ring-1 ring-slate-200 overflow-hidden mb-8">
                <!-- Header -->
                <div class="border-b border-slate-200 px-6 py-5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                                    <i class="ri-file-list-3-line text-slate-600"></i>
                                </div>
                                Sales Transactions
                            </h2>
                            <p class="text-lg text-slate-900 mt-1 ml-10">Showing {{ salesPaginationInfo }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="downloadSalesTablePDF"
                                class="h-9 px-4 inline-flex items-center gap-2 text-lg font-semibold text-white bg-rose-600 rounded-lg hover:bg-rose-700 active:scale-[.98] transition shadow-sm">
                                <i class="ri-file-pdf-line"></i> PDF
                            </button>
                            <button @click="downloadSalesTableExcel"
                                class="h-9 px-4 inline-flex items-center gap-2 text-lg font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 active:scale-[.98] transition shadow-sm">
                                <i class="ri-file-excel-2-line"></i> Excel (Page)
                            </button>
                            <button @click="downloadAllSalesTableExcel"
                                class="h-9 px-4 inline-flex items-center gap-2 text-lg font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 active:scale-[.98] transition shadow-sm">
                                <i class="ri-file-excel-2-line"></i> Excel (All)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Summary stats -->
                <div class="bg-slate-50/70 border-b border-slate-200 px-6 py-4">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center">
                                <i class="ri-shopping-bag-line text-emerald-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-[11px] text-slate-500 text-lg ">Total Qty</p>
                                <p class="text-lg font-bold text-slate-900">{{ salesTotalQty.toLocaleString() }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center">
                                <i class="ri-money-dollar-circle-line text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-[11px] text-slate-500 text-lg ">Gross Sales</p>
                                <p class="text-lg font-bold text-slate-900">{{ finalSalesAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center">
                                <i class="ri-percent-line text-amber-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-[11px] text-slate-500 text-lg ">Discounts</p>
                                <p class="text-lg font-bold text-slate-900">{{ totalDiscounts.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-rose-100 flex items-center justify-center">
                                <i class="ri-line-chart-line text-rose-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-[11px] text-slate-500 text-lg ">Profit</p>
                                <p class="text-lg font-bold text-slate-900">{{ salesProfitTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table id="salesTbl" class="sales-table w-full text-lg text-slate-700 min-w-[700px]">
                        <colgroup>
                            <col style="width:50px" />
                            <col style="width:90px" />
                            <col style="width:114px " />
                            <col style="width:60px" />
                            <col style="width:90px" />
                            <col style="width:70px" />
                            <col style="width:100px" />
                            <col style="width:60px" />
                        </colgroup>

                        <thead>
                            <tr class="bg-slate-900 text-white">
                                <th class="px-3 py-2 text-center text-[14px] font-semibold uppercase tracking-wider"># / Date</th>
                                <th class="px-3 py-2 text-left text-[14px] font-semibold uppercase tracking-wider">Order</th>
                                <th class="px-3 py-2 text-left text-[14px] font-semibold uppercase tracking-wider">Customer</th>
                                <th class="px-3 py-2 text-center text-[14px] font-semibold uppercase tracking-wider">Qty</th>
                                <th class="px-3 py-2 text-right text-[14px] font-semibold uppercase tracking-wider">Total</th>
                                <th class="px-3 py-2 text-right text-[14px] font-semibold uppercase tracking-wider">Svc %</th>
                                <th class="px-3 py-2 text-right text-[14px] font-semibold uppercase tracking-wider">Price+Svc</th>
                                <th class="px-3 py-2 text-center text-[14px] font-semibold uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(s, i) in salesData" :key="s.id ?? i" class="hover:bg-[var(--accent-50)]/60 transition-colors">
                                <td class="px-3 py-2 text-center text-slate-500 text-xs">
                                    <div class="font-semibold text-slate-900">{{ (sales.current_page - 1) * 25 + i + 1 }}</div>
                                    <div class="text-[9px] text-slate-900">{{ formatDate(s.sale_date) }}</div>
                                </td>
                                <td class="px-3 py-2 font-semibold text-slate-900">{{ s.order_id || 'Service' }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ s.customer?.name ?? 'N/A' }}</td>
                                <td class="px-3 py-2 text-center text-slate-900">{{ saleQty(s) }}</td>
                                <td class="px-3 py-2 text-right text-slate-900">{{ toMoney(Number(s.total_amount || 0)) }}</td>
                                <td class="px-3 py-2 text-right text-slate-500">{{ Number(s.service_charge || 0).toFixed(2) }}%</td>
                                <td class="px-3 py-2 text-right text-slate-900">{{ toMoney(priceWithService(s)) }}</td>
                                <td class="px-3 py-2 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button @click="printBill(s)"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 hover:bg-blue-50 transition">
                                            <i class="ri-printer-line text-lg"></i>
                                        </button>
                                        <button @click="openRefundModal(s)" title="Issue Refund"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-rose-600 hover:bg-rose-50 transition">
                                            <i class="ri-refund-2-line text-lg"></i>
                                        </button>
                                    </div>
                                    <div v-if="refundedTotal(s) > 0" class="mt-1 text-[10px] font-bold text-rose-600">
                                        Refunded: {{ toMoney(refundedTotal(s)) }}
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!salesData.length">
                                <td colspan="9" class="px-3 py-12 text-center text-slate-400">
                                    <i class="ri-inbox-line text-3xl mb-2 block"></i>
                                    No transactions found for the selected period.
                                </td>
                            </tr>
                        </tbody>

                        <tfoot v-if="salesData.length">
                            <tr class="bg-slate-100 border-t-2 border-slate-300 font-semibold text-slate-900">
                                <td class="px-3 py-2 text-right" colspan="3">Totals:</td>
                                <td class="px-3 py-2 text-center">{{ salesTotalQty.toLocaleString() }}</td>
                                <td class="px-3 py-2 text-right">{{ salesGrossTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
                                <td class="px-3 py-2 text-center">—</td>
                                <td class="px-3 py-2 text-right">{{ salesWithServiceTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
                                <td class="px-3 py-2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div class="border-t border-slate-200 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-slate-50/70">
                    <div class="flex items-center gap-3">
                        <label class="  text-lg text-slate-500">Show entries:</label>
                        <select v-model.number="salesPerPage" @change="changeSalesPerPage"
                            class="h-9 px-3   text-lg text-slate-700 bg-white border border-slate-300 rounded-lg hover:border-slate-400 focus:border-[var(--accent-600)] focus:ring-2 focus:ring-[var(--accent-100)] transition">
                            <option :value="10">10</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                            <option :value="100">100</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-center gap-1">
                        <button @click="() => goToSalesPage(1)" :disabled="sales.current_page <= 1"
                            class="h-9 w-9 inline-flex items-center justify-center text-slate-500 bg-white border border-slate-300 rounded-lg hover:bg-slate-100 hover:border-slate-400 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-white transition">
                            <i class="ri-skip-back-line text-sm"></i>
                        </button>
                        <button @click="prevSalesPage" :disabled="!sales.prev_page_url"
                            class="h-9 w-9 inline-flex items-center justify-center text-slate-500 bg-white border border-slate-300 rounded-lg hover:bg-slate-100 hover:border-slate-400 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-white transition">
                            <i class="ri-arrow-left-s-line text-lg"></i>
                        </button>

                        <button v-for="p in salesPageNumbers" :key="'sp-' + p" @click="() => goToSalesPage(p)"
                            class="h-9 min-w-9 px-2 inline-flex items-center justify-center text-lg font-semibold rounded-lg border transition"
                            :class="p === sales.current_page
                                ? 'bg-slate-900 border-slate-900 text-white'
                                : 'bg-white border-slate-300 text-slate-600 hover:bg-slate-100 hover:border-slate-400'">
                            {{ p }}
                        </button>

                        <button @click="nextSalesPage" :disabled="!sales.next_page_url"
                            class="h-9 w-9 inline-flex items-center justify-center text-slate-500 bg-white border border-slate-300 rounded-lg hover:bg-slate-100 hover:border-slate-400 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-white transition">
                            <i class="ri-arrow-right-s-line text-lg"></i>
                        </button>
                        <button @click="() => goToSalesPage(sales.last_page)" :disabled="sales.current_page >= sales.last_page"
                            class="h-9 w-9 inline-flex items-center justify-center text-slate-500 bg-white border border-slate-300 rounded-lg hover:bg-slate-100 hover:border-slate-400 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-white transition">
                            <i class="ri-skip-forward-line text-sm"></i>
                        </button>
                    </div>

                    <div class="text-right">
                        <p class="text-lg text-slate-500">
                            <span class="font-semibold text-slate-800">{{ sales.total ? (sales.current_page - 1) * 25 + 1 : 0 }}</span>
                            to
                            <span class="font-semibold text-slate-800">{{ Math.min(sales.current_page * 25, sales.total) }}</span>
                            of
                            <span class="font-semibold text-slate-800">{{ sales.total }}</span>
                            entries
                        </p>
                    </div>
                </div>
            </div>



        </div>
    </div>

    <!-- Refund Modal -->
    <div v-if="isRefundModalOpen" class="fixed inset-0 z-[1200] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="isRefundModalOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-xl font-bold text-slate-800">Issue Refund</h3>
                <button @click="isRefundModalOpen = false" class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <p class="text-md text-slate-500">
                    Order <span class="font-semibold text-slate-800">{{ refundTarget?.order_id || refundTarget?.id }}</span>
                    — Total {{ toMoney(Number(refundTarget?.total_amount || 0)) }} LKR
                    (already refunded {{ toMoney(refundedTotal(refundTarget || {})) }})
                </p>
                <div>
                    <label class="block text-md font-semibold text-slate-600 mb-1">Amount (LKR)</label>
                    <input v-model="refundAmount" type="number" min="0.01" step="0.01" placeholder="0.00"
                        class="w-full h-12 px-4 text-lg bg-slate-50 ring-1 ring-slate-200 rounded-xl text-slate-800 focus:ring-2 focus:ring-rose-400 focus:outline-none transition" />
                </div>
                <div>
                    <label class="block text-md font-semibold text-slate-600 mb-1">Reason</label>
                    <textarea v-model="refundReason" rows="3" placeholder="Reason for the refund"
                        class="w-full px-4 py-3 text-lg bg-slate-50 ring-1 ring-slate-200 rounded-xl text-slate-800 focus:ring-2 focus:ring-rose-400 focus:outline-none transition"></textarea>
                </div>
                <p v-if="refundError" class="text-rose-600 text-md">{{ refundError }}</p>
            </div>
            <div class="px-6 py-5 bg-slate-50 border-t border-slate-200 flex gap-3">
                <button @click="isRefundModalOpen = false" class="flex-1 h-12 rounded-xl bg-white ring-1 ring-slate-200 text-slate-600 font-semibold hover:bg-slate-100 transition">Cancel</button>
                <button @click="submitRefund" :disabled="refundSubmitting" class="flex-1 h-12 rounded-xl bg-rose-600 text-white font-semibold hover:bg-rose-700 transition disabled:opacity-60">
                    {{ refundSubmitting ? 'Processing...' : 'Issue Refund' }}
                </button>
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
import axios from "axios";
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
    totalRefunds: { type: Number, default: 0 },
    cashDrawerSummary: { type: Object, default: () => ({}) },
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
const salesPerPage = ref(10);
const productsPerPage = ref(10);
const productSearch = ref("");

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

// Get the actual sales data (handle both array and paginated object)
const salesData = computed(() => (sales.value && sales.value.data) ? sales.value.data : (Array.isArray(sales.value) ? sales.value : []));

// Sales totals (calculate from current page only)
const salesTotalQty = computed(() => salesData.value.reduce((a, s) => a + saleQty(s), 0));

const salesGrossTotal = computed(() =>
    salesData.value.reduce((a, s) => a + Number(s.total_amount || 0), 0)
);

const salesWithServiceTotal = computed(() =>
    salesData.value.reduce((a, s) => a + priceWithService(s), 0)
);

const salesCustomerDiscountTotal = computed(() =>
    salesData.value.reduce((a, s) => a + customerDiscountAmount(s), 0)
);

const salesOwnerDiscountTotal = computed(() =>
    salesData.value.reduce((sum, s) => sum + Number(s.owner_discount_value || 0), 0)
);

const totalDiscounts = computed(() =>
    salesCustomerDiscountTotal.value + salesOwnerDiscountTotal.value
);

const finalSalesAmount = computed(() =>
    Math.max(0, salesWithServiceTotal.value - salesCustomerDiscountTotal.value - salesOwnerDiscountTotal.value)
);

// Note: This mirrors the table's Profit column: (total_amount - total_cost)
const salesProfitTotal = computed(() =>
    salesData.value.reduce(
        (sum, s) => sum + (Number(s.total_amount ?? 0) - Number(s.total_cost ?? 0)),
        0
    )
);

// Get the actual products data (handle both array and paginated object)
const productsData = computed(() => (products.value && products.value.data) ? products.value.data : (Array.isArray(products.value) ? products.value : []));

const filteredProductsData = computed(() => {
    const q = productSearch.value.trim().toLowerCase();
    if (!q) return productsData.value;
    return productsData.value.filter((p) => String(p.name || "").toLowerCase().includes(q));
});

// Product table calculations
const priceAfterDiscount = (product) => {
    const price = Number(product.selling_price || 0);
    const discount = Number(product.discount || 0);
    return discount <= 100 ? price * (1 - discount / 100) : price - discount;
};

const profitPerUnit = (product) => priceAfterDiscount(product) - Number(product.cost_price || 0);
const totalProfit = (product) => profitPerUnit(product) * Number(product.sales_qty || 0);

const totalSalesQty = computed(() => productsData.value.reduce((s, p) => s + Number(p.sales_qty || 0), 0));
const grandTotalProfit = computed(() => productsData.value.reduce((s, p) => s + totalProfit(p), 0));

 // KPI cards (driven from a config array for consistent styling)
// NOTE: Uses full-dataset props (totalSaleAmount, netProfit, totalDiscountLkr, totalCustomDiscountLkr,
// totalTransactions, totalCustomer) instead of page-local computed values, so KPIs show ALL data totals,
// not just the current page's 25 rows.
const kpiCards = computed(() => {
    const fullDiscountTotal = Number(props.totalDiscountLkr || 0) + Number(props.totalCustomDiscountLkr || 0);

    return [
        {
            label: "Total Sales Amount",
            value: Number(props.totalSaleAmount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
            suffix: "LKR",
            icon: "ri-money-dollar-circle-line",
            color: "linear-gradient(90deg,#f97316,#fb923c)",
            bg: "#FFF1E6",
            textColor: "#ea580c",
        },
        {
            label: "Total Discount",
            value: fullDiscountTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
            suffix: "LKR",
            icon: "ri-percent-line",
            color: "linear-gradient(90deg,#2563eb,#60a5fa)",
            bg: "#EAF2FE",
            textColor: "#2563eb",
        },
        {
            label: "Net Profit",
            value: Number(props.netProfit || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
            suffix: "LKR",
            icon: "ri-line-chart-line",
            color: "linear-gradient(90deg,#059669,#34d399)",
            bg: "#E9F9F2",
            textColor: "#059669",
        },
        {
            label: "Transactions",
            value: Number(props.totalTransactions || 0).toLocaleString(),
            suffix: "Total",
            icon: "ri-exchange-line",
            color: "linear-gradient(90deg,#7c3aed,#a78bfa)",
            bg: "#F2EDFE",
            textColor: "#7c3aed",
        },
        {
            label: "Total Customers",
            value: (props.totalCustomer || 0).toLocaleString(),
            suffix: "Unique",
            icon: "ri-team-line",
            color: "linear-gradient(90deg,#db2777,#f472b6)",
            bg: "#FDECF4",
            textColor: "#db2777",
        },
        {
            label: "Refunds",
            value: Number(props.totalRefunds || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
            suffix: "LKR",
            icon: "ri-refund-2-line",
            color: "linear-gradient(90deg,#dc2626,#f87171)",
            bg: "#FDEDEC",
            textColor: "#dc2626",
        },
    ];
});

const totalTransactionsDisplay = computed(() => (sales.value && sales.value.total) ? sales.value.total : (props.totalTransactions ?? salesData.value.length));

// Date filter
const filterData = () => {
    if (startDate.value && endDate.value && new Date(startDate.value) > new Date(endDate.value)) {
        alert("Start date cannot be greater than end date.");
        return;
    }
    router.get(
        route("reports.index"),
        { start_date: startDate.value, end_date: endDate.value, sales_page: 1, products_page: 1 },
        { preserveScroll: true, preserveState: false }
    );
};

// Pagination info
const salesPaginationInfo = computed(() => {
    if (!sales.value || !sales.value.current_page) return "";
    const from = (sales.value.current_page - 1) * 25 + 1;
    const to = Math.min(sales.value.current_page * 25, sales.value.total);
    return `${from}-${to} of ${sales.value.total}`;
});

const productsPaginationInfo = computed(() => {
    if (!products.value || !products.value.current_page) return `${filteredProductsData.value.length} products`;
    const from = (products.value.current_page - 1) * 25 + 1;
    const to = Math.min(products.value.current_page * 25, products.value.total);
    return `${from}-${to} of ${products.value.total}`;
});

// Page number window helper (for numbered pagination buttons)
const pageWindow = (current, last, span = 5) => {
    if (!current || !last) return [];
    const half = Math.floor(span / 2);
    let start = Math.max(1, current - half);
    let end = Math.min(last, start + span - 1);
    start = Math.max(1, end - span + 1);
    const pages = [];
    for (let i = start; i <= end; i++) pages.push(i);
    return pages;
};

const salesPageNumbers = computed(() => pageWindow(sales.value?.current_page, sales.value?.last_page));
const productsPageNumbers = computed(() => pageWindow(products.value?.current_page, products.value?.last_page));

// Pagination methods
const nextSalesPage = () => {
    if (sales.value && sales.value.next_page_url) {
        goToSalesPage((sales.value.current_page || 1) + 1);
    }
};

const prevSalesPage = () => {
    if (sales.value && sales.value.prev_page_url) {
        goToSalesPage((sales.value.current_page || 1) - 1);
    }
};

const goToSalesPage = (page) => {
    if (!page || page < 1 || (sales.value.last_page && page > sales.value.last_page)) return;
    router.get(
        route("reports.index"),
        {
            start_date: startDate.value,
            end_date: endDate.value,
            sales_page: page,
            per_page: salesPerPage.value,
        },
        { preserveScroll: true }
    );
};

const changeSalesPerPage = () => {
    router.get(
        route("reports.index"),
        {
            start_date: startDate.value,
            end_date: endDate.value,
            sales_page: 1,
            per_page: salesPerPage.value,
        },
        { preserveScroll: true }
    );
};

const nextProductsPage = () => {
    if (products.value && products.value.next_page_url) {
        goToProductsPage((products.value.current_page || 1) + 1);
    }
};

const prevProductsPage = () => {
    if (products.value && products.value.prev_page_url) {
        goToProductsPage((products.value.current_page || 1) - 1);
    }
};

const goToProductsPage = (page) => {
    if (!page || page < 1 || (products.value.last_page && page > products.value.last_page)) return;
    router.get(
        route("reports.index"),
        {
            start_date: startDate.value,
            end_date: endDate.value,
            products_page: page,
            per_page: productsPerPage.value,
        },
        { preserveScroll: true }
    );
};

const changeProductsPerPage = () => {
    router.get(
        route("reports.index"),
        {
            start_date: startDate.value,
            end_date: endDate.value,
            products_page: 1,
            per_page: productsPerPage.value,
        },
        { preserveScroll: true }
    );
};

// Quick Filters
const showQuickFilter = ref(false);

const quickFilterOptions = [
    { key: 'today', label: 'Today' },
    { key: 'yesterday', label: 'Yesterday' },
    { key: 'this_week', label: 'This Week' },
    { key: 'last_week', label: 'Last Week' },
    { key: 'this_month', label: 'This Month' },
    { key: 'last_month', label: 'Last Month' },
    { key: 'this_year', label: 'This Year' },
    { key: 'last_year', label: 'Last Year' },
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
            end = fmt(new Date(t.getFullYear(), t.getMonth() + 1, 0));
            break;
        case 'last_month':
            start = fmt(new Date(t.getFullYear(), t.getMonth() - 1, 1));
            end = fmt(new Date(t.getFullYear(), t.getMonth(), 0));
            break;
        case 'this_year':
            start = fmt(new Date(t.getFullYear(), 0, 1));
            end = fmt(new Date(t.getFullYear(), 11, 31));
            break;
        case 'last_year':
            start = fmt(new Date(t.getFullYear() - 1, 0, 1));
            end = fmt(new Date(t.getFullYear() - 1, 11, 31));
            break;
    }

    startDate.value = start;
    endDate.value = end;
    showQuickFilter.value = false;
    filterData();
};

// Charts
const sortDescending = (data) =>
    Object.entries(data).sort((a, b) => b[1] - a[1]).reduce((acc, [k, v]) => ((acc[k] = v), acc), {});

const productQuantities = computed(() => {
    const quantities = {};
    const raw = (sales.value && sales.value.data) || props.sales || [];
    const salesToUse = Array.isArray(raw) ? raw : (props.sales || []);
    salesToUse.forEach((sale) => {
        (sale.sale_items || []).forEach((item) => {
            const name = item.product && item.product.name ? item.product.name : "N/A";
            quantities[name] = (quantities[name] || 0) + Number(item.quantity || 0);
        });
    });
    return sortDescending(quantities);
});

const CHART_PALETTE = [
    "#6366F1", "#F59E0B", "#10B981", "#EC4899", "#3B82F6", "#22C55E",
    "#F97316", "#14B8A6", "#EF4444", "#8B5CF6", "#0EA5E9", "#84CC16",
    "#D946EF", "#94A3B8", "#06B6D4", "#EAB308", "#A855F7", "#F43F5E",
    "#65A30D", "#0D9488",
];

const chartData = computed(() => ({
    labels: Object.keys(productQuantities.value),
    datasets: [{
        data: Object.values(productQuantities.value),
        backgroundColor: CHART_PALETTE,
        borderWidth: 2,
        borderColor: "#ffffff",
    }],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: true, position: "bottom", labels: { boxWidth: 10, font: { size: 10 } } }
    }
};

const paymentMethodTotals = computed(() => {
    const totals = {};
    const raw = (sales.value && sales.value.data) || props.sales || [];
    const salesToUse = Array.isArray(raw) ? raw : (props.sales || []);
    salesToUse.forEach((s) => {
        const m = s.payment_method || "N/A";
        totals[m] = (totals[m] || 0) + (parseFloat(s.total_amount) || 0);
    });
    return sortDescending(totals);
});

const chartData1 = computed(() => ({
    labels: Object.keys(paymentMethodTotals.value),
    datasets: [{
        data: Object.values(paymentMethodTotals.value),
        backgroundColor: CHART_PALETTE,
        borderWidth: 2,
        borderColor: "#ffffff",
    }],
}));

const chartOptions1 = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: true, position: "bottom", labels: { boxWidth: 10, font: { size: 10 } } },
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
        backgroundColor: CHART_PALETTE,
        borderWidth: 2,
        borderColor: "#ffffff",
    }],
}));

const chartOptions4 = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: true, position: "bottom", labels: { boxWidth: 10, font: { size: 10 } } },
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
        "#", "Date", "Order Number", "Customer", "Dish Qty",
        "Total Price", "Service Charge (%)", "Price with Service"
    ];

    const salesRows = (sales.value && sales.value.data) || sales.value || [];
    const pageStart = (sales.value && sales.value.current_page) ? (sales.value.current_page - 1) * 25 : 0;
    const rows = salesRows.map((s, i) => {
        const qty = saleQty(s);
        const total = Number(s.total_amount || 0);
        const svcPct = Number(s.service_charge || 0);
        const priceWSvc = priceWithService(s);

        return [
            pageStart + i + 1,
            formatDate(s.sale_date),
            s.order_id ? s.order_id : `Service - ${s.service_name || ""}`,
            s.customer?.name ?? "N/A",
            qty,
            total,
            svcPct,            // numeric; header clarifies it's %
            priceWSvc,
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
        money: [5, 7],
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

// Download all sales data to Excel (regardless of pagination)
const downloadAllSalesTableExcel = async () => {
    try {
        const allSales = [];
        let currentPage = 1;
        const lastPage = sales.value?.last_page || 1;

        // Collect data from all pages
        while (currentPage <= lastPage) {
            await new Promise((resolve) => {
                router.get(
                    route("reports.index"),
                    {
                        start_date: startDate.value,
                        end_date: endDate.value,
                        sales_page: currentPage,
                        per_page: 100,
                    },
                    {
                        preserveScroll: true,
                        onSuccess: (page) => {
                            const pageData = page.props.sales?.data || [];
                            allSales.push(...pageData);
                            resolve();
                        },
                        onError: () => {
                            throw new Error("Failed to fetch page " + currentPage);
                        },
                    }
                );
            });

            currentPage++;
        }

        if (allSales.length === 0) {
            alert("No data available to download.");
            return;
        }

        const header = [
            "#", "Date", "Order Number", "Customer", "Dish Qty",
            "Total Price", "Service Charge (%)", "Price with Service"
        ];

        const rows = allSales.map((s, i) => {
            const qty = saleQty(s);
            const total = Number(s.total_amount || 0);
            const svcPct = Number(s.service_charge || 0);
            const priceWSvc = priceWithService(s);

            return [
                i + 1,
                formatDate(s.sale_date),
                s.order_id ? s.order_id : `Service - ${s.service_name || ""}`,
                s.customer?.name ?? "N/A",
                qty,
                total,
                svcPct,
                priceWSvc,
            ];
        });

        const aoa = [header, ...rows];
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(aoa);

        const widths = header.map((h, c) => {
            const contentLens = aoa.map(r => (r[c] == null ? 0 : String(r[c]).length));
            const maxLen = Math.max(h.length, ...contentLens);
            return { wch: Math.min(Math.max(maxLen + 2, 12), 40) };
        });
        ws['!cols'] = widths;

        const formatCols = { qty: 4, money: [5, 7], percent: 6 };
        const range = XLSX.utils.decode_range(ws['!ref']);

        for (let R = 1; R <= range.e.r; R++) {
            const qCell = ws[XLSX.utils.encode_cell({ r: R, c: formatCols.qty })];
            if (qCell && typeof qCell.v === "number") qCell.z = "0";

            for (const C of formatCols.money) {
                const cell = ws[XLSX.utils.encode_cell({ r: R, c: C })];
                if (cell && typeof cell.v === "number") cell.z = "0.00";
            }

            const pCell = ws[XLSX.utils.encode_cell({ r: R, c: formatCols.percent })];
            if (pCell && typeof pCell.v === "number") pCell.z = "0.00";
        }

        XLSX.utils.book_append_sheet(wb, ws, "Sales");
        XLSX.writeFile(wb, `Sales_Report_All_${safe(dateRangeLabel.value)}.xlsx`);
    } catch (error) {
        console.error("Error downloading all sales data:", error);
        alert(`Failed to download all data: ${error.message}`);
    }
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
        "#", "Date", "Order Number", "Customer", "Qty",
        "Total Price (LKR)", "Service Charge (%)", "Price with Service (LKR)"
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
        saleQty(s),
        (+s.total_amount || 0).toFixed(2),
        (+s.service_charge || 0).toFixed(2),
        priceWithService(s).toFixed(2),
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
        { label: "Total Qty", value: salesTotalQty.value.toLocaleString() },
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
    ]];

    const salesRows = (sales.value && sales.value.data) || sales.value || [];
    const pageStart = (sales.value && sales.value.current_page) ? (sales.value.current_page - 1) * 25 : 0;
    const body = salesRows.map((s, i) => [
        pageStart + i + 1,
        formatDate(s.sale_date),
        s.order_id ? String(s.order_id) : `Svc-${s.service_name || ""}`,
        s.customer?.name ?? "N/A",
        saleQty(s),
        (+s.total_amount || 0).toFixed(2),
        `${(+s.service_charge || 0).toFixed(2)}%`,
        priceWithService(s).toFixed(2),
    ]);

    // Totals foot row
    const foot = [[
        "", "", "", "TOTALS",
        salesTotalQty.value.toLocaleString(),
        salesGrossTotal.value.toFixed(2),
        "—",
        salesWithServiceTotal.value.toFixed(2),
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
            0: { cellWidth: 8, halign: "center" },
            1: { cellWidth: 24, halign: "center" },
            2: { cellWidth: 28 },
            3: { cellWidth: 36 },
            4: { cellWidth: 12, halign: "right" },
            5: { cellWidth: 24, halign: "right" },
            6: { cellWidth: 16, halign: "right" },
            7: { cellWidth: 26, halign: "right" },
        },
        // Page numbers in footer
        didDrawPage: (data) => {
            const pageCount = doc.internal.getNumberOfPages();
            const current = doc.internal.getCurrentPageInfo().pageNumber;
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

    const productsRows = (products.value && products.value.data) || products.value || [];
    const pageStart = (products.value && products.value.current_page) ? (products.value.current_page - 1) * 25 : 0;
    const rows = productsRows.map((p, i) => [
        pageStart + i + 1,
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

    const totalsRow = [
        "", "Totals:",
        productsRows.reduce((s, p) => s + Number(p.sales_qty || 0), 0),
        "",
        "",
        "",
        "",
        productsRows.reduce((s, p) => s + totalProfit(p), 0).toFixed(2),
    ];

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

 // ---------- Refunds ----------
 const isRefundModalOpen = ref(false);
 const refundTarget = ref(null);
 const refundAmount = ref("");
 const refundReason = ref("");
 const refundSubmitting = ref(false);
 const refundError = ref("");

 const refundedTotal = (sale) => (sale.refunds || []).reduce((sum, r) => sum + Number(r.amount || 0), 0);

 const openRefundModal = (sale) => {
    refundTarget.value = sale;
    refundAmount.value = "";
    refundReason.value = "";
    refundError.value = "";
    isRefundModalOpen.value = true;
 };

 const submitRefund = async () => {
    refundError.value = "";
    const amount = parseFloat(refundAmount.value || "");
    if (!amount || amount <= 0) {
        refundError.value = "Enter a valid refund amount.";
        return;
    }
    if (!refundReason.value.trim()) {
        refundError.value = "Reason is required.";
        return;
    }
    refundSubmitting.value = true;
    try {
        const { data } = await axios.post("/refunds", {
            sale_id: refundTarget.value.id,
            amount,
            reason: refundReason.value,
        });
        if (!refundTarget.value.refunds) refundTarget.value.refunds = [];
        refundTarget.value.refunds.push(data.refund);
        isRefundModalOpen.value = false;
    } catch (err) {
        refundError.value = err.response?.data?.message || "Failed to record refund.";
    } finally {
        refundSubmitting.value = false;
    }
 };

 const printBill = (sale) => {
    if (!sale) return;

    const items = sale.sale_items || [];
    const company = props.companyInfo || {};

    // ---- Totals (same calc as before) ----
    const subtotal = Number(sale.total_amount || 0);
    const serviceChargePct = Number(sale.service_charge || 0);
    const serviceChargeAmt = (subtotal * serviceChargePct) / 100;
    const customerDiscount = customerDiscountAmount(sale);
    const ownerDiscount = Number(sale.owner_discount_value || 0);
    const finalTotal = Math.max(0, subtotal + serviceChargeAmt - customerDiscount - ownerDiscount);
    const cashPaid = Number(sale.cash_paid || finalTotal);
    const balance = Math.max(0, cashPaid - finalTotal);

    // ---- Item rows ----
    const productRows = items.length
        ? items.map((item) => {
            const name = item.product?.name || "N/A";
            const qty = Number(item.quantity || 0);
            const unitPrice = Number(item.unit_price || 0);
            const total = qty * unitPrice;
            return `
                <tr class="item-sub">
                    <td class="name" colspan="3">${name}</td>
                </tr>
                <tr>
                    <td></td>
                    <td class="pqty">${unitPrice.toFixed(2)} × ${qty}</td>
                    <td class="ptotal">${total.toFixed(2)}</td>
                </tr>
            `;
        }).join("")
        : `<tr><td colspan="3" style="text-align:center;padding:8px 0;">No items found</td></tr>`;

    const saleDate = sale.sale_date ? new Date(sale.sale_date) : new Date();
    const saleType = (sale.sale_type || "dine_in").toLowerCase();
    const orderTypeLabel = saleType === "takeaway" ? "Takeaway" : saleType === "pickup" ? "Delivery" : "Dine In";

    // ---- Receipt HTML (thermal 80mm layout) ----
    const receiptHTML = `
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Receipt</title>
        <style>
            @page { size: 80mm auto; margin: 0; }
            * { box-sizing: border-box; }
            html, body { width: 80mm; height: auto; min-height: 0; margin: 0; padding: 0; background: #fff; }
            body { font-family: 'Arial', sans-serif; font-size: 13px; color: #000; padding: 8px 10px; overflow: visible; }
            .receipt { width: 100%; overflow: visible; page-break-inside: avoid; break-inside: avoid; }
            .header { text-align: center; padding-bottom: 8px; margin-bottom: 8px; border-bottom: 2px solid #000; }
            .header h1 { font-size: 18px; font-weight: 900; margin: 0 0 3px; letter-spacing: 0.5px; }
            .header p { font-size: 12px; margin: 2px 0; }
            .order-type { font-size: 13px; font-weight: 800; text-align: center; border: 2px solid #000; border-radius: 4px; padding: 4px 0; margin: 8px 0; letter-spacing: 0.5px; text-transform: uppercase; }
            .meta { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 8px; }
            .meta td { padding: 2px 0; vertical-align: top; }
            .meta td:first-child { font-weight: 700; width: 50%; }
            .meta td:last-child { text-align: right; font-weight: 400; }
            .divider-solid { border: none; border-top: 2px solid #000; margin: 6px 0; }
            .items { width: 100%; border-collapse: collapse; font-size: 13px; }
            .items thead tr { border-bottom: 1px solid #000; }
            .items th { font-size: 12px; font-weight: 800; padding: 4px 2px; text-transform: uppercase; }
            .items th:first-child { text-align: left; }
            .items th:nth-child(2) { text-align: center; }
            .items th:last-child { text-align: right; }
            .items td { padding: 3px 2px; }
            .items td.name { font-weight: 700; font-size: 13px; padding-top: 4px; }
            .items .item-sub { border-bottom: 1px dashed #aaa; }
            .items td.pqty { text-align: center; font-size: 12px; }
            .items td.ptotal { text-align: right; font-weight: 700; font-size: 13px; }
            .items tr, .totals tr { page-break-inside: avoid; break-inside: avoid; }
            .totals { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 4px; }
            .totals td { padding: 3px 0; }
            .totals td:last-child { text-align: right; }
            .totals .grand td { font-size: 15px; font-weight: 900; border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 5px 0; }
            .totals .bold td { font-weight: 700; }
            .footer { text-align: center; margin-top: 10px; padding-top: 0; }
            .footer .no-refund { font-size: 13px; font-weight: 800; letter-spacing: 0.3px; margin: 6px 0; }
            .footer .thank-you { font-size: 14px; font-weight: 900; letter-spacing: 0.5px; margin: 4px 0; text-transform: uppercase; }
            .footer .powered { font-size: 11px; margin-top: 6px; margin-bottom: 0; color: #444; }
            @media print {
                @page { size: 80mm auto; margin: 0; }
                html, body { width: 80mm; height: auto; margin: 0; padding: 0; }
                body { padding: 8px 10px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                .receipt { page-break-inside: avoid; break-inside: avoid; page-break-after: avoid; break-after: avoid; }
                .items tr, .totals tr { page-break-inside: avoid; break-inside: avoid; }
            }
        </style>
    </head>
    <body>
        <div class="receipt">
            <div class="header">
                <h1>${(company.name || "DELICASY").toUpperCase()}</h1>
                ${company.address ? `<p>${company.address}</p>` : ""}
                ${(company.phone || company.phone2) ? `<p>${[company.phone, company.phone2].filter(Boolean).join(" | ")}</p>` : ""}
                ${company.email ? `<p>${company.email}</p>` : ""}
            </div>

            <div class="order-type">${orderTypeLabel}</div>

            <table class="meta">
                <tr><td>Date &amp; Time:</td><td>${saleDate.toLocaleDateString()} ${saleDate.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" })}</td></tr>
             
                <tr><td>Order No:</td><td>${sale.order_id || "N/A"}</td></tr>
                <tr><td>Customer:</td><td>${sale.customer?.name ?? "Walking Customer"}</td></tr>
                <tr><td>Cashier:</td><td>${sale.user?.name || "Admin"}</td></tr>
                <tr><td>Payment:</td><td>${sale.payment_method || "Cash"}</td></tr>
            </table>

            <hr class="divider-solid" />

            <table class="items">
                <thead>
                    <tr>
                        <th style="width:44%;text-align:left">Item</th>
                        <th style="width:32%;text-align:center">Price × Qty</th>
                        <th style="width:24%;text-align:right">Total</th>
                    </tr>
                </thead>
                <tbody>${productRows}</tbody>
            </table>

            <hr class="divider-solid" />

            <table class="totals">
                ${subtotal !== finalTotal ? `<tr><td>Sub Total</td><td>${subtotal.toFixed(2)} LKR</td></tr>` : ""}
                ${ownerDiscount !== 0 ? `<tr><td>Owner Discount</td><td>(${ownerDiscount.toFixed(2)}) LKR</td></tr>` : ""}
                ${customerDiscount !== 0 ? `<tr><td>Customer Discount</td><td>(${customerDiscount.toFixed(2)}) LKR</td></tr>` : ""}
                ${serviceChargePct !== 0 ? `<tr><td>Service Charge</td><td>${serviceChargePct.toFixed(2)} %</td></tr>` : ""}
                <tr class="grand"><td>TOTAL</td><td>${finalTotal.toFixed(2)} LKR</td></tr>
                <tr><td>Cash Paid</td><td>${cashPaid.toFixed(2)} LKR</td></tr>
                <tr class="bold"><td>Balance</td><td>${balance.toFixed(2)} LKR</td></tr>
            </table>

            <div class="footer">
                <p class="no-refund">-- No Exchange or Refunds --</p>
                <p class="thank-you">Thank You, Come Again!</p>
                <p class="powered">Powered by Delicasy POS</p>
            </div>
        </div>
    </body>
    </html>
    `;

    // ---- Open + print ----
    const printWindow = window.open("", "_blank", "width=380,height=600");
    if (!printWindow) {
        alert("Popup block wela thiyenne. Browser eke popup allow karala try karanna.");
        return;
    }
    printWindow.document.open();
    printWindow.document.write(receiptHTML);
    printWindow.document.close();

    // Onload trigger, + fallback timeout (image load slow unata)
    let printed = false;
    const triggerPrint = () => {
        if (printed) return;
        printed = true;
        printWindow.focus();
        printWindow.print();
    };
    printWindow.onload = triggerPrint;
    setTimeout(triggerPrint, 600); // fallback if onload eka fire wenne naththan
};

onMounted(() => {
    // Intentionally no jQuery DataTables here — pagination, search and export
    // are handled natively above so the table stays in sync with Inertia state.
});
</script>

<style>
.reports-page {
    --surface-app: #f4f6fb;
    --accent-50: #eef2ff;
    --accent-100: #e0e7ff;
    --accent-600: #4f46e5;
    --accent-700: #4338ca;
    background: var(--surface-app);
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

/* Professional table styling */
.sales-table {
    border-collapse: collapse;
}

.sales-table tbody tr {
    border-bottom: 1px solid #e2e8f0;
}

.sales-table tbody tr:last-child {
    border-bottom: none;
}

.sales-table tfoot tr {
    background-color: #f8fafc;
    border-top: 2px solid #cbd5e1;
}
</style>
