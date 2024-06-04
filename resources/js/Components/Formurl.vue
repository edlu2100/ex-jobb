<template>
    <form @submit.prevent="submitForm" class="ml-10">
      <input type="text" v-model="url" placeholder="Enter URL to scan" required/>
      <button type="submit" class="bg-red-600 p-2 rounded ml-2">Scan URL</button>
    </form>
  </template>

  <script setup>
    import { ref } from 'vue';
    import { Inertia } from '@inertiajs/inertia';

    const url = ref('');

    const submitForm = () => {
        Inertia.post(route('scan-url'), { url: url.value }); // Ändrat till string från array
    };
  </script>
