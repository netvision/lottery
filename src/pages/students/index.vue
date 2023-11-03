<script setup>
import axios from 'axios'

const batches = ref([])
const students = ref([])

async function getStudents(batchId) {
  students.value = await axios.get(`https://collegeapi.netserve.in/student-batch?filter[batch_id][eq]=${batchId}&expand=student`).then(r => r.data)
}

onMounted(async () => {
  batches.value = await axios.get('https://collegeapi.netserve.in/batch?expand=course,part').then(r => r.data)
  getStudents(1)
})
</script>

<template>
  <div class="flex">
    <!-- First Column -->
    <div class="w-1/4 p-4">
      <ul>
        <li v-for="batch in batches" :key="batch.id" class="hover:cursor-pointer hover:bg-blue-200" @click="getStudents(batch.id)">
          {{ batch.course.short_name }} {{ batch.part.title }}
        </li>
      </ul>
    </div>

    <!-- Second Column -->
    <div class="w-3/4 p-4">
      <ul>
        <li v-for="s in students" :key="s.id">
          <a :href="`/students/${s.student.id}`">{{ s.student.full_name }}</a>
        </li>
      </ul>
    </div>
  </div>
</template>
