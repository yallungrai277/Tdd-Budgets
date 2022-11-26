<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Checkout') }}
        </h2>
    </x-slot>

    <x-slot name="css">
        <style>
            .base-stripe-element {
                border: 1px solid #D1D5DA;
                padding: 12px;
                border-radius: 5px;
                font-family: inherit;
            }

            #card-errors {
                color: rgb(233 49 49)
            }
        </style>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    You are paying ${{ $cart->totalPrice() }}.
                    <form id="payment-form" action="{{ route('orders.store') }}" method="POST" class="mt-3">
                        @csrf
                        @guest

                            <input
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                                id="email" name="email" type="text" value="" placeholder="Email">
                            <x-input-error :messages="$errors->first('email')" class="mt-2" />
                        @endguest

                        <div id="card-element" class="mt-4">
                            <!-- Elements will create input elements here -->
                        </div>

                        <!-- We'll put the error messages in this element -->
                        <div id="card-errors" role="alert"></div>

                        <br>

                        <button
                            class='inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150'
                            type="submit" id="submit-btn">Submit</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
    <x-slot name="js">
        <script src="https://js.stripe.com/v3/"></script>

        <script>
            var style = {
                base: {
                    iconColor: '#666EE8',
                    color: '#31325F',
                    lineHeight: '40px',
                    fontWeight: 300,
                    fontFamily: 'Helvetica Neue',
                    fontSize: '15px',

                    '::placeholder': {
                        color: '#CFD7E0',
                    },
                },
            };
            const stripe = Stripe("{{ config('services.stripe.publishable_key') }}");
            const elements = stripe.elements();
            const displayError = document.getElementById('card-errors');


            var card = elements.create("card", {
                hidePostalCode: true,
                classes: {
                    base: 'base-stripe-element'
                }
            });
            card.mount("#card-element");

            card.on('change', ({
                error
            }) => {

                if (error) {
                    displayError.textContent = error.message;
                } else {
                    displayError.textContent = '';
                }
            });

            const form = document.getElementById('payment-form');

            form.addEventListener('submit', async (event) => {
                document.getElementById('submit-btn').disabled = true;
                event.preventDefault();

                const res = stripe.createToken(card).then(function(result) {
                    if (result.token) {
                        let token = result.token;
                        const input = document.createElement("input");
                        input.setAttribute('type', 'hidden');
                        input.setAttribute('name', 'token');
                        input.setAttribute('value', token.id);
                        form.appendChild(input);
                        form.submit();
                    }
                    if (result.error) {
                        document.getElementById('submit-btn').disabled = false;
                    }
                });

            });
        </script>
    </x-slot>
</x-app-layout>
