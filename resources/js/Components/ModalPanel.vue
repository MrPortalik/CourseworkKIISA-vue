<script setup>
defineProps({
    title: { type: String, required: true },
    open: { type: Boolean, default: false },
})
defineEmits(['close'])
</script>

<template>
    <Teleport to="body">
        <div v-if="open" class="modal-backdrop" @click="$emit('close')" />
        <div v-if="open" class="modal-panel" role="dialog" :aria-label="title">
            <div class="modal-header">
                <h3>{{ title }}</h3>
                <div class="modal-header-actions">
                    <slot name="header-actions" />
                    <button type="button" class="modal-close" aria-label="Закрыть" @click="$emit('close')">×</button>
                </div>
            </div>
            <div class="modal-body">
                <slot />
            </div>
            <div v-if="$slots.footer" class="modal-footer">
                <slot name="footer" />
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.55);
    z-index: 200;
}
.modal-panel {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 210;
    width: min(520px, calc(100vw - 2rem));
    max-height: 85vh;
    overflow-y: auto;
    background: #fff;
    color: #1a202c;
    border-radius: 14px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
    padding: 1.25rem 1.5rem 1.5rem;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 1rem;
}
.modal-header h3 {
    margin: 0;
    font-size: 1.2rem;
    flex: 1;
    min-width: 0;
}
.modal-header-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
}
.modal-close {
    background: none;
    border: none;
    font-size: 1.75rem;
    line-height: 1;
    cursor: pointer;
    color: inherit;
}
.modal-footer {
    margin-top: 1.25rem;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}
[data-theme="dark"] .modal-panel {
    background: #1e1e1e;
    color: #f0f0f0;
    border: 1px solid #333;
}
</style>
