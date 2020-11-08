<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\loginModel;
class loginController extends Controller
{
    public function create(Request $request)
    {
            $returnData = loginModel::getdata();
    }
}
