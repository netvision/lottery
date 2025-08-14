<!-- eslint-disable vue/html-self-closing -->
<!-- eslint-disable vue/html-self-closing -->
<!-- eslint-disable vue/html-self-closing -->
<!-- eslint-disable prefer-const -->
<!-- eslint-disable antfu/top-level-function -->
<!-- eslint-disable no-console -->
<!-- eslint-disable @typescript-eslint/indent -->
<script setup>
import { FwbButton, FwbModal, FwbSelect } from 'flowbite-vue'
import VueDatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import axios from 'axios'

const numbers = ref([])
const inputBids = ref([])
const slots = ref([])
const bids = ref([])
const results = ref([])
const currentTime = ref(new Date())
const currentSlot = ref()
const curBids = ref([])
const newBid = ref({})
const slotsOptions = ref([])
const bidsBySlot = ref([])
const slotSummery = ref({})
const daySummery = ({})

axios.get('https://worldtimeapi.org/api/timezone/Asia/Kolkata').then((r) => {
  currentTime.value = new Date(r.data.datetime)
})

const isShowModal = ref(false)

const showModal = () => {
  isShowModal.value = true
  /* axios.get('https://worldtimeapi.org/api/timezone/Asia/Kolkata').then((r) => {
    currentTime.value = new Date(r.data.datetime)
 }) */
}

// eslint-disable-next-line antfu/top-level-function
const reset = () => {
  newBid.value = {}
  newBid.value.slot_id = currentSlot.value.id
  isShowModal.value = false
}

const getBids = async (date) => {
  let bids = await axios.get(`https://superlaxmi.netserve.in/bids?filter[date][eq]=${date}`).then(r => r.data)
  return bids
}

const getResults = async (date) => {
  let results = await axios.get(`https://superlaxmi.netserve.in/results?filter[date][eq]=${date}`).then(r => r.data)
  return results
}

const getSlotData = (id) => {
  curBids.value = []
  bidsBySlot.value = []
  slotSummery.value = {}
  currentSlot.value = slots.value.filter(sl => sl.id === id)[0]
  if (bids.value.length > 0) {
    let slotResult = {}
    if (results.value.length > 0)
      slotResult = results.value.filter(rs => rs.slot_id === id)[0]
      // console.log(slotResult)
    let slotBids = bids.value.filter(b => b.slot_id === id)
    slotSummery.value.totalBids = slotBids.length
    slotSummery.value.totalAmount = slotBids.reduce((acc, item) => {
      return acc + item.bid_amount
    }, 0)
    bidsBySlot.value = slotBids.reduce((bids, item) => {
      bids[item.bid_no] = bids[item.bid_no] || { items: [], count: 0, amount: 0, isWinningNo: false }
      bids[item.bid_no].items.push(item)
      bids[item.bid_no].amount += item.bid_amount
      bids[item.bid_no].count++
      bids[item.bid_no].isWinningNo = (item.bid_no === slotResult?.winning_no)
      return bids
    }, {})
  }
}

const getDayData = async (data) => {
  let theSlot = null
  bids.value = []
  results.value = []
  curBids.value = []
  daySummery.value = {}
  let date = `${data.getFullYear()}-${data.getMonth() + 1}-${data.getDate().toString().padStart(2, '0')}`
  results.value = await getResults(date)
  bids.value = await getBids(date)

  if (data.toDateString() === new Date().toDateString()) {
    slots.value.map((sl) => {
      if (+sl.title <= +currentTime.value.getHours()) {
        sl.status = 'completed'
        sl.canAdd = false
      }
      else if (+sl.title === +currentTime.value.getHours() + 1) {
        sl.status = 'current'
        sl.canAdd = true
        theSlot = sl
      }
      else {
        sl.status = 'upcoming'
        sl.canAdd = true
      }
      return sl
    })
    slots.value.forEach((sl) => {
    if (sl.status !== 'completed') {
      slotsOptions.value.push({
        value: sl.id,
        name: sl.title,
      })
      }
    })
  }
  else if (data > new Date()) {
    slots.value.map((sl) => {
      sl.status = 'upcoming'
      sl.canAdd = false
      return sl
    })
  }
  else {
    slots.value.map((sl) => {
      sl.status = 'completed'
      sl.canAdd = false
      return sl
    })
  }
  currentSlot.value = (theSlot) || slots.value[0]
  newBid.value.slot_id = currentSlot.value.id
  if (currentSlot.value)
    getSlotData(currentSlot.value.id)
}

