@auth
    <form
        id="kadiPlayForm"
        action="{{ $playKadiUrl }}"
        method="POST"
        style="display:none;"
    >
        <input type="hidden" name="ggid" id="ggid" value="{{ $googleId }}">
    </form>
@endauth
