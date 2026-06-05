<script setup>
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import SideMenuComponent from '@/Layouts/SideMenuComponent.vue'
import { useMobileSidebar } from '@/composables/useMobileSidebar'
import { useEdgeSwipeOpen } from '@/composables/useEdgeSwipeOpen'

const { open: openSidebar, toggle: toggleSidebar } = useMobileSidebar()
useEdgeSwipeOpen(openSidebar)
</script>

<template>
    <HeaderComponent />
    <div class="page-divider">
        <SideMenuComponent />
        <main class="page-main">
            <button type="button" class="mobile-sidebar-toggle" @click="toggleSidebar">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"/>
                </svg>
                Категории и подборки
            </button>
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

.mobile-sidebar-toggle {
    display: none;
    align-items: center;
    gap: 0.5rem;
    width: 100%;
    margin-bottom: 1rem;
    padding: 0.75rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    color: var(--page-text);
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    box-sizing: border-box;
}

@media (max-width: 768px) {
    .page-divider {
        min-height: calc(100vh - 72px);
    }

    .page-main {
        padding: 1rem 1rem 2rem;
        width: 100%;
    }

    .mobile-sidebar-toggle {
        display: inline-flex;
    }
}

[data-theme="dark"] .mobile-sidebar-toggle {
    background: var(--theme_black);
    border-color: #404040;
    color: #f0f0f0;
}
</style>
