<template>
  <TransitionRoot as="template" :show="open" static>
    <Dialog class="relative z-10" static>
      <!-- Modal Overlay -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div
          class="fixed inset-0 bg-black/75 backdrop-blur-sm transition-opacity"
          @click.stop
        />
      </TransitionChild>

      <!-- Modal Content -->
      <div class="fixed inset-0 z-10 flex items-center justify-center">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel
            class="bg-zinc-900 border border-white/10 rounded-3xl shadow-2xl max-w-xl w-full overflow-hidden"
          >
            <!-- Header -->
            <div class="bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900 border-b border-white/10 px-8 py-6 text-center">
              <div class="flex items-center justify-center gap-3 mb-1">
                <div class="w-12 h-12 flex items-center justify-center rounded-2xl bg-emerald-500/20 ring-1 ring-emerald-500/40">
                  <i class="ri-check-double-line text-emerald-400 text-2xl"></i>
                </div>
              </div>
              <DialogTitle class="text-5xl font-bold text-white mt-3">Payment Successful!</DialogTitle>
            </div>

            <!-- Body -->
            <div class="px-8 py-8 flex flex-col items-center space-y-6">
              <p class="text-3xl text-zinc-200 text-center font-medium">
                Order Payment is Successful!
              </p>
              <div class="w-28 h-28 flex items-center justify-center rounded-full bg-emerald-500/15 ring-2 ring-emerald-500/30">
                <img
                  src="/images/checked.png"
                  class="h-16 w-16 object-contain"
                />
              </div>
            </div>

            <!-- Footer Buttons -->
            <div class="px-8 pb-8 flex justify-center items-center gap-4">
              <p
                @click="handlePrintReceipt"
                class="cursor-pointer bg-amber-500 hover:bg-amber-400 text-zinc-900 font-bold uppercase tracking-wider px-6 py-4 rounded-2xl shadow-lg shadow-amber-500/20 active:scale-95 transition"
              >
                Print Receipt
              </p>
              <p
                @click="$emit('update:open', false)"
                class="cursor-pointer bg-zinc-800 hover:bg-zinc-700 border border-white/10 text-zinc-300 hover:text-white font-bold uppercase tracking-wider px-6 py-4 rounded-2xl active:scale-95 transition"
              >
                Close
              </p>
            </div>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { computed } from "vue";
import { ref } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";

const page = usePage();

// Access the companyInfo from the page props
const companyInfo = computed(() => page.props.companyInfo);

if (companyInfo.value) {
  console.log(companyInfo.value);
} else {
  console.log("companyInfo is undefined or null");
}

const handleClose = () => {
  console.log("Modal close prevented");
};

const emit = defineEmits(["update:open"]);

// The `open` prop controls the visibility of the modal
const props = defineProps({
  open: {
    type: Boolean,
    required: true,
  },
  products: {
    type: Array,
    required: true,
  },
  selectedTable: {
    type: Object,
    required: true,
  },
  cashier: Object,
  customer: Object,
  orderId: String,
  balance: Number,
  cash: Number,
  subTotal: String,
  totalDiscount: String,
  total: String,
  custom_discount: Number,
  custom_discount_type: String,
  kitchen_note: String,
  delivery_charge : String,
  bank_service_charge : String,
  service_charge : String,
  order_type : String,
  selectedPaymentMethod : String,
  shopping_bag_charge: {
    type: Number,
    default: 0
  },

   owner_discount_value: {
    type: Number,
    default: 0
  },
  owner_code: {
    type: String,
    default: null
  }
});

