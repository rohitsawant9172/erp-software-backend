<?php

namespace App\Http\Controllers;

use App\Models\ProductBatch;
use App\Models\Product;
use Illuminate\Http\Request;

class BatchInventoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $query = ProductBatch::with('product')
            ->when($search, fn($q) => $q->where('batch_number', 'like', "%{$search}%")
                ->orWhereHas('product', fn($q2) => $q2->where('name', 'like', "%{$search}%")));

        return response()->json($query->orderByDesc('created_at')->paginate($request->input('limit', 20)));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'   => 'required|exists:products,id',
            'batch_number' => 'required|string',
            'mfg_date'     => 'nullable|date',
            'expiry_date'  => 'nullable|date',
            'cost_price'   => 'nullable|numeric',
            'mrp'          => 'nullable|numeric',
            'sale_price'   => 'required|numeric',
            'stock_level'  => 'required|integer|min:0',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $validated['tenant_id'] = $product->tenant_id;

        $batch = ProductBatch::create($validated);

        // Sync product stock level
        $totalStock = ProductBatch::where('product_id', $product->id)->sum('stock_level');
        $product->update(['stock_level' => $totalStock]);

        return response()->json($batch->load('product'), 201);
    }

    public function show(ProductBatch $batch)
    {
        return response()->json($batch->load('product'));
    }

    public function update(Request $request, ProductBatch $batch)
    {
        $validated = $request->validate([
            'batch_number' => 'sometimes|required|string',
            'mfg_date'     => 'nullable|date',
            'expiry_date'  => 'nullable|date',
            'cost_price'   => 'nullable|numeric',
            'mrp'          => 'nullable|numeric',
            'sale_price'   => 'sometimes|required|numeric',
            'stock_level'  => 'sometimes|required|integer|min:0',
        ]);

        $batch->update($validated);

        // Sync product stock
        $product = $batch->product;
        $totalStock = ProductBatch::where('product_id', $product->id)->sum('stock_level');
        $product->update(['stock_level' => $totalStock]);

        return response()->json($batch->load('product'));
    }

    public function destroy(ProductBatch $batch)
    {
        $productId = $batch->product_id;
        $batch->delete();

        // Re-sync product stock
        $product = Product::find($productId);
        if ($product) {
            $totalStock = ProductBatch::where('product_id', $productId)->sum('stock_level');
            $product->update(['stock_level' => $totalStock]);
        }

        return response()->json(null, 204);
    }
}
