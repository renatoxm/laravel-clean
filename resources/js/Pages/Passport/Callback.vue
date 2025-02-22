<template>
    <div class="p-6 max-2/3 mx-auto bg-white shadow-md rounded-lg">
        <h2 class="text-xl font-semibold mb-4">Authorize Token</h2>
        <div v-if="isSubmitting" class="text-gray-600">Processing Callback...</div>
        <div v-else>
            <div v-if="response">
                <p class="text-green-600">Callback processed successfully.</p>
                <p class="mb-5">Token information</p>
                <pre class="bg-gray-100 p-4 rounded-md text-sm inline-flex items-baseline">{{ response }}</pre>
            </div>
            <div v-else>
                <p class="text-red-600">{{err}}</p>
                <p>{{ err.response?.data || err.message}}</p>
            </div>

        </div>
    </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();

const isSubmitting = ref(false);
const response = ref(false);
const err = ref(false);

onMounted(async () => {
    isSubmitting.value = true;
    try {
        response.value = await axios.post(page.props.url, page.props.data);
    } catch (error) {
        err.value = error;
        console.error('Error posting data:', error.response?.data || error.message);
    } finally {
        isSubmitting.value = false;
    }
});
</script>
