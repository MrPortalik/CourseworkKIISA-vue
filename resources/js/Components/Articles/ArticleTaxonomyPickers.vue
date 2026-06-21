<script setup>
import { computed, ref } from 'vue'
import ModalPanel from '@/Components/ModalPanel.vue'
import CatCheckbox from '@/Components/UI/CatCheckbox.vue'

const SPECIAL_OPTIONS = [
    { key: 'is_hit', label: 'Хит' },
    { key: 'is_editors_choice', label: 'Выбор редакции' },
]

const props = defineProps({
    tags: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    tagIds: { type: Array, required: true },
    categoryIds: { type: Array, required: true },
    isAdmin: { type: Boolean, default: false },
    canModerate: { type: Boolean, default: false },
    isHit: { type: Boolean, default: false },
    isEditorsChoice: { type: Boolean, default: false },
})

const emit = defineEmits([
    'update:tagIds',
    'update:categoryIds',
    'update:isHit',
    'update:isEditorsChoice',
])

const tagsOpen = ref(false)
const categoriesOpen = ref(false)
const specialOpen = ref(false)

const canAssignTag = (tag) => {
    if (!tag.assign_role) return true
    if (tag.assign_role === 'moderator') return props.canModerate || props.isAdmin
    if (tag.assign_role === 'admin') return props.isAdmin
    return true
}

const tagRoleHint = (tag) => {
    if (tag.assign_role === 'moderator') return 'Этот тег может установить только модератор'
    if (tag.assign_role === 'admin') return 'Этот тег может установить только администратор'
    return ''
}

const selectedTagNames = computed(() =>
    props.tags.filter((t) => props.tagIds.includes(t.id)).map((t) => t.name),
)
const selectedCategoryNames = computed(() =>
    props.categories.filter((c) => props.categoryIds.includes(c.id)).map((c) => c.name),
)

const selectedSpecialNames = computed(() =>
    SPECIAL_OPTIONS
        .filter((option) => {
            if (option.key === 'is_hit') return props.isHit
            if (option.key === 'is_editors_choice') return props.isEditorsChoice
            return false
        })
        .map((option) => option.label),
)

const toggleTag = (tag) => {
    if (!canAssignTag(tag)) return

    const set = new Set(props.tagIds)
    if (set.has(tag.id)) set.delete(tag.id)
    else set.add(tag.id)
    emit('update:tagIds', [...set])
}

const toggleCategory = (id) => {
    const set = new Set(props.categoryIds)
    if (set.has(id)) set.delete(id)
    else set.add(id)
    emit('update:categoryIds', [...set])
}

const toggleSpecial = (key) => {
    if (key === 'is_hit') {
        emit('update:isHit', !props.isHit)
        return
    }

    if (key === 'is_editors_choice') {
        emit('update:isEditorsChoice', !props.isEditorsChoice)
    }
}

const isSpecialSelected = (key) => {
    if (key === 'is_hit') return props.isHit
    if (key === 'is_editors_choice') return props.isEditorsChoice
    return false
}
</script>

<template>
    <div class="taxonomy-pickers">
        <div class="picker-row">
            <label class="form-label">Категории</label>
            <button type="button" class="picker-btn" @click="categoriesOpen = true">
                {{ selectedCategoryNames.length ? selectedCategoryNames.join(', ') : 'Выбрать категории' }}
            </button>
        </div>

        <div v-if="isAdmin" class="picker-row special-section">
            <label class="form-label">Особые категории</label>
            <button type="button" class="picker-btn picker-btn--special" @click="specialOpen = true">
                {{ selectedSpecialNames.length ? selectedSpecialNames.join(', ') : 'Выбрать особые категории' }}
            </button>
        </div>

        <div class="picker-row picker-row--tags">
            <label class="form-label">Теги</label>
            <button type="button" class="picker-btn" @click="tagsOpen = true">
                {{ selectedTagNames.length ? selectedTagNames.join(', ') : 'Выбрать теги' }}
            </button>
        </div>

        <ModalPanel title="Категории" :open="categoriesOpen" @close="categoriesOpen = false">
            <p v-if="!categories.length" class="empty-hint">На данный момент нет категорий</p>
            <label v-for="cat in categories" :key="cat.id" class="check-row">
                <CatCheckbox :checked="categoryIds.includes(cat.id)" @change="() => toggleCategory(cat.id)" />
                {{ cat.name }}
            </label>
            <template #footer>
                <button type="button" class="done-btn" @click="categoriesOpen = false">Готово</button>
            </template>
        </ModalPanel>

        <ModalPanel title="Особые категории" :open="specialOpen" @close="specialOpen = false">
            <p class="special-hint">Доступно только администрации. Статья может попасть в «Хиты» автоматически по настройкам модерации.</p>
            <label v-for="option in SPECIAL_OPTIONS" :key="option.key" class="check-row">
                <CatCheckbox :checked="isSpecialSelected(option.key)" @change="() => toggleSpecial(option.key)" />
                {{ option.label }}
            </label>
            <template #footer>
                <button type="button" class="done-btn" @click="specialOpen = false">Готово</button>
            </template>
        </ModalPanel>

        <ModalPanel title="Теги" :open="tagsOpen" @close="tagsOpen = false">
            <p v-if="!tags.length" class="empty-hint">На данный момент нет тегов</p>
            <div
                v-for="tag in tags"
                :key="tag.id"
                class="check-row check-row--tag"
                :class="{ 'check-row--tag-restricted': !!tagRoleHint(tag) }"
            >
                <div class="check-row__main">
                    <div class="check-row__checkbox-anchor">
                        <CatCheckbox
                            :checked="tagIds.includes(tag.id)"
                            :disabled="!canAssignTag(tag)"
                            @change="() => toggleTag(tag)"
                        />
                    </div>
                    <span class="check-row__name">{{ tag.name }}</span>
                </div>
                <div
                    v-if="tagRoleHint(tag)"
                    class="tag-role-note"
                    :class="tag.assign_role === 'admin' ? 'tag-role-note--admin' : 'tag-role-note--moderator'"
                >
                    <svg class="tag-role-connector" viewBox="0 0 18 14" aria-hidden="true">
                        <path
                            d="M 1 0 L 1 8 Q 1 12 5 12 L 17 12"
                            stroke="currentColor"
                            stroke-width="2"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                    <span class="tag-role-hint">{{ tagRoleHint(tag) }}</span>
                </div>
            </div>
            <template #footer>
                <button type="button" class="done-btn" @click="tagsOpen = false">Готово</button>
            </template>
        </ModalPanel>
    </div>
