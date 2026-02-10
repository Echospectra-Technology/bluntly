<?php

namespace App\Http\Controllers;

use App\Models\AiPersona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PersonaController extends Controller
{
    /**
     * Display user's persona dashboard
     */
    public function dashboard()
    {
        $personas = Auth::user()->aiPersonas()->with('actions')->get();

        return view('personas.dashboard', compact('personas'));
    }

    /**
     * Show form to create a new persona
     */
    public function create()
    {
        return view('personas.create');
    }

    /**
     * Store a newly created persona
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9_-]+$/',
                'unique:ai_personas,username',
            ],
            'persona' => 'nullable|string|max:2000',
            'system_prompt' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'post_frequency' => 'required|integer|min:1|max:100',
            'reply_probability' => 'required|integer|min:0|max:100',
            'writing_style' => 'nullable|string|max:100',
            'topics_of_interest' => 'nullable|string',
        ], [
            'avatar.image' => 'The avatar must be an image file.',
            'avatar.mimes' => 'The avatar must be a JPEG, PNG, GIF, or WebP image.',
            'avatar.max' => 'The avatar image must not exceed 2MB.',
            'username.unique' => 'This username is already taken. Please choose a different one.',
            'username.regex' => 'Username can only contain letters, numbers, hyphens, and underscores.',
            'username.min' => 'Username must be at least 3 characters.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $avatarUrl = null;

            // Handle avatar upload to S3
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $filename = 'avatars/' . Str::uuid() . '.' . $file->getClientOriginalExtension();
                Storage::disk('s3')->putFileAs('avatars', $file, basename($filename), 'public');
                $avatarUrl = Storage::disk('s3')->url($filename);
            }

            $behaviorRules = [
                'post_frequency' => $request->post_frequency,
                'reply_probability' => $request->reply_probability,
                'writing_style' => $request->writing_style,
                'topics_of_interest' => $request->topics_of_interest ?
                    array_map('trim', explode(',', $request->topics_of_interest)) : [],
            ];

            $persona = AiPersona::create([
                'user_id' => Auth::id(),
                'name' => $request->name,
                'username' => $request->username,
                'persona' => $request->persona,
                'system_prompt' => $request->system_prompt,
                'avatar_url' => $avatarUrl,
                'behavior_rules' => $behaviorRules,
                'is_active' => true,
            ]);

            return redirect()
                ->route('personas.dashboard')
                ->with('success', "AI Persona '{$persona->name}' created successfully!");

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to create persona. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Show form to edit a persona
     */
    public function edit(AiPersona $persona)
    {
        // Ensure user owns this persona
        if ($persona->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return view('personas.edit', compact('persona'));
    }

    /**
     * Update the specified persona
     */
    public function update(Request $request, AiPersona $persona)
    {
        // Ensure user owns this persona
        if ($persona->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9_-]+$/',
                'unique:ai_personas,username,' . $persona->id,
            ],
            'persona' => 'nullable|string|max:2000',
            'system_prompt' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'post_frequency' => 'required|integer|min:1|max:100',
            'reply_probability' => 'required|integer|min:0|max:100',
            'writing_style' => 'nullable|string|max:100',
            'topics_of_interest' => 'nullable|string',
        ], [
            'avatar.image' => 'The avatar must be an image file.',
            'avatar.mimes' => 'The avatar must be a JPEG, PNG, GIF, or WebP image.',
            'avatar.max' => 'The avatar image must not exceed 2MB.',
            'username.unique' => 'This username is already taken. Please choose a different one.',
            'username.regex' => 'Username can only contain letters, numbers, hyphens, and underscores.',
            'username.min' => 'Username must be at least 3 characters.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $avatarUrl = $persona->avatar_url;

            // Handle avatar upload to S3
            if ($request->hasFile('avatar')) {
                // Delete old avatar from S3 if exists
                if ($persona->avatar_url) {
                    $oldPath = parse_url($persona->avatar_url, PHP_URL_PATH);
                    if ($oldPath) {
                        Storage::disk('s3')->delete(ltrim($oldPath, '/'));
                    }
                }

                $file = $request->file('avatar');
                $filename = 'avatars/' . Str::uuid() . '.' . $file->getClientOriginalExtension();
                Storage::disk('s3')->putFileAs('avatars', $file, basename($filename), 'public');
                $avatarUrl = Storage::disk('s3')->url($filename);
            }

            $behaviorRules = [
                'post_frequency' => $request->post_frequency,
                'reply_probability' => $request->reply_probability,
                'writing_style' => $request->writing_style,
                'topics_of_interest' => $request->topics_of_interest ?
                    array_map('trim', explode(',', $request->topics_of_interest)) : [],
            ];

            $persona->update([
                'name' => $request->name,
                'username' => $request->username,
                'persona' => $request->persona,
                'system_prompt' => $request->system_prompt,
                'avatar_url' => $avatarUrl,
                'behavior_rules' => $behaviorRules,
            ]);

            return redirect()
                ->route('personas.dashboard')
                ->with('success', "AI Persona '{$persona->name}' updated successfully!");

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to update persona. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Toggle persona active status
     */
    public function toggleActive(AiPersona $persona)
    {
        // Ensure user owns this persona
        if ($persona->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $persona->update(['is_active' => !$persona->is_active]);

        $status = $persona->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Persona '{$persona->name}' {$status}.");
    }

    /**
     * Delete the specified persona
     */
    public function destroy(AiPersona $persona)
    {
        // Ensure user owns this persona
        if ($persona->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $name = $persona->name;

        // Delete avatar from S3 if exists
        if ($persona->avatar_url) {
            $oldPath = parse_url($persona->avatar_url, PHP_URL_PATH);
            if ($oldPath) {
                Storage::disk('s3')->delete(ltrim($oldPath, '/'));
            }
        }

        $persona->delete();

        return redirect()
            ->route('personas.dashboard')
            ->with('success', "AI Persona '{$name}' deleted successfully.");
    }
}
