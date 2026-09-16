<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{

    public function index(): View
    {
        $this->authorize('viewAny', Service::class);

        return view('admin.services.index', [
            'services' => Service::orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Service::class);

        return view('admin.services.create', ['service' => new Service()]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $this->authorize('create', Service::class);

        Service::create($request->serviceData());

        return to_route('admin.services.index')->with('status', 'Service created.');
    }

    public function edit(Service $service): View
    {
        $this->authorize('update', $service);

        return view('admin.services.edit', compact('service'));
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $this->authorize('update', $service);

        $service->update($request->serviceData());

        return to_route('admin.services.index')->with('status', 'Service updated.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->authorize('delete', $service);

        $service->delete();

        return to_route('admin.services.index')->with('status', 'Service retired.');
    }
}
