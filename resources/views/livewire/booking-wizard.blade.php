<div class="space-y-8">

    @if ($warning)
        <div class="rounded-md bg-amber-50 p-4 text-sm text-amber-900">{{ $warning }}</div>
    @endif

    <section>
        <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-500">1 &middot; Service</h3>
        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->services as $option)
                <button type="button" wire:click="chooseService({{ $option->id }})"
                        class="rounded-lg border p-4 text-left transition
                               {{ $serviceId === $option->id
                                  ? 'border-indigo-600 bg-indigo-50 ring-1 ring-indigo-600'
                                  : 'border-gray-200 bg-white hover:border-gray-400' }}">
                    <div class="font-medium text-gray-900">{{ $option->name }}</div>
                    <div class="mt-1 text-sm text-gray-500">
                        {{ $option->duration_minutes }} min &middot; {{ $option->price }}
                    </div>
                </button>
            @endforeach
        </div>
        @error('serviceId') <p class="mt-2 text-sm text-red-600">Please choose a service.</p> @enderror
    </section>

    @if ($this->service)
        <section>
            <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-500">2 &middot; Who with</h3>
            <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($this->staffOptions as $person)
                    <button type="button" wire:click="chooseStaff({{ $person->id }})"
                            class="rounded-lg border p-4 text-left transition
                                   {{ $staffId === $person->id
                                      ? 'border-indigo-600 bg-indigo-50 ring-1 ring-indigo-600'
                                      : 'border-gray-200 bg-white hover:border-gray-400' }}">
                        <div class="font-medium text-gray-900">{{ $person->name }}</div>
                        <div class="mt-1 text-sm text-gray-500">{{ $person->title }}</div>
                    </button>
                @empty
                    <p class="text-sm text-gray-500">Nobody currently offers this service.</p>
                @endforelse
            </div>
            @error('staffId') <p class="mt-2 text-sm text-red-600">Please choose a staff member.</p> @enderror
        </section>
    @endif

    @if ($this->staff)
        <section>
            <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-500">3 &middot; Date</h3>
            <input type="date" id="date" wire:model.live="date"
                   min="{{ now(config('booking.timezone'))->toDateString() }}"
                   max="{{ now(config('booking.timezone'))->addDays(config('booking.max_days_ahead'))->toDateString() }}"
                   class="mt-3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </section>

        <section>
            <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-500">4 &middot; Time</h3>
            <div wire:loading.class="opacity-40" wire:target="date,chooseStaff"
                 class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-5 lg:grid-cols-7">
                @forelse ($this->slots as $option)
                    @php $value = $option->startsAt->toIso8601String(); @endphp
                    <button type="button" wire:click="$set('slot', '{{ $value }}')"
                            class="rounded-md border px-2 py-2 text-sm transition
                                   {{ $slot === $value
                                      ? 'border-indigo-600 bg-indigo-600 text-white'
                                      : 'border-gray-200 bg-white text-gray-700 hover:border-gray-400' }}">
                        {{ $option->label(config('booking.timezone')) }}
                    </button>
                @empty
                    <p class="col-span-full text-sm text-gray-500">
                        No times available on this day. Try another date.
                    </p>
                @endforelse
            </div>
            @error('slot') <p class="mt-2 text-sm text-red-600">Please choose a time.</p> @enderror
        </section>
    @endif

    @if ($slot)
        <section class="rounded-lg bg-white p-6 shadow-sm">
            <h3 class="text-xs font-semibold uppercase tracking-widest text-gray-500">Confirm</h3>

            <p class="mt-3 text-gray-900">
                <span class="font-medium">{{ $this->service->name }}</span>
                with {{ $this->staff->name }}<br>
                {{ \Carbon\CarbonImmutable::parse($slot)->setTimezone(config('booking.timezone'))->format('l j F, g:i A') }}
                &middot; {{ $this->service->duration_minutes }} min
                &middot; {{ $this->service->price }}
            </p>

            <div class="mt-4">
                <label for="note" class="block text-sm text-gray-600">Anything we should know? (optional)</label>
                <textarea id="note" wire:model="note" rows="2"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>

            <button type="button" wire:click="book" wire:loading.attr="disabled"
                    class="mt-4 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50">
                <span wire:loading.remove wire:target="book">Confirm booking</span>
                <span wire:loading wire:target="book">Booking&hellip;</span>
            </button>
        </section>
    @endif

</div>
