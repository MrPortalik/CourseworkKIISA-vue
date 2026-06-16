import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'fs'
import { join, dirname } from 'path'
import { fileURLToPath } from 'url'
import { execSync } from 'child_process'

const __dirname = dirname(fileURLToPath(import.meta.url))
const root = join(__dirname, '..')
const flowsPath = join(root, 'docs', 'flows.md')
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
    const blockRe = /## ((?:TaskFlow|UserFlow):[^\n]+)\s+```mermaid\s+([\s\S]*?)```/g

    let match
    let index = 0

    while ((match = blockRe.exec(markdown)) !== null) {
        index += 1
        const title = match[1].trim()
        const prefix = title.startsWith('TaskFlow') ? 'taskflow' : 'userflow'
        const slug = slugify(title.replace(/^(TaskFlow|UserFlow):\s*/, ''))
        const filename = `${prefix}-${String(index).padStart(2, '0')}-${slug || 'diagram'}`

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

if (!existsSync(configPath)) {
    writeFileSync(configPath, JSON.stringify({
        theme: 'neutral',
        themeVariables: {
            fontFamily: 'Arial, sans-serif',
            fontSize: '14px',
        },
        flowchart: {
            htmlLabels: true,
            curve: 'basis',
        },
    }, null, 2))
}

const markdown = readFileSync(flowsPath, 'utf8')
const diagrams = parseDiagrams(markdown)

if (diagrams.length === 0) {
    throw new Error(`No diagrams found in ${flowsPath}`)
}

const readmePath = join(root, 'docs', 'diagrams', 'README.md')
let preservedSitemapSection = ''

if (existsSync(readmePath)) {
    const existing = readFileSync(readmePath, 'utf8')
    const markerStart = '## SiteMap (карта сайта)'
    const markerEnd = '## TaskFlow'

    if (existing.includes(markerStart)) {
        const afterStart = existing.split(markerStart)[1]
        preservedSitemapSection = afterStart.includes(markerEnd)
            ? `${markerStart}${afterStart.split(markerEnd)[0].trimEnd()}\n\n`
            : `${markerStart}${afterStart.trimEnd()}\n\n`
    }
}

const indexLines = [
    '# SVG-диаграммы потоков — портал КИИСА',
    '',
    'Файлы для вставки в Word: **Вставка → Рисунки → Этот устройство** и выберите нужный `.svg` из папки `svg/`.',
    '',
    'Перегенерация TaskFlow/UserFlow: `node scripts/generate-flow-diagrams.mjs`',
    'Перегенерация SiteMap: `node scripts/generate-sitemap-diagrams.mjs`',
    '',
]

if (preservedSitemapSection) {
    indexLines.push(preservedSitemapSection.trimEnd(), '')
}

indexLines.push('## TaskFlow', '')

let currentSection = 'TaskFlow'

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

    if (diagram.title.startsWith('UserFlow') && currentSection === 'TaskFlow') {
        indexLines.push('', '## UserFlow', '')
        currentSection = 'UserFlow'
    }

    indexLines.push(`### ${diagram.title}`)
    indexLines.push('')
    indexLines.push(`- Mermaid: \`mmd/${diagram.filename}.mmd\``)
    indexLines.push(`- SVG: \`svg/${diagram.filename}.svg\``)
    indexLines.push('')
}

writeFileSync(readmePath, indexLines.join('\n'), 'utf8')

console.log(`Generated ${diagrams.length} SVG diagrams in docs/diagrams/svg/`)
