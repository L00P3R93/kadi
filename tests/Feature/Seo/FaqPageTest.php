<?php

use App\Support\Faqs;

it('serves the faq page with every question and FAQPage markup', function () {
    $response = $this->get('/faq')->assertOk()->assertSee('Kadi FAQ');

    foreach (Faqs::all() as $faq) {
        $response->assertSee($faq['q'], false);
    }

    $response->assertSee('"@type":"FAQPage"', false);
    $response->assertSee('<link rel="canonical" href="'.rtrim(config('app.url'), '/').'/faq">', false);
});

it('keeps the faq off the home page and links to it', function () {
    $this->get('/')->assertOk()
        ->assertDontSee('FAQPage', false)
        ->assertSee(route('faq'), false);
});

it('lists the faq in the sitemap', function () {
    expect(file_get_contents(public_path('sitemap.xml')))->toContain('https://kadi.online/faq');
});
