<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Guests must authenticate before entering a role-protected area.
     */
    public function test_guest_is_redirected_from_admin_dashboard_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_medewerker_is_forbidden_from_admin_dashboard(): void
    {
        $medewerker = User::factory()->magazijnMedewerker()->create();

        $response = $this->actingAs($medewerker)->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    public function test_admin_can_view_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Adminomgeving');
    }

    public function test_medewerker_can_view_medewerker_dashboard(): void
    {
        $medewerker = User::factory()->magazijnMedewerker()->create();

        $response = $this->actingAs($medewerker)->get(route('medewerker.dashboard'));

        $response->assertOk();
        $response->assertSee('Medewerkeromgeving');
    }

    public function test_admin_can_view_medewerker_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('medewerker.dashboard'));

        $response->assertOk();
    }
}
