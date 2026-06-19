<script setup>
import { ref } from 'vue'
import { useAppSettings } from '@/composables/useAppSettings'
import CatCheckbox from '@/Components/UI/CatCheckbox.vue'

const open = ref(false)
const { theme, fontScale, setTheme, setFontScale } = useAppSettings()

const toggle = () => { open.value = !open.value }
const close = () => { open.value = false }
</script>

<template>
    <div class="settings-wrap">
        <button type="button" class="settings-trigger" title="Настройки" @click="toggle">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="3"/>
                <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
            </svg>
        </button>

        <div v-if="open" class="settings-backdrop" @click="close" />
        <div v-if="open" class="settings-panel">
            <h3>Настройки</h3>

            <label class="setting-row">
                <span>Тёмная тема</span>
                <CatCheckbox
                    variant="theme"
                    :checked="theme === 'dark'"
                    @change="setTheme($event.target.checked ? 'dark' : 'light')"
                />
            </label>

            <label class="setting-row">
                <span>Размер текста: {{ Math.round(fontScale * 100) }}%</span>
                <input
                    type="range"
                    min="1"
                    max="3"
                    step="0.05"
                    :value="fontScale"
                    @input="setFontScale(parseFloat($event.target.value))"
                />
            </label>

            <button type="button" class="close-btn" @click="close">Закрыть</button>
        </div>
    </div>
</template>

<style scoped>
.settings-wrap { position: relative; }
.settings-trigger {
    background: transparent;
    border: none;
    color: #ffffff;
    cursor: pointer;
    padding: 0.25rem;
    display: flex;
    align-items: center;
    min-width: 44px;
    min-height: 44px;
    justify-content: center;
}
.settings-backdrop {
    position: fixed;
    inset: 0;
    z-index: 90;
}
.settings-panel {
    position: absolute;
    right: 0;
    top: calc(100% + 8px);
    z-index: 100;
    width: 280px;
    background: #fff;
    color: #1a202c;
    border-radius: 12px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.2);
    padding: 1.25rem;
}
.settings-panel h3 { margin: 0 0 1rem; font-size: 1.1rem; color: #1a202c; }
.setting-row {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: #1a202c;
}
.close-btn {
    width: 100%;
    margin-top: 0.5rem;
    padding: 0.55rem;
    border: none;
    background: #edf2f7;
    color: #1a202c;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}
[data-theme="dark"] .settings-panel {
    background: #1e1e1e;
    color: #f0f0f0;
}
[data-theme="dark"] .settings-panel h3,
[data-theme="dark"] .setting-row {
    color: #f0f0f0;
}
[data-theme="dark"] .close-btn {
    background: #e8e8e8;
    color: #111;
}

@media (max-width: 768px) {
    .settings-panel {
        position: fixed;
        left: 1rem;
        right: 1rem;
        top: auto;
        bottom: 1rem;
        width: auto;
        max-width: none;
    }
}
</style>
