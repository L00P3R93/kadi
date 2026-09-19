@php($minAge = config('kadi.min_age'))

<div class="flex flex-col gap-3">
    <label class="flex items-start gap-3 text-sm text-zinc-300">
        <input type="checkbox" name="age_confirmed" value="1" required
               @checked(old('age_confirmed'))
               class="mt-0.5 h-4 w-4 shrink-0 rounded border-zinc-600 bg-zinc-800 accent-[#f5c542]" />
        <span>{{ __('I confirm that I am :age years or older.', ['age' => $minAge]) }}</span>
    </label>
    @error('age_confirmed')
        <p class="-mt-1 text-sm font-medium text-red-500" role="alert">{{ $message }}</p>
    @enderror

    <label class="flex items-start gap-3 text-sm text-zinc-300">
        <input type="checkbox" name="terms" value="1" required
               @checked(old('terms'))
               class="mt-0.5 h-4 w-4 shrink-0 rounded border-zinc-600 bg-zinc-800 accent-[#f5c542]" />
        <span>
            {{ __('I accept the') }}
            <a href="{{ route('legal.terms') }}" target="_blank" rel="noopener" class="text-[#f5c542] underline">{{ __('Terms & Conditions') }}</a>
            {{ __('and') }}
            <a href="{{ route('legal.privacy') }}" target="_blank" rel="noopener" class="text-[#f5c542] underline">{{ __('Privacy Policy') }}</a>.
        </span>
    </label>
    @error('terms')
        <p class="-mt-1 text-sm font-medium text-red-500" role="alert">{{ $message }}</p>
    @enderror
</div>
