<template>
  <Head title="POS" />
  <Banner />
  <div class="pos-page h-screen flex flex-col overflow-hidden bg-zinc-950">
    <!-- Header hidden on POS page -->

    <div class="flex-1 flex flex-col overflow-hidden max-w-[1600px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-4">
      <div class="flex items-center justify-between mb-4 flex-shrink-0">
        <div class="flex items-center gap-3">
          <Link href="/" class="w-11 h-11 flex items-center justify-center rounded-xl bg-zinc-800 border border-white/10 text-zinc-400 hover:bg-zinc-700 hover:text-white transition">
            <i class="ri-arrow-left-s-line text-2xl"></i>
          </Link>
          <h1 class="text-2xl font-bold text-white tracking-wide uppercase">
            Point of Sale
          </h1>
        </div>
        <div class="flex items-center gap-4">

          <!-- Waiter Orders Alert -->
          <!-- <WaiterOrderAlert /> -->

          <!-- Last Order List button -->
          <button
            @click="openExpensesModal"
            class="flex items-center gap-2 px-4 py-2.5 bg-zinc-800 rounded-xl border border-white/10 text-zinc-300 hover:bg-zinc-700 hover:text-white transition text-[14px] font-semibold"
          >
            <i class="ri-money-dollar-circle-line text-lg"></i>
            Expenses
          </button>

          <button
            @click="openLastOrders"
            class="flex items-center gap-2 px-4 py-2.5 bg-zinc-800 rounded-xl border border-white/10 text-zinc-300 hover:bg-zinc-700 hover:text-white transition text-[14px] font-semibold"
          >
            <i class="ri-receipt-line text-lg"></i>
            Last Orders
          </button>

          <!-- Ongoing Takeaway Orders button (shown only when there are held orders) -->
          <button
            @click="isOngoingTakeawayOpen = true"
            class="relative flex items-center gap-2 px-4 py-2.5 bg-amber-500/15 rounded-xl border border-amber-500/40 text-amber-400 hover:bg-amber-500/25 hover:text-amber-300 transition text-[14px] font-semibold"
            :class="{ 'ring-anim': heldTakeawayOrders.length > 0 }"
            :style="heldTakeawayOrders.length === 0 ? 'opacity:0.38; pointer-events:none;' : ''"
            title="Ongoing Takeaway Orders"
          >
            <i class="ri-alarm-warning-line text-lg" :class="heldTakeawayOrders.length > 0 ? 'ongoing-ring-icon' : ''"></i>
            Ongoing Orders
            <span
              v-if="heldTakeawayOrders.length > 0"
              class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 flex items-center justify-center bg-amber-500 text-zinc-900 text-[11px] font-extrabold rounded-full px-1 ring-2 ring-zinc-950"
            >{{ heldTakeawayOrders.length }}</span>
          </button>

          <!-- <div v-if="selectedTable?.orderId" class="px-4 py-2 bg-zinc-800 rounded-xl border border-white/10 text-zinc-300 font-semibold text-[15px]">
            Order No <span class="font-bold text-amber-400">#{{ selectedTable.orderId }}</span>
          </div> -->
          <button @click="refreshData" class="w-11 h-11 flex items-center justify-center rounded-xl bg-zinc-800 border border-white/10 text-zinc-400 hover:bg-zinc-700 hover:text-white transition">
            <i class="ri-restart-line text-xl"></i>
          </button>

          <!-- Divider -->
          <div class="w-px h-7 bg-white/10"></div>

          <!-- Logged-in user -->
          <div class="flex items-center gap-2 px-4 py-2 bg-zinc-800 rounded-xl border border-white/10">
            <div class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-500/20 ring-1 ring-amber-500/30">
              <i class="ri-user-line text-amber-400 text-base"></i>
            </div>
            <span class="text-[14px] font-semibold text-zinc-200">{{ loggedInUser?.name }}</span>
          </div>

          <!-- Cash Drawer -->
          <button
            @click="openCashDrawerModal"
            class="flex items-center gap-2 px-4 py-2.5 bg-amber-500/15 rounded-xl border border-amber-500/40 text-amber-300 hover:bg-amber-500/25 hover:text-amber-200 transition text-[14px] font-semibold"
            title="Open/Close Cash Drawer"
          >
            <i class="ri-safe-line text-lg"></i>
            Cash Drawer
          </button>

          <!-- Logout -->
          <Link
            href="/logout"
            method="post"
            as="button"
            class="flex items-center gap-2 px-4 py-2.5 bg-zinc-800 rounded-xl border border-white/10 text-zinc-300 hover:bg-red-500/15 hover:border-red-500/40 hover:text-red-400 transition text-[14px] font-semibold"
          >
            <i class="ri-logout-box-r-line text-lg"></i>
            Logout
          </Link>
        </div>
      </div>

      <div class="flex gap-4 flex-1 overflow-hidden min-h-0">
        <!-- Left: Categories -->
        <div class="flex flex-col w-[22%] min-h-0">
          <div class="flex flex-col h-full bg-zinc-900 rounded-2xl border border-white/10 overflow-hidden">
            <div class="p-5 border-b border-white/10 flex-shrink-0 space-y-3">
              <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="ri-menu-line text-zinc-500"></i> Categories
              </h2>
              <!-- Menu Type Filter Buttons -->
              <div class="flex gap-2">
                <button
                  @click="selectedMenuType = 'food'; selectedCategory = null"
                  :class="[
                    'flex-1 px-3 py-2.5 rounded-lg font-semibold text-[13px] transition border ring-1',
                    selectedMenuType === 'food'
                      ? 'bg-orange-500/20 border-orange-500/50 ring-orange-500/30 text-orange-400'
                      : 'bg-zinc-800 border-white/10 ring-white/5 text-zinc-300 hover:bg-zinc-700'
                  ]"
                >
                  <i class="ri-restaurant-2-line mr-1.5"></i>Food Menu
                </button>
                <button
                  @click="selectedMenuType = 'beverage'; selectedCategory = null"
                  :class="[
                    'flex-1 px-3 py-2.5 rounded-lg font-semibold text-[13px] transition border ring-1',
                    selectedMenuType === 'beverage'
                      ? 'bg-blue-500/20 border-blue-500/50 ring-blue-500/30 text-blue-400'
                      : 'bg-zinc-800 border-white/10 ring-white/5 text-zinc-300 hover:bg-zinc-700'
                  ]"
                >
                  <i class="ri-drink-2-line mr-1.5"></i>Beverages
                </button>
              </div>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-2">
              <!-- Show All Button -->
              <button
                @click="selectedCategory = null"
                :class="[
                  'w-full text-left px-4 py-3 rounded-xl border transition font-semibold',
                  !selectedCategory
                    ? 'bg-emerald-500/20 border-emerald-500/50 text-emerald-400 ring-1 ring-emerald-500/30'
                    : 'bg-zinc-800 border-white/10 text-zinc-300 hover:bg-emerald-500/15 hover:border-emerald-500/40 hover:text-emerald-400'
                ]"
              >
                <div class="flex items-center justify-between">
                  <span class="text-[14px] font-semibold">Show All</span>
                  <span class="text-[12px] text-zinc-500">{{ categoryProducts?.length || 0 }}</span>
                </div>
              </button>

              <!-- Category Buttons -->
              <button
                v-for="category in filteredCategories"
                :key="category.id"
                @click="selectedCategory = category"
                :class="[
                  'w-full text-left px-4 py-3 rounded-xl border transition',
                  selectedCategory?.id === category.id
                    ? 'bg-amber-500/20 border-amber-500/50 text-amber-400 ring-1 ring-amber-500/30'
                    : 'bg-zinc-800 border-white/10 text-zinc-300 hover:bg-amber-500/15 hover:border-amber-500/40 hover:text-amber-400'
                ]"
              >
                <div class="flex items-center justify-between">
                  <p class="text-[14px] font-semibold truncate">{{ category.name }}</p>
                  <span class="text-[12px] text-zinc-500 flex-shrink-0 ml-2">{{ category.products?.length || 0 }}</span>
                </div>
              </button>
            </div>
          </div>
        </div>

        <!-- Middle: Products Grid -->
        <div class="flex flex-col w-[28%] min-h-0">
          <div class="flex flex-col h-full bg-zinc-900 rounded-2xl border border-white/10 overflow-hidden">
            <div class="p-5 border-b border-white/10 flex-shrink-0 space-y-3">
              <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="ri-shopping-cart-line text-zinc-500"></i>
                {{ selectedCategory?.name || 'All Products' }}
              </h2>
              <input
                v-model="productSearch"
                type="text"
                placeholder="Search products..."
                class="w-full px-3 py-2 bg-zinc-800 border border-white/10 rounded-lg text-[13px] text-white placeholder-zinc-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition"
              />
            </div>

            <!-- Products List (all products) -->
            <div class="flex-1 flex flex-col min-h-0 p-3">
              <div v-if="categoryProducts.length === 0" class="flex flex-col items-center justify-center h-full">
                <i class="ri-inbox-line text-3xl text-zinc-600 mb-2"></i>
                <p class="text-xs text-zinc-500">No products</p>
              </div>
              <div v-else-if="filteredProducts.length === 0" class="flex flex-col items-center justify-center h-full">
                <i class="ri-search-line text-3xl text-zinc-600 mb-2"></i>
                <p class="text-xs text-zinc-500">No products found</p>
              </div>
              <div v-else class="grid grid-cols-2 gap-2 overflow-y-auto flex-1 pr-1">
                <button
                  v-for="product in filteredProducts"
                  :key="product.id"
                  @click="addProductToCart(product)"
                  :disabled="product.is_sold_out"
                  class="relative w-full flex flex-col items-stretch gap-2 p-2 rounded-lg transition-all duration-200"
                  :class="[
                    product.is_sold_out
                      ? 'bg-zinc-700 border border-red-500/30 cursor-not-allowed opacity-50'
                      : 'bg-zinc-800 border border-white/10 hover:border-amber-500/50 hover:bg-zinc-700 active:scale-95'
                  ]"
                >
                  <!-- Product Image (Small) -->
                  <div class="w-full aspect-square rounded-md overflow-hidden bg-zinc-700">
                    <img
                      v-if="product.image"
                      :src="product.image.replace('/storage/storage/', '/storage/')"
                      :alt="product.name"
                      class="w-full h-full object-cover"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center text-zinc-600 text-sm">
                      <i class="ri-image-line"></i>
                    </div>
                  </div>

                  <!-- Product Info (Compact) -->
                  <div class="flex-1 min-w-0">
                    <p class="text-[12px] font-semibold text-white leading-tight line-clamp-2">{{ product.name }}</p>
                    <div class="flex flex-wrap items-center gap-1 mt-1">
                      <p v-if="product.sizes && product.sizes.length > 0" class="text-[14px] text-amber-400 font-bold text-center">
                        LKR {{ Math.min(...product.sizes.map(s => parseFloat(s.price || 0))).toFixed(0) }} - LKR {{ Math.max(...product.sizes.map(s => parseFloat(s.price || 0))).toFixed(0) }}
                      </p>
                      <p v-else class="text-[14px] text-amber-400 font-bold text-center">LKR {{ product.selling_price }} </p>
                    </div>
                  </div>

                  <!-- Sold Out Badge -->
                  <div v-if="product.is_sold_out" class="absolute inset-0 flex items-center justify-center bg-zinc-900/70 rounded-lg">
                    <span class="text-[10px] font-bold text-red-400">SOLD OUT</span>
                  </div>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Bill -->
        <div class="flex flex-col w-[50%] min-h-0">
          <div class="flex flex-col h-full bg-zinc-900 rounded-2xl border border-white/10 overflow-hidden">

            <!-- Bill Header — fixed -->
            <div class="flex-shrink-0 border-b border-white/10">
              <!-- Tables Selector -->
              <div class="px-5 pt-5 pb-3 border-b border-white/10 bg-zinc-950/50">
                <div class="flex items-center justify-between mb-3">
                  <h3 class="text-sm font-bold text-zinc-400 uppercase tracking-wide">Tables</h3>
                  <button
                    @click="addTable"
                    class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white text-[12px] font-semibold transition active:scale-95"
                    title="Add new table"
                  >
                    <i class="ri-add-line text-base"></i>
                    Add Table
                  </button>
                </div>
                <!-- Tables & Live Bill Tabs -->
                <div class="flex gap-2 overflow-x-auto pb-2">
                  <!-- Live Bill Tab -->
                  <button
                    @click="selectedTable = tables.find(t => t.id === 'default')"
                    :class="[
                      'flex-shrink-0 px-4 py-2.5 rounded-xl font-semibold text-[14px] transition border ring-1',
                      selectedTable?.id === 'default'
                        ? 'bg-amber-500/20 border-amber-500/50 ring-amber-500/30 text-amber-400'
                        : 'bg-zinc-800 border-white/10 ring-white/5 text-zinc-300 hover:bg-zinc-700 hover:border-amber-500/30'
                    ]"
                  >
                    <i class="ri-receipt-line text-base mr-2"></i>
                    Live Bill
                  </button>
                  <!-- Restaurant Tables -->
                  <button
                    v-for="table in addedTables"
                    :key="table.id"
                    @click="selectedTable = table"
                    :class="[
                      'flex-shrink-0 px-4 py-2.5 rounded-xl font-semibold text-[14px] transition border ring-1 relative',
                      selectedTable?.id === table.id
                        ? 'bg-blue-500/20 border-blue-500/50 ring-blue-500/30 text-blue-400'
                        : 'bg-zinc-800 border-white/10 ring-white/5 text-zinc-300 hover:bg-zinc-700 hover:border-blue-500/30'
                    ]"
                  >
                    <i class="ri-table-2 text-base mr-2"></i>
                    Table {{ table.number }}
                    <span v-if="table.products?.length > 0" class="absolute -top-2 -right-2 min-w-[20px] h-5 flex items-center justify-center bg-red-500 text-white text-[10px] font-bold rounded-full">
                      {{ table.products.length }}
                    </span>
                  </button>
                </div>
              </div>

              <!-- Bill Title & Controls -->
              <div class="px-5 py-4">
                <div class="flex flex-wrap items-center justify-between w-full gap-3">
                  <!-- Left: title + customer button -->
                  <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                      <i class="ri-file-list-3-line text-amber-400"></i>
                      {{ selectedTable?.id === 'default' ? 'Live Bill' : `Table ${selectedTable?.number}` }}
                    </h2>
                    <button
                      @click="() => { openEditModal(color); }"
                      class="flex items-center gap-2 px-4 py-2.5 bg-zinc-800 text-zinc-300 border border-white/10 rounded-xl hover:bg-zinc-700 hover:text-white transition text-[15px] font-medium"
                      title="Customer details"
                    >
                      <i class="ri-user-line text-lg text-zinc-400"></i>
                    </button>
                  </div>

                  <!-- Right: order type buttons -->
                  <div class="flex items-center gap-2">
                    <!-- Takeaway -->
                    <button
                      @click="selectedTable.order_type = 'takeaway'"
                      :class="[
                        'flex items-center gap-2 px-4 py-2.5 rounded-xl ring-1 text-[15px] font-semibold transition active:scale-95',
                        selectedTable.order_type === 'takeaway'
                          ? 'bg-amber-500 ring-amber-500 text-zinc-900 shadow-md shadow-amber-500/20'
                          : 'bg-zinc-800 ring-white/10 text-zinc-300 hover:bg-amber-500/15 hover:ring-amber-500/40 hover:text-amber-400'
                      ]"
                    >
                      <i class="ri-shopping-bag-3-line text-lg"></i>
                      <span>Takeaway</span>
                    </button>

                    <!-- In-Room Dining -->
                    <button
                      @click="selectedTable.order_type = 'pickup'"
                      :class="[
                        'flex items-center gap-2 px-4 py-2.5 rounded-xl ring-1 text-[15px] font-semibold transition active:scale-95',
                        selectedTable.order_type === 'pickup'
                          ? 'bg-violet-600 ring-violet-600 text-white shadow-md'
                          : 'bg-zinc-800 ring-white/10 text-zinc-300 hover:bg-violet-500/15 hover:ring-violet-500/40 hover:text-violet-400'
                      ]"
                    >
                      <i class="ri-hotel-bed-line text-lg"></i>
                      <span>Delivery</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Scrollable bill body -->
            <div class="flex-1 overflow-y-auto px-5 py-4">

            <div class="w-full text-center py-10" v-if="!selectedTable || selectedTable.products.length === 0">
              <div class="flex flex-col items-center gap-3">
                <i class="ri-shopping-basket-line text-5xl text-zinc-700"></i>
                <p class="text-lg text-zinc-500 font-medium">No products added yet</p>
              </div>
            </div>

            <div class="space-y-2 mb-4 max-h-[420px] overflow-y-auto">
              <div
                v-for="item in selectedTable.products"
                :key="item.cart_key || item.id"
                class="group flex gap-3 p-3 rounded-2xl bg-zinc-800 border border-white/10 hover:border-white/20 transition-all duration-200"
              >
                <!-- Product Image -->
                <div class="w-[72px] h-[72px] flex-shrink-0 rounded-xl overflow-hidden ring-1 ring-white/10">
                  <img
  :src="item.image
    ? item.image.replace('/storage/storage/', '/storage/')
    : '/images/placeholder.jpg'"
  alt="Product Image"
  class="object-cover w-full h-full"
