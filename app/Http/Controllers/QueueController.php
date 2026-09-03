<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\OrderService;

class QueueController extends Controller
{
    public function index(Request $request)
    {
        $branchId = session('branch_id') ?? auth()->user()->branch_id;
        $isDemo = (bool) auth()->user()->is_demo;
        $mid = auth()->user()->merchant_id;

        $q = Order::with(['customer', 'cashier', 'branch'])
            ->where('is_demo', $isDemo)
            ->when($mid, fn($qq) => $qq->where('merchant_id', $mid))
            ->when($branchId, fn($qq) => $qq->where('branch_id', $branchId))
            ->where('order_status', 'received')
            ->latest('order_date');

        $search = $request->get('search');
        if ($search) {
            $q->where(fn($qq) =>
                $qq->where('order_number', 'like', "%$search%")
                    ->orWhereHas('customer', fn($qq) => $qq->where('name', 'like', "%$search%"))
                    ->orWhereHas('customer', fn($qq) => $qq->where('phone', 'like', "%$search%"))
            );
        }

        $orders = $q->paginate(15);

        return view('queue.index', compact('orders'));
    }

    public function update(Request $request, Order $order, OrderService $svc)
    {
        $this->assertOrderAccess($order);

        $request->validate([
            'order_status' => 'required|in:received,ready,picked_up,complete,cancelled',
            'notes' => 'nullable|string',
        ]);

        // delegate to OrderService so rollback rules (ready->received, etc) apply consistently
        $svc->updateStatus($order, $request->order_status, auth()->user());

        if($request->notes !== null && $request->notes !== ''){
            $order->update(['notes' => $request->notes]);
        }

        return back()->with('success', 'Status order #' . $order->order_number . ' diupdate ke ' . $request->order_status);
    }

    private function assertOrderAccess(Order $order): void
    {
        $isDemo = (bool) auth()->user()->is_demo;
        if ((bool) $order->is_demo !== $isDemo) abort(403, 'Tidak punya akses order ini');
        $mid = auth()->user()->merchant_id;
        if ($mid && (int) $order->merchant_id !== (int) $mid) abort(403, 'Bukan order merchant Anda');
    }
}