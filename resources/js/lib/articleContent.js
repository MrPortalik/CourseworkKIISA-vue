/**
 * Преобразование HTML контента статьи ↔ простой текст для редактора.
 * Изображения привязываются по стабильному id, а не по имени файла.
 */

export const IMAGE_MARKER_RE = /^\[изображение:([^\]]+)\]\s*$/i

export function isImageMarker(text) {
    return IMAGE_MARKER_RE.test(String(text ?? '').trim())
}

export function imageMarker(imageId) {
    return `[изображение:${imageId}]`
}

export function createImageId() {
    return typeof crypto !== 'undefined' && crypto.randomUUID
        ? crypto.randomUUID()
        : `img-${Date.now()}-${Math.random().toString(36).slice(2, 9)}`
}

export function isDividerLine(line) {
    const value = String(line ?? '')
    return value.trim() === '---' && !value.trimStart().startsWith('/')
}

export function unescapeDividerLine(line) {
    const value = String(line ?? '')
    const trimmedStart = value.trimStart()
    if (trimmedStart.startsWith('/---')) {
        const leading = value.slice(0, value.length - trimmedStart.length)
        return leading + trimmedStart.slice(1)
    }
    return value
}

export function parseContentBlocks(plainText) {
    const parts = String(plainText ?? '')
        .split(/\n\n+/)
        .filter((part) => part.trim().length > 0)

    return parts.map((part) => {
        const match = part.trim().match(IMAGE_MARKER_RE)
        if (match) {
            return { type: 'image', imageId: match[1].trim() }
        }
        if (isDividerLine(part)) {
            return { type: 'divider' }
        }
        return { type: 'paragraph', text: part }
    })
}

/** Разделить полное содержание на текст для textarea и позиции изображений. */
export function splitEditorContent(plainText) {
    const imagePlacements = []
    const paragraphs = []
    let paragraphIndex = -1

    for (const block of parseContentBlocks(plainText)) {
        if (block.type === 'paragraph') {
            paragraphs.push(block.text)
            paragraphIndex++
        } else if (block.type === 'divider') {
            paragraphs.push('---')
        } else {
            imagePlacements.push({
                imageId: block.imageId,
                afterParagraphIndex: paragraphIndex,
            })
        }
    }

    return {
        text: paragraphs.join('\n\n'),
        imagePlacements,
    }
}

/** Собрать полное содержание из текста и позиций изображений. */
export function mergeEditorContent(text, imagePlacements = []) {
    const paragraphs = String(text ?? '')
        .split(/\n\n+/)
        .filter((p) => p.trim().length > 0)

    if (!paragraphs.length) {
        return imagePlacements
            .slice()
            .sort((a, b) => a.afterParagraphIndex - b.afterParagraphIndex)
            .map((p) => imageMarker(p.imageId))
            .join('\n\n')
    }

    const byAfter = new Map()
    for (const placement of imagePlacements) {
        const key = placement.afterParagraphIndex
        if (!byAfter.has(key)) {
            byAfter.set(key, [])
        }
        byAfter.get(key).push(placement.imageId)
    }

    const blocks = []

    for (let i = 0; i < paragraphs.length; i++) {
        if (i === 0) {
            for (const imageId of byAfter.get(-1) ?? []) {
                blocks.push(imageMarker(imageId))
            }
        }

        blocks.push(paragraphs[i])

        for (const imageId of byAfter.get(i) ?? []) {
            blocks.push(imageMarker(imageId))
        }
    }

    return blocks.join('\n\n')
}

export function getReferencedImageIds(plainText) {
    return new Set(
        parseContentBlocks(plainText)
            .filter((block) => block.type === 'image')
            .map((block) => block.imageId),
    )
}

export function getParagraphIndexAtPosition(plainText, position) {
    if (!plainText?.trim()) {
        return 0
    }

    const pos = Math.max(0, Math.min(position, plainText.length))
    let paragraphIndex = 0
    let searchFrom = 0

    while (searchFrom < plainText.length) {
        const nextBreak = plainText.indexOf('\n\n', searchFrom)
        const blockEnd = nextBreak === -1 ? plainText.length : nextBreak
        const block = plainText.slice(searchFrom, blockEnd)
        const trimmed = block.trim()

        if (pos >= searchFrom && pos <= blockEnd) {
            if (trimmed && !isImageMarker(trimmed) && !isDividerLine(trimmed)) {
                return paragraphIndex
            }
            return Math.max(0, paragraphIndex - 1)
        }

        if (trimmed && !isImageMarker(trimmed) && !isDividerLine(trimmed)) {
            paragraphIndex++
        }

        searchFrom = nextBreak === -1 ? plainText.length : nextBreak + 2
    }

    return Math.max(0, paragraphIndex - 1)
}

/** Вставить маркер изображения сразу после абзаца с указанным индексом (0-based). */
export function insertImageAfterParagraph(plainText, paragraphIndex, imageId) {
    const marker = imageMarker(imageId)
    const source = String(plainText ?? '')

    if (!source.trim()) {
        return marker
    }

    const blocks = []
    let paraCount = 0
    let inserted = false

    for (const part of source.split(/\n\n+/)) {
        if (!part.trim()) continue

        if (isImageMarker(part)) {
            blocks.push(part.trim())
            continue
        }

        if (isDividerLine(part)) {
            blocks.push(part.trim())
            continue
        }

        blocks.push(part)

        if (paraCount === paragraphIndex) {
            blocks.push(marker)
            inserted = true
        }

        paraCount++
    }

    if (!inserted) {
        blocks.push(marker)
    }

    return blocks.join('\n\n')
}

