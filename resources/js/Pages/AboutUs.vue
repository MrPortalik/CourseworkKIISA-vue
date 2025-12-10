<script setup>
    import HeaderComponent from '@/Layouts/HeaderComponent.vue';
</script>

<template>
    <HeaderComponent/>

    <section class="aboutUs">
        <h1>О нас</h1>

        <p>Мы творческое объединение “Я не придумал название, обсужу со своими потом” 
            и с 2024 года мы стараемся над тем, чтобы сюжет нашей вселенной был интересным и полным, 
            а также имел низкий порог вхождения. <br>
        КИИСА это один из проектов, составляющих глобальный сюжет. Часто в статьях вы сможете наткнуться 
        на упоминания персонажей или явлений, упоминаемых в других проектах (Геалис, Общий лор и др.), 
        а также увидеть упоминания явлений или персонажей КИИСА в подобных проектах. <br>
        КИИСА не является закрытым проектом, на портале будут публиковаться как официальные статьи от издателя, так и утверждённые издателем статьи пользователей портала. Обновлением, добавлением и удалением статей занимается администрация.
        Если у вас остались какие либо вопросы вы можете посетить вкладку FAQ.
        </p>

        <button class="actionBtn">FAQ</button>
    </section>
    
</template>

<style>
.actionBtn {
        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: center;
        padding: 16px 60px;
        gap: 10px;
        background: #0DB7FF;
        border-radius: 20px;
        font-weight: 700;
        font-size: 24px;
        margin-top: 57px;
    }

.aboutUs p {
    font-size: 36px;
}

.aboutUs h1, .aboutUs p, .aboutUs button {
    margin-top: 35px;
}

.aboutUs {
    margin-bottom: 30px;
}
</style>