const handlePrintReceipt = () => {
  // Calculate totals from props.products
  const subTotal = props.products.reduce(
    (sum, product) =>
      sum + parseFloat(product.selling_price) * product.quantity,
    0
  );
  const customDiscount = Number(props.custom_discount || 0);
  const totalDiscount = props.products
    .reduce((total, item) => {
      // Check if item has a discount
      if (item.discount && item.discount > 0 && item.apply_discount == true) {
        const discountAmount =
          (parseFloat(item.selling_price) - parseFloat(item.discounted_price)) *
          item.quantity;
        return total + discountAmount;
      }
      return total; // If no discount, return total as-is
    }, 0)
    .toFixed(2); // Ensures two decimal places

  const discount = 0; // Example discount (can be dynamic)
  const total = subTotal - totalDiscount - customDiscount;

  // Generate table rows dynamically using props.products
  const productRows = props.products
    .map((product) => {
      // Determine the price based on discount
      const price =
        product.discount > 0 && product.apply_discount
          ? product.discounted_price // Use discounted price if discount is applied
          : product.selling_price; // Use selling price if no discount

        const Price =
        product.discount > 0 && product.apply_discount
          ? product.discounted_price * product.quantity
          : product.selling_price * product.quantity;

      return `
        <tr>
          <td class="name" colspan="3" style="padding:4px 2px 1px;">${product.name}${product.size?.name ? ` (${product.size.name})` : ""}</td>
        </tr>
        <tr style="border-bottom: 1px dashed #aaa;">
          <td></td>
          <td class="pqty">
            ${product.selling_price} \u00d7 ${product.quantity}
            ${product.discount > 0 && product.apply_discount
              ? `<div class="discount-badge">${product.discount}% OFF</div>`
              : ""}
          </td>
          <td class="ptotal">
            ${product.discount > 0 && product.apply_discount
              ? (product.selling_price * product.quantity * (1 - product.discount / 100)).toFixed(2)
              : (product.selling_price * product.quantity).toFixed(2)}
          </td>
        </tr>
      `;
    })
    .join("");

  // Generate the receipt HTML
  const receiptHTML = `
  <!DOCTYPE html>
  <html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Receipt</title>
      <style>
          @page { size: 80mm auto; margin: 0; }
          @media print {
              body { margin: 0; padding: 0; -webkit-print-color-adjust: exact; }
          }
          * { box-sizing: border-box; }
          body {
              background: #fff;
              font-family: 'Arial', sans-serif;
              font-size: 13px;
              color: #000;
              margin: 0;
              padding: 8px 10px 16px;
              width: 80mm;
          }
          .header { text-align: center; padding-bottom: 8px; margin-bottom: 8px; border-bottom: 2px solid #000; }
          .header h1 { font-size: 18px; font-weight: 900; margin: 0 0 3px; letter-spacing: 0.5px; }
          .header p { font-size: 12px; margin: 2px 0; }
          .order-type {
              font-size: 13px; font-weight: 800; text-align: center;
              border: 2px solid #000; border-radius: 4px;
              padding: 4px 0; margin: 8px 0;
              letter-spacing: 0.5px; text-transform: uppercase;
          }
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
          .items td.name { font-weight: 700; font-size: 13px; }
          .items td.pqty { text-align: center; font-size: 12px; }
          .items td.ptotal { text-align: right; font-weight: 700; font-size: 13px; }
          .discount-badge {
              display: inline-block; background: #000; color: #fff;
              font-size: 10px; font-weight: 700; padding: 0 4px;
              border-radius: 3px; margin-top: 2px;
          }
          .totals { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 4px; }
          .totals td { padding: 3px 0; }
          .totals td:last-child { text-align: right; }
          .totals .grand td {
              font-size: 15px; font-weight: 900;
              border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 5px 0;
          }
          .totals .bold td { font-weight: 700; }
          .kitchen-note {
              font-size: 12px; font-weight: 700;
              border-top: 1px dashed #555; border-bottom: 1px dashed #555;
              padding: 5px 0; margin: 8px 0;
          }
          .footer { text-align: center; margin-top: 10px; }
          .footer .no-refund { font-size: 13px; font-weight: 800; letter-spacing: 0.3px; margin: 6px 0; }
          .footer .thank-you { font-size: 14px; font-weight: 900; letter-spacing: 0.5px; margin: 4px 0; text-transform: uppercase; }
          .footer .powered { font-size: 11px; margin-top: 6px; color: #444; }
      </style>
  </head>
  <body>
      <!-- Header -->
      <div class="header">
          ${ companyInfo?.value?.name ? `<img src="/images/delicasy_logo.png" alt="${companyInfo.value.name}" style="max-width: 70mm; max-height: 50px; margin: 0 auto; display: block;">` : "" }
          ${ companyInfo?.value?.address ? `<p>${companyInfo.value.address}</p>` : "" }
          ${ (companyInfo?.value?.phone || companyInfo?.value?.phone2) ? `<p>${[companyInfo.value.phone, companyInfo.value.phone2].filter(Boolean).join(" | ")}</p>` : "" }
          ${ companyInfo?.value?.email ? `<p>${companyInfo.value.email}</p>` : "" }
      </div>

      <!-- Order type badge -->
      <div class="order-type">
          ${ props.order_type === 'takeaway' ? 'Takeaway' : props.order_type === 'pickup' ? 'Delivery' : 'Dine In' }
      </div>

      <!-- Order meta -->
      <table class="meta">
          <tr><td>Date &amp; Time:</td><td>${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}</td></tr>
          <tr><td>Order No:</td><td>${props.orderId}</td></tr>
          <tr><td>Customer:</td><td>${props.customer?.name || 'Walking Customer'}</td></tr>
          <tr><td>Cashier:</td><td>${props.cashier.name}</td></tr>
          <tr><td>Payment:</td><td>${props.selectedPaymentMethod}</td></tr>
      </table>

      <hr class="divider-solid" />

      <!-- Items -->
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

      <!-- Totals -->
      <table class="totals">
          ${Number(props.subTotal) !== Number(props.total) && Number(props.subTotal) !== 0
              ? `<tr><td>Sub Total</td><td>${(Number(props.subTotal)||0).toFixed(2)} LKR</td></tr>` : ""}
          ${Number(props.totalDiscount) !== 0
              ? `<tr><td>Discount</td><td>(${(Number(props.totalDiscount)||0).toFixed(2)}) LKR</td></tr>` : ""}
          ${Number(props.owner_discount_value) !== 0
              ? `<tr><td>Owner Discount${props.owner_code ? ` (${props.owner_code})` : ""}</td><td>(${(Number(props.owner_discount_value)||0).toFixed(2)}) LKR</td></tr>` : ""}
          ${Number(props.custom_discount) !== 0
              ? `<tr><td>Customer Discount</td><td>(${(Number(props.custom_discount)||0).toFixed(2)}) LKR</td></tr>` : ""}
          ${props.delivery_charge
              ? `<tr><td>Delivery Charge</td><td>${(Number(props.delivery_charge)||0).toFixed(2)} LKR</td></tr>` : ""}
          ${props.service_charge
              ? `<tr><td>Service Charge</td><td>${(Number(props.service_charge)||0).toFixed(2)} %</td></tr>` : ""}
          ${props.bank_service_charge
              ? `<tr><td>Bank Service Charge</td><td>${(Number(props.bank_service_charge)||0).toFixed(2)} %</td></tr>` : ""}
          ${Number(props.shopping_bag_charge) !== 0
              ? `<tr><td>Shopping Bag</td><td>${(Number(props.shopping_bag_charge)||0).toFixed(2)} LKR</td></tr>` : ""}
          ${Number(props.total) !== 0
              ? `<tr class="grand"><td>TOTAL</td><td>${(Number(props.total)||0).toFixed(2)} LKR</td></tr>` : ""}
          ${Number(props.cash) !== 0
              ? `<tr><td>Cash Paid</td><td>${(Number(props.cash)||0).toFixed(2)} LKR</td></tr>` : ""}
          ${Number(props.balance) !== 0
              ? `<tr class="bold"><td>Balance</td><td>${(Number(props.balance)||0).toFixed(2)} LKR</td></tr>` : ""}
      </table>

      <!-- Kitchen note -->
      ${props.kitchen_note ? `<div class="kitchen-note">Note: ${props.kitchen_note}</div>` : ""}

      <!-- Footer -->
      <div class="footer">
          <p class="no-refund">-- No Exchange or Refunds --</p>
          <p class="thank-you">Thank You, Come Again!</p>
          <p class="powered">Powered by ඔන්ලයින් මුදලාලී.</p>
      </div>
  </body>
  </html>
  `;

  // Create a hidden iframe for printing
  const iframe = document.createElement("iframe");
  iframe.style.display = "none";
  document.body.appendChild(iframe);

  // Write content to iframe
  iframe.contentDocument.open();
  iframe.contentDocument.write(receiptHTML);
  iframe.contentDocument.close();

  // Wait for content to load and then print
  iframe.onload = () => {
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
    // Remove iframe after a delay to ensure print command is sent
    setTimeout(() => {
      document.body.removeChild(iframe);
    }, 500);
  };
};






