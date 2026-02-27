<script setup>
import { Head, Link, usePage, router } from "@inertiajs/vue3";
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import {
    mdiAlert,
    mdiEye,
    mdiCloseCircle,
} from "@mdi/js";
import SectionMain from "@/components/SectionMain.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import BaseButton from "@/components/BaseButton.vue";

import CardBoxModal from "@/components/CardBoxModal.vue";
import CardBox from "@/components/CardBox.vue";
import NotificationBar from "@/components/NotificationBar.vue";
import Table from "@/components/DataTable/Table.vue";
import { useToast } from "vue-toast-notification";
import "vue-toast-notification/dist/theme-sugar.css";
import { computed, onMounted, ref, onUnmounted } from "vue";
import ActionMenu from "@/components/ActionMenu.vue";
import { can } from '@/utils/permissions';

const message = computed(() => usePage().props.flash.message);
const msg_type = computed(() => usePage().props.flash.msg_type ?? "warning");
const props = defineProps({
    resourceData: {
        type: Object,
        default: () => ({}),
    },
    can: {
        type: Object,
        default: () => ({}),
    },
    resourceNeo: {
        type: Object,
        default: () => ({}),
    },
});

const isModalCancelActive = ref(false);
const selectedOrder = ref(null);

const cancelOrder = () => {
    if (selectedOrder.value) {
        router.post(
            route('order.cancel', selectedOrder.value.id),
            {},
            {
                preserveScroll: true,
                onFinish: () => {
                    isModalCancelActive.value = false;
                    selectedOrder.value = null;
                },
            }
        );
    }
};

const openCancelModal = (order) => {
    selectedOrder.value = order;
    isModalCancelActive.value = true;
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
    window.addEventListener("click", handleWindowClick);
});

onUnmounted(() => {
    window.removeEventListener("click", handleWindowClick);
});

const openMenuIds = ref(new Set());

const toggleMenu = (id) => {
    if (openMenuIds.value.has(id)) {
        openMenuIds.value.delete(id);
    } else {
        openMenuIds.value.clear();
        openMenuIds.value.add(id);
    }
};

const closeAllMenus = () => {
    openMenuIds.value.clear();
};

const handleWindowClick = (event) => {
    const isMenuClick = event.target.closest(".menu-container");
    if (!isMenuClick) {
        closeAllMenus();
    }
};

const actionClasses = {
    menuItem: "block hover:bg-gray-100 dark:hover:bg-gray-700",
    button: "w-full text-left",
    dropdown:
        "absolute right-0 z-50 mt-2 bg-white border rounded-md shadow-lg dark:bg-gray-800 dark:border-gray-700",
};

const getOrderStatusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        confirmed: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        processing: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
        shipped: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        out_for_delivery: 'bg-violet-100 text-violet-800 dark:bg-violet-900 dark:text-violet-300',
        delivered: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        returned: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
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

const canBeCancelled = (status) => {
    return ['pending', 'confirmed', 'processing'].includes(status);
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
            >
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
                <Table
                    :resource="resourceData"
                    :resourceNeo="resourceNeo"
                    :stickyHeader="!0"
                >
                    <!-- Order Number with Link -->
                    <template #cell(order_number)="{ item: dItem }">
                        <Link
                            :href="route('order.show', dItem.id)"
                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                        >
                            {{ dItem.order_number }}
                        </Link>
                    </template>

                    <!-- Customer Info -->
                    <template #cell(customer_name)="{ item: dItem }">
                        <div>
                            <p class="font-medium">{{ dItem.customer_name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ dItem.customer_email }}</p>
                        </div>
                    </template>

                    <!-- Order Status Badge -->
                    <template #cell(order_status)="{ item: dItem }">
                        <span
                            class="px-2 py-1 text-xs font-semibold rounded-full"
                            :class="getOrderStatusBadgeClass(dItem.order_status)"
                        >
                            {{ formatStatus(dItem.order_status) }}
                        </span>
                    </template>

                    <!-- Payment Status Badge -->
                    <template #cell(payment_status)="{ item: dItem }">
                        <span
                            class="px-2 py-1 text-xs font-semibold rounded-full"
                            :class="getPaymentStatusBadgeClass(dItem.payment_status)"
                        >
                            {{ formatStatus(dItem.payment_status) }}
                        </span>
                    </template>

                    <!-- Sellers Count -->
                    <template #cell(sellers_count)="{ item: dItem }">
                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 dark:bg-gray-700 rounded">
                            {{ dItem.sellers_count }} seller{{ dItem.sellers_count > 1 ? 's' : '' }}
                        </span>
                    </template>

                    <!-- Actions -->
                    <template #cell(actions)="{ item: dItem }">
                        <div class="relative menu-container">
                            <ActionMenu
                                :item="dItem"
                                :extra-links="props.resourceNeo.extraLinks"
                                :action-expand="props.resourceNeo.actionExpand"
                                :is-open="openMenuIds.has(dItem.id)"
                                :menu-classes="actionClasses"
                                @toggle="toggleMenu(dItem.id)"
                            >
                                <!-- View Button -->
                                <Link
                                    v-if="can('order_view')"
                                    :href="route('order.show', dItem.id)"
                                    :class="[actionClasses.menuItem]"
                                >
                                    <BaseButton
                                        :class="[actionClasses.button, 'w-auto']"
                                        color="info"
                                        :icon="mdiEye"
                                        small
                                        title="View Order"
                                    />
                                </Link>

                                <!-- Cancel Button (only for pending/confirmed/processing) -->
                                <button
                                    v-if="canBeCancelled(dItem.order_status) && can('order_cancel')"
                                    :class="[actionClasses.button]"
                                    @click="openCancelModal(dItem)"
                                >
                                    <BaseButton
                                        :class="[actionClasses.button, 'w-auto']"
                                        color="danger"
                                        :icon="mdiCloseCircle"
                                        small
                                        title="Cancel Order"
                                    />
                                </button>
                            </ActionMenu>
                        </div>
                    </template>
                </Table>
            </CardBox>
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
            <p class="mb-4">Are you sure you want to cancel order <strong>{{ selectedOrder?.order_number }}</strong>?</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                This action will:
            </p>
            <ul class="mt-2 text-sm text-gray-600 dark:text-gray-400 list-disc list-inside">
                <li>Cancel all sub-orders from sellers</li>
                <li>Restore stock for all items</li>
                <li>Mark the order as cancelled</li>
            </ul>
        </CardBoxModal>
    </LayoutAuthenticated>
</template>

<style scoped>
:deep(body) {
    @apply cursor-pointer;
}
</style>
