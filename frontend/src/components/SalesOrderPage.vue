<template>
  <div class="zo-page">
    <div class="zo-form">

      <div v-if="!authorized" class="auth-wrap">
        <div class="auth-box">
          <p class="auth-text">Zoho is not connected. Please authorize first.</p>
          <a href="http://localhost:8000/zoho/auth" class="auth-btn">
            Connect Zoho
          </a>
        </div>
      </div>

      <template v-else>
        <div class="zo-form__header">
          <div class="zo-form__title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
              <line x1="3" y1="6" x2="21" y2="6" />
              <path d="M16 10a4 4 0 01-8 0" />
            </svg>
            New Sales Order
          </div>
          <div class="zo-form__header-actions">
            <button class="zo-icon-btn">⚙</button>
            <button class="zo-icon-btn">✕</button>
          </div>
        </div>

        <div class="zo-form__body">
          <div class="zo-field-row">
            <label class="zo-label zo-label--required">Customer Name</label>
            <div class="zo-customer-wrap">
              <div class="zo-customer-select-group">
                <div
                  class="zo-customer-select"
                  @click="toggleCustomerDrop"
                  :class="{
                    active: showCustomerDrop,
                    'zo-customer-select--error': errors.customer_id,
                  }"
                >
                  <span v-if="!selectedCustomer" class="zo-customer-select__placeholder">Select or add a customer</span>
                  <span v-else class="zo-customer-select__value">{{ selectedCustomer.contact_name }}</span>
                  <span class="zo-customer-select__arrow">{{ showCustomerDrop ? "▲" : "▼" }}</span>
                </div>
                <Transition name="expand">
                  <div v-if="selectedCustomer" class="selected_form_details">
                    <span v-if="selectedCustomer.email" class="selected_form_details__item">
                      <span class="selected_form_details__label">Email</span>
                      {{ selectedCustomer.email }}
                    </span>
                    <span v-if="selectedCustomer.phone" class="selected_form_details__item">
                      <span class="selected_form_details__label">Phone</span>
                      {{ selectedCustomer.phone }}
                    </span>
                    <span v-if="selectedCustomer.company_name" class="selected_form_details__item">
                      <span class="selected_form_details__label">Company</span>
                      {{ selectedCustomer.company_name }}
                    </span>
                  </div>
                </Transition>
              </div>

              <div v-if="showCustomerDrop" class="zo-customer-drop">
                <div class="zo-customer-drop__search">
                  <input
                    v-model="customerSearch"
                    ref="customerSearchInput"
                    placeholder="Search"
                    class="zo-customer-drop__input"
                    autocomplete="off"
                  />
                </div>
                <div class="zo-customer-drop__list">
                  <div
                    v-for="c in filteredCustomers"
                    :key="c.contact_id"
                    class="zo-customer-drop__item"
                    :class="{ selected: selectedCustomer?.contact_id === c.contact_id }"
                    @click="selectCustomer(c)"
                  >
                    <div class="zo-avatar">{{ c.contact_name[0].toUpperCase() }}</div>
                    <div class="zo-customer-drop__item-info">
                      <div class="zo-customer-drop__item-name">{{ c.contact_name }}</div>
                      <div v-if="c.email" class="zo-customer-drop__item-sub">{{ c.email }}</div>
                      <div v-else-if="c.company_name" class="zo-customer-drop__item-sub">{{ c.company_name }}</div>
                    </div>
                  </div>
                </div>
                <div class="zo-customer-drop__new" @click="openCreateCustomerModal">
                  ＋ New Customer
                </div>
              </div>
            </div>
            <span class="zo-error" v-if="errors.customer_id">{{ errors.customer_id }}</span>
          </div>

          <div class="zo-fields-grid">
            <div class="zo-field-row">
              <label class="zo-label zo-label--required">Sales Order#</label>
              <div class="zo-input-wrap">
                <input v-model="form.salesorder_number" class="zo-input" :class="{ 'field-input--error': errors.salesorder_number }", placeholder="SO-00001" />
                <span class="zo-input-icon">⚙</span>
              </div>
                <span class="zo-error" v-if="errors.salesorder_number">{{ errors.salesorder_number }}</span>
            </div>
            <div class="zo-field-row">
              <label class="zo-label">Reference#</label>
              <input v-model="form.reference_number" class="zo-input" />
            </div>
            <div class="zo-field-row">
              <label class="zo-label zo-label--required">Sales Order Date</label>
              <input v-model="form.date" type="date" class="zo-input" />
            </div>
            <div class="zo-field-row">
              <label class="zo-label">Expected Shipment Date</label>
              <input v-model="form.shipment_date" type="date" class="zo-input" />
            </div>
            <div class="zo-field-row">
              <label class="zo-label">Payment Terms</label>
              <select v-model="form.payment_terms" class="zo-select">
                <option value="0">Due on Receipt</option>
                <option value="15">Net 15</option>
                <option value="30">Net 30</option>
                <option value="45">Net 45</option>
                <option value="60">Net 60</option>
              </select>
            </div>
            <div class="zo-field-row">
              <label class="zo-label">Delivery Method</label>
              <select v-model="form.delivery_method" class="zo-select">
                <option value="">Select a delivery method or type to add</option>
                <option value="courier">Courier</option>
                <option value="pickup">Pickup</option>
              </select>
            </div>
            <div class="zo-field-row">
              <label class="zo-label">Salesperson</label>
              <select v-model="form.salesperson_id" class="zo-select">
                <option value="">Select or Add Salesperson</option>
              </select>
            </div>
          </div>

          <ItemsTable
            :form="form"
            :vendors="vendors"
            @item-search="onItemSearch"
            @qty-change="onQtyChange"
            @select-item="selectItem"
            @add-line="addLine"
            @remove-line="removeLine"
            @bulk-confirm="onBulkConfirm"
            @duplicate-item="onDuplicateItem"
          />

          <div class="zo-form__footer">
            <div class="zo-form__footer-left">
              <button class="zo-btn zo-btn--draft" :disabled="loading">Save as Draft</button>
              <div class="zo-save-primary">
                <button class="zo-btn zo-btn--primary" @click="submitForm('confirm')" :disabled="loading">
                  <span v-if="loading" class="zo-spinner"></span>
                  {{ loading ? "Saving..." : "Save and Send" }}
                </button>
                <button class="zo-btn zo-btn--arrow">▾</button>
              </div>
              <button class="zo-btn zo-btn--cancel">Cancel</button>
            </div>
            <div class="zo-total-summary">
              <span>Total Amount: <strong>UAH {{ formatMoney(total) }}</strong></span>
              <span>Total Quantity: <strong>{{ totalQty }}</strong></span>
            </div>
          </div>
        </div>

        <div v-if="submitStatus" :class="['zo-toast', submitStatus.type]">
          {{ submitStatus.message }}
        </div>
      </template>

    </div>
  </div>

  <CreateCustomerModal
    v-if="openCreateCustomer"
    @close="openCreateCustomer = false"
    @created="onCustomerCreated"
  />

