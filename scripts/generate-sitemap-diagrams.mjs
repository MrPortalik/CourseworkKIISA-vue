import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'fs'
import { join, dirname } from 'path'
import { fileURLToPath } from 'url'
import { execSync } from 'child_process'

const __dirname = dirname(fileURLToPath(import.meta.url))
const root = join(__dirname, '..')
const sitemapPath = join(root, 'docs', 'sitemap.md')
const mmdDir = join(root, 'docs', 'diagrams', 'mmd')
const svgDir = join(root, 'docs', 'diagrams', 'svg')
const configPath = join(root, 'docs', 'diagrams', 'mermaid-config.json')

const translitMap = {
    а: 'a', б: 'b', в: 'v', г: 'g', д: 'd', е: 'e', ё: 'yo', ж: 'zh', з: 'z', и: 'i', й: 'y',
    к: 'k', л: 'l', м: 'm', н: 'n', о: 'o', п: 'p', р: 'r', с: 's', т: 't', у: 'u', ф: 'f',
    х: 'h', ц: 'ts', ч: 'ch', ш: 'sh', щ: 'sch', ъ: '', ы: 'y', ь: '', э: 'e', ю: 'yu', я: 'ya',
}

function slugify(title) {
    const lower = title.toLowerCase()
    let result = ''
    for (const char of lower) {
        if (translitMap[char] !== undefined) {
            result += translitMap[char]
        } else if (/[a-z0-9]/.test(char)) {
            result += char
        } else if (/\s/.test(char) || char === '—' || char === '-') {
            result += '-'
        }
    }
    return result.replace(/-+/g, '-').replace(/^-|-$/g, '').slice(0, 80)
}

function parseDiagrams(markdown) {
    const diagrams = []
    const blockRe = /## SiteMap:([^\n]+)\s+```mermaid\s+([\s\S]*?)```/g

    let match
    let index = 0

    while ((match = blockRe.exec(markdown)) !== null) {
        index += 1
        const title = `SiteMap:${match[1].trim()}`
        const slug = slugify(match[1].trim())
        const filename = `sitemap-${String(index).padStart(2, '0')}-${slug || 'diagram'}`

        diagrams.push({
            title,
            filename,
            content: match[2].trim(),
        })
    }

    return diagrams
}

mkdirSync(mmdDir, { recursive: true })
mkdirSync(svgDir, { recursive: true })

const markdown = readFileSync(sitemapPath, 'utf8')
const diagrams = parseDiagrams(markdown)

if (diagrams.length === 0) {
    throw new Error(`No SiteMap diagrams found in ${sitemapPath}`)
}

const indexSection = [
    '',
    '## SiteMap (карта сайта)',
    '',
    'Источник: `docs/sitemap.md`. Перегенерация: `node scripts/generate-sitemap-diagrams.mjs`',
    '',
]

for (const diagram of diagrams) {
    const mmdPath = join(mmdDir, `${diagram.filename}.mmd`)
    const svgPath = join(svgDir, `${diagram.filename}.svg`)

    writeFileSync(mmdPath, diagram.content, 'utf8')

    try {
        execSync(
            `npx --yes @mermaid-js/mermaid-cli -i "${mmdPath}" -o "${svgPath}" -c "${configPath}" -b transparent`,
            { cwd: root, stdio: 'pipe', shell: true },
        )
    } catch (error) {
        console.error(`Failed: ${diagram.filename}`)
        throw error
    }

    indexSection.push(`### ${diagram.title}`)
    indexSection.push('')
    indexSection.push(`- Mermaid: \`mmd/${diagram.filename}.mmd\``)
    indexSection.push(`- SVG: \`svg/${diagram.filename}.svg\``)
    indexSection.push('')
}

const readmePath = join(root, 'docs', 'diagrams', 'README.md')
let readme = existsSync(readmePath) ? readFileSync(readmePath, 'utf8') : ''

const markerStart = '## SiteMap (карта сайта)'
const markerEnd = '## TaskFlow'
const sitemapBlock = indexSection.join('\n').trimEnd()

if (readme.includes(markerStart) && readme.includes(markerEnd)) {
    const before = readme.split(markerStart)[0].trimEnd()
    const after = readme.split(markerEnd).slice(1).join(markerEnd).trimStart()
    readme = `${before}\n\n${sitemapBlock}\n\n${markerEnd}\n${after}`
} else if (readme.includes(markerEnd)) {
    readme = readme.replace(markerEnd, `${sitemapBlock}\n\n${markerEnd}`)
} else {
    readme = `${readme.trimEnd()}\n\n${sitemapBlock}\n`
}

writeFileSync(readmePath, readme.endsWith('\n') ? readme : `${readme}\n`, 'utf8')

console.log(`Generated ${diagrams.length} sitemap SVG diagrams in docs/diagrams/svg/`)
