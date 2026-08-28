<?php

use App\Livewire\Dashboard;
use App\Models\User;
use Carbon\CarbonImmutable;

function dashboardComponent(): Dashboard
{
    return new Dashboard;
}

test('live stats are deterministic for the same minute', function () {
    $now = CarbonImmutable::parse('2026-08-26 20:30:00');

    $first = dashboardComponent()->liveStats($now);
    $second = dashboardComponent()->liveStats($now);

    expect($first)->toBe($second);
});

test('live stats follow a diurnal curve peaking in the evening', function () {
    $component = dashboardComponent();

    $morning = $component->liveStats(CarbonImmutable::parse('2026-08-26 06:00:00'));
    $evening = $component->liveStats(CarbonImmutable::parse('2026-08-26 21:00:00'));

    expect($evening['onlineUsers'])->toBeGreaterThan($morning['onlineUsers']);
    expect($evening['liveTables'])->toBeGreaterThan($morning['liveTables']);
    expect($evening['activeGames'])->toBeGreaterThan($morning['activeGames']);
});

test('the jackpot grows through the day from the seeded opening pool', function () {
    $component = dashboardComponent();

    $midnight = $component->progressiveJackpot(CarbonImmutable::parse('2026-08-26 00:00:00'));
    $noon = $component->progressiveJackpot(CarbonImmutable::parse('2026-08-26 12:00:00'));
    $laterNoon = $component->progressiveJackpot(CarbonImmutable::parse('2026-08-26 12:01:00'));

    expect($midnight)->toBe(2_097_152);
    expect($noon)->toBeGreaterThan($midnight);
    expect($laterNoon)->toBeGreaterThan($noon);

    // Two renders in the same second must agree (no per-render jumps).
    expect(dashboardComponent()->progressiveJackpot(CarbonImmutable::parse('2026-08-26 18:33:21')))
        ->toBe(dashboardComponent()->progressiveJackpot(CarbonImmutable::parse('2026-08-26 18:33:21')));
});

test('countdown rolls to tomorrow after the 21:00 draw', function () {
    $component = dashboardComponent();

    $before = $component->secondsUntilNextDraw(CarbonImmutable::parse('2026-08-26 20:59:30'));
    $justAfter = $component->secondsUntilNextDraw(CarbonImmutable::parse('2026-08-26 21:00:30'));
    $noon = $component->secondsUntilNextDraw(CarbonImmutable::parse('2026-08-26 12:00:00'));

    expect($before)->toBe(30);
    expect($noon)->toBe(9 * 3600);
    expect($justAfter)->toBe(23 * 3600 + 59 * 60 + 30);
});

test('dashboard renders the computed jackpot and countdown seed', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $html = $this->get(route('dashboard'))->getContent();

    // The Alpine countdown must be seeded server-side with real seconds.
    expect(preg_match('/total: (\d+)/', $html, $m))->toBe(1);
    expect((int) $m[1])->toBeBetween(0, 86400);
});
