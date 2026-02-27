<script setup>
import FormControl from "@/components/FormControl.vue";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import Multiselect from "vue-multiselect";
import "../../css/vue-multiselect.css";
import FormCheckRadioGroup from '@/components/FormCheckRadioGroup.vue';
import { ref } from 'vue';

const props = defineProps({
    formField: {
        type: Object,
        default: {},
    },
    form: {
        type: Object,
        default: {},
    },
    fkey: {
        type: String,
        default: "",
    },
    onChangeFunc: {
        type: Function,
        default: () => {},
        required: false,
    },
});

// File preview state
const filePreview = ref({});

// Handle file change with preview
const handleFileChange = (event, key) => {
    const file = event.target.files[0];
    if (file) {
        props.form[key] = file;
        
        // Create preview for images
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                filePreview.value[key] = e.target.result;
            };
            reader.readAsDataURL(file);
        }
        
        props.onChangeFunc(key);
    }
};

// Clear file selection
const clearFile = (key) => {
    props.form[key] = null;
    filePreview.value[key] = null;
    // Reset file input
    const fileInput = document.querySelector(`input[name="${key}"]`);
    if (fileInput) fileInput.value = '';
};

// Format file size
const formatFileSize = (bytes) => {
    if (!bytes) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const filteredOption = (source, sKey, sFetch, compVal, key) => {
    let redata = [];
    source.forEach((element) => {
        if (element[sKey] == compVal) {
            redata.push(element[sFetch]);
        }
    });
    props.form[key] = redata.includes(props.form[key]) ? props.form[key] : null;
    return redata;
};
</script>
<template>
    <div v-if="formField.type == 'dummy'"></div>
    <Multiselect
        v-else-if="formField.type == 'multiselect'"
        :multiple="true"
        :disabled="formField.disabled"
        track-by="id"
        label="label"
        select-label=""
        v-model="form[fkey]"
        :options="formField.options"
        @select="onChangeFunc(fkey)"
    >
    </Multiselect>
    <Multiselect
        v-else-if="
            formField.type == 'select' && formField.optionType == 'array'
        "
        v-model="form[fkey]"
        select-label=""
        :disabled="formField.disabled"
        :options="
            formField.filter
                ? filteredOption(
                      formField.options,
                      formField.filter['comp'],
                      formField.filter['fetch'],
                      form[formField.filter['on']],
                      fkey
                  )
                : formField.options
        "
        @select="onChangeFunc(fkey)"
    >
    </Multiselect>
    <Multiselect
        v-else-if="formField.type == 'select'"
        track-by="id"
        label="label"
        :disabled="formField.disabled"
        select-label=""
        v-model="form[fkey]"
        :options="formField.options"
        @select="onChangeFunc(fkey)"
    >
    </Multiselect>
    <FormControl
        v-else-if="formField.type == 'number'"
        type="number"
        :disabled="formField.disabled"
        :readonly="formField.readonly"
        :name="fkey"
        v-model="form[fkey]"
        @update:modelValue="onChangeFunc(fkey)"
    />
    <FormControl
        v-else-if="formField.type == 'password'"
        type="password"
        :name="fkey"
        v-model="form[fkey]"
        @update:modelValue="onChangeFunc(fkey)"
    />
    <VueDatePicker
        v-else-if="formField.type == 'datepicker'"
        input-class-name="text-gray-500 dark:!text-white shadow-sm text-sm !bg-white dark:!bg-slate-800 !border-gray-700 "
        :month-change-on-scroll="false"
        :range="false"
        :enable-time-picker="false"
        arrow-navigation
        format="yyyy-MM-dd"
        auto-apply
        v-model="form[fkey]"
    >
    </VueDatePicker>
    <VueDatePicker
        v-else-if="formField.type == 'datetimepicker'"
        input-class-name="text-gray-500 dark:!text-white shadow-sm text-sm !bg-white dark:!bg-slate-800 !border-gray-700 "
        :month-change-on-scroll="false"
        :range="false"
        :enable-time-picker="true"
        arrow-navigation
        format="yyyy-MM-dd HH:mm"
        auto-apply
        v-model="form[fkey]"
    >
    </VueDatePicker>
    <!-- Enhanced File Upload -->
    <div v-else-if="formField.type == 'file'" class="space-y-2">
        <!-- File Input -->
        <div class="relative">
            <input
                type="file"
                :name="fkey"
                :disabled="formField.disabled"
                :readonly="formField.readonly"
                :accept="formField.accept || 'image/*'"
                @change="handleFileChange($event, fkey)"
                class="block w-full text-sm text-gray-500 dark:text-gray-400
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-md file:border-0
                    file:text-sm file:font-semibold
                    file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100
                    dark:file:bg-gray-700 dark:file:text-gray-300
                    dark:hover:file:bg-gray-600
                    cursor-pointer"
            />
        </div>
        
        <!-- Image Preview -->
        <div v-if="filePreview[fkey]" class="mt-3">
            <div class="relative inline-block">
                <img 
                    :src="filePreview[fkey]" 
                    :alt="form[fkey]?.name || 'Preview'"
                    class="max-w-xs max-h-48 rounded-lg border-2 border-gray-200 dark:border-gray-700 shadow-sm"
                />
                <button
                    type="button"
                    @click="clearFile(fkey)"
                    class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 shadow-lg"
                    title="Remove image"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                {{ form[fkey]?.name }} ({{ formatFileSize(form[fkey]?.size) }})
            </p>
        </div>
        
        <!-- Existing Image (for edit mode) -->
        <div v-else-if="formField.currentFile" class="mt-3">
            <div class="relative inline-block">
                <img 
                    :src="formField.currentFile" 
                    alt="Current image"
                    class="max-w-xs max-h-48 rounded-lg border-2 border-gray-200 dark:border-gray-700 shadow-sm"
                />
                <span class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded-b-lg">
                    Current Image
                </span>
            </div>
        </div>
    </div>


    <FormCheckRadioGroup
    v-else-if="formField.type == 'switch'"
     v-model="form[fkey]"
    name="fkey"
    type="switch"
    :options="{ true: formField.value??'Active' }"
            />
    <FormControl
        v-else
        :disabled="formField.disabled"
        :readonly="formField.readonly"
        :name="fkey"
        v-model="form[fkey]"
        @update:modelValue="onChangeFunc(fkey)"
    />
</template>
