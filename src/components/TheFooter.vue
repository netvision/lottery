<template>
  <nav sticky bottom-0 mt-6 gap-2 text-xl>
    <button icon-btn @click="toggleDark()">
      <div i-carbon-sun dark:i-carbon-moon />
    </button>

    &copy; <a
      icon-btn
      rel="noreferrer"
      href="https://rkjkbarasia.com/"
      target="_blank"
      title="RKJK Barasia College"
    >My Lottery</a>
  </nav>
</template>
