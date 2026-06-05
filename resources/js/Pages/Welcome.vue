<script setup>
import HeaderComponent from '@/Layouts/HeaderComponent.vue';
import {Head} from "@inertiajs/vue3";
</script>

<template>
    <Head title="Лендинг" />
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

    .landing h1 {
        padding-top: 271px;
        padding-left: 510px;
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
        padding-right: 543px;
        margin: 0;
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
            text-align: left;
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
