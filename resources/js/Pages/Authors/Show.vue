<script setup>
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import ArticleCard from '@/Components/Articles/ArticleCard.vue'
import EditPencilIcon from '@/Components/EditPencilIcon.vue'
import UserAvatar from '@/Components/User/UserAvatar.vue'
import UserRoleBadge from '@/Components/UI/UserRoleBadge.vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    author: Object,
    articles: Object,
    isSubscribed: Boolean,
    isOwnProfile: Boolean,
})

const page = usePage()
const isLoggedIn = computed(() => !!page.props.auth?.user)
const avatarPreview = ref(props.author.avatar || null)
const editingBio = ref(false)
const avatarInput = ref(null)

const profileForm = useForm({
    bio: props.author.bio || '',
    avatar: null,
})

const onAvatar = (e) => {
    const file = e.target.files[0]
    if (!file) return
    profileForm.avatar = file
    avatarPreview.value = URL.createObjectURL(file)
    saveProfile()
}

const saveProfile = () => {
    profileForm.post(route('profile.dashboard.update'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            editingBio.value = false
        },
    })
}

const saveBio = () => {
    profileForm.avatar = null
    saveProfile()
}

const subscribeOpts = { preserveScroll: true, only: ['articles', 'isSubscribed'] }

const toggleSubscribe = () => {
    if (props.isSubscribed) {
        router.delete(route('authors.unsubscribe', props.author.id), subscribeOpts)
    } else {
        router.post(route('authors.subscribe', props.author.id), {}, subscribeOpts)
    }
}
</script>

<template>
    <Head :title="author.name" />
    <HeaderComponent />

    <div v-if="isOwnProfile && profileForm.processing" class="profile-loading">
        <span>Сохранение...</span>
    </div>

    <section class="user content-area">
        <div class="left">
            <figure class="profile">
                <img
                    v-if="avatarPreview"
                    :src="avatarPreview"
                    alt="Профиль"
                    class="avatar-round"
                    loading="lazy"
                />
                <UserAvatar v-else :src="null" alt="Профиль" :size="356" />
                <button
                    v-if="isOwnProfile"
                    type="button"
                    class="avatar-edit-btn"
                    title="Загрузить фото"
                    :disabled="profileForm.processing"
                    @click="avatarInput?.click()"
                >
                    <EditPencilIcon variant="light" />
                </button>
                <input ref="avatarInput" type="file" accept="image/*" hidden @change="onAvatar" />
            </figure>

            <button
                v-if="isLoggedIn && !isOwnProfile"
                class="actionBtn"
                :class="{ subscribed: isSubscribed }"
                @click="toggleSubscribe"
            >
                <span class="actionBtn-text">{{ isSubscribed ? 'Отписаться' : 'Подписаться' }}</span>
            </button>
        </div>

        <div class="right">
            <p class="userName">
                {{ author.name }}
                <UserRoleBadge :user="author" class="profile-role-badge" />
            </p>
            <p class="userTag">id_{{ author.id }}</p>

            <div v-if="isOwnProfile" class="bio-section">
                <div class="bio-header">
                    <h3 class="bio-label">О себе</h3>
                    <button
                        v-if="!editingBio"
                        type="button"
                        class="icon-btn"
                        title="Редактировать"
                        @click="editingBio = true"
                    >
                        <EditPencilIcon variant="light" class="bio-pencil" />
                    </button>
                </div>
                <p v-if="!editingBio" class="txt bio-text">{{ author.bio || 'Расскажите о себе...' }}</p>
                <div v-else class="bio-edit">
                    <textarea v-model="profileForm.bio" rows="5" placeholder="Расскажите о себе..." />
                    <div class="bio-actions">
                        <button type="button" class="save-bio" :disabled="profileForm.processing" @click="saveBio">
                            {{ profileForm.processing ? 'Сохранение...' : 'Сохранить' }}
                        </button>
                        <button type="button" class="cancel-bio" :disabled="profileForm.processing" @click="editingBio = false">
                            Отмена
                        </button>
                    </div>
                </div>
            </div>
            <p v-else-if="author.bio" class="txt">{{ author.bio }}</p>

            <p class="works-label">Работы:</p>

            <div v-if="articles.data?.length" class="cards">
                <ArticleCard
                    v-for="article in articles.data"
                    :key="article.id"
                    :article="article"
                    :show-author="false"
                    class="profile-card-wrap"
                />
            </div>
            <div v-else class="empty-state"><h3>Нет работ</h3></div>

            <nav v-if="articles.data?.length && articles.links?.length > 3" class="pagination">
                <Link
                    v-for="(link, index) in articles.links"
                    :key="index"
                    :href="link.url || '#'"
                    class="page-link"
                    :class="{ active: link.active, disabled: !link.url }"
                    v-html="link.label"
                />
            </nav>
        </div>
    </section>
