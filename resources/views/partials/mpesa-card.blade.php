<div class="mx-3 mb-4 mt-2 rounded-xl overflow-hidden border border-green-800/40">

    {{-- M-Pesa header --}}
    <div class="px-4 py-3 flex items-center gap-3"
         style="background: linear-gradient(135deg, #006400 0%, #00a000 50%, #007a00 100%);">
        <div class="flex-shrink-0">
            <div class="w-10 h-10 rounded-full bg-white/15 flex items-center justify-center border border-white/20">
                <span class="text-white font-black text-xs leading-none text-center">
                    M<br><span class="text-[8px] font-bold tracking-tight">PESA</span>
                </span>
            </div>
        </div>
        <div>
            <div class="text-white font-black text-sm tracking-wide">M-PESA</div>
            <div class="text-green-200 text-[10px]">Deposit Instantly</div>
        </div>
        <div class="ml-auto">
            <svg class="w-5 h-5 text-white/70" fill="currentColor" viewBox="0 0 24 24">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
            </svg>
        </div>
    </div>

    {{-- Payment details --}}
    <div class="bg-[#0a1a0a] px-4 py-3 space-y-2.5 border-t border-green-900/40">

        {{-- Paybill number --}}
        <div class="flex items-center justify-between">
            <span class="text-[10px] text-gray-500 uppercase tracking-widest font-medium">Paybill No.</span>
            <div class="flex items-center gap-2">
                <span class="text-white font-black text-base tracking-widest font-mono">4151665</span>
                <button
                    x-data="{ copied: false }"
                    @click="navigator.clipboard.writeText('247247'); copied = true; setTimeout(() => copied = false, 2000)"
                    class="text-[10px] px-2 py-0.5 rounded border transition-colors duration-150
                           border-green-700 text-green-400 hover:bg-green-700 hover:text-white"
                    :class="copied ? 'bg-green-700 text-white' : ''"
                    x-text="copied ? '✓ Copied' : 'Copy'"
                ></button>
            </div>
        </div>

        <div class="h-px bg-green-900/30"></div>

        {{-- Account number --}}
        <div class="flex items-center justify-between">
            <span class="text-[10px] text-gray-500 uppercase tracking-widest font-medium">Account No.</span>
            <div class="flex items-center gap-2">
                <span class="text-white font-black text-base tracking-widest font-mono">YOUR PHONE NO</span>
                <!--<button
                    x-data="{ copied: false }"
                    @click="navigator.clipboard.writeText('ANGEL001'); copied = true; setTimeout(() => copied = false, 2000)"
                    class="text-[10px] px-2 py-0.5 rounded border transition-colors duration-150
                           border-green-700 text-green-400 hover:bg-green-700 hover:text-white"
                    :class="copied ? 'bg-green-700 text-white' : ''"
                    x-text="copied ? '✓ Copied' : 'Copy'"
                ></button>-->
            </div>
        </div>

        <div class="h-px bg-green-900/30"></div>

        {{-- Instructions --}}
        <div class="text-[10px] text-gray-500 leading-relaxed">
            Go to <span class="text-green-400 font-semibold">M-Pesa</span> →
            Lipa na M-Pesa → Pay Bill → Enter paybill &amp; account → Amount → PIN
        </div>

        {{-- Minimum deposit note --}}
        <div class="flex items-center justify-between pt-0.5">
            <span class="text-[10px] text-gray-600">Min. deposit</span>
            <span class="text-green-400 font-bold text-xs">KES 50</span>
        </div>

    </div>
</div>
