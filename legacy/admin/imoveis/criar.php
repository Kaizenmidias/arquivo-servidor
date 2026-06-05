<?php
require_once __DIR__ . '/../../includes/admin-header.php';

$tipos = $pdo->query("SELECT * FROM tipos_propriedade ORDER BY nome_tipo")->fetchAll();
$caracteristicas = $pdo->query("SELECT * FROM imoveis_caracteristicas ORDER BY nome")->fetchAll();

$erros = [];
$dados = $_POST ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar inputs
    $dados = array_map('sanitize', $_POST);
    $dados['descricao'] = $_POST['descricao'] ?? ''; // Permitir HTML na descrição
    
    // Validação básica
    if (empty($dados['titulo'])) $erros[] = 'Título é obrigatório';
    if (empty($dados['descricao'])) $erros[] = 'Descrição é obrigatória';
    if (empty($dados['tipo_propriedade_id'])) $erros[] = 'Tipo de propriedade é obrigatório';
    if (empty($dados['operacao'])) $erros[] = 'Operação é obrigatória';
    if (empty($dados['valor'])) $erros[] = 'Valor é obrigatório';
    if (empty($dados['endereco'])) $erros[] = 'Endereço é obrigatório';
    if (empty($dados['bairro'])) $erros[] = 'Bairro é obrigatório';
    if (empty($dados['cidade'])) $erros[] = 'Cidade é obrigatória';
    if (empty($dados['estado'])) $erros[] = 'Estado é obrigatório';

    if (empty($erros)) {
        // Gerar código único e slug
        $codigo_anuncio = gerar_codigo_anuncio();
        $slug = slugify($dados['titulo'] . ' ' . $dados['bairro'] . ' ' . $dados['cidade']);
        
        // Garantir slug único
        $contador = 1;
        $slug_original = $slug;
        while ($pdo->query("SELECT id FROM imoveis WHERE slug = '$slug'")->fetchColumn()) {
            $slug = $slug_original . '-' . $contador++;
        }

        // Inserir imóvel
        $stmt = $pdo->prepare("
            INSERT INTO imoveis (
                codigo_anuncio, codigo_referencia, titulo, slug, descricao, tipo_propriedade_id,
                operacao, valor, moeda, endereco, numero, complemento, bairro, cidade, estado,
                cep, id_localidade_xml, localidade_xml, latitud, longitud, area_util, area_total,
                quartos, suites, banheiros, garagens, condominio, iptu, destaque, ativo, data_modificacao_xml
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $codigo_anuncio,
            $dados['codigo_referencia'] ?? null,
            $dados['titulo'],
            $slug,
            $dados['descricao'],
            $dados['tipo_propriedade_id'],
            $dados['operacao'],
            str_replace(['.', ','], ['', '.'], $dados['valor']),
            'BRL',
            $dados['endereco'],
            $dados['numero'] ?? null,
            $dados['complemento'] ?? null,
            $dados['bairro'],
            $dados['cidade'],
            $dados['estado'],
            $dados['cep'] ?? null,
            $dados['id_localidade_xml'] ?? null,
            $dados['localidade_xml'] ?? null,
            !empty($dados['latitud']) ? (float)$dados['latitud'] : null,
            !empty($dados['longitud']) ? (float)$dados['longitud'] : null,
            !empty($dados['area_util']) ? (float)$dados['area_util'] : null,
            !empty($dados['area_total']) ? (float)$dados['area_total'] : null,
            !empty($dados['quartos']) ? (int)$dados['quartos'] : null,
            !empty($dados['suites']) ? (int)$dados['suites'] : null,
            !empty($dados['banheiros']) ? (int)$dados['banheiros'] : null,
            !empty($dados['garagens']) ? (int)$dados['garagens'] : null,
            !empty($dados['condominio']) ? str_replace(['.', ','], ['', '.'], $dados['condominio']) : null,
            !empty($dados['iptu']) ? str_replace(['.', ','], ['', '.'], $dados['iptu']) : null,
            isset($dados['destaque']) ? 1 : 0,
            isset($dados['ativo']) ? 1 : 0,
            round(microtime(true) * 1000) // Timestamp em ms para XML
        ]);

        $imovel_id = $pdo->lastInsertId();

        // Inserir características selecionadas
        if (!empty($dados['caracteristicas'])) {
            foreach ($dados['caracteristicas'] as $caracteristica_id) {
                $pdo->prepare("INSERT INTO imovel_caracteristica (imovel_id, caracteristica_id) VALUES (?, ?)")
                    ->execute([$imovel_id, $caracteristica_id]);
            }
        }

        $_SESSION['sucesso'] = 'Imóvel cadastrado com sucesso!';
        redirect(BASE_URL . '/admin/imoveis/');
    }
}
?>

