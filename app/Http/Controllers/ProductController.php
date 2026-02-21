<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search', '');
        
        $query = Product::with('batches');
        
        if (!empty($search)) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
        }
        
        return response()->json($query->paginate($limit));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string',
            'category' => 'nullable|string',
            'gst_rate' => 'nullable|numeric',
            'price' => 'required|numeric',
            'stock_level' => 'nullable|integer',
            'low_stock_threshold' => 'nullable|integer',
        ]);

        $product = Product::create($validated);

        // Auto-create a primary batch for ERP architecture transition
        \App\Models\ProductBatch::create([
            'tenant_id' => $product->tenant_id,
            'product_id' => $product->id,
            'batch_number' => 'B-' . strtoupper(uniqid()),
            'sale_price' => $product->price,
            'stock_level' => $product->stock_level ?? 0,
            'mrp' => $product->price,
            'expiry_date' => now()->addYear(), // default 1 year expiry
        ]);

        return response()->json($product->load('batches'), 201);
    }

    public function show(Product $product)
    {
        return $product->load('batches');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'sku' => 'nullable|string',
            'category' => 'nullable|string',
            'gst_rate' => 'nullable|numeric',
            'price' => 'sometimes|required|numeric',
            'stock_level' => 'nullable|integer',
            'low_stock_threshold' => 'nullable|integer',
        ]);

        $product->update($validated);
        
        // Update first batch if exists (for backwards compatibility demo)
        $batch = $product->batches()->first();
        if ($batch && $request->has('stock_level')) {
            $batch->update(['stock_level' => $request->stock_level]);
        }

        return $product->load('batches');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(null, 204);
    }

    public function stockReport()
    {
        return Product::whereColumn('stock_level', '<=', 'low_stock_threshold')->get();
    }
}
