<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed, nextTick, onMounted, ref, watch } from "vue";
import Quill from "quill";
import "quill/dist/quill.snow.css";
import * as QuillTableUI from "quill-table-ui";
import "quill-table-ui/dist/index.css";

Quill.register(
    {
        "modules/tableUI": QuillTableUI.default,
    },
    true
);
import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import { mdiArrowLeft, mdiFileDocumentEdit } from "@mdi/js";
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

const slugify = (value) =>
    (value || "")
        .toString()
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, "")
        .replace(/\s+/g, "-")
        .replace(/-+/g, "-");

const form = useForm({
    title: props.model?.title ?? "",
    slug: props.model?.slug ?? "",
    content: props.model?.content ?? "<p></p>",
    meta_title: props.model?.meta_title ?? "",
    meta_description: props.model?.meta_description ?? "",
    is_active: props.model?.is_active ?? true,
});

const previousGeneratedSlug = ref(slugify(form.title));

watch(
    () => form.title,
    (newTitle) => {
        const generatedSlug = slugify(newTitle);
        if (!props.model || form.slug === previousGeneratedSlug.value || form.slug === "") {
            form.slug = generatedSlug;
        }
        previousGeneratedSlug.value = generatedSlug;
    }
);

const editorEl = ref(null);
let quillInstance = null;
let updatingFromForm = false;

onMounted(async () => {
    await nextTick();

    if (!editorEl.value) {
        return;
    }

    quillInstance = new Quill(editorEl.value, {
        theme: "snow",
        modules: {
            table: true,
            tableUI: true,
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ["bold", "italic", "underline", "strike"],
                [{ list: "ordered" }, { list: "bullet" }],
                [{ color: [] }, { background: [] }],
                [{ align: [] }],
                ["link", "blockquote", "code-block"],
                ["table"],
                ["clean"],
            ],
        },
    });

    quillInstance.root.innerHTML = form.content || "<p><br></p>";

    quillInstance.on("text-change", () => {
        if (updatingFromForm) {
            return;
        }
        form.content = quillInstance.root.innerHTML;
    });
});

watch(
    () => form.content,
    (value) => {
        if (!quillInstance) {
            return;
        }

        if (quillInstance.root.innerHTML === value) {
            return;
        }

        updatingFromForm = true;
        quillInstance.root.innerHTML = value || "<p><br></p>";
        updatingFromForm = false;
    }
);

const submit = () => {
    if (props.model) {
        form.put(route("page.update", props.model.id));
        return;
    }

    form.post(route("page.store"));
};

const pageTitle = computed(() => (props.model ? "Edit Page" : "Create Page"));
</script>

<template>
    <LayoutAuthenticated>
        <Head :title="pageTitle" />
        <SectionMain>
            <SectionTitleLineWithButton :icon="mdiFileDocumentEdit" :title="pageTitle" main>
                <Link :href="route('page.index')">
                    <BaseButton class="m-2" :icon="mdiArrowLeft" color="whiteDark" rounded-full small label="Back" />
                </Link>
            </SectionTitleLineWithButton>

            <form @submit.prevent="submit">
                <CardBox>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <FormField label="Title" :error="form.errors.title" class="!mb-0">
                            <FormControl v-model="form.title" placeholder="Page title" required />
                        </FormField>

                        <FormField label="Slug" :error="form.errors.slug" class="!mb-0">
                            <FormControl v-model="form.slug" placeholder="page-slug" />
                        </FormField>

                        <FormField label="Meta Title" :error="form.errors.meta_title" class="!mb-0">
                            <FormControl v-model="form.meta_title" placeholder="SEO meta title" />
                        </FormField>

                        <FormField label="Active" :error="form.errors.is_active" class="!mb-0">
                            <label class="inline-flex items-center gap-2 text-sm font-medium mt-2">
                                <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600" />
                                Active
                            </label>
                        </FormField>

                        <FormField label="Meta Description" :error="form.errors.meta_description" class="!mb-0 lg:col-span-2">
                            <FormControl v-model="form.meta_description" type="textarea" placeholder="SEO meta description" />
                        </FormField>

                        <FormField label="Content" :error="form.errors.content" class="!mb-0 lg:col-span-2">
                            <div class="bg-white border border-gray-300 rounded dark:bg-slate-800 dark:border-slate-700 overflow-hidden">
                                <div ref="editorEl" class="page-editor"></div>
                            </div>
                        </FormField>
                    </div>
                </CardBox>

                <div class="mt-4 flex">
                    <BaseButton
                        class="mr-2"
                        type="submit"
                        small
                        :disabled="form.processing"
                        :loading="form.processing"
                        color="info"
                        :label="props.model ? 'Update' : 'Save'"
                    />
                    <Link :href="route('page.index')">
                        <BaseButton type="button" small color="info" outline label="Cancel" />
                    </Link>
                </div>
            </form>
        </SectionMain>
    </LayoutAuthenticated>
</template>

<style scoped>
.page-editor :deep(.ql-editor) {
    min-height: 320px;
}
</style>
