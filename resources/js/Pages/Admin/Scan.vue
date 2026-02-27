<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";

import LayoutAuthenticated from "@/layouts/LayoutAuthenticated.vue";
import { mdiFormatListBulleted, mdiBarcode, mdiQrcode } from "@mdi/js";
import SectionMain from "@/components/SectionMain.vue";
import SectionTitleLineWithButton from "@/components/SectionTitleLineWithButton.vue";
import BaseButton from "@/components/BaseButton.vue";
import CardBox from "@/components/CardBox.vue";
import "@vuepic/vue-datepicker/dist/main.css";
import NotificationBar from "@/components/NotificationBar.vue";
import FormField from "@/components/FormField.vue";
import FormFields from "@/components/FormFields.vue";
import FormFieldsMulti from "@/components/FormFieldsMulti.vue";
import BarcodeScanner from "@/components/BarcodeScanner.vue";
import CardBoxModal from "@/components/CardBoxModal.vue";
import axios from "axios";

const message = computed(() => usePage().props.flash.message);
const msg_type = computed(() => usePage().props.flash.msg_type ?? "warning");

import clone from "lodash-es/clone";
import { computed, onBeforeMount, watch, ref } from "vue";
const props = defineProps({
    formdata: {
        type: Object,
        default: () => ({}),
    },
    resourceNeo: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm(() => {
    const temp = clone(props.resourceNeo.formInfo);
    temp["multi"] = [];
    return temp;
});

const multiDatas = ref([]);
const addMoreMulti = () => {
    let tepm = {};
    let tepm2 = {};
    for (const key in props.resourceNeo.formInfoMulti) {
        tepm[key] = props.resourceNeo.formInfoMulti[key]["default"] ?? "";
        tepm2[key] = clone(props.resourceNeo.formInfoMulti[key]);
    }
    multiDatas.value.push(tepm2);
    form["multi"].push(tepm);
    multiDatas.value[multiDatas.value.length - 1]["out_product_group"][
        "options"
    ] = [];
    multiDatas.value[multiDatas.value.length - 1]["out_product"]["options"] =
        [];
};
const delMulti = (el) => {
    multiDatas.value.splice(el * 1, 1);
    form["multi"].splice(el * 1, 1);
};

onBeforeMount(async () => {
    let tepm2 = {};
    for (const key in props.resourceNeo.formInfoMulti) {
        tepm2[key] = clone(props.resourceNeo.formInfoMulti[key]);
    }
    multiDatas.value.push(tepm2);

    for (const key in props.resourceNeo.formInfo) {
        form[key] =
            props.formdata[key] ??
            props.resourceNeo.formInfo[key]["default"] ??
            "";
    }
    form["out_date"] = form["out_date"] != "" ? form["out_date"] : new Date();

    if (props.formdata["multi"]) {
        for (const index in props.formdata["multi"]) {
            let tepm = {};
            for (const key in props.resourceNeo.formInfoMulti) {
                tepm[key] = props.formdata["multi"][index][key];
            }
            tepm["id"] = props.formdata["multi"][index]["id"];
            if (index > 0) {
                multiDatas.value.push(props.resourceNeo.formInfoMulti);
            }
            if (
                props.formdata["multi"][index]["out_product_group"] &&
                props.formdata["multi"][index]["out_product_group_id"]
            ) {
                tepm["out_product_group"] = {
                    id: props.formdata["multi"][index]["out_product_group_id"],
                    label: props.formdata["multi"][index]["out_product_group"],
                };
            }

            form["multi"].push(tepm);

            if (props.formdata["multi"][index]["out_product_id"]) {
                await fetchProd(index).then(() => {
                    for (const opkey in props.resourceNeo.formInfoMulti[
                        "out_product"
                    ].options) {
                        if (
                            props.resourceNeo.formInfoMulti["out_product"]
                                .options[opkey].id ==
                            props.formdata["multi"][index]["out_product_id"]
                        ) {
                            form["multi"][index]["out_product"] =
                                props.resourceNeo.formInfoMulti[
                                    "out_product"
                                ].options[opkey];
                        }
                    }
                });
            }
        }
    } else {
        let tepm = {};
        for (const key in props.resourceNeo.formInfoMulti) {
            tepm[key] =
                props.formdata[key] ??
                props.resourceNeo.formInfoMulti[key]["default"] ??
                "";
        }
        form["multi"].push(tepm);
    }
});

const submitform = () => {
    if (valdateform()) {
        if (props.formdata.id) {
            form.put(
                route(
                    props.resourceNeo.resourceName + ".update",
                    props.formdata.id
                )
            );
        } else {
            form.post(route(props.resourceNeo.resourceName + ".store"));
        }
    }
};

const onChangeFunc = (index, fkey) => {
    if (fkey == "out_incharge") {
        fetchProdLoc(index);
    }
    if (fkey == "out_incharge" || fkey == "out_loc") {
        fetchProdGroup(index);
    }
    if (
        fkey == "out_incharge" ||
        fkey == "out_loc" ||
        fkey == "out_product_group"
    ) {
        fetchProd(index);
    }

    if (fkey == "out_product") {
        form["multi"][index].out_qty_unit =
            form["multi"][index].out_product.data.pur_unint_int;
        form["multi"][index].out_qty_unit_alt =
            form["multi"][index].out_product.data.pur_unint_int_alt;
    } else if (fkey == "out_qty") {
        form["multi"][index].out_qty_alt =
            parseFloat(form["multi"][index].out_qty) *
            (parseFloat(form["multi"][index].out_product.data.pur_qty_int_alt) /
                parseFloat(form["multi"][index].out_product.data.pur_qty_int));
    } else if (fkey == "out_qty_alt") {
        form["multi"][index].out_qty =
            parseFloat(form["multi"][index].out_qty_alt) /
            (parseFloat(form["multi"][index].out_product.data.pur_qty_int_alt) /
                parseFloat(form["multi"][index].out_product.data.pur_qty_int));
    }
};

const valdateform = () => {
    return true;
};

const fetchProdLoc = async (index) => {
    if (form["multi"][index]["out_incharge"]) {
        await axios
            .post(route("outward.productsloc"), {
                out_incharge: form["multi"][index]["out_incharge"],
            })
            .then((response) => {
                form["multi"][index]["out_loc"] = "";
                multiDatas.value[index]["out_loc"]["options"] = response.data;
            });
    }
};

const fetchProd = async (index) => {
    if (
        form["multi"][index]["out_incharge"] &&
        form["multi"][index]["out_loc"] &&
        form["multi"][index]["out_product_group"]
    ) {
        await axios
            .post(route("outward.products"), {
                out_incharge: form["multi"][index]["out_incharge"],
                out_loc: form["multi"][index]["out_loc"],
                out_product_group: form["multi"][index]["out_product_group"].id,
            })
            .then((response) => {
                form["multi"][index]["out_product"] = "";
                multiDatas.value[index]["out_product"]["options"] =
                    response.data;
                // props.resourceNeo.formInfo["out_product"]["options"] = response.data;
            });
    }
};

const fetchProdGroup = async (index) => {
    if (
        form["multi"][index]["out_incharge"] &&
        form["multi"][index]["out_loc"]
    ) {
        await axios
            .post(route("outward.productsgroup"), {
                out_incharge: form["multi"][index]["out_incharge"],
                out_loc: form["multi"][index]["out_loc"],
            })
            .then((response) => {
                form["multi"][index]["out_product_group"] = "";
                multiDatas.value[index]["out_product_group"]["options"] =
                    response.data;
            });
    }
};

const isModalActive = ref(false);
const currentIndex = ref(null);
const scanMode = ref("qrcode");

const openscanner = (index, mode = "qrcode") => {
    currentIndex.value = index;
    scanMode.value = mode;
    isModalActive.value = true;
};
const resetIndex = () => {
    currentIndex.value = null;
};

const onBarcodeDetected = (barcode) => {
    // Handle the scanned barcode here
    console.log("Barcode detected:", barcode);
    // You can update the form with the scanned barcode
    if (form.multi[currentIndex.value]) {
        const options =
            multiDatas.value[currentIndex.value]["out_product"]["options"];
        for (const opkey in options) {
            if (options[opkey].label == barcode) {
                form["multi"][currentIndex.value]["out_product"] =
                    options[opkey];
                onChangeFunc(currentIndex.value, "out_product");
                form["multi"][currentIndex.value]["out_qty_alt"] = 1;
                onChangeFunc(currentIndex.value, "out_qty_alt");
                isModalActive.value = false;
                currentIndex.value = null;
            }
        }
    }
};
</script>
<template>
    <LayoutAuthenticated>
        <Head :title="props.resourceNeo.resourceTitle" />
        <SectionMain>
            <SectionTitleLineWithButton
                :icon="props.resourceNeo.iconPath"
                :title="props.resourceNeo.resourceTitle"
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
                            :label="'List ' + props.resourceNeo.resourceTitle"
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
                <CardBox>
                    <div
                        class="grid grid-cols-1 gap-6"
                        :class="[
                            'lg:grid-cols-' + (props.resourceNeo.fColumn ?? 2),
                        ]"
                    >
                        <FormField
                            v-for="(formField, key) in props.resourceNeo
                                .formInfo"
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
                    <h1 class="font-bold text-xl border-b-2 mb-2 mt-2">
                        {{ props.resourceNeo.Multilabel }}
                    </h1>
                    <div
                        class="grid grid-cols-1 gap-2 border-b mb-2 pb-2"
                        v-for="(multiData, pkey) in multiDatas"
                        :class="[
                            'lg:grid-cols-' +
                                (props.resourceNeo.fColumnMulti
                                    ? props.resourceNeo.fColumnMulti
                                    : props.resourceNeo.fColumn ?? 2),
                        ]"
                    >
                        <FormField
                            v-for="(formField, key) in multiData"
                            :label="formField.label"
                            :help="formField.tooltip"
                            :error="form.errors['multi.' + pkey + '.' + key]"
                            class="!mb-0"
                            :class="['lg:col-span-' + formField.colspan ?? 1]"
                        >
                            <div class="flex">
                                <FormFieldsMulti
                                    :form-field="formField"
                                    :multiDatasModel="form['multi']"
                                    :form="form"
                                    :fkey="key"
                                    :pkey="pkey"
                                    :onChangeFunc="onChangeFunc"
                                ></FormFieldsMulti>
                                <div class="ml-2" v-if="key == 'out_product'">
                                    <!--<BaseButton
                                        :icon="mdiBarcode"
                                        label="Scan Barcode"
                                        color="success"
                                        class="mr-2"
                                        @click="openscanner(pkey, 'barcode')"
                                    />-->
                                    <BaseButton
                                        :icon="mdiQrcode"
                                        label="Scan QR"
                                        color="info"
                                        @click="openscanner(pkey, 'qrcode')"
                                    />
                                </div>
                            </div>
                        </FormField>
                        <div
                            class="col-span-1 pt-9 text-right"
                            v-if="
                                props.resourceNeo.AllowDel &&
                                multiDatas.length > 1
                            "
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill=" currentColor"
                                    @click="delMulti(pkey)"
                                    class="cursor-pointer"
                                    d="M9,3V4H4V6H5V19A2,2 0 0,0 7,21H17A2,2 0 0,0 19,19V6H20V4H15V3H9M9,8H11V17H9V8M13,8H15V17H13V8Z"
                                ></path>
                            </svg>
                        </div>
                    </div>
                    <div
                        class="mt-4 float-right"
                        v-if="!props.formdata.id || props.resourceNeo.AllowMore"
                    >
                        <BaseButton
                            label="+ Add More"
                            color="success"
                            @click="addMoreMulti"
                        />
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

        <CardBoxModal
            v-model="isModalActive"
            :title="scanMode === 'barcode' ? 'Scan Barcode' : 'Scan QR Code'"
            button="info"
            has-cancel
            @cancel="resetIndex"
            @confirm="resetIndex"
        >
            <div class="w-full">
                <BarcodeScanner
                    @detected="onBarcodeDetected"
                    :target="'#scanner-container'"
                    :mode="scanMode"
                    :currentIndex="currentIndex"
                />
                <div
                    id="scanner-container"
                    class="w-full h-64 bg-gray-100"
                ></div>
            </div>
        </CardBoxModal>
    </LayoutAuthenticated>
</template>

<style scoped>
#scanner-container {
    position: relative;
    overflow: hidden;
}
</style>
