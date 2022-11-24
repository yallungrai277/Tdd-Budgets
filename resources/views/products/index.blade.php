<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Products') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 sm:grid-cols-2 ">
                @forelse ($products as $product)
                    <div class="w-full px-4 lg:px-0">
                        <div class="p-3 bg-white rounded shadow-md">
                            <div class="">
                                <div class="relative w-full mb-3 h-62 lg:mb-0">
                                    <img src="{{ asset('assets/images/no-product-image.png') }}" alt="Just a flower"
                                        class="object-fill w-full h-full rounded">
                                </div>
                                <div class="flex-auto
                                        p-2 justify-evenly">
                                    <div class="flex flex-wrap ">
                                        <div class="flex items-center justify-between w-full min-w-0 ">
                                            <h2 class="mr-auto text-lg cursor-pointer hover:text-gray-900 ">
                                                {{ $product->name }}
                                            </h2>
                                        </div>
                                    </div>
                                    <div class="mt-1 text-xl font-semibold">${{ $product->price }}</div>
                                    <form action="{{ route('cart.update', $product->id) }}" method="POST">
                                        @method('PUT')
                                        @csrf
                                        <button> Add to cart</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    No products found
                @endforelse
            </div>
        </div>
    </div>


</x-app-layout>
