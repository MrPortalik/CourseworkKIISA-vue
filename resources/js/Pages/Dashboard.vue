<script setup>
    import HeaderComponent from '@/Layouts/HeaderComponent.vue';
    import PageHead from '@/Components/PageHead.vue';
    import {Link} from "@inertiajs/vue3";

    const props = defineProps({
        articles: Object,
    });

    const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleDateString('ru-RU')
    }
</script>

<template>
    <PageHead
        title="Личный кабинет"
        description="Ваши опубликованные статьи и профиль автора на информационном портале КИИСА."
    />
    <HeaderComponent/>

    <section class="user">
        <div class="left">
            <figure class="profile">
                <img src="@/Assets/userLogo.png" alt="Картинка профиля" loading="lazy">
            </figure>

            <button class="actionBtn">Подписаться</button>
        </div>

        <div class="right">
            <!-- <p class="userName">{{ $page.props.user.name }}</p>
            <p class="userTag">{{ $page.props.user.email }}</p> -->
            <p class="txt">Информация о пользователе<br>
                О себе: “Lorem ipsum dolor sit amet consectetur adipiscing elit.
                Lorem ipsum dolor sit amet consectetur adipiscing elitб lorem ipsum dolor
                sit amet consectetur adipiscing elit. Lorem ipsum dolor sit amet consectetur
                adipiscing elit. Lorem ipsum dolor.” </p>
            <p>Работы:</p>

            <div> <!--v-if="articles.length" class="articles-grid"-->
              <article
                  v-for="article in articles"
                  :key="article.id"
                  class="article-card"
              >
                <Link :href="route('articles.show', article.slug)" class="card-link">
                  <h2 class="card-title">{{ article.title }}</h2>
                  <div class="card-meta">
                      <span class="author">{{ article.user?.name }}</span>
                      <span class="date">{{ formatDate(article.created_at) }}</span>
                  </div>
                  <div v-if="!article.is_publishable" class="card-draft">
                          *Верификация статьи
                  </div>
                </Link>
              </article>
            </div>

            <div class="empty-state">
                <h3>У пользователя нет созданных работ</h3>
            </div>

            <nav v-if="articles.length > 3" class="pagination">
                <Link
                    v-for="(link, index) in articles.links"
                    :key="index"
                    :href="link.url || '#'"
                    class="page-link"
                    :class="{
                    'active': link.active,
                    'disabled': !link.url
                    }"
                    v-html="link.label"
                    :preserve-scroll="true"
                />
            </nav>
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

    .empty-state h3 {
        font-size: clamp(1.5rem, 6vw, 45px);
        text-align: center;
        margin-top: 15%;
    }

    @media (max-width: 768px) {
        .user {
            flex-direction: column;
            align-items: center;
        }

        .right {
            margin-top: 1.5rem;
            margin-left: 0;
            width: 100%;
            padding: 0 1rem;
            box-sizing: border-box;
        }

        .profile img {
            width: min(220px, 70vw);
            height: min(220px, 70vw);
        }

        .actionBtn {
            width: min(356px, 100%);
            margin-top: 1.5rem;
        }
    }
</style>
