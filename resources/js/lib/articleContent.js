/**
 * Преобразование HTML контента статьи ↔ простой текст для редактора.
 */

export function htmlToPlainText(html) {
    if (!html || typeof html !== 'string') {
        return ''
    }

    const doc = new DOMParser().parseFromString(html, 'text/html')
    const images = [...doc.querySelectorAll('img')]

    images.forEach((img) => {
        img.replaceWith(doc.createTextNode('\n'))
    })

    const paragraphs = [...doc.body.querySelectorAll('p')]

    if (paragraphs.length) {
        return paragraphs
            .map((p) => (p.textContent ?? '').trim())
            .filter(Boolean)
            .join('\n\n')
    }

    return (doc.body.textContent ?? '').trim()
}

export function extractContentImages(html) {
    if (!html || typeof html !== 'string') {
        return []
    }

    const doc = new DOMParser().parseFromString(html, 'text/html')

    return [...doc.querySelectorAll('img')].map((img) => ({
        src: img.getAttribute('src') ?? '',
        className: img.getAttribute('class') ?? 'content-image-float',
        alt: img.getAttribute('alt') ?? '',
    }))
}

export function buildArticleHtml(plainText, images = []) {
    const paragraphs = String(plainText ?? '')
        .split(/\r\n|\r|\n/)
        .map((p) => p.trim())
        .filter(Boolean)
        .map((p) => `<p>${escapeHtml(p)}</p>`)
        .join('')

    const imageHtml = images
        .filter((img) => img.src)
        .map(
            (img) =>
                `<img src="${img.src}" class="${img.className || 'content-image-float'}" alt="${escapeHtml(img.alt || '')}" />`,
        )
        .join('\n')

    return paragraphs + (imageHtml ? (paragraphs ? '\n' : '') + imageHtml : '')
}

function escapeHtml(text) {
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
}
