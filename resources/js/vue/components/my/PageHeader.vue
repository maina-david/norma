<template>
  <header>
    <div class="py-4 px-4 sm:px-6 flex flex-col md:flex-row justify-between space-x-2 space-y-4">
      <div class="font-light text-lg md:text-2xl text-libryo-gray-900 font-serif">
        <slot />
      </div>
      <div>
        <slot name="actions" />
      </div>
    </div>
  </header>
</template>
