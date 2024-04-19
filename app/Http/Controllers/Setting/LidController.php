<?php

namespace App\Http\Controllers\Setting;

use App\Clients\MoySklad;
use App\Clients\MsClient;
use App\Clients\newClient;
use App\Http\Controllers\Controller;
use App\Models\Automation;
use App\Models\employeeModel;
use App\Models\Scenario;
use Illuminate\Http\Request;

class LidController extends Controller
{

    function getLid(Request $request, $accountId)
    {
        $isAdmin = $request->isAdmin ?? 'NO';
        $fullName = $request->fullName ?? "Имя аккаунта";
        $uid = $request->uid ?? "логин аккаунта";
        //$main = employeeModel::getAllEmpl($accountId);

        $msClient = new MoySklad($accountId);
        $req = $msClient->getAll('employee');
        if ($req->status){
            $employee = $req->data->rows;
        } else return to_route('error', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $request->fullName ?? "Имя аккаунта",
            'uid' => $request->uid ?? "логин аккаунта",
            'message' => "",
        ]);
        //dd($employee);






        return view('setting.LID.main', [
            'employee' => $employee,


            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $fullName,
            'uid' => $uid,

            'message' => $request->message ?? '',
        ]);

    }

    function postLid(Request $request, $accountId)
    {
        $isAdmin = $request->isAdmin ?? 'NO';
        $fullName = $request->fullName ?? "Имя аккаунта";
        $uid = $request->uid ?? "логин аккаунта";


        return to_route('lid', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $request->fullName ?? "Имя аккаунта",
            'uid' => $request->uid ?? "логин аккаунта",
        ]);
    }

}
