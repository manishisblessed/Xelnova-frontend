<script setup>
import { Head, Link, usePage, router } from "@inertiajs/vue3";
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import {
    mdiPackageVariantPlus,
    mdiAlert,
    mdiTrashCan,
    mdiStar,
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

const getStatusBadgeClass = (status) => {
    const classes = {
        approved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
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
                    <Link
                        :href="route(props.resourceNeo.resourceName + '.create')"
                        v-if="can(props.resourceNeo.resourceName + '_create')"
                    >
                        <BaseButton
                            class="m-2"
                            :icon="mdiStar"
                            color="success"
                            rounded-full
                            small
                            label="Add Featured"
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
                    <!-- Product Name -->
                    <template #cell(name)="{ item: dItem }">
                        <div class="flex items-center">
                            <img
                                v-if="dItem.images?.length > 0"
                                :src="'/storage/' + dItem.images[0].path"
                                :alt="dItem.name"
                                class="w-12 h-12 object-cover rounded mr-3"
                            />
                            <div>
                                <p class="font-medium">{{ dItem.name }}</p>
                                <p class="text-xs text-gray-500">{{ dItem.sku }}</p>
                            </div>
                        </div>
                    </template>

                    <!-- Status Badge -->
                    <template #cell(status)="{ item: dItem }">
                        <span
                            class="px-2 py-1 text-xs font-semibold rounded-full"
                            :class="getStatusBadgeClass(dItem.status)"
                        >
                            {{ dItem.status.charAt(0).toUpperCase() + dItem.status.slice(1) }}
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
                                <!-- Remove from Featured Button -->
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
                                        title="Remove from Featured"
                                    />
                                </button>
                            </ActionMenu>
                        </div>
                    </template>
                </Table>
            </CardBox>
        </SectionMain>

        <!-- Remove Confirmation Modal -->
        <CardBoxModal
            v-model="isModalDangerActive"
            buttonLabel="Remove"
            title="Remove from Featured"
            button="danger"
            has-cancel
            @confirm="deleteRecord"
        >
            <p>Remove this product from featured products?</p>
        </CardBoxModal>
    </LayoutAuthenticated>
</template>

<style scoped>
:deep(body) {
    @apply cursor-pointer;
}
</style>
