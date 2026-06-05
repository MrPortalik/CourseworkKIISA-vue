<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleContentImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_upload_content_image_with_relative_url(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/articles/content-images', [
            'image' => UploadedFile::fake()->image('diagram.png', 400, 300),
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['url']);

        $url = $response->json('url');
        $this->assertStringStartsWith('/storage/articles/content/', $url);
        $this->assertStringNotContainsString('://', $url);

        $path = str_replace('/storage/', '', $url);
        Storage::disk('public')->assertExists($path);
    }

    public function test_guest_cannot_upload_content_image(): void
    {
        Storage::fake('public');

        $response = $this->post('/articles/content-images', [
            'image' => UploadedFile::fake()->image('diagram.png'),
        ]);

        $response->assertRedirect('/login');
    }
}
