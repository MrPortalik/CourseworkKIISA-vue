<script setup>
import { computed } from 'vue'

const props = defineProps({
    modelValue: { type: [Boolean, Array], default: false },
    value: { default: null },
    checked: { type: Boolean, default: undefined },
    variant: { type: String, default: 'default' },
})

const emit = defineEmits(['update:modelValue', 'change'])

const isChecked = computed(() => {
    if (props.checked !== undefined) {
        return props.checked
    }
    if (Array.isArray(props.modelValue)) {
        return props.modelValue.includes(props.value)
    }
    return !!props.modelValue
})

const toggle = (event) => {
    if (Array.isArray(props.modelValue)) {
        const set = new Set(props.modelValue)
        if (set.has(props.value)) {
            set.delete(props.value)
        } else {
            set.add(props.value)
        }
        emit('update:modelValue', [...set])
    } else {
        emit('update:modelValue', event.target.checked)
    }
    emit('change', event)
}
</script>

<template>
    <label class="cat-checkbox" :class="{ 'cat-checkbox--theme': variant === 'theme' }">
        <input
            type="checkbox"
            class="cat-checkbox__native"
            :checked="isChecked"
            :value="value"
            @change="toggle"
        />
        <span class="cat-checkbox__face" aria-hidden="true">
            <svg viewBox="0 0 32 32" class="cat-checkbox__svg">
                <path
                    class="cat-silhouette"
                    d="M6 12 L10 4 L14 12 Z M18 12 L22 4 L26 12 Z M5 18 C5 24 10 28 16 28 C22 28 27 24 27 18 C27 14 25 11 22 10 C20 9 18 10 16 12 C14 10 12 9 10 10 C7 11 5 14 5 18 Z"
                />
            </svg>
        </span>
        <span v-if="$slots.default" class="cat-checkbox__label">
            <slot />
        </span>
    </label>
</template>

<style scoped>
.cat-checkbox {
    display: inline-flex;
    align-items: flex-start;
    gap: 0.5rem;
    cursor: pointer;
    user-select: none;
}

.cat-checkbox__native {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
    pointer-events: none;
}

.cat-checkbox__face {
    flex-shrink: 0;
    width: 1.65rem;
    height: 1.65rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: 2px solid #cbd5e0;
    background: #fff;
    transition: border-color 0.15s, background 0.15s, transform 0.15s;
}

.cat-checkbox__svg {
    width: 1.2rem;
    height: 1.2rem;
    overflow: visible;
}

.cat-silhouette {
    fill: transparent;
    stroke: none;
    transition: fill 0.15s;
}

.cat-checkbox--theme .cat-silhouette {
    fill: #151515;
}

.cat-checkbox__native:checked + .cat-checkbox__face .cat-silhouette {
    fill: #151515;
}

.cat-checkbox__native:focus-visible + .cat-checkbox__face {
    outline: 2px solid #0db7ff;
    outline-offset: 2px;
}

.cat-checkbox__native:checked + .cat-checkbox__face {
    border-color: #151515;
    background: #f7fafc;
    transform: scale(1.04);
}

.cat-checkbox__label {
    line-height: 1.45;
    font-size: inherit;
}

[data-theme="dark"] .cat-checkbox__face {
    border-color: #52525b;
    background: #000000;
}

[data-theme="dark"] .cat-silhouette {
    fill: transparent;
}

[data-theme="dark"] .cat-checkbox--theme .cat-silhouette,
[data-theme="dark"] .cat-checkbox__native:checked + .cat-checkbox__face .cat-silhouette {
    fill: #ffffff;
}

[data-theme="dark"] .cat-checkbox__native:checked + .cat-checkbox__face {
    border-color: #ffffff;
    background: #000000;
}
</style>
