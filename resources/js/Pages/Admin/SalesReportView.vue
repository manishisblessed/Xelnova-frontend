<script setup>
import { Head, usePage, router } from "@inertiajs/vue3";
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import {
    mdiChartLine,
    mdiCart,
    mdiCash,
    mdiPackageCheck,
    mdiCancel,
    mdiStore,
} from "@mdi/js";
import SectionMain from "@/components/SectionMain.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import CardBox from "@/components/CardBox.vue";
import BaseIcon from "@/components/BaseIcon.vue";
import { computed, ref } from "vue";

const props = defineProps({
    overallStats: {
        type: Object,
        default: () => ({}),
    },
    salesData: {
        type: Array,
        default: () => [],
    },
    topSellers: {
        type: Array,
        default: () => [],
    },
    statusBreakdown: {
        type: Object,
        default: () => ({}),
    },
    paymentBreakdown: {
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
const groupBy = ref(props.filters.group_by);

const applyFilters = () => {
    router.get(route('salesReport.index'), {
        start_date: startDate.value,
        end_date: endDate.value,
        group_by: groupBy.value,
    }, {
        preserveState: true,
    });
};

const formatMoney = (amount) => {
    return '₹' + Number(amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
};

const formatStatus = (status) => {
    if (!status) return '';
    return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-500',
        confirmed: 'bg-blue-500',
        processing: 'bg-indigo-500',
        shipped: 'bg-purple-500',
        delivered: 'bg-green-500',
        cancelled: 'bg-red-500',
    };
    return colors[status] || 'bg-gray-500';
};

const totalStatusOrders = computed(() => {
    return Object.values(props.statusBreakdown).reduce((a, b) => a + b, 0);
});
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
                    <div>
                        <label class="block text-sm font-medium mb-1">Group By</label>
                        <select
                            v-model="groupBy"
                            class="px-3 py-2 border rounded-lg dark:bg-gray-800 dark:border-gray-700"
                        >
                            <option value="day">Daily</option>
                            <option value="week">Weekly</option>
                            <option value="month">Monthly</option>
                        </select>
                    </div>
                    <button
                        @click="applyFilters"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        Apply Filters
                    </button>
                </div>
            </CardBox>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <CardBox class="p-4">
                    <div class="flex items-center">
                        <BaseIcon :path="mdiCart" class="text-blue-500" w="w-10" h="h-10" />
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Orders</p>
                            <p class="text-2xl font-bold">{{ overallStats.total_orders }}</p>
                        </div>
                    </div>
                </CardBox>

                <CardBox class="p-4">
                    <div class="flex items-center">
                        <BaseIcon :path="mdiCash" class="text-green-500" w="w-10" h="h-10" />
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Revenue</p>
                            <p class="text-2xl font-bold">{{ formatMoney(overallStats.total_revenue) }}</p>
                        </div>
                    </div>
                </CardBox>

                <CardBox class="p-4">
                    <div class="flex items-center">
                        <BaseIcon :path="mdiChartLine" class="text-purple-500" w="w-10" h="h-10" />
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Avg. Order</p>
                            <p class="text-2xl font-bold">{{ formatMoney(overallStats.avg_order_value) }}</p>
                        </div>
                    </div>
                </CardBox>

                <CardBox class="p-4">
                    <div class="flex items-center">
                        <BaseIcon :path="mdiPackageCheck" class="text-teal-500" w="w-10" h="h-10" />
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Completed</p>
                            <p class="text-2xl font-bold">{{ overallStats.completed_orders }}</p>
                        </div>
                    </div>
                </CardBox>

                <CardBox class="p-4">
                    <div class="flex items-center">
                        <BaseIcon :path="mdiCancel" class="text-red-500" w="w-10" h="h-10" />
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Cancelled</p>
                            <p class="text-2xl font-bold">{{ overallStats.cancelled_orders }}</p>
                        </div>
                    </div>
                </CardBox>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Sales Over Time -->
                <CardBox class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Sales Over Time</h3>
                    <div v-if="salesData.length > 0" class="space-y-2">
                        <div v-for="item in salesData.slice(-10)" :key="item.period" class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded">
                            <span class="text-sm font-medium">{{ item.period }}</span>
                            <div class="flex items-center gap-4">
                                <span class="text-sm text-gray-500">{{ item.orders }} orders</span>
                                <span class="font-bold text-green-600">{{ formatMoney(item.revenue) }}</span>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-500 text-center py-8">No data for selected period</p>
                </CardBox>

                <!-- Top Sellers -->
                <CardBox class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Top Sellers</h3>
                    <div v-if="topSellers.length > 0" class="space-y-2">
                        <div v-for="(seller, index) in topSellers" :key="seller.seller_id" class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded">
                            <div class="flex items-center">
                                <span class="w-6 h-6 flex items-center justify-center text-xs font-bold bg-blue-500 text-white rounded-full mr-3">
                                    {{ index + 1 }}
                                </span>
                                <span class="font-medium">{{ seller.seller?.business_name || 'Unknown' }}</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="text-sm text-gray-500">{{ seller.order_count }} orders</span>
                                <span class="font-bold text-green-600">{{ formatMoney(seller.total_sales) }}</span>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-500 text-center py-8">No sales data</p>
                </CardBox>

                <!-- Order Status Breakdown -->
                <CardBox class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Order Status Distribution</h3>
                    <div v-if="Object.keys(statusBreakdown).length > 0" class="space-y-3">
                        <div v-for="(count, status) in statusBreakdown" :key="status">
                            <div class="flex justify-between text-sm mb-1">
                                <span>{{ formatStatus(status) }}</span>
                                <span class="font-medium">{{ count }} ({{ Math.round(count / totalStatusOrders * 100) }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div
                                    :class="getStatusColor(status)"
                                    class="h-2 rounded-full"
                                    :style="{ width: (count / totalStatusOrders * 100) + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-500 text-center py-8">No orders in selected period</p>
                </CardBox>

                <!-- Payment Methods -->
                <CardBox class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Payment Methods</h3>
                    <div v-if="paymentBreakdown.length > 0" class="space-y-2">
                        <div v-for="item in paymentBreakdown" :key="item.payment_method" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded">
                            <span class="font-medium capitalize">{{ item.payment_method || 'Unknown' }}</span>
                            <div class="flex items-center gap-4">
                                <span class="text-sm text-gray-500">{{ item.count }} orders</span>
                                <span class="font-bold text-green-600">{{ formatMoney(item.total) }}</span>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-500 text-center py-8">No payment data</p>
                </CardBox>
            </div>
        </SectionMain>
    </LayoutAuthenticated>
</template>
