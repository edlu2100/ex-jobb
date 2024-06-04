<template>
    <div class="p-6 bg-white border-b border-gray-200 max-w-md mx-auto">
        <h1 class="text-2xl font-bold mb-4">Add Website</h1>
        <form
            @submit.prevent="submitForm"
            class="space-y-4"
        >
            <!-- Field for company name -->
            <div >
                <label
                    for="Company_name"
                    class="block font-medium text-sm text-gray-700"
                    >Company Name</label
                >
                <input
                    type="text"
                    v-model="Company_name"
                    id="Company_name"
                    placeholder="Enter company name"
                    required
                    class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block w-full"
                />
            </div>

            <!-- Field for website URL -->
            <div >
                <label
                    for="URL"
                    class="block font-medium text-sm text-gray-700"
                    >Website URL</label
                >
                <input
                    type="text"
                    v-model="URL"
                    id="URL"
                    placeholder="Enter website URL"
                    required
                    class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block w-full"
                />
            </div>

            <!-- Checkbox for Scan with PHP comment -->
            <div class="mb-4" >
                <!-- Check this box to enable Link-Scan (checked by default) -->
                <input
                    type="checkbox"
                    v-model="Scan"
                    id="Scan"
                    class="mr-2"
                    checked
                />
                <label for="Scan" class="text-gray-700"
                    >Perform Link-Scan</label
                >
            </div>

            <!-- Checkbox for DNS scan with PHP comment -->
            <div class="mb-4">
                <!-- Check this box to enable DNS-Check (checked by default) -->
                <input
                    type="checkbox"
                    v-model="DNS"
                    id="DNS"
                    class="mr-2"
                    checked
                />
                <label for="DNS" class="text-gray-700">Perform DNS-Check</label>
            </div>

            <!-- Checkbox for SSL with PHP comment -->
            <div class="mb-4">
                <!-- Check this box to enable SSL-Check (checked by default) -->
                <input
                    type="checkbox"
                    v-model="SSL"
                    id="SSL"
                    class="mr-2"
                    checked
                />
                <label for="SSL" class="text-gray-700">Perform SSL-Check</label>
            </div>

            <!-- Button to add website -->
            <div class="flex justify-end mt-4">
                <button
                    type="submit"
                    class="bg-blue-500 mt-4 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline m-auto w-40"
                >
                    Add Website
                </button>
            </div>

        </form>
    </div>
</template>

<script>
import { ref } from "vue";
import { Inertia } from "@inertiajs/inertia";

export default {
    data() {
        return {
            errorMessage: "",
        };
    },
    setup() {
        const Company_name = ref("");
        const URL = ref("");
        const Scan = ref(true); // Checkbox for Scan, pre-filled by default
        const DNS = ref(true); // Checkbox for DNS scan, pre-filled by default
        const SSL = ref(true); // Checkbox for SSL, pre-filled by default
        const errorMessage = ref("");

        const submitForm = () => {
            try {
                // Send a POST request to create both company and website
                Inertia.post(route("createCompany"), {
                    Company_name: Company_name.value,
                    URL: URL.value,
                    Scan: Scan.value,
                    DNS: DNS.value,
                    SSL: SSL.value,
                });
            } catch (error) {
                Company_name.value = "";
                URL.value = "";
                Scan.value = true;
                DNS.value = true;
                SSL.value = true;

                if (error.response && error.response.data.error) {
                    errorMessage.value = error.response.data.error;
                } else {
                    errorMessage.value = {
                        message: "An error occurred. Please try again later.",
                        comment: "",
                    };
                }
            }
        };

        return { Company_name, URL, Scan, DNS, SSL, errorMessage, submitForm };
    },
};
</script>
