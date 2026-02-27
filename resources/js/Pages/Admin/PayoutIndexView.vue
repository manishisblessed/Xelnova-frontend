<script setup>
import { Head, Link, usePage } from "@inertiajs/vue3";
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import { mdiAlert, mdiEye } from "@mdi/js";
import SectionMain from "@/components/SectionMain.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import CardBox from "@/components/CardBox.vue";
import NotificationBar from "@/components/NotificationBar.vue";
import Table from "@/components/DataTable/Table.vue";
import BaseButton from "@/components/BaseButton.vue";
import { computed } from "vue";

const message = computed(() => usePage().props.flash.message);
const msg_type = computed(() => usePage().props.flash.msg_type ?? "warning");

const props = defineProps({
    resourceData: { type: Object, default: () => ({}) },
    resourceNeo: { type: Object, default: () => ({}) },
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

            <CardBox has-table>
                <Table :resource="resourceData" :resourceNeo="resourceNeo" :stickyHeader="true">
                    <template #cell(requested_amount)="{ item: dItem }">
                        {{ formatMoney(dItem.requested_amount) }}
                    </template>
                    <template #cell(approved_amount)="{ item: dItem }">
                        {{ formatMoney(dItem.approved_amount || 0) }}
                    </template>
                    <template #cell(status)="{ item: dItem }">
                        <span
                            class="px-2 py-1 text-xs rounded font-medium"
                            :class="{
                                'bg-yellow-100 text-yellow-700': dItem.status === 'pending',
                                'bg-blue-100 text-blue-700': dItem.status === 'approved',
                                'bg-red-100 text-red-700': dItem.status === 'rejected',
                                'bg-green-100 text-green-700': dItem.status === 'paid',
                            }"
                        >
                            {{ dItem.status }}
                        </span>
                    </template>
                    <template #cell(requested_at)="{ item: dItem }">
                        {{ formatDate(dItem.requested_at) }}
                    </template>
                    <template #cell(actions)="{ item: dItem }">
                        <Link :href="route('payout.show', dItem.id)">
                            <BaseButton color="info" :icon="mdiEye" small title="View" />
                        </Link>
                    </template>
                </Table>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template>
