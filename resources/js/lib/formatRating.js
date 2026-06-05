export function formatRating(value) {
    const num = Number(value)
    if (!Number.isFinite(num)) {
        return '0'
    }

    return parseFloat(num.toFixed(2)).toString()
}
