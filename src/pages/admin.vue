<!-- eslint-disable no-unmodified-loop-condition -->
<!-- eslint-disable no-restricted-globals -->
<!-- eslint-disable prefer-const -->
<!-- eslint-disable no-console -->
<!-- eslint-disable antfu/top-level-function -->
<script setup>
import VueDatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import axios from 'axios'
import protectedAPI from '~/utils/api'

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
      protectedAPI.put(`/results/${slot.res_id}`, newRes).then((r) => {
        console.log(r)
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
      protectedAPI.post('/results', newRes).then((r) => {
        console.log(r)
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
  }

  else {
    slot.status = 'error'
    slot.message = 'only number between 0 to 99 is allowed'
  }
}

const getDayData = async () => {
  // eslint-disable-next-line prefer-const
  let dt = `${date.value.getFullYear()}-${date.value.getMonth() + 1}-${date.value.getDate().toString().padStart(2, '0')}`
  results.value = await axios.get(`https://superlaxmi.netserve.in/results?filter[date][eq]=${dt}`).then(r => r.data)
  console.log(results.value)
  slots.value.map((sl) => {
    sl.winning_no = results.value?.filter(rs => rs.time === sl.result_time)[0]?.winning_no ?? null
    sl.res_id = results.value?.filter(rs => rs.time === sl.result_time)[0]?.id ?? null
    sl.canEdit = (timeDiff(sl.result_time) < 0 && timeDiff(sl.result_time) > -(20 * 60 * 1000)) ? 'enabled' : 'disabled'
    return sl
  })
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
  // slots.value = await axios.get('https://superlaxmi.netserve.in/slots').then(r => r.data)
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
  <div class="grid grid-cols-1 min-h-screen bg-gray-50 md:grid-cols-4">
    <div class="bg-white p-6 shadow-sm md:col-span-1">
      <div class="mb-6">
        <h2 class="mb-4 flex items-center text-lg font-semibold text-gray-800">
          <svg class="mr-2 h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          Select Date
        </h2>
        <VueDatePicker
          v-model="date"
          auto-apply
          inline
          :max-date="new Date()"
          :enable-time-picker="false"
          format="dd-MM-yyyy"
          @update:model-value="getDayData"
        />
      </div>

      <!-- Quick Stats -->
      <div class="mt-6 rounded-lg bg-blue-50 p-4">
        <h3 class="mb-3 text-sm font-medium text-blue-800">
          Quick Stats
        </h3>
        <div class="space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-blue-600">Total Slots:</span>
            <span class="font-medium text-blue-800">{{ slots.length }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-green-600">Editable:</span>
            <span class="font-medium text-green-700">{{ slots.filter(s => s.canEdit === 'enabled').length }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Completed:</span>
            <span class="font-medium text-gray-700">{{ slots.filter(s => s.winning_no).length }}</span>
          </div>
        </div>
      </div>
    </div>
    <div class="bg-blue-50 p-6 md:col-span-3">
      <div class="mb-6 rounded-lg bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
          <h1 class="flex items-center text-2xl font-bold text-gray-800">
            <svg class="mr-2 h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            Lottery Results Management
          </h1>
          <div class="text-sm text-gray-600">
            {{ date.toDateString() }}
          </div>
        </div>

        <!-- Legend -->
        <div class="flex flex-wrap gap-4 text-xs">
          <div class="flex items-center">
            <div class="mr-2 h-3 w-3 animate-pulse rounded-full bg-green-500" />
            <span class="text-green-700">Editable Now</span>
          </div>
          <div class="flex items-center">
            <div class="mr-2 h-3 w-3 rounded-full bg-gray-400" />
            <span class="text-gray-600">Locked</span>
          </div>
          <div class="flex items-center">
            <div class="mr-2 h-3 w-3 rounded-full bg-emerald-500" />
            <span class="text-emerald-700">Saved Successfully</span>
          </div>
          <div class="flex items-center">
            <div class="mr-2 h-3 w-3 rounded-full bg-red-500" />
            <span class="text-red-700">Error</span>
          </div>
        </div>
      </div>

      <div class="mb-12">
        <div v-for="slot in slots" :key="slot.id">
          <div
            class="relative mb-4 rounded-lg px-4 py-3 transition-all duration-300 hover:shadow-md"
            :class="{
              'bg-green-50 border-2 border-green-200 shadow-sm': slot.canEdit === 'enabled',
              'bg-gray-50 border border-gray-200': slot.canEdit === 'disabled',
              'bg-red-50 border-red-200': slot.status === 'error',
              'bg-emerald-50 border-emerald-200': slot.status === 'success',
            }"
          >
            <!-- Status indicator -->
            <div
              class="absolute right-2 top-2 h-3 w-3 rounded-full"
              :class="{
                'bg-green-500 animate-pulse': slot.canEdit === 'enabled',
                'bg-gray-400': slot.canEdit === 'disabled',
                'bg-red-500': slot.status === 'error',
                'bg-emerald-500': slot.status === 'success',
              }"
            />

            <!-- Slot info with enhanced styling -->
            <div class="pointer-events-none absolute start-0 inset-y-0 flex items-center ps-3">
              <div class="text-left">
                <div
                  class="text-sm font-semibold"
                  :class="{
                    'text-green-700': slot.canEdit === 'enabled',
                    'text-gray-600': slot.canEdit === 'disabled',
                  }"
                >
                  Slot {{ slot.id }}
                </div>
                <div
                  class="text-xs"
                  :class="{
                    'text-green-600': slot.canEdit === 'enabled',
                    'text-gray-500': slot.canEdit === 'disabled',
                  }"
                >
                  {{ formatTime(slot.result_time) }}
                </div>
                <div v-if="slot.canEdit === 'enabled'" class="mt-1 text-xs font-medium text-green-600">
                  ✏️ Editable
                </div>
              </div>
            </div>

            <!-- Editable input with enhanced styling -->
            <div v-if="slot.canEdit === 'enabled'">
              <input
                v-model="slot.winning_no"
                class="block w-3/4 border-2 border-green-300 rounded-lg bg-white p-4 ps-24 text-sm text-gray-900 transition-all duration-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 placeholder-green-400"
                placeholder="Enter winning number (0-99)"
                @blur="autoSave(slot)"
                @focus="slot.status = ''"
              >

              <!-- Enhanced status messages -->
              <div v-if="slot.status === 'error'" class="mt-2 border border-red-300 rounded-md bg-red-100 p-2">
                <p class="flex items-center text-sm text-red-700">
                  <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="font-medium">Error:</span> {{ slot.message }}
                </p>
              </div>

              <div v-else-if="slot.status === 'success'" class="mt-2 border border-emerald-300 rounded-md bg-emerald-100 p-2">
                <p class="flex items-center text-sm text-emerald-700">
                  <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="font-medium">Success:</span> {{ slot.message }}
                </p>
              </div>
            </div>

            <!-- Read-only display with enhanced styling -->
            <div v-else>
              <div class="block w-3/4 border border-gray-300 rounded-lg bg-gray-100 p-4 ps-24 text-sm text-gray-700">
                <div class="flex items-center justify-between">
                  <span class="text-lg font-bold">
                    {{ slot.winning_no?.toString().padStart(2, '0') ?? 'Wait...' }}
                  </span>
                  <span v-if="!slot.winning_no" class="animate-pulse text-xs text-gray-500">
                    Pending result...
                  </span>
                  <span v-else class="text-xs text-gray-500">
                    🔒 Locked
                  </span>
                </div>
              </div>
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
