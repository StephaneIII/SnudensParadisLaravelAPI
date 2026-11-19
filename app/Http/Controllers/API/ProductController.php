<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // create product
    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'attribute_values' => 'sometimes|array',
            'attribute_values.*' => 'required|array:attributes_id,value,min_value,max_value',
            'attribute_values.*.attributes_id' => 'required|integer|distinct|exists:attributes,attributes_id',
            'attribute_values.*.value' => 'required|string|max:255',
            'attribute_values.*.min_value' => 'nullable|numeric',
            'attribute_values.*.max_value' => 'nullable|numeric',
        ]);

        $product = DB::transaction(function () use ($validated) {
            $product = Product::create(Arr::only($validated, ['name', 'description', 'price']));

            if (array_key_exists('attribute_values', $validated)) {
                $product->setRelation('attributeValues', $product->attributeValues()->createMany($validated['attribute_values']));
            }

            return $product;
        });

        return response()->json(['product' => $product], 201);
    }

    // list all products with attributes
    public function index()
    {
        $products = Product::with('attributeValues.attribute')->get();

        return response()->json(['products' => $products], 200);
    }

    // read single product with attributes
    public function show($id)
    {
        $product = Product::with('attributeValues.attribute')->find($id);
        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json(['product' => $product], 200);
    }

    // update product
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
        ]);

        $product = Product::find($id);
        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
        ]);

        return response()->json(['product' => $product], 200);
    }

    // delete product
    public function destroy($id)
    {
        $product = Product::find($id);
        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
