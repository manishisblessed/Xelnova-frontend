<script setup>
import { Head, usePage } from "@inertiajs/vue3";
import {
    mdiViewDashboard,
    mdiAlert,
    mdiTrendingUp,
    mdiTrendingDown,
    mdiPackageVariant,
    mdiFileDocumentOutline,
    mdiCashMultiple,
    mdiAccountGroup,
    mdiFactory,
    mdiChartLine,
    mdiClockOutline,
    mdiAccountMultiple,
    mdiDomain,
    mdiTruckDelivery,
    mdiCog,
    mdiChartPie,
    mdiChartBar,
    mdiInformation,
} from "@mdi/js";

import { computed, onMounted, ref } from "vue";
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    BarElement,
    ArcElement,
} from "chart.js";
import { Line, Bar, Doughnut } from "vue-chartjs";

import SectionMain from "@/components/SectionMain.vue";
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import NotificationBar from "@/components/NotificationBar.vue";
import CardBox from "@/components/CardBox.vue";
import BaseIcon from "@/components/BaseIcon.vue";

// Register Chart.js components
ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    BarElement,
    ArcElement
);

const props = defineProps({
    stockOverview: Object,
    enquiryOverview: Object,
    salesOverview: Object,
    monthlySales: Array,
    topClients: Array,
    recentActivities: Array,
    systemStats: Object,
    productionSummary: Object,
});

const message = computed(() => usePage().props.flash.message);
const msg_type = computed(() => usePage().props.flash.msg_type ?? "warning");
const user_name = usePage().props.auth.user.name;

// Chart configurations
const salesTrendOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: "top",
        },
        title: {
            display: true,
            text: "Monthly Sales Trend (Last 12 Months)",
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1,
            },
        },
    },
};

const salesTrendData = computed(() => ({
    labels: props.monthlySales.map((item) => item.month),
    datasets: [
        {
            label: "Tubes",
            data: props.monthlySales.map((item) => item.tubes),
            borderColor: "#3B82F6",
            backgroundColor: "rgba(59, 130, 246, 0.1)",
            tension: 0.4,
        },
        {
            label: "Sheets",
            data: props.monthlySales.map((item) => item.sheets),
            borderColor: "#10B981",
            backgroundColor: "rgba(16, 185, 129, 0.1)",
            tension: 0.4,
        },
        {
            label: "Coils",
            data: props.monthlySales.map((item) => item.coils),
            borderColor: "#F59E0B",
            backgroundColor: "rgba(245, 158, 11, 0.1)",
            tension: 0.4,
        },
        {
            label: "Strips",
            data: props.monthlySales.map((item) => item.strips),
            borderColor: "#EF4444",
            backgroundColor: "rgba(239, 68, 68, 0.1)",
            tension: 0.4,
        },
        {
            label: "Blusters",
            data: props.monthlySales.map((item) => item.blusters),
            borderColor: "#8B5CF6",
            backgroundColor: "rgba(139, 92, 246, 0.1)",
            tension: 0.4,
        },
        {
            label: "Black Tubes",
            data: props.monthlySales.map((item) => item.blackTubes),
            borderColor: "#6B7280",
            backgroundColor: "rgba(107, 114, 128, 0.1)",
            tension: 0.4,
        },
    ],
}));

const topClientsData = computed(() => ({
    labels: props.topClients.map((client) => client.name),
    datasets: [
        {
            label: "Total Sales",
            data: props.topClients.map((client) => client.total_sales),
            backgroundColor: [
                "#3B82F6",
                "#10B981",
                "#F59E0B",
                "#EF4444",
                "#8B5CF6",
                "#EC4899",
                "#14B8A6",
                "#F97316",
                "#84CC16",
                "#6366F1",
            ],
            borderWidth: 1,
        },
    ],
}));

const stockDistributionData = computed(() => {
    const totalStock =
        props.stockOverview.coils.total +
        props.stockOverview.tubes.total +
        props.stockOverview.sheets.total +
        props.stockOverview.blusters.total +
        props.stockOverview.strips.total;

    return {
        labels: ["Coils", "Tubes", "Sheets", "Blusters", "Strips"],
        datasets: [
            {
                data: [
                    props.stockOverview.coils.total,
                    props.stockOverview.tubes.total,
                    props.stockOverview.sheets.total,
                    props.stockOverview.blusters.total,
                    props.stockOverview.strips.total,
                ],
                backgroundColor: [
                    "#3B82F6",
                    "#10B981",
                    "#F59E0B",
                    "#EF4444",
                    "#8B5CF6",
                ],
                borderWidth: 2,
                borderColor: "#ffffff",
            },
        ],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: "bottom",
        },
    },
};

