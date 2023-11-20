<!-- eslint-disable prefer-const -->
<!-- eslint-disable no-console -->
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
const ifFuture = (rtime) => {
  let dt = `${date.value.getFullYear()}-${(date.value.getMonth() + 1).toString().padStart(2, '0')}-${date.value.getDate().toString().padStart(2, '0')}`
  return moment().diff(moment(`${dt}T${rtime}`), 'minute') > 0
}

const getDayData = async () => {
  let dt = `${date.value.getFullYear()}-${(date.value.getMonth() + 1).toString().padStart(2, '0')}-${date.value.getDate().toString().padStart(2, '0')}`

  results.value = await axios.get(`https://lotteryapi.netserve.in/results?filter[date][eq]=${dt}`).then(r => r.data)
  slots.value
    .map((sl) => {
      sl.winning_no = results.value?.filter(rs => rs.slot_id === sl.id)[0]?.winning_no ?? 'NA'
      return sl
    })
}

const intervals = ref([])

const schedules = () => {
  slots.value.forEach((slot) => {
    const hours = slot.result_time.split(':')[0]
    const scheduleTime = new Date()
    // eslint-disable-next-line no-restricted-globals
    scheduleTime.setHours(parseInt(hours, 10))
    scheduleTime.setMinutes(0)
    scheduleTime.setSeconds(15)
    const currentTime = new Date()
    const timeDiff = scheduleTime.getTime() - currentTime.getTime()
    if (timeDiff > 0) {
      const timeOutId = setTimeout(async () => {
        await getDayData()
      }, timeDiff)
      intervals.value.push(timeOutId)
    }
  })
}

onMounted(async () => {
  slots.value = await axios.get('https://lotteryapi.netserve.in/slots').then(r => r.data)
  await getDayData()
  schedules()
})

onBeforeUnmount(() => {
  intervals.value.forEach((id) => {
    clearInterval(id)
    clearTimeout(id)
  })
})
</script>

<template class="min-h-screen">
  <NtpClock />
  <div class="grid grid-cols-1 md:grid-cols-4">
    <div class="bg-gray-100 p-4 md:col-span-1">
      <VueDatePicker v-model="date" auto-apply inline :max-date="new Date()" :enable-time-picker="false" format="dd-MM-yyyy" @update:model-value="getDayData" />
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
          <div v-if="ifFuture(slot.result_time)" class="relative py-3">
            <div class="pointer-events-none absolute start-0 inset-y-0 flex items-center ps-3">
              Slot {{ slot.title }}
              <br>
              {{ formatTime(slot.result_time) }}
            </div>
            <div class="block w-3/4 border border-gray-300 rounded-lg bg-gray-50 p-4 ps-24 text-2xl text-gray-900 dark:border-gray-600 focus:border-blue-500 dark:bg-gray-700 dark:text-white focus:ring-blue-500 dark:focus:border-blue-500 dark:focus:ring-blue-500 dark:placeholder-gray-400">
              <span class="m-2 border border-blue-200 bg-green-400 p-2 text-red-600">{{ slot.winning_no.toString().padStart(2, '0') }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
