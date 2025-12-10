<script setup>
import HeaderComponent from '@/Layouts/HeaderComponent.vue';
</script>

<template>
    <main class="landing">
        <HeaderComponent/>
        <h1>ИНФОРМАЦИОННЫЙ ПОРТАЛ<br>СОБСТВЕННЫХ ТЕМАТИЧЕСКИХ ИСТОРИЙ</h1>
        <p>КИИСА</p>
    </main>
</template>

<style>
    
    .landing {
        background-image: url('@/Assets/Background.png');
        position: relative;
        height: 960px;
        background-size: cover;
        background-repeat: no-repeat;
    }

    .landing h1 {
        padding-top: 271px;
        padding-left: 510px;
        justify-self: start;
        color: white;
    }

    .landing p {
        font-family: "Oswald", sans-serif;
        font-weight: 400;
        font-size: 58px;
        color: white;
        justify-self: end;
        padding-right: 543px;
    }

</style>