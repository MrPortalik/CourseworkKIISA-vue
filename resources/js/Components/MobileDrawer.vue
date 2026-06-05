<script setup>
import { onMounted, onUnmounted, toRef } from 'vue'
import { useBodyScrollLock } from '@/composables/useBodyScrollLock'

const props = defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: 'Меню' },
    placement: {
        type: String,
        default: 'side',
        validator: (value) => ['side', 'top'].includes(value),
    },
})

const emit = defineEmits(['close'])

useBodyScrollLock(toRef(props, 'open'))

const onKeydown = (event) => {
    if (event.key === 'Escape' && props.open) {
        emit('close')
    }
}

onMounted(() => {
    document.addEventListener('keydown', onKeydown)
})

onUnmounted(() => {
    document.removeEventListener('keydown', onKeydown)
})
</script>

<template>
    <Teleport to="body">
        <Transition name="mobile-drawer">
            <div v-if="open" class="mobile-drawer-root">
                <div class="mobile-drawer-backdrop" aria-hidden="true" @click="emit('close')" />
                <aside
                    class="mobile-drawer-panel"
                    :class="{ 'mobile-drawer-panel--top': placement === 'top' }"
                    role="dialog"
                    :aria-label="title"
                >
                    <div class="mobile-drawer-header">
                        <h2 class="mobile-drawer-title">{{ title }}</h2>
                        <button type="button" class="mobile-drawer-close" aria-label="Закрыть" @click="emit('close')">
                            ×
                        </button>
                    </div>
                    <div class="mobile-drawer-body">
                        <slot />
                    </div>
                </aside>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.mobile-drawer-root {
    position: fixed;
    inset: 0;
    z-index: 300;
}

.mobile-drawer-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
}

.mobile-drawer-panel {
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    width: min(320px, 88vw);
    background: var(--sidebar-bg, var(--dark_black));
    color: #fff;
    display: flex;
    flex-direction: column;
    box-shadow: 4px 0 24px rgba(0, 0, 0, 0.25);
}

.mobile-drawer-panel--top {
    top: 0;
    right: 0;
    bottom: auto;
    left: 0;
    width: 100%;
    max-height: min(75vh, 100%);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
}

.mobile-drawer-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    flex-shrink: 0;
}

.mobile-drawer-title {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 600;
    font-family: "Oswald", sans-serif;
}

.mobile-drawer-close {
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    font-size: 1.75rem;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: inherit;
}

.mobile-drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 0.75rem 0 1.5rem;
    -webkit-overflow-scrolling: touch;
}

.mobile-drawer-enter-active .mobile-drawer-panel,
.mobile-drawer-leave-active .mobile-drawer-panel {
    transition: transform 0.25s ease;
}

.mobile-drawer-enter-active .mobile-drawer-backdrop,
.mobile-drawer-leave-active .mobile-drawer-backdrop {
    transition: opacity 0.25s ease;
}

.mobile-drawer-enter-from .mobile-drawer-panel:not(.mobile-drawer-panel--top),
.mobile-drawer-leave-to .mobile-drawer-panel:not(.mobile-drawer-panel--top) {
    transform: translateX(-100%);
}

.mobile-drawer-enter-from .mobile-drawer-panel--top,
.mobile-drawer-leave-to .mobile-drawer-panel--top {
    transform: translateY(-100%);
}

.mobile-drawer-enter-from .mobile-drawer-backdrop,
.mobile-drawer-leave-to .mobile-drawer-backdrop {
    opacity: 0;
}
</style>
