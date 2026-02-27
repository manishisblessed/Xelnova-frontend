<template>
    <ButtonWithDropdown
        placement="bottom-end"
        dusk="filters-dropdown"
        :active="hasEnabledFilters"
        title="Filters"
    >
        <template #button>
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                :class="{
                    'text-gray-400': !hasEnabledFilters,
                    'text-green-400': hasEnabledFilters,
                }"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path
                    fill-rule="evenodd"
                    d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                    clip-rule="evenodd"
                />
            </svg>
        </template>

        <div
            role="menu"
            aria-orientation="horizontal"
            aria-labelledby="filter-menu"
            class="min-w-max"
        >
            <div
                v-for="(filter, key) in filters"
                :key="key"
                class="flex flex-row py-1"
            >
                <div
                    class="w-2/5 text-xs uppercase tracking-wide text-gray-900 dark:text-gray-100 text-opacity py-3 pr-3"
                >
                    {{ filter.label }}
                </div>
                <div class="w-3/5 p-0">
                    <select
                        v-if="filter.type === 'select'"
                        :name="filter.key"
                        :value="filter.value"
                        class="block focus:border-transparent focus:ring-0 text-gray-500 dark:text-gray-100 w-full shadow-sm text-sm bg-gray-100 dark:bg-slate-600 rounded-md"
                        @change="
                            onFilterChange(filter.key, $event.target.value)
                        "
                    >
                        <option
                            v-for="(option, optionKey) in filter.options"
                            :key="optionKey"
                            :value="optionKey"
                        >
                            {{ option }}
                        </option>
                    </select>
                    <VueDatePicker
                        input-class-name="text-gray-500 dark:text-gray-100 shadow-sm text-sm bg-gray-100 dark:bg-slate-600"
                        :month-change-on-scroll="false"
                        :range="false"
                        :enable-time-picker="false"
                        format="dd-MM-yyyy"
                        auto-apply
                        v-if="filter.type === 'datePicker'"
                        :model-value="filter.value"
                        @update:model-value="setFilterValue(filter.key, $event)"
                        :name="filter.key"
                    >
                    </VueDatePicker>
                </div>
            </div>
        </div>
    </ButtonWithDropdown>
</template>

<script setup>
import ButtonWithDropdown from "./ButtonWithDropdown.vue";

import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

const props = defineProps({
    hasEnabledFilters: {
        type: Boolean,
        required: true,
    },

    filters: {
        type: Object,
        required: true,
    },

    onFilterChange: {
        type: Function,
        required: true,
    },
});

function setFilterValue(key, val) {
    var d = new Date(val),
        month = d.getMonth() + 1,
        day = d.getDate(),
        year = d.getFullYear();
    props.onFilterChange(key, val ? year + "-" + month + "-" + day : "");
}
</script>
