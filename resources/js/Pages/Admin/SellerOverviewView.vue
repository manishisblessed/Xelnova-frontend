<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import {
    mdiFileDocument,
    mdiBank,
    mdiArrowLeft,
    mdiCheckCircle,
    mdiCloseCircle,
    mdiAlertCircle,
} from "@mdi/js";
import SectionMain from "@/components/SectionMain.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import BaseButton from "@/components/BaseButton.vue";
import CardBox from "@/components/CardBox.vue";
import BaseIcon from "@/components/BaseIcon.vue";

const props = defineProps({
    seller: {
        type: Object,
        required: true,
    },
    documentsStats: {
        type: Object,
        required: true,
    },
    banksStats: {
        type: Object,
        required: true,
    },
    resourceNeo: {
        type: Object,
        default: () => ({}),
    },
});

const getStatusColor = (status) => {
    const colors = {
        pending: 'text-yellow-600 dark:text-yellow-400',
        approved: 'text-green-600 dark:text-green-400',
        suspended: 'text-red-600 dark:text-red-400',
        rejected: 'text-gray-600 dark:text-gray-400',
    };
    return colors[status] || 'text-gray-600';
};

const getVerificationColor = (status) => {
    const colors = {
        unverified: 'text-gray-600 dark:text-gray-400',
        verified: 'text-blue-600 dark:text-blue-400',
        rejected: 'text-red-600 dark:text-red-400',
    };
    return colors[status] || 'text-gray-600';
};
</script>

<template>
    <LayoutAuthenticated>
        <Head :title="`Seller Overview - ${seller.business_name}`" />
        <SectionMain>
            <SectionTitleLineWithButton
                :icon="resourceNeo.iconPath"
                :title="`Seller Overview - ${seller.business_name}`"
                main
            >
                <Link :href="route('seller.index')">
                    <BaseButton
                        :icon="mdiArrowLeft"
                        color="info"
                        rounded-full
                        small
                        label="Back to Sellers"
                    />
                </Link>
            </SectionTitleLineWithButton>

            <!-- Seller Information Card -->
            <CardBox class="mb-6">
                <div class="p-6">
                    <h3 class="text-xl font-semibold mb-4 dark:text-white">Seller Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Business Name</p>
                            <p class="font-medium dark:text-white">{{ seller.business_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Business Type</p>
                            <p class="font-medium dark:text-white capitalize">{{ seller.business_type }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Email</p>
                            <p class="font-medium dark:text-white">{{ seller.email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Phone</p>
                            <p class="font-medium dark:text-white">{{ seller.phone }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                            <p class="font-medium capitalize" :class="getStatusColor(seller.status)">
                                {{ seller.status }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Verification Status</p>
                            <p class="font-medium capitalize" :class="getVerificationColor(seller.verification_status)">
                                {{ seller.verification_status }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">GST Number</p>
                            <p class="font-medium dark:text-white">{{ seller.gst_number || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">PAN Number</p>
                            <p class="font-medium dark:text-white">{{ seller.pan_number || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Commission Rate</p>
                            <p class="font-medium dark:text-white">{{ seller.commission_rate }}%</p>
                        </div>
                    </div>
                </div>
            </CardBox>

            <!-- Documents and Banks Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Documents Card -->
                <CardBox>
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <BaseIcon :path="mdiFileDocument" class="mr-2" :size="24" />
                                <h3 class="text-xl font-semibold dark:text-white">Documents</h3>
                            </div>
                            <Link :href="route('sellerDocument.index', seller.id)">
                                <BaseButton
                                    color="info"
                                    small
                                    label="View All"
                                />
                            </Link>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 dark:text-gray-400">Total Documents</span>
                                <span class="font-semibold text-lg dark:text-white">{{ documentsStats.total }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center">
                                    <BaseIcon :path="mdiAlertCircle" class="mr-1 text-yellow-600" :size="18" />
                                    <span class="text-gray-600 dark:text-gray-400">Pending</span>
                                </div>
                                <span class="font-semibold text-yellow-600">{{ documentsStats.pending }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center">
                                    <BaseIcon :path="mdiCheckCircle" class="mr-1 text-green-600" :size="18" />
                                    <span class="text-gray-600 dark:text-gray-400">Verified</span>
                                </div>
                                <span class="font-semibold text-green-600">{{ documentsStats.verified }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center">
                                    <BaseIcon :path="mdiCloseCircle" class="mr-1 text-red-600" :size="18" />
                                    <span class="text-gray-600 dark:text-gray-400">Rejected</span>
                                </div>
                                <span class="font-semibold text-red-600">{{ documentsStats.rejected }}</span>
                            </div>
                        </div>
                    </div>
                </CardBox>

                <!-- Bank Accounts Card -->
                <CardBox>
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <BaseIcon :path="mdiBank" class="mr-2" :size="24" />
                                <h3 class="text-xl font-semibold dark:text-white">Bank Accounts</h3>
                            </div>
                            <Link :href="route('sellerBank.index', seller.id)">
                                <BaseButton
                                    color="info"
                                    small
                                    label="View All"
                                />
                            </Link>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 dark:text-gray-400">Total Accounts</span>
                                <span class="font-semibold text-lg dark:text-white">{{ banksStats.total }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center">
                                    <BaseIcon :path="mdiAlertCircle" class="mr-1 text-yellow-600" :size="18" />
                                    <span class="text-gray-600 dark:text-gray-400">Pending</span>
                                </div>
                                <span class="font-semibold text-yellow-600">{{ banksStats.pending }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center">
                                    <BaseIcon :path="mdiCheckCircle" class="mr-1 text-green-600" :size="18" />
                                    <span class="text-gray-600 dark:text-gray-400">Verified</span>
                                </div>
                                <span class="font-semibold text-green-600">{{ banksStats.verified }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center">
                                    <BaseIcon :path="mdiCloseCircle" class="mr-1 text-red-600" :size="18" />
                                    <span class="text-gray-600 dark:text-gray-400">Rejected</span>
                                </div>
                                <span class="font-semibold text-red-600">{{ banksStats.rejected }}</span>
                            </div>
                        </div>
                    </div>
                </CardBox>
            </div>
        </SectionMain>
    </LayoutAuthenticated>
</template>
