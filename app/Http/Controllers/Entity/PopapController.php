<?php

namespace App\Http\Controllers\Entity;

use App\Clients\MsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Controller;
use App\Services\ticket\DevService;
use App\Services\ticket\TicketService;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\ArrayShape;

class PopapController extends Controller
{
    public function Popup($object): Factory|View|Application
    {
        return view( 'popup.ViewPopap', ['Entity' => $object,] );
    }


}
