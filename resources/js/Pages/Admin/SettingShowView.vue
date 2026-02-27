<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import {
    mdiAlert,
    mdiCog,
} from "@mdi/js";
import SectionMain from "@/components/SectionMain.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import BaseButton from "@/components/BaseButton.vue";
import CardBox from "@/components/CardBox.vue";

import NotificationBar from "@/components/NotificationBar.vue";

import FormField from "@/components/FormField.vue";
import FormControl from "@/components/FormControl.vue";

import { computed } from 'vue';

const message = computed(() => usePage().props.flash.message)
const msg_type = computed(() => usePage().props.flash.msg_type ?? 'warning')

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    settings: props.settings,
});

const groupedSettings = computed(() => {
    const groups = {};
    props.settings.forEach(setting => {
        if (!groups[setting.group]) {
            groups[setting.group] = [];
        }
        groups[setting.group].push(setting);
    });
    return groups;
});

const submitform = () => {
    form.put(route('setting.bulkUpdate'));
};
</script>
<template>
    <LayoutAuthenticated>

        <Head title="Settings" />
        <SectionMain>
            <SectionTitleLineWithButton :icon="mdiCog" title="Settings" main>
                <div class="flex">
                </div>
            </SectionTitleLineWithButton>
            <NotificationBar v-if="message" @closed="usePage().props.flash.message = ''" :color="msg_type" :icon="mdiAlert"
                :outline="true">
                {{ message }}
            </NotificationBar>
            <form @submit.prevent="submitform">
                <CardBox>
                    <div v-for="(groupSettings, groupName) in groupedSettings" :key="groupName" class="mb-6">
                        <h1 class="bg-zinc-50 mb-4 p-2 text-black font-bold rounded uppercase">
                            {{ groupName }}
                        </h1>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div v-for="(item, index) in groupSettings" :key="item.id">
                                <FormField :label="item.label" help="">
                                    <FormControl :type="item.vtype" 
                                        v-model="form.settings[settings.indexOf(item)].value" 
                                        required />
                                </FormField>
                            </div>
                        </div>
                    </div>
                </CardBox>

                <div class="mt-4 flex">
                    <BaseButton class="mr-2" type="submit" small color="info" label="Update" />
                    <Link :href="route('dashboard')">
                    <BaseButton type="reset" small color="info" outline label="Cancel" />
                    </Link>
                </div>

            </form>
        </SectionMain>
    </LayoutAuthenticated>
</template>
