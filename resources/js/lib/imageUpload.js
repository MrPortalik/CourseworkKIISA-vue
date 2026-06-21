const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp']
const BLOCKED_EXTENSIONS = ['php', 'exe', 'bat', 'cmd', 'sh', 'js', 'html', 'svg']
export const MAX_IMAGE_FILE_SIZE = 4.5 * 1024 * 1024
export const MAX_FEEDBACK_ATTACHMENTS_SIZE = 10 * 1024 * 1024

export function validateImageFiles(files, { maxTotalSize = MAX_FEEDBACK_ATTACHMENTS_SIZE } = {}) {
    if (!files?.length) {
        return null
    }

    let totalSize = 0
    for (const file of files) {
        const error = validateImageFile(file)
        if (error) {
            return error
        }
        totalSize += file.size
    }

    if (totalSize > maxTotalSize) {
        return 'Общий размер вложений не должен превышать 10 МБ.'
    }

    return null
}

export function validateImageFile(file) {
    if (!file) {
        return 'Файл не выбран.'
    }

    const extension = file.name.split('.').pop()?.toLowerCase() ?? ''
    if (BLOCKED_EXTENSIONS.includes(extension)) {
        return 'Разрешены только изображения JPEG, PNG и WebP.'
    }

    if (!ALLOWED_TYPES.includes(file.type)) {
        return 'Разрешены только изображения JPEG, PNG и WebP.'
    }

    if (file.size > MAX_IMAGE_FILE_SIZE) {
        return 'Размер файла не должен превышать 4,5 МБ.'
    }

    return null
}

export const imageAccept = 'image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp'
