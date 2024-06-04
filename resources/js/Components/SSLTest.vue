<template>
    <div class="ml-10">
        <h1>Check SSL Certificate</h1>
        <form @submit.prevent="checkSSL" c>
            <label for="url">Enter URL:</label><br />
            <input type="text" id="url" v-model="url" /><br /><br />
            <button type="submit">Check SSL</button>
        </form>
<form @submit.prevent="scan" class="mt-10">
    <button>test scan</button>
</form>
</div>
</template>

<script setup>
import { ref } from "vue";
import { Inertia } from "@inertiajs/inertia";

const domain = ref("");

const checkSSL = () => {
    Inertia.post(route("getWebsites"), { url: url.value });
};
const scan = () => {
    Inertia.post(route("processWebsites"), { url: url.value });
};
</script>
