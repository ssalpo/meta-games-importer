<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function index(): View
    {
        $accounts = Account::query()
            ->latest()
            ->paginate(10);

        return view('accounts.index', [
            'accounts' => $accounts,
        ]);
    }

    public function create(): View
    {
        return view('accounts.create', [
            'account' => new Account,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Account::create($this->validatedData($request));

        return redirect()
            ->route('accounts.index')
            ->with('status', 'Account created.');
    }

    public function show(Account $account): View
    {
        return view('accounts.show', [
            'account' => $account,
        ]);
    }

    public function edit(Account $account): View
    {
        return view('accounts.edit', [
            'account' => $account,
        ]);
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        $data = $this->validatedData($request, $account);

        if (blank($data['access_token'] ?? null)) {
            unset($data['access_token']);
        }

        $account->update($data);

        return redirect()
            ->route('accounts.index')
            ->with('status', 'Account updated.');
    }

    public function destroy(Account $account): RedirectResponse
    {
        $account->delete();

        return redirect()
            ->route('accounts.index')
            ->with('status', 'Account deleted.');
    }

    private function validatedData(Request $request, ?Account $account = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('accounts', 'name')->ignore($account),
            ],
            'access_token' => [
                $account ? 'nullable' : 'required',
                'string',
                'max:10000',
            ],
        ]);
    }
}
