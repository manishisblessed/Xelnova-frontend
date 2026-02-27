<script setup>
import { Head, Link, usePage } from "@inertiajs/vue3";
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import {
    mdiArrowLeft,
    mdiAlert,
    mdiAccount,
    mdiEmail,
    mdiPhone,
    mdiCart,
    mdiCash,
    mdiMapMarker,
    mdiStar,
    mdiCalendar,
    mdiCheck,
    mdiClose,
} from "@mdi/js";
import SectionMain from "@/components/SectionMain.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import BaseButton from "@/components/BaseButton.vue";
import CardBox from "@/components/CardBox.vue";
import NotificationBar from "@/components/NotificationBar.vue";
import BaseDivider from "@/components/BaseDivider.vue";
import BaseIcon from "@/components/BaseIcon.vue";
import { useToast } from "vue-toast-notification";
import "vue-toast-notification/dist/theme-sugar.css";
import { computed, onMounted } from "vue";

const message = computed(() => usePage().props.flash.message);
const msg_type = computed(() => usePage().props.flash.msg_type ?? "warning");

const props = defineProps({
    customer: {
        type: Object,
        required: true,
    },
    recentOrders: {
        type: Array,
        default: () => [],
    },
    resourceNeo: {
        type: Object,
        default: () => ({}),
    },
});

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

const getOrderStatusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        confirmed: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        processing: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
        shipped: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        delivered: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const formatStatus = (status) => {
    if (!status) return '';
    return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const formatDateTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatMoney = (amount) => {
    return '₹' + Number(amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
};

const isVerified = computed(() => {
    return props.customer.email_verified_at || props.customer.phone_verified_at;
});
</script>

<template>
    <LayoutAuthenticated>
        <Head :title="'Customer: ' + (customer.name || 'Unknown')" />
        <SectionMain>
            <SectionTitleLineWithButton
                :icon="props.resourceNeo.iconPath"
                :title="'Customer: ' + (customer.name || 'Unknown')"
                main
            >
                <Link :href="route('customer.index')">
                    <BaseButton
                        :icon="mdiArrowLeft"
                        color="contrast"
                        rounded-full
                        small
                        label="Back to Customers"
                    />
                </Link>
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

            <!-- Customer Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <!-- Total Orders -->
                <CardBox class="p-4">
                    <div class="flex items-center">
                        <BaseIcon :path="mdiCart" class="text-blue-500" w="w-10" h="h-10" />
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Orders</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ customer.orders_count || 0 }}</p>
                        </div>
                    </div>
                </CardBox>

                <!-- Total Spent -->
                <CardBox class="p-4">
                    <div class="flex items-center">
                        <BaseIcon :path="mdiCash" class="text-green-500" w="w-10" h="h-10" />
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Spent</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ formatMoney(customer.orders_sum_total) }}</p>
                        </div>
                    </div>
                </CardBox>

                <!-- Verification Status -->
                <CardBox class="p-4">
                    <div class="flex items-center">
                        <BaseIcon :path="isVerified ? mdiCheck : mdiClose" :class="isVerified ? 'text-green-500' : 'text-gray-500'" w="w-10" h="h-10" />
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Verified</p>
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full"
                                :class="isVerified ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                            >
                                {{ isVerified ? 'Yes' : 'No' }}
                            </span>
                        </div>
                    </div>
                </CardBox>

                <!-- Member Since -->
                <CardBox class="p-4">
                    <div class="flex items-center">
                        <BaseIcon :path="mdiCalendar" class="text-purple-500" w="w-10" h="h-10" />
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Member Since</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ formatDate(customer.created_at) }}</p>
                        </div>
                    </div>
                </CardBox>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Orders -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Recent Orders -->
                    <CardBox class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Recent Orders</h3>
                        <div v-if="recentOrders.length > 0" class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b dark:border-gray-700">
                                        <th class="text-left py-2 text-sm font-medium text-gray-500">Order #</th>
                                        <th class="text-left py-2 text-sm font-medium text-gray-500">Date</th>
                                        <th class="text-right py-2 text-sm font-medium text-gray-500">Total</th>
                                        <th class="text-center py-2 text-sm font-medium text-gray-500">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="order in recentOrders" :key="order.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="py-3">
                                            <Link
                                                :href="route('order.show', order.id)"
                                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 font-medium"
                                            >
                                                {{ order.order_number }}
                                            </Link>
                                        </td>
                                        <td class="py-3 text-sm text-gray-600 dark:text-gray-400">
                                            {{ formatDate(order.created_at) }}
                                        </td>
                                        <td class="py-3 text-right font-medium">
                                            {{ formatMoney(order.total) }}
                                        </td>
                                        <td class="py-3 text-center">
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full"
                                                :class="getOrderStatusBadgeClass(order.order_status)"
                                            >
                                                {{ formatStatus(order.order_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p v-else class="text-gray-500 text-center py-8">No orders yet</p>
                    </CardBox>

                    <!-- Reviews (if any) -->
                    <CardBox v-if="customer.reviews?.length > 0" class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Customer Reviews</h3>
                        <div class="space-y-4">
                            <div v-for="review in customer.reviews.slice(0, 5)" :key="review.id" class="border-b dark:border-gray-700 pb-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="font-medium">{{ review.product?.name || 'Product' }}</p>
                                        <div class="flex items-center mt-1">
                                            <BaseIcon v-for="i in 5" :key="i" :path="mdiStar" :class="i <= review.rating ? 'text-yellow-400' : 'text-gray-300'" w="w-4" h="h-4" />
                                        </div>
                                    </div>
                                    <span class="text-sm text-gray-500">{{ formatDate(review.created_at) }}</span>
                                </div>
                                <p v-if="review.comment" class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ review.comment }}</p>
                            </div>
                        </div>
                    </CardBox>
                </div>

                <!-- Right Column - Customer Info -->
                <div class="space-y-6">
                    <!-- Contact Info -->
                    <CardBox class="p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <BaseIcon :path="mdiAccount" class="mr-2 text-gray-500" />
                            Contact Info
                        </h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center">
                                <BaseIcon :path="mdiEmail" class="mr-3 text-gray-400" w="w-5" h="h-5" />
                                <span>{{ customer.email || 'Not provided' }}</span>
                            </div>
                            <div class="flex items-center">
                                <BaseIcon :path="mdiPhone" class="mr-3 text-gray-400" w="w-5" h="h-5" />
                                <span>{{ customer.phone || 'Not provided' }}</span>
                            </div>
                        </div>
                    </CardBox>

                    <!-- Saved Addresses -->
                    <CardBox class="p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <BaseIcon :path="mdiMapMarker" class="mr-2 text-gray-500" />
                            Saved Addresses
                        </h3>
                        <div v-if="customer.addresses?.length > 0" class="space-y-4">
                            <div v-for="address in customer.addresses" :key="address.id" class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                <div class="flex items-start justify-between">
                                    <span class="text-xs font-medium uppercase text-gray-500">{{ address.type || 'Address' }}</span>
                                    <span v-if="address.is_default" class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded">Default</span>
                                </div>
                                <p class="mt-2 font-medium">{{ address.name }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ address.address_line_1 }}</p>
                                <p v-if="address.address_line_2" class="text-sm text-gray-600 dark:text-gray-400">{{ address.address_line_2 }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ address.city }}, {{ address.state }} - {{ address.pincode }}</p>
                                <p v-if="address.phone" class="text-sm text-gray-500 mt-1">{{ address.phone }}</p>
                            </div>
                        </div>
                        <p v-else class="text-gray-500">No saved addresses</p>
                    </CardBox>
                </div>
            </div>
        </SectionMain>
    </LayoutAuthenticated>
</template>
