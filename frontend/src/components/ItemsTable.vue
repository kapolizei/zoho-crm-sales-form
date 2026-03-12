<template>
  <div class="zo-item-table">
    <div class="zo-item-table__header">
      <span class="zo-item-table__title">Item Table</span>
      <button class="zo-bulk-btn">⊕ Bulk Actions</button>
    </div>
    <div class="zo-item-table__cols">
      <div class="col-drag"></div>
      <div class="col-img"></div>
      <div class="col-details">ITEM DETAILS</div>
      <div class="col-qty">QUANTITY</div>
      <div class="col-rate">RATE</div>
      <div class="col-tax">TAX</div>
      <div class="col-amount">AMOUNT</div>
      <div class="col-actions"></div>
    </div>
    <draggable
      v-model="form.line_items"
      item-key="id"
      handle=".zo-drag-handle"
      animation="150"
    >
      <template #item="{ element: line, index: idx }">
        <div :key="idx" class="zo-item-row">
          <div class="col-drag"><span class="zo-drag-handle">⠿</span></div>

          <div class="col-img"><div class="zo-item-img">🖼</div></div>

          <div class="col-details">
            <div class="zo-item-search-wrap">
              <input
                v-model="line.itemSearch"
                @input="onItemSearch(idx)"
                @click="line.showDrop = true"
                class="zo-item-input"
                placeholder="Type to select an item."
              />
              <div
                v-if="
                  line.showDrop && (line.itemResults?.length || line.itemSearch)
                "
                class="zo-item-drop"
              >
                <div
                  v-for="item in line.itemResults"
                  :key="item.item_id"
                  class="zo-item-drop__item"
                  @mousedown.prevent="selectItem(idx, item)"
                >
                  <div class="zo-item-drop__left">
                    <div class="zo-item-drop__name">{{ item.name }}</div>
                    <div class="zo-item-drop__sub">
                      <span v-if="item.sku">SKU: {{ item.sku }} </span> Rate:
                      UAH{{ item.rate }}
                    </div>
                  </div>
                  <div class="zo-item-drop__right">
                    <div class="zo-item-drop__stock-label">Stock on Hand</div>
                    <div
                      class="zo-item-drop__stock-val"
                      :class="
                        (item.available_stock ?? 0) > 0
                          ? 'stock-ok'
                          : 'stock-zero'
                      "
                    >
                      {{ item.available_stock ?? 0 }} {{ item.unit }}
                    </div>
                  </div>
                </div>

                <div
                  class="zo-item-drop__new"
                  @mousedown.prevent="openCreateItem(idx)"
                >
                  ＋ Add New Item
                </div>
              </div>
            </div>
            <input
              v-model="line.description"
              class="zo-item-desc"
              placeholder="Item description"
            />
            <div class="zo-reporting-tags">🏷 Reporting Tags ▾</div>
          </div>

          <div class="col-qty">
            <input
              v-model.number="line.quantity"
              @input="onQtyChange(idx)"
              type="number"
              min="1"
              class="zo-cell-input zo-cell-input--right"
            />
          </div>
          <div class="col-rate">
            <input
              v-model.number="line.rate"
              type="number"
              min="0"
              step="0.01"
              class="zo-cell-input zo-cell-input--right"
            />
          </div>
          <div class="col-tax">
            <select v-model="line.tax_id" class="zo-tax-select">
              <option value="">Select a Tax</option>
              <option value="exempt">Tax Exempt</option>
            </select>
          </div>
          <div class="col-amount">
            <span class="zo-amount">{{
              formatMoney(line.quantity * line.rate)
            }}</span>
          </div>
          <div class="col-actions">
            <button
              class="zo-row-delete"
              @click="removeLine(idx)"
              :disabled="form.line_items.length === 1"
            >
              ✕
            </button>
          </div>

          <div
            v-if="
              line.item_id &&
              line.available_stock !== null &&
              line.quantity > line.available_stock
            "
            class="zo-stock-warning"
          >
            Only {{ line.available_stock }} in stock — PO will be created
            <label class="zo-po-toggle"
              ><input type="checkbox" v-model="line.need_po" /> Create PO</label
            >
          </div>
        </div>
      </template>
    </draggable>

    <div class="zo-item-table__add">
      <button class="zo-add-row" @click="addLine">＋ Add New Row</button>
      <button class="zo-items-bulk" @click="showBulkItems = true">
        ＋ Add Items In Bulk
      </button>
    </div>
  </div>

  <div v-if="poLines.length" class="zo-po-summary">
    <div class="zo-po-summary__title">
      <h1>⚠ Purchase Orders Required</h1>
      <p1 class="zo-po-summary__p1"
        >Before creating a Purchase Request - select the vendor. Otherwise it
        will not be created
      </p1>
    </div>
    <div class="zo-po-summary__table">
      <div class="zo-po-summary__head">
        <div>Item</div>
        <div>Ordered</div>
        <div>In Stock</div>
        <div>To Buy</div>
        <div>Vendor</div>
      </div>
      <div v-for="(line, idx) in poLines" :key="idx" class="zo-po-summary__row">
        <div>{{ line.itemSearch }}</div>
        <div>{{ line.quantity }}</div>
        <div class="text-red">{{ line.available_stock ?? 0 }}</div>
        <div class="text-orange">
          {{ Math.max(0, line.quantity - (line.available_stock ?? 0)) }}
        </div>
        <div>
          <select v-model="line.vendor_id" class="zo-tax-select">
            <option value="">Select vendor</option>
            <option
              v-for="v in vendors"
              :key="v.contact_id"
              :value="v.contact_id"
            >
              {{ v.contact_name }}
            </option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <div class="zo-bottom-grid">
    <div class="zo-notes">
      <div class="zo-notes__section">
        <label class="zo-notes__label">Customer Notes</label>
        <textarea
          v-model="form.notes"
          class="zo-notes__input"
          placeholder="Enter any notes to be displayed in the transaction"
        ></textarea>
      </div>
      <div class="zo-notes__section">
        <label class="zo-notes__label">Terms &amp; Conditions</label>
        <textarea
          v-model="form.terms"
          class="zo-notes__input"
          placeholder="Enter the terms and conditions of your business"
        ></textarea>
      </div>
    </div>
    <div class="zo-totals">
      <div class="zo-totals__row">
        <span>Sub Total</span>
        <span>{{ formatMoney(subtotal) }}</span>
      </div>
      <div class="zo-totals__row">
        <span>Discount</span>
        <div class="zo-discount-wrap">
          <input
            :disabled="true"
            v-model.number="form.discount"
            type="number"
            min="0"
            max="100"
            class="zo-discount-input"
          />
          <span>%</span>
        </div>
        <span>-{{ formatMoney(discountAmount) }}</span>
      </div>
      <div class="zo-totals__row">
        <span>Adjustment</span>
        <input
          :disabled="true"
          v-model.number="form.adjustment"
          type="number"
          step="0.01"
          class="zo-adj-input"
        />
        <span>{{ formatMoney(form.adjustment || 0) }}</span>
      </div>
      <div class="zo-totals__row zo-totals__row--total">
        <span>Total ( UAH )</span>
        <span>{{ formatMoney(total) }}</span>
      </div>
    </div>
  </div>

  <CreateItemModal
    v-if="showCreateItem"
    :vendors="vendors"
    @close="showCreateItem = false"
    @created="onItemCreated"
  />

  <BulkItemsModal
    v-if="showBulkItems"
    @close="showBulkItems = false"
    @confirm="onBulkConfirm"
  />
