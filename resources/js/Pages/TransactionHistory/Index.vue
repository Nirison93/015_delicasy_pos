<template>
  <Head title="Order History" />
  <Banner />

  <div class="min-h-screen bg-slate-50">
    <Header />

    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 py-6">

      <!-- Page Header -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
          <Link href="/" class="w-11 h-11 flex items-center justify-center rounded-xl bg-white ring-1 ring-slate-200 text-slate-600 hover:bg-slate-100 transition">
            <i class="ri-arrow-left-s-line text-2xl"></i>
          </Link>
          <h1 class="text-2xl font-bold text-slate-800 tracking-wide uppercase">Order History</h1>
        </div>
        <!-- Total badge -->
        <div class="flex items-center gap-3 px-5 py-2.5 bg-white rounded-xl ring-1 ring-slate-200">
          <span class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-800 text-white text-[15px] font-bold">
            {{ totalhistoryTransactions }}
          </span>
          <span class="text-[15px] font-semibold text-slate-600">Total Transactions</span>
        </div>
      </div>

      <!-- Filters Card -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm px-6 py-5 mb-6">
        <div class="flex flex-wrap items-center gap-3">
          <div class="flex items-center gap-2">
            <i class="ri-calendar-line text-slate-400 text-lg"></i>
            <input
              v-model="startDate"
              type="date"
              class="h-11 px-4 text-[15px] text-slate-700 bg-slate-50 ring-1 ring-slate-200 border-0 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
            />
          </div>
          <span class="text-[15px] font-semibold text-slate-400">to</span>
          <div class="flex items-center gap-2">
            <i class="ri-calendar-line text-slate-400 text-lg"></i>
            <input
              v-model="endDate"
              type="date"
              class="h-11 px-4 text-[15px] text-slate-700 bg-slate-50 ring-1 ring-slate-200 border-0 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
            />
          </div>
          <button
            @click="filterData"
            class="h-11 px-6 flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-white text-[15px] font-semibold rounded-xl active:scale-95 transition"
          >
            <i class="ri-filter-3-line"></i> Filter
          </button>
          <Link
            href="/transactionHistory"
            class="h-11 px-6 flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[15px] font-semibold rounded-xl active:scale-95 transition"
          >
            <i class="ri-refresh-line"></i> Reset
          </Link>
          <button
            v-if="selectedOrders.length"
            @click="deleteSelected"
            class="h-11 px-6 flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white text-[15px] font-semibold rounded-xl active:scale-95 transition ml-auto"
          >
            <i class="ri-delete-bin-line"></i>
            Delete Selected ({{ selectedOrders.length }})
          </button>
        </div>
      </div>

      <!-- Table Card -->
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm overflow-hidden">
        <template v-if="allhistoryTransactions && allhistoryTransactions.length > 0">
          <div class="overflow-x-auto">
            <table
              id="TransitionTable"
              class="w-full table-auto"
            >
              <thead>
                <tr class="bg-slate-800 text-white">
                  <th class="px-5 py-4 text-left text-[13px] font-semibold tracking-wider uppercase whitespace-nowrap">#</th>
                  <th class="px-5 py-4 text-left text-[13px] font-semibold tracking-wider uppercase">
                    <input type="checkbox" @change="toggleAll" :checked="areAllSelected" class="w-4 h-4 rounded cursor-pointer" />
                  </th>
                  <th class="px-5 py-4 text-left text-[13px] font-semibold tracking-wider uppercase whitespace-nowrap">Order ID</th>
                  <th class="px-5 py-4 text-left text-[13px] font-semibold tracking-wider uppercase whitespace-nowrap">Total Amount</th>
                  <th class="px-5 py-4 text-left text-[13px] font-semibold tracking-wider uppercase whitespace-nowrap">Discount</th>
                  <th class="px-5 py-4 text-left text-[13px] font-semibold tracking-wider uppercase whitespace-nowrap">Payment</th>
                  <th class="px-5 py-4 text-left text-[13px] font-semibold tracking-wider uppercase whitespace-nowrap">Bank</th>
                  <th class="px-5 py-4 text-left text-[13px] font-semibold tracking-wider uppercase whitespace-nowrap">Card Last 4</th>
                  <th class="px-5 py-4 text-left text-[13px] font-semibold tracking-wider uppercase whitespace-nowrap">Order Type</th>
                  <th class="px-5 py-4 text-left text-[13px] font-semibold tracking-wider uppercase whitespace-nowrap">Sale Date</th>
                  <th class="px-5 py-4 text-left text-[13px] font-semibold tracking-wider uppercase whitespace-nowrap">Actions</th>
                </tr>
              </thead>

              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="(history, index) in allhistoryTransactions"
                  :key="history.id"
                  class="hover:bg-slate-50 transition-colors"
                >
                  <td class="px-5 py-4 text-[14px] text-slate-500 font-medium">{{ index + 1 }}</td>
                  <td class="px-5 py-4">
                    <input type="checkbox" v-model="selectedOrders" :value="history.order_id" class="w-4 h-4 rounded cursor-pointer" />
                  </td>

                  <td class="px-5 py-4">
                    <span class="text-[15px] font-bold text-slate-800">{{ history.order_id || '—' }}</span>
                  </td>

                  <td class="px-5 py-4">
                    <span class="text-[15px] font-bold text-emerald-700">{{ formatLKR(rowNetTotal(history)) }}</span>
                  </td>

                  <td class="px-5 py-4">
                    <span class="text-[15px] font-semibold text-red-500">
                      {{ formatLKR(asNumber(history.discount) + asNumber(history.custom_discount)) }}
                    </span>
                  </td>

                  <td class="px-5 py-4">
                    <span :class="[
                      'inline-flex items-center px-3 py-1 rounded-lg text-[13px] font-semibold',
                      history.payment_method === 'card'
                        ? 'bg-blue-50 text-blue-700'
                        : 'bg-amber-50 text-amber-700'
                    ]">
                      <i :class="history.payment_method === 'card' ? 'ri-bank-card-line mr-1' : 'ri-money-dollar-circle-line mr-1'"></i>
                      {{ history.payment_method || 'N/A' }}
                    </span>
                  </td>

                  <td class="px-5 py-4 text-[15px] text-slate-700">{{ history.bank_name || '—' }}</td>
                  <td class="px-5 py-4 text-[15px] text-slate-700">{{ history.card_last4 || '—' }}</td>

                  <td class="px-5 py-4">
                    <span v-if="history.order_type === 'takeaway'"
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-[13px] font-semibold bg-amber-50 text-amber-700">
                      <i class="ri-shopping-bag-3-line"></i> Takeaway
                    </span>
                    <span v-else-if="history.order_type === 'pickup'"
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-[13px] font-semibold bg-violet-50 text-violet-700">
                      <i class="ri-hotel-bed-line"></i>
                      Delivery
                      <span v-if="history.delivery_charge" class="text-violet-500">({{ history.delivery_charge }} LKR)</span>
                    </span>
                    <span v-else
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-[13px] font-semibold bg-emerald-50 text-emerald-700">
                      <i class="ri-restaurant-line"></i> Dine In
                    </span>
                  </td>

                  <td class="px-5 py-4 text-[15px] text-slate-700 whitespace-nowrap">{{ history.sale_date || '—' }}</td>

                  <!-- Actions -->
                  <td class="px-5 py-4">
                    <div class="flex items-center gap-2">
                      <button
                        @click="printReceipt(history)"
                        class="h-9 px-4 flex items-center gap-1.5 bg-orange-500 hover:bg-orange-600 text-white text-[13px] font-semibold rounded-xl active:scale-95 transition"
                      >
                        <i class="ri-printer-line"></i> Print
                      </button>
                      <button
                        @click="viewDetails(history)"
                        class="h-9 px-4 flex items-center gap-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-[13px] font-semibold rounded-xl active:scale-95 transition"
                      >
                        <i class="ri-eye-line"></i> View
                      </button>
                      <button
                        @click="printKOTReceipt(history)"
                        class="h-9 px-4 flex items-center gap-1.5 bg-blue-500 hover:bg-blue-600 text-white text-[13px] font-semibold rounded-xl active:scale-95 transition"
                      >
                        <i class="ri-restaurant-2-line"></i> KOT
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>

        <template v-else>
          <div class="flex flex-col items-center justify-center py-20 gap-4">
            <i class="ri-file-list-3-line text-6xl text-slate-300"></i>
            <p class="text-xl font-semibold text-slate-400">No transaction history available</p>
          </div>
        </template>
      </div>

    </div>
  </div>

  <!-- View Details Modal -->
  <div
    v-if="selectedTransaction"
    class="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-50 p-4"
  >
    <div class="bg-white rounded-3xl shadow-2xl ring-1 ring-slate-200 w-full max-w-3xl max-h-[90vh] overflow-y-auto flex flex-col">

      <!-- Modal Header -->
      <div class="flex items-center justify-between px-7 py-5 border-b border-slate-100 sticky top-0 bg-white z-10">
        <div class="flex items-center gap-3">
          <div class="w-11 h-11 flex items-center justify-center rounded-xl bg-blue-50">
            <i class="ri-file-list-3-line text-xl text-blue-600"></i>
          </div>
          <div>
            <h2 class="text-xl font-bold text-slate-800">Order Details</h2>
            <p class="text-[13px] text-slate-400">Order #{{ selectedTransaction.order_id }}</p>
          </div>
        </div>
        <button
          @click="closeModal"
          class="w-10 h-10 flex items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
        >
          <i class="ri-close-line text-xl"></i>
        </button>
      </div>

      <!-- Order Info Grid -->
      <div class="px-7 py-6">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
          <div class="bg-slate-50 rounded-2xl px-4 py-4">
            <p class="text-[12px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Order ID</p>
            <p class="text-[16px] font-bold text-slate-800">{{ selectedTransaction.order_id }}</p>
          </div>
          <div class="bg-emerald-50 rounded-2xl px-4 py-4">
            <p class="text-[12px] font-semibold text-emerald-500 uppercase tracking-wider mb-1">Total Amount</p>
            <p class="text-[16px] font-bold text-emerald-800">{{ formatLKR(rowNetTotal(selectedTransaction)) }}</p>
          </div>
          <div class="bg-red-50 rounded-2xl px-4 py-4">
            <p class="text-[12px] font-semibold text-red-400 uppercase tracking-wider mb-1">Discount</p>
            <p class="text-[16px] font-bold text-red-700">
              {{ formatLKR(asNumber(selectedTransaction.discount) + asNumber(selectedTransaction.custom_discount)) }}
            </p>
          </div>
          <div class="bg-slate-50 rounded-2xl px-4 py-4">
            <p class="text-[12px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Payment</p>
            <p class="text-[16px] font-bold text-slate-800">{{ selectedTransaction.payment_method }}</p>
          </div>
          <div class="bg-slate-50 rounded-2xl px-4 py-4">
            <p class="text-[12px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Order Type</p>
            <p class="text-[16px] font-bold text-slate-800">
              <span v-if="selectedTransaction.order_type === 'takeaway'">Takeaway</span>
              <span v-else-if="selectedTransaction.order_type === 'pickup'">Delivery</span>
              <span v-else>Dine In</span>
            </p>
          </div>
          <div class="bg-slate-50 rounded-2xl px-4 py-4">
            <p class="text-[12px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sale Date</p>
            <p class="text-[16px] font-bold text-slate-800">{{ selectedTransaction.sale_date }}</p>
          </div>
          <div class="bg-slate-50 rounded-2xl px-4 py-4">
            <p class="text-[12px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Customer</p>
            <p class="text-[16px] font-bold text-slate-800">{{ selectedTransaction.customer?.name || 'N/A' }}</p>
          </div>
          <div class="bg-slate-50 rounded-2xl px-4 py-4">
            <p class="text-[12px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Cashier</p>
            <p class="text-[16px] font-bold text-slate-800">{{ selectedTransaction.user?.name || 'N/A' }}</p>
          </div>
          <div class="bg-slate-50 rounded-2xl px-4 py-4">
            <p class="text-[12px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Bank</p>
            <p class="text-[16px] font-bold text-slate-800">{{ selectedTransaction.bank_name || '—' }}</p>
          </div>
          <div v-if="selectedTransaction.card_last4" class="bg-slate-50 rounded-2xl px-4 py-4">
            <p class="text-[12px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Card Last 4</p>
            <p class="text-[16px] font-bold text-slate-800">{{ selectedTransaction.card_last4 }}</p>
          </div>
          <div v-if="selectedTransaction.delivery_charge && selectedTransaction.delivery_charge != 0" class="bg-slate-50 rounded-2xl px-4 py-4">
            <p class="text-[12px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Delivery Charge</p>
            <p class="text-[16px] font-bold text-slate-800">{{ formatLKR(selectedTransaction.delivery_charge) }}</p>
          </div>
          <div v-if="selectedTransaction.service_charge && selectedTransaction.service_charge != 0" class="bg-slate-50 rounded-2xl px-4 py-4">
            <p class="text-[12px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Service Charge</p>
            <p class="text-[16px] font-bold text-slate-800">{{ asNumber(selectedTransaction.service_charge) }}%</p>
          </div>
          <div v-if="selectedTransaction.bank_service_charge && selectedTransaction.bank_service_charge != 0" class="bg-slate-50 rounded-2xl px-4 py-4">
            <p class="text-[12px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Bank Service Charge</p>
            <p class="text-[16px] font-bold text-slate-800">{{ asNumber(selectedTransaction.bank_service_charge) }}%</p>
          </div>
        </div>

        <!-- Items Table -->
        <div>
          <h3 class="text-[16px] font-bold text-slate-700 mb-3 flex items-center gap-2">
            <i class="ri-shopping-cart-line text-slate-400"></i> Items
          </h3>
          <div class="overflow-x-auto rounded-2xl ring-1 ring-slate-200">
            <table class="min-w-full">
              <thead class="bg-slate-800 text-white">
                <tr>
                  <th class="px-5 py-3.5 text-left text-[13px] font-semibold tracking-wider uppercase">Product</th>
                  <th class="px-5 py-3.5 text-right text-[13px] font-semibold tracking-wider uppercase">Qty</th>
                  <th class="px-5 py-3.5 text-right text-[13px] font-semibold tracking-wider uppercase">Unit Price</th>
                  <th class="px-5 py-3.5 text-right text-[13px] font-semibold tracking-wider uppercase">Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="(item, index) in selectedTransaction.sale_items"
                  :key="index"
                  class="hover:bg-slate-50 transition-colors"
                >
                  <td class="px-5 py-3.5 text-[15px] text-slate-800 font-medium">{{ item.product?.name || 'N/A' }}</td>
                  <td class="px-5 py-3.5 text-right text-[15px] text-slate-700">{{ asNumber(item.quantity) }}</td>
                  <td class="px-5 py-3.5 text-right text-[15px] text-slate-700">{{ formatLKR(asNumber(item.unit_price)) }}</td>
                  <td class="px-5 py-3.5 text-right text-[15px] font-bold text-slate-800">{{ formatLKR(lineTotal(item)) }}</td>
                </tr>
                <tr v-if="!selectedTransaction.sale_items.length">
                  <td colspan="4" class="text-center py-8 text-[15px] text-slate-400">
                    No items in this order
                  </td>
                </tr>
              </tbody>
              <tfoot v-if="selectedTransaction.sale_items && selectedTransaction.sale_items.length" class="bg-slate-50 border-t border-slate-200">
                <tr>
                  <td colspan="3" class="px-5 py-3 text-right text-[15px] font-medium text-slate-600">Sub Total</td>
                  <td class="px-5 py-3 text-right text-[15px] font-bold text-slate-800">
                    {{ formatLKR(itemsSubtotal(selectedTransaction)) }}
                  </td>
                </tr>
                <tr v-if="asNumber(selectedTransaction.discount) || asNumber(selectedTransaction.custom_discount)">
                  <td colspan="3" class="px-5 py-2.5 text-right text-[15px] text-slate-600">Discount</td>
                  <td class="px-5 py-2.5 text-right text-[15px] font-semibold text-red-500">
                    - {{ formatLKR(asNumber(selectedTransaction.discount) + asNumber(selectedTransaction.custom_discount)) }}
                  </td>
                </tr>
                <tr v-if="asNumber(selectedTransaction.delivery_charge)">
                  <td colspan="3" class="px-5 py-2.5 text-right text-[15px] text-slate-600">Delivery Charge</td>
                  <td class="px-5 py-2.5 text-right text-[15px] font-semibold text-slate-700">
                    + {{ formatLKR(selectedTransaction.delivery_charge) }}
                  </td>
                </tr>
                <tr v-if="asNumber(selectedTransaction.bank_service_charge)">
                  <td colspan="3" class="px-5 py-2.5 text-right text-[15px] text-slate-600">Bank Service Charge</td>
                  <td class="px-5 py-2.5 text-right text-[15px] font-semibold text-slate-700">
                    + {{ formatLKR(itemsSubtotal(selectedTransaction) * asNumber(selectedTransaction.bank_service_charge) / 100) }}
                  </td>
                </tr>
                <tr v-if="asNumber(selectedTransaction.service_charge)">
                  <td colspan="3" class="px-5 py-2.5 text-right text-[15px] text-slate-600">Service Charge</td>
                  <td class="px-5 py-2.5 text-right text-[15px] font-semibold text-slate-700">
                    + {{ formatLKR(itemsSubtotal(selectedTransaction) * asNumber(selectedTransaction.service_charge) / 100) }}
                  </td>
                </tr>
                <tr class="border-t-2 border-slate-200">
                  <td colspan="3" class="px-5 py-4 text-right text-[16px] font-bold text-slate-800">Grand Total</td>
                  <td class="px-5 py-4 text-right text-[18px] font-extrabold text-emerald-700">
                    {{ formatLKR(rowNetTotal(selectedTransaction)) }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="px-7 py-5 border-t border-slate-100 flex justify-end">
        <button
          @click="closeModal"
          class="h-12 px-8 bg-slate-800 hover:bg-slate-700 text-white text-[15px] font-semibold rounded-2xl active:scale-95 transition"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { Head, Link } from '@inertiajs/vue3'
import Header from '@/Components/custom/Header.vue'
import Footer from '@/Components/custom/Footer.vue'
import Banner from '@/Components/Banner.vue'
import { HasRole } from '@/Utils/Permissions'

// Props
const props = defineProps({
  allhistoryTransactions: { type: Array, default: () => [] },
  totalhistoryTransactions: { type: Number, default: 0 },
  companyInfo1: { type: Array, default: () => [] },
  startDate: { type: String, default: '' },
  endDate: { type: String, default: '' },
})

const form = useForm({})
const selectedTransaction = ref(null)

const viewDetails = (history) => { selectedTransaction.value = history }
const closeModal = () => { selectedTransaction.value = null }

// --- Helpers ---
const asNumber = (val) => Number.parseFloat(val ?? 0) || 0
const formatLKR = (value) =>
  `${asNumber(value).toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} LKR`

const lineTotal = (item) => asNumber(item.unit_price) * asNumber(item.quantity)

const itemsSubtotal = (row) => (row?.sale_items ?? []).reduce((sum, it) => sum + lineTotal(it), 0)

// Final net total per row with charges/discounts.
// Includes: subtotal (or total_amount if you rely on backend), + delivery, - discounts, + bank/service % on subtotal.
const rowNetTotal = (row) => {
  // prefer backend `total_amount` if it already equals items subtotal; fallback to computed
  const base = asNumber(row.total_amount) || itemsSubtotal(row)
  const withDelivery = base + asNumber(row.delivery_charge)
  const afterDiscounts = withDelivery - (asNumber(row.discount) + asNumber(row.custom_discount))
  const bankPct = asNumber(row.bank_service_charge)
  const svcPct = asNumber(row.service_charge)
  const pctAdd = (base * bankPct) / 100 + (base * svcPct) / 100
  return afterDiscounts + pctAdd
}

// DataTables
onMounted(() => {
  // eslint-disable-next-line no-undef
  const table = $('#TransitionTable').DataTable({
    dom: 'Bfrtip',
    pageLength: 10,
    buttons: [],
    order: [],
    aaSorting: [],
    columnDefs: [
      {
        targets: '_all',
        orderable: false,
      },
      { targets: 2, searchable: false },
    ],
    initComplete: function () {
      const searchInput = $('div.dataTables_filter input')
      searchInput.attr('placeholder', 'Search ...')
      searchInput.on('keypress', function (e) {
        if (e.which === 13) {
          // eslint-disable-next-line no-undef
          table.search(this.value).draw()
        }
      })
    },
    language: { search: '' },
  })
})

// Printing - Thermal Receipt Format
const printReceipt = (history) => {
  const companyData = props.companyInfo1[0] || {}
  const getSafeValue = (obj, path) => path.split('.').reduce((acc, part) => (acc && acc[part] ? acc[part] : ''), obj)
  const saleItems = history.sale_items || []

  const itemRows = saleItems.map(item => {
    const itemName = getSafeValue(item, 'product.name') || 'N/A'
    const price = asNumber(item.unit_price)
    const qty = asNumber(item.quantity)
    const itemTotal = price * qty
    return `
      <tr style="border-bottom: 1px solid #ddd;">
        <td style="padding: 6px 4px; text-align: left; font-size: 12px;">${itemName}</td>
        <td style="padding: 6px 4px; text-align: center; font-size: 12px;">${price.toFixed(2)} × ${qty}</td>
        <td style="padding: 6px 4px; text-align: right; font-size: 12px; font-weight: bold;">${itemTotal.toFixed(2)}</td>
      </tr>
    `
  }).join('')

  const cash = asNumber(history.cash)
  const total = rowNetTotal(history)
  const balance = cash - total

  const dateTime = new Date(history.created_at)
  const dateStr = dateTime.toLocaleDateString('en-US', { year: '2-digit', month: '2-digit', day: '2-digit' })
  const timeStr = dateTime.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true })

  const receiptContent = `
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <style>
      * { margin: 0; padding: 0; box-sizing: border-box; }
      @page { size: 80mm auto; margin: 0; padding: 0; }
      @media print {
        html, body { margin: 0 !important; padding: 0 !important; background: white !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { padding: 0 !important; }
      }
      body {
        background: white;
        font-family: Arial, sans-serif;
        font-size: 12px;
        margin: 0;
        padding: 10px;
        color: #000;
        width: 80mm;
        line-height: 1.4;
      }
      .receipt { width: 100%; }
      .header { text-align: center; margin-bottom: 10px; border-bottom: 2px solid #000; padding-bottom: 8px; }
      .header h1 { font-size: 16px; font-weight: bold; margin: 0 0 3px 0; letter-spacing: 1px; }
      .header p { font-size: 9px; margin: 2px 0; line-height: 1.2; }
      .order-type-box { text-align: center; font-weight: bold; margin: 8px 0; padding: 6px; border: 2px solid #000; font-size: 13px; }
      .info-section { margin: 8px 0; font-size: 11px; }
      .info-row { display: flex; justify-content: space-between; margin: 3px 0; }
      .info-label { font-weight: bold; }
      .info-value { text-align: right; }
      table { width: 100%; border-collapse: collapse; margin: 8px 0; }
      .table-header { background-color: #f5f5f5; }
      .table-header th { padding: 6px 4px; text-align: left; font-weight: bold; font-size: 11px; border-bottom: 2px solid #000; }
      .table-header th:nth-child(2) { text-align: center; }
      .table-header th:nth-child(3) { text-align: right; }
      tbody tr { border-bottom: 1px solid #e0e0e0; }
      tbody tr:hover { background-color: #fafafa; }
      .totals-section { border-top: 2px solid #000; padding-top: 8px; margin-top: 8px; }
      .total-row { display: flex; justify-content: space-between; margin: 5px 0; font-size: 12px; }
      .total-row.final { font-weight: bold; font-size: 13px; border-top: 1px solid #000; padding-top: 5px; }
      .footer { text-align: center; font-size: 10px; margin-top: 10px; border-top: 2px solid #000; padding-top: 8px; }
      .footer p { margin: 3px 0; }
    </style>
  </head>
  <body>
    <div class="receipt">
      <!-- Header -->
      <div class="header">
        <h1>${companyData.name || 'DELICASY'}</h1>
        <p>${companyData.address || ''}</p>
        <p>${companyData.phone || ''}</p>
      </div>

      <!-- Order Type -->
      <div class="order-type-box">${
        history.order_type === 'takeaway' ? 'TAKEAWAY' :
        history.order_type === 'pickup' ? 'DELIVERY' : 'DINE IN'
      }</div>

      <!-- Order Info -->
      <div class="info-section">
        <div class="info-row">
          <span class="info-label">Date & Time :</span>
          <span class="info-value">${dateStr} ${timeStr}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Order No</span>
          <span class="info-value">${history.order_id}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Customer</span>
          <span class="info-value">${history.customer?.name || 'Walking Customer'}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Cashier</span>
          <span class="info-value">${history.user?.name || 'N/A'}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Payment</span>
          <span class="info-value">${history.payment_method === 'card' ? 'Card' : 'Cash'}</span>
        </div>
      </div>

      <!-- Items Table -->
      <table>
        <thead class="table-header">
          <tr>
            <th>ITEM</th>
            <th>PRICE × QTY</th>
            <th>TOTAL</th>
          </tr>
        </thead>
        <tbody>
          ${itemRows}
        </tbody>
      </table>

      <!-- Totals -->
      <div class="totals-section">
        <div class="total-row final">
          <span>TOTAL</span>
          <span>${total.toFixed(2)} LKR</span>
        </div>
        ${cash > 0 ? `<div class="total-row">
          <span>Cash Paid</span>
          <span>${cash.toFixed(2)} LKR</span>
        </div>` : ''}
        ${balance > 0 ? `<div class="total-row">
          <span>Balance</span>
          <span>${balance.toFixed(2)} LKR</span>
        </div>` : ''}
      </div>

      <!-- Footer -->
      <div class="footer">
        <p>-- No Exchange or Refunds --</p>
        <p style="font-weight: bold; font-size: 12px; margin-top: 5px;">THANK YOU, COME AGAIN!</p>
        <p style="margin-top: 5px;">Powered by Delicasy POS</p>
      </div>
    </div>
  </body>
  </html>`

  const printWindow = window.open('', '_blank')
  if (!printWindow) { alert('Please allow popups to print receipts'); return; }
  printWindow.document.write(receiptContent)
  printWindow.document.close()
  printWindow.focus()
  printWindow.onload = () => {
    printWindow.print()
    printWindow.close()
  }
}

