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

const getTypeBadgeClass = (type) => {
    return type === 'percentage' 
        ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'
        : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
};

const formatType = (type) => {
    return type === 'percentage' ? 'Percentage' : 'Fixed';
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
                    <Link
                        :href="route(props.resourceNeo.resourceName + '.create')"
                        v-if="can(props.resourceNeo.resourceName + '_create')"
                    >
                        <BaseButton
                            class="m-2"
                            :icon="mdiPackageVariantPlus"
                            color="success"
                            rounded-full
                            small
                            label="Add Coupon"
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
                    <!-- Coupon Code -->
                    <template #cell(code)="{ item: dItem }">
                        <span class="font-mono font-bold text-lg">{{ dItem.code }}</span>
                    </template>

                    <!-- Type Badge -->
                    <template #cell(type)="{ item: dItem }">
                        <span
                            class="px-2 py-1 text-xs font-semibold rounded-full"
                            :class="getTypeBadgeClass(dItem.type)"
                        >
                            {{ formatType(dItem.type) }}
                        </span>
                    </template>

                    <!-- Value -->
                    <template #cell(formatted_value)="{ item: dItem }">
                        <span class="font-bold text-green-600 dark:text-green-400">
                            {{ dItem.formatted_value }}
                        </span>
                    </template>

                    <!-- Usage -->
                    <template #cell(usage_info)="{ item: dItem }">
                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 dark:bg-gray-700 rounded">
                            {{ dItem.usage_info }}
                        </span>
                    </template>

                    <!-- Active Status -->
                    <template #cell(is_active)="{ item: dItem }">
                        <span
                            v-if="dItem.is_active && dItem.is_valid"
                            class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300"
                        >
                            Active
                        </span>
                        <span
                            v-else-if="dItem.is_active && !dItem.is_valid"
                            class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300"
                        >
                            Expired
                        </span>
                        <span
                            v-else
                            class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300"
                        >
                            Inactive
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
                                <!-- Edit Button -->
                                <Link
                                    v-if="can(props.resourceNeo.resourceName + '_edit')"
                                    :href="route(props.resourceNeo.resourceName + '.edit', dItem.id)"
                                    :class="[actionClasses.menuItem]"
                                >
                                    <BaseButton
                                        :class="[actionClasses.button, 'w-auto']"
                                        color="info"
                                        :icon="mdiFileEdit"
                                        small
                                        title="Edit"
                                    />
                                </Link>

                                <!-- Delete Button -->
                                <button
                                    v-if="can(props.resourceNeo.resourceName + '_delete')"
                                    :class="[actionClasses.button]"
                                    @click="
                                        delselect = dItem.id;
                                        isModalDangerActive = true;
                                    "
                                >
                                    <BaseButton
                                        :class="[actionClasses.button, 'w-auto']"
                                        color="danger"
                                        :icon="mdiTrashCan"
                                        small
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
            title="Delete Coupon"
            button="danger"
            has-cancel
            @confirm="deleteRecord"
        >
            <p>Are you sure you want to delete this coupon?</p>
        </CardBoxModal>
    </LayoutAuthenticated>
</template>

<style scoped>
:deep(body) {
    @apply cursor-pointer;
}
</style>
