<script setup>
import { Head } from '@inertiajs/vue3'
import Chat from './Chat.vue'
</script>

<template>
  <Head title="Dashboard" />
  <div class="!bg-white">
    <Chat />
  </div>
</template>

<style scoped>
:deep(body) {
  background-color: white !important;
}
</style>