<template>
  <div class="zo-modal-overlay" @click.self="$emit('close')">
    <div class="zo-modal">
      <div class="zo-modal__title">New Customer</div>
      <div class="zo-modal__body">
        <div class="zo-modal-field">
          <label>Full Name *</label
          ><input
            v-model="newCustomer.contact_name"
            class="zo-input"
            placeholder="John Doe"
            :class="{ 'field-input--error': createErrors.contact_name }"
          />
        </div>
        <div class="zo-modal-field">
          <label>Email *</label
          ><input
            v-model="newCustomer.email"
            class="zo-input"
            placeholder="john@example.com"
            :class="{ 'field-input--error': createErrors.email }"
          />
        </div>
        <div class="zo-modal-field">
          <label>Phone *</label
          ><input
            v-model="newCustomer.phone"
            class="zo-input"
            placeholder="+380..."
            :class="{ 'field-input--error': createErrors.phone }"
          />
        </div>
        <div class="zo-modal-field">
          <label>Company *</label
          ><input
            v-model="newCustomer.company_name"
            class="zo-input"
            placeholder="Company name"
            :class="{ 'field-input--error': createErrors.company_name }"
          />
        </div>
      </div>
      <div class="zo-modal__footer">
        <button
          class="zo-btn zo-btn--primary"
          @click="createCustomer"
          :disabled="loadingCustomer"
        >
          {{ loadingCustomer ? "Creating..." : "Save" }}
        </button>
        <button class="zo-btn zo-btn--cancel" @click="$emit('close')">
          Cancel
        </button>
      </div>
    </div>
  </div>

  <div v-if="submitStatus" :class="['zo-toast', submitStatus.type]">
    {{ submitStatus.message }}
  </div>
</template>

<script setup>
import { ref } from "vue";
import "../assets/customerModal.css";
import { API } from "../config.js";

const emit = defineEmits(["close", "created"]);

const loadingCustomer = ref(false);
const createErrors = ref({});
const submitStatus = ref(null);
const newCustomer = ref({
  contact_name: "",
  email: "",
  phone: "",
  company_name: "",
  contact_type: "",
});

async function createCustomer() {
  createErrors.value = {};
  if (newCustomer.value.contact_name.length < 3)
    createErrors.value.contact_name = "Required";
  if (!newCustomer.value.email) createErrors.value.email = "Required";
  if (!newCustomer.value.phone) createErrors.value.phone = "Required";
  if (!newCustomer.value.company_name)
    createErrors.value.company_name = "Required";
  if (Object.keys(createErrors.value).length) return;

  loadingCustomer.value = true;

  const payload = {
    contact_type: "customer",
    contact_name: newCustomer.value.contact_name,
    company_name: newCustomer.value.company_name,
    contact_persons: [
      {
        email: newCustomer.value.email,
        phone: newCustomer.value.phone,
      },
    ],
  };

  try {
    const res = await fetch(`${API}/contacts`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(payload),
    });

    const data = await res.json();

    if (!res.ok) {
      const errorMessage =
        data.error || data.message || "Failed to create customer";
      submitStatus.value = {
        type: "error",
        message: errorMessage,
      };
      console.error(errorMessage);
      return;
    }

    const created = data.contact ?? data;
    emit("created", created);
    submitStatus.value = {
      type: "success",
      message: "Customer created successfully!",
    };

    newCustomer.value = {
      contact_name: "",
      email: "",
      phone: "",
      company_name: "",
    };
  } catch (e) {
    const errorMessage = e.message || "An error occurred";
    submitStatus.value = {
      type: "error",
      message: errorMessage,
    };
    console.error(e);
  } finally {
    loadingCustomer.value = false;
    setTimeout(() => {
      if (submitStatus.value?.type === "success") {
        emit("close");
      }
    }, 2000);
  }
}
</script>
