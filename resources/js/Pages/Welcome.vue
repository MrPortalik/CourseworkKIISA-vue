<script setup>
import HeaderComponent from '@/Layouts/HeaderComponent.vue';
import PageHead from '@/Components/PageHead.vue';
</script>

<template>
    <PageHead
        title="Главная"
        description="Портал КИИСА: тематические истории и статьи собственной вселенной. Читайте официальные и пользовательские материалы."
    />
    <main class="landing">
        <HeaderComponent/>
        <div class="landing-content">
            <h1>ИНФОРМАЦИОННЫЙ ПОРТАЛ<br>СОБСТВЕННЫХ ТЕМАТИЧЕСКИХ ИСТОРИЙ</h1>
            <p>КИИСА</p>
        </div>
    </main>
</template>

<style>
    .landing {
        background-image: url('/Assets/landingBackground.png');
        background-color: #0f0f10;
        position: relative;
        width: 100%;
        max-width: 100%;
        overflow: hidden;
        box-sizing: border-box;
        height: 960px;
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
    }

    [data-theme="light"] .landing,
    [data-theme="dark"] .landing {
        background-image: url('/Assets/landingBackground.png');
    }

    .landing-content {
        position: relative;
        width: 100%;
        max-width: 1920px;
        height: 100%;
        margin: 0 auto;
    }

    .landing h1 {
        position: absolute;
        top: 28.23%;
        left: 26.56%;
        padding: 0;
        margin: 0;
        max-width: min(42%, 720px);
        box-sizing: border-box;
        color: white;
        font-size: clamp(1.35rem, 3.02vw, 58px);
        line-height: 1.15;
    }

    .landing p {
        position: absolute;
        right: 28.28%;
        bottom: 12.5%;
        padding: 0;
        margin: 0;
        font-family: "Oswald", sans-serif;
        font-weight: 400;
        font-size: clamp(2rem, 3.02vw, 58px);
        line-height: 1;
        color: white;
        box-sizing: border-box;
    }

    @media (max-width: 768px) {
        .landing {
            height: auto;
            min-height: calc(100dvh - 72px);
            background-color: #110000;
            background-size: contain;
            background-position: center 50%;
        }

        [data-theme="light"] .landing,
        [data-theme="dark"] .landing {
            background-color: #110000;
        }

        .landing-content {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 1rem;
            min-height: calc(100dvh - 72px);
            padding: 1.25rem 1.25rem 2rem;
            box-sizing: border-box;
            position: relative;
            z-index: 1;
        }

        .landing p {
            position: static;
            order: 1;
            align-self: flex-start;
            font-size: clamp(3rem, 16vw, 4.5rem);
            line-height: 1;
            text-align: left;
        }

        .landing h1 {
            position: static;
            order: 2;
            margin-top: auto;
            max-width: 100%;
            font-size: clamp(1.35rem, 5.5vw, 2rem);
            line-height: 1.25;
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
