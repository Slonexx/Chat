<?php

namespace App\Http\Controllers\integration;

use App\Clients\KassClient;
use App\Clients\MsClient;
use App\Clients\testKassClient;
use App\Http\Controllers\Controller;
use App\Models\employeeModel;
use App\Services\AdditionalServices\AttributeService;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;

class connectController extends Controller
{
    public function connectClient(Request $request, $accountId): \Illuminate\Http\JsonResponse
    {
        $employeeId = $request->employee ?? "";

        $existingRecords = employeeModel::where('employeeId', $employeeId)->first();

        if ($existingRecords != null) {
            return response()->json([
                'accountId' => $existingRecords->accountId,
                'employeeId' => $existingRecords->employeeId,
                'employeeName' => $existingRecords->employeeName,

                'email' => $existingRecords->email,
                'password' => $existingRecords->password,
                'appId' => $existingRecords->appId,

                'access' => $existingRecords->access,

                'cabinetUserId' => $existingRecords->cabinetUserId,
                'accessToken' => $existingRecords->accessToken,
                'refreshToken' => $existingRecords->refreshToken,
            ]);
        }


        return response()->json([
            'status' => 500,
            'message' => 'Ошибка с индикатором сотрудника, проблема поиска',
        ]);

    }

}
