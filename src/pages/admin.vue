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

// Computed: slot order for admin view
const orderedSlots = computed(() => {
  // Find the nearest future slot with no result (pending/awaiting)
  const now = new Date()
  let pending = null
  let minDiff = Infinity
  const completed = []
  slots.value.forEach((slot) => {
    const diff = timeDiff(slot.result_time)
    if (!slot.winning_no && diff < 0 && Math.abs(diff) < minDiff) {
      pending = slot
      minDiff = Math.abs(diff)
    }
    else if (slot.winning_no) {
      completed.push(slot)
    }
  })
  // Sort completed in reverse order (latest first by result_time)
  completed.sort((a, b) => b.result_time.localeCompare(a.result_time))
  // Return array: [pending, ...completed]
  return pending ? [pending, ...completed] : completed
})

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
  <div class="min-h-screen bg-gray-50">
    <!-- Mobile Header -->
    <div class="bg-white p-4 shadow-sm sm:p-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
        <h1 class="flex items-center text-xl font-bold text-gray-800 sm:text-2xl">
          <svg class="mr-2 h-5 w-5 text-blue-600 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
          </svg>
          Admin Panel
        </h1>
        <div class="text-sm text-gray-600 sm:text-base">
          {{ date.toDateString() }}
        </div>
      </div>
    </div>

    <!-- Mobile-First Controls Section -->
    <div class="border-b border-gray-200 bg-white p-4 sm:p-6">
      <!-- Date Picker Section -->
      <div class="max-w-md rounded-lg bg-blue-50 p-4">
        <h2 class="mb-3 flex items-center text-base font-semibold text-gray-800 sm:text-lg">
          <svg class="mr-2 h-4 w-4 text-blue-600 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          Select Date
        </h2>
        <VueDatePicker
          v-model="date"
          auto-apply
          :inline="false"
          :max-date="new Date()"
          :enable-time-picker="false"
          format="dd-MM-yyyy"
          @update:model-value="getDayData"
        />
      </div>

      <!-- Mobile Legend -->
      <div class="mt-4 rounded-lg bg-gray-50 p-3">
        <div class="grid grid-cols-2 gap-2 text-xs sm:flex sm:flex-wrap sm:gap-4 sm:text-sm">
          <div class="flex items-center">
            <div class="mr-2 h-2 w-2 animate-pulse rounded-full bg-green-500 sm:h-3 sm:w-3" />
            <span class="text-green-700">Editable</span>
          </div>
          <div class="flex items-center">
            <div class="mr-2 h-2 w-2 rounded-full bg-gray-400 sm:h-3 sm:w-3" />
            <span class="text-gray-600">Locked</span>
          </div>
          <div class="flex items-center">
            <div class="mr-2 h-2 w-2 rounded-full bg-emerald-500 sm:h-3 sm:w-3" />
            <span class="text-emerald-700">Saved</span>
          </div>
          <div class="flex items-center">
            <div class="mr-2 h-2 w-2 rounded-full bg-red-500 sm:h-3 sm:w-3" />
            <span class="text-red-700">Error</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile-First Results Section -->
    <div class="p-4 sm:p-6">
      <div class="mb-12">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3 sm:gap-4">
          <div
            v-for="slot in orderedSlots"
            :key="slot.id"
            class="relative rounded-lg transition-all duration-300 hover:shadow-lg"
            :class="{
              'bg-green-50 border-2 border-green-200 shadow-sm': slot.canEdit === 'enabled',
              'bg-gray-50 border border-gray-200': slot.canEdit === 'disabled',
              'bg-red-50 border-red-200': slot.status === 'error',
              'bg-emerald-50 border-emerald-200': slot.status === 'success',
            }"
          >
            <!-- Mobile-optimized card layout -->
            <div class="p-4">
              <!-- Header with slot info and status -->
              <div class="mb-3 flex items-center justify-between">
                <div>
                  <div
                    class="text-sm font-bold sm:text-base"
                    :class="{
                      'text-green-700': slot.canEdit === 'enabled',
                      'text-gray-600': slot.canEdit === 'disabled',
                    }"
                  >
                    Slot {{ slot.id }}
                  </div>
                  <div
                    class="text-xs sm:text-sm"
                    :class="{
                      'text-green-600': slot.canEdit === 'enabled',
                      'text-gray-500': slot.canEdit === 'disabled',
                    }"
                  >
                    {{ formatTime(slot.result_time) }}
                  </div>
                </div>

                <!-- Status indicator with enhanced mobile visibility -->
                <div
                  class="h-4 w-4 rounded-full sm:h-3 sm:w-3"
                  :class="{
                    'bg-green-500 animate-pulse': slot.canEdit === 'enabled',
                    'bg-gray-400': slot.canEdit === 'disabled',
                    'bg-red-500': slot.status === 'error',
                    'bg-emerald-500': slot.status === 'success',
                  }"
                />
              </div>

              <!-- Status label for mobile -->
              <div v-if="slot.canEdit === 'enabled'" class="mb-3 inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700 sm:text-sm">
                ✏️ Editable Now
              </div>

              <!-- Input/Display Section -->
              <div v-if="slot.canEdit === 'enabled'">
                <input
                  v-model="slot.winning_no"
                  class="block w-full border-2 border-green-300 rounded-lg bg-white p-3 text-base text-gray-900 transition-all duration-200 focus:border-green-500 sm:p-4 sm:text-sm focus:ring-2 focus:ring-green-200 placeholder-green-400"
                  placeholder="Enter number (0-99)"
                  type="number"
                  min="0"
                  max="99"
                  @blur="autoSave(slot)"
                  @focus="slot.status = ''"
                >

                <!-- Mobile-optimized status messages -->
                <div v-if="slot.status === 'error'" class="mt-2 border border-red-300 rounded-md bg-red-100 p-2">
                  <p class="flex items-start text-xs text-red-700 sm:text-sm">
                    <svg class="mr-1 mt-0.5 h-3 w-3 flex-shrink-0 sm:h-4 sm:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ slot.message }}</span>
                  </p>
                </div>

                <div v-else-if="slot.status === 'success'" class="mt-2 border border-emerald-300 rounded-md bg-emerald-100 p-2">
                  <p class="flex items-start text-xs text-emerald-700 sm:text-sm">
                    <svg class="mr-1 mt-0.5 h-3 w-3 flex-shrink-0 sm:h-4 sm:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ slot.message }}</span>
                  </p>
                </div>
              </div>

              <!-- Read-only display for locked slots -->
              <div v-else>
                <div class="flex items-center justify-between border border-gray-300 rounded-lg bg-gray-100 p-3 sm:p-4">
                  <div class="flex items-center space-x-3">
                    <span class="text-2xl font-bold text-gray-700 sm:text-3xl">
                      {{ slot.winning_no?.toString().padStart(2, '0') ?? '--' }}
                    </span>
                    <div class="text-xs text-gray-500 sm:text-sm">
                      <div v-if="!slot.winning_no" class="flex items-center">
                        <div class="mr-1 h-2 w-2 animate-pulse rounded-full bg-gray-400" />
                        Pending...
                      </div>
                      <div v-else class="flex items-center">
                        🔒 Final Result
                      </div>
                    </div>
                  </div>
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
