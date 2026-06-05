import { onMounted, onUnmounted } from 'vue'

const EDGE_WIDTH = 28
const MIN_SWIPE_DISTANCE = 60
const MAX_VERTICAL_DRIFT = 80

export function useEdgeSwipeOpen(onOpen) {
    let startX = 0
    let startY = 0
    let tracking = false

    const isMobile = () => window.matchMedia('(max-width: 768px)').matches

    const onTouchStart = (event) => {
        if (!isMobile()) {
            return
        }

        const touch = event.touches[0]
        if (touch.clientX > EDGE_WIDTH) {
            return
        }

        startX = touch.clientX
        startY = touch.clientY
        tracking = true
    }

    const onTouchMove = (event) => {
        if (!tracking) {
            return
        }

        const touch = event.touches[0]
        const dx = touch.clientX - startX
        const dy = Math.abs(touch.clientY - startY)

        if (dy > MAX_VERTICAL_DRIFT && dy > Math.abs(dx)) {
            tracking = false
        }
    }

    const onTouchEnd = (event) => {
        if (!tracking) {
            return
        }

        tracking = false

        const touch = event.changedTouches[0]
        if (touch.clientX - startX >= MIN_SWIPE_DISTANCE) {
            onOpen()
        }
    }

    onMounted(() => {
        document.addEventListener('touchstart', onTouchStart, { passive: true })
        document.addEventListener('touchmove', onTouchMove, { passive: true })
        document.addEventListener('touchend', onTouchEnd, { passive: true })
    })

    onUnmounted(() => {
        document.removeEventListener('touchstart', onTouchStart)
        document.removeEventListener('touchmove', onTouchMove)
        document.removeEventListener('touchend', onTouchEnd)
    })
}
