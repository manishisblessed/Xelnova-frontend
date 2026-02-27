<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import {
    mdiReact,
    mdiArrowLeft
} from "@mdi/js";
import SectionMain from "@/components/SectionMain.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import BaseButton from "@/components/BaseButton.vue";
import CardBox from "@/components/CardBox.vue";
import FormField from "@/components/FormField.vue";
import FormControl from "@/components/FormControl.vue";

const props = defineProps({
    model: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    name: props.model ? props.model.name : '',
    rate: props.model ? props.model.rate : '',
    is_active: props.model ? props.model.is_active : true,
});

const submit = () => {
    if (props.model) {
        form.put(route('tax-rate.update', props.model.id));
    } else {
        form.post(route('tax-rate.store'));
    }
};
</script>

<template>
    <LayoutAuthenticated>
        <Head :title="model ? 'Edit Tax Rate' : 'Create Tax Rate'" />
        <SectionMain>
            <SectionTitleLineWithButton :icon="mdiReact" :title="model ? 'Edit Tax Rate' : 'Create Tax Rate'" main>
                <Link :href="route('tax-rate.index')">
                    <BaseButton class="m-2" :icon="mdiArrowLeft" color="whiteDark" rounded-full small label="Back" />
                </Link>
            </SectionTitleLineWithButton>

            <CardBox is-form @submit.prevent="submit">
                <FormField label="Name" :error="form.errors.name">
                    <FormControl v-model="form.name" placeholder="e.g. GST 18%" required />
                </FormField>

                <FormField label="Rate (%)" :error="form.errors.rate">
                    <FormControl v-model="form.rate" type="number" step="0.01" placeholder="18.00" required />
                </FormField>

                <FormField label="Status" :error="form.errors.is_active">
                    <div class="flex items-center gap-4 py-2">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" v-model="form.is_active" :value="true" class="form-radio text-blue-600">
                            <span class="ml-2">Active</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" v-model="form.is_active" :value="false" class="form-radio text-red-600">
                            <span class="ml-2">Inactive</span>
                        </label>
                    </div>
                </FormField>

                <template #footer>
                    <BaseButton type="submit" color="info" label="Save" :disabled="form.processing"
                        :loading="form.processing" />
                    <BaseButton type="reset" color="info" outline label="Reset" />
                </template>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template>