/>
                </div>

                <!-- Product Details -->
                <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5">

                  <!-- Top row: name + remove -->
                  <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                      <h3 class="text-[15px] font-bold text-white leading-snug truncate">
                        {{ item.name }}
                        <span v-if="item.size?.name" class="text-[12px] text-amber-400 font-semibold"> ({{ item.size.name }})</span>
                      </h3>

                    </div>
                    <button
                      @click="removeProduct(item.id)"
                      class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-lg text-yellow-400 hover:bg-yellow-400/15 hover:text-yellow-300 transition"
                    >
                      <i class="ri-delete-bin-6-line text-2xl"></i>
                    </button>
                  </div>

                  <!-- Bottom row: qty controls left / price + discount right -->
                  <div class="flex items-center justify-between mt-2">

                    <!-- Qty controls -->
                    <div class="flex items-center gap-0 bg-zinc-700 ring-1 ring-white/10 rounded-xl overflow-hidden">
                      <button
                        @click="decrementQuantity(item.id)"
                        class="w-9 h-9 flex items-center justify-center text-zinc-300 hover:bg-zinc-600 active:scale-90 transition text-lg font-bold"
                      >
                        <i class="ri-subtract-line"></i>
                      </button>
                      <span class="w-9 text-center text-[15px] font-bold text-white select-none">
                        {{ item.quantity }}
                      </span>
                      <button
                        @click="incrementQuantity(item.id)"
                        class="w-9 h-9 flex items-center justify-center text-zinc-300 hover:bg-zinc-600 active:scale-90 transition text-lg font-bold"
                      >
                        <i class="ri-add-line"></i>
                      </button>
                    </div>

                    <!-- Price + discount -->
                    <div class="flex flex-col items-end gap-1">
                      <p class="text-[16px] font-extrabold text-amber-400 leading-none">
                        {{
                          item.apply_discount
                            ? ((item.selling_price * item.quantity * (100 - item.discount)) / 100).toFixed(2)
                            : (item.selling_price * item.quantity).toFixed(2)
                        }}
                        <span class="text-[11px] font-semibold text-zinc-500 ml-0.5">LKR</span>
                      </p>
                      <div class="flex items-center gap-1.5">
                        <span class="text-[11px] text-zinc-500">{{ item.selling_price }} × {{ item.quantity }}</span>
                        <button
                          v-if="item.discount && item.discount > 0 && item.apply_discount == false && !appliedCoupon"
                          @click="applyDiscount(item.id)"
                          class="text-[11px] px-2 py-0.5 bg-emerald-500/15 ring-1 ring-emerald-500/30 text-emerald-400 rounded-lg font-semibold hover:bg-emerald-500/25 transition active:scale-95"
                        >
                          -{{ item.discount }}%
                        </button>
                        <button
                          v-if="item.discount && item.discount > 0 && item.apply_discount == true && !appliedCoupon"
                          @click="removeDiscount(item.id)"
                          class="text-[11px] px-2 py-0.5 bg-red-500/15 ring-1 ring-red-500/30 text-red-400 rounded-lg font-semibold hover:bg-red-500/25 transition active:scale-95"
                        >
                          ✕ {{ item.discount }}%
                        </button>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            </div>

            <!-- Summary -->
            <div class="border-t border-white/10 mt-2 pt-4 space-y-3">
              <div class="flex items-center justify-between px-2">
                <p class="text-[15px] text-zinc-400">Sub Total</p>
                <p class="text-[15px] font-semibold text-zinc-200">{{ subtotal }} LKR</p>
              </div>

              <div class="flex items-center justify-between px-2 pb-3 border-b border-white/10">
                <p class="text-[15px] text-zinc-400">Discount</p>
                <p class="text-[15px] font-semibold text-red-400">( {{ totalDiscount }} LKR )</p>
              </div>

              <div
                v-if="selectedTable.order_type === 'pickup'"
                class="px-2 pb-3 border-b border-white/10"
              >
                <select
                  v-model="selectedTable.delivery_charge"
                  class="w-full py-3 text-[15px] font-semibold text-zinc-200 bg-zinc-800 border border-white/10 rounded-xl cursor-pointer focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition"
                >
                  <option value="" class="bg-zinc-800">Select Delivery Charge</option>
                  <option v-for="charge in delivery" :key="charge.id" :value="charge.delivery_charge">
                    {{ charge.delivery_charge }} LKR
                  </option>
                </select>
              </div>

              <div
                v-if="selectedTable && selectedTable.id !== 'default' && selectedTable.order_type !== 'pickup'"
                class="px-2 pb-3 border-b border-white/10"
              >
                <select
                  v-model="selectedTable.service_charge"
                  class="w-full py-3 text-[15px] font-semibold text-zinc-200 bg-zinc-800 border border-white/10 rounded-xl cursor-pointer focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition"
                >
                  <option value="" class="bg-zinc-800">Select Service Charge</option>
                  <option
                    v-for="charge in serviceCharge"
                    :key="charge.id"
                    :value="parseFloat(charge.service_charge)"
                  >
                    {{ charge.service_charge }}%{{ charge.service_check === true || charge.service_check === 'true' ? ' (Default)' : '' }}
                  </option>
                </select>
              </div>

              <!-- Shopping Bag Charge -->
              <div class="flex items-center justify-between px-2 pb-3 border-b border-white/10">
                <div class="flex items-center gap-2">
                  <p class="text-[15px] text-zinc-400">Shopping Bag</p>
                  <button
                    @click="selectedTable.shopping_bag_charge_enabled = !selectedTable.shopping_bag_charge_enabled"
                    :class="[
                      'relative w-11 h-6 rounded-full transition-colors duration-200',
                      selectedTable.shopping_bag_charge_enabled ? 'bg-amber-500' : 'bg-zinc-700'
                    ]"
                  >
                    <div
                      :class="[
                        'absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full transition-transform duration-200',
                        selectedTable.shopping_bag_charge_enabled ? 'translate-x-5' : 'translate-x-0'
                      ]"
                    />
                  </button>
                </div>
                <p :class="['text-[15px] font-semibold', selectedTable.shopping_bag_charge_enabled ? 'text-amber-400' : 'text-zinc-500 line-through']">
                  {{ selectedTable.shopping_bag_charge_enabled ? '10.00' : '0.00' }} LKR
                </p>
              </div>

              <div class="flex items-center justify-between px-2 pt-2">
                <p class="text-xl font-bold text-white">Total</p>
                <p class="text-xl font-bold text-amber-400">{{ total }} LKR</p>
              </div>

              <div
                v-if="selectedPaymentMethod === 'cash'"
                class="flex items-center justify-between px-2 pb-3 border-b border-white/10"
              >
                <p class="text-[15px] text-zinc-400">Cash</p>
                <button
                  @click="openCashNumpad"
                  class="h-14 min-w-[180px] pl-5 pr-4 flex items-center justify-between gap-3 bg-zinc-800 border border-white/10 rounded-xl hover:border-amber-500 transition active:scale-95"
                >
                  <span :class="selectedTable.cash && Number(selectedTable.cash) > 0 ? 'text-white font-bold text-xl' : 'text-zinc-500 text-[15px]'">
                    {{ selectedTable.cash && Number(selectedTable.cash) > 0 ? Number(selectedTable.cash).toFixed(2) : 'Enter Amount' }}
                  </span>
                  <span class="text-[13px] text-zinc-500 font-medium">LKR</span>
                  <i class="ri-calculator-line text-zinc-500 text-xl flex-shrink-0"></i>
                </button>
              </div>

              <!-- Card fields — Bank / Service Charge / Last 4 in one row -->
              <div v-if="selectedPaymentMethod === 'card'" class="px-2 pb-3 border-b border-white/10">
                <div class="grid grid-cols-3 gap-3">

                  <!-- Select Bank -->
                  <div class="flex flex-col gap-1.5">
                    <p class="flex items-center gap-1.5 text-[13px] font-semibold text-zinc-500 uppercase tracking-wide">
                      <i class="ri-bank-line text-blue-400"></i> Bank
                    </p>
                    <button
                      @click="isBankModalOpen = true"
                      class="h-14 w-full pl-3 pr-2 flex items-center justify-between gap-1 bg-zinc-800 border border-white/10 rounded-xl text-[13px] hover:border-amber-500 active:scale-[0.98] transition"
                    >
                      <span :class="selectedTable.bank_name ? 'text-white font-semibold text-[13px] truncate leading-tight' : 'text-zinc-500 text-[13px] font-normal'">
                        {{ selectedTable.bank_name || 'Select Bank' }}
                      </span>
                      <i class="ri-arrow-down-s-line text-zinc-500 text-base flex-shrink-0"></i>
                    </button>
                  </div>

                  <!-- Bank Service Charge -->
                  <div class="flex flex-col gap-1.5">
                    <p class="flex items-center gap-1.5 text-[13px] font-semibold text-zinc-500 uppercase tracking-wide">
                      <i class="ri-percent-line text-violet-400"></i> Charge
                    </p>
                    <select
                      v-model="selectedTable.bank_service_charge"
                      class="h-14 w-full px-3 text-[14px] font-semibold text-zinc-200 bg-zinc-800 border border-white/10 rounded-xl cursor-pointer focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition"
                    >
                      <option value="">— %</option>
                      <option
                        v-for="charge in bankCharge"
                        :key="charge.id"
                        :value="parseFloat(charge.bank_service_charge)"
                      >
                        {{ charge.bank_service_charge }}%{{ charge.service_check === true || charge.service_check === 'true' ? ' ✓' : '' }}
                      </option>
                    </select>
                  </div>

                  <!-- Last 4 Digits -->
                  <div class="flex flex-col gap-1.5">
                    <p class="flex items-center gap-1.5 text-[13px] font-semibold text-zinc-500 uppercase tracking-wide">
                      <i class="ri-bank-card-line text-amber-400"></i> Last 4
                    </p>
                    <input
                      v-model="selectedTable.card_last4"
                      type="text"
                      maxlength="4"
                      pattern="[0-9]{4}"
                      placeholder="•••• "
                      class="h-14 w-full text-center bg-zinc-800 border border-white/10 rounded-xl text-[18px] font-bold text-white placeholder-zinc-600 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition tracking-widest"
                    />
                  </div>

                </div>
              </div>

              <div class="flex items-center justify-between px-2 pb-3 border-b border-white/10">
                <p class="text-[15px] font-semibold text-zinc-400">Balance</p>
                <p class="text-[15px] font-bold text-zinc-200">{{ balance }} LKR</p>
              </div>
            </div>

            <!-- Owner Discount -->
            <!-- (unchanged UI; logic updated in script) -->
            <div class="w-full my-5">
              <!-- ... owner discount UI exactly as before ... -->
              <!-- To keep the answer concise, the markup here remains identical to your original -->
            </div>

            <!-- Kitchen Note -->
            <div class="w-full mb-4">
              <input
                id="coupon"
                v-model="selectedTable.kitchen_note"
                type="text"
                placeholder="Kitchen Note"
                class="w-full h-14 px-5 text-[15px] text-zinc-200 placeholder-zinc-500 bg-zinc-800 border border-white/10 rounded-xl focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition"
              />
            </div>

            <div class="flex flex-col w-full gap-2">
              <!-- Action Buttons (4 columns) -->
              <div class="grid grid-cols-4 gap-2">
                <!-- Cash Button -->
                <button
                  @click="selectedPaymentMethod = 'cash'"
                  :class="[
                    'py-3 flex items-center justify-center text-xs font-bold rounded-xl transition-all active:scale-[0.98]',
                    selectedPaymentMethod === 'cash'
                      ? 'bg-amber-500 hover:bg-amber-600 text-zinc-900 shadow-lg shadow-amber-500/20'
                      : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700'
                  ]"
                >
                  Cash
                </button>

                <!-- Card Button -->
                <button
                  @click="selectedPaymentMethod = 'card'"
                  :class="[
                    'py-3 flex items-center justify-center text-xs font-bold rounded-xl transition-all active:scale-[0.98]',
                    selectedPaymentMethod === 'card'
                      ? 'bg-blue-500 hover:bg-blue-600 text-white shadow-lg shadow-blue-500/20'
                      : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700'
                  ]"
                >
                  Card
                </button>

                <!-- Send KOT Button -->
                <button
                  v-if="selectedTable && selectedTable.products.length > 0"
                  @click="sendTakeawayKOT"
                  type="button"
                  :class="[
                    'py-3 flex items-center justify-center text-xs font-bold rounded-xl transition-all active:scale-[0.98] text-white',
                    selectedTable.id !== 'default' || selectedTable.order_type === 'pickup' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-amber-600 hover:bg-amber-700'
                  ]"
                >
                  Send KOT
                </button>

                <!-- Get Bill Button -->
                <button
                  @click="printBillOnly"
                  type="button"
                  :disabled="!selectedTable || selectedTable.products.length === 0"
                  :class="[
                    'py-3 flex items-center justify-center text-xs font-bold rounded-xl transition-all active:scale-[0.98]',
                    !selectedTable || selectedTable.products.length === 0
                      ? 'bg-zinc-800 text-zinc-600 cursor-not-allowed'
                      : 'bg-zinc-700 text-zinc-200 hover:bg-zinc-600'
                  ]"
                >
                  Get Bill
                </button>
              </div>

              <!-- Hold Order Button (conditional, full width if shown) -->
              <button
                v-if="selectedTable?.id === 'default' && selectedTable.order_type === 'takeaway' && selectedTable.products.length > 0"
                @click="holdTakeawayOrder"
                type="button"
                class="w-full py-3 flex items-center justify-center text-xs font-bold text-amber-400 rounded-xl bg-zinc-800 hover:bg-zinc-700 transition-all active:scale-[0.98]"
              >
                Hold Order
              </button>

              <!-- Full-Width Confirm Button -->
              <button
                @click="submitOrder"
                type="button"
                :disabled="!selectedTable || selectedTable.products.length === 0"
                :class="[
                  'w-full py-4 flex items-center justify-center text-sm font-bold rounded-xl transition-all active:scale-[0.98]',
                  !selectedTable || selectedTable.products.length === 0
                    ? 'bg-zinc-700 text-zinc-500 cursor-not-allowed'
                    : 'bg-green-500 hover:bg-green-600 text-white shadow-lg shadow-green-500/20',
                ]"
              >
                Confirm Order
              </button>
            </div>

            </div><!-- end scrollable body -->
          </div>
        </div>
        <!-- /Right -->
      </div>
    </div>
  </div>

  <!-- Opening Balance Modal (blocks POS until opened) -->
  <Teleport to="body">
    <div v-if="isOpeningBalanceModalOpen" class="fixed inset-0 z-[1001] flex items-center justify-center">
      <div class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>
      <div class="relative bg-zinc-900 rounded-2xl border border-white/10 shadow-2xl w-[420px] max-w-[90vw]">
        <div class="px-5 py-4 border-b border-white/10 bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900">
          <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/20 ring-1 ring-amber-500/40">
              <i class="ri-safe-line text-amber-400"></i>
            </div>
            <h3 class="text-2xl font-bold text-white">Open Cash Drawer</h3>
          </div>
        </div>
        <div class="p-5 space-y-4">
          <p class="text-xl text-zinc-400">
            Enter the opening balance to start using the POS.
          </p>
          <div>
            <label class="block text-2xl font-semibold text-zinc-300 mb-2">Opening Balance</label>
            <input
              v-model="openingBalanceInput"
              type="number"
              min="0"
              step="0.01"
              inputmode="decimal"
              placeholder="0.00"
              class="w-full h-11 px-4 bg-zinc-800 border border-white/10 rounded-xl text-[15px] text-white placeholder-zinc-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition"
            />
            <p v-if="openingBalanceError" class="mt-2 text-2xl text-red-400">{{ openingBalanceError }}</p>
          </div>
          <button
            @click="submitOpeningBalance"
            :disabled="isOpeningBalanceSaving"
            class="w-full h-11 rounded-xl font-bold text-zinc-900 bg-amber-500 hover:bg-amber-400 transition disabled:opacity-60 disabled:cursor-not-allowed"
          >
            {{ isOpeningBalanceSaving ? "Opening..." : "Open Drawer" }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>

  <!-- Cash Drawer Modal (open/close) -->
  <Teleport to="body">
    <div v-if="isCashDrawerModalOpen" class="fixed inset-0 z-[1000] flex items-center justify-center">
      <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="isCashDrawerModalOpen = false"></div>
      <div class="relative bg-zinc-900 rounded-2xl border border-white/10 shadow-2xl w-[460px] max-w-[92vw]">
        <div class="flex items-center justify-between px-5 py-4 border-b border-white/10 bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900">
          <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/20 ring-1 ring-amber-500/40">
              <i class="ri-safe-line text-amber-400"></i>
            </div>
            <h3 class="text-2xl font-bold text-white">Cash Drawer</h3>
          </div>
          <button
            @click="isCashDrawerModalOpen = false"
            class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/10 text-zinc-400 hover:bg-white/20 hover:text-white transition"
          >
            <i class="ri-close-line text-xl"></i>
          </button>
        </div>

        <div class="p-5 space-y-6">
          <div v-if="openCashDrawer" class="space-y-3">
            <div class="flex items-center justify-between text-lg text-zinc-400">
              <span class="text-amber-300 font-semibold">Opening: {{ Number(openCashDrawer?.opening_balance || 0).toFixed(2) }} LKR</span>
              <span class="text-xl">Opened Time : {{ new Date(openCashDrawer.opened_at).toLocaleString() }}</span>
            </div>
            <div>
              <label class="block text-xl font-semibold text-zinc-300 mb-2">Closing Balance</label>
              <input
                v-model="closingBalanceInput"
                type="number"
                min="0"
                step="0.01"
                inputmode="decimal"
                placeholder="0.00"
                class="w-full h-11 px-4 bg-zinc-800 border border-white/10 rounded-xl text-[15px] text-white placeholder-zinc-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition"
              />
              <p v-if="closingBalanceError" class="mt-2 text-sm text-red-400">{{ closingBalanceError }}</p>
            </div>
            <button
              @click="submitClosingBalance"
              :disabled="isClosingBalanceSaving"
              class="w-full h-11 rounded-xl font-bold text-white bg-rose-500 hover:bg-rose-400 transition disabled:opacity-60 disabled:cursor-not-allowed"
            >
              {{ isClosingBalanceSaving ? "Closing..." : "Close Drawer" }}
            </button>
          </div>

          <div v-else class="space-y-3">
            <p class="text-sm text-zinc-400">No cash drawer is open. Enter the opening balance to start.</p>
            <div>
              <label class="block text-sm font-semibold text-zinc-300 mb-2">Opening Balance</label>
              <input
                v-model="openingBalanceInput"
                type="number"
                min="0"
                step="0.01"
                inputmode="decimal"
                placeholder="0.00"
                class="w-full h-11 px-4 bg-zinc-800 border border-white/10 rounded-xl text-[15px] text-white placeholder-zinc-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition"
              />
              <p v-if="openingBalanceError" class="mt-2 text-sm text-red-400">{{ openingBalanceError }}</p>
            </div>
            <button
              @click="submitOpeningBalance"
              :disabled="isOpeningBalanceSaving"
              class="w-full h-11 rounded-xl font-bold text-zinc-900 bg-amber-500 hover:bg-amber-400 transition disabled:opacity-60 disabled:cursor-not-allowed"
            >
              {{ isOpeningBalanceSaving ? "Opening..." : "Open Drawer" }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>

  <PosSuccessModel
    :open="isSuccessModalOpen"
    @update:open="handleModalOpenUpdate"
    :products="selectedTable.products"
    :cashier="loggedInUser"
    :customer="customer"
    :orderId="selectedTable.orderId"
    :cash="selectedTable.cash"
    :balance="balance"
    :subTotal="subtotal"
    :totalDiscount="totalDiscount"
    :total="total"
    :custom_discount="customDiscCalculated"
    :delivery_charge="selectedTable.delivery_charge"
    :service_charge="selectedTable.service_charge"
    :bank_service_charge="selectedTable.bank_service_charge"
    :shopping_bag_charge="selectedTable.shopping_bag_charge_enabled ? 10.00 : 0"
    :selectedTable="selectedTable"
    :kitchen_note="selectedTable.kitchen_note"
    :selectedPaymentMethod="selectedPaymentMethod"
    :order_type="selectedTable.order_type"
    :owner_discount_value="ownerDiscountValue"
    :owner_code="ownerCodeValue"
  />
  <AlertModel v-model:open="isAlertModalOpen" :message="message" />

  <SelectProductModel
    v-model:open="isSelectModalOpen"
    :allcategories="allcategories"
    :colors="colors"
    :sizes="sizes"
    :menuType="selectedMenuType"
    @selected-products="handleSelectedProducts"
  />

   <ColorUpdateModel
    :customer="customer"
    v-model:open="isEditModalOpen"
    @update:customer="handleCustomerUpdate"
    @search="searchCustomer"
  />

  <!-- Product Size & Quantity Selection Modal -->
  <Teleport to="body">
    <div v-if="isProductSelectionModalOpen" class="fixed inset-0 z-[1000] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="isProductSelectionModalOpen = false"></div>
      <div class="relative bg-zinc-900 rounded-3xl border border-white/10 shadow-2xl w-full max-w-[420px] overflow-hidden flex flex-col">

        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-white/10">
          <div>
            <h3 class="text-lg font-bold text-white">{{ selectedProductForModal?.name }}</h3>
            <p v-if="selectedProductForModal?.sizes" class="text-[12px] text-amber-400 font-semibold mt-1">
              {{ Math.min(...selectedProductForModal.sizes.map(s => parseFloat(s.price || 0))).toFixed(0) }} - {{ Math.max(...selectedProductForModal.sizes.map(s => parseFloat(s.price || 0))).toFixed(0) }} LKR
            </p>
          </div>
          <button @click="isProductSelectionModalOpen = false"
            class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/10 text-zinc-400 hover:bg-white/20 hover:text-white transition">
            <i class="ri-close-line text-xl"></i>
          </button>
        </div>

        <div class="p-5 space-y-4 flex-1 overflow-y-auto">
          <!-- Size Selection -->
          <div>
            <label class="block text-sm font-semibold text-zinc-300 mb-3">Select Size</label>
            <div class="grid grid-cols-2 gap-2">
              <button
                v-for="size in selectedProductForModal?.sizes"
                :key="size.id"
                @click="selectedSizeForModal = size"
                :class="[
                  'py-3 px-4 rounded-xl font-semibold text-sm transition border ring-1 flex flex-col items-center gap-1',
                  selectedSizeForModal?.id === size.id
                    ? 'bg-amber-500/20 border-amber-500/50 ring-amber-500/30 text-amber-400'
                    : 'bg-zinc-800 border-white/10 ring-white/5 text-zinc-300 hover:border-amber-500/40'
                ]"
              >
                <span>{{ size.name }}</span>
                <span class="text-[11px] opacity-75">{{ parseFloat(size.price || 0).toFixed(2) }} LKR</span>
              </button>
            </div>
          </div>

          <!-- Quantity Selection -->
          <div>
            <label class="block text-sm font-semibold text-zinc-300 mb-3">Quantity</label>
            <div class="flex items-center gap-2">
              <button
                @click="quantityForModal = Math.max(1, quantityForModal - 1)"
                class="w-10 h-10 flex items-center justify-center rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-300 transition"
              >
                <i class="ri-subtract-line text-lg"></i>
              </button>
              <input
                v-model.number="quantityForModal"
                type="number"
                min="1"
                class="flex-1 h-10 text-center bg-zinc-800 border border-white/10 rounded-lg text-white font-bold text-lg focus:border-amber-500 focus:outline-none"
              />
              <button
                @click="quantityForModal = quantityForModal + 1"
                class="w-10 h-10 flex items-center justify-center rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-300 transition"
              >
                <i class="ri-add-line text-lg"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 p-5 border-t border-white/10">
          <button
            @click="isProductSelectionModalOpen = false"
            class="flex-1 py-3 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-semibold transition"
          >
            Cancel
          </button>
          <button
            @click="confirmProductSelection"
            class="flex-1 py-3 rounded-lg bg-amber-500 hover:bg-amber-600 text-zinc-900 font-bold transition"
          >
            Add to Cart
          </button>
        </div>
      </div>
    </div>
  </Teleport>

  <!-- Bank Selection Modal -->
  <Teleport to="body">
    <div v-if="isBankModalOpen" class="fixed inset-0 z-[999] flex items-center justify-center">
      <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" @click="isBankModalOpen = false"></div>
      <div class="relative bg-zinc-900 rounded-2xl border border-white/10 shadow-2xl w-full max-w-[680px] max-h-[85vh] flex flex-col overflow-hidden">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-5 py-4 bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900 border-b border-white/10">
          <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/20 ring-1 ring-amber-500/40">
              <i class="ri-bank-line text-amber-400"></i>
            </div>
            <h3 class="text-lg font-bold text-white">Select Bank</h3>
          </div>
          <button @click="isBankModalOpen = false"
            class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/10 text-zinc-400 hover:bg-white/20 hover:text-white transition">
            <i class="ri-close-line text-xl"></i>
          </button>
        </div>
        <!-- Search -->
        <div class="px-5 py-3 border-b border-white/10">
          <input
            v-model="bankQuery"
            type="text"
            placeholder="Search bank name..."
            autofocus
            class="w-full h-11 px-4 bg-zinc-800 border border-white/10 rounded-xl text-[15px] text-white placeholder-zinc-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition"
          />
        </div>
        <!-- Bank Grid -->
        <div class="overflow-y-auto flex-1 p-4 bg-zinc-950/40">
          <div v-if="filteredBanksModal.length > 0" class="space-y-6">
            <!-- Group by First Letter -->
            <div v-for="(banks, letter) in groupedBanksByLetter" :key="letter" class="space-y-3">
              <!-- Letter Header -->
              <div class="sticky top-0 bg-zinc-950/80 backdrop-blur-sm px-4 py-2 rounded-lg border border-white/10">
                <h3 class="text-lg font-bold text-amber-400">{{ letter }}</h3>
              </div>
              <!-- Banks Grid for this Letter -->
              <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                <button
                  v-for="bank in banks"
                  :key="bank"
                  @click="selectBank(bank)"
                  :class="[
                    'flex flex-col items-center justify-center p-5 rounded-2xl border-2 transition duration-200 active:scale-95 h-auto min-h-[200px]',
                    'hover:bg-zinc-800/50',
                    selectedTable.bank_name === bank
                      ? 'bg-amber-500/20 border-amber-500 ring-2 ring-amber-500/40'
                      : 'bg-zinc-800 border-white/10 hover:border-amber-500/30'
                  ]"
                  :title="bank"
                >
                  <!-- Bank Logo -->
                  <div class="w-28 h-28 flex items-center justify-center mb-4 bg-white/5 rounded-lg flex-shrink-0">
                    <img
                      :src="`/images/banks/${getBankLogoName(bank)}`"
                      :alt="bank"
                      class="w-24 h-24 object-contain"
                      @error="$event.target.style.display = 'none'"
                    />
                  </div>
                  <!-- Bank Name -->
                  <span class="text-center text-base font-bold text-white leading-snug line-clamp-3">{{ bank }}</span>
                  <!-- Check Icon -->
                  <i v-if="selectedTable.bank_name === bank" class="ri-check-circle-fill text-amber-400 text-2xl absolute top-2 right-2"></i>
                </button>
              </div>
            </div>
          </div>
          <div v-else class="flex flex-col items-center justify-center py-12">
            <i class="ri-search-line text-4xl text-zinc-700 mb-3"></i>
            <p class="text-center text-zinc-500 text-sm">No banks found</p>
          </div>
        </div>
      </div>
    </div>
  </Teleport>

  <!-- Cash Numpad Modal -->
  <Teleport to="body">
    <div v-if="isCashNumpadOpen" class="fixed inset-0 z-[1000] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" @click="isCashNumpadOpen = false"></div>
      <div class="relative bg-zinc-900 rounded-3xl border border-white/10 shadow-2xl w-full max-w-[420px] overflow-hidden flex flex-col">

        <!-- Header -->
        <div class="flex items-center justify-between px-5 pt-5 pb-4 bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900 border-b border-white/10">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-amber-500/20 ring-1 ring-amber-500/40">
              <i class="ri-calculator-line text-amber-400 text-xl"></i>
            </div>
            <div>
              <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Cash Payment</p>
              <p class="text-[13px] font-semibold text-zinc-400">Total: <span class="text-amber-400">{{ total }} LKR</span></p>
            </div>
          </div>
          <button @click="isCashNumpadOpen = false"
            class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/10 text-zinc-400 hover:bg-white/20 hover:text-white transition">
            <i class="ri-close-line text-xl"></i>
          </button>
        </div>

        <!-- Display -->
        <div class="mx-5 mb-4 mt-4 px-5 py-4 bg-zinc-800 border border-white/10 rounded-2xl flex items-center justify-between">
          <span class="text-4xl font-bold text-white tracking-tight truncate">
            {{ cashNumpadInput === '' || cashNumpadInput === '0' ? '0' : cashNumpadInput }}
          </span>
          <span class="text-lg font-semibold text-zinc-500 ml-3 flex-shrink-0">LKR</span>
        </div>

        <!-- Quick amount buttons -->
        <div class="px-5 mb-4 grid grid-cols-4 gap-2">
          <button v-for="quick in [500, 1000, 2000, 5000]" :key="quick"
            @click="numpadSetQuick(quick)"
            class="py-2.5 rounded-xl bg-amber-500/15 ring-1 ring-amber-500/30 text-amber-400 font-semibold text-[13px] hover:bg-amber-500/25 active:scale-95 transition">
            {{ quick.toLocaleString() }}
          </button>
        </div>

        <!-- Numpad grid -->
        <div class="px-5 pb-5 grid grid-cols-3 gap-3">
          <!-- 7 8 9 -->
          <button @click="numpadPress('7')" class="py-5 rounded-2xl bg-zinc-800 border border-white/10 hover:bg-zinc-700 active:scale-95 transition text-2xl font-bold text-white">7</button>
          <button @click="numpadPress('8')" class="py-5 rounded-2xl bg-zinc-800 border border-white/10 hover:bg-zinc-700 active:scale-95 transition text-2xl font-bold text-white">8</button>
          <button @click="numpadPress('9')" class="py-5 rounded-2xl bg-zinc-800 border border-white/10 hover:bg-zinc-700 active:scale-95 transition text-2xl font-bold text-white">9</button>
          <!-- 4 5 6 -->
          <button @click="numpadPress('4')" class="py-5 rounded-2xl bg-zinc-800 border border-white/10 hover:bg-zinc-700 active:scale-95 transition text-2xl font-bold text-white">4</button>
          <button @click="numpadPress('5')" class="py-5 rounded-2xl bg-zinc-800 border border-white/10 hover:bg-zinc-700 active:scale-95 transition text-2xl font-bold text-white">5</button>
          <button @click="numpadPress('6')" class="py-5 rounded-2xl bg-zinc-800 border border-white/10 hover:bg-zinc-700 active:scale-95 transition text-2xl font-bold text-white">6</button>
          <!-- 1 2 3 -->
          <button @click="numpadPress('1')" class="py-5 rounded-2xl bg-zinc-800 border border-white/10 hover:bg-zinc-700 active:scale-95 transition text-2xl font-bold text-white">1</button>
          <button @click="numpadPress('2')" class="py-5 rounded-2xl bg-zinc-800 border border-white/10 hover:bg-zinc-700 active:scale-95 transition text-2xl font-bold text-white">2</button>
          <button @click="numpadPress('3')" class="py-5 rounded-2xl bg-zinc-800 border border-white/10 hover:bg-zinc-700 active:scale-95 transition text-2xl font-bold text-white">3</button>
          <!-- . 0 ⌫ -->
          <button @click="numpadPress('.')" class="py-5 rounded-2xl bg-zinc-800 border border-white/10 hover:bg-zinc-700 active:scale-95 transition text-3xl font-extrabold text-white">.</button>
          <button @click="numpadPress('0')" class="py-5 rounded-2xl bg-zinc-800 border border-white/10 hover:bg-zinc-700 active:scale-95 transition text-2xl font-bold text-white">0</button>
          <button @click="numpadPress('backspace')" class="py-5 rounded-2xl bg-zinc-800 border border-white/10 hover:bg-zinc-700 active:scale-95 transition text-2xl text-zinc-400 flex items-center justify-center">
            <i class="ri-delete-back-2-line text-2xl"></i>
          </button>

          <!-- Clear (span 1) + Confirm (span 2) -->
          <button @click="numpadPress('clear')"
            class="py-5 rounded-2xl bg-red-500/15 ring-1 ring-red-500/30 text-red-400 font-bold text-lg hover:bg-red-500/25 active:scale-95 transition">
            Clear
          </button>
          <button @click="confirmCashNumpad"
            class="col-span-2 py-5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-zinc-900 font-bold text-xl tracking-wide shadow-lg shadow-amber-500/20 active:scale-[0.98] transition flex items-center justify-center gap-2">
            <i class="ri-check-line text-2xl"></i> Confirm
          </button>
        </div>
      </div>
    </div>
  </Teleport>

  <!-- ===== Last Orders Modal ===== -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="isLastOrdersOpen" class="fixed inset-0 z-[1100] flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="isLastOrdersOpen = false"></div>

        <!-- Panel -->
        <div class="relative bg-zinc-900 rounded-3xl border border-white/10 shadow-2xl w-full max-w-[820px] max-h-[90vh] flex flex-col overflow-hidden">

          <!-- Header -->
          <div class="flex items-center justify-between px-6 py-5 bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900 border-b border-white/10 rounded-t-3xl flex-shrink-0">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-amber-500/20 ring-1 ring-amber-500/40">
                <i class="ri-receipt-line text-amber-400 text-xl"></i>
              </div>
              <div>
                <h3 class="text-lg font-bold text-white leading-none">Last Orders</h3>
                <p class="text-[12px] text-zinc-400 mt-0.5">Most recent 20 transactions</p>
              </div>
            </div>
            <button @click="isLastOrdersOpen = false"
              class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/10 text-zinc-400 hover:bg-white/20 hover:text-white transition">
              <i class="ri-close-line text-xl"></i>
            </button>
          </div>

          <!-- Loading -->
          <div v-if="lastOrdersLoading" class="flex-1 flex items-center justify-center py-20">
            <div class="flex flex-col items-center gap-3">
              <i class="ri-loader-4-line text-4xl text-zinc-600 animate-spin"></i>
              <p class="text-zinc-500 text-[14px] font-medium">Loading orders...</p>
            </div>
          </div>

          <!-- Empty -->
          <div v-else-if="lastOrders.length === 0" class="flex-1 flex items-center justify-center py-20">
            <div class="flex flex-col items-center gap-3">
              <i class="ri-shopping-basket-line text-5xl text-zinc-700"></i>
              <p class="text-zinc-500 text-[15px] font-medium">No orders found</p>
            </div>
          </div>

          <!-- Order list -->
          <div v-else class="overflow-y-auto flex-1 px-4 py-4 space-y-2 bg-zinc-900">
            <div
              v-for="order in lastOrders"
              :key="order.id"
              class="rounded-2xl border border-white/10 overflow-hidden bg-zinc-800"
            >
              <!-- Order Row (always visible) -->
              <button
                class="w-full flex items-center gap-4 px-4 py-3 hover:bg-zinc-700/50 transition text-left"
                @click="order._expanded = !order._expanded"
              >
                <!-- Order ID -->
                <div class="w-[90px] flex-shrink-0">
                  <span class="block text-[11px] font-bold text-zinc-500 uppercase tracking-wider">Order No</span>
                  <span class="text-[14px] font-extrabold text-white leading-tight">#{{ order.order_id }}</span>
                </div>

                <!-- Status: order type + payment -->
                <div class="flex-1 min-w-0 flex items-center gap-2 flex-wrap">
                  <span
                    :class="[
                      'inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold ring-1',
                      order.order_type === 'takeaway'
                        ? 'bg-amber-500/15 ring-amber-500/40 text-amber-400'
                        : order.order_type === 'delivery'
                          ? 'bg-violet-500/15 ring-violet-500/40 text-violet-400'
                          : 'bg-blue-500/15 ring-blue-500/40 text-blue-400'
                    ]"
                  >
                    <i :class="order.order_type === 'takeaway' ? 'ri-shopping-bag-3-line' : order.order_type === 'delivery' ? 'ri-hotel-bed-line' : 'ri-restaurant-line'"></i>
                    {{ order.order_type === 'takeaway' ? 'Takeaway' : order.order_type === 'delivery' ? 'In-Room' : 'Dine-In' }}
                  </span>
                  <span
                    :class="[
                      'inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold ring-1',
                      order.payment_method?.toLowerCase() === 'card'
                        ? 'bg-blue-500/15 ring-blue-500/40 text-blue-400'
                        : 'bg-emerald-500/15 ring-emerald-500/40 text-emerald-400'
                    ]"
                  >
                    <i :class="order.payment_method?.toLowerCase() === 'card' ? 'ri-bank-card-line' : 'ri-money-dollar-circle-line'"></i>
                    {{ order.payment_method?.toLowerCase() === 'card' ? 'Card' : 'Cash' }}
                  </span>
                </div>

                <!-- Total -->
                <div class="flex-shrink-0 text-right">
                  <p class="text-[16px] font-extrabold text-amber-400 leading-none">
                    {{ calcOrderTotal(order) }}
                  </p>
                  <p class="text-[11px] text-zinc-500 mt-0.5">LKR</p>
                </div>

                <!-- Date -->
                <div class="flex-shrink-0 text-right w-[80px]">
                  <p class="text-[12px] text-zinc-300 font-semibold leading-tight">
                    {{ order.sale_date ? order.sale_date.slice(0, 10) : '—' }}
                  </p>
                </div>

                <!-- Expand indicator -->
                <i :class="['ri-arrow-down-s-line text-zinc-500 text-base flex-shrink-0 transition-transform', order._expanded ? 'rotate-180' : '']"></i>
              </button>

              <!-- Expanded items -->
              <div v-if="order._expanded" class="px-4 pb-4 border-t border-white/10 bg-zinc-900/60">
                <table class="w-full mt-3 text-[13px]">
                  <thead>
                    <tr class="text-left">
                      <th class="pb-2 font-semibold text-zinc-500 w-[40%]">Item</th>
                      <th class="pb-2 font-semibold text-zinc-500 text-center w-[15%]">Qty</th>
                      <th class="pb-2 font-semibold text-zinc-500 text-right w-[20%]">Unit</th>
                      <th class="pb-2 font-semibold text-zinc-500 text-right w-[25%]">Total</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-white/10">
                    <tr v-for="item in order.items" :key="item.name" class="hover:bg-zinc-700/30 transition">
                      <td class="py-1.5 font-medium text-zinc-300">{{ item.name }}</td>
                      <td class="py-1.5 text-center text-zinc-400">{{ item.qty }}</td>
                      <td class="py-1.5 text-right text-zinc-400">{{ Number(item.unit_price).toFixed(2) }}</td>
                      <td class="py-1.5 text-right font-bold text-white">{{ Number(item.total).toFixed(2) }}</td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr v-if="Number(order.service_charge) > 0">
                      <td colspan="3" class="pt-2 text-right text-zinc-500 text-[12px]">Service Charge ({{ order.service_charge }}%)</td>
                      <td class="pt-2 text-right text-zinc-400 text-[12px]">+{{ calcServiceChargeAmt(order).toFixed(2) }}</td>
                    </tr>
                    <tr v-if="Number(order.delivery_charge) > 0">
                      <td colspan="3" class="pt-1 text-right text-zinc-500 text-[12px]">Delivery Charge</td>
                      <td class="pt-1 text-right text-zinc-400 text-[12px]">+{{ Number(order.delivery_charge).toFixed(2) }}</td>
                    </tr>
                    <tr v-if="Number(order.bank_service_charge) > 0">
                      <td colspan="3" class="pt-1 text-right text-zinc-500 text-[12px]">Bank Charge ({{ order.bank_service_charge }}%)</td>
                      <td class="pt-1 text-right text-zinc-400 text-[12px]">+{{ calcBankChargeAmt(order).toFixed(2) }}</td>
                    </tr>
                    <tr>
                      <td colspan="3" class="pt-3 text-right font-bold text-zinc-400 text-[13px]">Total</td>
                      <td class="pt-3 text-right font-extrabold text-amber-400 text-[14px]">{{ calcOrderTotal(order) }} LKR</td>
                    </tr>
                  </tfoot>

                </table>
              </div>

            </div>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>

  <!-- ===== Expenses Modal ===== -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="isExpensesModalOpen" class="fixed inset-0 z-[1100] flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="isExpensesModalOpen = false"></div>

        <!-- Panel -->
        <div class="relative bg-zinc-900 rounded-3xl border border-white/10 shadow-2xl w-full max-w-[500px] overflow-hidden">

          <!-- Header -->
          <div class="flex items-center justify-between px-6 py-5 bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900 border-b border-white/10 rounded-t-3xl">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-rose-500/20 ring-1 ring-rose-500/40">
                <i class="ri-money-dollar-circle-line text-rose-400 text-xl"></i>
              </div>
              <div>
                <h3 class="text-lg font-bold text-white leading-none">Record Expense</h3>
                <p class="text-[12px] text-zinc-400 mt-0.5">From cash drawer</p>
              </div>
            </div>
            <button @click="isExpensesModalOpen = false"
              class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/10 text-zinc-400 hover:bg-white/20 hover:text-white transition">
              <i class="ri-close-line text-xl"></i>
            </button>
          </div>

          <!-- Form -->
          <div class="px-6 py-6 space-y-5">

            <!-- Expense Reason -->
            <div>
              <label class="block text-sm font-semibold text-zinc-300 mb-2">Expense Reason <span class="text-rose-400">*</span></label>
              <input
                v-model="newExpense.reason"
                type="text"
                placeholder="e.g., Cleaning supplies, Miscellaneous, etc."
                class="w-full px-4 py-3 bg-zinc-800 border border-white/10 rounded-xl text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
              />
            </div>

            <!-- Amount -->
            <div>
              <label class="block text-sm font-semibold text-zinc-300 mb-2">Amount (LKR) <span class="text-rose-400">*</span></label>
              <input
                v-model="newExpense.amount"
                type="number"
                placeholder="0.00"
                step="0.01"
                min="0"
                class="w-full px-4 py-3 bg-zinc-800 border border-white/10 rounded-xl text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
              />
            </div>

            <!-- Display Info -->
            <div class="bg-zinc-800/50 rounded-xl p-4 border border-white/5 space-y-2 text-[13px]">
              <div class="flex justify-between text-zinc-400">
                <span>Date:</span>
                <span class="text-white font-medium">{{ new Date().toLocaleDateString() }}</span>
              </div>
              <div class="flex justify-between text-zinc-400">
                <span>Time:</span>
                <span class="text-white font-medium">{{ new Date().toLocaleTimeString() }}</span>
              </div>
              <div class="flex justify-between text-zinc-400">
                <span>User:</span>
                <span class="text-white font-medium">{{ loggedInUser?.name ?? 'Unknown' }}</span>
              </div>
            </div>

          </div>

          <!-- Actions -->
          <div class="px-6 py-5 bg-zinc-800/30 border-t border-white/10 flex gap-3 rounded-b-3xl">
            <button
              @click="isExpensesModalOpen = false"
              class="flex-1 px-4 py-3 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-semibold transition"
            >
              Cancel
            </button>
            <button
              @click="submitExpense"
              :disabled="!newExpense.reason || !newExpense.amount || isExpenseSubmitting"
              class="flex-1 px-4 py-3 rounded-xl bg-rose-600 hover:bg-rose-500 disabled:bg-zinc-700 disabled:text-zinc-500 disabled:cursor-not-allowed text-white font-semibold transition flex items-center justify-center gap-2"
            >
              <i v-if="isExpenseSubmitting" class="ri-loader-4-line animate-spin"></i>
              <span>{{ isExpenseSubmitting ? 'Saving...' : 'Save Expense' }}</span>
            </button>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>

  <!-- ===== Ongoing Takeaway Orders Modal ===== -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-if="isOngoingTakeawayOpen" class="fixed inset-0 z-[1200] flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="isOngoingTakeawayOpen = false"></div>

        <!-- Panel -->
        <div class="relative bg-zinc-900 rounded-3xl border border-white/10 shadow-2xl w-full max-w-[860px] max-h-[88vh] flex flex-col overflow-hidden">

          <!-- Header -->
          <div class="flex items-center justify-between px-6 py-5 bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900 border-b border-white/10 rounded-t-3xl flex-shrink-0">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-amber-500/20 ring-1 ring-amber-500/40">
                <i class="ri-alarm-warning-line text-amber-400 text-xl ongoing-ring-icon"></i>
              </div>
              <div>
                <h3 class="text-lg font-bold text-white leading-none">Ongoing Takeaway Orders</h3>
                <p class="text-[12px] text-zinc-400 mt-0.5">{{ heldTakeawayOrders.length }} order{{ heldTakeawayOrders.length !== 1 ? 's' : '' }} on hold — click to restore</p>
              </div>
            </div>
            <!-- View toggle + close -->
            <div class="flex items-center gap-2">
              <button
                @click="ongoingViewMode = 'box'"
                :class="['w-9 h-9 flex items-center justify-center rounded-xl ring-1 transition', ongoingViewMode === 'box' ? 'bg-amber-500/20 ring-amber-500/50 text-amber-400' : 'bg-zinc-800 ring-white/10 text-zinc-400 hover:text-white']"
                title="Box view"
              >
                <i class="ri-layout-grid-line text-base"></i>
              </button>
              <button
                @click="ongoingViewMode = 'list'"
                :class="['w-9 h-9 flex items-center justify-center rounded-xl ring-1 transition', ongoingViewMode === 'list' ? 'bg-amber-500/20 ring-amber-500/50 text-amber-400' : 'bg-zinc-800 ring-white/10 text-zinc-400 hover:text-white']"
                title="List view"
              >
                <i class="ri-list-unordered text-base"></i>
              </button>
              <button
                @click="isOngoingTakeawayOpen = false"
                class="ml-1 w-9 h-9 flex items-center justify-center rounded-xl bg-white/10 text-zinc-400 hover:bg-white/20 hover:text-white transition"
              >
                <i class="ri-close-line text-xl"></i>
              </button>
            </div>
          </div>

          <!-- Empty state -->
          <div v-if="heldTakeawayOrders.length === 0" class="flex-1 flex items-center justify-center py-20">
            <div class="flex flex-col items-center gap-3">
              <i class="ri-shopping-bag-3-line text-5xl text-zinc-700"></i>
              <p class="text-zinc-500 text-[15px] font-medium">No held takeaway orders</p>
            </div>
          </div>

          <!-- BOX VIEW -->
          <div
            v-else-if="ongoingViewMode === 'box'"
            class="overflow-y-auto flex-1 p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 bg-zinc-950/40"
          >
            <div
              v-for="held in heldTakeawayOrders"
              :key="held.id"
              class="group relative flex flex-col gap-3 p-4 rounded-2xl bg-zinc-800 border border-white/10 hover:border-amber-500/50 hover:bg-amber-500/5 transition-all duration-200 cursor-pointer"
              @click="restoreTakeawayOrder(held)"
            >
              <!-- Trash button -->
              <button
                @click.stop="removeHeldTakeawayOrder(held.id)"
                class="absolute top-3 right-3 w-7 h-7 flex items-center justify-center rounded-lg text-zinc-600 hover:bg-red-500/15 hover:text-red-400 transition"
                title="Remove held order"
              >
                <i class="ri-delete-bin-6-line text-sm"></i>
              </button>

              <!-- Order badge + order id -->
              <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold ring-1 bg-amber-500/15 ring-amber-500/40 text-amber-400">
                  <i class="ri-shopping-bag-3-line"></i> Takeaway
                </span>
                <span class="text-[12px] font-bold text-zinc-400">#{{ held.orderId }}</span>
              </div>

              <!-- Items list (up to 4) -->
              <ul class="space-y-1 flex-1">
                <li
                  v-for="(item, idx) in held.products.slice(0, 4)"
                  :key="item.id"
                  class="flex items-center justify-between text-[13px]"
                >
                  <span class="text-zinc-300 truncate pr-2">{{ item.name }}</span>
                  <span class="text-zinc-500 flex-shrink-0">× {{ item.quantity }}</span>
                </li>
                <li v-if="held.products.length > 4" class="text-[12px] text-zinc-600 font-medium">
                  +{{ held.products.length - 4 }} more items
                </li>
              </ul>

              <!-- Footer: total + held time + customer -->
              <div class="pt-2 border-t border-white/10 flex items-end justify-between gap-2">
                <div>
                  <p class="text-[11px] text-zinc-500">
                    {{ held.customer?.name ? held.customer.name : 'Walk-in' }}
                  </p>
                  <p class="text-[11px] text-zinc-600 mt-0.5">
                    {{ new Date(held.heldAt).toLocaleTimeString([], { hour: '2-digit', minute:'2-digit' }) }}
                  </p>
                </div>
                <div class="text-right">
                  <p class="text-[18px] font-extrabold text-amber-400 leading-none">{{ calcHeldOrderTotal(held) }}</p>
                  <p class="text-[11px] text-zinc-500">LKR</p>
                </div>
              </div>

              <!-- Restore label on hover -->
              <div class="absolute inset-0 flex items-center justify-center rounded-2xl bg-amber-500/0 group-hover:bg-amber-500/8 transition pointer-events-none">
                <span class="opacity-0 group-hover:opacity-100 transition text-[13px] font-bold text-amber-400 bg-zinc-900/90 px-3 py-1 rounded-lg ring-1 ring-amber-500/40">
                  <i class="ri-arrow-go-back-line mr-1"></i>Restore Order
                </span>
              </div>
            </div>
          </div>

          <!-- LIST VIEW -->
          <div v-else class="overflow-y-auto flex-1 px-4 py-4 space-y-2 bg-zinc-950/40">
            <div
              v-for="held in heldTakeawayOrders"
              :key="held.id"
              class="group flex items-center gap-4 px-4 py-3 rounded-2xl bg-zinc-800 border border-white/10 hover:border-amber-500/50 hover:bg-amber-500/5 transition cursor-pointer"
              @click="restoreTakeawayOrder(held)"
            >
              <!-- Icon -->
              <div class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl bg-amber-500/15 ring-1 ring-amber-500/30">
                <i class="ri-shopping-bag-3-line text-amber-400"></i>
              </div>

              <!-- Order info -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="text-[14px] font-extrabold text-white">#{{ held.orderId }}</span>
                  <span class="text-[11px] text-zinc-500">{{ held.products.length }} item{{ held.products.length !== 1 ? 's' : '' }}</span>
                  <span v-if="held.customer?.name" class="text-[11px] text-zinc-400 font-medium">· {{ held.customer.name }}</span>
                </div>
                <p class="text-[12px] text-zinc-500 mt-0.5 truncate">
                  {{ held.products.map(p => p.name).join(', ') }}
                </p>
              </div>

              <!-- Held time -->
              <div class="flex-shrink-0 text-right">
                <p class="text-[12px] text-zinc-400 font-semibold">
                  {{ new Date(held.heldAt).toLocaleTimeString([], { hour: '2-digit', minute:'2-digit' }) }}
                </p>
                <p class="text-[11px] text-zinc-600">{{ new Date(held.heldAt).toLocaleDateString() }}</p>
              </div>

              <!-- Total -->
              <div class="flex-shrink-0 text-right w-[80px]">
                <p class="text-[16px] font-extrabold text-amber-400 leading-none">{{ calcHeldOrderTotal(held) }}</p>
                <p class="text-[11px] text-zinc-500">LKR</p>
              </div>

              <!-- Restore hint -->
              <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition">
                <span class="text-[12px] font-bold text-amber-400 flex items-center gap-1">
                  <i class="ri-arrow-go-back-line"></i> Restore
                </span>
              </div>

              <!-- Delete -->
              <button
                @click.stop="removeHeldTakeawayOrder(held.id)"
                class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg text-zinc-600 hover:bg-red-500/15 hover:text-red-400 transition"
                title="Remove"
              >
                <i class="ri-delete-bin-6-line text-sm"></i>
              </button>
            </div>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>

