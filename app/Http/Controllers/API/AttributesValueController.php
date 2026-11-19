<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AttributesValue;
use Illuminate\Http\Request;

class AttributesValueController extends Controller
{
    // Crud
    public function create(Request $request)
    {
        $request->validate([
            'attribute_id' => 'required|integer',
            'value' => 'required|string|max:255',
        ]);

        $attributesValue = AttributesValue::create([
            'attribute_id' => $request->attribute_id,
            'value' => $request->value,
        ]);

        return response()->json(['attributesValue' => $attributesValue], 201);
    }

    // read all attributes values
    public function index()
    {
        $attributesValues = AttributesValue::all();

        return response()->json(['attributesValues' => $attributesValues], 200);
    }

    // read single attribute value
    public function show($id)
    {
        $attributesValue = AttributesValue::find($id);
        if (! $attributesValue) {
            return response()->json(['message' => 'Attribute value not found'], 404);
        }

        return response()->json(['attributesValue' => $attributesValue], 200);
    }

    // update attribute value
    public function update(Request $request, $id)
    {
        $request->validate([
            'attribute_id' => 'required|integer',
            'value' => 'required|string|max:255',
        ]);

        $attributesValue = AttributesValue::find($id);
        if (! $attributesValue) {
            return response()->json(['message' => 'Attribute value not found'], 404);
        }

        $attributesValue->update([
            'attribute_id' => $request->attribute_id,
            'value' => $request->value,
        ]);

        return response()->json(['attributesValue' => $attributesValue], 200);
    }

    // delete attribute value
    public function destroy($id)
    {
        $attributesValue = AttributesValue::find($id);
        if (! $attributesValue) {
            return response()->json(['message' => 'Attribute value not found'], 404);
        }

        $attributesValue->delete();

        return response()->json(['message' => 'Attribute value deleted successfully'], 200);
    }
}
