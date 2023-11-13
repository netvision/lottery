<!-- eslint-disable vue/html-self-closing -->
<!-- eslint-disable antfu/top-level-function -->
<script setup>
import moment from 'moment'
import VueDatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import axios from 'axios'

const date = ref(new Date())
const results = ref([])
const slots = ref([])

const formatTime = (mysqlTime, userLocale = navigator.language || 'en-US') => moment(mysqlTime, 'HH:mm:ss').locale(userLocale).format('LT')

const getDayData = async () => {
  // eslint-disable-next-line prefer-const
  let dt = `${date.value.getFullYear()}-${date.value.getMonth() + 1}-${date.value.getDate().toString().padStart(2, '0')}`
  results.value = await axios.get(`https://lotteryapi.netserve.in/results?filter[date][eq]=${dt}`).then(r => r.data)
  slots.value.map((sl) => {
    sl.winning_no = results.value?.filter(rs => rs.slot_id === sl.id)[0]?.winning_no ?? 'NA'
    return sl
  })
}

onMounted(async () => {
  slots.value = await axios.get('https://lotteryapi.netserve.in/slots').then(r => r.data)
  await getDayData()
})
</script>

<template class="min-h-screen">
  <NtpClock />
  <div class="grid grid-cols-1 md:grid-cols-4">
    <div class="bg-gray-100 p-4 md:col-span-1">
      <VueDatePicker v-model="date" auto-apply inline :enable-time-picker="false" format="dd-MM-yyyy" @update:model-value="getDayData" />
    </div>
    <div class="bg-blue-50 p-4 md:col-span-3">
      <div class="mx-5 text-left">
        {{ date.toDateString() }}
      </div>
      <div class="mb-12">
        <h1 class="mx-5 mb-6 text-xl underline underline-offset-4 underline-solid">
          Results
        </h1>
        <div v-for="slot in slots" :key="slot.id">
          <div class="relative py-3">
            <div class="pointer-events-none absolute start-0 inset-y-0 flex items-center ps-3">
              Slot {{ slot.title }}
              <br>
              {{ formatTime(slot.result_time) }}
            </div>
            <div class="block w-3/4 border border-gray-300 rounded-lg bg-gray-50 p-4 ps-24 text-2xl text-gray-900 dark:border-gray-600 focus:border-blue-500 dark:bg-gray-700 dark:text-white focus:ring-blue-500 dark:focus:border-blue-500 dark:focus:ring-blue-500 dark:placeholder-gray-400">
              <span class="m-2 border border-blue-200 bg-green-400 p-2 text-red-600">{{ slot.winning_no }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