</template>

<script setup>
import draggable from "vuedraggable";
import CreateItemModal from "./CreateItemModal.vue";
import BulkItemsModal from "./BulkItemsModal.vue";
import "../assets/itemsTable.css";
import { ref, computed } from "vue";
import { API } from "../config.js";

const props = defineProps({
  form: {
    type: Object,
    required: true,
  },
  vendors: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits([
  "item-search",
  "qty-change",
  "select-item",
  "add-line",
  "remove-line",
  "bulk-confirm",
]);

function formatMoney(val) {
  return (val || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

const showCreateItem = ref(false);
const activeLineIdx = ref(0);

const showBulkItems = ref(false);

function onBulkConfirm(items) {
  props.form.line_items = props.form.line_items.filter((l) => l.item_id);

  items.forEach((item) => {
    const alreadyExists = props.form.line_items.some(
      (l) => l.item_id === item.item_id,
    );

    if (alreadyExists) {
      emit("duplicate-item");
      return;
    }

    const line = emptyLine();
    line.item_id = item.item_id;
    line.itemSearch = item.name;
    line.rate = item.rate ?? 0;
    line.quantity = item.quantity;
    line.available_stock = item.available_stock ?? 0;
    if (line.quantity > line.available_stock) line.need_po = true;
    props.form.line_items.push(line);
  });
}

const subtotal = computed(() =>
  props.form.line_items.reduce((s, l) => s + (l.quantity * l.rate || 0), 0),
);
const discountAmount = computed(
  () => (subtotal.value * (props.form.discount || 0)) / 100,
);
const total = computed(
  () => subtotal.value - discountAmount.value + (props.form.adjustment || 0),
);
const totalQty = computed(() =>
  props.form.line_items.reduce((s, l) => s + (l.quantity || 0), 0),
);
const poLines = computed(() =>
  props.form.line_items.filter((l) => l.need_po && l.item_id),
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

const itemTimers = {};
function onItemSearch(idx) {
  clearTimeout(itemTimers[idx]);
  const line = props.form.line_items[idx];
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
  emit("item-search", idx);
}

function selectItem(idx, item) {
  const line = props.form.line_items[idx];

  const alreadyExists = props.form.line_items.some(
    (l) => l.item_id === item.item_id,
  );

  if (alreadyExists) {
    emit("duplicate-item");
    return;
  }

  line.item_id = item.item_id;
  line.itemSearch = item.name;
  line.rate = item.rate ?? 0;
  line.available_stock = item.available_stock ?? item.stock_on_hand ?? 0;
  line.showDrop = false;
  line.itemResults = [];
  if (line.quantity > line.available_stock) line.need_po = true;
  emit("select-item", idx, item);
}

function onQtyChange(idx) {
  const line = props.form.line_items[idx];
  if (line.available_stock !== null)
    line.need_po = line.quantity > line.available_stock;
  emit("qty-change", idx);
}

function addLine() {
  props.form.line_items.push(emptyLine());
}

function removeLine(idx) {
  if (props.form.line_items.length > 1) {
    props.form.line_items.splice(idx, 1);
  }
}

function openCreateItem(idx) {
  activeLineIdx.value = idx;
  showCreateItem.value = true;
}

function onItemCreated(item) {
  selectItem(activeLineIdx.value, item);
}
</script>
