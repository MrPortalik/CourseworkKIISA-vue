<script setup>
    import HeaderComponent from '@/Layouts/HeaderComponent.vue'; 
</script>

<template>
    <HeaderComponent/>

    <section class="user">
        <div class="left">
            <figure class="profile">
                <img src="@/Assets/userLogo.png" alt="Картинка профиля" loading="lazy">
            </figure>

            <button class="actionBtn">Подписаться</button>
        </div>

        <div class="right">
            <p class="userName">{{ $page.props.auth.user.name }}</p>
            <p class="userTag">{{ $page.props.auth.user.email }}</p>
            <p class="txt">Информация о пользователе<br>
                О себе: “Lorem ipsum dolor sit amet consectetur adipiscing elit. 
                Lorem ipsum dolor sit amet consectetur adipiscing elitб lorem ipsum dolor 
                sit amet consectetur adipiscing elit. Lorem ipsum dolor sit amet consectetur 
                adipiscing elit. Lorem ipsum dolor.”
            <p>Работы:</p>

            <div class="cards">
                <div class="card"></div>
                <div class="card"></div>
                <div class="card"></div>
                <div class="card"></div>
                <div class="card"></div>
                <div class="card"></div>
            </div>
</p>
        </div>
    </section>
    <section class="actions">

    </section>
</template>

<style>
    .user {
        display: flex;
    }

    .profile {
        margin-top: 35px;
    }

    .profile img {
        width: 356px;
        height: 356px;
    }

    .right {
        margin-top: 76px;
        margin-left: 72px;
    }

    .userName {
        font-size: 58px;
    }

     .userTag {
        font-size: 48px;
        margin-bottom: 23px;
    }

    .txt {
        width: 1188px;
        margin-bottom: 20px;
    }

    .actionBtn {
        background-color: #0DB7FF;
        color: white;
        border-radius: 20px;
        border: 0;
        width: 356px;
        height: 60px;
        font-weight: 550;
        font-size: 24px;
        margin-top: 57px;
    }

    .cards { 
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 120px;
    }

    .card {
        width: 580px;
        height: 270px;
        background-color: #D9D9D9;
        border-radius: 10px;
    }
</style>