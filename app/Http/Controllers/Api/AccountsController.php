<?php

namespace App\Http\Controllers\Api;

use App\Models\Account;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AccountsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Account::latest()->with('accountable')->paginate(25);
    }

    /**
     * @param   int $id
     * @return  JsonResponse
     */
    public function show($id)
    {
        return Account::with('accountable')->findOrFail($id);
    }

    /**
     * @param   void
     * @return  JsonResponse
     */
    public function types()
    {
        return new JsonResponse(Account::$accounts_type);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'type' => 'required|in:'.implode(',', array_keys(Account::$accounts_type)),
            'site_id' => 'required|exists:sites,id',
            'credential_login' => 'required',
            'credential_password' => 'required'
        ]);

        $site = Site::findOrFail($request->get('site_id'));

        $account = new Account();
        $account->forceFill([
            'type' => $request->get('type'),
            'credential_login' => $request->get('credential_login'),
            'credential_password' => $request->get('credential_password')
        ]);

        $site->accounts()->save($account);

        return response('ok', 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $account = Account::findOrFail($id);

        $this->validate($request, [
            'site_id' => 'required|exists:sites,id',
            'type' => 'required|in:'.implode(',', array_keys(Account::$accounts_type)),
            'credential_login' => 'required',
            'credential_password' => 'required'
        ]);

        $account->forceFill([
            'type' => $request->get('type')
        ]);

        if ($request->get('credential_login') != $account->credential_login)
        {
            $account->credential_login = $request->get('credential_login');
        }

        if ($request->get('credential_password') != $account->credential_password)
        {
            $account->credential_password = $request->get('credential_password');
        }

        if ($request->get('site_id') != $account->accountable->id)
        {
            $id = $request->get('site_id');
            $site = Site::findOrFail($id);
            $site->accounts()->save($account);
        }

        $account->save();

        return response('ok', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $account = Account::findOrFail($id);

        $account->delete();

        return response('ok', 200);
    }

    /**
     * @param   int $id
     * @return  JsonResponse
     */
    public function history($id)
    {
        $account = Account::with(['revisionHistory', 'accountable'])->findOrFail($id);
        $history = [];

        if (!$account->revisionHistory->isEmpty())
        {
            $history = $account->revisionHistory->groupBy(function ($item, $key)
            {
                return substr($item->created_at, 0, 10);
            })->map(function ($item, $key)
            {
                $item->transform(function($item) {
                    switch ($item->key)
                    {
                        case 'credential_login':
                        case 'credential_password':
                            $item->old_value = decrypt($item->old_value);
                            $item->new_value = decrypt($item->new_value);
                            break;

                        default:
                            break;
                    }

                    return $item;
                });

                return collect(['date'    => $key,
                                'entries' => $item
                ]);
            })->values();
        }

        return new JsonResponse([
            'account'    => $account,
            'history' => $history
        ]);
    }
}
