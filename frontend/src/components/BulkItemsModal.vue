<template>
  <div class="zo-modal-overlay" @click.self="$emit('close')">
    <div class="bulk-modal">

      <div class="bulk-modal__header">
        <h2 class="bulk-modal__title">Add Items in Bulk</h2>
        <button class="bulk-modal__close" @click="$emit('close')">✕</button>
      </div>

      <div class="bulk-modal__body">

        <div class="bulk-left">
          <input
            v-model="search"
            @input="onSearch"
            class="bulk-search"
            placeholder="Type to search or scan the barcode of the item"
            autofocus
          />

          <div class="bulk-list">
            <div v-if="loading" class="bulk-list__empty">Loading...</div>
            <div v-else-if="!filteredItems.length" class="bulk-list__empty">No items found</div>

            <div
              v-for="item in filteredItems"
              :key="item.item_id"
              class="bulk-list__item"
              :class="{ selected: isSelected(item) }"
              @click="toggleItem(item)"
            >
              <div class="bulk-list__item-info">
                <div class="bulk-list__item-name" :class="{ 'bulk-list__item-name--selected': isSelected(item) }">
                  {{ item.name }}
                </div>
                <div class="bulk-list__item-sub">
                  <span v-if="item.sku">SKU: {{ item.sku }}</span>
                  <span> Rate: UAH{{ item.rate }}</span>
                </div>
              </div>
              <div class="bulk-list__item-stock">
                <div class="bulk-list__stock-label">Stock on Hand</div>
                <div class="bulk-list__stock-val" :class="(item.available_stock ?? 0) > 0 ? 'stock-ok' : 'stock-zero'">
                  {{ item.available_stock ?? 0 }} {{ item.unit }}
                </div>
              </div>
              <div class="bulk-list__item-check">
                <span v-if="isSelected(item)" class="bulk-check">✓</span>
              </div>
            </div>
          </div>
        </div>

        <div class="bulk-right">
          <div class="bulk-right__header">
            <span class="bulk-right__title">Selected Items</span>
            <span class="bulk-right__count">{{ selected.length }}</span>
            <span class="bulk-right__total">Total Quantity: {{ totalQty }}</span>
          </div>

          <div class="bulk-right__list">
            <div v-if="!selected.length" class="bulk-right__empty">No items selected</div>
            <div v-for="s in selected" :key="s.item_id" class="bulk-selected-row">
              <span class="bulk-selected-row__name">
                <span v-if="s.sku" class="bulk-selected-row__sku">[{{ s.sku }}]</span>
                {{ s.name }}
              </span>
              <div class="bulk-qty">
                <button class="bulk-qty__btn" @click="decQty(s)" :disabled="s.quantity <= 1">−</button>
                <input
                  v-model.number="s.quantity"
                  type="number"
                  min="1"
                  class="bulk-qty__input"
                  @input="s.quantity = Math.max(1, s.quantity)"
                />
                <button class="bulk-qty__btn" @click="s.quantity++">+</button>
              </div>
            </div>
          </div>
        </div>

      </div>

      <div class="bulk-modal__footer">
        <button class="bulk-btn bulk-btn--primary" @click="confirm" :disabled="!selected.length">
          Add Items
        </button>
        <button class="bulk-btn bulk-btn--cancel" @click="$emit('close')">Cancel</button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import '../assets/bulkModal.css'
import { API } from '../config.js'

const emit = defineEmits(['close', 'confirm'])

const allItems = ref([])
const loading = ref(false)
const search = ref('')
const selected = ref([])

const filteredItems = computed(() => {
  if (!search.value) return allItems.value
  const q = search.value.toLowerCase()
  return allItems.value.filter(i => 
    i.name.toLowerCase().includes(q) || (i.sku || '').toLowerCase().includes(q)
  )
})

const totalQty = computed(() => selected.value.reduce((s, i) => s + i.quantity, 0))

async function loadItems() {
  loading.value = true
  try {
    const res = await fetch(`${API}/items`)
    const data = await res.json()
    
    allItems.value = data.items ?? []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

let searchTimer = null
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {}, 300)
}

function isSelected(item) {
  return selected.value.some(s => s.item_id === item.item_id)
}

function toggleItem(item) {
  const idx = selected.value.findIndex(s => s.item_id === item.item_id)
  if (idx === -1) {
    selected.value.push({ ...item, quantity: 1 })
  } else {
    selected.value.splice(idx, 1)
  }
}

function decQty(s) {
  if (s.quantity > 1) s.quantity--
}

function confirm() {
  emit('confirm', selected.value.map(s => ({
    item_id: s.item_id,
    name: s.name,
    quantity: s.quantity,
    rate: s.rate,
    available_stock: s.available_stock ?? 0,
    unit: s.unit,
    sku: s.sku,
  })))
  emit('close')
}

onMounted(loadItems)
</script>
