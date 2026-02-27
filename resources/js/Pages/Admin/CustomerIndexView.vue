<script setup>
import { Head, Link, usePage, router } from "@inertiajs/vue3";
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import {
    mdiAlert,
    mdiEye,
    mdiCheck,
    mdiClose,
} from "@mdi/js";
import SectionMain from "@/components/SectionMain.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import BaseButton from "@/components/BaseButton.vue";

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
                    <!-- Customer Name with Link -->
                    <template #cell(name)="{ item: dItem }">
                        <Link
                            :href="route('customer.show', dItem.id)"
                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                        >
                            {{ dItem.name || 'Unknown' }}
                        </Link>
                    </template>

                    <!-- Email/Phone -->
                    <template #cell(email)="{ item: dItem }">
                        <div>
                            <p v-if="dItem.email" class="text-sm">{{ dItem.email }}</p>
                            <p v-else class="text-sm text-gray-400">No email</p>
                        </div>
                    </template>

                    <template #cell(phone)="{ item: dItem }">
                        <span v-if="dItem.phone">{{ dItem.phone }}</span>
                        <span v-else class="text-gray-400">-</span>
                    </template>

                    <!-- Orders Count -->
                    <template #cell(orders_count)="{ item: dItem }">
                        <span class="px-2 py-1 text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded">
                            {{ dItem.orders_count || 0 }}
                        </span>
                    </template>

                    <!-- Total Spent -->
                    <template #cell(total_spent)="{ item: dItem }">
                        <span class="font-medium text-green-600 dark:text-green-400">
                            {{ dItem.total_spent }}
                        </span>
                    </template>

                    <!-- Verified Status -->
                    <template #cell(is_verified)="{ item: dItem }">
                        <span
                            v-if="dItem.is_verified"
                            class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300"
                        >
                            <BaseButton :icon="mdiCheck" small color="success" class="w-auto h-auto p-0" />
                        </span>
                        <span
                            v-else
                            class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300"
                        >
                            <BaseButton :icon="mdiClose" small color="info" class="w-auto h-auto p-0" />
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
                                    v-if="can('customer_view')"
                                    :href="route('customer.show', dItem.id)"
                                    :class="[actionClasses.menuItem]"
                                >
                                    <BaseButton
                                        :class="[actionClasses.button, 'w-auto']"
                                        color="info"
                                        :icon="mdiEye"
                                        small
                                        title="View Customer"
                                    />
                                </Link>
                            </ActionMenu>
                        </div>
                    </template>
                </Table>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template>

<style scoped>
:deep(body) {
    @apply cursor-pointer;
}
</style>
