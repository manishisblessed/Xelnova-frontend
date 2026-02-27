<script setup>
import { Head, Link, useForm, usePage, router } from "@inertiajs/vue3";

import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import { mdiFormatListBulleted, mdiAlert, mdiPlus, mdiDelete } from "@mdi/js";
import SectionMain from "@/components/SectionMain.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import BaseButton from "@/components/BaseButton.vue";
import CardBox from "@/components/CardBox.vue";
import NotificationBar from "@/components/NotificationBar.vue";
import FormField from "@/components/FormField.vue";
import FormFields from "@/components/FormFields.vue";
import BaseIcon from "@/components/BaseIcon.vue";

import Multiselect from "vue-multiselect";
import "../../../css/vue-multiselect.css";
import clone from "lodash-es/clone";
import { computed, onBeforeMount, ref } from "vue";

const message = computed(() => usePage().props.flash.message);
const msg_type = computed(() => usePage().props.flash.msg_type ?? "warning");

const props = defineProps({
    formdata: {
        type: Object,
        default: () => ({}),
    },
    resourceNeo: {
        type: Object,
        default: () => ({}),
    },
    variantTypes: {
        type: Array,
        default: () => [],
    },
    categoryVariantTypes: {
        type: Array,
        default: () => [],
    },
});

// Form initialization
const form = useForm(() => {
    return clone(props.resourceNeo.formInfo);
});

// Variant Types Management
const selectedVariantTypes = ref([]);

// Available variant types for dropdown (not already selected)
const availableVariantTypes = computed(() => {
    if (!props.variantTypes) return [];
    const selectedIds = selectedVariantTypes.value.map(vt => vt.variant_type_id);
    return props.variantTypes.filter(vt => !selectedIds.includes(vt.id));
});

// File preview state
const imagePreview = ref(null);

onBeforeMount(() => {
    // Initialize form fields
    for (const key in props.resourceNeo.formInfo) {
        form[key] =
            props.formdata[key] ??
            props.resourceNeo.formInfo[key]["default"] ??
            "";
    }
    
    // Initialize variant types from props
    if (props.categoryVariantTypes && props.categoryVariantTypes.length > 0) {
        selectedVariantTypes.value = props.categoryVariantTypes.map(cvt => ({
            variant_type_id: cvt.variant_type_id,
        }));
    }
});

// Add variant type to selected list
const addVariantType = () => {
    const selectedIds = selectedVariantTypes.value.map(vt => vt.variant_type_id);
    const available = props.variantTypes.find(vt => !selectedIds.includes(vt.id));
    if (available) {
        selectedVariantTypes.value.push({
            variant_type_id: available.id,
        });
    }
};

// Remove variant type from selected list
const removeVariantType = (index) => {
    selectedVariantTypes.value.splice(index, 1);
};

// Reorder variant types
const moveVariantType = (index, direction) => {
    const newIndex = index + direction;
    if (newIndex >= 0 && newIndex < selectedVariantTypes.value.length) {
        // Swap elements
        const temp = selectedVariantTypes.value[index];
        selectedVariantTypes.value[index] = selectedVariantTypes.value[newIndex];
        selectedVariantTypes.value[newIndex] = temp;
    }
};

// Handle image change
const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

// Clear image
const clearImage = () => {
    form.image = null;
    imagePreview.value = null;
    const fileInput = document.querySelector('input[name="image"]');
    if (fileInput) fileInput.value = '';
};

// Submit form
const submitform = () => {
    // Build FormData manually to include variant types and file
    const formData = new FormData();
    
    // Add all form fields
    for (const key in props.resourceNeo.formInfo) {
        const value = form[key];
        if (value !== null && value !== undefined && value !== '') {
            // Handle file uploads
            if (value instanceof File) {
                formData.append(key, value);
            }
            // Handle booleans - convert to 0/1
            else if (typeof value === 'boolean') {
                formData.append(key, value ? '1' : '0');
            }
            // Handle objects (like multiselect values)
            else if (typeof value === 'object' && value.id !== undefined) {
                formData.append(key, value.id);
            }
            // Handle regular values
            else {
                formData.append(key, value);
            }
        }
    }
    
    // Add variant types
    if (selectedVariantTypes.value.length > 0) {
        selectedVariantTypes.value.forEach((vt, index) => {
            formData.append(`variant_types[${index}][variant_type_id]`, vt.variant_type_id);
        });
    }
    
    // Add _method for PUT requests
    if (props.formdata.id) {
        formData.append('_method', 'PUT');
    }
    
    // Use Inertia router with FormData
    const url = props.formdata.id 
        ? route(props.resourceNeo.resourceName + ".update", props.formdata.id)
        : route(props.resourceNeo.resourceName + ".store");
    
    form.processing = true;
    
    router.post(url, formData, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.processing = false;
        },
        onError: (errors) => {
            form.processing = false;
            form.errors = errors;
        },
        onFinish: () => {
            form.processing = false;
        }
    });
};

