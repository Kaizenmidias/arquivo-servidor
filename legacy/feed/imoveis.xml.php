<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/xml; charset=utf-8');

// Buscar imóveis ativos
$stmt = $pdo->query("
    SELECT 
        i.*, 
        t.nome_tipo,
        t.id_tipo_xml
    FROM imoveis i 
    LEFT JOIN tipos_propriedade t ON i.tipo_propriedade_id = t.id 
    WHERE i.ativo = 1
");
$imoveis = $stmt->fetchAll();

// Gerar XML
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<OpenNavent>
    <DataModificacao><?php echo round(microtime(true) * 1000); ?></DataModificacao>
    <Imoveis>
        <?php foreach ($imoveis as $imovel): ?>
            <Imovel>
                <CodigoAnuncio><?php echo htmlspecialchars($imovel['codigo_anuncio']); ?></CodigoAnuncio>
                <?php if ($imovel['codigo_referencia']): ?>
                    <CodigoReferencia><?php echo htmlspecialchars($imovel['codigo_referencia']); ?></CodigoReferencia>
                <?php endif; ?>
                
                <Titulo><?php echo htmlspecialchars($imovel['titulo']); ?></Titulo>
                <Descricao><?php echo htmlspecialchars($imovel['descricao']); ?></Descricao>
                
                <TipoPropriedade>
                    <?php if ($imovel['id_tipo_xml']): ?>
                        <IdTipo><?php echo $imovel['id_tipo_xml']; ?></IdTipo>
                    <?php endif; ?>
                    <Tipo><?php echo htmlspecialchars($imovel['nome_tipo']); ?></Tipo>
                </TipoPropriedade>
                
                <Precos>
                    <Preco>
                        <Quantidade><?php echo (int)$imovel['valor']; ?></Quantidade>
                        <Moeda>BRL</Moeda>
                        <Operacao><?php echo htmlspecialchars($imovel['operacao']); ?></Operacao>
                    </Preco>
                </Precos>
                
                <Localizacao>
                    <Endereco><?php echo htmlspecialchars($imovel['endereco']); ?></Endereco>
                    <?php if ($imovel['numero']): ?>
                        <Numero><?php echo htmlspecialchars($imovel['numero']); ?></Numero>
                    <?php endif; ?>
                    <Bairro><?php echo htmlspecialchars($imovel['bairro']); ?></Bairro>
                    <Cidade><?php echo htmlspecialchars($imovel['cidade']); ?></Cidade>
                    <Estado><?php echo htmlspecialchars($imovel['estado']); ?></Estado>
                    <?php if ($imovel['cep']): ?>
                        <CEP><?php echo htmlspecialchars($imovel['cep']); ?></CEP>
                    <?php endif; ?>
                    <?php if ($imovel['latitud'] && $imovel['longitud']): ?>
                        <Latitude><?php echo $imovel['latitud']; ?></Latitude>
                        <Longitude><?php echo $imovel['longitud']; ?></Longitude>
                    <?php endif; ?>
                    <?php if ($imovel['id_localidade_xml']): ?>
                        <IdLocalidade><?php echo htmlspecialchars($imovel['id_localidade_xml']); ?></IdLocalidade>
                    <?php endif; ?>
                    <Localidade><?php echo htmlspecialchars($imovel['bairro'] . ', ' . $imovel['cidade'] . ', ' . $imovel['estado'] . ', Brasil'); ?></Localidade>
                </Localizacao>
                
                <Caracteristicas>
                    <?php if ($imovel['quartos']): ?>
                        <Quartos><?php echo $imovel['quartos']; ?></Quartos>
                    <?php endif; ?>
                    <?php if ($imovel['suites']): ?>
                        <Suites><?php echo $imovel['suites']; ?></Suites>
                    <?php endif; ?>
                    <?php if ($imovel['banheiros']): ?>
                        <Banheiros><?php echo $imovel['banheiros']; ?></Banheiros>
                    <?php endif; ?>
                    <?php if ($imovel['garagens']): ?>
                        <Vagas><?php echo $imovel['garagens']; ?></Vagas>
                    <?php endif; ?>
                    <?php if ($imovel['area_util']): ?>
                        <AreaUtil><?php echo $imovel['area_util']; ?></AreaUtil>
                    <?php endif; ?>
                    <?php if ($imovel['area_total']): ?>
                        <AreaTotal><?php echo $imovel['area_total']; ?></AreaTotal>
                    <?php endif; ?>
                </Caracteristicas>
                
                <!-- Imagens (placeholder - implementar quando tiver sistema de upload) -->
                <Imagens>
                    <Imagem>
                        <URL><?php echo BASE_URL; ?>/assets/img/placeholder.jpg</URL>
                        <Principal>1</Principal>
                    </Imagem>
                </Imagens>
                
                <URL><?php echo BASE_URL; ?>/imovel.php?slug=<?php echo $imovel['slug']; ?></URL>
            </Imovel>
        <?php endforeach; ?>
    </Imoveis>
</OpenNavent>