</template>

<style scoped>
.profile-loading {
    position: fixed;
    top: 120px;
    right: 24px;
    z-index: 50;
    background: rgba(13, 183, 255, 0.95);
    color: #fff;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
}
.user { display: flex; max-width: 1620px; margin: 0 auto; padding: 0 1rem; gap: 2rem; }
.left {
    flex: 0 0 auto;
    width: min(356px, 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
}
.profile { margin-top: 35px; text-align: center; position: relative; display: inline-block; }
.avatar-round { width: 356px; height: 356px; border-radius: 50%; object-fit: cover; display: block; }
.avatar-edit-btn {
    position: absolute;
    bottom: 12px;
    right: 12px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid var(--header-bg, #050505);
    background: var(--header-bg, #050505);
    color: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.avatar-edit-btn:disabled { opacity: 0.6; cursor: wait; }
.right { margin-top: 76px; flex: 1; min-width: 0; }
.userName {
    font-size: clamp(2rem, 5vw, 58px);
    margin: 0;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.profile-role-badge {
    font-size: 0.85rem;
    vertical-align: middle;
}
.userTag { font-size: clamp(1.25rem, 3vw, 48px); margin-bottom: 23px; color: #718096; }
.bio-section { margin-bottom: 1.5rem; max-width: 800px; }
.bio-header { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; }
.bio-label { margin: 0; font-size: 1.25rem; font-weight: 600; }
.icon-btn {
    background: var(--theme_black);
    border: none;
    border-radius: 50%;
    cursor: pointer;
    padding: 0.35rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
.icon-btn :deep(.edit-pencil--light) { color: #ffffff; }
.bio-text { white-space: pre-wrap; }
.bio-edit textarea { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e0; border-radius: 8px; font-size: 1rem; }
.bio-actions { display: flex; gap: 0.75rem; margin-top: 0.75rem; }
.save-bio { background: #0db7ff; color: #fff; border: none; padding: 0.6rem 1.25rem; border-radius: 8px; cursor: pointer; font-weight: 600; }
.cancel-bio { background: #edf2f7; color: #2d3748; border: none; padding: 0.6rem 1.25rem; border-radius: 8px; cursor: pointer; font-weight: 600; }
[data-theme="dark"] .cancel-bio { background: #2a2a2a; color: #f0f0f0; }
.txt { max-width: 100%; margin-bottom: 20px; font-size: 1.1rem; line-height: 1.6; }
.works-label { font-weight: 600; margin-bottom: 1rem; }
.actionBtn {
    background: #ffffff;
    color: #151515;
    border-radius: 20px;
    border: 2px solid #ffffff;
    width: min(356px, 100%);
    min-width: min(356px, 100%);
    height: 60px;
    font-weight: 550;
    font-size: 1.25rem;
    margin-top: 57px;
    cursor: pointer;
    transition: background 0.2s, color 0.2s, border-color 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    flex-shrink: 0;
}
.actionBtn-text {
    display: block;
    min-width: 10.75rem;
    text-align: center;
}
.actionBtn.subscribed {
    background: #0db7ff;
    color: #ffffff;
    border-color: #0db7ff;
}
[data-theme="dark"] .actionBtn {
    background: #ffffff;
    color: #0a0a0a;
    border-color: #ffffff;
}
[data-theme="dark"] .actionBtn.subscribed {
    background: #b0b0b0;
    color: #1a1a1a;
    border-color: #b0b0b0;
}
.cards {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 20px;
    margin-bottom: 4rem;
}
@media (max-width: 1200px) {
    .cards { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
@media (max-width: 900px) {
    .cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 520px) {
    .cards { grid-template-columns: 1fr; }
}
.empty-state h3 { color: #718096; text-align: center; margin-top: 2rem; }
.pagination { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.page-link { padding: 0.5rem 1rem; border: 1px solid #e2e8f0; border-radius: 0.25rem; text-decoration: none; color: #4a5568; }
.page-link.active { background: #4299e1; color: white; }
[data-theme="dark"] .user { color: #f0f0f0; }
[data-theme="dark"] .bio-edit textarea {
    background: #141414;
    color: #f0f0f0;
    border-color: #404040;
}
[data-theme="dark"] .cards :deep(.article-card) {
    background: var(--theme_black);
    border-color: #333;
}
</style>