</template>

<script setup>
import { ref, computed, onMounted, nextTick, onUnmounted } from "vue";
import CreateCustomerModal from "./CreateCustomerModal.vue";
import ItemsTable from "./ItemsTable.vue";
import "../assets/salesOrderPage.css";
import { API } from "../config.js";

const loading = ref(false);
const errors = ref({});
const submitStatus = ref(null);
const openCreateCustomer = ref(false);
const authorized = ref(false);

const showCreateItem = ref(false);
const activeLineIdx = ref(0);

const customers = ref([]);
const vendors = ref([]);
const customerSearch = ref("");
const showCustomerDrop = ref(false);
const selectedCustomer = ref(null);
const customerSearchInput = ref(null);

function onCustomerCreated(customer) {
  customers.value.push(customer);
  selectCustomer(customer);
}

function openCreateCustomerModal() {
  showCustomerDrop.value = false;
  openCreateCustomer.value = true;
}

function formatMoney(val) {
  return (val || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

const filteredCustomers = computed(() => {
  if (!customerSearch.value) return customers.value;
  const q = customerSearch.value.toLowerCase();
  return customers.value.filter(
    (c) =>
      c.contact_name.toLowerCase().includes(q) ||
      (c.email || "").toLowerCase().includes(q),
  );
});

const subtotal = computed(() =>
  form.value.line_items.reduce((s, l) => s + (l.quantity * l.rate || 0), 0),
);
const discountAmount = computed(
  () => (subtotal.value * (form.value.discount || 0)) / 100,
);
const total = computed(
  () => subtotal.value - discountAmount.value + (form.value.adjustment || 0),
);
const totalQty = computed(() =>
  form.value.line_items.reduce((s, l) => s + (l.quantity || 0), 0),
);
const poLines = computed(() =>
  form.value.line_items.filter((l) => l.need_po && l.item_id),
);

let lineId = 0;
function emptyLine() {
  return {
    id: ++lineId,
    item_id: null,
    itemSearch: "",
    itemResults: [],
    showDrop: false,
    quantity: 1,
    rate: 0,
    description: "",
    tax_id: "",
    available_stock: null,
    need_po: false,
    vendor_id: "",
  };
}

const form = ref({
  customer_id: null,
  salesorder_number: "",
  reference_number: "",
  date: new Date().toISOString().split("T")[0],
  shipment_date: "",
  payment_terms: "0",
  delivery_method: "",
  salesperson_id: "",
  notes: "",
  terms: "",
  discount: 0,
  adjustment: 0,
  line_items: [emptyLine()],
});

async function loadCustomers() {
  try {
    const res = await fetch(`${API}/contacts?type=customer`);
    const data = await res.json();
    customers.value = data.contacts ?? [];
  } catch (e) {
    console.error(e);
  }
}

async function loadVendors() {
  try {
    const res = await fetch(`${API}/contacts?type=vendor`);
    const data = await res.json();
    vendors.value = data.contacts ?? [];
  } catch (e) {
    console.error(e);
  }
}

function onDuplicateItem() {
  submitStatus.value = { type: 'error', message: '✗ Item already in order' }
  setTimeout(() => submitStatus.value = null, 3000)
}

async function toggleCustomerDrop() {
  showCustomerDrop.value = !showCustomerDrop.value;
  if (showCustomerDrop.value) {
    await nextTick();
    customerSearchInput.value?.focus();
  }
}

function selectCustomer(c) {
  selectedCustomer.value = c;
  form.value.customer_id = c.contact_id;
  showCustomerDrop.value = false;
  customerSearch.value = "";
  delete errors.value.customer_id;
}

const itemTimers = {};
async function onItemSearch(idx) {
  clearTimeout(itemTimers[idx]);
  const line = form.value.line_items[idx];
  if (!line.itemSearch) {
    line.itemResults = [];
    return;
  }
  itemTimers[idx] = setTimeout(async () => {
    try {
      const res = await fetch(`${API}/items?search=${line.itemSearch}`);
      const data = await res.json();
      line.itemResults = (data.items ?? []).slice(0, 10);
      line.showDrop = true;
    } catch (e) {}
  }, 300);
}

function selectItem(idx, item) {
  const line = form.value.line_items[idx];
  line.item_id = item.item_id;
  line.itemSearch = item.name;
  line.rate = item.rate ?? 0;
  line.available_stock = item.available_stock ?? item.stock_on_hand ?? 0;
  line.showDrop = false;
  line.itemResults = [];
  if (line.quantity > line.available_stock) line.need_po = true;
}

function onQtyChange(idx) {
  const line = form.value.line_items[idx];
  if (line.available_stock !== null)
    line.need_po = line.quantity > line.available_stock;
}

function addLine() {
  form.value.line_items.push(emptyLine());
}
function removeLine(idx) {
  if (form.value.line_items.length > 1) form.value.line_items.splice(idx, 1);
}

function validate() {
  errors.value = {};
  if (!form.value.customer_id) errors.value.customer_id = "Customer Name is required";
  if (!form.value.line_items.some((l) => l.item_id)) errors.value.line_items = "Add at least one item";
  if (!form.value.salesorder_number) errors.value.salesorder_number = "Required";

  return Object.keys(errors.value).length === 0;
}

async function submitForm(mode = "confirm") {
  if (!validate()) return;
  loading.value = true;
  console.log("poLines:", poLines.value);

  try {
    const soRes = await fetch(`${API}/sales`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        customer_id: form.value.customer_id,
        salesorder_number: form.value.salesorder_number || undefined,
        date: form.value.date,
        shipment_date: form.value.shipment_date || undefined,
        payment_terms: parseInt(form.value.payment_terms),
        notes: form.value.notes || undefined,
        line_items: form.value.line_items
          .filter((l) => l.item_id)
          .map((l) => ({
            item_id: l.item_id,
            quantity: l.quantity,
            rate: l.rate,
            description: l.description || undefined,
          })),
      }),
    });
    if (!soRes.ok) throw new Error(await soRes.text());

    let message = "Sales Order created";

    if (poLines.value.length) {
      let allPoOk = true;
      for (const po of groupPoByVendor(poLines.value)) {
        const puRes = await fetch(`${API}/purchase`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(po),
        });
        if (!puRes.ok) allPoOk = false;
      }
      message = allPoOk
        ? "Sales Order and Purchase Orders created"
        : "Sales Order created, some Purchase Orders failed";
    }

    submitStatus.value = { type: "success", message };
    setTimeout(() => (submitStatus.value = null), 3000);
    resetForm();
  } catch (e) {
    submitStatus.value = { type: "error", message: "✗ " + e.message };
    setTimeout(() => (submitStatus.value = null), 4000);
  } finally {
    loading.value = false;
  }
}