const printKOTReceipt = (history) => {
  const getSafeValue = (obj, path) => path.split('.').reduce((acc, part) => (acc && acc[part] ? acc[part] : ''), obj)
  const saleItems = history.sale_items || []
  const productRows = saleItems.map(item => `
    <tr>
      <td><strong>${getSafeValue(item, 'product.name') || 'N/A'}</strong></td>
      <td class="text-right"><strong>${asNumber(item.quantity)}</strong></td>
      <td><strong>${getSafeValue(item, 'product.size.name') || 'N/A'}</strong></td>
    </tr>
  `).join('')

  const receiptContent = `
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KOT</title>
    <style>
      * { margin: 0; padding: 0; box-sizing: border-box; }

      @page { size: 80mm auto; margin: 0; padding: 0; orphans: 1; widows: 1; }

      @media print {
        html, body { margin: 0 !important; padding: 0 !important; width: 80mm !important; height: auto !important; background: white !important; overflow: visible !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; page-break-inside: avoid !important; break-inside: avoid !important; }
        body { padding: 10px !important; }
      }

      body { background: white; font-size: 13px; font-weight: bold; font-family: Arial, sans-serif; margin: 0; padding: 10px; color: #000; width: 80mm; line-height: 1.2; }
      h1 { font-weight: bold; font-size: 16px; margin: 8px 0; page-break-inside: avoid; break-inside: avoid; }
      .section { margin-bottom: 16px; padding-top: 8px; border-top: 1px solid #000; page-break-inside: avoid; break-inside: avoid; }
      .info-row { display: flex; justify-content: space-between; font-size: 13px; font-weight: bold; margin-top: 8px; page-break-inside: avoid; break-inside: avoid; }
      table { width: 100%; font-size: 13px; font-weight: bold; border-collapse: collapse; margin-top: 8px; page-break-inside: avoid; break-inside: avoid; }
      thead { page-break-inside: avoid; break-inside: avoid; }
      tbody { page-break-inside: avoid; break-inside: avoid; }
      tbody tr { page-break-inside: avoid; break-inside: avoid; }
      table th, table td { padding: 8px 8px; border-bottom: 1px solid #000; font-weight: bold; page-break-inside: avoid; break-inside: avoid; }
      table th { text-align: left; font-weight: bold; }
      table td { text-align: right; font-weight: bold; }
      table td:first-child { text-align: left; font-weight: bold; }
      .kot-container { page-break-inside: avoid; break-inside: avoid; width: 80mm; }
    </style>
  </head>
  <body>
    <div class="kot-container">
    <h1 style="text-align:center">KOT Note</h1>

    <div style="font-weight:bold; border:1px solid black; text-align:center; padding:5px; margin:8px 0;">
      <small style="display:block;">
        Order Type: ${
          history.order_type === 'takeaway' ? 'Takeaway' :
          history.order_type === 'pickup' ? 'Delivery' : 'Dine In'
        }
      </small>
    </div>

    <div class="section">
      <div class="info-row">
        <div><strong>Date:</strong> ${new Date(history.created_at).toLocaleDateString('en-US', { dateStyle: 'medium' })}</div>
        <div><strong>Order No:</strong> ${history.order_id}</div>
      </div>
      <div class="info-row">
        <div><strong>Customer:</strong> ${history.customer?.name || ''}</div>
        <div><strong>Cashier:</strong> ${history.user?.name || ''}</div>
      </div>
    </div>

    <div class="section">
      <table>
        <thead><tr><th>Product</th><th class="text-right">Qty</th><th>Size</th></tr></thead>
        <tbody>${productRows}</tbody>
      </table>
    </div>

    ${history.kitchen_note
      ? `<div style="font-weight:bold; text-align:left; border-top:1px solid black; border-bottom:1px solid black; padding:10px 0; page-break-inside: avoid; break-inside: avoid;">
           <small style="display:block; text-align:left;">Note: ${history.kitchen_note}</small>
         </div>`
      : ''
    }
    </div>
  </body>
  </html>`

  const printWindow = window.open('', '_blank')
  if (!printWindow) { alert('Please allow popups to print KOT'); return; }
  printWindow.document.write(receiptContent)
  printWindow.document.close()
  printWindow.focus()
  printWindow.onload = () => {
    printWindow.print()
    printWindow.close()
  }
}

