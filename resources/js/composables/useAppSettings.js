import { ref, watch } from 'vue'

const theme = ref(localStorage.getItem('app-theme') || 'light')
const fontScale = ref(parseFloat(localStorage.getItem('app-font-scale') || '1'))

function applySettings() {
    document.documentElement.dataset.theme = theme.value
    document.documentElement.style.setProperty('--font-scale', fontScale.value)
    document.documentElement.style.fontSize = `${fontScale.value * 100}%`
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
        setFontScale: (v) => { fontScale.value = Math.min(1.5, Math.max(1, v)) },
        applySettings,
    }
}