const filteredOption = (source, sKey, sFetch, compVal, key) => {
    let redata = [];
    source.forEach((element) => {
        if (element[sKey] == form[compVal]) {
            redata.push(element[sFetch]);
        }
    });
    form[key] = redata.includes(form[key]) ? form[key] : null;
    return redata;
};
</script>

<template>
    <LayoutAuthenticated>
        <Head :title="props.resourceNeo.resourceTitle" />
        <SectionMain>
            <SectionTitleLineWithButton
                :icon="props.resourceNeo.iconPath"
                :title="props.formdata.id ? 'Edit Category' : 'Add New Category'"
                main
            >
                <div class="flex">
                    <Link
                        :href="route(props.resourceNeo.resourceName + '.index')"
                    >
                        <BaseButton
                            class="m-2"
                            :icon="mdiFormatListBulleted"
                            color="success"
                            rounded-full
                            small
                            label="Category List"
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
            
            <form @submit.prevent="submitform">
                <!-- Category Fields -->
                <CardBox>
                    <div
                        class="grid grid-cols-1 gap-6"
                        :class="[
                            'lg:grid-cols-' + (props.resourceNeo.fColumn ?? 2),
                        ]"
                    >
                        <FormField
                            v-for="(formField, key) in props.resourceNeo.formInfo"
                            :key="key"
                            :label="formField.label"
                            :help="formField.tooltip"
                            :error="form.errors[key]"
                            class="!mb-0"
                        >
                            <FormFields
                                :form-field="formField"
                                :form="form"
                                :fkey="key"
                            />
                        </FormField>
                    </div>
                </CardBox>

                <!-- Variant Types Section -->
                <CardBox v-if="props.variantTypes && props.variantTypes.length > 0" class="mt-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Variant Types</h3>
                        <button
                            type="button"
                            @click="addVariantType"
                            :disabled="availableVariantTypes.length === 0"
                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <BaseIcon :path="mdiPlus" w="w-4" h="h-4" class="mr-1" />
                            Add Variant Type
                        </button>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        Select which variant types are available for products in this category. Sellers will define specific values when creating products.
                    </p>
                    
                    <div v-if="selectedVariantTypes.length === 0" class="text-center py-6 text-gray-500">
                        No variant types assigned. Click "Add Variant Type" to assign one.
                    </div>
                    
                    <div v-else class="space-y-2">
                        <div
                            v-for="(vt, index) in selectedVariantTypes"
                            :key="index"
                            class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
                        >
                            <select
                                v-model="vt.variant_type_id"
                                class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option
                                    v-for="availVt in [...availableVariantTypes, props.variantTypes.find(v => v.id === vt.variant_type_id)].filter(Boolean)"
                                    :key="availVt.id"
                                    :value="availVt.id"
                                >
                                    {{ availVt.name }} ({{ availVt.input_type_label }})
                                </option>
                            </select>
                            
                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    @click="moveVariantType(index, -1)"
                                    :disabled="index === 0"
                                    class="p-1 text-gray-500 hover:text-gray-700 disabled:opacity-30 disabled:cursor-not-allowed"
                                    title="Move Up"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                        <path fill-rule="evenodd" d="M12 2.25c.621 0 1.125.504 1.125 1.125v16.19l2.47-2.47a.75.75 0 111.06 1.06l-3.75 3.75a.75.75 0 01-1.06 0l-3.75-3.75a.75.75 0 111.06-1.06l2.47 2.47V3.375c0-.621.504-1.125 1.125-1.125z" clip-rule="evenodd" transform="rotate(180 12 12)" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    @click="moveVariantType(index, 1)"
                                    :disabled="index === selectedVariantTypes.length - 1"
                                    class="p-1 text-gray-500 hover:text-gray-700 disabled:opacity-30 disabled:cursor-not-allowed"
                                    title="Move Down"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                        <path fill-rule="evenodd" d="M12 2.25c.621 0 1.125.504 1.125 1.125v16.19l2.47-2.47a.75.75 0 111.06 1.06l-3.75 3.75a.75.75 0 01-1.06 0l-3.75-3.75a.75.75 0 111.06-1.06l2.47 2.47V3.375c0-.621.504-1.125 1.125-1.125z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    @click="removeVariantType(index)"
                                    class="p-2 text-red-600 hover:text-red-800 hover:bg-red-100 dark:hover:bg-red-900/20 rounded transition-colors"
                                    title="Remove variant type"
                                >
                                    <BaseIcon :path="mdiDelete" w="w-5" h="h-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </CardBox>

                <div class="mt-4 flex">
                    <BaseButton
                        class="mr-2"
                        type="submit"
                        small
                        :disabled="form.processing"
                        color="info"
                        :label="props.formdata.id ? 'Update' : 'Save'"
                    />
                    <Link
                        :href="route(props.resourceNeo.resourceName + '.index')"
                    >
                        <BaseButton
                            type="reset"
                            small
                            color="info"
                            outline
                            label="Cancel"
                        />
                    </Link>
                </div>
            </form>
        </SectionMain>
    </LayoutAuthenticated>
</template>
