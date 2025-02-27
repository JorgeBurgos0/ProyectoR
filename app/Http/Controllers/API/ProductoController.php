<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{
    public function __construct()
    {
        // Se protege el CRUD con Sanctum.
        $this->middleware('auth:sanctum');
    }

    /**
     * Mostrar listado de productos, con opción de búsqueda.
     */
    public function index(Request $request)
    {
        if ($request->has('buscar')) {
            $buscar = $request->buscar;
            $productos = Producto::where('nombre', 'like', "%{$buscar}%")
                ->orWhere('descripcion', 'like', "%{$buscar}%")
                ->get();
        } else {
            $productos = Producto::all();
        }
        return response()->json($productos);
    }

    /**
     * Guardar un nuevo producto.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'precio'      => 'required|numeric',
            'stock'       => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $producto = Producto::create($request->all());

        return response()->json($producto, 201);
    }

    /**
     * Mostrar un producto específico.
     */
    public function show($id)
    {
        $producto = Producto::findOrFail($id);
        return response()->json($producto);
    }

    /**
     * Actualizar un producto.
     */
    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'precio'      => 'required|numeric',
            'stock'       => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $producto->update($request->all());

        return response()->json($producto);
    }

    /**
     * Eliminar un producto.
     */
    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->delete();
        return response()->json(['message' => 'Producto eliminado']);
    }
}
