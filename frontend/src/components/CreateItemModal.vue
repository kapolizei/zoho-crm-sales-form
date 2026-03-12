<template>
  <div class="zo-modal-overlay" @click.self="$emit('close')">
    <div class="ci-modal">
      <div class="ci-modal__header">
        <h2 class="ci-modal__title">New Item</h2>
        <button class="ci-modal__close" @click="$emit('close')">✕</button>
      </div>

      <div class="ci-modal__body">
        <div class="ci-top">
          <div class="ci-top__fields">
            <div class="ci-field">
              <label class="ci-label ci-label--required">Name</label>
              <input
                v-model="form.name"
                class="ci-input"
                :class="{ 'ci-input--error': errors.name }"
                placeholder="Item name"
              />
              <span class="ci-error" v-if="errors.name">{{ errors.name }}</span>
            </div>

            <div class="ci-field">
              <label class="ci-label"
                >Type <span class="ci-info">ⓘ</span></label
              >
              <div class="ci-radio-group">
                <label class="ci-radio">
                  <input
                    type="radio"
                    v-model="form.product_type"
                    value="goods"
                  />
                  <span class="ci-radio__dot"></span>
                  Goods
                </label>
                <label class="ci-radio">
                  <input
                    type="radio"
                    v-model="form.product_type"
                    value="service"
                  />
                  <span class="ci-radio__dot"></span>
                  Service
                </label>
              </div>
            </div>
          </div>

          <div class="ci-image-upload">
            <div class="ci-image-box ci-image-box--main">
              <div v-if="!previewImage" class="ci-image-box__placeholder">
                <span class="ci-image-box__icon">↑</span>
                <span>Upload Image</span>
              </div>
              <img v-else :src="previewImage" class="ci-image-box__preview" />
              <input
                type="file"
                accept="image/*"
                @change="onImageUpload"
                class="ci-image-box__input"
              />
            </div>
          </div>
        </div>

        <div class="ci-divider"></div>

        <div class="ci-section">
          <div class="ci-section__title">Item Details</div>

          <div class="ci-field ci-field--row">
            <label class="ci-label">Item Type</label>
            <div class="ci-toggle-group">
              <button
                class="ci-toggle"
                :class="{ active: form.item_type === 'single' }"
                @click="form.item_type = 'single'"
              >
                <span class="ci-toggle__check">✓</span> Single Item
              </button>
            </div>
          </div>

          <div class="ci-fields-row">
            <div class="ci-field">
              <label class="ci-label ci-label--required"
                >Unit <span class="ci-info">ⓘ</span></label
              >
              <select
                v-model="form.unit"
                class="ci-select"
                :class="{ 'ci-input--error': errors.unit }"
              >
                <option value="">Select or type to add</option>
                <option value="pcs">pcs</option>
                <option value="kg">kg</option>
                <option value="l">l</option>
                <option value="m">m</option>
                <option value="box">box</option>
              </select>
              <span class="ci-error" v-if="errors.unit">{{ errors.unit }}</span>
            </div>

            <div class="ci-field">
              <label class="ci-label">SKU <span class="ci-info">ⓘ</span></label>
              <input
                v-model="form.sku"
                class="ci-input"
                placeholder="e.g. SKU-0001"
              />
            </div>
          </div>
        </div>

        <div class="ci-divider"></div>

        <div class="ci-section">
          <div class="ci-section__header">
            <input
              type="checkbox"
              v-model="form.sales_enabled"
              id="sales_cb"
              class="ci-checkbox"
            />
            <label
              for="sales_cb"
              class="ci-section__title ci-section__title--check"
              >Sales Information</label
            >
          </div>

          <div v-if="form.sales_enabled" class="ci-fields-row">
            <div class="ci-field">
              <label class="ci-label ci-label--required">Selling Price</label>
              <div class="ci-price-wrap">
                <span class="ci-price-currency">UAH</span>
                <input
                  v-model.number="form.rate"
                  type="number"
                  min="0"
                  step="0.01"
                  class="ci-input ci-input--price"
                  :class="{ 'ci-input--error': errors.rate }"
                  placeholder="0.00"
                />
              </div>
              <span class="ci-error" v-if="errors.rate">{{ errors.rate }}</span>
            </div>

            <div class="ci-field">
              <label class="ci-label">Description</label>
              <textarea
                v-model="form.description"
                class="ci-textarea"
                placeholder="Description for sales..."
              ></textarea>
            </div>
          </div>
        </div>

        <div class="ci-divider"></div>

        <div class="ci-section">
          <div class="ci-section__header">
            <input
              type="checkbox"
              v-model="form.purchase_enabled"
              id="purchase_cb"
              class="ci-checkbox"
            />
            <label
              for="purchase_cb"
              class="ci-section__title ci-section__title--check"
              >Purchase Information</label
            >
          </div>

          <div v-if="form.purchase_enabled" class="ci-fields-row">
            <div class="ci-field">
              <label class="ci-label ci-label--required">Cost Price</label>
              <div class="ci-price-wrap">
                <span class="ci-price-currency">UAH</span>
                <input
                  v-model.number="form.purchase_rate"
                  type="number"
                  min="0"
                  step="0.01"
                  class="ci-input ci-input--price"
                  placeholder="0.00"
                />
              </div>
            </div>

            <div class="ci-field">
              <label class="ci-label">Preferred Vendor</label>
              <select
                v-model="form.vendor_id"
                class="ci-select"
                :class="{ 'ci-input--error': errors.vendor_id }"
              >
                <span class="ci-error" v-if="errors.vendor_id">{{
                  errors.vendor_id
                }}</span>
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

      <div class="ci-modal__footer">
        <button
          class="ci-btn ci-btn--primary"
          @click="submit"
          :disabled="loading"
        >
          <span v-if="loading" class="ci-spinner"></span>
          {{ loading ? "Saving..." : "Save" }}
        </button>
        <button class="ci-btn ci-btn--cancel" @click="$emit('close')">
          Cancel
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import "../assets/itemModal.css";
import { API } from '../config.js'

