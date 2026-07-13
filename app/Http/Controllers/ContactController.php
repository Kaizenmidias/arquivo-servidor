<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    private array $settings;

    public function __construct()
    {
        $this->settings = Setting::query()->pluck('valor', 'chave')->all();
    }

    public function index(): Response
    {
        $page = Page::where('slug', 'contato')->first();
        return Inertia::render('Contact', [
            'page' => $page,
            'settings' => $this->settings,
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
            'recaptcha_token' => ['required', 'string'],
        ]);

        if (!empty($this->settings['recaptcha_site_key']) && !empty($this->settings['recaptcha_secret_key'])) {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $this->settings['recaptcha_secret_key'],
                'response' => $validated['recaptcha_token'],
                'remoteip' => $request->ip(),
            ]);

            if ($response->failed() || !$response->json('success')) {
                throw ValidationException::withMessages([
                    'recaptcha_token' => 'Falha na verificação do reCAPTCHA. Tente novamente.',
                ]);
            }
        }

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
