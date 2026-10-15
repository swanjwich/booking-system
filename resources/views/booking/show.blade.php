<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Booking confirmed</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <p class="font-mono text-sm text-gray-500">{{ $appointment->reference }}</p>

                <h3 class="mt-2 text-lg font-semibold text-gray-900">
                    {{ $appointment->service->name }}
                </h3>

                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">With</dt>
                        <dd class="text-gray-900">{{ $appointment->staffProfile->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">When</dt>
                        <dd class="text-gray-900">
                            {{ $appointment->starts_at->setTimezone(config('booking.timezone'))->format('l j F Y, g:i A') }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Length</dt>
                        <dd class="text-gray-900">{{ $appointment->duration_minutes }} minutes</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Price</dt>
                        <dd class="text-gray-900">{{ \Illuminate\Support\Number::currency($appointment->price_amount / 100, 'PHP') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Status</dt>
                        <dd class="text-gray-900">{{ $appointment->status->label() }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
