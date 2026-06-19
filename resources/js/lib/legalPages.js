import { portalRulesTitle } from '@/content/legal/portalRules'
import { privacyPolicyTitle } from '@/content/legal/privacyPolicy'
import { termsOfUseTitle } from '@/content/legal/termsOfUse'

export const LEGAL_PAGE_ORDER = ['publication', 'terms', 'privacy']

export const LEGAL_PAGES = {
    publication: {
        key: 'publication',
        route: 'publication-rules',
        title: portalRulesTitle,
        prevLabel: '← Перейти к политике конфиденциальности',
        nextLabel: 'Перейти к пользовательскому соглашению →',
    },
    terms: {
        key: 'terms',
        route: 'terms-of-use',
        title: termsOfUseTitle,
        prevLabel: '← Перейти к правилам публикации',
        nextLabel: 'Перейти к политике конфиденциальности →',
    },
    privacy: {
        key: 'privacy',
        route: 'privacy-policy',
        title: privacyPolicyTitle,
        prevLabel: '← Перейти к пользовательскому соглашению',
        nextLabel: 'Перейти к правилам публикации →',
    },
}

export function legalPageNav(currentKey) {
    const index = LEGAL_PAGE_ORDER.indexOf(currentKey)
    const prevKey = LEGAL_PAGE_ORDER[(index - 1 + LEGAL_PAGE_ORDER.length) % LEGAL_PAGE_ORDER.length]
    const nextKey = LEGAL_PAGE_ORDER[(index + 1) % LEGAL_PAGE_ORDER.length]

    return {
        prev: LEGAL_PAGES[prevKey],
        next: LEGAL_PAGES[nextKey],
    }
}

export function legalStandaloneHref(key) {
    const page = LEGAL_PAGES[key]
    return page ? route(page.route) : null
}
