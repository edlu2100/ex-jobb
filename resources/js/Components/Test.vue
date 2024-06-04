<template>
    <div>
      <form @submit.prevent="testLink">
        <label for="url">URL:</label>
        <input type="text" id="url" v-model="url" required>

        <label for="websiteId">Website ID:</label>
        <input type="number" id="websiteId" v-model="websiteId" placeholder="1" value="1" >

        <button type="submit">Test Website</button>
      </form>
    </div>
  </template>

  <script>
    import { ref } from 'vue';
    import { Inertia } from '@inertiajs/inertia';
    export default {
        setup(){
            const url = ref('');
            const websiteId = ref('');


        const testLink = () => {
            // Send post-request to laravel-route
            Inertia.post(route('processWebsite'), { url: url.value, websiteId: websiteId.value })
            .then(response => {
                console.log('Link testing');
                // Reset input-fields
                url.value = '';
                websiteId.value = '';
            })
            .catch(error => {
                console.error('Error testing link:', error);
            });
        }
        return {
                url,
                websiteId,
                testLink
                }
        }
    };
  </script>
