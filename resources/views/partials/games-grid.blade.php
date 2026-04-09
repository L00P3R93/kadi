<div class="games-grid">
    @foreach ($games as $game)
        <div
            class="game-card"
            wire:click="openComingSoon('{{ $game['name'] }}')"
            role="button"
            tabindex="0"
            aria-label="Play {{ $game['name'] }}"
        >
            <div class="game-card__image-wrap">
                <img
                    src="{{ asset($game['path']) }}"
                    alt="{{ $game['name'] }}"
                    width="{{ $game['width'] }}"
                    height="{{ $game['height'] }}"
                    loading="lazy"
                />
            </div>
            <!--<div class="game-card__label">{{ $game['name'] }}</div>-->
        </div>
    @endforeach
</div>