<div class="max-w-5xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">➕ Novo Imóvel</h1>
        <a href="<?php echo BASE_URL; ?>/admin/imoveis/" class="text-gray-600 hover:text-gray-800">← Voltar</a>
    </div>

    <?php if ($erros): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                <?php foreach ($erros as $erro): ?>
                    <li><?php echo $erro; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="bg-white rounded-xl shadow-md p-8 space-y-8">
        <!-- Dados Básicos -->
        <div>
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">📋 Dados Básicos</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Título *</label>
                    <input type="text" name="titulo" value="<?php echo $dados['titulo'] ?? ''; ?>" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Código Anúncio (Gerado automaticamente)</label>
                    <input type="text" disabled class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Código Referência (opcional)</label>
                    <input type="text" name="codigo_referencia" value="<?php echo $dados['codigo_referencia'] ?? ''; ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Propriedade *</label>
                    <select name="tipo_propriedade_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione...</option>
                        <?php foreach ($tipos as $tipo): ?>
                            <option value="<?php echo $tipo['id']; ?>" <?php echo (isset($dados['tipo_propriedade_id']) && $dados['tipo_propriedade_id'] == $tipo['id']) ? 'selected' : ''; ?>>
                                <?php echo $tipo['nome_tipo']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Operação *</label>
                    <select name="operacao" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione...</option>
                        <option value="Venda" <?php echo (isset($dados['operacao']) && $dados['operacao'] === 'Venda') ? 'selected' : ''; ?>>Venda</option>
                        <option value="Aluguel" <?php echo (isset($dados['operacao']) && $dados['operacao'] === 'Aluguel') ? 'selected' : ''; ?>>Aluguel</option>
                        <option value="Temporada" <?php echo (isset($dados['operacao']) && $dados['operacao'] === 'Temporada') ? 'selected' : ''; ?>>Temporada</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Valor *</label>
                    <input type="text" name="valor" value="<?php echo $dados['valor'] ?? ''; ?>" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        placeholder="Ex: 500000">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descrição *</label>
                    <textarea name="descricao" rows="6" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?php echo $dados['descricao'] ?? ''; ?></textarea>
                </div>
            </div>
        </div>

        <!-- Localização -->
        <div>
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">📍 Localização</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Endereço *</label>
                    <input type="text" name="endereco" value="<?php echo $dados['endereco'] ?? ''; ?>" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Número</label>
                    <input type="text" name="numero" value="<?php echo $dados['numero'] ?? ''; ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Complemento</label>
                    <input type="text" name="complemento" value="<?php echo $dados['complemento'] ?? ''; ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bairro *</label>
                    <input type="text" name="bairro" value="<?php echo $dados['bairro'] ?? ''; ?>" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cidade *</label>
                    <input type="text" name="cidade" value="<?php echo $dados['cidade'] ?? ''; ?>" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Estado *</label>
                    <select name="estado" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione...</option>
                        <?php
                        $estados = ['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO'];
                        foreach ($estados as $uf):
                        ?>
                            <option value="<?php echo $uf; ?>" <?php echo (isset($dados['estado']) && $dados['estado'] === $uf) ? 'selected' : ''; ?>>
                                <?php echo $uf; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">CEP</label>
                    <input type="text" name="cep" value="<?php echo $dados['cep'] ?? ''; ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Latitude</label>
                    <input type="text" name="latitud" value="<?php echo $dados['latitud'] ?? ''; ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Longitude</label>
                    <input type="text" name="longitud" value="<?php echo $dados['longitud'] ?? ''; ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Características -->
        <div>
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">🏗️ Características</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Área Útil (m²)</label>
                    <input type="number" name="area_util" value="<?php echo $dados['area_util'] ?? ''; ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Área Total (m²)</label>
                    <input type="number" name="area_total" value="<?php echo $dados['area_total'] ?? ''; ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quartos</label>
                    <input type="number" name="quartos" value="<?php echo $dados['quartos'] ?? ''; ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Suítes</label>
                    <input type="number" name="suites" value="<?php echo $dados['suites'] ?? ''; ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Banheiros</label>
                    <input type="number" name="banheiros" value="<?php echo $dados['banheiros'] ?? ''; ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Garagens</label>
                    <input type="number" name="garagens" value="<?php echo $dados['garagens'] ?? ''; ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Condomínio</label>
                    <input type="text" name="condominio" value="<?php echo $dados['condominio'] ?? ''; ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">IPTU</label>
                    <input type="text" name="iptu" value="<?php echo $dados['iptu'] ?? ''; ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Comodidades</label>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                    <?php foreach ($caracteristicas as $c): ?>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="caracteristicas[]" value="<?php echo $c['id']; ?>"
                                <?php echo (isset($dados['caracteristicas']) && in_array($c['id'], $dados['caracteristicas'])) ? 'checked' : ''; ?>
                                class="w-4 h-4 text-blue-600">
                            <span class="text-gray-700"><?php echo $c['icone']; ?> <?php echo $c['nome']; ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Opções -->
        <div>
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">⚙️ Opções</h2>
            <div class="flex items-center space-x-8">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="destaque" <?php echo isset($dados['destaque']) ? 'checked' : ''; ?>
                        class="w-5 h-5 text-yellow-600">
                    <span class="text-gray-700 font-medium">⭐ Destaque</span>
                </label>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="ativo" <?php echo !isset($dados['ativo']) || $dados['ativo'] ? 'checked' : ''; ?>
                        class="w-5 h-5 text-green-600">
                    <span class="text-gray-700 font-medium">✅ Ativo</span>
                </label>
            </div>
        </div>

        <!-- Botões -->
        <div class="flex justify-end space-x-4 pt-4 border-t">
            <a href="<?php echo BASE_URL; ?>/admin/imoveis/"
                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancelar
            </a>
            <button type="submit"
                class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition">
                Salvar Imóvel
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>
