import { watch, onUnmounted } from 'vue'

export function useBodyScrollLock(sourceRef) {
    const apply = (locked) => {
        if (typeof document === 'undefined') {
            return
        }
        document.body.style.overflow = locked ? 'hidden' : ''
    }

    watch(sourceRef, (locked) => apply(locked), { immediate: true })

    onUnmounted(() => apply(false))
}
