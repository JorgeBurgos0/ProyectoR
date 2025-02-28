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

        foreach ($productos as $producto) {
            $producto->imagen = $producto->imagen ? asset('storage/' . $producto->imagen) : null;
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
        'stock'       => 'required|integer',
        'imagen'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // Validación de imagen
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $data = $request->all();

    if ($request->hasFile('imagen')) {
        $imagenPath = $request->file('imagen')->store('productos', 'public'); // Guarda en storage/app/public/productos
        $data['imagen'] = $imagenPath;
    }

    $producto = Producto::create($data);

    return response()->json($producto, 201);
}


    /**
     * Mostrar un producto específico.
     */
    public function show($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->imagen = $producto->imagen ? asset('storage/' . $producto->imagen) : null;
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
            'stock'       => 'required|integer',
            'imagen'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->all();

        if ($request->hasFile('imagen')) {
            // Eliminar la imagen anterior si existe
            if ($producto->imagen) {
                \Storage::disk('public')->delete($producto->imagen);
            }

            $imagenPath = $request->file('imagen')->store('productos', 'public');
            $data['imagen'] = $imagenPath;
        }

        $producto->update($data);

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
