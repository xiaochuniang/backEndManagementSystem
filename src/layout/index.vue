<script setup lang="ts">
import Sidebar from './components/Sidebar.vue'
import Header from './components/Header.vue'
import TagsView from './components/TagsView.vue'
</script>

<template>
  <div class="app-wrapper">
    <Sidebar />
    <div class="main-container">
      <Header />
      <TagsView />
      <div class="app-main">
        <router-view v-slot="{ Component }">
          <transition name="fade-transform" mode="out-in">
            <keep-alive>
              <component :is="Component" />
            </keep-alive>
          </transition>
        </router-view>
      </div>
    </div>
  </div>
</template>

<style scoped>
.fade-transform-enter-active,
.fade-transform-leave-active {
  transition: all 0.2s;
}

.fade-transform-enter-from {
  opacity: 0;
  transform: translateX(-10px);
}

.fade-transform-leave-to {
  opacity: 0;
  transform: translateX(10px);
}
</style>
