const appName = import.meta.env.VITE_APP_NAME || 'КИИСА'
const titleSuffix = ` - ${appName}`

export function truncateSeoTitle(title, maxTotal = 60) {
    const max = Math.max(1, maxTotal - titleSuffix.length)
    const value = String(title ?? '').trim()
    if (value.length <= max) {
        return value
    }

    return `${value.slice(0, max - 1).trimEnd()} ...`
}

export function truncateSeoDescription(description, max = 160) {
    const value = String(description ?? '').trim()
    if (value.length <= max) {
        return value
    }

    return `${value.slice(0, max - 1).trimEnd()} ...`
}
