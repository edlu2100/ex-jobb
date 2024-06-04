
  <template>
    <div class="max-w-md mx-auto">
      <form @submit.prevent="submitForm" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">

        <div class="mb-4">
          <label for="Company_name" class="block text-gray-700 text-sm font-bold mb-2">Company ID</label>
          <input type="text" v-model="Company_name" placeholder="Company Name" required
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="flex items-center justify-between">
          <button type="submit"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Add Company
          </button>
        </div>
      </form>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  import { Inertia } from '@inertiajs/inertia';
  
  const Company_name = ref('');
  
  const submitForm = () => {
      Inertia.post(route('createCompany'), { Company_name: Company_name.value });
  };
  </script>
  