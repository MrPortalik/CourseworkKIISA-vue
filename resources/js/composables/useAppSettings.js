import { ref, watch } from 'vue'

const theme = ref(localStorage.getItem('app-theme') || 'light')
const fontScale = ref(parseFloat(localStorage.getItem('app-font-scale') || '1'))

function applySettings() {
    document.documentElement.dataset.theme = theme.value
    document.documentElement.style.setProperty('--font-scale', String(fontScale.value))
    document.documentElement.dataset.fontScale = fontScale.value >= 1.75 ? 'large' : 'normal'
    document.documentElement.style.removeProperty('font-size')
}

if (typeof window !== 'undefined') {
    applySettings()
}

watch(theme, (v) => {
    localStorage.setItem('app-theme', v)
    applySettings()
})

watch(fontScale, (v) => {
    localStorage.setItem('app-font-scale', String(v))
    applySettings()
})

export function useAppSettings() {
    return {
        theme,
        fontScale,
        setTheme: (v) => { theme.value = v },
        setFontScale: (v) => { fontScale.value = Math.min(3, Math.max(1, v)) },
        applySettings,
    }
}