const handleKOTPrintReceipt = () => {
  // Calculate totals from props.products
  const subTotal = props.products.reduce(
    (sum, product) =>
      sum + parseFloat(product.selling_price) * product.quantity,
    0
  );
  const customDiscount = Number(props.custom_discount || 0);
  const totalDiscount = props.products
    .reduce((total, item) => {
      // Check if item has a discount
      if (item.discount && item.discount > 0 && item.apply_discount == true) {
        const discountAmount =
          (parseFloat(item.selling_price) - parseFloat(item.discounted_price)) *
          item.quantity;
        return total + discountAmount;
      }
      return total; // If no discount, return total as-is
    }, 0)
    .toFixed(2); // Ensures two decimal places

  const discount = 0; // Example discount (can be dynamic)
  const total = subTotal - totalDiscount - customDiscount;

  // Generate table rows dynamically using props.products
  const productRows = props.products
    .map((product) => {
      // Determine the price based on discount
      const price =
        product.discount > 0 && product.apply_discount
          ? product.discounted_price // Use discounted price if discount is applied
          : product.selling_price; // Use selling price if no discount

      return `
        <tr>
          <td>${product.name}</td>
          <td style="text-align: center;">${product.quantity}</td>

        </tr>
      `;
    })
    .join("");

  // Generate the receipt HTML
  const receiptHTML = `
  <!DOCTYPE html>
  <html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Receipt</title>
      <style>
          @media print {
              body {
                  margin: 0;
                  padding: 0;
                  -webkit-print-color-adjust: exact;
              }
              @page {
                  size: 80mm auto;
                  margin: 0;
              }
          }
          body {
              background-color: #ffffff;
              font-size: 12px;
              font-family: 'Arial', sans-serif;
              margin: 0;
              padding: 10px;
              color: #000;
          }

          .section {
              margin-bottom: 16px;
               margin: 8px 0;

          }
          .info-row {
              display: flex;
              justify-content: space-between;
              font-size: 12px;
              margin-top: 8px;
          }
          .info-row p {
              margin: 0;
              font-weight: bold;
          }
          .info-row small {
              font-weight: normal;
          }
          table {
              width: 100%;
              font-size: 12px;
              border-collapse: collapse;
              margin-top: 8px;
          }
          table th, table td {
              padding: 6px 8px;

          }
          table th {
              text-align: left;
          }
          table td {
              text-align: right;
          }
          table td:first-child {
              text-align: left;
          }
          .totals {
              border-top: 1px solid #000;
              padding-top: 8px;
              font-size: 12px;
          }
          .totals div {
              display: flex;
              justify-content: space-between;
              margin-bottom: 8px;
          }

          .footer {
              text-align: center;
              font-size: 10px;
              margin-top: 16px;
          }
          .footer p {
              margin: 6px 0;
          }
          .footer .italic {
              font-style: italic;
          }


      </style>
  </head>
  <body>
      <div class="receipt-container">


<h1 style="text-align:center">
<img src="/images/delicasy_logo.png" alt="KOT" style="max-width: 70mm; max-height: 50px; display: block; margin: 0 auto;">
</h1>

   <div style="font-weight: bold; border: 1px solid black; text-align: center; padding: 5px; margin: 8px 0;">
                <small style="display: block;">



 Order Type: ${ props.order_type === 'takeaway'
      ? 'Takeaway'
      : props.order_type === 'pickup'
        ? 'Delivery'
        : 'Dine In' }


                </small>
              </div>

          <div class="section">
              <div class="info-row">
                  <div>
                      <p>Date:</p>
                      <small>${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}</small>
                  </div>
                  <div>
                      <p>Order No:</p>
                      <small>${props.orderId}</small>
                  </div>
              </div>
              <div class="info-row">
                  <div>
                      <p>Customer:</p>
                      <small>${props.customer?.name || 'Walking Customer'}</small>
                  </div>
                  <div>
                      <p>Cashier:</p>
                      <small>${props.cashier.name}</small>
                  </div>
              </div>


          </div>
          <div class="section">
              <table>
                  <thead>
                      <tr>
                          <th>Product Name</th>
                          <th style="text-align: center;">Qty</th>

                      </tr>
                  </thead>
                  <tbody>
                      ${productRows}
                  </tbody>
              </table>
          </div>

          ${props.kitchen_note ? `
              <div style="font-weight: bold; text-align: left; border-top: 1px solid black;
              border-bottom: 1px solid black; padding-top: 10px; padding-bottom: 10px;">
                <small style="display: block; text-align: left;">Note: ${props.kitchen_note}</small>
              </div>` : ''}

      </div>
  </body>
  </html>
  `;

  // Create a hidden iframe for printing
  const iframe = document.createElement("iframe");
  iframe.style.display = "none";
  document.body.appendChild(iframe);

  // Write content to iframe
  iframe.contentDocument.open();
  iframe.contentDocument.write(receiptHTML);
  iframe.contentDocument.close();

  // Wait for content to load and then print
  iframe.onload = () => {
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
    // Remove iframe after a delay to ensure print command is sent
    setTimeout(() => {
      if (iframe.parentNode) {
        document.body.removeChild(iframe);
      }
    }, 500);
  };
};














</script>