const getDaySummery = () => {
  let amount = bids.value.reduce((acc, item) => {
      return acc + item.bid_amount
    }, 0)
  return { bids: bids.value.length, amount }
}

const saveBid = async () => {
  /*
  newBid.value.date = `${currentTime.value.getFullYear()}-${currentTime.value.getMonth() + 1}-${currentTime.value.getDate().toString().padStart(2, '0')}`
  newBid.value.time = `${currentTime.value.getHours().toString().padStart(2, '0')}:${currentTime.value.getMinutes().toString().padStart(2, '0')}:${currentTime.value.getSeconds().toString().padStart(2, '0')}`
  let res = await protectedAPI.post('/bids', newBid.value)
  if (res.status === 201) {
    newBid.value = {}
    getDayData(currentTime.value)
  }
  */
 console.log(inputBids.value)
  isShowModal.value = false
}

const autoSave = (n) => {
  newBid.value.date = `${currentTime.value.getFullYear()}-${currentTime.value.getMonth() + 1}-${currentTime.value.getDate().toString().padStart(2, '0')}`
  newBid.value.time = `${currentTime.value.getHours().toString().padStart(2, '0')}:${currentTime.value.getMinutes().toString().padStart(2, '0')}:${currentTime.value.getSeconds().toString().padStart(2, '0')}`
  newBid.value.bid_no = n
  newBid.value.bid_amount = inputBids.value[n]
  newBid.value.client_no = 'default'
  console.log(newBid.value)
}

const showBidsModal = (v) => {
  curBids.value = v
}

onMounted(async () => {
  numbers.value = Array.from({ length: 99 }, (_, i) => (i + 1).toString().padStart(2, '0'))
  slots.value = await axios.get('https://superlaxmi.netserve.in/slots').then(r => r.data)
  currentSlot.value = slots.value[0]
  await getDayData(currentTime.value)
  /*
  const digits = [0, 6, 7]
  const twoDigitIntegers = digits.flatMap(firstDigit => digits.map(secondDigit => `${firstDigit}${secondDigit}`));
  console.log(twoDigitIntegers)
  */
})
</script>

