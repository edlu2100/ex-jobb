<template>
        <div class="container mx-auto">
        <h1 class="text-2xl font-bold mb-4">All Websites</h1>
        <div class="overflow-x-auto">
            <table class="table-auto min-w-full border-collapse border border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">URL</th>
                        <th class="px-4 py-2 text-left">Company Name</th>
                        <th class="px-4 py-2">Scan</th>
                        <th class="px-4 py-2">DNS</th>
                        <th class="px-4 py-2">SSL</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="website in websites" :key="website.id" class="border-b border-gray-200">
                        <td class="px-4 py-2 sm:px-2 sm:py-1">{{ website.URL }}</td>
                        <td class="px-4 py-2 sm:px-2 sm:py-1" contenteditable="">{{ website.company.Company_name }} {{ website.id }}</td>
                        <td class="px-4 py-2 sm:px-2 sm:py-1 text-center">{{ website.Scan ? "Yes" : "No" }}</td>
                        <td class="px-4 py-2 sm:px-2 sm:py-1 text-center">{{ website.DNS ? "Yes" : "No" }}</td>
                        <td class="px-4 py-2 sm:px-2 sm:py-1 text-center">{{ website.SSL ? "Yes" : "No" }}</td>
                        <td class="px-4 py-2 sm:px-2 sm:py-1 flex items-center justify-center">
                            <router-link :to="{ name: 'EditWebsite', params: { id: website.id } }" class="text-blue-500 hover:underline flex items-center">
                                <img src="../Images/edit.svg" class="w-5 mr-1" alt="Edit Icon" />
                                <p>Edit</p>
                            </router-link>
                            <button @click="deleteWebsite(website.id)" class="text-red-500 hover:underline flex items-center ml-4">
                                <img src="../Images/delete.svg" class="w-5 mr-1" alt="Delete Icon" />
                                <p>Delete</p>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import { Inertia } from '@inertiajs/inertia';
export default {
    methods: {
        testWebsites() {
            // Skicka en postförfrågan för att starta bearbetningen av webbplatser
            Inertia.post(route('processWebsites'))
            .then(response => {
                console.log('Processing websites');
            })
            .catch(error => {
                console.error('Error processing websites:', error);
            });
        }
    }
};</script>