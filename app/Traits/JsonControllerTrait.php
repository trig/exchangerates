<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse as JsonResponse;

trait JsonControllerTrait {

    /**
     * For successfull responses
     * @param array $data
     * @return JsonResponse
     */
    public function renderSuccessResponse(array $data) {
        return new JsonResponse([
            'state' => true,
            'payload' => $data
                ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * For error responses
     * @param array $data
     * @return JsonResponse
     */
    public function renderErrorResponse(array $data) {
        return new JsonResponse([
            'state' => false,
            'error' => $data
                ], JsonResponse::HTTP_EXPECTATION_FAILED, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

}
