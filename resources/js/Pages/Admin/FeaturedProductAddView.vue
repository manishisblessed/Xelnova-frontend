<script setup>
import { Head, Link, usePage, router, useForm } from "@inertiajs/vue3";
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import {
    mdiArrowLeft,
    mdiAlert,
    mdiStar,
} from "@mdi/js";
import SectionMain from "@/components/SectionMain.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import BaseButton from "@/components/BaseButton.vue";
import CardBox from "@/components/CardBox.vue";
import NotificationBar from "@/components/NotificationBar.vue";
import { useToast } from "vue-toast-notification";
import "vue-toast-notification/dist/theme-sugar.css";
import { computed, onMounted, ref } from "vue";

const message = computed(() => usePage().props.flash.message);
const msg_type = computed(() => usePage().props.flash.msg_type ?? "warning");

const props = defineProps({
    availableProducts: {
        type: Array,
        default: () => [],
    },
    resourceNeo: {
        type: Object,
        default: () => ({}),
    },
});

const selectedProducts = ref([]);

const form = useForm({
    product_ids: [],
});

const submitForm = () => {
    form.product_ids = selectedProducts.value.map(p => p.id);
    form.post(route('featuredProduct.store'), {
        preserveScroll: true,
    });
};

const toggleProduct = (product) => {
    const index = selectedProducts.value.findIndex(p => p.id === product.id);
    if (index > -1) {
        selectedProducts.value.splice(index, 1);
    } else {
        selectedProducts.value.push(product);
    }
};

const isSelected = (product) => {
    return selectedProducts.value.some(p => p.id === product.id);
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
        <Head :title="'Add ' + props.resourceNeo.resourceTitle" />
        <SectionMain>
            <SectionTitleLineWithButton
                :icon="props.resourceNeo.iconPath"
                :title="'Add ' + props.resourceNeo.resourceTitle"
                main
            >
                <Link :href="route(props.resourceNeo.resourceName + '.index')">
                    <BaseButton
                        :icon="mdiArrowLeft"
                        color="contrast"
                        rounded-full
                        small
                        label="Back"
                    />
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

            <CardBox class="p-6">
                <h3 class="text-lg font-semibold mb-4">Select Products to Feature</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Selected: {{ selectedProducts.length }} product(s)
                </p>

                <div v-if="availableProducts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <div
                        v-for="product in availableProducts"
                        :key="product.id"
                        @click="toggleProduct(product)"
                        :class="[
                            'p-4 border-2 rounded-lg cursor-pointer transition-all',
                            isSelected(product)
                                ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                : 'border-gray-200 dark:border-gray-700 hover:border-blue-300'
                        ]"
                    >
                        <div class="flex items-center justify-between">
                            <span class="font-medium">{{ product.label }}</span>
                            <span v-if="isSelected(product)" class="text-blue-500">
                                <BaseButton :icon="mdiStar" small color="info" class="w-auto h-auto p-0" />
                            </span>
                        </div>
                    </div>
                </div>

                <p v-else class="text-gray-500 text-center py-8">
                    No products available to feature. All approved products are already featured.
                </p>

                <div class="flex justify-end gap-3 mt-6">
                    <Link :href="route(props.resourceNeo.resourceName + '.index')">
                        <BaseButton color="contrast" label="Cancel" />
                    </Link>
                    <BaseButton
                        v-if="selectedProducts.length > 0"
                        color="success"
                        :label="'Feature ' + selectedProducts.length + ' Product(s)'"
                        @click="submitForm"
                        :disabled="form.processing"
                    />
                </div>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template>
