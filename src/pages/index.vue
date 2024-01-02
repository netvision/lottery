<!-- eslint-disable no-unmodified-loop-condition -->
<!-- eslint-disable prefer-const -->
<!-- eslint-disable no-console -->
<!-- eslint-disable vue/html-self-closing -->
<!-- eslint-disable antfu/top-level-function -->
<script setup>
import moment from 'moment'
import VueDatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import axios from 'axios'
import logo from '~/assets/laxmiji.gif'

const date = ref(new Date())
const results = ref([])
const slots = ref([])
const upcoming = ref({})
const upTime = ref()

const formatTime = (mysqlTime, userLocale = navigator.language || 'en-US') => moment(mysqlTime, 'HH:mm:ss').locale(userLocale).format('LT')
const ifFuture = (rtime) => {
  let dt = `${date.value.getFullYear()}-${(date.value.getMonth() + 1).toString().padStart(2, '0')}-${date.value.getDate().toString().padStart(2, '0')}`
  return Number.parseInt(moment().diff(moment(`${dt}T${rtime}`), 'second'))
}

const timeArray = () => {
  const startTime = new Date()
  startTime.setHours(8, 0, 0, 0) // Set start time to 8:00 AM

  const endTime = new Date()
  endTime.setHours(20, 0, 0, 0) // Set end time to 8:00 PM

  const timeArray = []
  let currentTime = new Date(startTime)
  let n = 1
  while (currentTime <= endTime) {
    const formattedTime = currentTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
    timeArray.push({ result_time: formattedTime, id: n })
    currentTime.setMinutes(currentTime.getMinutes() + 20)
    n = n + 1
  }
  return timeArray
}

function mysqlTime(mysqlTime) {
  // Assuming mysqlTime is in the format 'HH:MM:SS'
  const [hours, minutes] = mysqlTime.split(':')

  // Create a Date object with the time values
  const dateObj = new Date()
  dateObj.setHours(hours, minutes, 0, 0)

  // Format the time using toLocaleTimeString
  const formattedTime = dateObj.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: 'numeric',
    hour12: true,
  })

  return formattedTime
}

const getDayData = async () => {
  upcoming.value = slots.value.find(sl => ifFuture(sl.result_time) < 0)
  let dt = `${date.value.getFullYear()}-${(date.value.getMonth() + 1).toString().padStart(2, '0')}-${date.value.getDate().toString().padStart(2, '0')}`
  let curTime = new Date()
  curTime.setHours(0)
  curTime.setMinutes(0)
  curTime.setSeconds(0)
  results.value = await axios.get(`https://lotteryapi.netserve.in/results?filter[date][eq]=${dt}`).then(r => r.data)
  slots.value
    .map((sl) => {
      sl.winning_no = results.value?.filter(rs => rs.time === sl.result_time)[0]?.winning_no ?? 'NA'
      return sl
    })
  console.log(upcoming.value)
}

const intervals = ref([])

const intId = setInterval(() => {
  if (upcoming.value?.result_time) {
    let t = Math.abs(ifFuture(upcoming.value.result_time)) ?? 0
    upTime.value = moment.utc(t * 1000).format('HH:mm:ss')
    if (t > 0)
      t--
    else location.reload()
  }
}, 1000)
/*
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
*/

function isLastDayOfMonth(date) {
  const currentMonth = date.getMonth()
  const nextDay = new Date(date)
  nextDay.setDate(date.getDate() + 1)

  return nextDay.getMonth() !== currentMonth
}

onMounted(async () => {
  // slots.value = await axios.get('https://lotteryapi.netserve.in/slots').then(r => r.data)
  /*
  slots.value = (new Date() > date) ? data.filter(d => ifFuture(d.result_time) > 0) : data
  upcoming.value = data.filter(d => ifFuture(d.result_time) < 0)[0]
  */
  slots.value = timeArray()
  await getDayData()
  // schedules()
})

onBeforeUnmount(() => {
  intervals.value.forEach((id) => {
    clearInterval(id)
    clearTimeout(id)
  })
  clearInterval(intId)
})
</script>

<template class="min-h-screen pb-10">
  <NtpClock />
  <div class="grid grid-cols-1 bg-blue-700 text-slate-50 md:grid-cols-4">
    <div class="p-4 md:col-span-1">
      <VueDatePicker v-model="date" menu-class-name="dp-custom-menu" calendar-class-name="calendar" auto-apply inline :max-date="new Date()" :enable-time-picker="false" format="dd-MM-yyyy" @update:model-value="getDayData" />
    </div>
    <div class="p-4 md:col-span-1">
      <img :src="logo" class="m-auto w-auto justify-center">
    </div>
    <div v-if="date && !isLastDayOfMonth(date)" class="p-4 md:col-span-2">
      <div class="mx-5 text-left">
        {{ date?.toDateString() }}
      </div>
      <div class="mb-12">
        <h1 class="mx-5 mb-6 text-xl underline underline-offset-4 underline-solid">
          Results
        </h1>
        <div v-for="slot in slots" :key="slot.id">
          <div v-if="ifFuture(slot.result_time) > 0" class="relative py-3">
            <div class="pointer-events-none absolute start-0 inset-y-0 flex items-center ps-3 font-bold text-blue-900">
              Slot {{ slot.id }}
              <br>
              {{ mysqlTime(slot.result_time) }}
            </div>
            <div class="block w-3/4 border border-gray-300 rounded-lg bg-gray-50 p-4 ps-24 text-2xl text-gray-900 focus:border-blue-500 focus:ring-blue-500">
              <span class="m-2 block w-20 border rounded-2xl bg-green-400 p-2 text-center font-sans font-bold text-red-600 shadow-md">{{ slot?.winning_no?.toString().padStart(2, '0') }}</span>
            </div>
          </div>
        </div>
        <div v-if="upcoming" class="relative py-3">
          <div class="block w-3/4 border border-gray-300 rounded-lg bg-gray-50 p-4 ps-24 text-2xl text-gray-900 focus:border-blue-500 focus:ring-blue-500">
            <span class="m-2 text-sm">Next result in<br></span><span class="m-2 text-lg">{{ upTime }}</span>
          </div>
        </div>
      </div>
    </div>
    <div v-else>
      <div class="mx-5 text-left">
        {{ date?.toDateString() }}
      </div>
      <h2 class="mx-4 mt-6 text-xl">
        Last day of the Month
      </h2>
    </div>
  </div>
</template>

<style>
.dp-custom-menu {
  box-shadow: 0 0 6px #1976d2;
  background-color: rgb(221, 110, 59);
  font-weight: bold;
}
.calendar{
  font-style: italic;
}
</style>
