<script setup>
import { Head, Link, usePage, router, useForm } from "@inertiajs/vue3";
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import {
    mdiArrowLeft,
    mdiAlert,
    mdiPackage,
    mdiTruck,
    mdiCash,
    mdiAccount,
    mdiMapMarker,
    mdiStore,
} from "@mdi/js";
import SectionMain from "@/components/SectionMain.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import BaseButton from "@/components/BaseButton.vue";
import CardBox from "@/components/CardBox.vue";
import CardBoxModal from "@/components/CardBoxModal.vue";
import NotificationBar from "@/components/NotificationBar.vue";
import BaseDivider from "@/components/BaseDivider.vue";
import BaseIcon from "@/components/BaseIcon.vue";
import { useToast } from "vue-toast-notification";
import "vue-toast-notification/dist/theme-sugar.css";
import { computed, onMounted, ref } from "vue";
import { can } from '@/utils/permissions';

const message = computed(() => usePage().props.flash.message);
const msg_type = computed(() => usePage().props.flash.msg_type ?? "warning");

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
    sellers: {
        type: Object,
        default: () => ({}),
    },
    resourceNeo: {
        type: Object,
        default: () => ({}),
    },
});

const isModalCancelActive = ref(false);
const isModalStatusActive = ref(false);
const selectedStatus = ref('');

const statusForm = useForm({
    order_status: '',
});

const cancelOrder = () => {
    router.post(
        route('order.cancel', props.order.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                isModalCancelActive.value = false;
            },
        }
    );
};

const updateOrderStatus = () => {
    statusForm.order_status = selectedStatus.value;
    statusForm.put(route('order.update', props.order.id), {
        preserveScroll: true,
        onSuccess: () => {
            isModalStatusActive.value = false;
            selectedStatus.value = '';
        },
    });
};

