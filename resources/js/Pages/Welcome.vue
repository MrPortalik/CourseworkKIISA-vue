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
            background-size: 120% auto;
            background-position: center 32%;
        }

        .landing-content {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 1.5rem;
            min-height: calc(100dvh - 72px);
            padding: 0 1.25rem 2rem;
            box-sizing: border-box;
        }

        .landing h1 {
            padding: 4.5rem 0 0;
            font-size: clamp(1.15rem, 4.5vw, 1.75rem);
            line-height: 1.2;
            max-width: 88%;
        }

        .landing p {
            padding: 0;
            align-self: flex-end;
            font-size: clamp(2.25rem, 13vw, 3.5rem);
            text-align: right;
        }
    }

    @media (max-width: 480px) {
        .landing {
            background-size: 145% auto;
            background-position: center 38%;
        }

        .landing-content {
            padding-inline: 1rem;
        }

        .landing h1 {
            padding-top: 3.25rem;
            max-width: 95%;
            font-size: clamp(1rem, 4.8vw, 1.35rem);
        }

        .landing p {
            font-size: clamp(1.85rem, 15vw, 2.75rem);
        }
    }

</style>
