<script setup>
import { Head, usePage, Link } from "@inertiajs/vue3";
import {
    mdiViewDashboard,
    mdiAlert,
    mdiPackageVariant,
    mdiFileDocumentOutline,
    mdiCashMultiple,
    mdiCurrencyUsd,
    mdiPipe,
    mdiCandle,
    mdiChartPie,
    mdiChartBar,
    mdiTrendingUp,
    mdiFactory,
    mdiAccountGroup,
    mdiTruckDelivery
} from "@mdi/js";

import { computed } from "vue";
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
} from "chart.js";
import { Line, Bar } from "vue-chartjs";


import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import NotificationBar from "@/components/NotificationBar.vue";
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
    BarElement
);

const props = defineProps({});

const message = computed(() => usePage().props.flash.message);
const msg_type = computed(() => usePage().props.flash.msg_type ?? "warning");
const user_name = usePage().props.auth.user.name;

// Helper functions
const formatNumber = (num) => {
    return new Intl.NumberFormat("en-IN").format(num || 0);
};

const formatWeight = (weight) => {
    return new Intl.NumberFormat("en-IN", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(weight || 0) + " kg";
};

</script>

<template>
    <LayoutAuthenticated>
        <Head title="Dashboard" />
        
        <div class="px-3 py-1 flex-1 min-h-[calc(100vh-64px)] xl:max-w-full xl:mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 animate-fade-in-down">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-800 dark:text-white tracking-tight flex items-center gap-3">
                        <span class="bg-gradient-to-br from-blue-600 to-indigo-600 text-white p-2.5 rounded-xl shadow-lg shadow-blue-500/30">
                            <BaseIcon :path="mdiViewDashboard" size="28" />
                        </span>
                        Dashboard
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm font-medium ml-1">
                        Welcome back, <span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ user_name }}</span>
                    </p>
                </div>
            </div>

            <NotificationBar
                v-if="message"
                @closed="usePage().props.flash.message = ''"
                :color="msg_type"
                :icon="mdiAlert"
                :outline="true"
                class="mb-6"
            >
                {{ message }}
            </NotificationBar>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                            
            </div>

        </div>
    </LayoutAuthenticated>
</template>

<style scoped>
.animate-fade-in-down {
    animation: fadeInDown 0.5s ease-out;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
