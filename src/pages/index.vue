<script setup>
import { onMounted, ref } from 'vue'
import logo from '~/assets/laxmiji.gif'

// Countdown to Monday, August 18th, 2025
const targetDate = new Date('2025-08-18T00:00:00')
const timeLeft = ref({
  days: 0,
  hours: 0,
  minutes: 0,
  seconds: 0,
})

function updateCountdown() {
  const now = new Date()
  const difference = targetDate.getTime() - now.getTime()

  if (difference > 0) {
    timeLeft.value = {
      days: Math.floor(difference / (1000 * 60 * 60 * 24)),
      hours: Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
      minutes: Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60)),
      seconds: Math.floor((difference % (1000 * 60)) / 1000),
    }
  }
  else {
    timeLeft.value = { days: 0, hours: 0, minutes: 0, seconds: 0 }
  }
}

onMounted(() => {
  updateCountdown()
  setInterval(updateCountdown, 1000)
})
</script>

<template>
  <div class="min-h-screen flex flex-col from-yellow-50 via-orange-50 to-red-50 bg-gradient-to-br">
    <!-- Header -->
    <header class="bg-yellow-200 shadow-md">
      <nav class="flex justify-center py-6">
        <div class="flex items-center">
          <img :src="logo" class="h-24 w-auto border-2 border-blue-400 rounded-lg bg-white p-2 shadow-lg">
        </div>
      </nav>
    </header>

    <!-- Main Content -->
    <main class="flex flex-1 items-center justify-center px-4 py-12">
      <div class="mx-auto max-w-4xl text-center">
        <!-- Maintenance Icon -->
        <div class="mb-8">
          <div class="mb-4 h-24 w-24 inline-flex items-center justify-center rounded-full bg-yellow-100">
            <svg class="h-12 w-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z" />
            </svg>
          </div>
        </div>

        <!-- Main Heading -->
        <h1 class="mb-6 text-4xl font-bold text-gray-800 md:text-6xl">
          Website Under
          <span class="text-yellow-600">Maintenance</span>
        </h1>

        <!-- Description -->
        <p class="mx-auto mb-8 max-w-3xl text-xl leading-relaxed text-gray-600 md:text-2xl">
          We're working hard to improve your experience. Our lottery system will be back online with enhanced security and new features.
        </p>

        <!-- Launch Date -->
        <div class="mb-8 border border-yellow-200 rounded-2xl bg-white p-8 shadow-xl">
          <h2 class="mb-4 text-2xl font-semibold text-gray-800 md:text-3xl">
            🚀 Launching on
          </h2>
          <div class="mb-6 text-3xl font-bold text-yellow-600 md:text-4xl">
            Monday, August 18th, 2025
          </div>

          <!-- Countdown Timer -->
          <div class="grid grid-cols-2 mx-auto max-w-md gap-4 md:grid-cols-4">
            <div class="rounded-xl from-yellow-400 to-orange-400 bg-gradient-to-br p-4 text-white">
              <div class="text-2xl font-bold md:text-3xl">
                {{ timeLeft.days }}
              </div>
              <div class="text-sm tracking-wide uppercase">
                Days
              </div>
            </div>
            <div class="rounded-xl from-orange-400 to-red-400 bg-gradient-to-br p-4 text-white">
              <div class="text-2xl font-bold md:text-3xl">
                {{ timeLeft.hours }}
              </div>
              <div class="text-sm tracking-wide uppercase">
                Hours
              </div>
            </div>
            <div class="rounded-xl from-red-400 to-pink-400 bg-gradient-to-br p-4 text-white">
              <div class="text-2xl font-bold md:text-3xl">
                {{ timeLeft.minutes }}
              </div>
              <div class="text-sm tracking-wide uppercase">
                Minutes
              </div>
            </div>
            <div class="rounded-xl from-pink-400 to-purple-400 bg-gradient-to-br p-4 text-white">
              <div class="text-2xl font-bold md:text-3xl">
                {{ timeLeft.seconds }}
              </div>
              <div class="text-sm tracking-wide uppercase">
                Seconds
              </div>
            </div>
          </div>
        </div>

        <!-- What's New Section -->
        <div class="mb-8 border border-yellow-200 rounded-2xl bg-white p-8 shadow-xl">
          <h3 class="mb-6 text-2xl font-semibold text-gray-800">
            🎯 What's Coming
          </h3>
          <div class="grid gap-6 md:grid-cols-3">
            <div class="text-center">
              <div class="mx-auto mb-4 h-16 w-16 flex items-center justify-center rounded-full bg-green-100">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <h4 class="mb-2 font-semibold text-gray-800">
                Enhanced Security
              </h4>
              <p class="text-gray-600">
                Firebase authentication for secure access
              </p>
            </div>
            <div class="text-center">
              <div class="mx-auto mb-4 h-16 w-16 flex items-center justify-center rounded-full bg-blue-100">
                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </div>
              <h4 class="mb-2 font-semibold text-gray-800">
                Faster Performance
              </h4>
              <p class="text-gray-600">
                Optimized for speed and reliability
              </p>
            </div>
            <div class="text-center">
              <div class="mx-auto mb-4 h-16 w-16 flex items-center justify-center rounded-full bg-purple-100">
                <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
              </div>
              <h4 class="mb-2 font-semibold text-gray-800">
                Better Experience
              </h4>
              <p class="text-gray-600">
                Improved user interface and features
              </p>
            </div>
          </div>
        </div>

        <!-- Contact Info -->
        <div class="text-lg text-gray-600">
          <p class="mb-2">
            Need assistance? We're here to help!
          </p>
          <p class="font-semibold text-yellow-600">
            Thank you for your patience
          </p>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 py-6 text-white">
      <div class="mx-auto max-w-6xl px-4 text-center">
        <p class="text-gray-300">
          © {{ new Date().getFullYear() }} Super Laxmi Lottery. All rights reserved.
        </p>
        <p class="mt-2 text-sm text-gray-400">
          Scheduled maintenance: August 14-18, 2025
        </p>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* Add any custom animations or styles here */
.fade-in {
  animation: fadeIn 0.8s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
