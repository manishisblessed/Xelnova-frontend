<script setup>
import { Head, Link, usePage, useForm } from "@inertiajs/vue3";
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import { mdiArrowLeft, mdiAlert } from "@mdi/js";
import SectionMain from "@/components/SectionMain.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import BaseButton from "@/components/BaseButton.vue";
import CardBox from "@/components/CardBox.vue";
import NotificationBar from "@/components/NotificationBar.vue";
import { computed } from "vue";

const message = computed(() => usePage().props.flash.message);
const msg_type = computed(() => usePage().props.flash.msg_type ?? "warning");

const props = defineProps({
    payoutRequest: { type: Object, required: true },
    items: { type: Array, default: () => [] },
    verifiedBankAccounts: { type: Array, default: () => [] },
    summary: { type: Object, default: () => ({}) },
    resourceNeo: { type: Object, default: () => ({}) },
});

const approveForm = useForm({ notes: "" });
const rejectForm = useForm({ reason: "" });
const processForm = useForm({ payment_reference: "", payment_method: "", notes: "" });

const approveRequest = () => approveForm.post(route("payout.approve", props.payoutRequest.id));
const rejectRequest = () => rejectForm.post(route("payout.reject", props.payoutRequest.id));
const processPayout = () => processForm.post(route("payout.process", props.payoutRequest.id));

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
        <Head :title="'Payout Request - ' + payoutRequest.request_number" />
        <SectionMain>
            <SectionTitleLineWithButton
                :icon="props.resourceNeo.iconPath"
                :title="'Payout Request: ' + payoutRequest.request_number"
                main
            >
                <Link :href="route('payout.index')">
                    <BaseButton :icon="mdiArrowLeft" color="contrast" rounded-full small label="Back" />
                </Link>
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

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <CardBox class="p-4">
                    <p class="text-sm text-gray-500">Gross</p>
                    <p class="text-xl font-bold">{{ formatMoney(summary.gross_total) }}</p>
                </CardBox>
                <CardBox class="p-4">
                    <p class="text-sm text-gray-500">Commission</p>
                    <p class="text-xl font-bold text-red-600">{{ formatMoney(summary.commission_total) }}</p>
                </CardBox>
                <CardBox class="p-4">
                    <p class="text-sm text-gray-500">Net</p>
                    <p class="text-xl font-bold text-green-600">{{ formatMoney(summary.net_total) }}</p>
                </CardBox>
                <CardBox class="p-4">
                    <p class="text-sm text-gray-500">Status</p>
                    <p class="text-xl font-bold uppercase">{{ payoutRequest.status }}</p>
                </CardBox>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <CardBox class="p-6 mb-6">
                        <h3 class="text-lg font-semibold mb-4">Payout Items</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left py-2">Sub-Order</th>
                                        <th class="text-right py-2">Gross</th>
                                        <th class="text-right py-2">Commission</th>
                                        <th class="text-right py-2">Net</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in items" :key="item.id" class="border-b">
                                        <td class="py-2">
                                            {{ item.sub_order?.sub_order_number }}
                                            <p class="text-xs text-gray-500">{{ item.sub_order?.order?.order_number }}</p>
                                        </td>
                                        <td class="text-right">{{ formatMoney(item.gross_amount) }}</td>
                                        <td class="text-right">{{ formatMoney(item.commission_amount) }}</td>
                                        <td class="text-right">{{ formatMoney(item.net_amount) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardBox>
                </div>

                <div>
                    <CardBox class="p-6 mb-6">
                        <h3 class="text-lg font-semibold mb-3">Bank Accounts</h3>
                        <div v-if="verifiedBankAccounts.length">
                            <div v-for="bank in verifiedBankAccounts" :key="bank.id" class="mb-3 p-3 border rounded">
                                <p class="font-medium">{{ bank.bank_name }}</p>
                                <p class="text-sm">{{ bank.account_holder_name }}</p>
                                <p class="text-xs text-gray-500">{{ bank.account_number }} | {{ bank.ifsc_code }}</p>
                            </div>
                        </div>
                        <p v-else class="text-gray-500">No verified bank account</p>
                    </CardBox>

                    <CardBox class="p-6">
                        <h3 class="text-lg font-semibold mb-3">Actions</h3>

                        <div v-if="payoutRequest.status === 'pending'" class="space-y-3 mb-4">
                            <textarea v-model="approveForm.notes" class="w-full border rounded p-2" rows="2" placeholder="Approval notes (optional)"></textarea>
                            <BaseButton color="info" label="Approve Request" @click="approveRequest"/>

                            <textarea v-model="rejectForm.reason" class="w-full border rounded p-2" rows="2" placeholder="Rejection reason"></textarea>
                            <BaseButton color="danger" label="Reject Request" @click="rejectRequest"/>
                        </div>

                        <div v-if="payoutRequest.status === 'approved' || payoutRequest.status === 'pending'" class="space-y-3">
                            <input v-model="processForm.payment_reference" class="w-full border rounded p-2" placeholder="Payment reference" />
                            <input v-model="processForm.payment_method" class="w-full border rounded p-2" placeholder="Payment method (optional)" />
                            <textarea v-model="processForm.notes" class="w-full border rounded p-2" rows="2" placeholder="Processing notes"></textarea>
                            <BaseButton color="success" label="Mark as Paid" @click="processPayout"/>
                        </div>

                        <div class="mt-4 text-sm text-gray-500">
                            <p>Requested: {{ formatDate(payoutRequest.requested_at) }}</p>
                            <p>Reviewed: {{ formatDate(payoutRequest.reviewed_at) }}</p>
                            <p>Paid: {{ formatDate(payoutRequest.paid_at) }}</p>
                            <p>Reference: {{ payoutRequest.payment_reference || '-' }}</p>
                        </div>
                    </CardBox>
                </div>
            </div>
        </SectionMain>
    </LayoutAuthenticated>
</template>
