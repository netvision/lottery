<script setup>
import axios from 'axios'

const props = defineProps(['id'])
const student = ref({})

onMounted(async () => {
  student.value = await axios.get(`https://collegeapi.netserve.in/students/${props.id}`).then(r => r.data)
})
</script>

<template>
  <div class="flex">
    <!-- First Column -->
    <div class="w-1/4 p-4">
      <p>Left menu</p>
    </div>

    <!-- Second Column -->
    <div class="w-3/4 p-4">
      <pre>{{ student }}</pre>
    </div>
  </div>
</template>
