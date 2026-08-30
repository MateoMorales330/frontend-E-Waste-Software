<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Producto::with('categorias')->latest()->get());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductoRequest $request)
    {
        // AÑADIDO: la categoría se guarda en la tabla pivote, no en productos.
        $datos = $request->validated();
        $categoriaId = $datos['categoria_id'];
        unset($datos['categoria_id']);
        $producto = Producto::create($datos);
        $producto->categorias()->sync([$categoriaId]);

        return response()->json($producto->load('categorias'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $producto)
    {
        return response()->json($producto->load('categorias'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        $datos = $request->validated();
        $categoriaId = $datos['categoria_id'] ?? null;
        unset($datos['categoria_id']);
        $producto->update($datos);
        if ($categoriaId !== null) {
            $producto->categorias()->sync([$categoriaId]);
        }

        return response()->json($producto->fresh()->load('categorias'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        $producto->delete();

        return response()->json(['message' => 'Eliminado']);
    }
}
