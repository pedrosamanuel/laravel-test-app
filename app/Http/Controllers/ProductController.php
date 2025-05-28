<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware as ControllersMiddleware;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new ControllersMiddleware('auth:sanctum', except: ['index','show']),
        ];
    }
    public function index()
    {
        return Product::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'description' => 'required|max:255',
            'price' => 'required|min:0'
        ]);

        $product = $request->user()->products()->create($fields);

        return ['product' => $product];
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return ['product' => $product];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        Gate::authorize('modify', $product);
        $fields = $request->validate([
            'description' => 'max:255',
            'price' => 'min:0'
        ]);

        $product->update($fields);

        return ['product' => $product];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        Gate::authorize('modify', $product);
        $product->delete();
    }
}
