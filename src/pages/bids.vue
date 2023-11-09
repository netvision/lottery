<script setup>
import { useRoute } from 'vue-router'

const route = useRoute()
const bids = route.params.id
</script>

<template>
  <div>{{ bids }}</div>
</template>

<route lang="json">
  {
    "params": true,
    "meta" : {
      "requireAuth" : true
    }
  }
</route>
