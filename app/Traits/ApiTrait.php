<?php

namespace App\Traits;

use Illuminate\Validation\ValidationException;


trait ApiTrait
{
    ## ************ Error Response ************ ##
    public function returnError($msg)
    {
        return response()->json([
            'status'        => false,
            'Error Code'    => 'E001',
            'message'       => $msg,
        ], 500);
    }
    ## ************ Success Response ************ ##
    public function returnSuccess($msg)
    {
        return response()->json([
            'status'        => true,
            'Error Code'    => '0',
            'Message'       => $msg,
        ], 200);
    }
    ## ************  Get Data ************ ##
    public function returnData($key, $value, $msg = null)
    {
        $msg = "Return Data Succussfully";
        return response()->json([
            'status'        => true,
            'Error Code'    => '0',
            'Message'       => $msg,
            $key            => $value,
        ], 200);
    }
    ## ************  Validation Message ************ ##
    public function returnValidation($validateErr = []){
        throw ValidationException::withMessages([
            $validateErr
        ]);
    }

}
