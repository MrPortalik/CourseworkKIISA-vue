<script setup>
import { computed, onMounted, ref } from 'vue'
import LegalDocumentModal from '@/Components/Legal/LegalDocumentModal.vue'
import { cookiePolicySections, cookiePolicyTitle } from '@/content/legal/cookiePolicy'
import { privacyPolicySections, privacyPolicyTitle } from '@/content/legal/privacyPolicy'
import { termsOfUseSections, termsOfUseTitle } from '@/content/legal/termsOfUse'
import { legalStandaloneHref } from '@/lib/legalPages'

const visible = ref(false)
const activeDocKey = ref(null)

const docs = {
    terms: {
        title: termsOfUseTitle,
        sections: termsOfUseSections,
        crossLinks: [{ key: 'privacy', label: 'Политика конфиденциальности' }],
    },
    privacy: {
        title: privacyPolicyTitle,
        sections: privacyPolicySections,
        crossLinks: [{ key: 'terms', label: 'Пользовательское соглашение' }],
    },
    cookies: {
        title: cookiePolicyTitle,
        sections: cookiePolicySections,
        crossLinks: [],
    },
}

const activeDoc = computed(() => (activeDocKey.value ? docs[activeDocKey.value] : null))
const activeDocStandaloneHref = computed(() => (
    activeDocKey.value ? legalStandaloneHref(activeDocKey.value) : null
))

const accept = () => {
    localStorage.setItem('cookie_consent', 'accepted')
    visible.value = false
}

const openDoc = (key) => {
    activeDocKey.value = key
}

const closeDoc = () => {
    activeDocKey.value = null
}

onMounted(() => {
    if (localStorage.getItem('cookie_consent') !== 'accepted') {
        visible.value = true
    }
})
</script>

<template>
    <Transition name="cookie-rise">
        <div v-if="visible" class="cookie-banner" role="dialog" aria-label="Согласие на использование cookie">
            <div class="cookie-banner__card">
                <div class="cookie-banner__icon" aria-hidden="true">
                    <svg viewBox="0 0 32 32" fill="none">
                        <circle cx="16" cy="16" r="13" stroke="currentColor" stroke-width="1.75" />
                        <circle cx="12" cy="13" r="1.6" fill="currentColor" />
                        <circle cx="19" cy="11" r="1.2" fill="currentColor" />
                        <circle cx="21" cy="17" r="1.5" fill="currentColor" />
                        <circle cx="14" cy="20" r="1.3" fill="currentColor" />
                        <circle cx="10" cy="17" r="0.9" fill="currentColor" />
                    </svg>
                </div>

                <div class="cookie-banner__body">
                    <p class="cookie-banner__title">Мы используем cookie</p>
                    <p class="cookie-banner__text">
                        Файлы cookie нужны для работы сайта, авторизации и сохранения ваших настроек.
                    </p>
                    <div class="cookie-banner__links">
                        <button type="button" class="cookie-banner__link" @click="openDoc('cookies')">Политика cookie</button>
                        <span class="cookie-banner__dot" aria-hidden="true">·</span>
                        <button type="button" class="cookie-banner__link" @click="openDoc('privacy')">Конфиденциальность</button>
                        <span class="cookie-banner__dot" aria-hidden="true">·</span>
                        <button type="button" class="cookie-banner__link" @click="openDoc('terms')">Соглашение</button>
                    </div>
                </div>

                <button type="button" class="cookie-banner__accept" @click="accept">Принять</button>
            </div>
        </div>
    </Transition>

    <LegalDocumentModal
        v-if="activeDoc"
        :title="activeDoc.title"
        :sections="activeDoc.sections"
        :cross-links="activeDoc.crossLinks"
        :standalone-href="activeDocStandaloneHref"
        :open="!!activeDoc"
        @close="closeDoc"
        @navigate="openDoc"
    />
</template>

<style scoped>
.cookie-banner {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 180;
    padding: 0 1.25rem calc(1.25rem + env(safe-area-inset-bottom, 0px));
    pointer-events: none;
}

