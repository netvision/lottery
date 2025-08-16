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

const formatTime = (mysqlTime, userLocale = navigator.language || 'en-US') => {
  if (!mysqlTime)
    return 'Invalid Time'
  return moment(mysqlTime, 'HH:mm:ss').locale(userLocale).format('LT')
}

const ifFuture = (rtime) => {
  if (!rtime || !date.value)
    return 0
  const dt = `${date.value.getFullYear()}-${(date.value.getMonth() + 1).toString().padStart(2, '0')}-${date.value.getDate().toString().padStart(2, '0')}`
  return Number.parseInt(moment().diff(moment(`${dt}T${rtime}`, 'YYYY-MM-DDTHH:mm:ss'), 'second'))
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

function mysqlTime(mysqlTime) {
  // Check if mysqlTime is valid before processing
  if (!mysqlTime || typeof mysqlTime !== 'string')
    return 'Invalid Time'

  // Assuming mysqlTime is in the format 'HH:MM:SS'
  const timeParts = mysqlTime.split(':')
  if (timeParts.length < 2)
    return 'Invalid Time'

  const [hours, minutes] = timeParts

  // Create a Date object with the time values
  const dateObj = new Date()
  dateObj.setHours(Number.parseInt(hours, 10) || 0, Number.parseInt(minutes, 10) || 0, 0, 0)

  // Format the time using toLocaleTimeString
  const formattedTime = dateObj.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: 'numeric',
    hour12: true,
  })

  return formattedTime
}

const getDayData = async () => {
  const dt = `${date.value.getFullYear()}-${(date.value.getMonth() + 1).toString().padStart(2, '0')}-${date.value.getDate().toString().padStart(2, '0')}`
  const curTime = new Date()
  curTime.setHours(0)
  curTime.setMinutes(0)
  curTime.setSeconds(0)

  try {
    results.value = await axios.get(`https://superlaxmi.netserve.in/results?filter[date][eq]=${dt}`).then(r => r.data)

    slots.value.map((sl) => {
      sl.winning_no = results.value?.filter(rs => rs.time === sl.result_time)[0]?.winning_no ?? 'NA'
      return sl
    })

    // Find upcoming slot safely - this will be used by the computed property
    upcoming.value = slots.value.find(sl => ifFuture(sl.result_time) < 0) || null
  }
  catch (error) {
    console.error('Error fetching data:', error)
    upcoming.value = null
  }
}

// Computed properties for better data organization

// Computed: slot order for index view - only show slots whose time has passed + next upcoming
const orderedSlots = computed(() => {
  // Only show slots whose scheduled time has passed
  const passedSlots = slots.value.filter(slot => ifFuture(slot.result_time) >= 0)

  // Sort by result_time descending (latest first)
  return passedSlots.sort((a, b) => b.result_time.localeCompare(a.result_time))
})

// Find the next upcoming result (future slot)
const nextUpcoming = computed(() => {
  const upcomingList = slots.value.filter(slot => ifFuture(slot.result_time) < 0)
  if (upcomingList.length === 0)
    return null

  return upcomingList.reduce((closest, current) => {
    const closestTime = Math.abs(ifFuture(closest.result_time))
    const currentTime = Math.abs(ifFuture(current.result_time))
    return currentTime < closestTime ? current : closest
  })
})

// Dynamic header text based on selected date
const resultsHeaderText = computed(() => {
  const today = new Date()
  const selectedDate = date.value

  // Check if the selected date is today
  const isToday = selectedDate.toDateString() === today.toDateString()

  if (isToday)
    return 'Today\'s Results'

  // Check if it's yesterday
  const yesterday = new Date(today)
  yesterday.setDate(today.getDate() - 1)
  const isYesterday = selectedDate.toDateString() === yesterday.toDateString()

  if (isYesterday)
    return 'Yesterday\'s Results'

  // For other dates, show the actual date
  return `Results for ${selectedDate.toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })}`
})

// Dynamic subtitle text
const resultsSubtitle = computed(() => {
  const today = new Date()
  const selectedDate = date.value
  const isToday = selectedDate.toDateString() === today.toDateString()

  if (isToday)
    return 'Live updates every 20 minutes'

  return 'Historical results'
})

const intervals = ref([])

