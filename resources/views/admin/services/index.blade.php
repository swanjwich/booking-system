<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Services</h2>
            <a href="{{ route('admin.services.create') }}"
               class="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                New service
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($services as $service)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $service->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $service->duration_minutes }} min
                                    @if ($service->buffer_minutes)
                                        <span class="text-gray-400">+{{ $service->buffer_minutes }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $service->price }}</td>
                                <td class="px-6 py-4 text-sm">
                                    @if ($service->is_active)
                                        <span class="rounded-full bg-green-100 px-2 py-1 text-xs text-green-800">Active</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-600">Hidden</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <a href="{{ route('admin.services.edit', $service) }}"
                                       class="text-indigo-600 hover:underline">Edit</a>

                                    <form action="{{ route('admin.services.destroy', $service) }}" method="POST"
                                          class="ml-3 inline"
                                          onsubmit="return confirm('Retire this service?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline">Retire</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No services yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $services->links() }}</div>
        </div>
    </div>
</x-app-layout>
