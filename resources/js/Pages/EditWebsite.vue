<script setup>
import { ref, watch } from "vue";
import { usePage, useForm } from "@inertiajs/vue3";
import { Inertia } from "@inertiajs/inertia";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { onMounted } from "vue";

const url = ref("");
const companyName = ref("");
const scan = ref(false);
const dns = ref(false);
const ssl = ref(false);
const id = ref("");

const { website, company_name } = usePage().props;
url.value = website.URL;
scan.value = website.Scan === true;
dns.value = website.DNS === true;
ssl.value = website.SSL === true;
companyName.value = company_name;

const form = useForm({
    url: url.value,
    Company_name: companyName.value,
    scan: scan.value,
    dns: dns.value,
    ssl: ssl.value,
});

const updateWebsite = () => {
    const formData = {
        URL: url.value,
        Company_name: companyName.value,
        Scan: scan.value,
        DNS: dns.value,
        SSL: ssl.value,
    };
    console.log(formData);
    Inertia.post(route("updateWebsite", { id: website.id }), formData)
        .then(() => {
            // Handle successful update
            console.log("Website updated successfully");
        })
        .catch((error) => {
            // Handle error
            console.error("Error updating website:", error);
        });
};

// Uppdatera formuläret när companyName.value ändras
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Website
            </h2>
        </template>
        <div class="py-12 m-4 rounded">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="p-6 bg-white border-b border-gray-200 max-w-md mx-auto"
                >
                    <h1 class="text-2xl font-bold mb-4">Edit Website</h1>
                    <form @submit.prevent="updateWebsite" class="space-y-4">
                        <div>
                            <label
                                for="companyName"
                                class="block font-medium text-sm text-gray-700"
                                >Company Name:</label
                            >
                            <input
                                type="text"
                                id="companyName"
                                v-model="companyName"
                                class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block w-full"
                                required
                            />
                        </div>
                        <div>
                            <label
                                for="url"
                                class="block font-medium text-sm text-gray-700"
                                >URL:</label
                            >
                            <input
                                type="text"
                                id="url"
                                v-model="url"
                                class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block w-full"
                                required
                            />
                        </div>

                        <div class="mb-4">
                            <input
                                type="checkbox"
                                id="scan"
                                v-model="scan"
                                class="mr-2"
                            />
                            <label for="scan" class="text-gray-700"
                                >Perform Link-Scan:</label
                            >
                        </div>
                        <div class="mb-4">
                            <input
                                type="checkbox"
                                id="dns"
                                v-model="dns"
                                class="mr-2"
                            />
                            <label for="dns" class="text-gray-700"
                                >Perform DNS-Check</label
                            >
                        </div>

                        <div class="mb-4">
                            <!-- Check this box to enable SSL-Check (checked by default) -->
                            <input
                                type="checkbox"
                                v-model="ssl"
                                id="ssl"
                                class="mr-2"
                            />
                            <label for="ssl" class="text-gray-700"
                                >Perform SSL-Check</label
                            >
                        </div>
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                class="bg-blue-500 mt-4 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline m-auto w-40"
                            >
                                Update Website
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
