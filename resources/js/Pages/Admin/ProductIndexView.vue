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
const isRejectModalActive = ref(false);
const rejectProductId = ref(null);
const rejectionReason = ref('');

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

const approveProduct = (productId) => {
    router.post(route('product.approve', productId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            useToast().success('Product approved successfully!', { duration: 5000 });
        }
    });
};

const openRejectModal = (productId) => {
    rejectProductId.value = productId;
    rejectionReason.value = '';
    isRejectModalActive.value = true;
};

const rejectProduct = () => {
    if (!rejectionReason.value.trim()) {
        useToast().warning('Please enter a rejection reason', { duration: 5000 });
        return;
    }
    
    router.post(route('product.reject', rejectProductId.value), {
        rejection_reason: rejectionReason.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isRejectModalActive.value = false;
            rejectProductId.value = null;
            rejectionReason.value = '';
            useToast().success('Product rejected successfully!', { duration: 5000 });
        }
    });
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
    if (status === 'approved') return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
    if (status === 'rejected') return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
    return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
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
                    <!-- Product Image Column -->
                    <template #cell(main_image_url)="{ item }">
                        <div class="flex items-center justify-center">
                            <img v-if="item.main_image_url" :src="item.main_image_url" class="w-12 h-12 object-cover rounded shadow-sm border border-gray-200 dark:border-gray-600" />
                            <span v-else class="text-gray-400 italic text-xs">No image</span>
                        </div>
                    </template>

                    <!-- Status Badge Column -->
                    <template #cell(status_label)="{ item: dItem }">
                        <span :class="['px-2 py-1 text-xs font-semibold rounded-full', getStatusBadgeClass(dItem.status)]">
                            {{ dItem.status_label }}
                        </span>
                    </template>

                    <!-- Actions Column with Approve/Reject -->
                    <template #cell(actions)="{ item: dItem }">
                        <div class="flex items-center gap-2">
                            <!-- Approve Button -->
                            <button
                                v-if="dItem.status !== 'approved' && can('product_approve')"
                                @click="approveProduct(dItem.id)"
                                class="p-1.5 text-green-600 hover:bg-green-50 rounded transition"
                                title="Approve"
                            >
                                <BaseButton
                                    color="success"
                                    :icon="mdiCheck"
                                    small
                                    title="Approve"
                                />
                            </button>

                            <!-- Reject Button -->
                            <button
                                v-if="dItem.status !== 'rejected' && can('product_reject')"
                                @click="openRejectModal(dItem.id)"
                                class="p-1.5 text-red-600 hover:bg-red-50 rounded transition"
                                title="Reject"
                            >
                                <BaseButton
                                    color="danger"
                                    :icon="mdiClose"
                                    small
                                    title="Reject"
                                />
                            </button>

                            <!-- Regular Actions Menu -->
                            <div class="relative menu-container">
                                <ActionMenu
                                    :item="dItem"
                                    :extra-links="props.resourceNeo.extraLinks"
                                    :action-expand="props.resourceNeo.actionExpand"
                                    :is-open="openMenuIds.has(dItem.id)"
                                    :menu-classes="actionClasses"
                                    @toggle="toggleMenu(dItem.id)"
                                >
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

        <!-- Reject Reason Modal -->
        <CardBoxModal
            v-model="isRejectModalActive"
            buttonLabel="Reject Product"
            title="Reject Product"
            button="danger"
            has-cancel
            @confirm="rejectProduct"
        >
            <div class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Please provide a reason for rejecting this product:</p>
                <textarea
                    v-model="rejectionReason"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                    placeholder="Enter rejection reason..."
                    maxlength="1000"
                ></textarea>
                <p class="text-xs text-gray-500">{{ rejectionReason.length }}/1000 characters</p>
            </div>
        </CardBoxModal>
    </LayoutAuthenticated>
</template>

<style scoped>
:deep(body) {
    @apply cursor-pointer;
}
</style>
