<script setup>
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import {
    mdiArrowLeft,
    mdiCheckCircle,
    mdiCloseCircle,
    mdiDownload,
    mdiAlert,
} from "@mdi/js";
import SectionMain from "@/components/SectionMain.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import BaseButton from "@/components/BaseButton.vue";
import CardBox from "@/components/CardBox.vue";
import CardBoxModal from "@/components/CardBoxModal.vue";
import NotificationBar from "@/components/NotificationBar.vue";
import { computed, onMounted, ref } from "vue";
import { useToast } from "vue-toast-notification";
import "vue-toast-notification/dist/theme-sugar.css";

const message = computed(() => usePage().props.flash.message);
const msg_type = computed(() => usePage().props.flash.msg_type ?? "warning");

const props = defineProps({
    seller: {
        type: Object,
        required: true,
    },
    documents: {
        type: Array,
        default: () => [],
    },
    resourceNeo: {
        type: Object,
        default: () => ({}),
    },
});

const isModalRejectActive = ref(false);
const selectedDocument = ref(null);
const rejectionReason = ref('');

const verifyDocument = (document) => {
    router.post(
        route('sellerDocument.verify', [props.seller.id, document.id]),
        { action: 'verify' },
        {
            preserveScroll: true,
        }
    );
};

const openRejectModal = (document) => {
    selectedDocument.value = document;
    isModalRejectActive.value = true;
};

const rejectDocument = () => {
    if (selectedDocument.value && rejectionReason.value) {
        router.post(
            route('sellerDocument.verify', [props.seller.id, selectedDocument.value.id]),
            { 
                action: 'reject',
                rejection_reason: rejectionReason.value 
            },
            {
                preserveScroll: true,
                onFinish: () => {
                    isModalRejectActive.value = false;
                    selectedDocument.value = null;
                    rejectionReason.value = '';
                },
            }
        );
    }
};

const getStatusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        verified: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getDocumentTypeLabel = (type) => {
    const labels = {
        pan_card: 'PAN Card',
        gst_certificate: 'GST Certificate',
        business_registration: 'Business Registration',
        address_proof: 'Address Proof',
        bank_statement: 'Bank Statement',
        other: 'Other',
    };
    return labels[type] || type;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

onMounted(() => {
    if (message.value) {
        if (msg_type.value == "info") {
            useToast().info(message.value, { duration: 7000 });
        } else if (msg_type.value == "success") {
            useToast().success(message.value, { duration: 7000 });
        } else if (msg_type.value == "danger") {
            useToast().error(message.value, { duration: 7000 });
        } else {
            useToast().warning(message.value, { duration: 7000 });
        }
    }
});
</script>

<template>
    <LayoutAuthenticated>
        <Head :title="`Documents - ${seller.business_name}`" />
        <SectionMain>
            <SectionTitleLineWithButton
                :icon="resourceNeo.iconPath"
                :title="`Seller Documents - ${seller.business_name}`"
                main
            >
                <div class="flex gap-2">
                    <Link :href="route('seller.overview', seller.id)">
                        <BaseButton
                            :icon="mdiArrowLeft"
                            color="info"
                            rounded-full
                            small
                            label="Back to Overview"
                        />
                    </Link>
                </div>
            </SectionTitleLineWithButton>

            <NotificationBar
                v-if="message"
                @closed="usePage().props.flash.message = ''"
                :color="msg_type"
                :icon="mdiAlert"
                :outline="true"
            >
                {{ message }}
            </NotificationBar>

            <CardBox has-table>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Document Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    File Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Upload Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-if="documents.length === 0">
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No documents uploaded yet
                                </td>
                            </tr>
                            <tr v-for="document in documents" :key="document.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ getDocumentTypeLabel(document.document_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ document.original_filename || 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ formatDate(document.created_at) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full"
                                        :class="getStatusBadgeClass(document.verification_status)"
                                    >
                                        {{ document.verification_status.charAt(0).toUpperCase() + document.verification_status.slice(1) }}
                                    </span>
                                    <div v-if="document.rejection_reason" class="text-xs text-red-600 dark:text-red-400 mt-1">
                                        Reason: {{ document.rejection_reason }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex gap-2">
                                        <a 
                                            :href="`/storage/${document.document_path}`" 
                                            target="_blank"
                                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400"
                                        >
                                            <BaseButton
                                                :icon="mdiDownload"
                                                color="info"
                                                small
                                                title="Download"
                                            />
                                        </a>
                                        <BaseButton
                                            v-if="document.verification_status === 'pending'"
                                            :icon="mdiCheckCircle"
                                            color="success"
                                            small
                                            title="Verify"
                                            @click="verifyDocument(document)"
                                        />
                                        <BaseButton
                                            v-if="document.verification_status === 'pending'"
                                            :icon="mdiCloseCircle"
                                            color="danger"
                                            small
                                            title="Reject"
                                            @click="openRejectModal(document)"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardBox>
        </SectionMain>

        <!-- Reject Modal with Reason -->
        <CardBoxModal
            v-model="isModalRejectActive"
            buttonLabel="Reject"
            title="Reject Document"
            button="danger"
            has-cancel
            @confirm="rejectDocument"
        >
            <p class="mb-4">Reject <strong>{{ getDocumentTypeLabel(selectedDocument?.document_type) }}</strong>?</p>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Reason for Rejection *</label>
                <textarea
                    v-model="rejectionReason"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-200"
                    placeholder="Enter reason for rejecting this document..."
                    required
                ></textarea>
            </div>
        </CardBoxModal>
    </LayoutAuthenticated>
</template>
