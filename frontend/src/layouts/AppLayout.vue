<script setup lang="ts">
import { ref } from 'vue'
import Sidebar from '@/components/Sidebar.vue'
import Topbar from '@/components/Topbar.vue'

const sidebarOpen = ref(false)
</script>

<template>
  <div class="min-h-screen flex bg-surface">
    <Sidebar :open="sidebarOpen" @close="sidebarOpen = false" />

    <div class="flex-1 flex flex-col min-w-0">
      <Topbar @toggle-sidebar="sidebarOpen = !sidebarOpen" />
      <main class="flex-1 p-4 md:p-6">
        <router-view />
      </main>
      <footer class="px-4 md:px-6 py-3 text-xs text-muted border-t border-secondary">
        Planer kućanstva — studentski projekt
      </footer>
    </div>
  </div>
</template>
