<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AnonymousAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnonymousAuthController extends Controller
{
    private AnonymousAccountService $accountService;

    public function __construct(AnonymousAccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function showSignupForm()
    {
        if ($this->accountService->isLoggedIn()) {
            return redirect()->route('feed');
        }

        $suggestedUsername = $this->accountService->generateUsername();
        
        return view('auth.anonymous-signup', compact('suggestedUsername'));
    }

    public function showLoginForm()
    {
        if ($this->accountService->isLoggedIn()) {
            return redirect()->route('feed');
        }

        return view('auth.anonymous-login');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:20',
                'regex:/^[a-zA-Z0-9_-]+$/',
                'unique:anonymous_users,username'
            ],
            'password' => 'required|string|min:6|confirmed',
            'email' => 'nullable|email|unique:anonymous_users,email',
        ], [
            'username.regex' => 'Username can only contain letters, numbers, hyphens and underscores.',
            'username.unique' => 'This username is already taken.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        try {
            $user = $this->accountService->register(
                $request->username,
                $request->password,
                $request->email
            );

            // Auto-login after registration
            $this->accountService->login($request->username, $request->password);

            return redirect()
                ->route('stories')
                ->with('success', "Welcome @{$user->username}! Your anonymous account has been created. All your previous activity has been preserved.");
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to create account. Please try again.'])
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = $this->accountService->login($request->username, $request->password);

        if ($user) {
            return redirect()
                ->intended(route('feed'))
                ->with('success', "Welcome back @{$user->username}!");
        }

        return back()
            ->withErrors(['error' => 'Invalid username or password.'])
            ->withInput($request->only('username'));
    }

    public function logout()
    {
        $this->accountService->logout();
        
        return redirect()
            ->route('stories')
            ->with('success', 'You have been logged out.');
    }

    public function checkUsername(Request $request)
    {
        $username = $request->input('username');
        
        if (!$username) {
            return response()->json(['available' => false, 'message' => 'Username is required']);
        }

        if (strlen($username) < 3) {
            return response()->json(['available' => false, 'message' => 'Username must be at least 3 characters']);
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            return response()->json(['available' => false, 'message' => 'Username can only contain letters, numbers, hyphens and underscores']);
        }

        $available = $this->accountService->isUsernameAvailable($username);
        
        return response()->json([
            'available' => $available,
            'message' => $available ? 'Username is available!' : 'Username is already taken'
        ]);
    }

    public function generateUsername()
    {
        return response()->json([
            'username' => $this->accountService->generateUsername()
        ]);
    }
}