const props = defineProps({ vendors: { type: Array, default: () => [] } });
const emit = defineEmits(["close", "created"]);
const selectedVendor = ref(null);

const loading = ref(false);
const errors = ref({});
const previewImage = ref(null);

const form = ref({
  name: "",
  product_type: "goods",
  item_type: "single",
  unit: "",
  sku: "",
  rate: null,
  description: "",
  purchase_rate: null,
  vendor_id: "",
  sales_enabled: true,
  purchase_enabled: true,
});

function onImageUpload(e) {
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = () => (previewImage.value = reader.result);
  reader.readAsDataURL(file);
}

function validate() {
  errors.value = {};
  if (!form.value.name) errors.value.name = "Required";
  if (!form.value.unit) errors.value.unit = "Required";
  if (form.value.sales_enabled && !form.value.rate)
    errors.value.rate = "Required";
  if (form.value.purchase_enabled && !form.value.vendor_id)
    errors.value.vendor_id = "Required";
  return Object.keys(errors.value).length === 0;
}

async function submit() {
  if (!validate()) return;
  loading.value = true;
  try {
    const vendor = props.vendors.find(
      (v) => v.contact_id === form.value.vendor_id,
    );

    const payload = {
      name: form.value.name,
      product_type: form.value.product_type,
      item_type: "inventory",
      unit: form.value.unit,
      sku: form.value.sku || undefined,
      rate: form.value.rate,
      description: form.value.description || undefined,
      purchase_rate: form.value.purchase_enabled
        ? form.value.purchase_rate
        : undefined,
      vendor_id: form.value.purchase_enabled
        ? form.value.vendor_id || undefined
        : undefined,
      vendor_name: form.value.purchase_enabled
        ? vendor?.contact_name
        : undefined,
    };

    const res = await fetch(`${API}/items`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(payload),
    });
    if (!res.ok) throw new Error(await res.text());
    const data = await res.json();
    emit("created", data.item ?? data);
    emit("close");
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
}
</script>