</template>

<script setup>
import Header from "@/Components/custom/Header.vue";
import Banner from "@/Components/Banner.vue";
import PosSuccessModel from "@/Components/custom/PosSuccessModel.vue";
import AlertModel from "@/Components/custom/AlertModel.vue";
import WaiterOrderAlert from "@/Components/custom/WaiterOrderAlert.vue";

import { useForm, router } from "@inertiajs/vue3";
import { ref, onMounted, computed, watch } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import axios from "axios";
import CurrencyInput from "@/Components/custom/CurrencyInput.vue";
import ColorUpdateModel from "@/Components/custom/CustomerDetailsModal.vue";
import SelectProductModel from "@/Components/custom/SelectProductModel.vue";
import { Combobox, ComboboxInput, ComboboxOptions, ComboboxOption } from "@headlessui/vue";

// fetchNextOrderId: always asks the backend for the correct next order ID
const fetchNextOrderId = async () => {
  try {
    const { data } = await axios.get('/pos/next-order-id');
    return data.nextOrderId || 'Delicasy/0001';
  } catch {
    return 'Delicasy/0001';
  }
};

/* =========================
   PROPS
========================= */
const props = defineProps({
  loggedInUser: Object,
  allcategories: Array,
  allemployee: Array,
  colors: Array,
  sizes: Array,
  delivery: Array,
  bankCharge: Array,
  serviceCharge: Array,
  owners: Array,
  nextOrderId: String,
  restaurantTables: Array,
});


