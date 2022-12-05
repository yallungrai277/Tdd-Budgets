<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Profile photo</h2>
        <p class="mt-1 text-sm text-gray-600">Update your profile photo.</p>
    </header>


    <form method="post" action="{{ route('profile.photo') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="photo" :value="__('Photo')" />
            <input type="file" name="photo">
            <x-input-error class="mt-2" :messages="$errors->get('photo')" />
        </div>


        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600">Saved.</p>
            @endif
        </div>
    </form>
</section>