</template>

<style scoped>
.taxonomy-pickers { display: flex; flex-direction: column; gap: 1rem; }
.picker-row--tags { margin-bottom: 0.75rem; }
.picker-row { display: flex; flex-direction: column; gap: 0.4rem; }
.special-section {
    padding: 0.85rem;
    border: 1px dashed #cbd5e0;
    border-radius: 10px;
    background: #f8fafc;
}
.form-label { font-weight: 600; }
.picker-btn {
    text-align: left;
    padding: 0.65rem 0.85rem;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    background: #fff;
    cursor: pointer;
    color: inherit;
    font-size: 0.95rem;
}
.picker-btn--special {
    border-color: #0db7ff;
}
.check-row {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    padding: 0.35rem 0;
    font-size: 0.95rem;
}
.check-row--tag {
    flex-direction: column;
    align-items: stretch;
    gap: 0;
}
.check-row__main {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 0;
}
.check-row__name {
    flex: 1;
    min-width: 0;
}
.check-row--tag-restricted {
    --tag-checkbox-size: 1.65rem;
    --tag-connector-gap: 2px;
}
.check-row__checkbox-anchor {
    position: relative;
    flex-shrink: 0;
    width: var(--tag-checkbox-size, 1.65rem);
}
.check-row__checkbox-anchor :deep(.cat-checkbox) {
    display: block;
}
.tag-role-note {
    display: flex;
    align-items: center;
    margin-top: var(--tag-connector-gap);
    padding-left: calc(var(--tag-checkbox-size) / 2 - 1px);
    min-height: 1.35rem;
}
.tag-role-connector {
    flex-shrink: 0;
    width: 1.1rem;
    height: 0.875rem;
    color: #151515;
    pointer-events: none;
}
.tag-role-hint {
    flex: 1;
    min-width: 0;
    padding-left: 0.4rem;
    padding-top: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    line-height: 1.3;
}
.tag-role-note--moderator .tag-role-hint {
    color: #38a169;
}
.tag-role-note--admin .tag-role-hint {
    color: #e53e3e;
}
.empty-hint,
.special-hint {
    color: #718096;
    font-style: italic;
    margin: 0 0 0.5rem;
    line-height: 1.45;
}
.special-hint { font-style: normal; font-size: 0.875rem; }
.done-btn {
    background: var(--theme_black);
    color: #fff;
    border: none;
    padding: 0.55rem 1.25rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}
[data-theme="dark"] .special-section {
    background: #141414;
    border-color: #404040;
}
[data-theme="dark"] .picker-btn {
    background: #1a1a1a;
    border-color: #404040;
    color: #f0f0f0;
}
[data-theme="dark"] .picker-btn--special {
    border-color: #0db7ff;
}
[data-theme="dark"] .empty-hint,
[data-theme="dark"] .special-hint { color: #aaa; }
[data-theme="dark"] .tag-role-connector {
    color: #ffffff;
}
[data-theme="dark"] .tag-role-note--moderator .tag-role-hint { color: #68d391; }
[data-theme="dark"] .tag-role-note--admin .tag-role-hint { color: #fc8181; }
</style>
