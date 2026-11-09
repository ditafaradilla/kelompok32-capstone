<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_welcome_and_login_page(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_user_can_view_register_page(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret1234',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);
    }

    public function test_user_can_login_and_redirect_to_dashboard(): void
    {
        $user = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => Hash::make('secret1234'),
            'role' => 'user',
        ]);

        $response = $this->post('/login', [
            'email' => 'jane@example.com',
            'password' => 'secret1234',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_login_and_redirect_to_admin_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('secret1234'),
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'secret1234',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_authenticated_admin_can_view_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_view_edit_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard/edit-profil');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $user = User::factory()->create();
        $file = \Illuminate\Http\UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)->post('/dashboard/edit-profil', [
            'name' => 'Updated Name',
            'email' => $user->email,
            'phone' => '08123456789',
            'address' => 'Jl. Merdeka No. 1',
            'photo' => $file,
        ]);

        $response->assertRedirect(route('profile.edit'));
        $user->refresh();
        $this->assertNotNull($user->photo);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($user->photo);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'phone' => '08123456789',
            'address' => 'Jl. Merdeka No. 1',
        ]);
    }

    public function test_authenticated_user_can_remove_photo(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $user = User::factory()->create([
            'photo' => 'profiles/test.jpg',
        ]);
        \Illuminate\Support\Facades\Storage::disk('public')->put('profiles/test.jpg', 'fake content');

        $response = $this->actingAs($user)->post('/dashboard/edit-profil', [
            'name' => $user->name,
            'email' => $user->email,
            'remove_photo' => '1',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $user->refresh();
        $this->assertNull($user->photo);
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing('profiles/test.jpg');
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
