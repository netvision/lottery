<!-- eslint-disable no-unmodified-loop-condition -->
<!-- eslint-disable no-restricted-globals -->
<!-- eslint-disable prefer-const -->
<!-- eslint-disable no-console -->
<!-- eslint-disable antfu/top-level-function -->
<script setup>
import VueDatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import axios from 'axios'

const date = ref(new Date())

const results = ref([])
const slots = ref([])

const timeArray = () => {
  const startTime = new Date()
  startTime.setHours(8, 0, 0, 0) // Set start time to 8:00 AM

  const endTime = new Date()
  endTime.setHours(20, 0, 0, 0) // Set end time to 8:00 PM

  const timeArray = []
  let currentTime = new Date(startTime)
  let n = 1
  while (currentTime <= endTime) {
    const hours = currentTime.getHours().toString().padStart(2, '0')
    const minutes = currentTime.getMinutes().toString().padStart(2, '0')
    const seconds = currentTime.getSeconds().toString().padStart(2, '0')
    const timeString = `${hours}:${minutes}:${seconds}`
    timeArray.push({ result_time: timeString, id: n })
    currentTime.setMinutes(currentTime.getMinutes() + 20)
    n = n + 1
  }
  return timeArray
}

console.log(timeArray())

function formatTime(mysqlTime) {
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

const isValidValue = value => value != null && value !== '' && !isNaN(value) && value >= 0 && value <= 99

const timeDiff = (rTime) => {
  const hour = rTime.split(':')[0]
  const min = rTime.split(':')[1]
  const refDate = date.value
  refDate.setHours(parseInt(hour, 10))
  refDate.setMinutes(parseInt(min, 10))
  refDate.setSeconds(0)
  const curDate = new Date()
  const diff = curDate.getTime() - refDate.getTime()
  return diff
}

const autoSave = (slot) => {
  if (isValidValue(slot.winning_no)) {
    let curDate = new Date()
    let dt = `${curDate.getFullYear()}-${(curDate.getMonth() + 1).toString().padStart(2, '0')}-${curDate.getDate().toString().padStart(2, '0')}`
    console.log(dt)
    let newRes = {
      date: dt,
      time: slot.result_time,
      slot_id: slot.id,
      winning_no: slot.winning_no,
    }
    if (slot.res_id) {
      axios.put(`https://lotteryapi.netserve.in/results/${slot.res_id}`, newRes).then((r) => {
        if (r.status === 200) {
          slot.status = 'success'
          slot.message = 'Saved successfully'
        }
        else {
          slot.status = 'error'
          slot.message = 'Couldn\'t save! Please try again'
        }
      })
    }
    else {
      axios.post('https://lotteryapi.netserve.in/results', newRes).then((r) => {
        if (r.status === 201) {
          slot.status = 'success'
          slot.message = 'Saved successfully'
        }
        else {
          slot.status = 'error'
          slot.message = 'Couldn\'t save! Please try again'
        }
      })
    }
  }

  else {
    slot.status = 'error'
    slot.message = 'only number between 0 to 99 is allowed'
  }
}

const getDayData = async () => {
  // eslint-disable-next-line prefer-const
  let dt = `${date.value.getFullYear()}-${date.value.getMonth() + 1}-${date.value.getDate().toString().padStart(2, '0')}`
  results.value = await axios.get(`https://lotteryapi.netserve.in/results?filter[date][eq]=${dt}`).then(r => r.data)
  console.log(results.value?.filter(rs => rs.time === '09:00:00')[0])
  slots.value.map((sl) => {
    sl.winning_no = results.value?.filter(rs => rs.time === sl.result_time)[0]?.winning_no ?? null
    sl.res_id = results.value?.filter(rs => rs.time === sl.result_time)[0]?.id ?? null
    sl.canEdit = (timeDiff(sl.result_time) < 0 && timeDiff(sl.result_time) > -(20 * 60 * 1000)) ? 'enabled' : 'disabled'
    return sl
  })
  console.log(slots.value)
}

const intervals = ref([])

const schedules = () => {
  slots.value.forEach((slot) => {
    const hours = slot.result_time.split(':')[0]
    const min = slot.result_time.split(':')[1]
    const scheduleTime = new Date()
    scheduleTime.setHours(parseInt(hours, 10))
    scheduleTime.setMinutes(parseInt(min, 10))
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
  // slots.value = await axios.get('https://lotteryapi.netserve.in/slots').then(r => r.data)
  slots.value = timeArray()
  slots.value.push({ result_time: '22:00:00', id: 38 })
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
          <div class="relative py-3">
            <div class="pointer-events-none absolute start-0 inset-y-0 flex items-center ps-3">
              Slot {{ slot.id }} <br>
              {{ formatTime(slot.result_time) }}
            </div>
            <div v-if="slot.canEdit === 'enabled'">
              <input v-model="slot.winning_no" class="block w-3/4 border border-gray-300 rounded-lg bg-gray-50 p-4 ps-24 text-sm text-gray-900 dark:border-gray-600 focus:border-blue-500 dark:bg-gray-700 dark:text-white focus:ring-blue-500 dark:focus:border-blue-500 dark:focus:ring-blue-500 dark:placeholder-gray-400" placeholder="update winning no." @blur="autoSave(slot)">
              <p v-if="slot.status === 'error'" class="mt-2 text-sm text-red-600 dark:text-red-500">
                <span class="font-medium">Oh, snapp!</span> {{ slot.message }}
              </p>
              <p v-else-if="slot.status === 'success'" class="mt-2 text-sm text-green-600 dark:text-green-500">
                {{ slot.message }}
              </p>
            </div>
            <div v-else>
              <p class="block w-3/4 border border-gray-300 rounded-lg bg-gray-50 p-4 ps-24 text-sm text-gray-900 dark:border-gray-600 focus:border-blue-500 dark:bg-gray-700 dark:text-white focus:ring-blue-500 dark:focus:border-blue-500 dark:focus:ring-blue-500 dark:placeholder-gray-400">
                {{ slot.winning_no?.toString().padStart(2, '0') ?? 'Wait...' }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<route lang="json">
  {
    "meta" : {
      "requireAuth" : true
    }
  }
</route>
