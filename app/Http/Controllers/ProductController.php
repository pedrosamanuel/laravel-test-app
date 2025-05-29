<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware as ControllersMiddleware;
use Illuminate\Support\Facades\Gate;
use OpenApi\Annotations as OA;

class ProductController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new ControllersMiddleware('auth:sanctum', except: ['index','show']),
        ];
    }

    /**
     * @OA\Get(
     *     path="/api/product",
     *     summary="Listar todos los productos",
     *     tags={"Productos"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de productos",
     *         @OA\JsonContent(type="array",
     *             @OA\Items(ref="#/components/schemas/Product")
     *         )
     *     )
     * )
     */
    public function index()
    {
        return Product::all();
    }

    /**
     * @OA\Post(
     *     path="/api/product",
     *     summary="Crear un nuevo producto",
     *     tags={"Productos"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"description","price"},
     *             @OA\Property(property="description", type="string", maxLength=255),
     *             @OA\Property(property="price", type="number", format="float", minimum=0)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Producto creado",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'description' => 'required|max:255',
            'price' => 'required|min:0'
        ]);

        $product = $request->user()->products()->create($fields);

        return response(['product' => $product], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/product/{id}",
     *     summary="Mostrar un producto específico",
     *     tags={"Productos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del producto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(response=404, description="Producto no encontrado")
     * )
     */
    public function show(Product $product)
    {
        return ['product' => $product];
    }

    /**
     * @OA\Put(
     *     path="/api/product/{id}",
     *     summary="Actualizar un producto",
     *     tags={"Productos"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del producto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="description", type="string", maxLength=255),
     *             @OA\Property(property="price", type="number", format="float", minimum=0)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto actualizado",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(response=401, description="No autorizado"),
     *     @OA\Response(response=404, description="Producto no encontrado")
     * )
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
     * @OA\Delete(
     *     path="/api/product/{id}",
     *     summary="Eliminar un producto",
     *     tags={"Productos"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del producto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Producto eliminado"),
     *     @OA\Response(response=401, description="No autorizado"),
     *     @OA\Response(response=404, description="Producto no encontrado")
     * )
     */
    public function destroy(Product $product)
    {
        Gate::authorize('modify', $product);
        $product->delete();

        return response('deleted', 204);
    }
}
