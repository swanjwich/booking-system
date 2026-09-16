<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">New service</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.services.store') }}"
                  class="bg-white p-6 shadow-sm sm:rounded-lg">
                @csrf
                @include('admin.services.form')

                <div class="mt-6 flex items-center gap-4">
                    <x-primary-button>Save service</x-primary-button>
                    <a href="{{ route('admin.services.index') }}"
                       class="text-sm text-gray-600 hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
