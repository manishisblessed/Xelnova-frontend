<script setup>
import { Head, usePage, router } from "@inertiajs/vue3";
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import {
    mdiStore,
    mdiCart,
    mdiTruck,
    mdiCancel,
    mdiChartLine,
} from "@mdi/js";
import SectionMain from "@/components/SectionMain.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import CardBox from "@/components/CardBox.vue";
import BaseIcon from "@/components/BaseIcon.vue";
import { ref } from "vue";

const props = defineProps({
    sellers: {
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
    router.get(route('sellerPerformance.index'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, {
        preserveState: true,
    });
};

const formatMoney = (amount) => {
    return '₹' + Number(amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
};

const getPerformanceColor = (rate) => {
    if (rate >= 90) return 'text-green-600';
    if (rate >= 70) return 'text-yellow-600';
    return 'text-red-600';
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

            <!-- Sellers Performance Table -->
            <CardBox class="p-6">
                <h3 class="text-lg font-semibold mb-4">Seller Rankings</h3>
                <div v-if="sellers.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="text-left py-3 text-sm font-medium text-gray-500">#</th>
                                <th class="text-left py-3 text-sm font-medium text-gray-500">Seller</th>
                                <th class="text-right py-3 text-sm font-medium text-gray-500">Orders</th>
                                <th class="text-right py-3 text-sm font-medium text-gray-500">Sales</th>
                                <th class="text-center py-3 text-sm font-medium text-gray-500">Delivery Rate</th>
                                <th class="text-center py-3 text-sm font-medium text-gray-500">Cancel Rate</th>
                                <th class="text-center py-3 text-sm font-medium text-gray-500">Avg Delivery</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(seller, index) in sellers" :key="seller.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="py-3">
                                    <span class="w-8 h-8 flex items-center justify-center text-sm font-bold bg-blue-500 text-white rounded-full">
                                        {{ index + 1 }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <div>
                                        <p class="font-medium">{{ seller.business_name }}</p>
                                        <p class="text-xs text-gray-500">{{ seller.email }}</p>
                                    </div>
                                </td>
                                <td class="py-3 text-right">
                                    <span class="px-2 py-1 text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded">
                                        {{ seller.total_orders }}
                                    </span>
                                </td>
                                <td class="py-3 text-right font-bold text-green-600">
                                    {{ seller.formatted_sales }}
                                </td>
                                <td class="py-3 text-center">
                                    <span :class="['font-semibold', getPerformanceColor(seller.delivery_rate)]">
                                        {{ seller.delivery_rate }}%
                                    </span>
                                </td>
                                <td class="py-3 text-center">
                                    <span :class="['font-semibold', seller.cancellation_rate > 10 ? 'text-red-600' : 'text-green-600']">
                                        {{ seller.cancellation_rate }}%
                                    </span>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="text-sm">{{ seller.avg_delivery_days }} days</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-gray-500 text-center py-8">No seller data for selected period</p>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template>
