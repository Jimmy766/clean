<?php

namespace App\Core\Base\Services;

use App\Core\Base\Classes\ModelConst;
use App\Core\Blocks\Models\ExceptionBlock;
use Illuminate\Support\Collection;

/**
 * Class LocationService
 * @package App\Core\Base\Services
 */
class CheckExceptionService
{

    /**
     * @param      $request
     * @param null $exception
     * @return Collection
     */
    public function execute($request, $exception = null): Collection
    {
        $validationsCollect = collect([]);

        $validation = $this->validateExceptionByIp($request);
        $validationsCollect->push($validation);
        $validation = $this->validateExceptionByDomain($request);
        $validationsCollect->push($validation);

        return $validationsCollect;
    }

    /**
     * @param $request
     * @return array
     */
    private function validateExceptionByIp($request): array
    {
        $ipUser  = $request->user_ip;
        $value   = ExceptionBlock::query()
            ->where('active', ModelConst::ENABLED)
            ->where('type', ModelConst::TYPE_EXCEPTION_IP)
            ->where('value', $ipUser)
            ->firstFromCache([ 'id_exception' ], ExceptionBlock::TAG_CACHE_MODEL);
        $message = __('IP EXCEPTION') . " - " . $ipUser;

        return [ 'value' => $value, 'message' => $message ];
    }

    /**
     * @param $request
     * @return array
     */
    private function validateExceptionByDomain($request): array
    {
        $headers = GetAllValuesFromHeaderService::execute($request);
        $headers = $headers->toArray();
        $hostExist = array_key_exists('host', $headers);
        $host = $hostExist ? $headers['host'] : "";
        $value   = ExceptionBlock::query()
            ->where('active', ModelConst::ENABLED)
            ->where('type', ModelConst::TYPE_EXCEPTION_DOMAIN)
            ->where('value', $host)
            ->firstFromCache([ 'id_exception' ], ExceptionBlock::TAG_CACHE_MODEL);
        $message = __('DOMAIN EXCEPTION') . " - " . $host;

        return [ 'value' => $value, 'message' => $message ];
    }

}
