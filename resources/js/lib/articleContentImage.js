const UPLOAD_URL = '/articles/content-images'

export async function uploadArticleContentImage(file) {
    const formData = new FormData()
    formData.append('image', file)

    const { data } = await window.axios.post(UPLOAD_URL, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
    })

    return data.url
}
