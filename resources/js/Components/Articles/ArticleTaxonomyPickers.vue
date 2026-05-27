<script setup>
import { computed, ref } from 'vue'
import ModalPanel from '@/Components/ModalPanel.vue'

const props = defineProps({
    tags: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    tagIds: { type: Array, required: true },
    categoryIds: { type: Array, required: true },
})

const emit = defineEmits(['update:tagIds', 'update:categoryIds'])

const tagsOpen = ref(false)
const categoriesOpen = ref(false)

const selectedTagNames = computed(() =>
    props.tags.filter((t) => props.tagIds.includes(t.id)).map((t) => t.name)
)
const selectedCategoryNames = computed(() =>
    props.categories.filter((c) => props.categoryIds.includes(c.id)).map((c) => c.name)
)

const toggleTag = (id) => {
    const set = new Set(props.tagIds)
    if (set.has(id)) set.delete(id)
    else set.add(id)
    emit('update:tagIds', [...set])
}

const toggleCategory = (id) => {
    const set = new Set(props.categoryIds)
    if (set.has(id)) set.delete(id)
    else set.add(id)
    emit('update:categoryIds', [...set])
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
        <div class="picker-row">
            <label class="form-label">Теги</label>
            <button type="button" class="picker-btn" @click="tagsOpen = true">
                {{ selectedTagNames.length ? selectedTagNames.join(', ') : 'Выбрать теги' }}
            </button>
        </div>

        <ModalPanel title="Категории" :open="categoriesOpen" @close="categoriesOpen = false">
            <p v-if="!categories.length" class="empty-hint">На данный момент нет категорий</p>
            <label v-for="cat in categories" :key="cat.id" class="check-row">
                <input
                    type="checkbox"
                    :checked="categoryIds.includes(cat.id)"
                    @change="toggleCategory(cat.id)"
                />
                {{ cat.name }}
            </label>
            <template #footer>
                <button type="button" class="done-btn" @click="categoriesOpen = false">Готово</button>
            </template>
        </ModalPanel>

        <ModalPanel title="Теги" :open="tagsOpen" @close="tagsOpen = false">
            <p v-if="!tags.length" class="empty-hint">На данный момент нет тегов</p>
            <label v-for="tag in tags" :key="tag.id" class="check-row">
                <input
                    type="checkbox"
                    :checked="tagIds.includes(tag.id)"
                    @change="toggleTag(tag.id)"
                />
                {{ tag.name }}
            </label>
            <template #footer>
                <button type="button" class="done-btn" @click="tagsOpen = false">Готово</button>
            </template>
        </ModalPanel>
    </div>
</template>

<style scoped>
.taxonomy-pickers { display: flex; flex-direction: column; gap: 1rem; }
.picker-row { display: flex; flex-direction: column; gap: 0.4rem; }
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
.check-row {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    padding: 0.35rem 0;
    font-size: 0.95rem;
}
.empty-hint { color: #718096; font-style: italic; margin: 0 0 0.5rem; }
.done-btn {
    background: var(--theme_black);
    color: #fff;
    border: none;
    padding: 0.55rem 1.25rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}
[data-theme="dark"] .picker-btn {
    background: #1a1a1a;
    border-color: #404040;
    color: #f0f0f0;
}
[data-theme="dark"] .empty-hint { color: #aaa; }
</style>
