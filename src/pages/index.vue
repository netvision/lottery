<!-- eslint-disable prefer-const -->
<!-- eslint-disable antfu/top-level-function -->
<!-- eslint-disable no-console -->
<!-- eslint-disable @typescript-eslint/indent -->
<script setup>
import { FwbButton, FwbInput, FwbModal, FwbSelect } from 'flowbite-vue'
import axios from 'axios'

const slots = ref([])
const bids = ref([])
const currentTime = ref('')
const currentSlot = ref({})
const newBid = ref({})
const slotsOptions = ref([])

axios.get('https://worldtimeapi.org/api/timezone/Asia/Kolkata').then((r) => {
  currentTime.value = new Date(r.data.datetime)
})

const isShowModal = ref(false)

const showModal = () => {
  isShowModal.value = true
  axios.get('https://worldtimeapi.org/api/timezone/Asia/Kolkata').then((r) => {
  currentTime.value = new Date(r.data.datetime)
})
}

// eslint-disable-next-line antfu/top-level-function
const reset = () => {
  newBid.value = {}
  isShowModal.value = false
}

const saveBid = async () => {
  newBid.value.date = `${currentTime.value.getFullYear()}-${currentTime.value.getMonth() + 1}-${currentTime.value.getDate().toString().padStart(2, '0')}`
  newBid.value.time = `${currentTime.value.getHours()}:${currentTime.value.getMinutes()}:${currentTime.value.getSeconds().toString().padStart(2, '0')}`
  let res = await axios.post('https://lotteryapi.netserve.in/bids', newBid.value)
  console.log(res)
  isShowModal.value = false
}

onMounted(async () => {
  slots.value = await axios.get('https://lotteryapi.netserve.in/slots').then(r => r.data)
  slots.value.map((sl) => {
    if (+sl.title <= +currentTime.value.getHours()) {
      sl.status = 'completed'
    }
    else if (+sl.title === +currentTime.value.getHours() + 1) {
      sl.status = 'current'
      currentSlot.value = sl
    }
    else { sl.status = 'upcoming' }
    return sl
  })
  // eslint-disable-next-line no-console
  console.log(slots.value)
  // currentSlot.value = (currentSlot.value.length > 0) ? currentSlot.value : slots.value[0]
  newBid.value.slot_id = currentSlot.value.id
  slots.value.forEach((sl) => {
    if (sl.status !== 'completed') {
      slotsOptions.value.push({
        value: sl.id,
        name: sl.title,
      })
    }
  })
  let date = `${currentTime.value.getFullYear()}-${currentTime.value.getMonth() + 1}-${currentTime.value.getDate().toString().padStart(2, '0')}`
  bids.value = await axios.get(`https://lotteryapi.netserve.in/bids?filter[date][eq]=${date}`).then(r => r.data)
})
</script>

<template class="min-h-screen">
  <NtpClock />
  <div class="grid grid-cols-1 md:grid-cols-5">
    <div class="bg-gray-200 p-4 md:col-span-1">
      <div v-for="slot in slots" :key="slot.id" class="gap-3">
        <a class="group relative w-full inline-flex items-center justify-start overflow-hidden rounded bg-gray-50 py-3 pl-4 pr-12 font-semibold text-indigo-600 transition-all duration-150 ease-in-out hover:pl-10 hover:pr-6" @click="alert('Hello')">
          <span class="absolute bottom-0 left-0 h-1 w-full bg-indigo-600 transition-all duration-150 ease-in-out group-hover:h-full" />
          <span class="absolute right-0 pr-4 duration-200 ease-out group-hover:translate-x-12">
            <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
          </span>
          <span class="absolute left-0 pl-2.5 duration-200 ease-out -translate-x-12 group-hover:translate-x-0">
            <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
          </span>
          <span class="relative w-full text-left transition-colors duration-200 ease-in-out group-hover:text-white">Slot {{ slot.title }}</span>
        </a>
      </div>
    </div>
    <div class="bg-blue-200 p-4 md:col-span-4">
      <pre>{{ currentTime }}</pre>
      <FwbButton @click="showModal">
        New Bid
      </FwbButton>
      <FwbModal v-if="isShowModal" @close="reset">
        <template #header>
          <div class="w-full flex items-center justify-items-stretch gap-3 text-lg">
            <div>
              {{ currentTime.toDateString() }}
            </div>
            <div class="place-self-end">
              <FwbSelect
                v-model="newBid.slot_id"
                :options="slotsOptions"
                label="Select a slot"
              />
            </div>
          </div>
        </template>
        <template #body>
          <div class="flex gap-2">
            <FwbInput
              v-model="newBid.client_no"
              label="Client No"
              placeholder="Phone No of the Client"
              required
            />
            <FwbInput
              v-model="newBid.bid_no"
              label="Number"
              placeholder="Bid Number"
              required
            />
            <FwbInput
              v-model="newBid.bid_amount"
              label="Amount"
              placeholder="Bid Amount"
              required
            />
          </div>
        </template>
        <template #footer>
          <div class="flex justify-between">
            <FwbButton color="alternative" @click="reset">
              Cancel
            </FwbButton>
            <FwbButton color="green" @click="saveBid">
              Save
            </FwbButton>
          </div>
        </template>
      </FwbModal>
      <pre>{{ bids }}</pre>
    </div>
  </div>
</template>

<style>
.completed{
  background-color: blue;
}
</style>

<route lang="json">
  {
    "meta" : {
      "requireAuth" : true
    }
  }
</route>
