<script setup>
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import {
    mdiArrowLeft,
    mdiCheckCircle,
    mdiCloseCircle,
    mdiStar,
    mdiAlert,
} from "@mdi/js";
import SectionMain from "@/components/SectionMain.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import BaseButton from "@/components/BaseButton.vue";
import CardBox from "@/components/CardBox.vue";
import NotificationBar from "@/components/NotificationBar.vue";
import BaseIcon from "@/components/BaseIcon.vue";
import { computed, onMounted } from "vue";
import { useToast } from "vue-toast-notification";
import "vue-toast-notification/dist/theme-sugar.css";

const message = computed(() => usePage().props.flash.message);
const msg_type = computed(() => usePage().props.flash.msg_type ?? "warning");

const props = defineProps({
    seller: {
        type: Object,
        required: true,
    },
    bankAccounts: {
        type: Array,
        default: () => [],
    },
    resourceNeo: {
        type: Object,
        default: () => ({}),
    },
});

const verifyBank = (bank) => {
    router.post(
        route('sellerBank.verify', [props.seller.id, bank.id]),
        { action: 'verify' },
        {
            preserveScroll: true,
        }
    );
};

const rejectBank = (bank) => {
    router.post(
        route('sellerBank.verify', [props.seller.id, bank.id]),
        { action: 'reject' },
        {
            preserveScroll: true,
        }
    );
};

const getStatusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        verified: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const maskAccountNumber = (accountNumber) => {
    if (!accountNumber) return 'N/A';
    const length = accountNumber.length;
    if (length <= 4) return accountNumber;
    return 'X'.repeat(length - 4) + accountNumber.slice(-4);
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
});
</script>

<template>
    <LayoutAuthenticated>
        <Head :title="`Bank Accounts - ${seller.business_name}`" />
        <SectionMain>
            <SectionTitleLineWithButton
                :icon="resourceNeo.iconPath"
                :title="`Seller Bank Accounts - ${seller.business_name}`"
                main
            >
                <div class="flex gap-2">
                    <Link :href="route('seller.overview', seller.id)">
                        <BaseButton
                            :icon="mdiArrowLeft"
                            color="info"
                            rounded-full
                            small
                            label="Back to Overview"
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
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Account Holder
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Bank Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Account Number
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    IFSC Code
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Branch
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-if="bankAccounts.length === 0">
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No bank accounts added yet
                                </td>
                            </tr>
                            <tr v-for="bank in bankAccounts" :key="bank.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ bank.account_holder_name }}
                                        </span>
                                        <BaseIcon 
                                            v-if="bank.is_primary" 
                                            :path="mdiStar" 
                                            class="ml-2 text-yellow-500" 
                                            :size="16"
                                            title="Primary Account"
                                        />
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900 dark:text-white">
                                        {{ bank.bank_name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-600 dark:text-gray-400 font-mono">
                                        {{ maskAccountNumber(bank.account_number) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-600 dark:text-gray-400 font-mono">
                                        {{ bank.ifsc_code }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ bank.branch_name || 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full"
                                        :class="getStatusBadgeClass(bank.verification_status)"
                                    >
                                        {{ bank.verification_status.charAt(0).toUpperCase() + bank.verification_status.slice(1) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex gap-2">
                                        <BaseButton
                                            v-if="bank.verification_status === 'pending'"
                                            :icon="mdiCheckCircle"
                                            color="success"
                                            small
                                            title="Verify"
                                            @click="verifyBank(bank)"
                                        />
                                        <BaseButton
                                            v-if="bank.verification_status === 'pending'"
                                            :icon="mdiCloseCircle"
                                            color="danger"
                                            small
                                            title="Reject"
                                            @click="rejectBank(bank)"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template>
