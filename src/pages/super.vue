<!-- eslint-disable no-console -->
<script setup>
import collegeProtectedAPI from '~/utils/collegeApi'
import { useAuthStore } from '~/stores/authStore'

const authStore = useAuthStore()
const router = useRouter()
const csv = ref()
const ids = ref([])
function logout() {
  authStore.signout()
}
function formatDate(inputDate) {
  const dateParts = inputDate.split('/')
  const day = dateParts[1]
  const month = dateParts[0]
  const year = +dateParts[2] + 2000
  // Create a new Date object using the extracted components
  const dateObj = new Date(year, month - 1, day)

  // Extract the year, month, and day from the date object
  const formattedYear = dateObj.getFullYear()
  const formattedMonth = String(dateObj.getMonth() + 1).padStart(2, '0')
  const formattedDay = String(dateObj.getDate()).padStart(2, '0')

  // Concatenate the components to form the final formatted date string
  const formattedDate = `${formattedYear}-${formattedMonth}-${formattedDay}`

  return formattedDate
}
function handleFileChange(event) {
  console.log(event.target.files[0])
  const reader = new FileReader()
  reader.addEventListener(
    'load',
    () => {
      const data = []
      const lines = reader.result.split(/\r\n|\r|\n/)
      lines.forEach((el) => {
        const cols = el.split(',')
        const row = {
          full_name: cols[0],
          father_name: cols[1],
          current_address: cols[2],
          mobile_no: cols[3],
          whatsapp_no: cols[4],
        }
        data.push(row)
      })
      csv.value = data
    },
    false,
  )

  if (event.target.files[0])
    reader.readAsText(event.target.files[0])
}

function save() {
  const promises = []

  csv.value.forEach((row) => {
    const data = { ...row, sr_no: 'NA', permanent_address: 'NA' }
    console.log(data)
    promises.push(
      collegeProtectedAPI.post('/students', data)
        .then((res) => {
          ids.value.push(res.data.id)
        })
        .catch((err) => {
          console.log(err)
        }),
    )
  })

  Promise.all(promises)
    .then(() => {
      console.log('All requests completed.')
    })
    .catch((error) => {
      console.error('One or more requests failed:', error)
    })
}
</script>

<template class="min-h-screen">
  <header>
    <nav class="flex items-center justify-between bg-white px-20 py-10">
      <h1 class="text-xl font-bold text-gray-800">
        School Management System
      </h1>
      <div class="flex items-center">
        <img :src="authStore.photoURL" class="w-10 rounded-xl"> &nbsp; &nbsp; &nbsp;
        <button class="rounded-md bg-blue-500 px-2 py-1 text-white" @click="logout">
          Logout
        </button>
      </div>
    </nav>
  </header>
  <main>
    <div class="pb-10 text-center">
      <input id="" type="file" name="" @change="handleFileChange($event)">
    </div>
    <div>
      <ol v-if="csv && csv.length > 0" class="list-decimal list-inside">
        <li v-for="(data, i) in csv" :key="i">
          {{ data.full_name }}/ {{ data.father_name }}
        </li>
      </ol>
    </div>
    <div>
      <button class="bg-blue-500 text-white" @click="save">
        Save
      </button>
    </div>
  </main>
</template>

<route lang="json">
  {
    "meta" : {
      "requireAuth" : true
    }
  }
</route>
