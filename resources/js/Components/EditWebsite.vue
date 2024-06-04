<template>
    <div class="container mx-auto">
      <h1 class="text-2xl font-bold mb-4">Edit Website</h1>
      <!-- Form for editing website -->
      <form @submit.prevent="saveWebsite" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <div class="mb-4">
          <label for="URL" class="block text-gray-700 text-sm font-bold mb-2">URL</label>
          <input type="text" v-model="website.URL" id="URL" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="mb-4">
          <label for="Company_name" class="block text-gray-700 text-sm font-bold mb-2">Company Name</label>
          <input type="text" v-model="website.company.Company_name" id="Company_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="mb-4">
          <input type="checkbox" v-model="website.Scan" id="Scan" class="mr-2">
          <label for="Scan" class="text-gray-700">Perform Link-Scan</label>
        </div>
        <div class="mb-4">
          <input type="checkbox" v-model="website.DNS" id="DNS" class="mr-2">
          <label for="DNS" class="text-gray-700">Perform DNS-Check</label>
        </div>
        <div class="mb-4">
          <input type="checkbox" v-model="website.SSL" id="SSL" class="mr-2">
          <label for="SSL" class="text-gray-700">Perform SSL-Check</label>
        </div>
        <div class="flex items-center justify-between">
          <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Save</button>
        </div>
      </form>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        website: null
      };
    },
    mounted() {
      const websiteId = this.$route.params.id;
      fetch(`/websites/${websiteId}`)
        .then(response => response.json())
        .then(data => {
          this.website = data;
        })
        .catch(error => console.error('Error fetching website data:', error));
    },
    methods: {
      saveWebsite() {
        fetch(`/websites/${this.website.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(this.website)
        })
        .then(response => response.json())
        .then(data => {
          console.log('Website updated successfully:', data);
          this.$router.push({ name: 'allWebsites' });
        })
        .catch(error => console.error('Error updating website:', error));
      }
    }
  };
  </script>
  