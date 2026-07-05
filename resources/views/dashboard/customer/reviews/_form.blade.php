@csrf

<div class="mb-5" x-data="{ rating: {{ old('rating', $review->rating ?? 5) }} }">
    <label class="block text-sm font-medium text-slate-700 mb-2">Rating</label>
    <div class="flex items-center gap-1 text-2xl">
        @for ($i = 1; $i <= 5; $i++)
            <button type="button" @click="rating = {{ $i }}"
                    class="focus:outline-none"
                    :class="rating >= {{ $i }} ? 'text-amber-500' : 'text-slate-300'">★</button>
        @endfor
    </div>
    <input type="hidden" name="rating" :value="rating">
    @error('rating')
        <p class="text-sm text-rose-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-5">
    <label class="block text-sm font-medium text-slate-700 mb-2">Review</label>
    <textarea name="content" rows="5"
              class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]"
              placeholder="Bagikan pengalamanmu...">{{ old('content', $review->content ?? '') }}</textarea>
    @error('content')
        <p class="text-sm text-rose-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="flex justify-end gap-3">
    <a href="{{ route('dashboard.my-reviews.index') }}" class="rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100">Batal</a>
    <button type="submit" class="rounded-lg bg-[#a23f66] px-5 py-2 text-sm font-medium text-white hover:opacity-90">Simpan</button>
</div>
