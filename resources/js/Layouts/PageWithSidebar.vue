<script setup>
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import SideMenuComponent from '@/Layouts/SideMenuComponent.vue'
</script>

<template>
    <HeaderComponent />
    <div class="page-divider">
        <SideMenuComponent />
        <main class="page-main">
            <slot />
        </main>
    </div>
</template>

<style scoped>
.page-divider {
    display: flex;
    align-items: stretch;
    min-height: calc(100vh - 130px);
}
.page-main {
    flex: 1;
    min-width: 0;
    padding: 2rem 2.5rem 3rem;
    background: var(--content-bg);
}
</style>
