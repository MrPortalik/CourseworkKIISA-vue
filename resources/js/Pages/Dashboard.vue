<script setup>
    import HeaderComponent from '@/Layouts/HeaderComponent.vue'; 

    const props = defineProps({
        articles: Object,
    })

    const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleDateString('ru-RU')
    }
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
            <!-- <p class="userName">{{ $page.props.user.name }}</p>
            <p class="userTag">{{ $page.props.user.email }}</p> -->
            <p class="txt">Информация о пользователе<br>
                О себе: “Lorem ipsum dolor sit amet consectetur adipiscing elit. 
                Lorem ipsum dolor sit amet consectetur adipiscing elitб lorem ipsum dolor 
                sit amet consectetur adipiscing elit. Lorem ipsum dolor sit amet consectetur 
                adipiscing elit. Lorem ipsum dolor.” </p>
            <p>Работы:</p>

            <div v-if="articles.data.length" class="articles-grid">
              <article 
                  v-for="article in articles.data" 
                  :key="article.id" 
                  class="article-card"
              >
                <Link :href="route('articles.show', article.slug)" class="card-link">
                  <h2 class="card-title">{{ article.title }}</h2>
                  <div class="card-meta">
                      <span class="author">{{ article.user?.name }}</span>
                      <span class="date">{{ formatDate(article.created_at) }}</span>
                  </div>
                  <div v-if="!article.is_published" class="card-draft">
                          *Не опубликовано
                  </div>
                </Link>
              </article>
            </div>
            
            <div v-else class="empty-state">
            <p>У пользователя нет созданных работ</p>
            </div>

            <nav v-if="articles.data.length && articles.links.length > 3" class="pagination">
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
</style>