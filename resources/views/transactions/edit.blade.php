<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Transaction') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="post" action="{{ route('transactions.update', $transaction->id) }}"
                        class="mt-6 space-y-6">
                        @method('PUT')
                        @csrf
                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <x-text-input id="description" name="description" type="text" class="mt-1 block w-full"
                                value="{{ old('description', $transaction->description) }}" />
                            <x-input-error :messages="$errors->first('description')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="description" :value="__('Category')" />
                            <select name="category_id" id=""
                                class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">None</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected($category->id == old('category_id', $transaction->category_id))>
                                        {{ $category->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->first('category_id')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="amount" :value="__('Amount')" />
                            <x-text-input id="amount" name="amount" type="text" class="mt-1 block w-full"
                                value="{{ old('amount', $transaction->amount) }}" />
                            <x-input-error :messages="$errors->first('amount')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="date" :value="__('Date')" />
                            <x-text-input id="date" name="date" type="date" class="mt-1 block w-full"
                                value="{{ old('date', $transaction->date->format('Y-m-d')) }}" />
                            <x-input-error :messages="$errors->first('date')" class="mt-2" />
                        </div>
                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
