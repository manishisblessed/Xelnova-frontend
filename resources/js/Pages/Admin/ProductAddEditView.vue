<script setup>
import { Head, Link, useForm, usePage, router } from "@inertiajs/vue3";

import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import { mdiFormatListBulleted, mdiAlert, mdiPlus, mdiDelete, mdiCheck, mdiClose } from "@mdi/js";
import SectionMain from "@/components/SectionMain.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import BaseButton from "@/components/BaseButton.vue";
import CardBox from "@/components/CardBox.vue";
import CardBoxModal from "@/components/CardBoxModal.vue";
import NotificationBar from "@/components/NotificationBar.vue";
import FormField from "@/components/FormField.vue";
import FormFields from "@/components/FormFields.vue";
import BaseIcon from "@/components/BaseIcon.vue";

import Multiselect from "vue-multiselect";
import "../../../css/vue-multiselect.css";
import clone from "lodash-es/clone";
import { computed, onBeforeMount, ref } from "vue";
import { can } from '@/utils/permissions';
import { useToast } from "vue-toast-notification";

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
        default: () => ([]),
    },
    categoryVariantTypes: {
        type: Array,
        default: () => ([]),
    },
    existingVariantOptions: {
        type: Object,
        default: () => ({}),
    },
    existingVariants: {
        type: Array,
        default: () => ([]),
    }
});

// Active tab state
const activeTab = ref('basic');

// Form initialization
const form = useForm(() => {
    return clone(props.resourceNeo.formInfo);
});

// Gallery images state
const galleryImages = ref([]);
const deletedGalleryImages = ref([]);
const newGalleryFiles = ref([]);

// Highlights state
const highlights = ref([]);

// Variants state
const hasVariants = ref(false);
const variantOptions = ref({});
const variants = ref([]);
let tempIdCounter = 1;

// File preview states
const mainImagePreview = ref(null);

// Approval state
const isRejectModalActive = ref(false);
const rejectionReason = ref('');

onBeforeMount(() => {
    // Initialize form fields
    for (const key in props.resourceNeo.formInfo) {
        form[key] =
            props.formdata[key] ??
            props.resourceNeo.formInfo[key]["default"] ??
            "";
    }
    
    // Load existing gallery images for edit mode
    if (props.formdata.gallery_images_data) {
        galleryImages.value = [...props.formdata.gallery_images_data];
    }

    // Initialize highlights
    if (props.formdata.highlights && Array.isArray(props.formdata.highlights)) {
        highlights.value = [...props.formdata.highlights];
    } else {
        highlights.value = [];
    }

    // Initialize variants
    hasVariants.value = !!props.formdata.has_variants;
    
    if (props.existingVariantOptions && Object.keys(props.existingVariantOptions).length > 0) {
        variantOptions.value = clone(props.existingVariantOptions);
    }
    
    if (props.existingVariants && props.existingVariants.length > 0) {
        variants.value = clone(props.existingVariants);
    }
});

// --- Highlights Methods ---
const addHighlight = () => {
    highlights.value.push('');
};

const removeHighlight = (index) => {
    highlights.value.splice(index, 1);
};

// --- Variants Methods ---
const addVariantOption = (variantTypeId) => {
    if (!variantOptions.value[variantTypeId]) {
        variantOptions.value[variantTypeId] = [];
    }
    variantOptions.value[variantTypeId].push({
        temp_id: 'temp_' + tempIdCounter++,
        value: '',
        display_value: '',
        color_code: ''
    });
};

const removeVariantOption = (variantTypeId, index) => {
    variantOptions.value[variantTypeId].splice(index, 1);
    
    // Correctly remove the key if empty to avoid empty arrays in loops if logic depends on key existence
    if (variantOptions.value[variantTypeId].length === 0) {
        delete variantOptions.value[variantTypeId];
    }
    
    if (variants.value.length > 0) {
        generateVariantCombinations();
    }
};

