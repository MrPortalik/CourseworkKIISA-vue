export function formatRating(value) {
    const num = Number(value)
    if (!Number.isFinite(num) || num <= 0) {
        return '0.0'
    }

    const rounded2 = Math.round(num * 100) / 100
    const hasSecondDecimal = Math.abs(rounded2 * 10 - Math.round(rounded2 * 10)) > 0.001

    return hasSecondDecimal ? rounded2.toFixed(2) : rounded2.toFixed(1)
}
