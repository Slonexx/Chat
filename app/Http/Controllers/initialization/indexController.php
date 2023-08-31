<?php

namespace App\Http\Controllers\initialization;

use App\Http\Controllers\Controller;
use App\Http\Controllers\vendor\VendorApiController;
use Illuminate\Http\Request;

class indexController extends Controller
{
    public function initialization(Request $request): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $contextKey = $request->contextKey;
        if ($contextKey == null) {
            return view("main.dump");
        }
        $vendorAPI = new VendorApiController();
        $employee = $vendorAPI->context($contextKey);

        $accountId = $employee->accountId;
        $fullName = $employee->fullName;
        $uid = $employee->uid;

        $isAdmin = $employee->permissions->admin->view;

        return to_route('main', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $fullName,
            'uid' => $uid,
        ]);
    }

    public function index(Request $request, $accountId): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {



        $isAdmin = $request->isAdmin;
        $fullName = $request->fullName;
        $uid = $request->uid;

        return view("main.index" , [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'fullName' => $fullName,
            'uid' => $uid,
        ] );

    }

}
