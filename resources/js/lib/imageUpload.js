const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp']
const BLOCKED_EXTENSIONS = ['php', 'exe', 'bat', 'cmd', 'sh', 'js', 'html', 'svg']

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

    return null
}

export const imageAccept = 'image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp'
