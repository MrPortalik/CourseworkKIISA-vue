<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import ModalPanel from '@/Components/ModalPanel.vue'

const props = defineProps({
    articleSlug: { type: String, default: null },
    pendingCoauthors: { type: Array, default: () => [] },
    acceptedCoauthors: { type: Array, default: () => [] },
})

const pendingUserIds = defineModel('pendingUserIds', { type: Array, default: () => [] })

const open = ref(false)
const search = ref('')
const results = ref([])
const searching = ref(false)
const localPendingUsers = ref([])
let timer = null

const displayPending = computed(() =>
    props.articleSlug ? props.pendingCoauthors : localPendingUsers.value,
)

const searchUsers = () => {
    clearTimeout(timer)
    const term = search.value.trim()
    if (!term) {
        results.value = []
        return
    }
    timer = setTimeout(async () => {
        searching.value = true
        try {
            const { data } = await window.axios.get(route('users.search'), { params: { q: term } })
            results.value = data.users ?? []
        } catch {
            results.value = []
        } finally {
            searching.value = false
        }
    }, 300)
}

const invite = (user) => {
    if (props.articleSlug) {
        router.post(route('articles.coauthors.invite', props.articleSlug), { user_id: user.id }, {
            preserveScroll: true,
            onSuccess: () => {
                search.value = ''
                results.value = []
            },
        })
        return
    }

    if (pendingUserIds.value.includes(user.id)) {
        return
    }

    pendingUserIds.value = [...pendingUserIds.value, user.id]
    localPendingUsers.value = [...localPendingUsers.value, { id: user.id, name: user.name }]
    search.value = ''
    results.value = []
}

const removeLocal = (userId) => {
    pendingUserIds.value = pendingUserIds.value.filter((id) => id !== userId)
    localPendingUsers.value = localPendingUsers.value.filter((u) => u.id !== userId)
}
</script>

<template>
    <div class="coauthor-panel">
        <button type="button" class="picker-btn btn-accent" @click="open = true">
            Добавить со-автора
        </button>
        <p v-if="!articleSlug && localPendingUsers.length" class="hint">
            Приглашения будут отправлены после сохранения статьи.
        </p>

        <ul v-if="acceptedCoauthors.length" class="coauthor-list">
            <li v-for="user in acceptedCoauthors" :key="user.id">{{ user.name }} (со-автор)</li>
        </ul>
        <ul v-if="displayPending.length" class="coauthor-list pending">
            <li v-for="user in displayPending" :key="user.id" class="pending-row">
                <span>{{ user.name }} (ожидает ответа)</span>
                <button
                    v-if="!articleSlug"
                    type="button"
                    class="remove-pending"
                    aria-label="Убрать"
                    @click="removeLocal(user.id)"
                >
                    ×
                </button>
            </li>
        </ul>

        <ModalPanel title="Добавить со-автора" :open="open" @close="open = false">
            <input
                v-model="search"
                type="search"
                class="search-input"
                placeholder="ID или имя пользователя"
                @input="searchUsers"
            />
            <p v-if="searching" class="status">Поиск...</p>
            <ul v-else-if="results.length" class="results">
                <li v-for="user in results" :key="user.id">
                    <span>{{ user.name }} <small>id_{{ user.id }}</small></span>
                    <button type="button" class="invite-btn btn-accent" @click="invite(user)">Пригласить</button>
                </li>
            </ul>
            <p v-else-if="search.trim()" class="empty-hint">Никого не найдено</p>
            <template #footer>
                <button type="button" class="done-btn btn-accent" @click="open = false">Закрыть</button>
            </template>
        </ModalPanel>
    </div>
</template>

<style scoped>
.picker-btn {
    cursor: pointer;
    font-weight: 600;
}
.hint { font-size: 0.85rem; color: #718096; margin: 0.35rem 0 0; }
.coauthor-list { margin: 0.5rem 0 0; padding-left: 1.25rem; font-size: 0.9rem; }
.coauthor-list.pending { color: #718096; list-style: none; padding-left: 0; }
.pending-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.25rem 0;
}
.remove-pending {
    background: none;
    border: none;
    color: #e53e3e;
    font-size: 1.25rem;
    cursor: pointer;
    line-height: 1;
}
.search-input {
    width: 100%;
    padding: 0.65rem;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    margin-bottom: 0.75rem;
    box-sizing: border-box;
}
.results { list-style: none; padding: 0; margin: 0; }
.results li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid #edf2f7;
}
.invite-btn { font-size: 0.85rem; }
.empty-hint, .status { color: #718096; font-size: 0.9rem; }
[data-theme="dark"] .search-input {
    background: #1a1a1a;
    border-color: #404040;
    color: #f0f0f0;
}
[data-theme="dark"] .results li { border-color: #333; }
[data-theme="dark"] .hint { color: #aaa; }
</style>