const intId = setInterval(() => {
  if (nextUpcoming.value?.result_time) {
    const t = Math.abs(ifFuture(nextUpcoming.value.result_time)) ?? 0
    upTime.value = moment.utc(t * 1000).format('HH:mm:ss')
    if (t <= 0) {
      // Refresh data instead of full page reload for better UX
      location.reload()
    }
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
  // slots.value = await axios.get('https://superlaxmi.netserve.in/slots').then(r => r.data)
  /*
  slots.value = (new Date() > date) ? data.filter(d => ifFuture(d.result_time) > 0) : data
  upcoming.value = data.filter(d => ifFuture(d.result_time) < 0)[0]
  */
  slots.value = timeArray()
  slots.value.push({ result_time: '22:00:00', id: 38 })
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

<template class="min-h-screen">
  <NtpClock />

  <!-- Mobile-First Layout -->
  <div class="min-h-screen from-blue-600 via-blue-700 to-blue-800 bg-gradient-to-br">
    <!-- Mobile Header Section -->
    <div class="border-b border-white/20 bg-white/10 p-4 text-white backdrop-blur-sm sm:p-6">
      <div class="mx-auto container">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
          <div class="text-center sm:text-left">
            <h1 class="text-xl font-bold lg:text-3xl sm:text-2xl">
              Results
            </h1>
            <p class="text-sm text-blue-100 sm:text-base">
              {{ date?.toDateString() }}
            </p>
          </div>

          <!-- Logo for mobile -->
          <div class="flex justify-center sm:justify-end">
            <img
              :src="logo"
              class="h-16 w-auto rounded-lg shadow-lg md:h-24 sm:h-20"
              alt="Laxmi Logo"
            >
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile-First Content Grid -->
    <div class="mx-auto p-4 container sm:p-6">
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        <!-- Date Picker Section -->
        <div class="rounded-xl bg-white/10 p-4 shadow-lg backdrop-blur-sm lg:col-span-1">
          <h2 class="mb-4 text-lg font-semibold text-white sm:text-xl">
            Select Date
          </h2>
          <div class="rounded-lg bg-white p-3">
            <VueDatePicker
              v-model="date"
              menu-class-name="dp-custom-menu"
              calendar-class-name="calendar"
              auto-apply
              inline
              :max-date="new Date()"
              :enable-time-picker="false"
              format="dd-MM-yyyy"
              @update:model-value="getDayData"
            />
          </div>
        </div>

        <!-- Results Section -->
        <div class="lg:col-span-3">
          <div v-if="date && !isLastDayOfMonth(date)">
            <!-- Results Header -->
            <div class="mb-6 rounded-xl bg-white/10 p-4 text-white backdrop-blur-sm">
              <h2 class="mb-2 text-xl font-bold sm:text-2xl">
                {{ resultsHeaderText }}
              </h2>
              <div class="text-sm text-blue-100 sm:text-base">
                {{ resultsSubtitle }}
              </div>
            </div>

            <!-- Next Result Countdown - Show closest upcoming -->
            <div v-if="nextUpcoming" class="mb-6 rounded-xl from-blue-500 to-indigo-600 bg-gradient-to-r p-4 text-white shadow-lg sm:p-6">
              <div class="text-center">
                <div class="mb-2 text-lg font-semibold sm:text-xl">
                  ⏰ Next Result In
                </div>
                <div class="mb-2 text-sm opacity-90 sm:text-base">
                  Slot {{ nextUpcoming.id }} - {{ nextUpcoming.result_time ? mysqlTime(nextUpcoming.result_time) : 'No Time' }}
                </div>
                <div class="text-2xl font-bold sm:text-3xl">
                  {{ upTime }}
                </div>
                <div class="mt-2 flex items-center justify-center space-x-1">
                  <div class="h-2 w-2 animate-pulse rounded-full bg-white" />
                  <div class="h-2 w-2 animate-pulse rounded-full bg-white" style="animation-delay: 0.2s" />
                  <div class="h-2 w-2 animate-pulse rounded-full bg-white" style="animation-delay: 0.4s" />
                </div>
              </div>
            </div>

            <!-- Mobile-First Results Grid -->

            <div class="space-y-4">
              <div v-for="slot in orderedSlots" :key="slot.id">
                <div
                  :class="[
                    // Pending slot at top
                    (!slot.winning_no || slot.winning_no === 'NA') ? 'rounded-xl bg-orange-50 border-2 border-orange-200 p-4 shadow-lg transition-all duration-300 hover:shadow-xl sm:p-6'
                    // Completed slots
                    : 'rounded-xl bg-white/95 backdrop-blur-sm p-4 shadow-lg transition-all duration-300 hover:shadow-xl sm:p-6',
                  ]"
                >
                  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                    <!-- Slot Info -->
                    <div class="text-center sm:text-left">
                      <div :class="[(!slot.winning_no || slot.winning_no === 'NA') ? 'text-lg font-bold text-orange-800 sm:text-xl' : 'text-lg font-bold text-gray-800 sm:text-xl']">
                        Slot {{ slot.id }}
                      </div>
                      <div :class="[(!slot.winning_no || slot.winning_no === 'NA') ? 'text-sm text-orange-600 sm:text-base' : 'text-sm text-gray-600 sm:text-base']">
                        {{ slot.result_time ? mysqlTime(slot.result_time) : 'No Time' }}
                      </div>
                      <div v-if="slot.winning_no === null || slot.winning_no === undefined || slot.winning_no === 'NA'" class="mt-1 text-xs font-medium text-orange-700">
                        ⏳ Awaiting Result
                      </div>
                    </div>
                    <!-- Awaiting Display or Winner -->
                    <div class="flex justify-center sm:justify-end">
                      <div v-if="slot.winning_no === null || slot.winning_no === undefined || slot.winning_no === 'NA'" class="rounded-2xl from-orange-400 to-amber-500 bg-gradient-to-r p-4 shadow-lg">
                        <div class="text-center">
                          <div class="text-2xl font-bold text-white sm:text-3xl">
                            --
                          </div>
                          <div class="text-xs text-orange-100 sm:text-sm">
                            PENDING
                          </div>
                        </div>
                      </div>
                      <div v-else class="relative rounded-2xl from-green-400 to-emerald-500 bg-gradient-to-r p-4 shadow-lg">
                        <div class="text-center">
                          <div class="text-2xl font-bold text-white sm:text-3xl">
                            {{ slot?.winning_no?.toString().padStart(2, '0') }}
                          </div>
                        </div>
                        <!-- Sparkle effect -->
                        <div class="absolute h-3 w-3 animate-ping rounded-full bg-yellow-400 -right-1 -top-1" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Last Day of Month Message -->
          <div v-else class="rounded-xl bg-white/10 p-6 text-center text-white backdrop-blur-sm">
            <div class="mx-auto mb-4 h-16 w-16 flex items-center justify-center rounded-full bg-yellow-500">
              <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
            <h2 class="mb-2 text-xl font-bold sm:text-2xl">
              Last Day of the Month
            </h2>
            <p class="text-blue-100">
              No results available on the last day of the month
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
.dp-custom-menu {
  box-shadow: 0 0 6px #1976d2;
  background-color: rgb(59, 130, 246);
  color: white;
  font-weight: bold;
  border-radius: 0.75rem;
}

.calendar {
  font-style: normal;
  border-radius: 0.5rem;
  width: 100%;
  font-size: 0.875rem;
}

/* Enhanced calendar styling for full calendar view */
.dp__calendar_wrap {
  padding: 1rem;
}

.dp__calendar_header {
  padding: 0.5rem 0;
  margin-bottom: 0.5rem;
}

.dp__calendar_item {
  height: 2.5rem;
  border-radius: 0.375rem;
  transition: all 0.2s ease;
}

.dp__today {
  background-color: rgb(59, 130, 246) !important;
  color: white !important;
  font-weight: bold;
}

.dp__active_date {
  background-color: rgb(34, 197, 94) !important;
  color: white !important;
  font-weight: bold;
}

.dp__calendar_item:hover {
  background-color: rgb(219, 234, 254);
  transform: scale(1.05);
}

/* Mobile-first responsive enhancements */
@media (max-width: 640px) {
  .dp-custom-menu {
    font-size: 0.875rem;
  }

  .calendar {
    font-size: 0.75rem;
  }

  .dp__calendar_item {
    height: 2rem;
    font-size: 0.75rem;
  }
}

/* Enhanced mobile calendar for touch interaction */
@media (max-width: 480px) {
  .dp__calendar_item {
    height: 2.25rem;
    min-width: 2.25rem;
    touch-action: manipulation;
  }
}

/* Smooth animations for mobile */
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-slide-in {
  animation: slideIn 0.3s ease-out;
}

/* Touch-friendly hover states */
@media (hover: hover) {
  .hover-scale:hover {
    transform: scale(1.02);
  }
}

/* Calendar navigation buttons */
.dp__arrow_top {
  color: rgb(59, 130, 246);
  font-weight: bold;
}

.dp__calendar_header_item {
  color: rgb(75, 85, 99);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.05em;
}
</style>
