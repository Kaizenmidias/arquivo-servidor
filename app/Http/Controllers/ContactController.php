<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

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
            'recaptcha_token' => [$this->captchaEnabled() ? 'required' : 'nullable', 'string'],
        ]);

        $this->validateRecaptcha($request, $validated['recaptcha_token'] ?? null);

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

    private function captchaEnabled(): bool
    {
        return !empty($this->settings['recaptcha_site_key']) && !empty($this->settings['recaptcha_secret_key']);
    }

    private function validateRecaptcha(Request $request, ?string $token): void
    {
        if (!$this->captchaEnabled()) {
            return;
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $this->settings['recaptcha_secret_key'],
            'response' => (string) ($token ?? ''),
            'remoteip' => $request->ip(),
        ]);

        if ($response->failed() || !$response->json('success')) {
            throw ValidationException::withMessages([
                'recaptcha_token' => 'Falha na verificacao do captcha. Tente novamente.',
            ]);
        }
    }
}
