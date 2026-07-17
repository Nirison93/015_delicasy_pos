<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="handleClose"></div>
        <div class="relative bg-zinc-900 rounded-2xl border border-white/10 shadow-2xl w-full max-w-[400px] overflow-hidden">
          <!-- Header -->
          <div class="px-5 py-4 border-b border-white/10 bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900">
            <div class="flex items-center gap-3">
              <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/20 ring-1 ring-amber-500/40">
                <i class="ri-calculator-line text-amber-400"></i>
              </div>
              <h3 class="text-lg font-bold text-white">Enter Amount</h3>
            </div>
          </div>

          <!-- Display -->
          <div class="px-5 py-6 border-b border-white/10 bg-zinc-950/30">
            <p class="text-sm text-zinc-500 mb-2">Amount (LKR)</p>
            <p class="text-4xl font-bold text-amber-400 break-words">
              {{ displayValue || '0.00' }}
            </p>
          </div>

          <!-- Keypad -->
          <div class="p-5 space-y-3">
            <!-- Number Grid (4x3) -->
            <div class="grid grid-cols-3 gap-2">
              <button
                v-for="num in [1, 2, 3, 4, 5, 6, 7, 8, 9]"
                :key="num"
                @click="appendNumber(num)"
                @keydown.prevent
                class="h-16 rounded-lg bg-zinc-800 hover:bg-zinc-700 active:bg-zinc-600 border border-white/10 text-white font-bold text-2xl transition">
                {{ num }}
              </button>
            </div>

            <!-- Bottom Row: 0, Decimal, Backspace -->
            <div class="grid grid-cols-3 gap-2">
              <button
                @click="appendNumber(0)"
                class="h-16 rounded-lg bg-zinc-800 hover:bg-zinc-700 active:bg-zinc-600 border border-white/10 text-white font-bold text-2xl transition">
                0
              </button>
              <button
                @click="appendDecimal"
                class="h-16 rounded-lg bg-zinc-800 hover:bg-zinc-700 active:bg-zinc-600 border border-white/10 text-white font-bold text-2xl transition">
                .
              </button>
              <button
                @click="backspace"
                class="h-16 rounded-lg bg-red-500/20 hover:bg-red-500/30 active:bg-red-500/40 border border-red-500/30 text-red-400 font-bold text-2xl transition">
                <i class="ri-delete-back-line text-2xl"></i>
              </button>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-2 pt-3 border-t border-white/10">
              <button
                @click="handleClose"
                class="h-14 rounded-lg bg-zinc-800 hover:bg-zinc-700 active:scale-95 border border-white/10 text-zinc-300 font-bold text-lg transition">
                Cancel
              </button>
              <button
                @click="handleConfirm"
                class="h-14 rounded-lg bg-green-500 hover:bg-green-600 active:scale-95 text-white font-bold text-lg transition">
                Confirm
              </button>
            </div>

            <!-- Clear Button (full width) -->
            <button
              @click="clearInput"
              class="w-full h-12 rounded-lg bg-zinc-700 hover:bg-zinc-600 active:scale-95 text-zinc-300 font-semibold transition text-sm">
              Clear All
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  open: {
    type: Boolean,
    default: false
  },
  value: {
    type: [String, Number],
    default: ''
  }
});

const emit = defineEmits(['update:open', 'confirm']);

const displayValue = ref('');

onMounted(() => {
  document.addEventListener('keydown', handleKeyPress);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeyPress);
});

watch(() => props.open, (isOpen) => {
  if (isOpen && props.value) {
    const val = Number(props.value);
    displayValue.value = val > 0 ? String(val) : '';
  }
});

watch(() => props.value, (newVal) => {
  if (newVal && Number(newVal) > 0) {
    displayValue.value = String(newVal);
  }
});

const appendNumber = (num) => {
  if (displayValue.value === '' || displayValue.value === '0') {
    displayValue.value = String(num);
  } else {
    displayValue.value += String(num);
  }
};

const appendDecimal = () => {
  if (displayValue.value === '') {
    displayValue.value = '0.';
  } else if (!displayValue.value.includes('.')) {
    displayValue.value += '.';
  }
};

const backspace = () => {
  if (displayValue.value.length > 1) {
    displayValue.value = displayValue.value.slice(0, -1);
  } else {
    displayValue.value = '';
  }
};

const clearInput = () => {
  displayValue.value = '';
};

const handleKeyPress = (e) => {
  if (!props.open) return;

  // Numbers 0-9
  if (e.key >= '0' && e.key <= '9') {
    e.preventDefault();
    appendNumber(parseInt(e.key));
  }
  // Decimal point
  else if (e.key === '.' || e.key === ',') {
    e.preventDefault();
    appendDecimal();
  }
  // Backspace
  else if (e.key === 'Backspace') {
    e.preventDefault();
    backspace();
  }
  // Enter to confirm
  else if (e.key === 'Enter') {
    e.preventDefault();
    handleConfirm();
  }
  // Escape to close
  else if (e.key === 'Escape') {
    e.preventDefault();
    handleClose();
  }
};

const handleConfirm = () => {
  const value = displayValue.value.trim();
  if (value) {
    emit('confirm', parseFloat(value) || 0);
  }
  emit('update:open', false);
};

const handleClose = () => {
  emit('update:open', false);
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