// Delete single
const deleteReceipt = (orderId) => {
  if (confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
    router.post(route('transactions.delete'), { order_id: orderId }, {
      onError: (error) => alert('Error: ' + (error.message || 'Something went wrong.')),
    })
  }
}

// Date filters
const startDate = ref(props.startDate)
const endDate = ref(props.endDate)

const filterData = () => {
  if (new Date(startDate.value) > new Date(endDate.value)) {
    alert('Start date cannot be greater than end date.')
    return
    }
  router.get(route('transactionHistory.index'), {
    start_date: startDate.value,
    end_date: endDate.value,
  })
}

// Bulk select/delete
const selectedOrders = ref([])

const areAllSelected = computed(() =>
  props.allhistoryTransactions.length > 0 &&
  props.allhistoryTransactions.every((t) => selectedOrders.value.includes(t.order_id))
)

const toggleAll = () => {
  if (areAllSelected.value) {
    selectedOrders.value = []
  } else {
    selectedOrders.value = props.allhistoryTransactions.map((t) => t.order_id)
  }
}

const deleteSelected = () => {
  if (!selectedOrders.value.length) return

  if (confirm(`Are you sure you want to delete ${selectedOrders.value.length} record(s)?`)) {
    router.post(route('transactions.bulkDelete'), {
      order_ids: selectedOrders.value,
    }, {
      onSuccess: () => { selectedOrders.value = [] },
      onError: (error) => alert('Failed to delete: ' + (error.message || 'Unknown error')),
    })
  }
}
</script>

