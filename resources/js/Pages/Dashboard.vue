<template>
  <Head title="Dashboard" />

  <div class="min-h-screen bg-slate-50">
    <!-- ── Top bar ── -->
    <Header />

    <!-- ── Main content ── -->
    <main class="mx-auto max-w-[1600px] px-6 py-8 lg:px-10">
      <!-- Section: Quick Actions -->
      <section v-if="HasRole(['Admin', 'Cashier'])" class="mb-10">
        <h2 class="section-heading">Quick Actions</h2>
        <div class="grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
          <DashCard href="/pos" icon="/images/dashboard/checkout.png" label="POS" gradient="from-emerald-500 to-green-700" />
        </div>
      </section>

      <!-- Section: Management -->
      <section v-if="HasRole(['Admin', 'Manager'])" class="mb-10">
        <h2 class="section-heading">Management</h2>
        <div class="grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
          <DashCard href="/categories" icon="/images/dashboard/app.png" label="Categories" gradient="from-blue-600 to-indigo-800" />
          <DashCard href="/products" icon="/images/dashboard/products.png" label="Food & Beverages" gradient="from-rose-500 to-red-700" />
          <DashCard href="/suppliers" icon="/images/dashboard/team-building.png" label="Suppliers" gradient="from-amber-600 to-orange-800" />
          <DashCard href="/customers" icon="/images/dashboard/customers.png" label="Customers" gradient="from-violet-500 to-purple-700" />
          <DashCard href="/colors" icon="/images/dashboard/color-wheel.png" label="Base" gradient="from-slate-500 to-slate-700" />
          <DashCard href="/sizes" icon="/images/dashboard/measuring-tape.png" label="Sizes" gradient="from-cyan-500 to-teal-700" />
          <DashCard href="/delivery" icon="/images/dashboard/delivery.png" label="Delivery Charge" gradient="from-orange-400 to-orange-600" />
          <DashCard href="/service-charge" icon="/images/dashboard/waiter.png" label="Service Charge" gradient="from-sky-400 to-cyan-600" />
          <DashCard href="/bank-service-charge" icon="/images/dashboard/financial.png" label="Bank Service" gradient="from-lime-500 to-green-600" />
        </div>
      </section>

      <!-- Section: Report Sesstion -->
      <section v-if="HasRole(['Admin'])" class="mb-10">
          <h2 class="section-heading">Reports</h2>
          <div class="grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            <DashCard href="/reports" icon="/images/dashboard/report.png" label="Overall Report" gradient="from-amber-500 to-yellow-700" />
            <DashCard href="/reports/category-wise-sales" icon="/images/dashboard/app.png" label="Category Wise Sales" gradient="from-purple-500 to-violet-700" />
            <DashCard href="/reports/order-type-report" icon="/images/dashboard/history.png" label="Order Type Report" gradient="from-sky-500 to-blue-700" />
            <DashCard href="/reports/bar-sales-report" icon="/images/dashboard/waiter.png" label="Bar Sales Report" gradient="from-rose-500 to-red-700" />
            <DashCard href="/stock-transition" icon="/images/dashboard/stock_transtion.png" label="Stock Transition" gradient="from-indigo-500 to-blue-700" />
            <DashCard href="/transactionHistory" icon="/images/dashboard/history.png" label="Order History" gradient="from-yellow-500 to-amber-600" />
            <DashCard href="/reports/cash-drawer" icon="/images/dashboard/financial.png" label="Cash Drawer Report" gradient="from-emerald-500 to-teal-700" />
          </div>
      </section>  

      <!-- Section: Admin -->
      <section v-if="HasRole(['Admin'])" class="mb-10">
        <h2 class="section-heading">Administration</h2>
        <div class="grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
          <DashCard href="/owners" icon="/images/dashboard/coupons.png" label="Owner Discount" gradient="from-pink-500 to-rose-700" />
          <DashCard href="/employees" icon="/images/dashboard/employee.png" label="Employees" gradient="from-fuchsia-500 to-pink-700" />
          <DashCard href="/company-info" icon="/images/dashboard/building.png" label="Company Info" gradient="from-green-500 to-emerald-700" />
        </div>
      </section>

    </main>

    <Footer />
  </div>
</template>

<script setup>
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import DashCard from "@/Components/custom/DashCard.vue";
import { Head } from "@inertiajs/vue3";
import { HasRole } from "@/Utils/Permissions";
</script>

<style scoped>
.section-heading {
  @apply mb-4 text-lg font-bold uppercase tracking-widest text-slate-400;
}
</style>
