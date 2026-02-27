<script setup>
import { Head, Link, usePage, router } from "@inertiajs/vue3";
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import {
    mdiPackageVariantPlus,
    mdiAlert,
    mdiFileEdit,
    mdiTrashCan,
    mdiCheck,
    mdiClose,
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

const delselect = ref(0);
const isModalDangerActive = ref(false);
const isModalApproveActive = ref(false);
const isModalSuspendActive = ref(false);
const selectedSeller = ref(null);
const suspendReason = ref('');

const deleteRecord = () => {
    if (delselect.value != 0) {
        router.delete(
            route(props.resourceNeo.resourceName + ".destroy", delselect.value),
            {
                preserveScroll: true,
                resetOnSuccess: false,
                onFinish: () => {
                    delselect.value = 0;
                },
            }
        );
    }
};

const approveSeller = () => {
    if (selectedSeller.value) {
        router.post(
            route('seller.approve', selectedSeller.value.id),
            {},
            {
                preserveScroll: true,
                onFinish: () => {
                    isModalApproveActive.value = false;
                    selectedSeller.value = null;
                },
            }
        );
    }
};

const suspendSeller = () => {
    if (selectedSeller.value && suspendReason.value) {
        router.post(
            route('seller.suspend', selectedSeller.value.id),
            { reason: suspendReason.value },
            {
                preserveScroll: true,
                onFinish: () => {
                    isModalSuspendActive.value = false;
                    selectedSeller.value = null;
                    suspendReason.value = '';
                },
            }
        );
    }
};

const openApproveModal = (seller) => {
    selectedSeller.value = seller;
    isModalApproveActive.value = true;
};

const openSuspendModal = (seller) => {
    selectedSeller.value = seller;
    isModalSuspendActive.value = true;
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

const checkConditions = (item, conditions) => {
    if (!conditions) return true;
    return conditions.every((rule) => {
        if (rule.cond === "==") return item[rule.key] == rule.compvl;
        if (rule.cond === "!=") return item[rule.key] != rule.compvl;
        if (rule.cond === ">") return item[rule.key] > rule.compvl;
        if (rule.cond === "<") return item[rule.key] < rule.compvl;
        if (rule.cond === "*") return true;
        return false;
    });
};

const getStatusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        approved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        suspended: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        rejected: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getVerificationBadgeClass = (status) => {
    const classes = {
        unverified: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
        verified: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
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
                <div class="flex">
                    <template
                        v-for="exLink in props.resourceNeo.extraMainLinks"
                    >
                        <Link :href="route(exLink.link)">
                            <BaseButton
                                class="m-2"
                                :icon="mdiPackageVariantPlus"
                                color="success"
                                rounded-full
                                small
                                :label="exLink.label"
                            />
                        </Link>
                    </template>
                    <Link
                        :href="
                            route(props.resourceNeo.resourceName + '.create')
                        "
                        v-if="
                            props.resourceNeo.actions.includes('c') &&
                            (can(props.resourceNeo.resourceName + '_create'
                                ))
                        "
                    >
                        <BaseButton
                            class="m-2"
                            :icon="mdiPackageVariantPlus"
                            color="success"
                            rounded-full
                            small
                            label="Add New"
                        />
                    </Link>
                </div>
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
                    <!-- Custom Status Badge -->
                    <template #cell(status)="{ item: dItem }">
                        <span
                            class="px-2 py-1 text-xs font-semibold rounded-full"
                            :class="getStatusBadgeClass(dItem.status)"
                        >
                            {{ dItem.status.charAt(0).toUpperCase() + dItem.status.slice(1) }}
                        </span>
                    </template>

                    <!-- Custom Verification Status Badge -->
                    <template #cell(verification_status)="{ item: dItem }">
                        <span
                            class="px-2 py-1 text-xs font-semibold rounded-full"
                            :class="getVerificationBadgeClass(dItem.verification_status)"
                        >
                            {{ dItem.verification_status.charAt(0).toUpperCase() + dItem.verification_status.slice(1) }}
                        </span>
                    </template>

                    <!-- Custom Actions with Approve/Suspend -->
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
                                <!-- Approve Button (for pending, suspended, and rejected sellers) -->
                                <button
                                    v-if="(dItem.status === 'pending' || dItem.status === 'suspended' || dItem.status === 'rejected') && can('seller_approve')"
                                    :class="[
                                        actionClasses.button,
                                        {
                                            'p-2': props.resourceNeo
                                                .actionExpand,
                                        },
                                    ]"
                                    @click="openApproveModal(dItem)"
                                >
                                    <BaseButton
                                        :class="[
                                            actionClasses.button,
                                            'w-auto',
                                        ]"
                                        color="success"
                                        :icon="mdiCheck"
                                        small
                                        :label="
                                            props.resourceNeo.actionExpand
                                                ? 'Approve'
                                                : ''
                                        "
                                        title="Approve"
                                    />
                                </button>

                                <!-- Suspend Button -->
                                <button
                                    v-if="dItem.status === 'approved' && can('seller_suspend')"
                                    :class="[
                                        actionClasses.button,
                                        {
                                            'p-2': props.resourceNeo
                                                .actionExpand,
                                        },
                                    ]"
                                    @click="openSuspendModal(dItem)"
                                >
                                    <BaseButton
                                        :class="[
                                            actionClasses.button,
                                            'w-auto',
                                        ]"
                                        color="danger"
                                        :icon="mdiClose"
                                        small
                                        :label="
                                            props.resourceNeo.actionExpand
                                                ? 'Suspend'
                                                : ''
                                        "
                                        title="Suspend"
                                    />
                                </button>

                                <!-- Edit Button -->
                                <Link
                                    v-if="
                                        checkConditions(
                                            dItem,
                                            props.resourceNeo.editRule
                                        ) &&
                                        props.resourceNeo.actions.includes(
                                            'u'
                                        ) &&
                                        (can(props.resourceNeo.resourceName +
                                                    '_edit'
                                            ))
                                    "
                                    :href="
                                        route(
                                            props.resourceNeo.resourceName +
                                                '.edit',
                                            dItem.id
                                        )
                                    "
                                    :class="[
                                        actionClasses.menuItem,
                                        {
                                            'p-2': props.resourceNeo
                                                .actionExpand,
                                        },
                                    ]"
                                >
                                    <BaseButton
                                        :class="[
                                            actionClasses.button,
                                            'w-auto',
                                        ]"
                                        color="info"
                                        :icon="mdiFileEdit"
                                        small
                                        :label="
                                            props.resourceNeo.actionExpand
                                                ? 'Edit'
                                                : ''
                                        "
                                        title="Edit"
                                    />
                                </Link>

                                <!-- Delete Button -->
                                <button
                                    v-if="
                                        checkConditions(
                                            dItem,
                                            props.resourceNeo.deleteRule
                                        ) &&
                                        props.resourceNeo.actions.includes(
                                            'd'
                                        ) &&
                                        (can(props.resourceNeo.resourceName +
                                                    '_delete'
                                            ))
                                    "
                                    :class="[
                                        actionClasses.button,
                                        {
                                            'p-2': props.resourceNeo
                                                .actionExpand,
                                        },
                                    ]"
                                    @click="
                                        delselect = dItem.id;
                                        isModalDangerActive = true;
                                    "
                                >
                                    <BaseButton
                                        :class="[
                                            actionClasses.button,
                                            'w-auto',
                                        ]"
                                        color="danger"
                                        :icon="mdiTrashCan"
                                        small
                                        :label="
                                            props.resourceNeo.actionExpand
                                                ? 'Delete'
                                                : ''
                                        "
                                        title="Delete"
                                    />
                                </button>
                            </ActionMenu>
                        </div>
                    </template>
                </Table>
            </CardBox>
        </SectionMain>

        <!-- Delete Confirmation Modal -->
        <CardBoxModal
            v-model="isModalDangerActive"
            buttonLabel="Confirm"
            title="Please confirm"
            button="danger"
            has-cancel
            @confirm="deleteRecord"
        >
            <p>Are you sure to delete?</p>
        </CardBoxModal>

        <!-- Approve Confirmation Modal -->
        <CardBoxModal
            v-model="isModalApproveActive"
            buttonLabel="Approve"
            title="Approve Seller"
            button="success"
            has-cancel
            @confirm="approveSeller"
        >
            <p>Are you sure you want to approve <strong>{{ selectedSeller?.business_name }}</strong>?</p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                This will allow the seller to start listing products and accepting orders.
            </p>
        </CardBoxModal>

        <!-- Suspend Modal with Reason -->
        <CardBoxModal
            v-model="isModalSuspendActive"
            buttonLabel="Suspend"
            title="Suspend Seller"
            button="danger"
            has-cancel
            @confirm="suspendSeller"
        >
            <p class="mb-4">Suspend <strong>{{ selectedSeller?.business_name }}</strong>?</p>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Reason for Suspension *</label>
                <textarea
                    v-model="suspendReason"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-200"
                    placeholder="Enter reason for suspending this seller..."
                    required
                ></textarea>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                The seller will be notified and will not be able to accept new orders.
            </p>
        </CardBoxModal>
    </LayoutAuthenticated>
</template>

<style scoped>
/* Add click-away listener to close dropdown when clicking outside */
:deep(body) {
    @apply cursor-pointer;
}
</style>