const generateVariantCombinations = () => {
    const optionArrays = [];
    for (let typeId in variantOptions.value) {
        if (variantOptions.value[typeId].length > 0) {
            optionArrays.push(variantOptions.value[typeId]);
        }
    }
    
    if (optionArrays.length === 0) {
        variants.value = [];
        return;
    }
    
    const combinations = cartesianProduct(optionArrays);
    const basePrice = parseFloat(form.price) || 0;
    const baseCompareAtPrice = parseFloat(form.compare_at_price) || null;
    const baseSku = form.sku || 'VARIANT';
    
    variants.value = combinations.map((combo, index) => {
        const optionIds = combo.map(opt => opt.temp_id || opt.id);
        const skuParts = combo.map(opt => (opt.value || '').toUpperCase().replace(/\s+/g, '-'));
        
        return {
            sku: baseSku + '-' + skuParts.join('-'),
            price: basePrice,
            compare_at_price: baseCompareAtPrice,
            quantity: 0,
            stock_status: 'in_stock',
            option_ids: optionIds,
            is_default: index === 0,
            is_active: true,
            show_in_listing: false
        };
    });
};

const cartesianProduct = (arrays) => {
    if (arrays.length === 0) return [[]];
    if (arrays.length === 1) return arrays[0].map(item => [item]);
    
    const result = [];
    const firstArray = arrays[0];
    const otherArrays = arrays.slice(1);
    const otherProducts = cartesianProduct(otherArrays);
    
    for (let item of firstArray) {
        for (let product of otherProducts) {
            result.push([item, ...product]);
        }
    }
    return result;
};

const getVariantLabel = (variant) => {
    const labels = [];
    if (!variant.option_ids) return 'Unknown';
    
    for (let optionId of variant.option_ids) {
        for (let typeId in variantOptions.value) {
            const option = variantOptions.value[typeId].find(o => (o.temp_id || o.id) == optionId); // loose equality for string/int match
            if (option) {
                labels.push(option.display_value || option.value);
                break;
            }
        }
    }
    return labels.join(' / ');
};

const setDefaultVariant = (index) => {
    variants.value.forEach((v, i) => v.is_default = (i === index));
};

// Get fields for a specific tab
const getTabFields = (tabKey) => {
    const fields = {};
    for (const [key, field] of Object.entries(props.resourceNeo.formInfo)) {
        if (field.tab === tabKey) {
            fields[key] = field;
        }
    }
    return fields;
};

// Check if tab has errors
const tabHasErrors = (tabKey) => {
    const tabFields = getTabFields(tabKey);
    for (const key of Object.keys(tabFields)) {
        if (form.errors[key]) {
            return true;
        }
    }
    return false;
};

// Handle main image change
const handleMainImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.main_image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            mainImagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

// Clear main image
const clearMainImage = () => {
    form.main_image = null;
    mainImagePreview.value = null;
    const fileInput = document.querySelector('input[name="main_image"]');
    if (fileInput) fileInput.value = '';
};

// Handle gallery image upload
const handleGalleryUpload = (event) => {
    const files = Array.from(event.target.files);
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            newGalleryFiles.value.push({
                file: file,
                preview: e.target.result,
                name: file.name
            });
        };
        reader.readAsDataURL(file);
    });
    // Reset input
    event.target.value = '';
};

// Remove existing gallery image
const removeGalleryImage = (index) => {
    const image = galleryImages.value[index];
    deletedGalleryImages.value.push(image.id);
    galleryImages.value.splice(index, 1);
};

// Remove new gallery file
const removeNewGalleryFile = (index) => {
    newGalleryFiles.value.splice(index, 1);
};