function groupPoByVendor(lines) {
  const map = {};
  lines.forEach((l) => {
    if (!l.vendor_id) return;
    if (!map[l.vendor_id])
      map[l.vendor_id] = { vendor_id: l.vendor_id, line_items: [] };
    map[l.vendor_id].line_items.push({
      item_id: l.item_id,
      quantity: Math.max(1, l.quantity - (l.available_stock ?? 0)),
      rate: l.rate,
    });
  });
  return Object.values(map);
}

function resetForm() {
  form.value = {
    customer_id: null,
    salesorder_number: "",
    reference_number: "",
    date: new Date().toISOString().split("T")[0],
    shipment_date: "",
    payment_terms: "0",
    delivery_method: "",
    salesperson_id: "",
    notes: "",
    terms: "",
    discount: 0,
    adjustment: 0,
    line_items: [emptyLine()],
  };
  selectedCustomer.value = null;
  errors.value = {};
}

async function authStatus() {
  const res = await fetch(`${API}/crm/status`, {
    headers: { Accept: "application/json" },
  });
  const data = await res.json();
  authorized.value = data.authorized;
}

const customerHandler = (e) => {
  if (!e.target.closest(".zo-customer-wrap")) showCustomerDrop.value = false;
};

const itemSearchHandler = (e) => {
  if (!e.target.closest(".zo-item-search-wrap"))
    form.value.line_items.forEach((l) => (l.showDrop = false));
};

onMounted(() => {
  authStatus();
  loadCustomers();
  loadVendors();
  document.addEventListener("click", customerHandler);
  document.addEventListener("click", itemSearchHandler);
});

onUnmounted(() => {
  document.removeEventListener("click", customerHandler);
  document.removeEventListener("click", itemSearchHandler);
});
</script>
