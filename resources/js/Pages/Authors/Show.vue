<script setup>
import HeaderComponent from '@/Layouts/HeaderComponent.vue'
import ArticleCard from '@/Components/Articles/ArticleCard.vue'
import EditPencilIcon from '@/Components/EditPencilIcon.vue'
import UserAvatar from '@/Components/User/UserAvatar.vue'
import UserRoleBadge from '@/Components/UI/UserRoleBadge.vue'
import ProfileAccountSettings from '@/Components/Profile/ProfileAccountSettings.vue'
import FeedbackModal from '@/Components/FeedbackModal.vue'
import BlockUserModal from '@/Components/Admin/BlockUserModal.vue'
import { Link, router, useForm, usePage } from '@inertiajs/vue3'
import { imageAccept, validateImageFile } from '@/lib/imageUpload'
import PageHead from '@/Components/PageHead.vue'
import { computed, ref } from 'vue'

const props = defineProps({
    author: Object,
    articles: Object,
    isSubscribed: Boolean,
    isOwnProfile: Boolean,
    mustVerifyEmail: { type: Boolean, default: false },
    status: { type: String, default: null },
    canManageRoles: { type: Boolean, default: false },
    canManageUser: { type: Boolean, default: false },
    articlesCount: { type: Number, default: 0 },
    subscribersCount: { type: Number, default: 0 },
    subscriptionsCount: { type: Number, default: 0 },
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
    const error = validateImageFile(file)
    if (error) {
        alert(error)
        e.target.value = ''
        return
    }
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

const subscribeOpts = { preserveScroll: true, only: ['articles', 'isSubscribed', 'subscribersCount'] }

const showSettingsModal = ref(false)

const toggleSubscribe = () => {
    if (props.isSubscribed) {
        router.delete(route('authors.unsubscribe', props.author.id), subscribeOpts)
    } else {
        router.post(route('authors.subscribe', props.author.id), {}, subscribeOpts)
    }
}

const showReportModal = ref(false)
const showBlockModal = ref(false)
const canReport = computed(() => isLoggedIn.value && !props.isOwnProfile)

const promote = () => router.post(route('admin.users.promote', props.author.id), {}, { preserveScroll: true })
const demote = () => router.post(route('admin.users.demote', props.author.id), {}, { preserveScroll: true })
const unblock = () => router.post(route('admin.users.unblock', props.author.id), {}, { preserveScroll: true })

const formatBlockUntil = (value) => {
    if (!value) return ''
    return new Date(value).toLocaleString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}
const destroyUser = () => {
    if (confirm('Удалить пользователя безвозвратно?')) {
        router.delete(route('admin.users.destroy', props.author.id))
    }
}

const roleLabel = (role) => {
    if (role === 'owner') return 'Владелец'
    if (role === 'admin') return 'Администратор'
    return 'Пользователь'
}

const showModeration = computed(() => props.canManageRoles || props.canManageUser)
</script>

<template>
    <PageHead
        :title="author.name"
        :description="`Страница автора ${author.name} на портале КИИСА: биография и опубликованные статьи.`"
    />
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
                <input ref="avatarInput" type="file" :accept="imageAccept" hidden @change="onAvatar" />
            </figure>

            <button
                v-if="isOwnProfile"
                type="button"
                class="actionBtn actionBtn--edit"
                @click="showSettingsModal = true"
            >
                <span class="actionBtn-text">Изменить данные</span>
            </button>

            <button
                v-else-if="isLoggedIn"
                class="actionBtn"
                :class="{ subscribed: isSubscribed }"
                @click="toggleSubscribe"
            >
                <span class="actionBtn-text">{{ isSubscribed ? 'Отписаться' : 'Подписаться' }}</span>
            </button>

            <button
                v-if="canReport"
                type="button"
                class="actionBtn actionBtn--report"
                @click="showReportModal = true"
            >
                <span class="actionBtn-text">Пожаловаться</span>
            </button>

            <p class="subscription-stats">
                <span>Подписчиков: {{ subscribersCount }}</span>
                <span class="stats-sep">|</span>
                <span>Подписок: {{ subscriptionsCount }}</span>
            </p>
        </div>

        <div class="right">
            <p class="userName">
                {{ author.name }}
                <UserRoleBadge :user="author" variant="profile" />
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
                        <button type="button" class="save-bio btn-accent" :disabled="profileForm.processing" @click="saveBio">
                            {{ profileForm.processing ? 'Сохранение...' : 'Сохранить' }}
                        </button>
                        <button type="button" class="cancel-bio" :disabled="profileForm.processing" @click="editingBio = false">
                            Отмена
                        </button>
                    </div>
                </div>
            </div>
            <p v-else-if="author.bio" class="txt">{{ author.bio }}</p>

            <div v-if="showModeration" class="moderation-panel">
                <h3 class="moderation-heading">Модерация</h3>
                <p class="moderation-meta">
                    {{ author.email }} · {{ roleLabel(author.role) }} · Статей: {{ articlesCount }}
                </p>
                <template v-if="author.is_blocked">
                    <p class="moderation-blocked">Заблокирован</p>
                    <p v-if="author.block_reason" class="moderation-block-reason">{{ author.block_reason }}</p>
                    <p v-if="!author.blocked_until" class="moderation-block-meta">Перманентная блокировка</p>
                    <p v-else class="moderation-block-meta">До: {{ formatBlockUntil(author.blocked_until) }}</p>
                </template>
                <div class="moderation-actions">
                    <Link :href="route('admin.users.show', author.id)" class="mod-btn mod-btn--link">
                        Админ-панель пользователя
                    </Link>
                    <template v-if="canManageRoles">
                        <button v-if="author.role === 'user'" type="button" class="mod-btn mod-btn--promote" @click="promote">
                            Назначить администратором
                        </button>
                        <button v-if="author.role === 'admin'" type="button" class="mod-btn mod-btn--warn" @click="demote">
                            Снять права администратора
                        </button>
                        <button v-if="author.role !== 'owner'" type="button" class="mod-btn mod-btn--danger" @click="destroyUser">
                            Удалить
                        </button>
                    </template>
                    <template v-if="canManageUser">
                        <button v-if="!author.is_blocked" type="button" class="mod-btn mod-btn--danger" @click="showBlockModal = true">
                            Заблокировать
                        </button>
                        <button v-else type="button" class="mod-btn" @click="unblock">
                            Разблокировать
                        </button>
                    </template>
                </div>
            </div>

            <p class="works-label">Работы:</p>

            <div v-if="articles.data?.length" class="cards articles-grid articles-grid--6">
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

    <ProfileAccountSettings
        v-if="isOwnProfile"
        :open="showSettingsModal"
        :must-verify-email="mustVerifyEmail"
        :status="status"
        @close="showSettingsModal = false"
    />

    <FeedbackModal
        :open="showReportModal"
        type="user_complaint"
        :reported-user-id="author.id"
        @close="showReportModal = false"
    />

    <BlockUserModal
        v-if="canManageUser"
        :open="showBlockModal"
        :user-id="author.id"
        @close="showBlockModal = false"
    />
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
.user {
    display: flex;
    max-width: 1620px;
    width: 100%;
    margin: 0 auto;
    padding: 0 1rem;
    gap: 2rem;
    box-sizing: border-box;
    overflow-x: clip;
}
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
.cancel-bio { background: #edf2f7; color: #2d3748; border: none; padding: 0.6rem 1.25rem; border-radius: 8px; cursor: pointer; font-weight: 600; }
[data-theme="dark"] .cancel-bio { background: #2a2a2a; color: #f0f0f0; }
.txt { max-width: 100%; margin-bottom: 20px; font-size: calc(1.1rem * var(--font-scale, 1)); line-height: 1.6; }
.works-label { font-weight: 600; margin-bottom: 1rem; }
.actionBtn {
    background: #0db7ff;
    color: #ffffff;
    border-radius: 20px;
    border: 2px solid #0db7ff;
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
    background: #ffffff;
    color: #0db7ff;
    border-color: #0db7ff;
}
[data-theme="dark"] .actionBtn {
    background: #ffffff;
    color: #0a0a0a;
    border-color: #ffffff;
}
[data-theme="dark"] .actionBtn:not(.subscribed):not(.actionBtn--edit):not(.actionBtn--report) {
    background: #ffffff;
    color: #0a0a0a;
    border-color: #ffffff;
}
[data-theme="dark"] .actionBtn.subscribed {
    background: #b0b0b0;
    color: #1a1a1a;
    border-color: #b0b0b0;
}
.actionBtn--edit {
    background: var(--theme_black, #151515);
    color: #ffffff;
    border-color: var(--theme_black, #151515);
}
.actionBtn--edit:hover {
    background: #2d2d2d;
    border-color: #2d2d2d;
}
[data-theme="dark"] .actionBtn--edit {
    background: #ffffff;
    color: #0a0a0a;
    border-color: #ffffff;
}
[data-theme="dark"] .actionBtn--edit:hover {
    background: #f0f0f0;
    border-color: #f0f0f0;
}
.actionBtn--report {
    background: #e53e3e;
    color: #ffffff;
    border-color: #e53e3e;
    margin-top: 1rem;
}
.actionBtn--report:hover {
    background: #c53030;
    color: #ffffff;
    border-color: #c53030;
}
[data-theme="dark"] .actionBtn--report {
    background: #e53e3e;
    color: #ffffff;
    border-color: #e53e3e;
}
[data-theme="dark"] .actionBtn--report:hover {
    background: #c53030;
    color: #ffffff;
    border-color: #c53030;
}
.subscription-stats {
    margin-top: 1rem;
    font-size: 1rem;
    font-weight: 500;
    color: #718096;
    text-align: center;
    width: min(356px, 100%);
    line-height: 1.4;
}
.stats-sep {
    margin: 0 0.5rem;
    color: #a0aec0;
}
[data-theme="dark"] .subscription-stats {
    color: #aaa;
}
.cards {
    margin-bottom: 4rem;
}
@media (max-width: 900px) {
    .cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 768px) {
    .user {
        flex-direction: column;
        align-items: center;
        padding-top: 0.5rem;
    }
    .left {
        width: 100%;
    }
    .profile { margin-top: 1rem; }
    .avatar-round,
    .left :deep(.user-avatar) {
        width: min(220px, 70vw) !important;
        height: min(220px, 70vw) !important;
    }
    .right {
        margin-top: 1.5rem;
        width: 100%;
    }
    .actionBtn {
        margin-top: 1.5rem;
        min-width: 0;
    }
    .actionBtn--report {
        margin-top: 1rem;
    }
}
@media (max-width: 520px) {
    .cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
.moderation-panel {
    margin-bottom: 1.75rem;
    padding: 1rem 1.15rem;
    border: 1px solid #fde68a;
    border-radius: 10px;
    background: #fffbeb;
    max-width: 800px;
}
.moderation-heading {
    margin: 0 0 0.5rem;
    font-size: 1.15rem;
    font-weight: 600;
}
.moderation-meta {
    margin: 0 0 0.75rem;
    color: #718096;
    font-size: 0.9rem;
}
.moderation-blocked {
    color: #c53030;
    font-weight: 700;
    margin: 0 0 0.35rem;
}
.moderation-block-reason,
.moderation-block-meta {
    color: #c53030;
    margin: 0 0 0.35rem;
    font-size: 0.9rem;
    line-height: 1.4;
}
.moderation-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.mod-btn {
    padding: 0.5rem 0.9rem;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.85rem;
    font-family: inherit;
    background: #0db7ff;
    color: #fff;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}
.mod-btn--promote { background: #48bb78; color: #fff; }
.mod-btn--warn { background: #f59e0b; }
.mod-btn--danger { background: #e53e3e; }
.mod-btn--link { background: #edf2f7; color: #2d3748; }
[data-theme="dark"] .moderation-panel {
    background: rgba(254, 249, 195, 0.08);
    border-color: rgba(253, 224, 71, 0.35);
}
[data-theme="dark"] .mod-btn {
    background: transparent;
    border: 2px solid #ffffff;
    color: #ffffff;
}
[data-theme="dark"] .mod-btn:hover {
    background: rgba(255, 255, 255, 0.08);
}
[data-theme="dark"] .mod-btn--danger {
    background: transparent;
    color: #fc8181;
    border-color: #e53e3e;
}
[data-theme="dark"] .mod-btn--danger:hover {
    background: rgba(229, 62, 62, 0.12);
}
[data-theme="dark"] .mod-btn--promote {
    background: transparent;
    color: #9ae6b4;
    border-color: #68d391;
}
[data-theme="dark"] .mod-btn--promote:hover {
    background: rgba(104, 211, 145, 0.12);
}
[data-theme="dark"] .mod-btn--warn {
    background: transparent;
    color: #fbd38d;
    border-color: #f59e0b;
}
[data-theme="dark"] .mod-btn--warn:hover {
    background: rgba(245, 158, 11, 0.12);
}
[data-theme="dark"] .mod-btn--link {
    background: transparent;
    color: #ffffff;
    border-color: #ffffff;
}
[data-theme="dark"] .mod-btn--link:hover {
    background: rgba(255, 255, 255, 0.08);
}
.empty-state h3 { color: #718096; text-align: center; margin-top: 2rem; }
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