// Submit form
const submitform = () => {

    
    // Build FormData manually to include gallery images
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
            // Handle regular values (skip highlights array here, handled below)
            else if (key !== 'highlights') {
                formData.append(key, value);
            }
        }
    }
    
    // Add highlights
    if (highlights.value.length > 0) {
        highlights.value.forEach((highlight, index) => {
            if (highlight) formData.append(`highlights[${index}]`, highlight);
        });
    }

    // Add variants data
    formData.append('has_variants', hasVariants.value ? '1' : '0');
    
    if (hasVariants.value) {
        // Flatten variant options for backend
        const flattenedOptions = [];
        for (let typeId in variantOptions.value) {
            for (let option of variantOptions.value[typeId]) {
                flattenedOptions.push({
                    variant_type_id: parseInt(typeId),
                    temp_id: option.temp_id || option.id,
                    value: option.value,
                    display_value: option.display_value || option.value,
                    color_code: option.color_code || null
                });
            }
        }
        
        formData.append('variant_options_data', JSON.stringify(flattenedOptions));
        formData.append('variants_data', JSON.stringify(variants.value));
        
        // Handle variant images if implemented later (currently using main product image or separate upload needs loop)
    }

    // Add gallery images
    if (newGalleryFiles.value.length > 0) {
        newGalleryFiles.value.forEach((fileObj, index) => {
            formData.append(`gallery_images[${index}]`, fileObj.file);

        });
    }
    
    // Add deleted gallery images
    if (deletedGalleryImages.value.length > 0) {
        deletedGalleryImages.value.forEach((id, index) => {
            formData.append(`deleted_gallery_images[${index}]`, id);

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

// Approval functions
const approveProduct = () => {
    router.post(route('product.approve', props.formdata.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            useToast().success('Product approved successfully!', { duration: 5000 });
        }
    });
};

const openRejectModal = () => {
    rejectionReason.value = props.formdata.rejection_reason || '';
    isRejectModalActive.value = true;
};

const rejectProduct = () => {
    if (!rejectionReason.value.trim()) {
        useToast().warning('Please enter a rejection reason', { duration: 5000 });
        return;
    }
    
    router.post(route('product.reject', props.formdata.id), {
        rejection_reason: rejectionReason.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isRejectModalActive.value = false;
            rejectionReason.value = '';
            useToast().success('Product rejected successfully!', { duration: 5000 });
        }
    });
};

</script>

<template>
    <LayoutAuthenticated>
        <Head :title="props.resourceNeo.resourceTitle" />
        <SectionMain>
            <SectionTitleLineWithButton
                :icon="props.resourceNeo.iconPath"
                :title="props.formdata.id ? 'Edit Product' : 'Add New Product'"
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
                            label="Product List"
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
            
            <!-- Approval Section (Edit Mode Only) -->
            <CardBox v-if="props.formdata.id" class="mb-6">
                <!-- Pending Status -->
                <div v-if="props.formdata.status === 'pending'" class="flex items-center justify-between p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                            <BaseIcon :path="mdiAlert" :size="24" class="text-yellow-600 dark:text-yellow-400" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-yellow-900 dark:text-yellow-200">Pending Approval</h3>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300">This product is awaiting admin approval</p>
                        </div>
                    </div>
                    <div class="flex gap-2" v-if="can('product_approve') || can('product_reject')">
                        <BaseButton
                            v-if="can('product_approve')"
                            @click="approveProduct"
                            color="success"
                            :icon="mdiCheck"
                            label="Approve"
                        />
                        <BaseButton
                            v-if="can('product_reject')"
                            @click="openRejectModal"
                            color="danger"
                            :icon="mdiClose"
                            label="Reject"
                        />
                    </div>
                </div>

                <!-- Approved Status -->
                <div v-if="props.formdata.status === 'approved'" class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 dark:bg-green-900 rounded-full">
                            <BaseIcon :path="mdiCheck" :size="24" class="text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-green-900 dark:text-green-200">Approved</h3>
                            <p class="text-sm text-green-700 dark:text-green-300">
                                Approved by {{ props.formdata.approver_name || 'Admin' }} on 
                                {{ new Date(props.formdata.approved_at).toLocaleDateString() }}
                            </p>
                        </div>
                    </div>
                    <BaseButton
                        v-if="can('product_reject')"
                        @click="openRejectModal"
                        color="danger"
                        :icon="mdiClose"
                        label="Reject"
                        outline
                    />
                </div>

                <!-- Rejected Status -->
                <div v-if="props.formdata.status === 'rejected'" class="flex items-center justify-between p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-red-100 dark:bg-red-900 rounded-full">
                            <BaseIcon :path="mdiClose" :size="24" class="text-red-600 dark:text-red-400" />
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-red-900 dark:text-red-200">Rejected</h3>
                            <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                                <span class="font-medium">Reason:</span> {{ props.formdata.rejection_reason || 'No reason provided' }}
                            </p>
                        </div>
                    </div>
                    <BaseButton
                        v-if="can('product_approve')"
                        @click="approveProduct"
                        color="success"
                        :icon="mdiCheck"
                        label="Approve"
                        outline
                    />
                </div>
            </CardBox>
            
            <form @submit.prevent="submitform">
                <!-- Tab Navigation -->
                <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-4 overflow-x-auto" aria-label="Tabs">
                        <button
                            v-for="tab in props.resourceNeo.tabs"
                            :key="tab.key"
                            type="button"
                            @click="activeTab = tab.key"
                            :class="[
                                activeTab === tab.key
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                                'group inline-flex items-center py-4 px-4 border-b-2 font-medium text-sm whitespace-nowrap transition-colors duration-200'
                            ]"
                        >
                            <BaseIcon
                                :path="tab.icon"
                                :size="18"
                                class="mr-2"
                                :class="tabHasErrors(tab.key) ? 'text-red-500' : ''"
                            />
                            <span :class="tabHasErrors(tab.key) ? 'text-red-500' : ''">
                                {{ tab.label }}
                            </span>
                            <span
                                v-if="tabHasErrors(tab.key)"
                                class="ml-2 w-2 h-2 bg-red-500 rounded-full"
                            ></span>
                        </button>
                    </nav>
                </div>
                
                <!-- Tab Content -->
                <CardBox class="mb-6">
                    <!-- Basic Info Tab -->
                    <div v-show="activeTab === 'basic'">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <FormField
                                v-for="(formField, key) in getTabFields('basic')"
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
                    </div>
                    
                    <!-- Pricing & Stock Tab -->
                    <div v-show="activeTab === 'pricing'">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <FormField
                                v-for="(formField, key) in getTabFields('pricing')"
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
                    </div>
                    
                    <!-- Images Tab -->
                    <div v-show="activeTab === 'images'">
                        <!-- Main Image Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Main Image</h3>
                            <div class="flex items-start space-x-6">
                                <!-- Current/Preview Image -->
                                <div class="relative" v-if="mainImagePreview || props.resourceNeo.formInfo.main_image?.currentFile">
                                    <img
                                        :src="mainImagePreview || props.resourceNeo.formInfo.main_image?.currentFile"
                                        alt="Main product image"
                                        class="w-48 h-48 object-cover rounded-lg border-2 border-gray-200 dark:border-gray-700 shadow-sm"
                                    />
                                    <button
                                        type="button"
                                        @click="clearMainImage"
                                        v-if="mainImagePreview"
                                        class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 shadow-lg"
                                    >
                                        <BaseIcon :path="mdiDelete" :size="16" />
                                    </button>
                                </div>
                                
                                <!-- Upload Input -->
                                <div class="flex-1">
                                    <input
                                        type="file"
                                        name="main_image"
                                        accept="image/*"
                                        @change="handleMainImageChange"
                                        class="block w-full text-sm text-gray-500 dark:text-gray-400
                                            file:mr-4 file:py-2 file:px-4
                                            file:rounded-md file:border-0
                                            file:text-sm file:font-semibold
                                            file:bg-blue-50 file:text-blue-700
                                            hover:file:bg-blue-100
                                            dark:file:bg-gray-700 dark:file:text-gray-300
                                            cursor-pointer"
                                    />
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        Upload the primary product image (max 2MB)
                                    </p>
                                    <p v-if="form.errors.main_image" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.main_image }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Gallery Images Section -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Gallery Images</h3>
                            
                            <!-- No images message -->
                            <div v-if="galleryImages.length === 0 && newGalleryFiles.length === 0" class="mb-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                                    No gallery images yet. Upload images below to create a product gallery.
                                </p>
                            </div>
                            
                            <!-- Existing Gallery Images -->
                            <div v-if="galleryImages.length > 0" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-4">
                                <div
                                    v-for="(image, index) in galleryImages"
                                    :key="image.id"
                                    class="relative group"
                                >
                                    <img
                                        :src="image.url"
                                        :alt="image.alt_text || 'Gallery image'"
                                        class="w-full h-32 object-cover rounded-lg border-2 border-gray-200 dark:border-gray-700"
                                    />
                                    <button
                                        type="button"
                                        @click="removeGalleryImage(index)"
                                        class="absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                                    >
                                        <BaseIcon :path="mdiDelete" :size="14" />
                                    </button>
                                </div>
                            </div>
                            
                            <!-- New Gallery Images Preview -->
                            <div v-if="newGalleryFiles.length > 0" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-4">
                                <div
                                    v-for="(file, index) in newGalleryFiles"
                                    :key="'new-' + index"
                                    class="relative group"
                                >
                                    <img
                                        :src="file.preview"
                                        :alt="file.name"
                                        class="w-full h-32 object-cover rounded-lg border-2 border-green-400 dark:border-green-600"
                                    />
                                    <span class="absolute bottom-0 left-0 right-0 bg-green-500 text-white text-xs px-1 py-0.5 rounded-b-lg truncate">
                                        New
                                    </span>
                                    <button
                                        type="button"
                                        @click="removeNewGalleryFile(index)"
                                        class="absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                                    >
                                        <BaseIcon :path="mdiDelete" :size="14" />
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Upload More -->
                            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-blue-400 transition-colors">
                                <input
                                    type="file"
                                    multiple
                                    accept="image/*"
                                    @change="handleGalleryUpload"
                                    class="hidden"
                                    id="gallery-upload"
                                />
                                <label
                                    for="gallery-upload"
                                    class="cursor-pointer flex flex-col items-center"
                                >
                                    <BaseIcon :path="mdiPlus" :size="32" class="text-gray-400 mb-2" />
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        Click to add gallery images
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description Tab -->
                    <div v-show="activeTab === 'description'">
                        <div class="space-y-6">
                            <FormField
                                v-for="(formField, key) in getTabFields('description')"
                                :key="key"
                                :label="formField.label"
                                :help="formField.tooltip"
                                :error="form.errors[key]"
                            >
                                <textarea v-if="key === 'description' || key === 'short_description'"
                                    v-model="form[key]"
                                    :rows="key === 'description' ? 8 : 3"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-slate-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    :placeholder="formField.tooltip"
                                ></textarea>
                                
                                <!-- Highlights UI -->
                                <div v-if="key === 'highlights'" class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <BaseButton
                                            type="button"
                                            @click="addHighlight()"
                                            small
                                            color="info"
                                            outline
                                            :icon="mdiPlus"
                                            label="Add Highlight"
                                        />
                                    </div>
                                    
                                    <div v-if="highlights.length === 0" class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 text-center text-sm text-gray-500">
                                        No highlights added. Add highlights to show key features in a bulleted list.
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <div v-for="(highlight, index) in highlights" :key="index" class="flex items-center gap-2">
                                            <div class="flex-none bg-gray-100 dark:bg-gray-700 p-2 rounded text-gray-500 dark:text-gray-400">
                                                <BaseIcon :path="mdiFormatListBulleted" :size="16" />
                                            </div>
                                            <input 
                                                type="text" 
                                                v-model="highlights[index]" 
                                                class="flex-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-slate-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                                placeholder="e.g. Fast Charging Support"
                                            />
                                            <BaseButton
                                                type="button"
                                                @click="removeHighlight(index)"
                                                small
                                                color="danger"
                                                iconOnly
                                                :icon="mdiDelete"
                                                rounded-full
                                            />
                                        </div>
                                    </div>
                                </div>
                            </FormField>
                        </div>
                    </div>
                    
                    <!-- SEO Tab -->
                    <div v-show="activeTab === 'seo'">
                        <div class="space-y-6">
                            <FormField
                                v-for="(formField, key) in getTabFields('seo')"
                                :key="key"
                                :label="formField.label"
                                :help="formField.tooltip"
                                :error="form.errors[key]"
                            >
                                <FormFields
                                    :form-field="formField"
                                    :form="form"
                                    :fkey="key"
                                />
                            </FormField>
                        </div>
                        
                        <!-- SEO Preview -->
                        <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Search Engine Preview</h4>
                            <div class="bg-white dark:bg-gray-900 p-4 rounded border dark:border-gray-700">
                                <div class="text-blue-600 dark:text-blue-400 text-lg hover:underline cursor-pointer">
                                    {{ form.meta_title || form.name || 'Product Title' }}
                                </div>
                                <div class="text-green-700 dark:text-green-500 text-sm">
                                    {{ 'https://example.com/product/' + (form.slug || 'product-slug') }}
                                </div>
                                <div class="text-gray-600 dark:text-gray-400 text-sm mt-1">
                                    {{ form.meta_description || form.short_description || 'Product description will appear here...' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Info Tab (Now Shipping because key changed) -->
                    <div v-show="activeTab === 'shipping'">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <FormField
                                v-for="(formField, key) in getTabFields('shipping')"
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
                    </div>

                    <!-- Variants Tab -->
                    <div v-show="activeTab === 'variants'">
                        <div class="space-y-6">
                            <div class="flex items-center gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="hasVariants" class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                    <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">This product has multiple variants (e.g., different colors, sizes)</span>
                                </label>
                            </div>

                            <div v-if="!hasVariants" class="text-center py-8 text-gray-500 dark:text-gray-400 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                                <p>Enable variants to create multiple versions of this product with different attributes.</p>
                            </div>

                            <div v-else class="space-y-6">
                                <div v-if="categoryVariantTypes.length === 0" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-200 rounded-lg">
                                    No variant types available for this category. Please select a category that has variant types assigned.
                                </div>

                                <div v-else class="space-y-8">
                                    <!-- Variant Options Section -->
                                    <CardBox class="bg-gray-50 dark:bg-gray-800">
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Define Variant Options</h3>
                                        
                                        <div v-for="variantType in categoryVariantTypes" :key="variantType.id" class="mb-6 last:mb-0 pb-6 last:pb-0 border-b last:border-0 border-gray-200 dark:border-gray-700">
                                            <div class="flex items-center justify-between mb-3">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ variantType.name }}</label>
                                                <BaseButton
                                                    type="button"
                                                    @click="addVariantOption(variantType.id)"
                                                    small
                                                    color="info"
                                                    outline
                                                    :icon="mdiPlus"
                                                    :label="'Add ' + variantType.name"
                                                />
                                            </div>
                                            
                                            <div class="space-y-3">
                                                <p v-if="!variantOptions[variantType.id] || variantOptions[variantType.id].length === 0" class="text-sm text-gray-500 italic">
                                                    No options added yet
                                                </p>
                                                
                                                <div v-else class="space-y-3">
                                                    <div v-for="(option, index) in variantOptions[variantType.id]" :key="option.temp_id || option.id" class="flex items-center gap-3">
                                                        <input 
                                                            type="text" 
                                                            v-model="option.value" 
                                                            @blur="option.display_value = option.display_value || option.value"
                                                            class="w-32 rounded-md border-gray-300 dark:border-gray-700 dark:bg-slate-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                                            placeholder="Value"
                                                        />
                                                        <input 
                                                            type="text" 
                                                            v-model="option.display_value" 
                                                            class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-slate-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                                            placeholder="Display Name"
                                                        />
                                                        <input 
                                                            v-if="variantType.input_type === 'color'"
                                                            type="color" 
                                                            v-model="option.color_code" 
                                                            class="w-10 h-10 p-1 rounded-md border border-gray-300 dark:border-gray-700 bg-white"
                                                        />
                                                        <BaseButton
                                                            type="button"
                                                            @click="removeVariantOption(variantType.id, index)"
                                                            small
                                                            color="danger"
                                                            iconOnly
                                                            :icon="mdiDelete"
                                                            rounded-full
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </CardBox>

                                    <!-- Generate Button -->
                                    <div class="flex justify-center">
                                        <BaseButton
                                            type="button"
                                            @click="generateVariantCombinations"
                                            color="success"
                                            label="Generate Variant Combinations"
                                        />
                                    </div>

                                    <!-- Variants Matrix -->
                                    <CardBox v-if="variants.length > 0">
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Variant Combinations</h3>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                                <thead class="bg-gray-50 dark:bg-gray-700">
                                                    <tr>
                                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Combination</th>
                                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">SKU</th>
                                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Price</th>
                                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stock</th>
                                                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Default</th>
                                                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Active</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                    <tr v-for="(variant, index) in variants" :key="index">
                                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                            {{ getVariantLabel(variant) }}
                                                        </td>
                                                        <td class="px-3 py-3 whitespace-nowrap">
                                                            <input type="text" v-model="variant.sku" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-slate-700 dark:text-white text-sm py-1">
                                                        </td>
                                                        <td class="px-3 py-3 whitespace-nowrap">
                                                            <input type="number" v-model="variant.price" step="0.01" class="w-24 rounded-md border-gray-300 dark:border-gray-700 dark:bg-slate-700 dark:text-white text-sm py-1">
                                                        </td>
                                                        <td class="px-3 py-3 whitespace-nowrap">
                                                            <input type="number" v-model="variant.quantity" class="w-20 rounded-md border-gray-300 dark:border-gray-700 dark:bg-slate-700 dark:text-white text-sm py-1">
                                                        </td>
                                                        <td class="px-3 py-3 whitespace-nowrap text-center">
                                                            <input type="radio" name="default_variant" :checked="variant.is_default" @change="setDefaultVariant(index)" class="text-blue-600 focus:ring-blue-500">
                                                        </td>
                                                        <td class="px-3 py-3 whitespace-nowrap text-center">
                                                            <input type="checkbox" v-model="variant.is_active" class="rounded text-blue-600 focus:ring-blue-500">
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </CardBox>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardBox>
                
                <!-- Form Actions -->
                <div class="flex items-center justify-between">
                    <div class="flex">
                        <BaseButton
                            class="mr-2"
                            type="submit"
                            small
                            :disabled="form.processing"
                            color="info"
                            :label="props.formdata.id ? 'Update Product' : 'Save Product'"
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
                    
                    <!-- Tab Navigation Buttons -->
                    <div class="flex space-x-2">
                        <BaseButton
                            v-if="props.resourceNeo.tabs.findIndex(t => t.key === activeTab) > 0"
                            type="button"
                            small
                            color="white"
                            outline
                            label="← Previous"
                            @click="activeTab = props.resourceNeo.tabs[props.resourceNeo.tabs.findIndex(t => t.key === activeTab) - 1].key"
                        />
                        <BaseButton
                            v-if="props.resourceNeo.tabs.findIndex(t => t.key === activeTab) < props.resourceNeo.tabs.length - 1"
                            type="button"
                            small
                            color="success"
                            outline
                            label="Next →"
                            @click="activeTab = props.resourceNeo.tabs[props.resourceNeo.tabs.findIndex(t => t.key === activeTab) + 1].key"
                        />
                    </div>
                </div>
            </form>
        </SectionMain>

        <!-- Reject Reason Modal -->
        <CardBoxModal
            v-model="isRejectModalActive"
            buttonLabel="Reject Product"
            title="Reject Product"
            button="danger"
            has-cancel
            @confirm="rejectProduct"
        >
            <div class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Please provide a reason for rejecting this product:</p>
                <textarea
                    v-model="rejectionReason"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                    placeholder="Enter rejection reason..."
                    maxlength="1000"
                ></textarea>
                <p class="text-xs text-gray-500">{{ rejectionReason.length }}/1000 characters</p>
            </div>
        </CardBoxModal>
    </LayoutAuthenticated>
</template>
