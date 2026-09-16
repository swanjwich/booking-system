<div class="space-y-6">
    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $service->name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $service->description) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div>
            <x-input-label for="duration_minutes" value="Duration (minutes)" />
            <x-text-input id="duration_minutes" name="duration_minutes" type="number" step="5" min="5"
                          class="mt-1 block w-full"
                          :value="old('duration_minutes', $service->duration_minutes ?? 30)" required />
            <x-input-error class="mt-2" :messages="$errors->get('duration_minutes')" />
        </div>

        <div>
            <x-input-label for="buffer_minutes" value="Cleanup buffer" />
            <x-text-input id="buffer_minutes" name="buffer_minutes" type="number" step="5" min="0"
                          class="mt-1 block w-full"
                          :value="old('buffer_minutes', $service->buffer_minutes ?? 0)" required />
            <x-input-error class="mt-2" :messages="$errors->get('buffer_minutes')" />
        </div>

        <div>
            <x-input-label for="price" value="Price (₱)" />
            <x-text-input id="price" name="price" type="number" step="0.01" min="0"
                          class="mt-1 block w-full"
                          :value="old('price', $service->exists ? $service->price_amount / 100 : null)" required />
            <x-input-error class="mt-2" :messages="$errors->get('price')" />
        </div>
    </div>

    <label class="flex items-center">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1"
               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
               @checked(old('is_active', $service->is_active ?? true))>
        <span class="ml-2 text-sm text-gray-600">Available for booking</span>
    </label>
</div>
