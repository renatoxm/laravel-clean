<template>
    <Head title="Authorization Request" />
    <div class="flex items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-900">
      <div class="bg-white shadow-lg rounded-lg p-8 max-w-lg w-full">
        <div class="w-16 text-center m-auto mb-5"><ApplicationMark /></div>
        <h1 class="text-2xl font-bold text-center text-gray-800">Authorization Request</h1>

        <p class="mt-4 text-center text-gray-600">
          <strong class="text-gray-900">{{ props.client.name }}</strong> is requesting permission to access your account.
        </p>

        <div v-if="props.scopes.length" class="mt-4">
          <p class="text-gray-700 font-semibold">This application will be able to:</p>
          <ul class="list-disc list-inside mt-2 text-gray-600">
            <li v-for="scope in props.scopes" :key="scope.description">{{ scope.description }}</li>
          </ul>
        </div>

        <!-- Buttons -->
        <div class="mt-6 flex space-x-4 justify-center">
          <form method="post" :action="props.route.approve">
            <input type="hidden" name="_token" :value="props.csrfToken" />
            <input type="hidden" name="state" :value="props.request.state" />
            <input type="hidden" name="client_id" :value="props.client.id" />
            <input type="hidden" name="auth_token" :value="props.authToken" />
            <PrimaryButton type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">Authorize</PrimaryButton>
          </form>

          <form method="post" :action="props.route.deny">
            <input type="hidden" name="_method" value="DELETE" />
            <input type="hidden" name="_token" :value="props.csrfToken" />
            <input type="hidden" name="state" :value="props.request.state" />
            <input type="hidden" name="client_id" :value="props.client.id" />
            <input type="hidden" name="auth_token" :value="props.authToken" />
            <PrimaryButton type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">Cancel</PrimaryButton>
          </form>
        </div>
      </div>
    </div>
  </template>
<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import { Head } from '@inertiajs/vue3';
// idea from: https://lxc.no/blog/laravel-passport-authorization-with-inertia-js/

interface Client {
  id: number;
  name: string;
}

interface Scope {
  description: string;
}

interface PageProps {
  client: Client;
  scopes: Scope[];
  route: {
    approve: string;
    deny: string;
  };
  csrfToken: string;
  request: {
    state: string;
  };
  authToken: string;
}

// Cast props to correct type
const props = computed(() => usePage().props as unknown as PageProps);
</script>
