<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function about(): Response
    {
        $page = Page::where('slug', 'quem-somos')->first() ?? Page::where('slug', 'sobre')->first();
        return Inertia::render('About', ['page' => $page]);
    }

    public function offMarket(): Response
    {
        return Inertia::render('OffMarket');
    }

    public function exclusiveManagement(): Response
    {
        return Inertia::render('ExclusiveManagement');
    }

    public function evaluate(): Response
    {
        return Inertia::render('Evaluate');
    }

    public function partnerAgent(): Response
    {
        return Inertia::render('PartnerAgent');
    }

    public function sendEvaluate(Request $request)
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'telefone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'max:255'],
            'tipo_imovel' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'valor_estimado' => ['nullable', 'string', 'max:255'],
            'mensagem' => ['nullable', 'string'],
        ]);

        $parts = [];
        if (!empty($validated['tipo_imovel'])) $parts[] = 'Tipo: ' . $validated['tipo_imovel'];
        if (!empty($validated['cidade'])) $parts[] = 'Cidade: ' . $validated['cidade'];
        if (!empty($validated['bairro'])) $parts[] = 'Bairro: ' . $validated['bairro'];
        if (!empty($validated['valor_estimado'])) $parts[] = 'Valor estimado: ' . $validated['valor_estimado'];
        if (!empty($validated['mensagem'])) $parts[] = 'Mensagem: ' . $validated['mensagem'];
        $mensagem = count($parts) ? implode("\n", $parts) : null;

        Lead::create([
            'nome' => $validated['nome'],
            'telefone' => $validated['telefone'],
            'email' => $validated['email'] ?? '',
            'mensagem' => $mensagem,
            'origem' => 'Site - Avalie seu Imóvel',
            'categoria' => 'leads',
            'status' => 'Novo Lead',
        ]);

        return Redirect::back();
    }

    public function sendPartnerAgent(Request $request)
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'telefone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'max:255'],
            'creci' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'mensagem' => ['nullable', 'string'],
        ]);

        $parts = [];
        if (!empty($validated['creci'])) $parts[] = 'CRECI: ' . $validated['creci'];
        if (!empty($validated['cidade'])) $parts[] = 'Cidade: ' . $validated['cidade'];
        if (!empty($validated['mensagem'])) $parts[] = 'Mensagem: ' . $validated['mensagem'];
        $mensagem = count($parts) ? implode("\n", $parts) : null;

        Lead::create([
            'nome' => $validated['nome'],
            'telefone' => $validated['telefone'],
            'email' => $validated['email'] ?? '',
            'mensagem' => $mensagem,
            'origem' => 'Site - Corretor Parceiro',
            'categoria' => 'leads',
            'status' => 'Novo Lead',
        ]);

        return Redirect::back();
    }

    public function sendOffMarket(Request $request)
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'telefone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'max:255'],
            'mensagem' => ['nullable', 'string'],
        ]);

        Lead::create([
            'nome' => $validated['nome'],
            'telefone' => $validated['telefone'],
            'email' => $validated['email'] ?? '',
            'mensagem' => $validated['mensagem'] ?? null,
            'origem' => 'Site - Off Market',
            'categoria' => 'leads',
            'status' => 'Novo Lead',
        ]);

        return Redirect::back();
    }

    public function show(Page $page): Response
    {
        return Inertia::render('PageShow', ['page' => $page]);
    }
}