.cookie-banner__card {
    pointer-events: auto;
    max-width: 920px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: 1rem 1.25rem;
    padding: 1.1rem 1.25rem;
    border-radius: 18px;
    border: 1px solid rgba(13, 183, 255, 0.28);
    background: linear-gradient(135deg, rgba(22, 22, 24, 0.97) 0%, rgba(14, 14, 16, 0.98) 100%);
    color: #f5f5f5;
    box-shadow:
        0 18px 48px rgba(0, 0, 0, 0.38),
        0 0 0 1px rgba(255, 255, 255, 0.04) inset;
    backdrop-filter: blur(14px);
}

.cookie-banner__icon {
    flex-shrink: 0;
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    background: rgba(13, 183, 255, 0.14);
    color: #67e8f9;
    box-shadow: 0 0 0 1px rgba(13, 183, 255, 0.22) inset;
}

.cookie-banner__icon svg {
    width: 1.85rem;
    height: 1.85rem;
}

.cookie-banner__body {
    flex: 1;
    min-width: 0;
}

.cookie-banner__title {
    margin: 0 0 0.3rem;
    font-size: 1.05rem;
    font-weight: 700;
    letter-spacing: 0.01em;
    color: #ffffff;
}

.cookie-banner__text {
    margin: 0 0 0.55rem;
    font-size: 0.9rem;
    line-height: 1.5;
    color: rgba(245, 245, 245, 0.82);
}

.cookie-banner__links {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.35rem 0.5rem;
}

.cookie-banner__link {
    background: none;
    border: none;
    padding: 0;
    color: #67e8f9;
    font: inherit;
    font-size: 0.84rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: color 0.15s, opacity 0.15s;
}

.cookie-banner__link:hover {
    color: #a5f3fc;
    text-decoration: underline;
}

.cookie-banner__dot {
    color: rgba(255, 255, 255, 0.35);
    font-size: 0.85rem;
    user-select: none;
}

.cookie-banner__accept {
    flex-shrink: 0;
    border: none;
    background: linear-gradient(180deg, #0db7ff 0%, #0aa5e8 100%);
    color: #ffffff;
    border-radius: 999px;
    padding: 0.65rem 1.5rem;
    font-weight: 700;
    font-size: 0.92rem;
    cursor: pointer;
    font-family: inherit;
    box-shadow: 0 6px 18px rgba(13, 183, 255, 0.35);
    transition: transform 0.15s, box-shadow 0.15s, filter 0.15s;
}

.cookie-banner__accept:hover {
    filter: brightness(1.06);
    box-shadow: 0 8px 22px rgba(13, 183, 255, 0.42);
    transform: translateY(-1px);
}

.cookie-banner__accept:active {
    transform: translateY(0);
}

[data-theme="light"] .cookie-banner__card {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    color: #1a202c;
    border-color: rgba(13, 183, 255, 0.35);
    box-shadow:
        0 16px 40px rgba(15, 23, 42, 0.12),
        0 0 0 1px rgba(226, 232, 240, 0.9) inset;
}

[data-theme="light"] .cookie-banner__icon {
    background: rgba(13, 183, 255, 0.1);
    color: #0db7ff;
}

[data-theme="light"] .cookie-banner__title {
    color: #1a202c;
}

[data-theme="light"] .cookie-banner__text {
    color: #4a5568;
}

[data-theme="light"] .cookie-banner__link {
    color: #0db7ff;
}

[data-theme="light"] .cookie-banner__link:hover {
    color: #0891d4;
}

[data-theme="light"] .cookie-banner__dot {
    color: #cbd5e0;
}

[data-theme="light"] .cookie-banner__accept {
    color: #ffffff;
}

.cookie-rise-enter-active,
.cookie-rise-leave-active {
    transition: opacity 0.35s ease, transform 0.35s ease;
}

.cookie-rise-enter-from,
.cookie-rise-leave-to {
    opacity: 0;
    transform: translateY(1.25rem);
}

@media (max-width: 768px) {
    .cookie-banner {
        padding-inline: 0.85rem;
    }

    .cookie-banner__card {
        flex-direction: column;
        align-items: stretch;
        padding: 1rem;
        border-radius: 16px;
    }

    .cookie-banner__icon {
        width: 2.75rem;
        height: 2.75rem;
    }

    .cookie-banner__accept {
        width: 100%;
        text-align: center;
    }
}
</style>