const openEditModal = (color) => {
  console.log("Opening edit modal for Colors:", color);

  isEditModalOpen.value = true;
};
const isEditModalOpen = ref(false);

const handleCustomerUpdate = (updatedCustomer) => {
  customer.value = updatedCustomer;
  // Save customer to database if it has a valid name
  if (updatedCustomer.name && updatedCustomer.name.trim()) {
    saveCustomerToDB(updatedCustomer);
  }
};

const searchCustomer = async () => {
  if (!customer.value.contactNumber) {
    return;
  }
  try {
    const response = await axios.post('/customers/check', {
      contactNumber: customer.value.contactNumber
    });
    if (response.data && response.data.customer) {
      customer.value = response.data.customer;
    }
  } catch (error) {
    console.log('Customer not found');
  }
};

const saveCustomerToDB = async (customerData) => {
  try {
    const response = await axios.post('/pos/save-customer', {
      name: customerData.name,
      email: customerData.email || '',
      phone: customerData.contactNumber || '',
    });
    if (response.data && response.data.customer) {
      customer.value = response.data.customer;
      console.log('Customer saved successfully');
    }
  } catch (error) {
    console.error('Error saving customer:', error);
  }
};

/* =========================
   KOT DAILY SEQUENCE HELPERS
========================= */
const KOT_SEQUENCE_KEY = "kotDailySequence";
const toDateKey = (d = new Date()) => {
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${y}-${m}-${day}`;
};
const getKotSeqState = () => {
  try { const raw = localStorage.getItem(KOT_SEQUENCE_KEY); return raw ? JSON.parse(raw) : null; } catch { return null; }
};
const saveKotSeqState = (state) => localStorage.setItem(KOT_SEQUENCE_KEY, JSON.stringify(state));
const getNextKotNumberForToday = () => {
  const today = toDateKey();
  const state = getKotSeqState();
  if (!state || state.date !== today) {
    const fresh = { date: today, seq: 1 };
    saveKotSeqState(fresh);
    return 1;
  }
  const next = (Number(state.seq) || 0) + 1;
  saveKotSeqState({ date: today, seq: next });
  return next;
};

/* =========================
   STATE
========================= */
const product = ref(null);
const error = ref(null);
const products = ref([]);
const isSuccessModalOpen = ref(false);
const isAlertModalOpen = ref(false);
const message = ref("");
const isOpeningBalanceModalOpen = ref(false);
const openingBalanceInput = ref("");
const openingBalanceError = ref("");
const isOpeningBalanceSaving = ref(false);
const openCashDrawer = ref(null);
const isCashDrawerModalOpen = ref(false);
const closingBalanceInput = ref("");
const closingBalanceError = ref("");
const isClosingBalanceSaving = ref(false);
const appliedCoupon = ref(null);
const cash = ref(0);
const isSelectModalOpen = ref(false);
const selectedMenuType = ref("");
const order_type = ref("");
const kitchen_note = ref("");
const delivery_charge = ref("");
const service_charge = ref("");
const bank_service_charge = ref("");

// Last Orders panel
const isLastOrdersOpen = ref(false);
const lastOrders = ref([]);
const lastOrdersLoading = ref(false);

// Expenses panel
const isExpensesModalOpen = ref(false);
const newExpense = ref({ reason: '', amount: '' });
const isExpenseSubmitting = ref(false);

// Product size/quantity selection modal
const isProductSelectionModalOpen = ref(false);
const selectedProductForModal = ref(null);
const selectedSizeForModal = ref(null);
const quantityForModal = ref(1);

// ===== Ongoing Takeaway Orders (Hold / Ongoing) =====
const HELD_TAKEAWAY_KEY = 'heldTakeawayOrders';
const heldTakeawayOrders = ref(JSON.parse(localStorage.getItem(HELD_TAKEAWAY_KEY) || '[]'));
watch(heldTakeawayOrders, (val) => {
  localStorage.setItem(HELD_TAKEAWAY_KEY, JSON.stringify(val));
}, { deep: true });
const isOngoingTakeawayOpen = ref(false);
const ongoingViewMode = ref('box'); // 'box' | 'list'

const calcHeldOrderSubtotal = (heldOrder) => {
  return (heldOrder.products || []).reduce((acc, p) => {
    return acc + (parseFloat(p.selling_price) || 0) * (Number(p.quantity) || 1);
  }, 0);
};

const calcHeldOrderTotal = (heldOrder) => {
  const sub = calcHeldOrderSubtotal(heldOrder);
  const customDisc = parseFloat(heldOrder.custom_discount) || 0;
  const customDiscCalc =
    heldOrder.custom_discount_type === 'percent'
      ? (sub * customDisc) / 100
      : customDisc;
  return (sub - customDiscCalc).toFixed(2);
};

const holdTakeawayOrder = async () => {
  const t = selectedTable.value;
  if (!t || t.id !== 'default' || !t.products || t.products.length === 0) return;
  if (t.order_type !== 'takeaway') return;

  // Snapshot current order into held list
  const held = {
    id: Date.now(),
    orderId: t.orderId,
    products: JSON.parse(JSON.stringify(t.products)),
    cash: t.cash,
    custom_discount: t.custom_discount,
    custom_discount_type: t.custom_discount_type,
    kitchen_note: t.kitchen_note,
    order_type: t.order_type,
    delivery_charge: t.delivery_charge,
    service_charge: t.service_charge,
    bank_service_charge: t.bank_service_charge,
    bank_name: t.bank_name || '',
    card_last4: t.card_last4 || '',
    heldAt: new Date().toISOString(),
    customer: JSON.parse(JSON.stringify(customer.value)),
    paymentMethod: selectedPaymentMethod.value,
  };
  heldTakeawayOrders.value.push(held);

  // Clear the Live Bill for a fresh order
  const nextId = await fetchNextOrderId();
  const clearedTable = {
    id: 'default',
    number: 1,
    orderId: nextId,
    products: [],
    cash: 0.0,
    balance: 0.0,
    custom_discount: 0.0,
    custom_discount_type: 'percent',
    kitchen_note: '',
    order_type: 'takeaway',
    delivery_charge: '',
    service_charge: '',
    bank_service_charge: '',
    bank_name: '',
    card_last4: '',
    lastKotSnapshot: null,
  };
  selectedTable.value = clearedTable;
  const defaultIndex = tables.value.findIndex(t2 => t2.id === 'default');
  if (defaultIndex !== -1) tables.value[defaultIndex] = clearedTable;

  customer.value = { name: '', contactNumber: '', email: '' };
  selectedPaymentMethod.value = 'cash';
};

const restoreTakeawayOrder = async (heldOrder) => {
  const t = selectedTable.value;
  // Guard: if the live bill already has items, block
  if (t && t.id === 'default' && t.products && t.products.length > 0) {
    isAlertModalOpen.value = true;
    message.value = 'Please hold or confirm the current order before restoring a held order.';
    return;
  }

  const restoredTable = {
    id: 'default',
    number: 1,
    orderId: heldOrder.orderId,
    products: JSON.parse(JSON.stringify(heldOrder.products)),
    cash: heldOrder.cash,
    custom_discount: heldOrder.custom_discount,
    custom_discount_type: heldOrder.custom_discount_type,
    kitchen_note: heldOrder.kitchen_note,
    order_type: heldOrder.order_type,
    delivery_charge: heldOrder.delivery_charge,
    service_charge: heldOrder.service_charge,
    bank_service_charge: heldOrder.bank_service_charge,
    bank_name: heldOrder.bank_name || '',
    card_last4: heldOrder.card_last4 || '',
    balance: 0.0,
    lastKotSnapshot: null,
  };
  selectedTable.value = restoredTable;
  const defaultIndex = tables.value.findIndex(t2 => t2.id === 'default');
  if (defaultIndex !== -1) tables.value[defaultIndex] = restoredTable;

  customer.value = heldOrder.customer
    ? JSON.parse(JSON.stringify(heldOrder.customer))
    : { name: '', contactNumber: '', email: '' };
  selectedPaymentMethod.value = heldOrder.paymentMethod || 'cash';

  // Remove from held list
  heldTakeawayOrders.value = heldTakeawayOrders.value.filter(o => o.id !== heldOrder.id);
  isOngoingTakeawayOpen.value = false;
};

const removeHeldTakeawayOrder = (id) => {
  if (!confirm('Remove this held order? This cannot be undone.')) return;
  heldTakeawayOrders.value = heldTakeawayOrders.value.filter(o => o.id !== id);
};
// ===== End Ongoing Takeaway Orders =====

const openLastOrders = async () => {
  isLastOrdersOpen.value = true;
  lastOrdersLoading.value = true;
  try {
    const { data } = await axios.get('/pos/last-orders');
    lastOrders.value = (data.orders || []).map(o => ({ ...o, _expanded: false }));
  } catch {
    lastOrders.value = [];
  } finally {
    lastOrdersLoading.value = false;
  }
};

const openExpensesModal = () => {
  isExpensesModalOpen.value = true;
  newExpense.value = { reason: '', amount: '' };
};

const submitExpense = async () => {
  if (!newExpense.value.reason || !newExpense.value.amount) {
    alert('Please fill in all fields');
    return;
  }

  isExpenseSubmitting.value = true;
  try {
    const response = await axios.post('/expenses', {
      reason: newExpense.value.reason,
      amount: parseFloat(newExpense.value.amount),
      cash_drawer_id: null,
    });

    if (response.status === 201) {
      isAlertModalOpen.value = true;
      message.value = 'Expense recorded successfully';
      isExpensesModalOpen.value = false;
      newExpense.value = { reason: '', amount: '' };
    }
  } catch (err) {
    console.error('Error recording expense:', err);
    isAlertModalOpen.value = true;
    message.value = 'Failed to record expense';
  } finally {
    isExpenseSubmitting.value = false;
  }
};

// Compute the items subtotal from the stored sale items
const calcItemsSubtotal = (order) => {
  return (order.items || []).reduce((sum, i) => sum + Number(i.unit_price) * Number(i.qty), 0);
};
// Service charge amount (% of subtotal)
const calcServiceChargeAmt = (order) => {
  const subtotal = calcItemsSubtotal(order);
  const rate = parseFloat(order.service_charge) || 0;
  return subtotal * rate / 100;
};
// Bank service charge amount (% of pre-bank total)
const calcBankChargeAmt = (order) => {
  const subtotal    = calcItemsSubtotal(order);
  const discount    = parseFloat(order.discount) || 0;
  const custom      = parseFloat(order.custom_discount) || 0;
  const delivery    = order.order_type === 'delivery' ? (parseFloat(order.delivery_charge) || 0) : 0;
  const svcAmt      = calcServiceChargeAmt(order);
  const preBank     = subtotal - discount - custom + delivery + svcAmt;
  const rate        = parseFloat(order.bank_service_charge) || 0;
  return preBank * rate / 100;
};
// Final grand total used in both the row badge and the expanded footer
const calcOrderTotal = (order) => {
  const subtotal = calcItemsSubtotal(order);
  const discount  = parseFloat(order.discount) || 0;
  const custom    = parseFloat(order.custom_discount) || 0;
  const delivery  = order.order_type === 'delivery' ? (parseFloat(order.delivery_charge) || 0) : 0;
  const svcAmt    = calcServiceChargeAmt(order);
  const preBank   = subtotal - discount - custom + delivery + svcAmt;
  const bankAmt   = calcBankChargeAmt(order);
  return (preBank + bankAmt).toFixed(2);
};

const bankOptions = ref([
  "Alliance Finance Co PLC","Amana Bank","American Express Bank Ltd","Asia Asset Finance PLC",
  "Bank of Ceylon","Bank of China","CDB","Cargils Bank Ltd","Central Bank of Sri Lanka","Central Finance PLC",
  "City Bank","Commercial Bank","Commercial Credit","Cooperative Regional Rural Bank LTD",
  "DFCC Bank PLC","Deutsche Bank","Dialog Finance PLC","Fintrex Finance Limited",
  "HDFC Bank","HNB Finance PLC","HSBC","Hatton National Bank","Indian Bank","Indian Overseas Bank",
  "Kanrich Finance Bank","LB Finance","LOLC Development Finance Plc","LOLC Finance Plc","Lanka Credit and Business Finance Limited",
  "MBSL","MCB","Mercantile Investment","NDB Bank","NSB","Nations Trust Bank",
  "Peoples Leasing and Finance PLC","Pan Asia Bank","Peoples Bank","Public Bank Berhad",
  "RDB","Richard Pieris Finance","SDB","SENKADAGALA FINANCE","SMIB","Sampath Bank",
  "Sarvodaya Development Finace LTD","Seylan Bank","Singer Finance(Lanka) Bank","Siyapatha Finance PLC","Softlogic Finance PLC",
  "Standard Charted Bank","State Bank of India","Union Bank"
]);
const query = ref("");
const filteredBanks = computed(() =>
  query.value
    ? bankOptions.value.filter(b => b.toLowerCase().includes(query.value.toLowerCase()))
    : bankOptions.value
);

// Bank modal state
const isBankModalOpen = ref(false);
const bankQuery = ref("");
const filteredBanksModal = computed(() =>
  bankQuery.value
    ? bankOptions.value.filter(b => b.toLowerCase().includes(bankQuery.value.toLowerCase()))
    : bankOptions.value
);

const groupedBanksByLetter = computed(() => {
  const grouped = {};
  filteredBanksModal.value.forEach(bank => {
    const letter = bank.charAt(0).toUpperCase();
    if (!grouped[letter]) grouped[letter] = [];
    grouped[letter].push(bank);
  });
  return Object.keys(grouped).sort().reduce((acc, letter) => {
    acc[letter] = grouped[letter];
    return acc;
  }, {});
});

const selectBank = (bank) => {
  if (selectedTable.value) selectedTable.value.bank_name = bank;
  isBankModalOpen.value = false;
  bankQuery.value = "";
};

const getBankLogoName = (bankName) => {
  // Convert bank name to filename format
  // Examples: "Bank of Ceylon" -> "bank_of_ceylon_logo.jpg"
  const logoMap = {
    "Alliance Finance Co PLC": "alliance_finance_logo.jpg",
    "Amana Bank": "amana_bank_logo.jpg",
    "American Express Bank Ltd": "amex_bank_logo.jpg",
    "Asia Asset Finance PLC": "asia_asset_finance_logo.jpg",
    "Bank of Ceylon": "boc_bank_logo_001.jpg",
    "Bank of China": "bank_of_china_logo.jpg",
    "CDB": "cdb_logo.jpg",
    "Cargils Bank Ltd": "cargils_bank_logo.jpg",
    "Central Bank of Sri Lanka": "central_bank_logo.jpg",
    "Central Finance PLC": "central_finance_logo.jpg",
    "City Bank": "city_bank_logo.jpg",
    "Commercial Bank": "commercial_bank_logo.jpg",
    "Commercial Credit": "commercial_credit_logo.jpg",
    "Cooperative Regional Rural Bank LTD": "cooperative_rural_bank_logo.jpg",
    "DFCC Bank PLC": "dfcc_bank_logo.jpg",
    "Deutsche Bank": "deutsche_bank_logo.jpg",
    "Dialog Finance PLC": "dialog_finance_logo.jpg",
    "Fintrex Finance Limited": "fintrex_finance_logo.jpg",
    "HDFC Bank": "hdfc_bank_logo.jpg",
    "HNB Finance PLC": "hnb_finance_logo.jpg",
    "HSBC": "hsbc_logo.jpg",
    "Hatton National Bank": "hatton_national_bank_logo.jpg",
    "Indian Bank": "indian_bank_logo.jpg",
    "Indian Overseas Bank": "indian_overseas_bank_logo.jpg",
    "Kanrich Finance Bank": "kanrich_finance_logo.jpg",
    "LB Finance": "lb_finance_logo.jpg",
    "LOLC Development Finance Plc": "lolc_dev_finance_logo.jpg",
    "LOLC Finance Plc": "lolc_finance_logo.jpg",
    "Lanka Credit and Business Finance Limited": "lanka_credit_business_finance_logo.jpg",
    "MBSL": "mbsl_logo.jpg",
    "MCB": "mcb_logo.jpg",
    "Mercantile Investment": "mercantile_investment_logo.jpg",
    "NDB Bank": "ndb_bank_logo.jpg",
    "NSB": "nsb_logo.jpg",
    "Nations Trust Bank": "nations_trust_bank_logo.jpg",
    "Peoples Leasing and Finance PLC": "peoples_leasing_finance_logo.jpg",
    "Pan Asia Bank": "pan_asia_bank_logo.jpg",
    "Peoples Bank": "peoples_bank_logo.jpg",
    "Public Bank Berhad": "public_bank_logo.jpg",
    "RDB": "rdb_logo.jpg",
    "Richard Pieris Finance": "richard_pieris_finance_logo.jpg",
    "SDB": "sdb_logo.jpg",
    "SENKADAGALA FINANCE": "senkadagala_finance_logo.jpg",
    "SMIB": "smib_logo.jpg",
    "Sampath Bank": "sampath_bank_logo.jpg",
    "Sarvodaya Development Finace LTD": "sarvodaya_dev_finance_logo.jpg",
    "Seylan Bank": "seylan_bank_logo.jpg",
    "Singer Finance(Lanka) Bank": "singer_finance_logo.jpg",
    "Siyapatha Finance PLC": "siyapatha_finance_logo.jpg",
    "Softlogic Finance PLC": "softlogic_finance_logo.jpg",
    "Standard Charted Bank": "standard_chartered_bank_logo.jpg",
    "State Bank of India": "state_bank_india_logo.jpg",
    "Union Bank": "union_bank_logo.jpg",
  };
  return logoMap[bankName] || "bank_default_logo.jpg";
};

const checkOpenCashDrawer = async () => {
  try {
    const { data } = await axios.get('/cash-drawer/api/open');
    if (data && data.status === 'open') {
      openCashDrawer.value = data;
      isOpeningBalanceModalOpen.value = false;
      openingBalanceError.value = "";
      return true;
    }
    openCashDrawer.value = null;
    isOpeningBalanceModalOpen.value = true;
    return false;
  } catch (error) {
    openCashDrawer.value = null;
    isOpeningBalanceModalOpen.value = true;
    openingBalanceError.value = "Failed to check cash drawer. Please try again.";
    return false;
  }
};

const submitOpeningBalance = async () => {
  openingBalanceError.value = "";
  const raw = String(openingBalanceInput.value ?? "").trim();
  if (!raw) {
    openingBalanceError.value = "Opening balance is required.";
    return;
  }
  const amount = parseFloat(raw);
  if (Number.isNaN(amount) || amount < 0) {
    openingBalanceError.value = "Enter a valid amount (0 or more).";
    return;
  }

  isOpeningBalanceSaving.value = true;
  try {
    const { data } = await axios.post('/cash-drawer', {
      opening_balance: amount,
    });
    openCashDrawer.value = data?.cashDrawer || null;
    isOpeningBalanceModalOpen.value = false;
    isCashDrawerModalOpen.value = false;
    openingBalanceInput.value = "";
    await checkOpenCashDrawer();
  } catch (error) {
    openingBalanceError.value = error.response?.data?.message || "Failed to open cash drawer.";
  } finally {
    isOpeningBalanceSaving.value = false;
  }
};

const openCashDrawerModal = async () => {
  await checkOpenCashDrawer();
  isCashDrawerModalOpen.value = true;
  openingBalanceError.value = "";
  closingBalanceError.value = "";
};

const submitClosingBalance = async () => {
  closingBalanceError.value = "";
  if (!openCashDrawer.value?.id) {
    closingBalanceError.value = "No open cash drawer found.";
    return;
  }
  const raw = String(closingBalanceInput.value ?? "").trim();
  if (!raw) {
    closingBalanceError.value = "Closing balance is required.";
    return;
  }
  const amount = parseFloat(raw);
  if (Number.isNaN(amount) || amount < 0) {
    closingBalanceError.value = "Enter a valid amount (0 or more).";
    return;
  }

  isClosingBalanceSaving.value = true;
  try {
    await axios.post(`/cash-drawer/${openCashDrawer.value.id}`, {
      _method: "PUT",
      closing_balance: amount,
    });
    closingBalanceInput.value = "";
    isCashDrawerModalOpen.value = false;
    await checkOpenCashDrawer();
  } catch (error) {
    closingBalanceError.value = error.response?.data?.message || "Failed to close cash drawer.";
  } finally {
    isClosingBalanceSaving.value = false;
  }
};

// Cash numpad state
const isCashNumpadOpen = ref(false);
const cashNumpadInput = ref("0");

const openCashNumpad = () => {
  if (!selectedTable.value) return;
  const cur = Number(selectedTable.value.cash || 0);
  cashNumpadInput.value = cur > 0 ? String(cur) : "0";
  isCashNumpadOpen.value = true;
};

const numpadPress = (key) => {
  const cur = cashNumpadInput.value;
  if (key === "backspace") {
    cashNumpadInput.value = cur.length > 1 ? cur.slice(0, -1) : "0";
    return;
  }
  if (key === "clear") {
    cashNumpadInput.value = "0";
    return;
  }
  if (key === ".") {
    if (cur.includes(".")) return;
    cashNumpadInput.value = cur + ".";
    return;
  }
  // digit
  if (cur === "0") {
    cashNumpadInput.value = key;
  } else {
    if (cur.includes(".") && cur.split(".")[1].length >= 2) return;
    cashNumpadInput.value = cur + key;
  }
};

const numpadSetQuick = (amount) => {
  cashNumpadInput.value = String(amount);
};

const confirmCashNumpad = () => {
  const val = parseFloat(cashNumpadInput.value);
  if (selectedTable.value) selectedTable.value.cash = isNaN(val) ? 0 : val;
  isCashNumpadOpen.value = false;
};

/* ========= Tables persistence ========= */
const _rawSaved = JSON.parse(localStorage.getItem("tables"));
const _defaultSaved = _rawSaved?.find(t => t.id === 'default');
const _defaultTable = _defaultSaved || {
  id: "default",
  number: 0, // Live Bill — not a real table number
  orderId: props.nextOrderId || 'Delicasy/0001',
  products: [],
  cash: 0.0,
  balance: 0.0,
  custom_discount: 0.0,
  custom_discount_type: "percent",
  kitchen_note: "",
  order_type: "",
  delivery_charge: "",
  service_charge: "",
  bank_service_charge: "",
  shopping_bag_charge_enabled: true,
  lastKotSnapshot: null,
};
// Build initial table list from the DB (restaurant_tables) + restore any saved cart state
const _dbTables = (props.restaurantTables || []).map(dbTable => {
  const saved = _rawSaved?.find(t => t.id === dbTable.id);
  if (saved) return { ...saved, id: dbTable.id, number: dbTable.number };
  return {
    id: dbTable.id,
    number: dbTable.number,
    orderId: '',
    products: [],
    cash: 0.0,
    balance: 0.0,
    custom_discount: 0.0,
    custom_discount_type: "percent",
    kitchen_note: "",
    order_type: "",
    delivery_charge: "",
    service_charge: "",
    bank_service_charge: "",
    shopping_bag_charge_enabled: true,
    lastKotSnapshot: null,
    kotStatus: "pending",
  };
});
const savedTables = [_defaultTable, ..._dbTables];
const tables = ref(savedTables);
// Restore selectedTable only if it still exists in the current table list
const _resSel = JSON.parse(localStorage.getItem("selectedTable"));
const selectedTable = ref(
  (_resSel && savedTables.find(t => t.id === _resSel.id))
    ? (_resSel.id === 'default' ? _defaultTable : savedTables.find(t => t.id === _resSel.id))
    : savedTables[0]
);

/* ========= Derived lists ========= */
// const fixedTables = computed(() =>
//   tables.value.filter(t => t.id !== "default" && t.number >= 2 && t.number <= 26)
// );
const addedTables = computed(() =>
  tables.value.filter(t => t.id !== "default")
);


onMounted(async () => {
  await checkOpenCashDrawer();
  // Sync the default table's orderId with the backend value on every page load
  const defaultTable = tables.value.find(t => t.id === 'default');
  if (defaultTable && props.nextOrderId) {
    defaultTable.orderId = props.nextOrderId;
    if (selectedTable.value?.id === 'default') {
      selectedTable.value.orderId = props.nextOrderId;
    }
  }


  // Listen for waiter order selection from notification panel
  window.addEventListener('loadWaiterOrder', (event) => {
    const { tableId, products, orderId } = event.detail;

    // Find and select the table
    const table = tables.value.find(t => t.id === tableId);
    if (table) {
      // Clear existing products if this is a new order
      if (table.products.length === 0 || confirm('Replace existing items with waiter order?')) {
        table.products = [];

        // Load the waiter order products into the table
        if (products) {
          const foodItems = products.food || [];
          const barItems = products.bar || [];
          const allItems = [...foodItems, ...barItems];

          // Add all items with full product details
          allItems.forEach(item => {
            if (item && item.id) {
              table.products.push({
                ...item,
                quantity: item.quantity || 1,
                apply_discount: item.apply_discount || false,
              });
            }
          });
        }
      }

      // Update selected table reference to trigger reactivity
      selectedTable.value = table;
      localStorage.setItem("tables", JSON.stringify(tables.value));
    }
  });
});

// Keep default table orderId in sync whenever Inertia refreshes the prop
watch(() => props.nextOrderId, (val) => {
  if (!val) return;
  const defaultTable = tables.value.find(t => t.id === 'default');
  if (defaultTable && defaultTable.products.length === 0) {
    defaultTable.orderId = val;
    if (selectedTable.value?.id === 'default') selectedTable.value.orderId = val;
  }
});

watch(tables, (val) => {
  localStorage.setItem("tables", JSON.stringify(val));
}, { deep: true });

watch(selectedTable, (val) => {
  localStorage.setItem("selectedTable", JSON.stringify(val));
}, { deep: true });

/* Re-enable KOT whenever items on current table change */
watch(() => selectedTable.value?.products, () => {
  const t = selectedTable.value;
  if (!t || t.id === "default") return;
  if (!Array.isArray(t.products)) return;
  if (t.kotStatus === "sent") {
    t.kotStatus = "pending";
    localStorage.setItem("tables", JSON.stringify(tables.value));
  }
}, { deep: true });

/* =========================
   OWNER DISCOUNT (unchanged logic)
========================= */
const ownerForm = useForm({ owner_id: "" });
const ownerFetch = ref({
  owner_id: null,
  discount_value: 0,
  current_discount: 0,
  month: "",
  available: false,
  message: "",
  override_amount: 0,
});
const ownerDiscountApplied = ref(false);
const selectedOwner = computed(() => props.owners.find(o => o.id === ownerForm.owner_id) || null);
const ownerCodeValue = computed(() => selectedOwner.value ? selectedOwner.value.code : null);
const ownerBalance = computed(() => {
  const dv = Number(ownerFetch.value.discount_value || 0);
  const cd = Number(ownerFetch.value.current_discount || 0);
  return Math.max(0, dv - cd);
});
watch(() => ownerFetch.value.override_amount, (val) => {
  let n = Number(val);
  if (isNaN(n) || n < 0) {
    ownerFetch.value.override_amount = 0;
    return;
  }
  if (n > ownerBalance.value) {
    ownerFetch.value.override_amount = ownerBalance.value;
    isAlertModalOpen.value = true;
    message.value = `Override exceeds remaining balance. Max allowed is ${ownerBalance.value.toFixed(2)} LKR.`;
  }
});
const fetchOwnerDiscount = async () => {
  if (!ownerForm.owner_id) { isAlertModalOpen.value = true; message.value = "Please select an owner."; return; }
  try {
    const { data } = await axios.post("/pos/get-owner-discount", { owner_id: ownerForm.owner_id });
    ownerFetch.value = {
      owner_id: data.owner_id,
      discount_value: Number(data.discount_value || 0),
      current_discount: Number(data.current_discount || 0),
      override_amount: 0,
      month: data.month || "",
      available: !!data.available,
      message: data.message || "",
    };
  } catch (err) {
    isAlertModalOpen.value = true;
    message.value = err.response?.data?.message || "Failed to get owner discount.";
    ownerFetch.value = { owner_id: null, discount_value: 0, current_discount: 0, month: "", available: false, message: "", override_amount: 0 };
  }
};
const applyOwnerDiscount = () => {
  if (!ownerFetch.value.available) { isAlertModalOpen.value = true; message.value = ownerFetch.value.message || "No discount found for this owner."; return; }
  ownerDiscountApplied.value = true;
};
const removeOwnerDiscount = () => { ownerDiscountApplied.value = false; };
const resetOwnerState = () => {
  ownerForm.owner_id = "";
  ownerFetch.value = { owner_id: null, discount_value: 0, current_discount: 0, month: "", available: false, message: "", override_amount: 0 };
  ownerDiscountApplied.value = false;
};
const ownerDiscountValue = computed(() => ownerDiscountApplied.value ? Number(ownerFetch.value.override_amount || 0) : 0);

/* =========================
   POS LOGIC
========================= */
const discount = ref(0);
const customer = ref({ name: "", contactNumber: "", email: "" });
const employee_id = ref("");
const selectedPaymentMethod = ref("cash");
const selectedCategory = ref(null);
const productSearch = ref("");

// Watch selectedMenuType to reset category and search when menu type changes
watch(() => selectedMenuType.value, () => {
  selectedCategory.value = null;
  productSearch.value = "";
});

// Watch selectedCategory to clear search when category changes
watch(() => selectedCategory.value, () => {
  productSearch.value = "";
});

const filteredCategories = computed(() => {
  if (!selectedMenuType.value) return props.allcategories || [];

  return (props.allcategories || []).filter(category => {
    // Check category_type: 0 = Food, 1 = Bar/Beverages
    if (category.category_type !== null && category.category_type !== undefined) {
      const isFoodType = category.category_type === 0 || category.category_type === '0';
      const isBeverageType = category.category_type === 1 || category.category_type === '1';

      if (selectedMenuType.value === 'food') {
        return isFoodType;
      } else if (selectedMenuType.value === 'beverage') {
        return isBeverageType;
      }
    }

    // Fallback: check category name for keywords if category_type is missing
    const name = (category.name || '').toLowerCase();
    if (selectedMenuType.value === 'food') {
      return !name.includes('beverage') && !name.includes('drink') && !name.includes('wine') && !name.includes('beer');
    } else if (selectedMenuType.value === 'beverage') {
      return name.includes('beverage') || name.includes('drink') || name.includes('wine') || name.includes('beer');
    }
    return true;
  });
});

const categoryProducts = computed(() => {
  if (!selectedCategory.value) {
    // Show all products from filtered categories
    const allProducts = [];
    filteredCategories.value?.forEach(category => {
      if (category.products && Array.isArray(category.products)) {
        allProducts.push(...category.products);
      }
    });
    return allProducts;
  }
  // Show products from selected category
  const category = filteredCategories.value?.find(c => c.id === selectedCategory.value.id);
  if (!category || !category.products) return [];
  return category.products;
});

const filteredProducts = computed(() => {
  let filtered = categoryProducts.value;
  if (productSearch.value.trim()) {
    const query = productSearch.value.toLowerCase();
    filtered = categoryProducts.value.filter(product =>
      product.name?.toLowerCase().includes(query)
    );
  }

  // Group products by base name and merge size variants
  const grouped = {};
  filtered.forEach(product => {
    const baseName = product.name;
    if (!grouped[baseName]) {
      grouped[baseName] = {
        ...product,
        sizes: [],
        originalProduct: product
      };
    }

    // Add size variant if product has a size
    if (product.size?.id && product.size?.name) {
      const existingSize = grouped[baseName].sizes.find(s => s.id === product.size.id);
      if (!existingSize) {
        grouped[baseName].sizes.push({
          id: product.size.id,
          name: product.size.name,
          price: product.selling_price,
          product_id: product.id,
          full_product: product
        });
      }
    }
  });

  return Object.values(grouped);
});

const refreshData = async () => {
  if (selectedTable.value?.id === "default") {
    const today = new Date();
    const formattedDate = `${today.getFullYear().toString().slice(-2)}.${String(today.getMonth() + 1).padStart(2, "0")}.${String(today.getDate()).padStart(2, "0")}`;
    let existingOrderId = selectedTable.value.orderId;
    // Always fetch a fresh order ID from the backend on refresh
    existingOrderId = await fetchNextOrderId();

    const defaultTable = {
      id: "default",
      number: 1,
      orderId: existingOrderId,
      products: [],
      cash: 0.0,
      balance: 0.0,
      custom_discount: 0.0,
      custom_discount_type: "percent",
      kitchen_note: "",
      order_type: "",
      delivery_charge: "",
      service_charge: "",
      bank_service_charge: "",
      owner_discount_value: "",
      lastKotSnapshot: null,
    };
    selectedTable.value = defaultTable;

    const defaultIndex = tables.value.findIndex(t => t.id === "default");
    if (defaultIndex !== -1) tables.value[defaultIndex] = defaultTable;

    customer.value = { name: "", contactNumber: "", email: "", bdate: "" };
    appliedCoupon.value = null;
    cash.value = 0;
    selectedPaymentMethod.value = "cash";
    employee_id.value = "";

    localStorage.setItem("tables", JSON.stringify(tables.value));
    localStorage.setItem("selectedTable", JSON.stringify(selectedTable.value));

    try {
      await router.reload({
        only: ['loggedInUser', 'allcategories', 'allemployee', 'colors', 'sizes', 'delivery', 'serviceCharge', 'bankCharge'],
        preserveState: false,
        preserveScroll: false
      });
    } catch {
      window.location.reload();
    }
    resetOwnerState();
  }
};

const removeProduct = (id) => {
  if (!selectedTable.value) return;
  selectedTable.value.products = selectedTable.value.products.filter(item => item.id !== id);
  localStorage.setItem("tables", JSON.stringify(tables.value));
  localStorage.setItem("selectedTable", JSON.stringify(selectedTable.value));
};

const removeCoupon = () => { appliedCoupon.value = null; couponForm.code = ""; };

const incrementQuantity = (id) => {
  if (!selectedTable.value) return;
  const p = selectedTable.value.products.find(i => i.id === id);
  if (p) {
    p.quantity += 1;
    localStorage.setItem("tables", JSON.stringify(tables.value));
    localStorage.setItem("selectedTable", JSON.stringify(selectedTable.value));
  }
};

const decrementQuantity = (id) => {
  if (!selectedTable.value) return;
  const p = selectedTable.value.products.find(i => i.id === id);
  if (p && p.quantity > 1) {
    p.quantity -= 1;
    localStorage.setItem("tables", JSON.stringify(tables.value));
    localStorage.setItem("selectedTable", JSON.stringify(selectedTable.value));
  }
};

const addTable = async () => {
  const nums = tables.value.filter(t => t.id !== "default").map(t => t.number);
  const maxNum = nums.length ? Math.max(...nums) : 0;
  const newNumber = maxNum + 1;

  // Save to the database first, then use the real DB id
  let dbId = `added_${Date.now()}`; // fallback in case of API failure
  try {
    const { data } = await axios.post('/pos/tables', { number: newNumber });
    dbId = data.id;
  } catch (e) {
    console.error('Failed to save table to DB:', e);
  }

  const newTable = {
    id: dbId,
    number: newNumber,
    orderId: '',
    products: [],
    cash: 0.0,
    balance: 0.0,
    custom_discount: 0.0,
    custom_discount_type: "percent",
    kitchen_note: "",
    order_type: "",
    delivery_charge: "",
    service_charge: "",
    bank_service_charge: "",
    kotStatus: "pending",
    lastKotSnapshot: null,
  };

  tables.value.push(newTable);
  tables.value = [
    tables.value.find(t => t.id === "default"),
    ...tables.value.filter(t => t.id !== "default").sort((a, b) => a.number - b.number),
  ];
  await selectTable(newTable);
};

const selectTable = async (table) => {
  selectedTable.value = table;
  // Always show the true next sequential ID from the DB when switching tables
  selectedTable.value.orderId = await fetchNextOrderId();
};

/* Remove ADDED table (not fixed) — also deletes from the DB */
const removeAddedTable = (id) => {
  tables.value = tables.value.filter((t) => t.id !== id);
  if (selectedTable.value?.id === id) {
    selectedTable.value = tables.value[0];
  }
  axios.delete(`/pos/tables/${id}`).catch(e => console.error('Failed to delete table from DB:', e));
};

/* Clear selected table (kept for confirm flow) */
const removeSelectedTable = async () => {
  if (!selectedTable.value) return;
  const idx = tables.value.findIndex(t => t.id === selectedTable.value.id);
  const cleared = {
    ...tables.value[idx],
    orderId: await fetchNextOrderId(),
    products: [],
    cash: 0.0,
    balance: 0.0,
    custom_discount: 0.0,
    custom_discount_type: "percent",
    kitchen_note: "",
    order_type: "",
    delivery_charge: "",
    service_charge: "",
    bank_service_charge: "",
    lastKotSnapshot: null,
    kotStatus: "pending",
  };
  tables.value[idx] = cleared;
  selectedTable.value = cleared;
};

const handleModalOpenUpdate = async (open) => {
  isSuccessModalOpen.value = open;
  if (!open) {
    await removeSelectedTable();
    // seedFixedTables();
    selectedTable.value = tables.value[0]; // Live Bill
    cash.value = 0;
    refreshData();
  }
};

const submitOrder = async () => {
  const hasOpenDrawer = await checkOpenCashDrawer();
  if (!hasOpenDrawer) {
    isAlertModalOpen.value = true;
    message.value = "Opening balance required. Please open the cash drawer to continue.";
    return;
  }
  if (!total.value || parseFloat(total.value) <= 0) {
    isAlertModalOpen.value = true; message.value = "Total amount cannot be zero or less. Please check the bill."; return;
  }
  if (balance.value < 0) {
    isAlertModalOpen.value = true; message.value = "Cash is not enough"; return;
  }
  try {
    const response = await axios.post("/pos/submit", {
      customer: customer.value,
      products: selectedTable.value.products,
      employee_id: employee_id.value,
      paymentMethod: selectedPaymentMethod.value,
      userId: props.loggedInUser.id,
      custom_discount: customDiscCalculated.value,
      cash: selectedTable.value.cash,
      bank_name: selectedTable.value.bank_name,
      card_last4: selectedTable.value.card_last4,
      kitchen_note: selectedTable.value.kitchen_note,
      delivery_charge: selectedTable.value.delivery_charge,
      service_charge: selectedTable.value.service_charge,
      bank_service_charge: selectedTable.value.bank_service_charge,
      shopping_bag_charge: selectedTable.value.shopping_bag_charge_enabled ? 10.00 : 0,
      order_type: selectedTable.value.order_type,
      total: total.value,
      owner_id: ownerForm.owner_id || null,
      owner_discount_value: ownerDiscountValue.value,
      owner_override_amount: ownerFetch.value.override_amount || 0,
    });
    // Use the backend-confirmed order ID on the receipt
    selectedTable.value.orderId = response.data.orderId || selectedTable.value.orderId;
    isSuccessModalOpen.value = true;
    customer.value = { name: "", contactNumber: "", email: "" };
  } catch (error) {
    if (error.response?.status === 423) {
      isAlertModalOpen.value = true; message.value = error.response.data.message;
    }
    console.error("Error submitting:", error.response?.data || error.message);
  }
};

/* =========================
   Totals
========================= */
const subtotal = computed(() => {
  if (!selectedTable.value) return "0.00";
  return selectedTable.value.products
    .reduce((t, item) => t + parseFloat(item.selling_price) * item.quantity, 0)
    .toFixed(2);
});
const totalDiscount = computed(() => {
  if (!selectedTable.value) return "0.00";
  const productDiscount = selectedTable.value.products.reduce((total, item) => {
    if (item.discount && item.discount > 0 && item.apply_discount === true) {
      const discountAmount = (parseFloat(item.selling_price) - parseFloat(item.discounted_price)) * item.quantity;
      return total + discountAmount;
    }
    return total;
  }, 0);
  const couponDiscount = appliedCoupon.value ? Number(appliedCoupon.value.discount) : 0;
  const ownerDisc = ownerDiscountValue.value || 0;
  return (productDiscount + couponDiscount + ownerDisc).toFixed(2);
});
const customDiscCalculated = computed(() => {
  const subtotalValue = parseFloat(subtotal.value) || 0;
  const customDiscount = parseFloat(selectedTable.value.custom_discount) || 0;
  let customValue = 0;
  if (selectedTable.value.custom_discount_type === "percent") customValue = (subtotalValue * customDiscount) / 100;
  else if (selectedTable.value.custom_discount_type === "fixed") customValue = customDiscount;
  return customValue.toFixed(2);
});
const total = computed(() => {
  const subtotalValue = parseFloat(subtotal.value) || 0;
  const discountValue = parseFloat(totalDiscount.value) || 0;
  const customValue = parseFloat(customDiscCalculated.value) || 0;

  let deliveryChargeValue = 0;
  if (selectedTable.value.order_type === "pickup") deliveryChargeValue = parseFloat(selectedTable.value.delivery_charge) || 0;

  const serviceChargeRate = parseFloat(selectedTable.value.service_charge) || 0;
  const serviceChargeValue = (subtotalValue * serviceChargeRate) / 100;

  const shoppingBagChargeValue = selectedTable.value.shopping_bag_charge_enabled ? 10.00 : 0;

  const preBankTotal = subtotalValue - discountValue - customValue + deliveryChargeValue + serviceChargeValue + shoppingBagChargeValue;

  const bankServiceChargeRate = parseFloat(selectedTable.value.bank_service_charge) || 0;
  const bankServiceChargeAmount = (preBankTotal * bankServiceChargeRate) / 100;

  return (preBankTotal + bankServiceChargeAmount).toFixed(2);
});
const balance = computed(() => {
  if (!selectedTable.value) return 0;
  if (selectedTable.value.cash == null || selectedTable.value.cash === 0) return 0;
  return (parseFloat(selectedTable.value.cash) - parseFloat(total.value)).toFixed(2);
});

/* =========================
   Coupon & Barcode
========================= */
const form = useForm({ employee_id: "", barcode: "" });
const couponForm = useForm({ code: "" });

const submitCoupon = async () => {
  try {
    const { data } = await axios.post(route("pos.getCoupon"), { code: couponForm.code });
    const { coupon: fetchedCoupon, error: fetchedError } = data;
    if (fetchedCoupon) {
      appliedCoupon.value = fetchedCoupon;
      products.value.forEach((p) => { p.apply_discount = false; });
    } else {
      isAlertModalOpen.value = true; message.value = fetchedError; error.value = fetchedError;
    }
  } catch (err) {
    if (err.response?.status === 422) { isAlertModalOpen.value = true; message.value = err.response.data.message; }
  }
};

let barcode = ""; let timeout;
const submitBarcode = async () => {
  try {
    const { data } = await axios.post(route("pos.getProduct"), { barcode: form.barcode });
    const { product: fetchedProduct, error: fetchedError } = data;
    if (fetchedProduct) {
      if (fetchedProduct.stock_quantity < 1) { isAlertModalOpen.value = true; message.value = "Product is out of stock"; return; }
      const existing = products.value.find((i) => i.id === fetchedProduct.id);
      if (existing) existing.quantity += 1;
      else products.value.push({ ...fetchedProduct, quantity: 1, apply_discount: false });
      product.value = fetchedProduct; error.value = null;
    } else {
      isAlertModalOpen.value = true; message.value = fetchedError; error.value = fetchedError;
    }
  } catch (err) {
    if (err.response?.status === 422) { isAlertModalOpen.value = true; message.value = err.response.data.message; }
    error.value = "An unexpected error occurred. Please try again.";
  }
};
const handleScannerInput = (event) => {
  clearTimeout(timeout);
  if (event.key === "Enter") {
    form.barcode = barcode; submitBarcode(); barcode = "";
  } else {
    barcode += event.key;
  }
  timeout = setTimeout(() => { barcode = ""; }, 100);
};

/* =========================
   Item select/discounts
========================= */
const applyDiscount = (id) => {
  if (!selectedTable.value) return;
  const p = selectedTable.value.products.find((item) => item.id === id);
  if (p) p.apply_discount = true;
};
const removeDiscount = (id) => {
  if (!selectedTable.value) return;
  const p = selectedTable.value.products.find((item) => item.id === id);
  if (p) p.apply_discount = false;
};
const addProductToCart = (product) => {
  if (!selectedTable.value || !product) return;

  // Check if product has sizes available
  if (product.sizes && Array.isArray(product.sizes) && product.sizes.length > 0) {
    selectedProductForModal.value = product;
    selectedSizeForModal.value = product.sizes[0] || null;
    quantityForModal.value = 1;
    isProductSelectionModalOpen.value = true;
    return;
  }

  // For products without size variants, use the original product data
  const productToAdd = product.originalProduct || product;
  const existing = selectedTable.value.products.find((i) => i.id === productToAdd.id);
  if (existing) {
    existing.quantity += 1;
  } else {
    selectedTable.value.products.push({
      ...productToAdd,
      quantity: 1,
      apply_discount: false,
      size: productToAdd.size || null,
    });
  }

  // Save to localStorage
  localStorage.setItem("tables", JSON.stringify(tables.value));
  localStorage.setItem("selectedTable", JSON.stringify(selectedTable.value));
};

const confirmProductSelection = () => {
  if (!selectedTable.value || !selectedProductForModal.value) return;

  const groupedProduct = selectedProductForModal.value;
  const quantity = parseInt(quantityForModal.value) || 1;
  const selectedSize = selectedSizeForModal.value;

  // Get the full product data from the selected size
  const fullProduct = selectedSize?.full_product || groupedProduct.originalProduct || groupedProduct;
  const cartKey = `${fullProduct.id}_${selectedSize?.id || 'no-size'}`;
  const existing = selectedTable.value.products.find((i) => i.cart_key === cartKey);

  if (existing) {
    existing.quantity += quantity;
  } else {
    selectedTable.value.products.push({
      ...fullProduct,
      cart_key: cartKey,
      quantity: quantity,
      apply_discount: false,
      size: selectedSize ? { id: selectedSize.id, name: selectedSize.name } : null,
    });
  }

  // Save to localStorage
  localStorage.setItem("tables", JSON.stringify(tables.value));
  localStorage.setItem("selectedTable", JSON.stringify(selectedTable.value));

  // Close modal
  isProductSelectionModalOpen.value = false;
  selectedProductForModal.value = null;
  selectedSizeForModal.value = null;
  quantityForModal.value = 1;
};

const handleSelectedProducts = (selectedProducts) => {
  if (!selectedTable.value) return;
  selectedProducts.forEach((fetchedProduct) => {
    const existing = selectedTable.value.products.find((i) => i.id === fetchedProduct.id);
    if (existing) existing.quantity += 1;
    else selectedTable.value.products.push({ ...fetchedProduct, quantity: 1, apply_discount: false });
  });
};

/* =========================
   KOT helpers
========================= */
const getKotSnapshot = (table) => {
  if (!table || !Array.isArray(table.products)) return {};
  const snap = {};
  table.products.forEach(p => { snap[p.id] = Number(p.quantity) || 0; });
  return snap;
};
const getKotDelta = (table) => {
  if (!table) return [];
  const prev = table.lastKotSnapshot || {};
  const curr = getKotSnapshot(table);
  const deltas = [];
  (table.products || []).forEach(p => {
    const before = Number(prev[p.id] || 0);
    const now = Number(curr[p.id] || 0);
    const diff = now - before;
    if (diff > 0) deltas.push({ id: p.id, name: p.name, delta: diff, size: p.size?.name || "" });
  });
  return deltas;
};
const isKOTDisabled = (table) => {
  if (!table || table.id === "default") return true;
  if (!table.products || table.products.length === 0) return true;
  return getKotDelta(table).length === 0;
};
const sendKOT = (table) => {
  try {
    if (!table || table.id === "default") { isAlertModalOpen.value = true; message.value = "Select a table to print KOT."; return; }
    if (!table.products || table.products.length === 0) { isAlertModalOpen.value = true; message.value = "No items to send to kitchen for this table."; return; }

    const deltas = getKotDelta(table);
    if (deltas.length === 0) { isAlertModalOpen.value = true; message.value = "No increased items to send to kitchen."; return; }

    const kotNo = getNextKotNumberForToday();
    const now = new Date();
    const dateStr = now.toLocaleDateString();
    const timeStr = now.toLocaleTimeString();
    const productRows = deltas.map(d => `
      <tr>
        <td>${d.name}</td>
        <td style="text-align:center;">${d.delta}</td>
        <td style="text-align:center;">${d.size || "N/A"}</td>
      </tr>
    `).join("");

    const orderType =
      table.order_type === "takeaway" ? "Takeaway" :
      table.order_type === "pickup"   ? "Delivery" : "Dine In";

    const noteBlock = table.kitchen_note ? `<div class="note"><b>Kitchen Note:</b> ${table.kitchen_note}</div>` : "";

    const receiptHTML = `
      <!doctype html>
      <html>
        <head>
          <meta charset="utf-8" />
          <title>KOT</title>
          <style>
            @media print { body { margin:0; padding:0; -webkit-print-color-adjust: exact; print-color-adjust: exact; } @page { size: 80mm auto; margin:0; } }
            body { background:#fff; font-size:13px; font-weight:bold; font-family:Arial,sans-serif; margin:0; padding:10px; color:#000; }
            h1 { text-align:center; margin:0 0 10px 0; font-size:18px; font-weight:bold; }
            .kot-head { display:flex; justify-content:space-between; gap:8px; flex-wrap:wrap; margin-bottom:10px; font-size:12px; font-weight:bold; }
            .kot-head .cell { padding:4px 6px; }
            .note { border:1px dashed #000; padding:8px; margin:10px 0; font-weight:bold; font-size:12px; }
            table { width:100%; border-collapse:collapse; margin-top:8px; font-size:13px; font-weight:bold; }
            thead th { text-align:left; padding:6px 8px; font-size:12px; border-bottom:2px solid #000; font-weight:bold; }
            thead th:last-child { text-align:center; width:70px; }
            tbody tr { border-bottom:1px dashed #000; }
            tbody td { padding:6px 8px; font-size:13px; vertical-align:top; font-weight:bold; }
            tbody td:first-child { text-align:left; }
            tbody td:last-child { text-align:center; }
          </style>
        </head>
        <body>
          <h1>KOT Note - (${String(kotNo).padStart(3,'0')})</h1>
          <div class="kot-head">
            <div class="cell"><b>Date:</b> ${dateStr}</div>
            <div class="cell"><b>Time:</b> ${timeStr}</div>
            <div class="cell"><b>Order:</b> ${table.orderId}</div>
            <div class="cell"><b>Table:</b> ${table.number}</div>
            <div class="cell"><b>Type:</b> ${orderType}</div>
          </div>
          ${noteBlock}
          <table>
            <thead><tr><th>Product</th><th style="text-align:center;">Qty</th><th style="text-align:center;">Size</th></tr></thead>
            <tbody>${productRows}</tbody>
          </table>
        </body>
      </html>
    `;
    const w = window.open("", "_blank");
    if (!w) { isAlertModalOpen.value = true; message.value = "Popup blocked. Allow popups to print KOT."; return; }
    w.document.open(); w.document.write(receiptHTML); w.document.close();
    w.onload = () => {
      w.focus(); w.print(); w.close();
      table.kotStatus = "sent";
      table.lastKotSnapshot = getKotSnapshot(table);
      localStorage.setItem("tables", JSON.stringify(tables.value));
    };
  } catch (err) {
    isAlertModalOpen.value = true; message.value = "Failed to print KOT.";
    console.error("KOT print error:", err?.message || err);
  }
};

/* =========================
   Takeaway / In-Room KOT (Live Bill)
========================= */
const sendTakeawayKOT = () => {
  try {
    const table = selectedTable.value;
    if (!table || !Array.isArray(table.products) || table.products.length === 0) {
      isAlertModalOpen.value = true;
      message.value = "No items to send to kitchen.";
      return;
    }

    // Get only new/changed items (delta)
    const deltas = getKotDelta(table);
    if (deltas.length === 0) {
      isAlertModalOpen.value = true;
      message.value = "No new items to send to kitchen. All items already sent.";
      return;
    }

    const kotNo = getNextKotNumberForToday();
    const now = new Date();
    const dateStr = now.toLocaleDateString();
    const timeStr = now.toLocaleTimeString();

    const orderType =
      table.order_type === "takeaway" ? "Takeaway" :
      table.order_type === "pickup"   ? "In-Room Dining" : "Dine In";

    // Use only delta items in KOT
    const productRows = deltas.map(delta => {
      const product = table.products.find(p => p.id === delta.id);
      return `
      <tr>
        <td>${delta.name || ""}</td>
        <td style="text-align:center;">${delta.delta}</td>
        <td style="text-align:center;">${delta.size || "N/A"}</td>
      </tr>
    `;
    }).join("");

    const noteBlock = table.kitchen_note
      ? `<div class="note"><b>Kitchen Note:</b> ${table.kitchen_note}</div>`
      : "";

    const receiptHTML = `
      <!doctype html>
      <html>
        <head>
          <meta charset="utf-8" />
          <title>KOT</title>
          <style>
            @media print { body { margin:0; padding:0; -webkit-print-color-adjust: exact; print-color-adjust: exact; } @page { size: 80mm auto; margin:0; } }
            body { background:#fff; font-size:13px; font-weight:bold; font-family:Arial,sans-serif; margin:0; padding:10px; color:#000; }
            h1 { text-align:center; margin:0 0 10px 0; font-size:18px; font-weight:bold; }
            .kot-head { display:flex; justify-content:space-between; gap:8px; flex-wrap:wrap; margin-bottom:10px; font-size:12px; font-weight:bold; }
            .kot-head .cell { padding:4px 6px; }
            .note { border:1px dashed #000; padding:8px; margin:10px 0; font-weight:bold; font-size:12px; }
            table { width:100%; border-collapse:collapse; margin-top:8px; font-size:13px; font-weight:bold; }
            thead th { text-align:left; padding:6px 8px; font-size:12px; border-bottom:2px solid #000; font-weight:bold; }
            thead th:last-child { text-align:center; width:70px; }
            tbody tr { border-bottom:1px dashed #000; }
            tbody td { padding:6px 8px; font-size:13px; vertical-align:top; font-weight:bold; }
            tbody td:first-child { text-align:left; }
            tbody td:last-child { text-align:center; }
          </style>
        </head>
        <body>
          <h1>KOT Note - (${String(kotNo).padStart(3, "0")})</h1>
          <div class="kot-head">
            <div class="cell"><b>Date:</b> ${dateStr}</div>
            <div class="cell"><b>Time:</b> ${timeStr}</div>
            <div class="cell"><b>Order:</b> ${table.orderId}</div>
            <div class="cell"><b>Type:</b> ${orderType}</div>
          </div>
          ${noteBlock}
          <table>
            <thead><tr><th>Product</th><th style="text-align:center;">Qty</th><th style="text-align:center;">Size</th></tr></thead>
            <tbody>${productRows}</tbody>
          </table>
        </body>
      </html>
    `;

    const w = window.open("", "_blank");
    if (!w) {
      isAlertModalOpen.value = true;
      message.value = "Popup blocked. Allow popups to print KOT.";
      return;
    }
    w.document.open();
    w.document.write(receiptHTML);
    w.document.close();
    w.onload = () => {
      w.focus();
      w.print();
      w.close();
      // Update snapshot after KOT is sent
      table.lastKotSnapshot = getKotSnapshot(table);
      localStorage.setItem("tables", JSON.stringify(tables.value));
    };
  } catch (err) {
    isAlertModalOpen.value = true;
    message.value = "Failed to print KOT.";
    console.error("Takeaway KOT print error:", err?.message || err);
  }
};


/* =========================
   Print Bill Only
========================= */
const printBillOnly = () => {
  try {
    const t = selectedTable.value;
    if (!t || !Array.isArray(t.products) || t.products.length === 0) {
      isAlertModalOpen.value = true; message.value = "No items to print."; return;
    }
    const fmt = (n) => (Number(n || 0)).toFixed(2);
    const orderType = t.order_type === "takeaway" ? "Takeaway" : t.order_type === "pickup" ? "Delivery" : "Dine In";
    const itemRows = t.products.map(p => {
      const price = parseFloat(p.selling_price) || 0;
      const qty   = Number(p.quantity || 1);
      const discP = Number(p.discount || 0);
      const line  = p.apply_discount ? (price * qty * (100 - discP)) / 100 : price * qty;
      const sizeText = p.size?.name ? ` (${p.size.name})` : "";
      const discText = p.apply_discount ? ` (${discP}% off)` : "";
      return `
        <tr>
          <td>${p.name || ""}${sizeText}${discText}</td>
          <td style="text-align:center;">${qty}</td>
          <td style="text-align:right;">${fmt(price)}</td>
          <td style="text-align:right;">${fmt(line)}</td>
        </tr>
      `;
    }).join("");

    const sub        = Number(subtotal.value || 0);
    const discTotal  = Number(totalDiscount.value || 0);
    const customDisc = Number(customDiscCalculated.value || 0);
    const deliv      = t.order_type === "pickup" ? Number(t.delivery_charge || 0) : 0;
    const svcRate    = Number(t.service_charge || 0);
    const svcAmt     = (sub * svcRate) / 100;
    const preBank    = sub - discTotal - customDisc + deliv + svcAmt;
    const bankRate   = Number(t.bank_service_charge || 0);
    const bankAmt    = (preBank * bankRate) / 100;
    const grandTotal = Number(total.value || 0);
    const cashVal    = Number(t.cash || 0);
    const balVal     = Number(balance.value || 0);
    const couponVal  = appliedCoupon.value ? Number(appliedCoupon.value.discount || 0) : 0;
    const ownerVal   = Number(ownerDiscountValue.value || 0);

    const bankLine = selectedPaymentMethod.value === "card" ? `
      <div class="row"><span><b>Bank:</b> ${t.bank_name || "-"}</span></div>
      <div class="row"><span><b>Card:</b> ****${t.card_last4 || "-"}</span></div>` : "";
    const cashLine = selectedPaymentMethod.value === "cash" ? `
      <div class="divider"></div>
      <div class="row"><div>Cash Paid</div><div>${fmt(cashVal)}</div></div>
      <div class="row"><div>Balance</div><div>${fmt(balVal)}</div></div>` : "";
    const couponLine = couponVal > 0 ? `<div class="row"><div>Coupon</div><div>-${fmt(couponVal)}</div></div>` : "";
    const ownerLine  = ownerVal  > 0 ? `<div class="row"><div>Owner Disc</div><div>-${fmt(ownerVal)}</div></div>` : "";
    const customLine = customDisc> 0 ? `<div class="row"><div>Custom Disc</div><div>-${fmt(customDisc)}</div></div>` : "";
    const delivLine  = deliv     > 0 ? `<div class="row"><div>Delivery</div><div>+${fmt(deliv)}</div></div>` : "";
    const svcLine    = svcRate   > 0 ? `<div class="row"><div>Service (${fmt(svcRate)}%)</div><div>+${fmt(svcAmt)}</div></div>` : "";
    const bankSvcLine= bankRate  > 0 ? `<div class="row"><div>Bank Fee (${fmt(bankRate)}%)</div><div>+${fmt(bankAmt)}</div></div>` : "";

    const receiptHTML = `
      <!doctype html>
      <html>
        <head>
          <meta charset="utf-8" />
          <title>Bill</title>
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
              font-size: 16px;
              font-family: 'Courier New', monospace;
              margin: 0;
              padding: 8px 6px;
              color: #000 !important;
              width: 80mm;
              box-sizing: border-box;
              font-weight: 900;
            }
            h1 {
              text-align: center;
              margin: 0 0 12px 0;
              font-size: 19px;
              font-weight: 900;
              color: #000 !important;
            }
            .row {
              display: flex;
              justify-content: space-between;
              margin: 7px 0;
              word-break: break-word;
              color: #000 !important;
              font-weight: 900;
              font-size: 15px;
            }
            .badge {
              border: 2px solid #000;
              padding: 6px 8px;
              text-align: center;
              margin: 10px 0;
              font-weight: 900;
              font-size: 14px;
              color: #000 !important;
            }
            table {
              width: 100%;
              border-collapse: collapse;
              margin: 10px 0;
              font-size: 14px;
              font-weight: 900;
            }
            th, td {
              padding: 6px 3px;
              color: #000 !important;
              font-weight: 900;
            }
            th {
              text-align: left;
              font-weight: 900;
              border-bottom: 2px solid #000;
              padding-bottom: 8px;
              font-size: 14px;
            }
            tbody tr {
              border-bottom: 1px solid #ddd;
            }
            tbody tr:last-child {
              border-bottom: none;
            }
            td {
              text-align: right;
              color: #000 !important;
            }
            td:first-child {
              text-align: left;
              max-width: 35mm;
              word-wrap: break-word;
              font-weight: 700;
            }
            .totals {
              margin-top: 10px;
              border-top: 1px solid #999;
              padding-top: 10px;
              font-size: 15px;
              font-weight: 900;
            }
            .grand {
              font-weight: 900;
              font-size: 16px;
              border-top: 2px solid #000;
              padding-top: 10px;
              margin-top: 8px;
              color: #000 !important;
            }
            .note {
              border-top: 1px solid #000;
              padding-top: 10px;
              margin-top: 12px;
              font-weight: 900;
              font-size: 14px;
              color: #000 !important;
            }
            .divider {
              border-top: 2px solid #000;
              margin: 10px 0;
            }
            b {
              font-weight: 900;
              color: #000 !important;
            }
            span {
              color: #000 !important;
              font-weight: 900;
            }
          </style>
        </head>
        <body>
          <h1>Customer Bill</h1>
          <div class="badge">
            ${t.id === 'default' ? 'Temporary Bill' : `Table: ${t.number}`} | ${orderType}
          </div>
          <div class="row">
            <span><b>Date:</b> ${new Date().toLocaleDateString()}</span>
          </div>
          <div class="row">
            <span><b>Time:</b> ${new Date().toLocaleTimeString()}</span>
          </div>
          <div class="row">
            <span><b>Order #:</b> ${t.orderId}</span>
          </div>
          <div class="row">
            <span><b>Cashier:</b> ${props.loggedInUser?.name ?? "-"}</span>
          </div>
          ${customer.value?.name ? `<div class="row"><span><b>Customer:</b> ${customer.value.name}</span></div>` : ""}
          ${bankLine}
          <div class="divider"></div>
          <table>
            <thead>
              <tr>
                <th style="width: 40%; text-align: left;">Item</th>
                <th style="width: 12%; text-align: center;">Qty</th>
                <th style="width: 18%; text-align: right;">Price</th>
                <th style="width: 15%; text-align: right;">Total</th>
              </tr>
            </thead>
            <tbody>${itemRows}</tbody>
          </table>
          <div class="divider"></div>
          <div class="totals">
            <div class="row"><div>Subtotal</div><div>${fmt(sub)}</div></div>
            ${discTotal > 0 ? `<div class="row"><div>Discount</div><div>-${fmt(discTotal)}</div></div>` : ""}
            ${couponLine}${ownerLine}${customLine}${delivLine}${svcLine}${bankSvcLine}
            <div class="grand row"><div>TOTAL</div><div>${fmt(grandTotal)}</div></div>
          </div>
          ${cashLine}
          ${t.kitchen_note ? `<div class="note">Note: ${t.kitchen_note}</div>` : ""}
          <div style="text-align: center; margin-top: 8px; font-size: 9px;">
            Thank you!
          </div>
        </body>
      </html>
    `;
    const w = window.open("", "_blank");
    if (!w) { isAlertModalOpen.value = true; message.value = "Popup blocked. Allow popups to print the bill."; return; }
    w.document.open(); w.document.write(receiptHTML); w.document.close();
    w.onload = () => { w.focus(); w.print(); w.close(); };
  } catch (err) {
    console.error("Bill print error:", err);
    isAlertModalOpen.value = true; message.value = "Failed to print the bill.";
  }
};
</script>

<style scoped>
/* ---- Ongoing Orders ring animation ---- */
@keyframes bell-ring {
  0%, 100% { transform: rotate(0deg); }
  10%       { transform: rotate(14deg); }
  20%       { transform: rotate(-10deg); }
  30%       { transform: rotate(14deg); }
  40%       { transform: rotate(-8deg); }
  50%       { transform: rotate(10deg); }
  60%       { transform: rotate(0deg); }
}
.ongoing-ring-icon {
  display: inline-block;
  animation: bell-ring 2s ease-in-out infinite;
  transform-origin: top center;
}
@keyframes ring-pulse {
  0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
  50%       { box-shadow: 0 0 0 6px rgba(245, 158, 11, 0); }
}
.ring-anim {
  animation: ring-pulse 2s ease-in-out infinite;
}
/* ---- End animation ---- */

/* POS page global touch-friendly styles */
.pos-page select,
.pos-page input[type="text"] {
  min-height: 44px;
  font-size: 15px;
}

.pos-page select:focus,
.pos-page input[type="text"]:focus {
  outline: none;
}

/* Smooth card interactions */
.pos-page .ring-1 {
  transition: box-shadow 0.2s ease, border-color 0.2s ease;
}

/* CurrencyInput styling override */
.pos-page :deep(.currency-input) {
  background: rgb(248 250 252);
  border: none;
  border-radius: 0.75rem;
  padding: 0.625rem 0.75rem;
  font-size: 15px;
  color: rgb(51 65 85);
  box-shadow: inset 0 0 0 1px rgb(226 232 240);
  min-height: 44px;
}

.pos-page :deep(.currency-input:focus) {
  box-shadow: inset 0 0 0 2px rgb(96 165 250);
  outline: none;
}

/* Scrollbar for bill section */
.pos-page ::-webkit-scrollbar {
  width: 6px;
}

.pos-page ::-webkit-scrollbar-track {
  background: transparent;
}

.pos-page ::-webkit-scrollbar-thumb {
  background: rgb(203 213 225);
  border-radius: 3px;
}

.pos-page ::-webkit-scrollbar-thumb:hover {
  background: rgb(148 163 184);
}
</style>
