<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            'about' => [
                ['title' => 'Contact Us', 'slug' => 'contact-us'],
                ['title' => 'About Us', 'slug' => 'about-us'],
                ['title' => 'Careers', 'slug' => 'careers'],
                ['title' => 'Press', 'slug' => 'press'],
                ['title' => 'Corporate Information', 'slug' => 'corporate-information'],
            ],
            'help' => [
                ['title' => 'Payments', 'slug' => 'payments'],
                ['title' => 'Shipping', 'slug' => 'shipping'],
                ['title' => 'Cancellation & Returns', 'slug' => 'cancellation-returns'],
                ['title' => 'FAQ', 'slug' => 'faq'],
                ['title' => 'Report Infringement', 'slug' => 'report-infringement'],
            ],
            'policy' => [
                ['title' => 'Return Policy', 'slug' => 'return-policy'],
                ['title' => 'Terms of Use', 'slug' => 'terms-conditions'],
                ['title' => 'Security', 'slug' => 'security'],
                ['title' => 'Privacy Policy', 'slug' => 'privacy-policy'],
                ['title' => 'Sitemap', 'slug' => 'sitemap'],
            ],
        ];

        foreach ($sections as $pages) {
            foreach ($pages as $page) {
                Page::updateOrCreate(
                    ['slug' => $page['slug']],
                    [
                        'title' => $page['title'],
                        'content' => $this->sampleContent($page['title']),
                        'meta_title' => $page['title'] . ' | Xelnova',
                        'meta_description' => 'Learn more about ' . $page['title'] . ' on Xelnova.',
                        'is_active' => true,
                        'show_in_footer' => false,
                        'footer_section' => null,
                        'footer_order' => 0,
                    ]
                );
            }
        }
    }

    protected function sampleContent(string $title): string
    {
        return <<<HTML
<h2>{$title}</h2>
<p>This is a sample {$title} page managed from the admin CMS module.</p>
<p>Update this content anytime from <strong>Admin → Content → Pages</strong>.</p>
<ul>
    <li>Rich text content can be edited with the WYSIWYG editor.</li>
    <li>Page URL is controlled by the slug.</li>
    <li>Footer visibility can be toggled from page settings.</li>
</ul>
HTML;
    }
}