const openStatusModal = (status) => {
    selectedStatus.value = status;
    isModalStatusActive.value = true;
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

const getOrderStatusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        confirmed: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        processing: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
        packed: 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-300',
        shipped: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        out_for_delivery: 'bg-violet-100 text-violet-800 dark:bg-violet-900 dark:text-violet-300',
        delivered: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        returned: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
        refund_requested: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
        refunded: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getPaymentStatusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        paid: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        failed: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        refunded: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
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
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatMoney = (amount) => {
    return '₹' + Number(amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
};

const canBeCancelled = computed(() => {
    return ['pending', 'confirmed', 'processing'].includes(props.order.order_status);
});

const getProductImage = (item) => {
    // 1. Check item's snapshot image
    if (item.product_image) {
        if (item.product_image.startsWith('http')) return item.product_image;
        return '/storage/' + item.product_image;
    }
    
    // 2. Fallback to current product main image
    if (item.product?.main_image) {
        if (item.product.main_image.startsWith('http')) return item.product.main_image;
        return '/storage/' + item.product.main_image;
    }

    // 3. Fallback to gallery
    if (item.product?.images?.length > 0) {
        return '/storage/' + item.product.images[0].path;
    }

    return '/images/placeholder-product.png';
};

const getSellerName = (sellerId) => {
    return props.sellers[sellerId]?.business_name || 'Unknown Seller';
};
</script>

<template>
    <LayoutAuthenticated>
        <Head :title="'Order ' + order.order_number" />
        <SectionMain>
            <SectionTitleLineWithButton
                :icon="props.resourceNeo.iconPath"
                :title="'Order: ' + order.order_number"
                main
            >
                <Link :href="route('order.index')">
                    <BaseButton
                        :icon="mdiArrowLeft"
                        color="contrast"
                        rounded-full
                        small
                        label="Back to Orders"
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

            <!-- Order Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <!-- Order Status -->
                <CardBox class="p-4">
                    <div class="flex items-center">
                        <BaseIcon :path="mdiPackage" class="text-blue-500" w="w-10" h="h-10" />
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Order Status</p>
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full"
                                :class="getOrderStatusBadgeClass(order.order_status)"
                            >
                                {{ formatStatus(order.order_status) }}
                            </span>
                        </div>
                    </div>
                </CardBox>

                <!-- Payment Status -->
                <CardBox class="p-4">
                    <div class="flex items-center">
                        <BaseIcon :path="mdiCash" class="text-green-500" w="w-10" h="h-10" />
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Payment</p>
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full"
                                :class="getPaymentStatusBadgeClass(order.payment_status)"
                            >
                                {{ formatStatus(order.payment_status) }}
                            </span>
                        </div>
                    </div>
                </CardBox>

                <!-- Total Amount -->
                <CardBox class="p-4">
                    <div class="flex items-center">
                        <BaseIcon :path="mdiCash" class="text-purple-500" w="w-10" h="h-10" />
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Amount</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ formatMoney(order.total) }}</p>
                        </div>
                    </div>
                </CardBox>

                <!-- Sellers Count -->
                <CardBox class="p-4">
                    <div class="flex items-center">
                        <BaseIcon :path="mdiStore" class="text-orange-500" w="w-10" h="h-10" />
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Sellers</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ order.sub_orders?.length || 0 }}</p>
                        </div>
                    </div>
                </CardBox>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Order Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Sub-Orders by Seller -->
                    <CardBox v-for="subOrder in order.sub_orders" :key="subOrder.id" class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold flex items-center">
                                    <BaseIcon :path="mdiStore" class="mr-2 text-gray-500" />
                                    {{ getSellerName(subOrder.seller_id) }}
                                </h3>
                                <p class="text-sm text-gray-500">Sub-Order: {{ subOrder.sub_order_number }}</p>
                            </div>
                            <span
                                class="px-3 py-1 text-sm font-semibold rounded-full"
                                :class="getOrderStatusBadgeClass(subOrder.status)"
                            >
                                {{ formatStatus(subOrder.status) }}
                            </span>
                        </div>

                        <!-- Tracking Info -->
                        <div v-if="subOrder.tracking_number" class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div class="flex items-center">
                                <BaseIcon :path="mdiTruck" class="text-blue-500 mr-2" />
                                <span class="text-sm">
                                    <strong>{{ subOrder.courier || 'Courier' }}:</strong>
                                    {{ subOrder.tracking_number }}
                                </span>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <table class="w-full">
                            <thead>
                                <tr class="border-b dark:border-gray-700">
                                    <th class="text-left py-2 text-sm font-medium text-gray-500">Product</th>
                                    <th class="text-center py-2 text-sm font-medium text-gray-500">Qty</th>
                                    <th class="text-right py-2 text-sm font-medium text-gray-500">Price</th>
                                    <th class="text-right py-2 text-sm font-medium text-gray-500">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in subOrder.items" :key="item.id" class="border-b dark:border-gray-700">
                                    <td class="py-3">
                                        <div class="flex items-center">
                                            <img
                                                :src="getProductImage(item)"
                                                :alt="item.product_name"
                                                class="w-12 h-12 object-cover rounded mr-3"
                                            />
                                            <div>
                                                <a
                                                    :href="item.product?.slug ? route('marketplace.product.detail', item.product.slug) : '#'"
                                                    target="_blank"
                                                    class="font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors duration-200"
                                                >
                                                    {{ item.product_name }}
                                                </a>
                                                <p v-if="item.variant_details?.label" class="text-sm text-gray-500">{{ item.variant_details.label }}</p>
                                                <p v-else-if="item.variant_name" class="text-sm text-gray-500">{{ item.variant_name }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center py-3">{{ item.quantity }}</td>
                                    <td class="text-right py-3">{{ formatMoney(item.price) }}</td>
                                    <td class="text-right py-3 font-medium">{{ formatMoney(item.total) }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="font-semibold">
                                    <td colspan="3" class="text-right py-3">Subtotal:</td>
                                    <td class="text-right py-3">{{ formatMoney(subOrder.subtotal) }}</td>
                                </tr>
                                <tr v-if="subOrder.shipping_charge > 0">
                                    <td colspan="3" class="text-right py-1 text-sm text-gray-500">Shipping:</td>
                                    <td class="text-right py-1 text-sm">{{ formatMoney(subOrder.shipping_charge) }}</td>
                                </tr>
                                <tr class="font-bold text-lg">
                                    <td colspan="3" class="text-right py-2">Total:</td>
                                    <td class="text-right py-2">{{ formatMoney(subOrder.total) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </CardBox>

                    <!-- Order Timeline -->
                    <CardBox class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Order Timeline</h3>
                        <div class="space-y-3">
                            <div class="flex items-center text-sm">
                                <span class="w-32 text-gray-500">Created:</span>
                                <span>{{ formatDate(order.created_at) }}</span>
                            </div>
                            <div v-if="order.confirmed_at" class="flex items-center text-sm">
                                <span class="w-32 text-gray-500">Confirmed:</span>
                                <span>{{ formatDate(order.confirmed_at) }}</span>
                            </div>
                            <div v-if="order.shipped_at" class="flex items-center text-sm">
                                <span class="w-32 text-gray-500">Shipped:</span>
                                <span>{{ formatDate(order.shipped_at) }}</span>
                            </div>
                            <div v-if="order.delivered_at" class="flex items-center text-sm">
                                <span class="w-32 text-gray-500">Delivered:</span>
                                <span>{{ formatDate(order.delivered_at) }}</span>
                            </div>
                            <div v-if="order.cancelled_at" class="flex items-center text-sm text-red-600">
                                <span class="w-32">Cancelled:</span>
                                <span>{{ formatDate(order.cancelled_at) }}</span>
                            </div>
                        </div>
                    </CardBox>
                </div>

                <!-- Right Column - Customer & Payment Info -->
                <div class="space-y-6">
                    <!-- Customer Info -->
                    <CardBox class="p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <BaseIcon :path="mdiAccount" class="mr-2 text-gray-500" />
                            Customer
                        </h3>
                        <div class="space-y-2 text-sm">
                            <p><strong>Name:</strong> {{ order.user?.name || 'Guest' }}</p>
                            <p><strong>Email:</strong> {{ order.user?.email || '-' }}</p>
                            <p><strong>Phone:</strong> {{ order.user?.phone || '-' }}</p>
                        </div>
                    </CardBox>

                    <!-- Shipping Address -->
                    <CardBox class="p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <BaseIcon :path="mdiMapMarker" class="mr-2 text-gray-500" />
                            Shipping Address
                        </h3>
                        <div v-if="order.shipping_address" class="text-sm space-y-1">
                            <p class="font-medium">{{ order.shipping_address.name }}</p>
                            <p>{{ order.shipping_address.address_line_1 }}</p>
                            <p v-if="order.shipping_address.address_line_2">{{ order.shipping_address.address_line_2 }}</p>
                            <p v-if="order.shipping_address.landmark">{{ order.shipping_address.landmark }}</p>
                            <p>{{ order.shipping_address.city }}, {{ order.shipping_address.state }}</p>
                            <p>{{ order.shipping_address.pincode }}</p>
                            <p v-if="order.shipping_address.phone" class="mt-2">
                                <strong>Phone:</strong> {{ order.shipping_address.phone }}
                            </p>
                        </div>
                        <p v-else class="text-gray-500">No shipping address</p>
                    </CardBox>

                    <!-- Order Summary -->
                    <CardBox class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Order Summary</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Subtotal:</span>
                                <span>{{ formatMoney(order.subtotal) }}</span>
                            </div>
                            <div v-if="order.discount > 0" class="flex justify-between text-green-600">
                                <span>Discount:</span>
                                <span>-{{ formatMoney(order.discount) }}</span>
                            </div>
                            <div v-if="order.coupon_code" class="flex justify-between text-green-600">
                                <span>Coupon ({{ order.coupon_code }}):</span>
                                <span>-{{ formatMoney(order.coupon_discount) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Shipping:</span>
                                <span>{{ formatMoney(order.shipping_charge) }}</span>
                            </div>
                            <div v-if="order.tax > 0" class="flex justify-between">
                                <span class="text-gray-500">Tax:</span>
                                <span>{{ formatMoney(order.tax) }}</span>
                            </div>
                            <BaseDivider />
                            <div class="flex justify-between font-bold text-lg">
                                <span>Total:</span>
                                <span>{{ formatMoney(order.total) }}</span>
                            </div>
                        </div>
                    </CardBox>

                    <!-- Payment Info -->
                    <CardBox class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Payment Info</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Method:</span>
                                <span class="capitalize">{{ order.payment_method || '-' }}</span>
                            </div>
                            <div v-if="order.payment_id" class="flex justify-between">
                                <span class="text-gray-500">Transaction ID:</span>
                                <span class="font-mono text-xs">{{ order.payment_id }}</span>
                            </div>
                        </div>
                    </CardBox>

                    <!-- Admin Actions -->
                    <CardBox v-if="can('order_cancel') || can('order_update')" class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Actions</h3>
                        <div class="space-y-3">
                            <BaseButton
                                v-if="canBeCancelled && can('order_cancel')"
                                color="danger"
                                label="Cancel Order"
                                class="w-full"
                                @click="isModalCancelActive = true"
                            />
                            <p v-else-if="!canBeCancelled" class="text-sm text-gray-500">
                                This order cannot be cancelled.
                            </p>
                        </div>
                    </CardBox>
                </div>
            </div>
        </SectionMain>

        <!-- Cancel Confirmation Modal -->
        <CardBoxModal
            v-model="isModalCancelActive"
            buttonLabel="Cancel Order"
            title="Cancel Order"
            button="danger"
            has-cancel
            @confirm="cancelOrder"
        >
            <p class="mb-4">Are you sure you want to cancel this order?</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                This action will cancel all sub-orders and restore stock.
            </p>
        </CardBoxModal>
    </LayoutAuthenticated>
</template>
