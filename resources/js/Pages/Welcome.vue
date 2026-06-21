<script setup>
import { onMounted, onUnmounted } from 'vue'
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import PageHead from '@/Components/PageHead.vue'

const BROWSER_SIDEBAR_UA = /YaBrowser|OPR|Opera/i

const applyLandingViewportCompensation = () => {
    const root = document.documentElement
    const hasBrowserSidebar = BROWSER_SIDEBAR_UA.test(navigator.userAgent)
    const isDesktop = window.innerWidth >= 769

    if (!hasBrowserSidebar && isDesktop) {
        root.style.setProperty('--landing-effective-vw-scale', '1.25')
    } else {
        root.style.removeProperty('--landing-effective-vw-scale')
    }
}

onMounted(() => {
    applyLandingViewportCompensation()
    window.addEventListener('resize', applyLandingViewportCompensation, { passive: true })
})

onUnmounted(() => {
    window.removeEventListener('resize', applyLandingViewportCompensation)
    document.documentElement.style.removeProperty('--landing-effective-vw-scale')
})
</script>

<template>
    <PageHead
        title="Главная"
        description="Портал КИИСА: тематические истории и статьи собственной вселенной. Читайте официальные и пользовательские материалы."
    />

    <main class="landing">
        <HeaderComponent />
        <div class="landing-content">
            <h1>ИНФОРМАЦИОННЫЙ ПОРТАЛ<br>СОБСТВЕННЫХ ТЕМАТИЧЕСКИХ ИСТОРИЙ</h1>
            <p>КИИСА</p>
        </div>
    </main>
</template>

<style>
.landing {
    --landing-header-h: 130px;
    --landing-baseline-content-h: 950px;
    --landing-h1-offset: 306px;
    --landing-h1-left: 515px;
    --landing-p-right: 558px;
    --landing-wide-shift: 0.1390625;
    --landing-effective-vw-scale: 1;
    --landing-vw: calc(100vw * var(--landing-effective-vw-scale));
    --content-bg: rgba(17, 0, 0);
    background-image: url('/public/Assets/landingBackground.png');
    background-color: var(--content-bg);
    position: relative;
    width: 100%;
    max-width: 100%;
    overflow: hidden;
    box-sizing: border-box;
    min-height: 100vh;
    min-height: 100dvh;
    height: auto;
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center top;
}

[data-theme="light"] .landing,
[data-theme="dark"] .landing {
    --content-bg: rgba(17, 0, 0);
    background-image: url('/public/Assets/landingBackground.png');
    background-color: var(--content-bg);
}

body:has(.landing) {
    background: rgba(17, 0, 0);
}

.landing-content {
    position: relative;
    z-index: 1;
    min-height: calc(100dvh - var(--landing-header-h));
    box-sizing: border-box;
}

.landing h1 {
    padding-top: calc(var(--landing-h1-offset) / var(--landing-baseline-content-h) * (100dvh - var(--landing-header-h)));
    padding-left: calc(var(--landing-h1-left) + max(0px, (var(--landing-vw) - 1920px) * var(--landing-wide-shift)));
    justify-self: start;
    color: white;
    margin: 0;
    max-width: 100%;
    box-sizing: border-box;
}

.landing p {
    font-family: "Oswald", sans-serif;
    font-weight: 400;
    font-size: 58px;
    color: white;
    justify-self: end;
    padding-right: calc(var(--landing-p-right) - max(0px, (var(--landing-vw) - 1920px) * var(--landing-wide-shift)));
    margin: 0;
    box-sizing: border-box;
}

@media (min-width: 769px) and (max-width: 1400px) {
    .landing {
        background-size: cover;
        background-position: center center;
    }
}

@media (min-width: 1401px) {
    .landing {
        background-size: cover;
        background-position: center top;
    }
}

@media (max-width: 768px) {
    .landing {
        --landing-header-h: 72px;
        min-height: calc(100dvh - var(--landing-header-h));
        background-size: contain;
        background-position: center 50%;
    }

    .landing-content {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 1rem;
        min-height: calc(100dvh - var(--landing-header-h));
        padding: 1.25rem 1.25rem 2rem;
        box-sizing: border-box;
    }

    .landing p {
        order: 1;
        padding: 0;
        align-self: flex-start;
        font-size: clamp(3rem, 16vw, 4.5rem);
        line-height: 1;
        text-align: left;
    }

    .landing h1 {
        order: 2;
        padding: 0;
        margin-top: auto;
        font-size: clamp(1.35rem, 5.5vw, 2rem);
        line-height: 1.25;
        max-width: 100%;
        text-align: right;
    }
}

@media (max-width: 480px) {
    .landing {
        background-size: 115% auto;
        background-position: center 48%;
    }
    .landing-content {
        padding-inline: 1rem;
    }
    .landing p {
        font-size: clamp(2.75rem, 18vw, 3.75rem);
    }
    .landing h1 {
        font-size: clamp(1.2rem, 6vw, 1.75rem);
    }
}
</style>
