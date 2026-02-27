<script setup>
import { Head, usePage } from "@inertiajs/vue3";
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import { mdiAlert } from "@mdi/js";
import SectionMain from "@/components/SectionMain.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import CardBox from "@/components/CardBox.vue";
import NotificationBar from "@/components/NotificationBar.vue";
import Table from "@/components/DataTable/Table.vue";
import { computed } from "vue";

const message = computed(() => usePage().props.flash.message);
const msg_type = computed(() => usePage().props.flash.msg_type ?? "warning");

const props = defineProps({
    resourceData: { type: Object, default: () => ({}) },
    resourceNeo: { type: Object, default: () => ({}) },
    summary: { type: Object, default: () => ({}) },
});

const formatMoney = (amount) =>
    "₹" + Number(amount || 0).toLocaleString("en-IN", { minimumFractionDigits: 2 });

const formatDate = (date) => {
    if (!date) return "-";
    return new Date(date).toLocaleDateString("en-IN", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <CardBox class="p-4">
                    <p class="text-sm text-gray-500">Total Earned</p>
                    <p class="text-xl font-bold text-green-600">{{ formatMoney(summary.total_earned) }}</p>
                </CardBox>
                <CardBox class="p-4">
                    <p class="text-sm text-gray-500">Total Reversed</p>
                    <p class="text-xl font-bold text-red-600">{{ formatMoney(summary.total_reversed) }}</p>
                </CardBox>
                <CardBox class="p-4">
                    <p class="text-sm text-gray-500">Net Commission</p>
                    <p class="text-xl font-bold">{{ formatMoney(summary.net_commission) }}</p>
                </CardBox>
            </div>

            <CardBox has-table>
                <Table :resource="resourceData" :resourceNeo="resourceNeo" :stickyHeader="true">
                    <template #cell(entry_type)="{ item: dItem }">
                        <span
                            class="px-2 py-1 rounded text-xs font-medium"
                            :class="dItem.entry_type === 'earned' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                        >
                            {{ dItem.entry_type }}
                        </span>
                    </template>
                    <template #cell(base_amount)="{ item: dItem }">
                        {{ formatMoney(dItem.base_amount) }}
                    </template>
                    <template #cell(commission_amount)="{ item: dItem }">
                        {{ formatMoney(dItem.commission_amount) }}
                    </template>
                    <template #cell(created_at)="{ item: dItem }">
                        {{ formatDate(dItem.created_at) }}
                    </template>
                </Table>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template>
