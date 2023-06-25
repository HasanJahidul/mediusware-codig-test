<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;
use Exception;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
      // get products with relationship prices and prices also with relationship product_variant_one, product_variant_two, product_variant_three
        $products = Product::with(['prices', 'prices.product_variant_1', 'prices.product_variant_2', 'prices.product_variant_3'])->paginate(3);
        // $product_variants = ProductVariant::all();
        $result = Variant::with([
                                'productVariants' => function ($query) {
                                    $query->select('id', 'variant', 'variant_id');
                                }
                            ])
                            ->select('id', 'title')
                            ->get()
                            ->toArray();
        foreach ($result as $key => $value) {
            // now product_variants array contains multiple array with duplicate values in column variant
            // so we need to make the variant column value unique
            $product_variants = array_unique(array_column($value['product_variants'], 'variant'));
            // now we need to remove the duplicate values from the product_variants array 
            // and store the unique values in the product_variants array with variant key and product_variant_id with the values
            $result[$key]['product_variants'] = array_map(function ($variant) use ($value) {
                // here we are getting the product_variant_id of the unique variant value
                $product_variant_id = array_search($variant, array_column($value['product_variants'], 'variant'));
                // here we are getting the id of the product_variant table
                $product_variant = ProductVariant::find($value['product_variants'][$product_variant_id]['id']);
                // here we are getting the id of the variant table
                $variant_id = Variant::find($value['product_variants'][$product_variant_id]['variant_id']);
                return [
                    'variant' => $variant,
                    'variant_id' => $variant_id->id,
                    'product_variant_id' => $product_variant->id
                ];
            }, $product_variants);
        }
        $product_variants = json_decode(json_encode($result), FALSE);
        return view('products.index', compact('products','product_variants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        return view('products.edit', compact('variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
    public function search(Request $request)
    {
        // can be filtered by 1 or more field
        $title = $request->title;
        $variant = $request->variant;
        $price_from = $request->price_from;
        $price_to = $request->price_to;
        $date = $request->date;

        $vp = [$price_from, $price_to, $variant];

        $result = Variant::with([
            'productVariants' => function ($query) {
                $query->select('id', 'variant', 'variant_id');
            }
        ])
        ->select('id', 'title')
        ->get()
        ->toArray();
        foreach ($result as $key => $value) {
        // now product_variants array contains multiple array with duplicate values in column variant
        // so we need to make the variant column value unique
        $product_variants = array_unique(array_column($value['product_variants'], 'variant'));
        // now we need to remove the duplicate values from the product_variants array 
        // and store the unique values in the product_variants array with variant key and product_variant_id with the values
        $result[$key]['product_variants'] = array_map(function ($variant) use ($value) {
        // here we are getting the product_variant_id of the unique variant value
        $product_variant_id = array_search($variant, array_column($value['product_variants'], 'variant'));
        // here we are getting the id of the product_variant table
        $product_variant = ProductVariant::find($value['product_variants'][$product_variant_id]['id']);
        // here we are getting the id of the variant table
        $variant_id = Variant::find($value['product_variants'][$product_variant_id]['variant_id']);
        return [
        'variant' => $variant,
        'variant_id' => $variant_id->id,
        'product_variant_id' => $product_variant->id
        ];
        }, $product_variants);
        }
        $product_variants = json_decode(json_encode($result), FALSE);

        try{
            $products = Product::with('prices')
                ->when($title, function ($query, $title) {
                    return $query->where('title', 'like', '%'.$title.'%');
                })
                ->when($date, function ($query, $date) {
                    return $query->whereDate('created_at', $date);
                })->whereHas('prices', function($q) use($vp){

                    $price_from = $vp[0] ;
                    $price_to = $vp[1] ;
                    $variant = $vp[2] ;

                    $q->when($price_from, function ($query, $price_from) {
                        return $query->where('price', '>=', intval($price_from));
                    })->when($price_to, function ($query, $price_to) {
                        return $query->where('price', '<=', intval($price_to));
                    })->when($variant, function ($query, $variant) {
                        return $query->whereRaw("(product_variant_one = $variant or product_variant_two = $variant or product_variant_three = $variant)");
                    });
                })->paginate(3);
            $products->appends($request->all());

        } catch (Exception $e) {
            return $e->getMessage();
        }
        return view('products.index', compact('products', 'product_variants'));
    }
}
