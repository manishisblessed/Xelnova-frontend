<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Permission;
use App\Models\User;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_page_with_slug_and_html_content(): void
    {
        $admin = $this->createAdminWithPermissions(['page_create', 'page_list']);

        $response = $this->actingAs($admin)->post(route('page.store'), [
            'title' => 'Shipping Information',
            'slug' => 'shipping-information',
            'content' => '<h2>Shipping</h2><p>Sample content</p>',
            'meta_title' => 'Shipping Information',
            'meta_description' => 'Shipping info page',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('page.index'));
        $this->assertDatabaseHas('pages', [
            'title' => 'Shipping Information',
            'slug' => 'shipping-information',
            'footer_section' => null,
            'show_in_footer' => 0,
        ]);
    }

    public function test_slug_uniqueness_is_enforced_with_auto_suffix_for_generated_slugs(): void
    {
        $admin = $this->createAdminWithPermissions(['page_create', 'page_list']);

        $this->actingAs($admin)->post(route('page.store'), [
            'title' => 'Privacy Policy',
            'slug' => '',
            'content' => '<p>First version</p>',
            'is_active' => true,
            'show_in_footer' => false,
        ])->assertRedirect(route('page.index'));

        $this->actingAs($admin)->post(route('page.store'), [
            'title' => 'Privacy Policy',
            'slug' => '',
            'content' => '<p>Second version</p>',
            'is_active' => true,
            'show_in_footer' => false,
        ])->assertRedirect(route('page.index'));

        $this->assertDatabaseHas('pages', ['slug' => 'privacy-policy']);
        $this->assertDatabaseHas('pages', ['slug' => 'privacy-policy-1']);
    }

    public function test_inactive_page_returns_404_on_frontend(): void
    {
        Page::create([
            'title' => 'Private Policy',
            'slug' => 'private-policy',
            'content' => '<p>Hidden</p>',
            'is_active' => false,
            'show_in_footer' => false,
        ]);

        $response = $this->get(route('marketplace.page', ['slug' => 'private-policy']));

        $response->assertNotFound();
    }

    public function test_legacy_privacy_route_resolves_db_content(): void
    {
        Page::create([
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'content' => '<p>Privacy body</p>',
            'is_active' => true,
            'show_in_footer' => true,
            'footer_section' => 'policy',
            'footer_order' => 1,
        ]);

        $response = $this->get(route('privacy'));

        $response->assertOk();
        $response->assertSee('Privacy Policy');
        $response->assertSee('Privacy body');
    }

    public function test_generic_slug_route_resolves_db_content(): void
    {
        Page::create([
            'title' => 'Custom Help',
            'slug' => 'custom-help',
            'content' => '<p>Custom help page</p>',
            'is_active' => true,
            'show_in_footer' => false,
        ]);

        $response = $this->get(route('marketplace.page', ['slug' => 'custom-help']));

        $response->assertOk();
        $response->assertSee('Custom Help');
        $response->assertSee('Custom help page');
    }

    public function test_footer_links_render_seeded_slugs(): void
    {
        $this->seed(PageSeeder::class);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('/about-us', false);
        $response->assertSee('/contact-us', false);
        $response->assertSee('/faq', false);
        $response->assertSee('/terms-conditions', false);
        $response->assertSee('/privacy-policy', false);
        $response->assertSee('/page/careers', false);
        $response->assertSee('/page/sitemap', false);
    }

    protected function createAdminWithPermissions(array $permissionNames): User
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'twofa' => 0,
        ]);

        foreach ($permissionNames as $permissionName) {
            $permission = Permission::findOrCreate($permissionName, 'web');
            $admin->givePermissionTo($permission);
        }

        return $admin;
    }
}
