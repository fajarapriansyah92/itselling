<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = User::query()
            ->where('role', UserRole::Customer)
            ->withCount('orders')
            ->withSum(['orders as total_spent' => fn ($query) => $query->whereIn('status', OrderStatus::revenueStatuses())], 'total')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $keyword = '%'.$request->string('search')->trim()->toString().'%';

                $query->where(fn ($query) => $query->where('name', 'like', $keyword)->orWhere('email', 'like', $keyword));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.customers.index', ['customers' => $customers]);
    }

    public function show(User $customer): View
    {
        abort_unless($customer->role === UserRole::Customer, 404);

        return view('admin.customers.show', [
            'customer' => $customer,
            'orders' => $customer->orders()->withCount('items')->latest()->get(),
        ]);
    }
}
