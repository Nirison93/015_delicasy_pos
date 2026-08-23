<template>
  <Teleport to="body">
    <div v-if="open" class="fixed inset-0 z-[1000] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="handleBackdropClick"></div>
      <div class="relative bg-zinc-900 rounded-2xl border border-white/10 shadow-2xl w-[1180px] max-w-[98vw] max-h-[96vh] flex flex-col overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between px-8 py-6 border-b border-white/10 bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900 flex-shrink-0">
          <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-amber-500/20 ring-1 ring-amber-500/40">
              <i class="ri-safe-line text-amber-400 text-3xl"></i>
            </div>
            <div>
              <h3 class="text-3xl font-bold text-white leading-none">
                {{ view === 'report' ? 'Cash Drawer Closed' : 'Close Cash Drawer' }}
              </h3>
              <p class="text-xl text-zinc-400 mt-2" v-if="cashDrawer?.id">Drawer #{{ cashDrawer.id }}</p>
            </div>
          </div>
          <button @click="handleClose" class="w-14 h-14 flex items-center justify-center rounded-xl bg-white/10 text-zinc-400 hover:bg-white/20 hover:text-white transition">
            <i class="ri-close-line text-3xl"></i>
          </button>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto px-8 py-6 space-y-6">
          <div v-if="loadingPreview" class="py-20 text-center text-zinc-400 text-2xl">
            <i class="ri-loader-4-line animate-spin text-4xl"></i>
            <p class="mt-3">Loading drawer summary...</p>
          </div>

          <div v-else-if="previewError" class="py-10 text-center text-rose-400 text-2xl">
            {{ previewError }}
          </div>

          <!-- ============ FORM VIEW ============ -->
          <template v-else-if="view === 'form' && preview">
            <!-- 1. Drawer Information -->
            <section class="bg-zinc-800/50 rounded-2xl border border-white/5 p-6">
              <h4 class="text-2xl font-bold text-zinc-200 mb-4 flex items-center gap-2">
                <i class="ri-information-line text-amber-400"></i> Drawer Information
              </h4>
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-5 text-xl">
                <div>
                  <p class="text-zinc-500">Opened By</p>
                  <p class="text-white font-semibold text-2xl">{{ preview.cash_drawer?.openedByUser?.name || '-' }}</p>
                </div>
                <div>
                  <p class="text-zinc-500">Opened At</p>
                  <p class="text-white font-semibold text-2xl">{{ fmtDate(preview.cash_drawer?.opened_at) }}</p>
                </div>
                <div>
                  <p class="text-zinc-500">Opening Balance</p>
                  <p class="text-amber-300 font-semibold text-2xl">{{ fmt(preview.opening_balance) }} LKR</p>
                </div>
                <div>
                  <p class="text-zinc-500">Closing Cashier</p>
                  <p class="text-white font-semibold text-2xl">{{ loggedInUser?.name || '-' }}</p>
                </div>
              </div>
            </section>

            <!-- 2. Cash Summary + movements -->
            <section class="bg-zinc-800/50 rounded-2xl border border-white/5 p-6">
              <h4 class="text-2xl font-bold text-zinc-200 mb-4 flex items-center gap-2">
                <i class="ri-hand-coin-line text-amber-400"></i> Cash Summary
              </h4>
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-5 text-xl mb-6">
                <div class="bg-zinc-900/60 rounded-xl p-5">
                  <p class="text-zinc-500">Cash Sales</p>
                  <p class="text-emerald-400 font-bold text-2xl">{{ fmt(preview.cash_sales) }}</p>
                </div>
                <div class="bg-zinc-900/60 rounded-xl p-5">
                  <p class="text-zinc-500">Cash In</p>
                  <p class="text-emerald-400 font-bold text-2xl">{{ fmt(preview.cash_in) }}</p>
                </div>
                <div class="bg-zinc-900/60 rounded-xl p-5">
                  <p class="text-zinc-500">Cash Out</p>
                  <p class="text-rose-400 font-bold text-2xl">{{ fmt(preview.cash_out) }}</p>
                </div>
                <div class="bg-zinc-900/60 rounded-xl p-5">
                  <p class="text-zinc-500">Cash Drops</p>
                  <p class="text-rose-400 font-bold text-2xl">{{ fmt(preview.cash_drops) }}</p>
                </div>
              </div>

              <!-- Add movement mini-form -->
              <div class="flex flex-wrap items-end gap-4 mb-4">
                <div>
                  <label class="block text-lg text-zinc-400 mb-1.5">Type</label>
                  <select v-model="movementType" class="h-14 px-3 text-xl bg-zinc-800 border border-white/10 rounded-xl text-white">
                    <option value="cash_in">Cash In</option>
                    <option value="cash_out">Cash Out</option>
                    <option value="cash_drop">Cash Drop</option>
                  </select>
                </div>
                <div>
                  <label class="block text-lg text-zinc-400 mb-1.5">Amount</label>
                  <input v-model="movementAmount" type="number" min="0" step="0.01" placeholder="0.00"
                    class="h-14 w-36 px-3 text-xl bg-zinc-800 border border-white/10 rounded-xl text-white placeholder-zinc-500" />
                </div>
                <div class="flex-1 min-w-[200px]">
                  <label class="block text-lg text-zinc-400 mb-1.5">Reason {{ movementType !== 'cash_in' ? '(required)' : '(optional)' }}</label>
                  <input v-model="movementReason" type="text" placeholder="e.g. Safe deposit, petty cash..."
                    class="h-14 w-full px-3 text-xl bg-zinc-800 border border-white/10 rounded-xl text-white placeholder-zinc-500" />
                </div>
                <button @click="addMovement" :disabled="movementSubmitting"
                  class="h-14 px-6 rounded-xl bg-amber-500 hover:bg-amber-400 text-zinc-900 font-bold text-xl disabled:opacity-60 transition">
                  {{ movementSubmitting ? 'Adding...' : 'Add' }}
                </button>
              </div>
              <p v-if="movementError" class="text-rose-400 text-lg mb-3">{{ movementError }}</p>

              <div v-if="preview.movements?.length" class="max-h-40 overflow-y-auto rounded-xl border border-white/5">
                <table class="w-full text-lg">
                  <tbody class="divide-y divide-white/5">
                    <tr v-for="m in preview.movements" :key="m.id">
                      <td class="px-3 py-2.5 text-zinc-400 capitalize">{{ m.type.replace('_', ' ') }}</td>
                      <td class="px-3 py-2.5 text-zinc-300">{{ m.reason || '-' }}</td>
                      <td class="px-3 py-2.5 text-right font-semibold" :class="m.type === 'cash_in' ? 'text-emerald-400' : 'text-rose-400'">{{ fmt(m.amount) }}</td>
                      <td class="px-3 py-2.5 text-zinc-500">{{ m.user?.name || '-' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </section>

            <!-- 3. Payment Summary -->
            <section class="bg-zinc-800/50 rounded-2xl border border-white/5 p-6">
              <h4 class="text-2xl font-bold text-zinc-200 mb-4 flex items-center gap-2">
                <i class="ri-bank-card-line text-amber-400"></i> Payment Summary
              </h4>
              <div class="grid grid-cols-2 sm:grid-cols-5 gap-5 text-xl">
                <div v-for="b in paymentBuckets" :key="b.key" class="bg-zinc-900/60 rounded-xl p-5">
                  <p class="text-zinc-500">{{ b.label }}</p>
                  <p class="text-white font-bold text-2xl">{{ fmt(preview[b.field]) }}</p>
                  <p class="text-zinc-600 text-lg">{{ preview.sales_count?.[b.key] ?? 0 }} txns</p>
                </div>
              </div>
            </section>

            <!-- 4. Cash Count (denominations) -->
            <section class="bg-zinc-800/50 rounded-2xl border border-white/5 p-6">
              <div class="flex items-center justify-between mb-4">
                <h4 class="text-2xl font-bold text-zinc-200 flex items-center gap-2">
                  <i class="ri-coins-line text-amber-400"></i> Cash Count <span class="text-lg font-normal text-zinc-500">(optional)</span>
                </h4>
                <button @click="showDenominations = !showDenominations" class="text-amber-300 text-xl font-semibold">
                  {{ showDenominations ? 'Hide' : 'Count Cash' }}
                </button>
              </div>
              <div v-if="showDenominations" class="space-y-3">
                <div v-for="d in denominations" :key="d.key" class="flex items-center gap-4 text-xl">
                  <span class="w-32 text-zinc-300 font-semibold">{{ d.label }}</span>
                  <template v-if="!d.isCoins">
                    <span class="text-zinc-500 text-xl">×</span>
                    <input v-model.number="d.qty" type="number" min="0" step="1" placeholder="0"
                      class="h-14 w-28 px-3 text-xl bg-zinc-800 border border-white/10 rounded-lg text-white text-center" />
                    <span class="text-zinc-500 text-xl">=</span>
                    <span class="w-32 text-right text-emerald-400 font-semibold">{{ fmt(d.qty * d.value) }}</span>
                  </template>
                  <template v-else>
                    <input v-model.number="d.qty" type="number" min="0" step="0.01" placeholder="Total coin amount"
                      class="h-14 flex-1 px-3 text-xl bg-zinc-800 border border-white/10 rounded-lg text-white" />
                  </template>
                </div>
                <div class="flex items-center justify-between pt-4 border-t border-white/10">
                  <span class="text-zinc-300 font-bold text-2xl">Total Counted Cash</span>
                  <span class="text-4xl font-bold text-amber-300">{{ fmt(denominationTotal) }} LKR</span>
                </div>
                <button @click="useDenominationTotal" class="w-full h-14 rounded-xl bg-zinc-700 hover:bg-zinc-600 text-white font-semibold text-xl transition">
                  Use as Actual Closing Cash
                </button>
              </div>
            </section>

            <!-- 5. Expenses -->
            <section class="bg-zinc-800/50 rounded-2xl border border-white/5 p-6">
              <div class="flex items-center justify-between mb-4">
                <h4 class="text-2xl font-bold text-zinc-200 flex items-center gap-2">
                  <i class="ri-receipt-line text-amber-400"></i> Expenses
                </h4>
                <button @click="$emit('open-expense-modal')" class="text-amber-300 text-xl font-semibold">+ Record Expense</button>
              </div>
              <div class="flex gap-8 text-xl mb-4">
                <p class="text-zinc-400">Cash: <span class="text-rose-400 font-bold">{{ fmt(preview.cash_expenses) }}</span></p>
                <p class="text-zinc-400">Total (all methods): <span class="text-white font-bold">{{ fmt(preview.total_expenses) }}</span></p>
              </div>
              <div v-if="preview.expenses?.length" class="max-h-40 overflow-y-auto rounded-xl border border-white/5">
                <table class="w-full text-lg">
                  <tbody class="divide-y divide-white/5">
                    <tr v-for="e in preview.expenses" :key="e.id">
                      <td class="px-3 py-2.5 text-zinc-300">{{ e.reason }}</td>
                      <td class="px-3 py-2.5 text-zinc-500">{{ e.category || '-' }}</td>
                      <td class="px-3 py-2.5 text-zinc-500">{{ e.payment_method }}</td>
                      <td class="px-3 py-2.5 text-right font-semibold text-rose-400">{{ fmt(e.amount) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <p v-else class="text-zinc-500 text-xl">No expenses recorded this session.</p>
            </section>

            <!-- 6. Reconciliation -->
            <section class="bg-gradient-to-br from-zinc-800 to-zinc-800/60 rounded-2xl border border-amber-500/20 p-6">
              <h4 class="text-2xl font-bold text-zinc-200 mb-5 flex items-center gap-2">
                <i class="ri-scales-3-line text-amber-400"></i> Reconciliation
              </h4>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-zinc-900/60 rounded-xl p-6 text-center">
                  <p class="text-zinc-400 text-xl mb-2">Expected Cash</p>
                  <p class="text-5xl font-bold text-white">{{ fmt(preview.expected_cash) }}</p>
                </div>
                <div class="bg-zinc-900/60 rounded-xl p-6">
                  <label class="block text-zinc-400 text-xl mb-3 text-center">Actual Closing Cash</label>
                  <input v-model="actualClosingCash" type="number" min="0" step="0.01" placeholder="0.00" inputmode="decimal"
                    class="w-full h-20 px-4 text-4xl text-center bg-zinc-800 border border-white/10 rounded-xl text-white placeholder-zinc-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition font-bold" />
                </div>
                <div class="rounded-xl p-6 text-center" :class="differenceBoxClass">
                  <p class="text-xl mb-2 opacity-80">Difference</p>
                  <p class="text-5xl font-bold">{{ fmt(Math.abs(difference)) }}</p>
                  <p class="text-xl font-bold uppercase tracking-wide mt-2">{{ differenceLabel }}</p>
                </div>
              </div>
              <p v-if="overThreshold" class="mt-4 text-amber-300 bg-amber-500/10 border border-amber-500/30 rounded-xl px-5 py-4 text-xl">
                <i class="ri-alert-line mr-1"></i>
                This difference exceeds the Rs. {{ fmt(preview.variance_threshold) }} threshold — closing notes are required, and this drawer will be flagged for manager/admin approval.
              </p>
            </section>

            <!-- 7. Closing Notes -->
            <section class="bg-zinc-800/50 rounded-2xl border border-white/5 p-6">
              <label class="block text-2xl font-bold text-zinc-200 mb-3 flex items-center gap-2">
                <i class="ri-sticky-note-line text-amber-400"></i> Closing Notes
                <span v-if="overThreshold" class="text-rose-400 font-normal text-xl">(required)</span>
              </label>
              <textarea v-model="closingNotes" rows="3" placeholder="e.g. Customer refund was paid but not recorded."
                class="w-full px-4 py-3 text-xl bg-zinc-800 border rounded-xl text-white placeholder-zinc-500 focus:outline-none focus:ring-2 transition"
                :class="overThreshold && !closingNotes ? 'border-rose-500 focus:ring-rose-500/40' : 'border-white/10 focus:ring-amber-500/30'"></textarea>
              <p v-if="closeError" class="mt-2 text-rose-400 text-xl">{{ closeError }}</p>
            </section>
          </template>

          <!-- ============ CLOSING REPORT VIEW ============ -->
          <template v-else-if="view === 'report' && closedDrawer">
            <section class="bg-zinc-800/50 rounded-2xl border border-white/5 p-6 space-y-4">
              <div class="text-center py-4">
                <div class="w-20 h-20 mx-auto flex items-center justify-center rounded-full mb-3"
                  :class="closedDrawer.variance_status === 'balanced' ? 'bg-emerald-500/20' : (closedDrawer.variance_status === 'over' ? 'bg-sky-500/20' : 'bg-rose-500/20')">
                  <i class="ri-checkbox-circle-line text-4xl"
                    :class="closedDrawer.variance_status === 'balanced' ? 'text-emerald-400' : (closedDrawer.variance_status === 'over' ? 'text-sky-400' : 'text-rose-400')"></i>
                </div>
                <p class="text-3xl font-bold text-white">Drawer Closed</p>
                <p class="text-xl text-zinc-400 mt-2">{{ statusLabel(closedDrawer.variance_status) }} — {{ fmt(Math.abs(closedDrawer.variance)) }} LKR</p>
                <p v-if="closedDrawer.requires_approval" class="mt-3 inline-block px-4 py-1.5 rounded-full bg-amber-500/15 text-amber-300 text-lg font-bold uppercase">Pending Manager Approval</p>
              </div>

              <div class="grid grid-cols-2 sm:grid-cols-4 gap-5 text-xl">
                <div><p class="text-zinc-500">Opening</p><p class="text-white font-bold text-2xl">{{ fmt(closedDrawer.opening_balance) }}</p></div>
                <div><p class="text-zinc-500">Cash Sales</p><p class="text-white font-bold text-2xl">{{ fmt(closedDrawer.cash_sales) }}</p></div>
                <div><p class="text-zinc-500">Expected</p><p class="text-white font-bold text-2xl">{{ fmt(closedDrawer.expected_cash) }}</p></div>
                <div><p class="text-zinc-500">Actual</p><p class="text-white font-bold text-2xl">{{ fmt(closedDrawer.closing_balance) }}</p></div>
              </div>
            </section>

            <div class="flex gap-4">
              <button @click="printReceipt80" class="flex-1 h-16 rounded-xl bg-zinc-700 hover:bg-zinc-600 text-white font-semibold text-xl transition flex items-center justify-center gap-2">
                <i class="ri-printer-line"></i> Print Receipt (80mm)
              </button>
              <button @click="printReportA4" class="flex-1 h-16 rounded-xl bg-zinc-700 hover:bg-zinc-600 text-white font-semibold text-xl transition flex items-center justify-center gap-2">
                <i class="ri-file-text-line"></i> Print Report (A4)
              </button>
            </div>
          </template>
        </div>

        <!-- Footer -->
        <div class="px-8 py-6 bg-zinc-800/30 border-t border-white/10 flex gap-4 flex-shrink-0">
          <template v-if="view === 'form'">
            <button @click="handleClose" class="flex-1 h-16 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-semibold text-2xl transition">
              Cancel
            </button>
            <button @click="submitClose" :disabled="isClosing || loadingPreview"
              class="flex-1 h-16 rounded-xl bg-rose-500 hover:bg-rose-400 text-white font-bold text-2xl transition disabled:opacity-60 disabled:cursor-not-allowed">
              {{ isClosing ? 'Closing...' : 'Close Drawer' }}
            </button>
          </template>
          <template v-else>
            <button @click="finish" class="flex-1 h-16 rounded-xl bg-amber-500 hover:bg-amber-400 text-zinc-900 font-bold text-2xl transition">
              Done
            </button>
          </template>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import { printHtmlInIframe } from "@/Utils/print.js";

const props = defineProps({
  open: { type: Boolean, default: false },
  cashDrawer: { type: Object, default: null },
  loggedInUser: { type: Object, default: null },
});

const emit = defineEmits(["update:open", "closed", "open-expense-modal"]);

const page = usePage();
const companyInfo = computed(() => page.props.companyInfo);

const view = ref("form");
const preview = ref(null);
const loadingPreview = ref(false);
const previewError = ref("");
const closedDrawer = ref(null);

const movementType = ref("cash_in");
const movementAmount = ref("");
const movementReason = ref("");
const movementSubmitting = ref(false);
const movementError = ref("");

const showDenominations = ref(false);
const denominations = ref([
  { key: "5000", label: "Rs. 5,000", value: 5000, qty: 0 },
  { key: "2000", label: "Rs. 2,000", value: 2000, qty: 0 },
  { key: "1000", label: "Rs. 1,000", value: 1000, qty: 0 },
  { key: "500", label: "Rs. 500", value: 500, qty: 0 },
  { key: "100", label: "Rs. 100", value: 100, qty: 0 },
  { key: "50", label: "Rs. 50", value: 50, qty: 0 },
  { key: "20", label: "Rs. 20", value: 20, qty: 0 },
  { key: "10", label: "Rs. 10", value: 10, qty: 0 },
  { key: "coins", label: "Coins", value: null, qty: 0, isCoins: true },
]);

const actualClosingCash = ref("");
const closingNotes = ref("");
const closeError = ref("");
const isClosing = ref(false);

const paymentBuckets = [
  { key: "cash", label: "Cash", field: "cash_sales" },
  { key: "card", label: "Card", field: "card_sales" },
  { key: "qr", label: "QR / Online", field: "qr_sales" },
  { key: "bank_transfer", label: "Bank Transfer", field: "bank_transfer_sales" },
  { key: "other", label: "Other", field: "other_sales" },
];

const fmt = (val) => Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const fmtDate = (val) => {
  if (!val) return "-";
  const d = new Date(val);
  return isNaN(d.getTime()) ? "-" : d.toLocaleString();
};

const denominationTotal = computed(() =>
  denominations.value.reduce((sum, d) => sum + (d.isCoins ? Number(d.qty || 0) : Number(d.qty || 0) * d.value), 0)
);

const useDenominationTotal = () => {
  actualClosingCash.value = denominationTotal.value.toFixed(2);
};

const difference = computed(() => {
  if (!preview.value) return 0;
  const actual = parseFloat(actualClosingCash.value || 0);
  return actual - Number(preview.value.expected_cash || 0);
});

const overThreshold = computed(() => {
  if (!preview.value) return false;
  return Math.abs(difference.value) > Number(preview.value.variance_threshold || 0);
});

const differenceLabel = computed(() => {
  if (Math.abs(difference.value) < 0.01) return "Balanced";
  return difference.value > 0 ? "Cash Over" : "Cash Short";
});

const differenceBoxClass = computed(() => {
  if (Math.abs(difference.value) < 0.01) return "bg-emerald-500/15 text-emerald-300";
  return difference.value > 0 ? "bg-sky-500/15 text-sky-300" : "bg-rose-500/15 text-rose-300";
});

const statusLabel = (status) => ({ balanced: "Balanced", over: "Cash Over", short: "Cash Short" }[status] || status);

const resetFormState = () => {
  view.value = "form";
  closedDrawer.value = null;
  actualClosingCash.value = "";
  closingNotes.value = "";
  closeError.value = "";
  showDenominations.value = false;
  denominations.value.forEach((d) => (d.qty = 0));
  movementType.value = "cash_in";
  movementAmount.value = "";
  movementReason.value = "";
  movementError.value = "";
};

const fetchPreview = async () => {
  if (!props.cashDrawer?.id) return;
  loadingPreview.value = true;
  previewError.value = "";
  try {
    const { data } = await axios.get(`/cash-drawer/${props.cashDrawer.id}/preview`);
    preview.value = data;
  } catch (err) {
    previewError.value = err.response?.data?.message || "Failed to load drawer summary.";
  } finally {
    loadingPreview.value = false;
  }
};

defineExpose({ refreshPreview: fetchPreview });

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      resetFormState();
      fetchPreview();
    }
  }
);

const addMovement = async () => {
  movementError.value = "";
  const amount = parseFloat(movementAmount.value || "");
  if (!amount || amount <= 0) {
    movementError.value = "Enter a valid amount.";
    return;
  }
  if (movementType.value !== "cash_in" && !movementReason.value.trim()) {
    movementError.value = "Reason is required for cash out / cash drop.";
    return;
  }
  movementSubmitting.value = true;
  try {
    await axios.post("/cash-movements", {
      type: movementType.value,
      amount,
      reason: movementReason.value || null,
    });
    movementAmount.value = "";
    movementReason.value = "";
    await fetchPreview();
  } catch (err) {
    movementError.value = err.response?.data?.message || "Failed to record movement.";
  } finally {
    movementSubmitting.value = false;
  }
};

const submitClose = async () => {
  closeError.value = "";
  if (!props.cashDrawer?.id) return;

  const raw = String(actualClosingCash.value ?? "").trim();
  if (!raw) {
    closeError.value = "Actual closing cash is required.";
    return;
  }
  const amount = parseFloat(raw);
  if (Number.isNaN(amount) || amount < 0) {
    closeError.value = "Enter a valid amount (0 or more).";
    return;
  }

  const breakdown = {};
  denominations.value.forEach((d) => {
    if (Number(d.qty) > 0) breakdown[d.key] = d.qty;
  });

  isClosing.value = true;
  try {
    const { data } = await axios.post(`/cash-drawer/${props.cashDrawer.id}`, {
      _method: "PUT",
      closing_balance: amount,
      denomination_breakdown: Object.keys(breakdown).length ? breakdown : null,
      closing_notes: closingNotes.value || null,
    });
    closedDrawer.value = data.cashDrawer;
    view.value = "report";
    emit("closed", data.cashDrawer);
  } catch (err) {
    closeError.value = err.response?.data?.message || "Failed to close cash drawer.";
  } finally {
    isClosing.value = false;
  }
};

const handleBackdropClick = () => {
  if (view.value === "report") {
    finish();
  }
};

const handleClose = () => {
  emit("update:open", false);
};

const finish = () => {
  emit("update:open", false);
};

const buildPrintHeaderHtml = () => `
  ${companyInfo.value?.name ? `<h1>${companyInfo.value.name}</h1>` : "<h1>CASH DRAWER REPORT</h1>"}
  ${companyInfo.value?.address ? `<p class="sub">${companyInfo.value.address}</p>` : ""}
`;

const printReceipt80 = () => {
  const d = closedDrawer.value;
  if (!d) return;
  const html = `<!doctype html><html><head><meta charset="utf-8" /><title>Cash Drawer Receipt</title>
  <style>
    @page { size: 80mm auto; margin: 0; }
    body { font-family: 'Courier New', monospace; font-size: 13px; width: 80mm; margin: 0; padding: 10px 8px; color:#000; }
    h1 { text-align:center; font-size:18px; margin:0 0 4px; }
    .sub { text-align:center; font-size:11px; margin:0 0 8px; }
    .row { display:flex; justify-content:space-between; padding:2px 0; }
    .divider { border-top: 1px dashed #000; margin: 6px 0; }
    .divider-solid { border-top: 2px solid #000; margin: 6px 0; }
    .grand { font-weight:900; font-size:15px; border-top:2px solid #000; border-bottom:2px solid #000; padding:5px 0; margin:6px 0; }
    .footer { text-align:center; font-size:11px; margin-top:10px; }
  </style></head><body>
    ${buildPrintHeaderHtml()}
    <div class="row"><span>Drawer #</span><span>${d.id}</span></div>
    <div class="row"><span>Cashier</span><span>${escapeHtml(d.closedByUser?.name || loggedInUser?.name || '-')}</span></div>
    <div class="row"><span>Opened</span><span>${fmtDate(d.opened_at)}</span></div>
    <div class="row"><span>Closed</span><span>${fmtDate(d.closed_at)}</span></div>
    <div class="divider-solid"></div>
    <div class="row"><span>Opening Balance</span><span>${fmt(d.opening_balance)}</span></div>
    <div class="row"><span>Cash Sales</span><span>${fmt(d.cash_sales)}</span></div>
    <div class="row"><span>Card Sales</span><span>${fmt(d.card_sales)}</span></div>
    <div class="row"><span>QR Sales</span><span>${fmt(d.qr_sales)}</span></div>
    <div class="row"><span>Bank Transfer</span><span>${fmt(d.bank_transfer_sales)}</span></div>
    <div class="row"><span>Cash In</span><span>${fmt(d.cash_in)}</span></div>
    <div class="row"><span>Cash Out</span><span>${fmt(d.cash_out)}</span></div>
    <div class="row"><span>Cash Drops</span><span>${fmt(d.cash_drops)}</span></div>
    <div class="row"><span>Cash Expenses</span><span>${fmt(d.cash_expenses)}</span></div>
    <div class="row"><span>Cash Refunds</span><span>${fmt(d.cash_refunds)}</span></div>
    <div class="divider"></div>
    <div class="row"><span>Expected Cash</span><span>${fmt(d.expected_cash)}</span></div>
    <div class="row"><span>Actual Cash</span><span>${fmt(d.closing_balance)}</span></div>
    <div class="grand"><span>${statusLabel(d.variance_status)}</span><span>${fmt(Math.abs(d.variance))}</span></div>
    ${d.closing_notes ? `<div class="row"><span>Notes:</span></div><p>${escapeHtml(d.closing_notes)}</p>` : ""}
    ${d.requires_approval ? `<p style="text-align:center;font-weight:700;">PENDING MANAGER APPROVAL</p>` : ""}
    <div class="footer">${new Date().toLocaleString()}</div>
  </body></html>`;
  printHtmlInIframe(html);
};

const printReportA4 = () => {
  const d = closedDrawer.value;
  if (!d) return;
  const rows = [
    ["Drawer #", d.id],
    ["Cashier (Opened)", d.openedByUser?.name || "-"],
    ["Cashier (Closed)", d.closedByUser?.name || loggedInUser?.name || "-"],
    ["Opened At", fmtDate(d.opened_at)],
    ["Closed At", fmtDate(d.closed_at)],
    ["Opening Balance", fmt(d.opening_balance)],
    ["Cash Sales", fmt(d.cash_sales)],
    ["Card Sales", fmt(d.card_sales)],
    ["QR / Online Sales", fmt(d.qr_sales)],
    ["Bank Transfer Sales", fmt(d.bank_transfer_sales)],
    ["Other Sales", fmt(d.other_sales)],
    ["Cash In", fmt(d.cash_in)],
    ["Cash Out", fmt(d.cash_out)],
    ["Cash Drops", fmt(d.cash_drops)],
    ["Cash Expenses", fmt(d.cash_expenses)],
    ["Total Expenses", fmt(d.total_expenses)],
    ["Cash Refunds", fmt(d.cash_refunds)],
    ["Total Refunds", fmt(d.total_refunds)],
    ["Expected Cash", fmt(d.expected_cash)],
    ["Actual Closing Cash", fmt(d.closing_balance)],
    ["Difference", `${statusLabel(d.variance_status)} — ${fmt(Math.abs(d.variance))}`],
    ["Closing Notes", d.closing_notes || "-"],
    ["Approval", d.requires_approval ? (d.approved_at ? `Approved by ${d.approvedByUser?.name || ''} on ${fmtDate(d.approved_at)}` : "Pending Approval") : "Not Required"],
  ];

  const html = `<!doctype html><html><head><meta charset="utf-8" /><title>Cash Drawer Report</title>
  <style>
    @page { size: A4; margin: 15mm; }
    body { font-family: Arial, sans-serif; color:#000; }
    h1 { text-align:center; margin-bottom:4px; }
    .sub { text-align:center; color:#555; margin-top:0; margin-bottom:20px; }
    table { width:100%; border-collapse: collapse; }
    td { padding:8px 10px; border-bottom:1px solid #ddd; font-size:14px; }
    td:first-child { font-weight:700; width:40%; }
  </style></head><body>
    ${buildPrintHeaderHtml()}
    <table>
      ${rows.map(([label, value]) => `<tr><td>${escapeHtml(label)}</td><td>${escapeHtml(String(value))}</td></tr>`).join("")}
    </table>
  </body></html>`;
  printHtmlInIframe(html);
};

const escapeHtml = (value) =>
  String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
</script>