export function htmlToPlainText(html) {
    if (!html || typeof html !== 'string') {
        return ''
    }

    const doc = new DOMParser().parseFromString(html, 'text/html')
    const lines = []
    const seenImageIds = new Set()

    const pushImage = (img) => {
        const id =
            img.getAttribute('data-image-id')
            || stableIdFromSrc(img.getAttribute('src') ?? '')

        if (!id || seenImageIds.has(id)) {
            return
        }

        seenImageIds.add(id)
        lines.push(imageMarker(id))
    }

    const walk = (parent) => {
        for (const node of parent.childNodes) {
            if (node.nodeType !== Node.ELEMENT_NODE) {
                if (node.nodeType === Node.TEXT_NODE) {
                    const t = (node.textContent ?? '').trim()
                    if (t) {
                        lines.push(t)
                    }
                }
                continue
            }

            if (node.classList?.contains('article-content-end')) {
                continue
            }

            if (node.nodeName === 'FIGURE') {
                const img = node.querySelector('img')
                if (img) {
                    pushImage(img)
                }
                continue
            }

            if (node.nodeName === 'IMG') {
                pushImage(node)
                continue
            }

            if (node.nodeName === 'HR') {
                lines.push('---')
                continue
            }

            if (node.nodeName === 'P') {
                let text = ''
                for (const child of node.childNodes) {
                    if (child.nodeType === Node.ELEMENT_NODE && child.nodeName === 'IMG') {
                        if (text.trim()) {
                            lines.push(text)
                            text = ''
                        }
                        pushImage(child)
                    } else if (child.nodeType === Node.ELEMENT_NODE && child.nodeName === 'BR') {
                        text += '\n'
                    } else if (child.nodeType === Node.TEXT_NODE) {
                        text += child.textContent ?? ''
                    } else if (child.nodeType === Node.ELEMENT_NODE) {
                        text += extractParagraphText(child)
                    }
                }
                if (text.trim()) {
                    lines.push(text)
                }
                continue
            }

            walk(node)
        }
    }

    walk(doc.body)

    return lines.join('\n\n')
}

export function extractContentImages(html) {
    if (!html || typeof html !== 'string') {
        return []
    }

    const doc = new DOMParser().parseFromString(html, 'text/html')
    const seen = new Set()
    const images = []

    for (const [index, img] of [...doc.querySelectorAll('img')].entries()) {
        if (img.closest('.article-content-end')) {
            continue
        }

        const src = img.getAttribute('src') ?? ''
        const id = img.getAttribute('data-image-id') || stableIdFromSrc(src) || `legacy-${index}`

        if (seen.has(id)) {
            continue
        }

        seen.add(id)

        images.push({
            id,
            src,
            className: img.getAttribute('class') ?? 'content-image-float',
            alt: img.getAttribute('alt') ?? '',
            fileName:
                img.getAttribute('data-filename')
                || fileNameFromUrl(src)
                || `image-${index + 1}`,
        })
    }

    return images
}

export function buildArticleHtml(plainText, images = []) {
    const referencedIds = getReferencedImageIds(plainText)
    const imageMap = new Map()

    for (const img of images) {
        if (img?.id && img?.src && referencedIds.has(img.id)) {
            imageMap.set(img.id, img)
        }
    }

    const htmlParts = parseContentBlocks(plainText).flatMap((block) => {
        if (block.type === 'image') {
            const img = imageMap.get(block.imageId)

            if (!img?.src) {
                return []
            }

            const fileName = img.fileName || fileNameFromUrl(img.src)

            return [`<figure class="content-image-figure"><img src="${img.src}" class="${img.className || 'content-image-float'}" alt="${escapeHtml(img.alt || '')}" data-image-id="${escapeHtml(img.id)}" data-filename="${escapeHtml(fileName)}" /></figure>`]
        }

        if (block.type === 'divider') {
            return ['<hr class="article-content-divider" />']
        }

        return renderParagraphBlock(block.text)
    })

    let html = htmlParts.filter(Boolean).join('\n')

    if (html) {
        html += '\n<div class="article-content-end" aria-hidden="true"></div>'
    }

    return html
}

function extractParagraphText(node) {
    let text = ''

    for (const child of node.childNodes) {
        if (child.nodeType === Node.TEXT_NODE) {
            text += child.textContent ?? ''
            continue
        }

        if (child.nodeType !== Node.ELEMENT_NODE) {
            continue
        }

        if (child.nodeName === 'BR') {
            text += '\n'
            continue
        }

        if (child.nodeName === 'IMG') {
            continue
        }

        text += extractParagraphText(child)
    }

    return text
}

function stableIdFromSrc(src) {
    if (!src) return ''
    let hash = 0
    for (let i = 0; i < src.length; i++) {
        hash = (hash << 5) - hash + src.charCodeAt(i)
        hash |= 0
    }
    return `src-${Math.abs(hash)}`
}

function fileNameFromUrl(url) {
    if (!url) return ''
    try {
        const path = new URL(url, typeof window !== 'undefined' ? window.location.origin : 'http://localhost').pathname
        const base = path.split('/').pop() || ''
        return decodeURIComponent(base)
    } catch {
        const parts = url.split('/')
        return parts[parts.length - 1] || ''
    }
}

function renderParagraphBlock(text) {
    const lines = String(text ?? '').split('\n')
    const segments = []
    let buffer = []

    const flushBuffer = () => {
        if (!buffer.length) {
            return
        }

        const html = buffer
            .map((line) => escapeHtml(unescapeDividerLine(line)))
            .join('<br>')
        segments.push(`<p>${html}</p>`)
        buffer = []
    }

    for (const line of lines) {
        if (isDividerLine(line)) {
            flushBuffer()
            segments.push('<hr class="article-content-divider" />')
        } else {
            buffer.push(line)
        }
    }

    flushBuffer()

    return segments
}

function escapeHtml(text) {
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
}
