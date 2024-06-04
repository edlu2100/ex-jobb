<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import Websites from "@/Components/Websites.vue";
import { ref, onMounted, watch } from "vue";
import SSLTest from "@/Components/SSLTest.vue";

const websites = ref([]);
const searchQuery = ref(""); // Define a ref to store the search query
const searchResults = ref([]); // Define a ref to store the search results

// Method to fetch websites data and search based on query
const fetchAndSearchWebsites = async () => {
    try {
        const response = await fetch("/websites");
        const data = await response.json();
        websites.value = data;

        // Search websites if search query is not empty
        if (searchQuery.value !== "") {
            searchResults.value = websites.value.filter((website) => {
                return (
                    website.company.Company_name.toLowerCase().includes(
                        searchQuery.value.toLowerCase()
                    ) ||
                    website.URL.toLowerCase().includes(
                        searchQuery.value.toLowerCase()
                    )
                );
            });
        } else {
            // If search query is empty, display all websites
            searchResults.value = websites.value;
        }
    } catch (error) {
        console.error("Error fetching data:", error);
    }
};

// Fetch websites data on component mount
onMounted(() => {
    fetchAndSearchWebsites();
});
// Watch for changes in searchQuery and update searchResults accordingly
watch(searchQuery, () => {
    fetchAndSearchWebsites();
});
</script>

<template>
    <div>
        <Head title="Dashboard" />

        <AuthenticatedLayout>
            <template #header>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Dashboard
                </h2>
            </template>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6">
                            Welcome Back, {{ $page.props.auth.user.name }}!
                        </div>
                    </div>
                    <div>
                        <form id="form" class="flex flex-col items-end mt-10">
                            <!-- Search input -->

                            <input
                                id="Search"
                                type="text"
                                v-model="searchQuery"
                                placeholder="Search by Company Name or URL..."
                                class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mb-3 w-full md:max-w-xs"
                            />
                        </form>
                    </div>
                    <!-- Websites -->
                    <div class="w-full overflow-x-auto m-auto p-4 md:p-0">
                        <table
                            class="border-collapse border border-gray-200 w-full"
                        >
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-4 py-2 text-left">URL</th>
                                    <th class="px-4 py-2 text-left">
                                        Company Name
                                    </th>
                                    <th class="px-4 py-2">Scan</th>
                                    <th class="px-4 py-2">DNS</th>
                                    <th class="px-4 py-2">SSL</th>
                                    <th class="px-4 py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <Websites
                                    v-for="website in searchResults"
                                    :website="website"
                                    :key="website.id"
                                />
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </AuthenticatedLayout>
    </div>
</template>
