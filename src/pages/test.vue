<!-- eslint-disable no-console -->
<!-- eslint-disable no-restricted-globals -->
<script setup>
import { onBeforeUnmount, onMounted } from 'vue'

const events = [
  { name: 'Event 1', time: '18:45' },
  { name: 'Event 2', time: '18:46' },
  { name: 'Event 3', time: '18:47' },
  // Add more events with their specific times
]

const intervals = []

function scheduleEvents() {
  events.forEach((event) => {
    const [hours, minutes] = event.time.split(':')
    const scheduledTime = new Date()
    scheduledTime.setHours(parseInt(hours, 10))
    scheduledTime.setMinutes(parseInt(minutes, 10))
    scheduledTime.setSeconds(0)

    const currentTime = new Date()
    const timeDifference = scheduledTime.getTime() - currentTime.getTime()

    if (timeDifference > 0) {
      const timeoutId = setTimeout(() => {
        runEventFunction(event)
        const intervalId = setInterval(() => {
          runEventFunction(event)
        }, 24 * 60 * 60 * 1000)

        intervals.push(intervalId)
      }, timeDifference)

      intervals.push(timeoutId)
    }
  })
}

function runEventFunction(event) {
  console.log(`Running ${event.name} at ${event.time}`)
}

onMounted(() => {
  scheduleEvents()
})

onBeforeUnmount(() => {
  intervals.forEach((id) => {
    clearInterval(id)
    clearTimeout(id)
  })
})
</script>

<template>
  <div>
    <p v-for="(event, index) in events" :key="index">
      {{ event.name }} at {{ event.time }}
    </p>
  </div>
</template>
