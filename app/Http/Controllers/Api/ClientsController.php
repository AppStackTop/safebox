<?php

namespace App\Http\Controllers\Api;

use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ClientsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Client::latest()->get();
    }

    /**
     * @return  JsonResponse
     */
    public function lists()
    {
        $clients = Client::orderBy('name', 'asc')->get();

        if (!$clients->isEmpty())
        {
            return $clients->pluck('name', 'id')->toArray();
        }

        return [];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name'  => 'required|max:255',
            'email' => 'required|email',
            'phone' => 'required|max:12',
        ]);

        $client = new Client();
        $client->name = $request->get('name');
        $client->email = $request->get('email');
        $client->phone = $request->get('phone');
        $client->save();

        return response('ok', 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name'  => 'required|max:255',
            'email' => 'required|email',
            'phone' => 'required|max:12',
        ]);

        $client = Client::findOrFail($id);
        $client->name = $request->get('name');
        $client->email = $request->get('email');
        $client->phone = $request->get('phone');
        $client->save();

        return response('ok', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();
        return response('ok', 200);
    }
}
