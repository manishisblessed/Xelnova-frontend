<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import SectionMain from '@/components/SectionMain.vue'
import CardBox from '@/components/CardBox.vue'
import BaseButton from '@/components/BaseButton.vue'
import FormField from '@/components/FormField.vue'
import FormControl from '@/components/FormControl.vue'
import { mdiArrowLeft, mdiCheck, mdiClose, mdiDownload, mdiTag } from '@mdi/js'
import { computed } from 'vue'

const props = defineProps({
  brand: {
    type: Object,
    required: true
  },
  resourceNeo: {
    type: Object,
    required: true
  }
})

const rejectForm = useForm({
  rejection_reason: ''
})

const statusColor = computed(() => {
  const colors = {
    pending: 'warning',
    approved: 'success',
    rejected: 'danger'
  }
  return colors[props.brand.approval_status] || 'info'
})

const canApprove = computed(() => props.brand.approval_status !== 'approved')
const canReject = computed(() => props.brand.approval_status !== 'rejected')

const approve = () => {
  if (confirm('Are you sure you want to approve this brand?')) {
    useForm({}).post(route('sellerBrand.approve', props.brand.id))
  }
}

const reject = () => {
  if (rejectForm.rejection_reason.trim() === '') {
    alert('Please provide a rejection reason')
    return
  }
  if (confirm('Are you sure you want to reject this brand?')) {
    rejectForm.post(route('sellerBrand.reject', props.brand.id))
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <Head :title="`Brand: ${brand.brand_name}`" />
    <SectionMain>
      <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <Link :href="route('sellerBrand.index')" class="text-blue-600 hover:text-blue-800">
            <BaseButton :icon="mdiArrowLeft" label="Back to Brands" color="info" outline small />
          </Link>
          <h1 class="text-3xl font-bold">{{ brand.brand_name }}</h1>
        </div>
        <div class="flex gap-2">
          <BaseButton
            v-if="canApprove"
            :icon="mdiCheck"
            label="Approve"
            color="success"
            @click="approve"
          />
          <BaseButton
            v-if="canReject"
            :icon="mdiClose"
            label="Reject"
            color="danger"
            @click="() => { /* Show reject form */ }"
          />
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
          <CardBox>
            <div class="mb-4 pb-4 border-b">
              <h2 class="text-xl font-bold mb-2">Brand Information</h2>
            </div>

            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Brand Name</label>
                <p class="text-lg font-semibold">{{ brand.brand_name }}</p>
              </div>

              <div v-if="brand.description">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <p class="text-gray-800">{{ brand.description }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Seller</label>
                <p class="text-gray-800">{{ brand.seller?.business_name || 'N/A' }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <span
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                  :class="{
                    'bg-yellow-100 text-yellow-800': brand.approval_status === 'pending',
                    'bg-green-100 text-green-800': brand.approval_status === 'approved',
                    'bg-red-100 text-red-800': brand.approval_status === 'rejected'
                  }"
                >
                  {{ brand.approval_status.charAt(0).toUpperCase() + brand.approval_status.slice(1) }}
                </span>
              </div>

              <div v-if="brand.rejection_reason">
                <label class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason</label>
                <div class="bg-red-50 border border-red-200 rounded p-3">
                  <p class="text-red-800">{{ brand.rejection_reason }}</p>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Submitted</label>
                <p class="text-gray-800">{{ new Date(brand.created_at).toLocaleString() }}</p>
              </div>

              <div v-if="brand.approved_at">
                <label class="block text-sm font-medium text-gray-700 mb-1">Approved At</label>
                <p class="text-gray-800">{{ new Date(brand.approved_at).toLocaleString() }}</p>
              </div>
            </div>
          </CardBox>

          <!-- Reject Form -->
          <CardBox v-if="canReject && brand.approval_status !== 'rejected'">
            <div class="mb-4 pb-4 border-b">
              <h2 class="text-xl font-bold mb-2 text-red-600">Reject Brand</h2>
            </div>

            <FormField label="Rejection Reason" help="Provide a clear reason for rejection">
              <FormControl
                v-model="rejectForm.rejection_reason"
                type="textarea"
                placeholder="Enter rejection reason..."
                :error="rejectForm.errors.rejection_reason"
              />
            </FormField>

            <div class="mt-4">
              <BaseButton
                :icon="mdiClose"
                label="Reject Brand"
                color="danger"
                @click="reject"
                :disabled="rejectForm.processing"
              />
            </div>
          </CardBox>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Logo -->
          <CardBox v-if="brand.logo_path">
            <div class="mb-4 pb-4 border-b">
              <h2 class="text-lg font-bold">Brand Logo</h2>
            </div>
            <img
              :src="`/storage/${brand.logo_path}`"
              :alt="brand.brand_name"
              class="w-full h-auto rounded-lg border"
            />
          </CardBox>

          <!-- Proof Document -->
          <CardBox>
            <div class="mb-4 pb-4 border-b">
              <h2 class="text-lg font-bold">Proof Document</h2>
            </div>
            <div v-if="brand.proof_document_path">
              <a
                :href="route('seller.brands.download-proof', brand.id)"
                target="_blank"
                class="flex items-center gap-2 text-blue-600 hover:text-blue-800"
              >
                <BaseButton :icon="mdiDownload" label="Download Proof" color="info" outline small />
              </a>
              <p class="text-xs text-gray-500 mt-2">Click to view/download the proof document</p>
            </div>
            <p v-else class="text-gray-500">No proof document uploaded</p>
          </CardBox>

          <!-- Seller Info -->
          <CardBox v-if="brand.seller">
            <div class="mb-4 pb-4 border-b">
              <h2 class="text-lg font-bold">Seller Details</h2>
            </div>
            <div class="space-y-2 text-sm">
              <div>
                <span class="font-medium">Business:</span>
                <p>{{ brand.seller.business_name }}</p>
              </div>
              <div>
                <span class="font-medium">Email:</span>
                <p>{{ brand.seller.email }}</p>
              </div>
              <div>
                <span class="font-medium">Phone:</span>
                <p>{{ brand.seller.phone || 'N/A' }}</p>
              </div>
              <div class="pt-2">
                <Link :href="route('seller.show', brand.seller.id)" class="text-blue-600 hover:text-blue-800 text-sm">
                  View Seller Profile →
                </Link>
              </div>
            </div>
          </CardBox>
        </div>
      </div>
    </SectionMain>
  </LayoutAuthenticated>
</template>
