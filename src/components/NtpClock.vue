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
  <div class="w-full overflow-x-hidden bg-red-900">
    <div class="marquee w-full whitespace-nowrap py-3 font-bold text-white">
      <span>{{ ntpTime.toUpperCase() }}</span>
    </div>
  </div>
</template>

<style>
  .marquee {
    animation: marquee 15s linear infinite;
  }
  @keyframes marquee {
  from {
    transform: translateX(0%);
  }
  to {
    transform: translateX(100%);
  }
}
</style>