<template class="min-h-screen">
  <NtpClock />
  <div class="grid grid-cols-1 md:grid-cols-5">
    <div class="bg-gray-200 p-4 md:col-span-1">
      <VueDatePicker v-model="currentTime" auto-apply :enable-time-picker="false" format="dd-MM-yyyy" @update:model-value="getDayData" />
      <div v-for="slot in slots" :key="slot.id" class="gap-3">
        <a class="group relative w-full inline-flex items-center justify-start overflow-hidden rounded bg-gray-50 py-3 pl-4 pr-12 font-semibold text-orange-600 transition-all duration-150 ease-in-out hover:pl-10 hover:pr-6" @click="getSlotData(slot.id)">
          <span class="absolute bottom-0 left-0 h-1 w-full bg-indigo-600 transition-all duration-150 ease-in-out group-hover:h-full" />
          <span class="absolute right-0 pr-4 duration-200 ease-out group-hover:translate-x-12">
            <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
          </span>
          <span class="absolute left-0 pl-2.5 duration-200 ease-out -translate-x-12 group-hover:translate-x-0">
            <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
          </span>
          <span class="relative w-full text-left transition-colors duration-200 ease-in-out group-hover:text-white">Slot <span :class="slot.status">{{ slot.title }}</span></span>
        </a>
      </div>
    </div>
    <div class="bg-blue-200 p-4 md:col-span-4">
      <div>
        <div class="my-4 w-full overflow-hidden rounded-lg bg-white shadow-lg">
          <div class="flex items-center bg-gray-900 px-6 py-3">
            <div class="grid grid-cols-2 mx-3 w-full justify-between gap-2 text-lg font-semibold text-white">
              <div class="col-span-1 text-left">
                {{ currentTime.toDateString() }}
              </div>
              <div class="col-span-1 text-right align-bottom">
                <p class="px-2 text-sm">
                  Bids Recieved: {{ getDaySummery().bids }}
                </p>
                <p class="px-2 text-sm">
                  Total Amount: &#8377;{{ getDaySummery().amount }}
                </p>
              </div>
            </div>
          </div>
          <div class="px-6 py-4">
            <div class="grid grid-cols-2 mx-3 w-full justify-between gap-2 text-lg font-semibold text-gray-800">
              <div class="col-span-1 text-left">
                Slot: {{ currentSlot?.title }}
              </div>
              <div class="col-span-1 mr-10 text-right align-bottom">
                <p class="px-2 text-sm">
                  Bids Recieved: {{ slotSummery.totalBids }}
                </p>
                <p class="px-2 text-sm">
                  Total Amount: &#8377;{{ slotSummery.totalAmount }}
                </p>
              </div>
            </div>
            <hr class="mb-4 border-b border-gray-800">
            <FwbButton v-if="currentSlot?.canAdd" @click="showModal">
              New Bid
            </FwbButton>
          </div>
          <div class="grid grid-cols-3 gap-3 p-4 md:grid-cols-10">
            <div v-for="(v, k) in bidsBySlot" :key="k">
              <div class="rounded bg-white p-2 shadow">
                <h3 class="text-lg font-semibold">
                  {{ k }}
                </h3>
                <p class="text-sm text-gray-600">
                  &#8377;{{ v.amount }}<span class="m-1 inline-block cursor-pointer bg-blue-500 px-1 py-1 text-xs text-white" @click="showBidsModal(v.items)">{{ v.count }}</span>
                </p>
              </div>
            </div>
          </div>
          <div v-if="curBids">
            <ul>
              <li v-for="b in curBids" :key="b.id">
                {{ b.client_no }} - <span>No.{{ b.bid_no }}</span> - &#8377;{{ b.bid_amount }}
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
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
      <div v-if="numbers" class="flex flex-wrap gap-2">
        <div v-for="(n, i) in numbers" :key="i">
          <div class="relative mb-4 flex flex-wrap items-stretch">
            <span
              id="basic-addon1"
              class="flex items-center whitespace-nowrap border border-r-0 border-neutral-300 rounded-l border-solid px-1 py-[0.25rem] text-center text-base text-xs font-normal leading-[1.6] text-neutral-700 dark:border-neutral-600 dark:text-neutral-200 dark:placeholder:text-neutral-200"
            >{{ n }}</span>
            <input
              v-model="inputBids[n]"
              type="text"
              class="focus:border-primary dark:focus:border-primary relative m-0 block min-w-0 w-[45px] flex-auto border border-neutral-300 rounded-r border-solid bg-transparent bg-clip-padding px-1 py-[0.25rem] text-base font-normal leading-[1.6] text-neutral-700 outline-none transition duration-200 ease-in-out focus:z-[3] dark:border-neutral-600 dark:text-neutral-200 focus:text-neutral-700 focus:shadow-[inset_0_0_0_1px_rgb(59,113,202)] focus:outline-none dark:placeholder:text-neutral-200"
              placeholder=""
              aria-label="amount"
              aria-describedby="basic-addon1"
              @blur="autoSave(n)"
            >
          </div>
        </div>
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
</template>

<style>
.completed{
   @apply text-indigo-600 hover:text-white
}
.current{
  @apply text-green-600 hover:text-white
}
.upcoming{
  @apply text-blue-600 hover:text-white
}
</style>

<route lang="json">
  {
    "meta" : {
      "requireAuth" : true
    }
  }
</route>
