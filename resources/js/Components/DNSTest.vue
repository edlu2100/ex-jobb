<template>
    <div>
      <form @submit.prevent="submitDomain" class="mb-10">

        <button type="submit">Check DNS</button>
      </form>
    </div>
  </template>

  <script setup>
  import { ref } from 'vue';
  import { Inertia } from '@inertiajs/inertia';

  const domain = ref('');

  const submitDomain = () => {
      Inertia.post(route('dns_scan'), { domain: domain.value });

  };
  </script>
