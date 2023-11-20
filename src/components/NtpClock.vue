<!-- eslint-disable antfu/top-level-function -->
<!-- eslint-disable no-console -->
<script setup>
const ntpTime = ref(new Date().toLocaleString())
const updateTime = () => {
  const options = {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: true,
    timeZone: 'Asia/Kolkata',
  }

  const newTime = new Date()
  ntpTime.value = newTime.toLocaleString('en-IN', options)
}

onMounted(() => {
// Use setInterval in onMounted
  const intervalId = setInterval(updateTime, 1000)

  // Clean up the interval when the component is unmounted
  onBeforeUnmount(() => {
    clearInterval(intervalId)
  })
})
</script>

<template>
  <div>
    <p class="bg-slate-200 p-3 text-right">
      {{ ntpTime.toUpperCase() }}
    </p>
  </div>
</template>
