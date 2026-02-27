<script setup>
import { Head, Link, usePage, router } from "@inertiajs/vue3";
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import {
    mdiAlert,
    mdiCheck,
    mdiClose,
} from "@mdi/js";
import SectionMain from "@/components/SectionMain.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import BaseButton from "@/components/BaseButton.vue";

import CardBox from "@/components/CardBox.vue";
import CardBoxModal from "@/components/CardBoxModal.vue";
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
    resourceNeo: {
        type: Object,
        default: () => ({}),
    },
});

const isModalApproveActive = ref(false);
const isModalRejectActive = ref(false);
const selectedRefund = ref(null);
const refundAmount = ref(0);
const adminNotes = ref('');
const rejectionReason = ref('');

const openApproveModal = (refund) => {
    selectedRefund.value = refund;
    refundAmount.value = refund.total;
    adminNotes.value = '';
    isModalApproveActive.value = true;
};

const openRejectModal = (refund) => {
    selectedRefund.value = refund;
    rejectionReason.value = '';
    isModalRejectActive.value = true;
};

const approveRefund = () => {
    if (selectedRefund.value) {
        router.post(
            route('refund.approve', selectedRefund.value.id),
            {
                refund_amount: refundAmount.value,
                admin_notes: adminNotes.value,
            },
            {
                preserveScroll: true,
                onFinish: () => {
                    isModalApproveActive.value = false;
                    selectedRefund.value = null;
                },
            }
        );
    }
};

const rejectRefund = () => {
    if (selectedRefund.value && rejectionReason.value) {
        router.post(
            route('refund.reject', selectedRefund.value.id),
            { rejection_reason: rejectionReason.value },
            {
                preserveScroll: true,
                onFinish: () => {
                    isModalRejectActive.value = false;
                    selectedRefund.value = null;
                },
            }
        );
    }
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

const getStatusBadgeClass = (status) => {
    const classes = {
        refund_requested: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        refunded: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const formatStatus = (status) => {
    return status === 'refund_requested' ? 'Pending' : 'Refunded';
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
                    <!-- Sub-Order Number -->
                    <template #cell(sub_order_number)="{ item: dItem }">
                        <div>
                            <span class="font-medium">{{ dItem.sub_order_number }}</span>
                            <p class="text-xs text-gray-500">{{ dItem.order?.order_number }}</p>
                        </div>
                    </template>

                    <!-- Refund Reason -->
                    <template #cell(refund_reason)="{ item: dItem }">
                        <span class="text-sm">{{ dItem.refund_reason || '-' }}</span>
                    </template>

                    <!-- Status Badge -->
                    <template #cell(status)="{ item: dItem }">
                        <span
                            class="px-2 py-1 text-xs font-semibold rounded-full"
                            :class="getStatusBadgeClass(dItem.status)"
                        >
                            {{ formatStatus(dItem.status) }}
                        </span>
                    </template>

                    <!-- Actions -->
                    <template #cell(actions)="{ item: dItem }">
                        <div class="relative menu-container">
                            <ActionMenu
                                :item="dItem"
                                :extra-links="[]"
                                :action-expand="props.resourceNeo.actionExpand"
                                :is-open="openMenuIds.has(dItem.id)"
                                :menu-classes="actionClasses"
                                @toggle="toggleMenu(dItem.id)"
                            >
                                <!-- Approve Button -->
                                <button
                                    v-if="dItem.status === 'refund_requested' && can('refund_approve')"
                                    :class="[actionClasses.button]"
                                    @click="openApproveModal(dItem)"
                                >
                                    <BaseButton
                                        :class="[actionClasses.button, 'w-auto']"
                                        color="success"
                                        :icon="mdiCheck"
                                        small
                                        title="Approve Refund"
                                    />
                                </button>

                                <!-- Reject Button -->
                                <button
                                    v-if="dItem.status === 'refund_requested' && can('refund_reject')"
                                    :class="[actionClasses.button]"
                                    @click="openRejectModal(dItem)"
                                >
                                    <BaseButton
                                        :class="[actionClasses.button, 'w-auto']"
                                        color="danger"
                                        :icon="mdiClose"
                                        small
                                        title="Reject Refund"
                                    />
                                </button>
                            </ActionMenu>
                        </div>
                    </template>
                </Table>
            </CardBox>
        </SectionMain>

        <!-- Approve Refund Modal -->
        <CardBoxModal
            v-model="isModalApproveActive"
            buttonLabel="Approve Refund"
            title="Approve Refund Request"
            button="success"
            has-cancel
            @confirm="approveRefund"
        >
            <p class="mb-4">Approve refund for <strong>{{ selectedRefund?.sub_order_number }}</strong>?</p>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Refund Amount (₹) *</label>
                    <input
                        v-model.number="refundAmount"
                        type="number"
                        step="0.01"
                        :max="selectedRefund?.total"
                        class="w-full px-3 py-2 border rounded-lg dark:bg-gray-800 dark:border-gray-700"
                        required
                    />
                    <p class="text-xs text-gray-500 mt-1">Max: ₹{{ selectedRefund?.total }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Admin Notes (optional)</label>
                    <textarea
                        v-model="adminNotes"
                        rows="2"
                        class="w-full px-3 py-2 border rounded-lg dark:bg-gray-800 dark:border-gray-700"
                        placeholder="Any notes about this refund..."
                    ></textarea>
                </div>
            </div>
        </CardBoxModal>

        <!-- Reject Refund Modal -->
        <CardBoxModal
            v-model="isModalRejectActive"
            buttonLabel="Reject Refund"
            title="Reject Refund Request"
            button="danger"
            has-cancel
            @confirm="rejectRefund"
        >
            <p class="mb-4">Reject refund for <strong>{{ selectedRefund?.sub_order_number }}</strong>?</p>
            <div>
                <label class="block text-sm font-medium mb-1">Rejection Reason *</label>
                <textarea
                    v-model="rejectionReason"
                    rows="3"
                    class="w-full px-3 py-2 border rounded-lg dark:bg-gray-800 dark:border-gray-700"
                    placeholder="Explain why this refund is being rejected..."
                    required
                ></textarea>
            </div>
        </CardBoxModal>
    </LayoutAuthenticated>
</template>

<style scoped>
:deep(body) {
    @apply cursor-pointer;
}
</style>
