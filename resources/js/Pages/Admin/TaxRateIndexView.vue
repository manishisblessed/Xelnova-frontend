<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import {
    mdiReact,
    mdiAlert,
    mdiPlus,
    mdiFileEdit,
    mdiTrashCan,
} from "@mdi/js";
import SectionMain from "@/components/SectionMain.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import BaseButton from "@/components/BaseButton.vue";
import CardBox from "@/components/CardBox.vue";
import NotificationBar from "@/components/NotificationBar.vue";
import Table from "@/components/DataTable/Table.vue";
import { computed } from 'vue';

const message = computed(() => usePage().props.flash.message)
const msg_type = computed(() => usePage().props.flash.msg_type ?? 'success')

defineProps({
    taxRates: {
        type: Object,
        default: () => ({}),
    },
});

const destroy = (id) => {
    if (confirm('Are you sure you want to delete this tax rate?')) {
        router.delete(route('tax-rate.destroy', id));
    }
};
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Tax Rates" />
        <SectionMain>
            <SectionTitleLineWithButton :icon="mdiReact" title="Tax Rates" main>
                <Link :href="route('tax-rate.create')">
                    <BaseButton class="m-2" :icon="mdiPlus" color="success" rounded-full small label="Add New" />
                </Link>
            </SectionTitleLineWithButton>

            <NotificationBar v-if="message" @closed="usePage().props.flash.message = ''" :color="msg_type" :icon="mdiAlert"
                :outline="true">
                {{ message }}
            </NotificationBar>

            <CardBox has-table>
                <Table :resource="taxRates">
                    <template #cell(is_active)="{ item: taxRate }">
                        <span class="text-xs font-bold px-2 py-1 rounded text-white" 
                              :class="taxRate.is_active ? 'bg-green-500' : 'bg-red-500'">
                            {{ taxRate.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </template>
                    <template #cell(actions)="{ item: taxRate }">
                        <div class="flex gap-2">
                            <Link :href="route('tax-rate.edit', taxRate.id)">
                                <BaseButton color="info" :icon="mdiFileEdit" small />
                            </Link>
                            <BaseButton color="danger" :icon="mdiTrashCan" small
                                @click="destroy(taxRate.id)" />
                        </div>
                    </template>
                </Table>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template>
