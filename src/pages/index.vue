<script setup>
import { TheCard } from 'flowbite-vue'
</script>

<template class="min-h-screen">
  <div class="m-6 flex gap-3 p-6">
    <TheCard href="./students">
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
        Student Information Module
      </h5>
      <p class="font-normal text-gray-700 dark:text-gray-400">
        List
      </p>
    </TheCard>
    <TheCard href="./students">
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
        Student Information Module
      </h5>
      <p class="font-normal text-gray-700 dark:text-gray-400">
        List
      </p>
    </TheCard>
  </div>
</template>

<route lang="json">
  {
    "meta" : {
      "requireAuth" : true
    }
  }
</route>
