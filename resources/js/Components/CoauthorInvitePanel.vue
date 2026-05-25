<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import ModalPanel from './ModalPanel.vue'

const props = defineProps({
    articleSlug: { type: String, default: null },
    pendingCoauthors: { type: Array, default: () => [] },
    acceptedCoauthors: { type: Array, default: () => [] },
})

const open = ref(false)
const search = ref('')
const results = ref([])
const searching = ref(false)
let timer = null

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

const invite = (userId) => {
    if (!props.articleSlug) return
    router.post(route('articles.coauthors.invite', props.articleSlug), { user_id: userId }, {
        preserveScroll: true,
        onSuccess: () => {
            search.value = ''
            results.value = []
        },
    })
}
</script>

<template>
    <div class="coauthor-panel">
        <button type="button" class="picker-btn" :disabled="!articleSlug" @click="open = true">
            Добавить автора
        </button>
        <p v-if="!articleSlug" class="hint">Сохраните статью как черновик, чтобы приглашать со-авторов при редактировании.</p>

        <ul v-if="acceptedCoauthors.length" class="coauthor-list">
            <li v-for="user in acceptedCoauthors" :key="user.id">{{ user.name }} (со-автор)</li>
        </ul>
        <ul v-if="pendingCoauthors.length" class="coauthor-list pending">
            <li v-for="user in pendingCoauthors" :key="user.id">{{ user.name }} (ожидает ответа)</li>
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
                    <button type="button" class="invite-btn" @click="invite(user.id)">Пригласить</button>
                </li>
            </ul>
            <p v-else-if="search.trim()" class="empty-hint">Никого не найдено</p>
            <template #footer>
                <button type="button" class="done-btn" @click="open = false">Закрыть</button>
            </template>
        </ModalPanel>
    </div>
</template>

<style scoped>
.picker-btn {
    padding: 0.55rem 1rem;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    background: #fff;
    cursor: pointer;
    font-weight: 600;
}
.picker-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.hint { font-size: 0.85rem; color: #718096; margin: 0.35rem 0 0; }
.coauthor-list { margin: 0.5rem 0 0; padding-left: 1.25rem; font-size: 0.9rem; }
.coauthor-list.pending { color: #718096; }
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
.invite-btn {
    background: #0db7ff;
    color: #fff;
    border: none;
    padding: 0.35rem 0.75rem;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.85rem;
}
.done-btn {
    background: var(--theme_black);
    color: #fff;
    border: none;
    padding: 0.55rem 1.25rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}
.empty-hint, .status { color: #718096; font-size: 0.9rem; }
[data-theme="dark"] .picker-btn,
[data-theme="dark"] .search-input {
    background: #1a1a1a;
    border-color: #404040;
    color: #f0f0f0;
}
[data-theme="dark"] .results li { border-color: #333; }
</style>
