<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Extension;
use Exception;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Carbon\Carbon;
use App\Helpers\UUID;

class ExtensionApiController extends Controller
{

    public function getExtensionsList(Request $request){

        // Fetch all extensions
        try {
            $extions_list = Extension::orderBy('extension', 'desc')->get();
            sleep(30);
        } catch (QueryException $e){
            return response()->json([
                'code' => 505,
                'error' => [
                    'message' => 'Oop! Something went wrong, while trying to fecth extensions.',
                    'exception' => $e
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'code' => 504,
                'error' => [
                    'message' => 'Oop! Something went wrong, while trying to fecth extensions.',
                    'exception' => $e
                ]
            ]);
        }

        return response()->json([
            'code' => 200,
            'success' => [
                'message' => 'Successfully fecthed extensions',
                'data' => $extions_list
            ]
        ]);
    }

    public function checkExtension(Request $request, $ext_number){

    }

    public function getExtension(Request $request){

        $user_uuid = $request->headers->get('VKAPP-USERID');

        DB::beginTransaction();
        try {
            $extension = Extension::where(
                    'status', 1
                )
                ->whereNull('token')
                ->lockForUpdate()->firstOrFail();
            $uuid = UUID::generate(40, Extension::class, 'token');
            $extension->update(['token' => $uuid]);
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            return response()->json([
                'code' => 400,
                'error' => [
                    'message' => 'Sorry there is no available extension at this moment, we will let you know when there are available extensions.',
                    'exception' => $e
                ]
            ]);
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'code' => 500,
                'error' => [
                    'message' => 'Oop! Something went wrong, while getting extension.',
                    'exception' => $e
                ]
            ]);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'code' => 505,
                'error' => [
                    'message' => 'Oop! Something went wrong, while getting extension.',
                    'exception' => $e
                ]
            ]);

        }

        DB::commit();
        return response()->json([
            'code' => 200,
            'success' => [
                'message' => 'Successfully fecthed one available extension',
                'extension' => $extension,
                'reserved_token' => $uuid
            ]
        ]);

    }

    public function triggerExtension(Request $request){

        $user_uuid = $request->headers->get('VKAPP-USERID');
        $reserved_token = $request->input('reserved_token');
        $ext = $request->input('ext');
        $action = $request->input('action');
        $response_text = "";
        DB::beginTransaction();

        try {
            $extension = Extension::where([
                    ['extension', $ext]
                ])->firstOrFail();

        } catch (ModelNotFoundException $e) {
            DB::rollback();
            return response()->json([
                'code' => 400,
                'error' => [
                    'message' => 'Query result not found with this extension'.$ext,
                    'exception' => $e
                ]
            ]);

        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'code' => 500,
                'error' => [
                    'message' => 'Oop! Something went wrong, while getting extension.',
                    'exception' => $e
                ]
            ]);

        } catch (Exception $e){
            DB::rollback();
            return response()->json([
                'code' => 505,
                'error' => [
                    'message' => 'Oop! Something went wrong, while getting extension.',
                    'exception' => $e
                ]
            ]);

        }

        if($extension->token == null){
            DB::rollback();
            return response()->json([
                'code' => 302,
                'error' => [
                    'message' => "Extension haven't reserved yet",
                    'exception' => ''
                ]
            ]);
        }

        if($reserved_token !== $extension->token){
            DB::rollback();
            return response()->json([
                'code' => 300,
                'error' => [
                    'message' => 'Extension already reserved with other user.',
                    'exception' => ''
                ]
            ]);
        }

        switch ($action) {
            case 'register':
                try {
                    $extension->update(['status' => 0, 'last_registered' => Carbon::now()->format('Y-m-d H:i:s')]);
                    $response_text = "registered";
                } catch (QueryException $e) {
                    DB::rollback();
                    return response()->json([
                        'code' => 500,
                        'error' => [
                            'message' => 'Oop! Something went wrong, failed to update extension',
                            'exception' => $e
                        ]
                    ]);
                } catch (Exception $e) {
                    DB::rollback();
                    return response()->json([
                        'code' => 505,
                        'error' => [
                            'message' => 'Oop! Something went wrong, failed to update extension',
                            'exception' => $e
                        ]
                    ]);
                }
                break;

            case 'release':
                try {
                    $extension->update(['status' => 1, 'last_registered' => null, 'token' => null]);
                    $response_text = "released";
                } catch (QueryException $e) {
                    DB::rollback();
                    return response()->json([
                        'code' => 500,
                        'error' => [
                            'message' => 'Oop! Something went wrong, failed to update extension',
                            'exception' => $e
                        ]
                    ]);
                } catch (Exception $e) {
                    DB::rollback();
                    return response()->json([
                        'code' => 505,
                        'error' => [
                            'message' => 'Oop! Something went wrong, failed to update extension',
                            'exception' => $e
                        ]
                    ]);
                }
                break;

            default:
                DB::rollback();
                return response()->json([
                    'code' => 202,
                    'error' => [
                        'message' => 'No action specified in request.'
                    ]
                ]);
                break;
        }


        DB::commit();
        return response()->json([
            'code' => 200,
            'success' => [
                'message' => 'Successfully '.$response_text.' extension : '.$ext
            ]
        ]);

    }
}