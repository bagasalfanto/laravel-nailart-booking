<x-app-layout>
    <x-slot name="header">Profile</x-slot>

    <div class="max-w-7xl mx-auto px-2 sm:px-4">
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-slate-900">Personal Artist Profile</h1>
            <p class="mt-2 text-slate-500">Curate your personal professional presence.</p>
        </div>

        @include('profile.partials.update-profile-information-form')
    </div>
</x-app-layout>
