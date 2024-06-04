<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import AddWebsite from "@/Components/AddWebsite.vue";

</script>

<template>
    <div>
        <Head title="AddWebsite" />

        <AuthenticatedLayout>
            <template #header>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Add Website
                </h2>
            </template>
            <div class="py-12 m-4 rounded">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <AddWebsite />

                </div>
            </div>
        </AuthenticatedLayout>
    </div>
</template>
<script>
import { Inertia } from "@inertiajs/inertia";
export default {
    methods: {
        testWebsites() {
            // Skicka en postförfrågan för att starta bearbetningen av webbplatser
            Inertia.post(route("processWebsites"))
                .then((response) => {
                    console.log("Processing websites");
                })
                .catch((error) => {
                    console.error("Error processing websites:", error);
                });
        },
    },
};
</script>