<style>
/* DataTables wrapper spacing */
.dataTables_wrapper {
  padding: 20px 24px;
}

/* Search input */
#TransitionTable_filter {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
  float: left;
}
#TransitionTable_filter label {
  font-size: 15px;
  color: #475569;
  display: flex;
  align-items: center;
  gap: 8px;
}
#TransitionTable_filter input[type="search"] {
  padding: 9px 16px;
  font-size: 15px;
  color: #1e293b;
  border: none;
  border-radius: 12px;
  background: #f8fafc;
  box-shadow: inset 0 0 0 1px #e2e8f0;
  outline: none;
  transition: box-shadow 0.2s;
}
#TransitionTable_filter input[type="search"]:focus {
  box-shadow: inset 0 0 0 2px #60a5fa;
}

/* Pagination */
.dataTables_wrapper .dataTables_paginate {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 4px;
  margin-top: 20px;
  padding-bottom: 4px;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
  padding: 6px 14px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  color: #475569 !important;
  background: #f1f5f9;
  border: none !important;
  cursor: pointer;
  transition: background 0.15s;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
  background: #e2e8f0 !important;
  color: #1e293b !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current {
  background: #1e293b !important;
  color: #fff !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

/* Info text */
.dataTables_wrapper .dataTables_info {
  font-size: 14px;
  color: #64748b;
  padding: 8px 0;
}
</style>