// Helper functions
const getStatusColor = (status, type) => {
    const colors = {
        stock: {
            unused: "text-green-600",
            open: "text-green-600",
            used: "text-yellow-600",
            partially_sold: "text-yellow-600",
            sold: "text-red-600",
            finalized: "text-blue-600",
            planned: "text-gray-600",
        },
        enquiry: {
            open: "text-green-600",
            partially_sold: "text-yellow-600",
            sold: "text-blue-600",
            closed: "text-red-600",
        },
        sales: {
            open: "text-green-600",
            partially_delivered: "text-yellow-600",
            delivered: "text-blue-600",
            closed: "text-red-600",
        },
    };
    return colors[type]?.[status] || "text-gray-600";
};

const formatNumber = (num) => {
    return new Intl.NumberFormat("en-IN").format(num);
};

const formatWeight = (weight) => {
    return `${formatNumber(Math.round(weight))} kg`;
};
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Dashboard" />
        <SectionMain>
            <SectionTitleLineWithButton
                :icon="mdiViewDashboard"
                :title="`${user_name} Dashboard`"
                main
            />


            <NotificationBar
                v-if="message"
                @closed="usePage().props.flash.message = ''"
                :color="msg_type"
                :icon="mdiAlert"
                :outline="true"
            >
                {{ message }}
            </NotificationBar>

            <!-- Key Metrics Row -->
            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6"
            >
                <CardBox
                    class="bg-gradient-to-r from-blue-500 to-blue-600 text-white"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm">
                                Total Stock Items
                            </p>
                            <p class="text-2xl font-bold">
                                {{
                                    formatNumber(
                                        stockOverview.coils.total +
                                            stockOverview.tubes.total +
                                            stockOverview.sheets.total +
                                            stockOverview.blusters.total +
                                            stockOverview.strips.total
                                    )
                                }}
                            </p>
                        </div>
                        <BaseIcon
                            :path="mdiPackageVariant"
                            size="48"
                            class="text-blue-200"
                        />
                    </div>
                </CardBox>

                <CardBox
                    class="bg-gradient-to-r from-green-500 to-green-600 text-white"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm">
                                Total Enquiries
                            </p>
                            <p class="text-2xl font-bold">
                                {{
                                    formatNumber(
                                        enquiryOverview.tubes.total +
                                            enquiryOverview.sheets.total +
                                            enquiryOverview.coils.total +
                                            enquiryOverview.strips.total +
                                            enquiryOverview.blusters.total +
                                            enquiryOverview.blackTubes.total
                                    )
                                }}
                            </p>
                        </div>
                        <BaseIcon
                            :path="mdiFileDocumentOutline"
                            size="48"
                            class="text-green-200"
                        />
                    </div>
                </CardBox>

                <CardBox
                    class="bg-gradient-to-r from-purple-500 to-purple-600 text-white"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm">Total Sales</p>
                            <p class="text-2xl font-bold">
                                {{
                                    formatNumber(
                                        salesOverview.tubes.total +
                                            salesOverview.sheets.total +
                                            salesOverview.coils.total +
                                            salesOverview.strips.total +
                                            salesOverview.blusters.total +
                                            salesOverview.blackTubes.total
                                    )
                                }}
                            </p>
                        </div>
                        <BaseIcon
                            :path="mdiCashMultiple"
                            size="48"
                            class="text-purple-200"
                        />
                    </div>
                </CardBox>

                <CardBox
                    class="bg-gradient-to-r from-orange-500 to-orange-600 text-white"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm">Total Users</p>
                            <p class="text-2xl font-bold">
                                {{ formatNumber(systemStats.total_users) }}
                            </p>
                        </div>
                        <BaseIcon
                            :path="mdiAccountGroup"
                            size="48"
                            class="text-orange-200"
                        />
                    </div>
                </CardBox>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Sales Trend Chart -->
                <CardBox>
                    <div class="h-80">
                        <Line
                            :data="salesTrendData"
                            :options="salesTrendOptions"
                        />
                    </div>
                </CardBox>

                <!-- Stock Distribution Chart -->
                <CardBox>
                    <div class="h-80 flex flex-col">
                        <h3
                            class="text-lg font-semibold mb-4 flex items-center"
                        >
                            <BaseIcon :path="mdiChartPie" class="mr-2" />
                            Stock Distribution
                        </h3>
                        <div class="flex-1">
                            <Doughnut
                                :data="stockDistributionData"
                                :options="chartOptions"
                            />
                        </div>
                    </div>
                </CardBox>
            </div>

            <!-- Stock Overview -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <CardBox>
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        <BaseIcon
                            :path="mdiPackageVariant"
                            class="mr-2 text-blue-600"
                        />
                        Stock Overview
                    </h3>
                    <div class="space-y-4">
                        <div class="border-l-4 border-blue-500 pl-4">
                            <h4 class="font-medium text-blue-700">Coils</h4>
                            <div class="text-sm text-gray-600 space-y-1">
                                <div class="flex justify-between">
                                    <span>Total:</span>
                                    <span class="font-medium">{{
                                        formatNumber(stockOverview.coils.total)
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Weight:</span>
                                    <span class="font-medium">{{
                                        formatWeight(
                                            stockOverview.coils.total_weight
                                        )
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-green-600">Unused:</span>
                                    <span class="font-medium">{{
                                        formatNumber(stockOverview.coils.unused)
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-yellow-600">Used:</span>
                                    <span class="font-medium">{{
                                        formatNumber(stockOverview.coils.used)
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-red-600">Sold:</span>
                                    <span class="font-medium">{{
                                        formatNumber(stockOverview.coils.sold)
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="border-l-4 border-green-500 pl-4">
                            <h4 class="font-medium text-green-700">Tubes</h4>
                            <div class="text-sm text-gray-600 space-y-1">
                                <div class="flex justify-between">
                                    <span>Total:</span>
                                    <span class="font-medium">{{
                                        formatNumber(stockOverview.tubes.total)
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Planned:</span>
                                    <span class="font-medium">{{
                                        formatNumber(
                                            stockOverview.tubes.planned
                                        )
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-blue-600"
                                        >Finalized:</span
                                    >
                                    <span class="font-medium">{{
                                        formatNumber(
                                            stockOverview.tubes.finalized
                                        )
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-red-600">Used:</span>
                                    <span class="font-medium">{{
                                        formatNumber(stockOverview.tubes.used)
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="border-l-4 border-yellow-500 pl-4">
                            <h4 class="font-medium text-yellow-700">Sheets</h4>
                            <div class="text-sm text-gray-600 space-y-1">
                                <div class="flex justify-between">
                                    <span>Total:</span>
                                    <span class="font-medium">{{
                                        formatNumber(stockOverview.sheets.total)
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Weight:</span>
                                    <span class="font-medium">{{
                                        formatWeight(
                                            stockOverview.sheets.total_weight
                                        )
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-green-600">Open:</span>
                                    <span class="font-medium">{{
                                        formatNumber(stockOverview.sheets.open)
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-yellow-600"
                                        >Partially Sold:</span
                                    >
                                    <span class="font-medium">{{
                                        formatNumber(
                                            stockOverview.sheets.partially_sold
                                        )
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-red-600">Sold:</span>
                                    <span class="font-medium">{{
                                        formatNumber(stockOverview.sheets.sold)
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardBox>

                <!-- Enquiry Overview -->
                <CardBox>
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        <BaseIcon
                            :path="mdiFileDocumentOutline"
                            class="mr-2 text-green-600"
                        />
                        Enquiry Status
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="(product, key) in enquiryOverview"
                            :key="key"
                            class="bg-gray-50 p-3 rounded-lg"
                        >
                            <h4 class="font-medium capitalize mb-2">
                                {{ key.replace(/([A-Z])/g, " $1").trim() }}
                            </h4>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-green-600">Open:</span>
                                    <span class="font-medium">{{
                                        formatNumber(product.open)
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-yellow-600"
                                        >Partial:</span
                                    >
                                    <span class="font-medium">{{
                                        formatNumber(product.partially_sold)
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-blue-600">Sold:</span>
                                    <span class="font-medium">{{
                                        formatNumber(product.sold)
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-red-600">Closed:</span>
                                    <span class="font-medium">{{
                                        formatNumber(product.closed)
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardBox>

                <!-- Sales Overview -->
                <CardBox>
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        <BaseIcon
                            :path="mdiCashMultiple"
                            class="mr-2 text-purple-600"
                        />
                        Sales Status
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="(product, key) in salesOverview"
                            :key="key"
                            class="bg-gray-50 p-3 rounded-lg"
                        >
                            <h4 class="font-medium capitalize mb-2">
                                {{ key.replace(/([A-Z])/g, " $1").trim() }}
                            </h4>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-green-600">Open:</span>
                                    <span class="font-medium">{{
                                        formatNumber(product.open)
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-yellow-600"
                                        >Partial:</span
                                    >
                                    <span class="font-medium">{{
                                        formatNumber(
                                            product.partially_delivered
                                        )
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-blue-600"
                                        >Delivered:</span
                                    >
                                    <span class="font-medium">{{
                                        formatNumber(product.delivered)
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-red-600">Closed:</span>
                                    <span class="font-medium">{{
                                        formatNumber(product.closed)
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardBox>
            </div>

            <!-- Bottom Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Top Clients -->
                <CardBox>
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        <BaseIcon
                            :path="mdiChartBar"
                            class="mr-2 text-indigo-600"
                        />
                        Top 10 Clients by Sales
                    </h3>
                    <div class="h-64">
                        <Bar
                            :data="topClientsData"
                            :options="{ ...chartOptions, indexAxis: 'y' }"
                        />
                    </div>
                </CardBox>

                <!-- Recent Activities -->
                <CardBox>
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        <BaseIcon
                            :path="mdiClockOutline"
                            class="mr-2 text-gray-600"
                        />
                        Recent Activities
                    </h3>
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        <div
                            v-for="activity in recentActivities"
                            :key="activity.id"
                            class="flex items-start space-x-3 p-2 hover:bg-gray-50 rounded"
                        >
                            <div
                                class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"
                            ></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900">
                                    <span class="font-medium">{{
                                        activity.user
                                    }}</span>
                                    {{ activity.action }}
                                    <span class="font-medium">{{
                                        activity.model
                                    }}</span>
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ activity.time }}
                                </p>
                            </div>
                        </div>
                    </div>
                </CardBox>
            </div>

            <!-- Production Summary & System Stats -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Production Summary -->
                <CardBox>
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        <BaseIcon
                            :path="mdiFactory"
                            class="mr-2 text-blue-600"
                        />
                        Production Summary (Current Month)
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <p class="text-2xl font-bold text-blue-600">
                                {{
                                    formatNumber(
                                        productionSummary.coils_received
                                    )
                                }}
                            </p>
                            <p class="text-sm text-blue-700">Coils Received</p>
                        </div>
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <p class="text-2xl font-bold text-green-600">
                                {{
                                    formatNumber(
                                        productionSummary.tubes_planned
                                    )
                                }}
                            </p>
                            <p class="text-sm text-green-700">Tubes Planned</p>
                        </div>
                        <div class="text-center p-4 bg-purple-50 rounded-lg">
                            <p class="text-2xl font-bold text-purple-600">
                                {{
                                    formatNumber(
                                        productionSummary.tubes_finalized
                                    )
                                }}
                            </p>
                            <p class="text-sm text-purple-700">
                                Tubes Finalized
                            </p>
                        </div>
                        <div class="text-center p-4 bg-yellow-50 rounded-lg">
                            <p class="text-2xl font-bold text-yellow-600">
                                {{
                                    formatNumber(
                                        productionSummary.sheets_received
                                    )
                                }}
                            </p>
                            <p class="text-sm text-yellow-700">
                                Sheets Received
                            </p>
                        </div>
                    </div>
                </CardBox>

                <!-- System Statistics -->
                <CardBox>
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        <BaseIcon :path="mdiCog" class="mr-2 text-gray-600" />
                        System Statistics
                    </h3>
                    <div class="space-y-4">
                        <div
                            class="flex justify-between items-center p-3 bg-gray-50 rounded-lg"
                        >
                            <div class="flex items-center">
                                <BaseIcon
                                    :path="mdiAccountMultiple"
                                    class="mr-2 text-blue-600"
                                />
                                <span>Total Users</span>
                            </div>
                            <span class="font-bold text-blue-600">{{
                                formatNumber(systemStats.total_users)
                            }}</span>
                        </div>
                        <div
                            class="flex justify-between items-center p-3 bg-gray-50 rounded-lg"
                        >
                            <div class="flex items-center">
                                <BaseIcon
                                    :path="mdiAccountGroup"
                                    class="mr-2 text-green-600"
                                />
                                <span>Active Clients</span>
                            </div>
                            <span class="font-bold text-green-600">{{
                                formatNumber(systemStats.active_clients)
                            }}</span>
                        </div>
                        <div
                            class="flex justify-between items-center p-3 bg-gray-50 rounded-lg"
                        >
                            <div class="flex items-center">
                                <BaseIcon
                                    :path="mdiDomain"
                                    class="mr-2 text-purple-600"
                                />
                                <span>Total Suppliers</span>
                            </div>
                            <span class="font-bold text-purple-600">{{
                                formatNumber(systemStats.total_suppliers)
                            }}</span>
                        </div>
                        <div
                            class="flex justify-between items-center p-3 bg-gray-50 rounded-lg"
                        >
                            <div class="flex items-center">
                                <BaseIcon
                                    :path="mdiInformation"
                                    class="mr-2 text-orange-600"
                                />
                                <span>Total Activities</span>
                            </div>
                            <span class="font-bold text-orange-600">{{
                                formatNumber(systemStats.total_activities)
                            }}</span>
                        </div>
                    </div>
                </CardBox>
            </div>
        </SectionMain>
    </LayoutAuthenticated>
</template>

<style scoped>
.grid {
    gap: 1.5rem;
}

@media (max-width: 768px) {
    .grid {
        gap: 1rem;
    }
}
</style>
