<?php

namespace App\Core\Blocks\Services;

use App\Core\Blocks\Models\Block;
use App\Core\Casino\Models\CasinoGame;
use App\Core\Base\Classes\ModelConst;
use App\Core\Lotteries\Models\Lottery;
use Illuminate\Support\Collection;

class CheckBlockProductsService
{

    /**
     * @param      $request
     * @param null $exceptions
     * @return Collection
     */
    public function execute($request, $exceptions = null): Collection
    {
        $validationsCollect = collect([]);
        if ($exceptions === 'yes') {
            return $validationsCollect;
        }

        $validation = $this->validateBlockByOneProduct($request);
        $validationsCollect->push($validation);
        $validation = $this->validateBlockByListProduct($request);
        $validationsCollect->push($validation);

        return $validationsCollect;
    }

    private function validateBlockByOneProduct($request): array
    {
        $product     = $request->product;
        $typeProduct = $request->type_product;

        $value = Block::query()
            ->join('languages', 'languages.id_language', '=', 'blocks.id_blockable')
            ->where('active', ModelConst::ENABLED)
            ->where('type', ModelConst::TYPE_BLOCK_LANGUAGE)
            ->whereNull('id_entityable')
            ->whereNotNull('id_blockable')
            ->where('id_blockable', $product)
            ->whereNull('languages.deleted_at');

        if ($typeProduct === ModelConst::TYPE_BLOCK_TYPE_PRODUCT_LOTTERY) {
            $value = $value->where('type_blockable', Lottery::class);
        }
        if ($typeProduct === ModelConst::TYPE_BLOCK_TYPE_PRODUCT_GAME) {
            $value = $value->where('type_blockable', CasinoGame::class);
        }

        $value = $value->firstFromCache([ 'id_block' ], Block::TAG_CACHE_MODEL);

        $message = __('ONE PRODUCT BLOCK');
        $message = "{$message} - {$product} ";
        if($product === null && $typeProduct === null){
           $value = null;
        }

        return [ 'type' => 'one_product_block', 'value' => $value, 'message' => $message ];
    }

    private function validateBlockByListProduct($request): array
    {
        $listProduct     = $request->list_product;

        $value = Block::query()
            ->join('languages', 'languages.id_language', '=', 'blocks.id_blockable')
            ->where('active', ModelConst::ENABLED)
            ->where('type', ModelConst::TYPE_BLOCK_LANGUAGE)
            ->whereNull('id_entityable')
            ->whereNotNull('id_blockable')
            ->where('value', $listProduct)
            ->whereNull('languages.deleted_at');

        $value = $value->firstFromCache([ 'id_block' ], Block::TAG_CACHE_MODEL);

        $message = __('LIST PRODUCT BLOCK');
        $message = "{$message} - {$listProduct} ";
        $value = $listProduct === null ? null : $value;

        return [ 'type' => 'list_product_block', 'value' => $value, 'message' => $message ];
    }
}
