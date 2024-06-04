<script setup>
import { Link } from "@inertiajs/vue3";

</script>

<template>
    <tr>
        <td class="px-4 py-2 sm:px-2 sm:py-1">{{ website.URL }}</td>
        <td class="px-4 py-2 sm:px-2 sm:py-1">
            {{ website.company.Company_name }}
        </td>
        <td class="px-4 py-2 sm:px-2 sm:py-1 text-center">
            {{ website.Scan ? "Yes" : "No" }}
        </td>
        <td class="px-4 py-2 sm:px-2 sm:py-1 text-center">
            {{ website.DNS ? "Yes" : "No" }}
        </td>
        <td class="px-4 py-2 sm:px-2 sm:py-1 text-center">
            {{ website.SSL ? "Yes" : "No" }}
        </td>
        <td class="px-4 py-2 sm:px-2 sm:py-1 flex items-center justify-center gap-3 mr-4">
            <Link :href="route('EditWebsite', { id: website.id, company_name: website.company.Company_name })" class="flex row hover:underline hover:opacity-55">
                <img
                    src="../Images/edit.svg"
                    class="w-5 mr-1"
                    alt="Edit Icon"
                />
                <p>Edit</p>
            </Link>
            <button
                @click="confirmDelete"
                class="text-red-700 hover:underline flex items-center ml-4 hover:opacity-55"
            >
                <img
                    src="../Images/delete.svg"
                    class="w-5 mr-1"
                    alt="Delete Icon"
                />
                <p>Delete</p>
            </button>
        </td>
    </tr>
</template>

<script>
import { Inertia } from "@inertiajs/inertia";

export default {
    props: {
        key: Number,
        SSL: Object,
        Scan: Object,
        DNS: Object,
        URL: Object,
        id: Object,
        company_id: Object,
        website: Object,
        Company_name: Object
    },
    methods: {
        confirmDelete() {
            if (
                confirm(
                    "Are you sure you want to delete this website with url: " +
                        this.website.URL + "?"
                )
            ) {
                this.deleteWebsite(this.website.id);
            }
        },
        deleteWebsite(id) {
            Inertia.post(route("deleteWebsite", { id }))
                .then(() => {
                    console.log("Website deleted Successfully");
                })
                .catch((error) => {
                    console.error("Error deleting website:", error);
                });
        },
    },
    data() {
        return {
            id: null,
        };
    },
    mounted() {
        this.id = this.website.id;
    },
};
</script>
