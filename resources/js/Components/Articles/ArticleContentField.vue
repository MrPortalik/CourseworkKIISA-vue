<script setup>
import { computed, ref, watch } from 'vue'
import {
    getParagraphIndexAtPosition,
    mergeEditorContent,
    parseContentBlocks,
    splitEditorContent,
} from '@/lib/articleContent'

const content = defineModel('content', { type: String, default: '' })

const props = defineProps({
    rows: { type: Number, default: 15 },
    required: { type: Boolean, default: false },
    placeholder: { type: String, default: 'Текст статьи. Изображения добавляются кнопкой выше и отображаются в структуре.' },
    imageLabels: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['insert-at-paragraph'])

const textareaRef = ref(null)
const cursorPosition = ref(0)
const editorText = ref('')
const imagePlacements = ref([])
let syncingFromModel = false

const syncFromModel = (value) => {
    syncingFromModel = true
    const { text, imagePlacements: placements } = splitEditorContent(value)
    editorText.value = text
    imagePlacements.value = placements
    syncingFromModel = false
}

watch(() => content.value, (value) => {
    if (syncingFromModel) {
        return
    }
    syncFromModel(value)
}, { immediate: true })

const pushToModel = () => {
    syncingFromModel = true
    content.value = mergeEditorContent(editorText.value, imagePlacements.value)
    syncingFromModel = false
}

const displayBlocks = computed(() => {
    let paragraphIndex = 0
    return parseContentBlocks(content.value).map((block) => {
        if (block.type === 'paragraph') {
            return { ...block, paragraphIndex: paragraphIndex++ }
        }
        return block
    })
})

const textareaRequired = computed(
    () => props.required && !editorText.value.trim() && imagePlacements.value.length === 0,
)

const updateCursor = () => {
    const el = textareaRef.value
    if (el) {
        cursorPosition.value = el.selectionStart ?? 0
    }
}

const getParagraphIndexAtCursor = () => {
    updateCursor()
    return getParagraphIndexAtPosition(editorText.value, cursorPosition.value)
}

defineExpose({
    getParagraphIndexAtCursor,
    focus: () => textareaRef.value?.focus(),
})

const requestInsert = (paragraphIndex) => {
    emit('insert-at-paragraph', paragraphIndex)
}

const onEditorInput = () => {
    pushToModel()
    updateCursor()
}
</script>

<template>
    <div class="article-content-field">
        <div v-if="displayBlocks.length" class="content-structure" aria-label="Структура содержания">
            <template v-for="(block, index) in displayBlocks" :key="index">
                <div v-if="block.type === 'paragraph'" class="structure-paragraph">
                    <button
                        type="button"
                        class="insert-paragraph-btn"
                        @click="requestInsert(block.paragraphIndex)"
                    >
                        Вставить в Абзац {{ block.paragraphIndex + 1 }}
                    </button>
                    <span class="structure-text">{{ block.text }}</span>
                </div>
                <div
                    v-else-if="block.type === 'divider'"
                    class="structure-divider"
                    aria-label="Разделитель"
                >
                    ———
                </div>
                <div
                    v-else
                    class="image-marker-badge"
                    :title="imageLabels[block.imageId] || block.imageId"
                >
                    <span class="marker-icon" aria-hidden="true">📷</span>
                    <span class="marker-name">{{ imageLabels[block.imageId] || block.imageId }}</span>
                </div>
            </template>
        </div>
        <textarea
            ref="textareaRef"
            v-model="editorText"
            class="form-textarea"
            :rows="rows"
            :required="textareaRequired"
            :placeholder="placeholder"
            @click="updateCursor"
            @keyup="updateCursor"
            @select="updateCursor"
            @input="onEditorInput"
            @blur="updateCursor"
        />
    </div>
</template>

<style scoped>
.article-content-field {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    max-width: 100%;
    min-width: 0;
}
.content-structure {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 0.75rem;
    border: 1px dashed #cbd5e0;
    border-radius: 8px;
    background: #f8fafc;
    max-height: 220px;
    overflow-x: hidden;
    overflow-y: auto;
}
.structure-paragraph {
    margin: 0;
    font-size: 0.85rem;
    line-height: 1.4;
    color: #4a5568;
}
.insert-paragraph-btn {
    display: inline-block;
    margin-bottom: 5px;
    padding: 0.2rem 0.5rem;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #2b6cb0;
    background: #ebf8ff;
    border: 1px solid #90cdf4;
    border-radius: 4px;
    cursor: pointer;
    font-family: inherit;
}
.insert-paragraph-btn:hover {
    background: #bee3f8;
}
.structure-text {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.image-marker-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    align-self: flex-start;
    margin-bottom: 5px;
    padding: 0.35rem 0.65rem;
    border-radius: 6px;
    background: #ebf8ff;
    border: 1px solid #90cdf4;
    color: #2b6cb0;
    font-size: 0.8rem;
    font-weight: 600;
    max-width: 100%;
}
.marker-name {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 280px;
}
.form-textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #cbd5e0;
    border-radius: 6px;
    box-sizing: border-box;
    font-family: inherit;
    line-height: 1.6;
}
.structure-divider {
    align-self: flex-start;
    margin-bottom: 5px;
    padding: 0.2rem 0.65rem;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.15em;
    color: #718096;
    border: 1px dashed #cbd5e0;
    border-radius: 4px;
}
[data-theme="dark"] .structure-divider {
    color: #aaa;
    border-color: #404040;
}
[data-theme="dark"] .content-structure {
    background: #141414;
    border-color: #404040;
}
[data-theme="dark"] .structure-paragraph { color: #ccc; }
[data-theme="dark"] .insert-paragraph-btn {
    background: rgba(14, 165, 233, 0.15);
    border-color: rgba(56, 189, 248, 0.4);
    color: #7dd3fc;
}
[data-theme="dark"] .insert-paragraph-btn:hover {
    background: rgba(14, 165, 233, 0.28);
}
[data-theme="dark"] .image-marker-badge {
    background: rgba(14, 165, 233, 0.15);
    border-color: rgba(56, 189, 248, 0.4);
    color: #7dd3fc;
}
[data-theme="dark"] .form-textarea {
    background: #1a1a1a;
    border-color: #404040;
    color: #f0f0f0;
}
</style>
