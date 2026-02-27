<script setup>
import { Head, usePage, router } from "@inertiajs/vue3";
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import {
    mdiCash,
    mdiCart,
    mdiStore,
    mdiCashRefund,
    mdiTrendingUp,
} from "@mdi/js";
import SectionMain from "@/components/SectionMain.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import CardBox from "@/components/CardBox.vue";
import BaseIcon from "@/components/BaseIcon.vue";
import { ref } from "vue";

const props = defineProps({
    summary: {
        type: Object,
        default: () => ({}),
    },
    revenueByCategory: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    resourceNeo: {
        type: Object,
        default: () => ({}),
    },
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

const applyFilters = () => {
    router.get(route('revenueReport.index'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, {
        preserveState: true,
    });
};

const formatMoney = (amount) => {
    return '₹' + Number(amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
};
</script>

<template>
    <LayoutAuthenticated>
        <Head :title="props.resourceNeo.resourceTitle" />
        <SectionMain>
            <SectionTitleLineWithButton
                :icon="props.resourceNeo.iconPath"
                :title="props.resourceNeo.resourceTitle"
                main
            />

            <!-- Filters -->
            <CardBox class="mb-6 p-4">
                <div class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium mb-1">Start Date</label>
                        <input
                            v-model="startDate"
                            type="date"
                            class="px-3 py-2 border rounded-lg dark:bg-gray-800 dark:border-gray-700"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">End Date</label>
                        <input
                            v-model="endDate"
                            type="date"
                            class="px-3 py-2 border rounded-lg dark:bg-gray-800 dark:border-gray-700"
                        />
                    </div>
                    <button
                        @click="applyFilters"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        Apply Filters
                    </button>
                </div>
            </CardBox>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <CardBox class="p-4">
                    <div class="flex items-center">
                        <BaseIcon :path="mdiCash" class="text-green-500" w="w-10" h="h-10" />
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Revenue</p>
                            <p class="text-2xl font-bold text-green-600">{{ formatMoney(summary.total_revenue) }}</p>
                        </div>
                    </div>
                </CardBox>

                <CardBox class="p-4">
                    <div class="flex items-center">
                        <BaseIcon :path="mdiTrendingUp" class="text-blue-500" w="w-10" h="h-10" />
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Commission Earned</p>
                            <p class="text-2xl font-bold text-blue-600">{{ formatMoney(summary.commission_earned) }}</p>
                        </div>
                    </div>
                </CardBox>

                <CardBox class="p-4">
                    <div class="flex items-center">
                        <BaseIcon :path="mdiStore" class="text-purple-500" w="w-10" h="h-10" />
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Seller Payouts</p>
                            <p class="text-2xl font-bold text-purple-600">{{ formatMoney(summary.seller_payouts) }}</p>
                        </div>
                    </div>
                </CardBox>

                <CardBox class="p-4">
                    <div class="flex items-center">
                        <BaseIcon :path="mdiCashRefund" class="text-red-500" w="w-10" h="h-10" />
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Refunds</p>
                            <p class="text-2xl font-bold text-red-600">{{ formatMoney(summary.total_refunds) }}</p>
                        </div>
                    </div>
                </CardBox>
            </div>

            <!-- Additional Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <CardBox class="p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Pending Payouts</p>
                    <p class="text-xl font-bold">{{ formatMoney(summary.pending_payouts) }}</p>
                </CardBox>

                <CardBox class="p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Orders</p>
                    <p class="text-xl font-bold">{{ summary.total_orders }}</p>
                </CardBox>

                <CardBox class="p-4 bg-green-50 dark:bg-green-900/20">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Net Revenue</p>
                    <p class="text-2xl font-bold text-green-600">{{ formatMoney(summary.net_revenue) }}</p>
                </CardBox>
            </div>

            <!-- Revenue by Category -->
            <CardBox class="p-6">
                <h3 class="text-lg font-semibold mb-4">Revenue by Category</h3>
                <div v-if="revenueByCategory.length > 0" class="space-y-3">
                    <div v-for="item in revenueByCategory" :key="item.category" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded">
                        <span class="font-medium">{{ item.category }}</span>
                        <span class="text-lg font-bold text-green-600">{{ formatMoney(item.revenue) }}</span>
                    </div>
                </div>
                <p v-else class="text-gray-500 text-center py-8">No revenue data for selected period</p>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template>
