<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(): Response
    {
        $page = Page::where('slug', 'contato')->first();
        $settings = Setting::query()->pluck('valor', 'chave');

        return Inertia::render('Contact', [
            'page' => $page,
            'settings' => $settings,
        ]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
            'nome' => ['required', 'string', 'max:255'],
            'telefone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'max:255'],
            'mensagem' => ['nullable', 'string'],
            'origem' => ['nullable', 'string', 'max:255'],
        ]);

        Lead::create([
            'property_id' => $validated['property_id'] ?? null,
            'nome' => $validated['nome'],
            'telefone' => $validated['telefone'],
            'email' => $validated['email'] ?? '',
            'mensagem' => $validated['mensagem'] ?? null,
            'origem' => $validated['origem'] ?? 'Site - Contato',
            'categoria' => 'leads',
            'status' => 'Novo Lead',
        ]);

        return Redirect::back();
    }
}
