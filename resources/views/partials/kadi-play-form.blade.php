@auth
    <form
        id="kadiPlayForm"
        action="{{ $playKadiUrl }}"
        method="POST"
        target="_blank"
        style="display:none;"
    >
        @csrf {{-- remove this line if the remote Kadi endpoint doesn't expect Laravel's token --}}
        <input type="hidden" name="ggid" id="ggid" value="{{ $googleId }}">
    </form>
@endauth
