export function formatPaginationLabel(label) {
    const text = String(label)

    if (/previous/i.test(text)) {
        return '&laquo; Назад'
    }

    if (/next/i.test(text)) {
        return 'Вперёд &raquo;'
    }

    return text
}

export function filterPaginationLinks(links, lastPage = null) {
    return (links ?? []).filter((link) => {
        if (!link.url && !link.active) {
            return false
        }

        if (lastPage !== null) {
            const match = String(link.label).match(/^\d+$/)
            if (match) {
                const pageNum = parseInt(match[0], 10)
                return pageNum >= 1 && pageNum <= lastPage
            }
        }

        return true
    })
}
