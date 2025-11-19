<?php
require_once 'utils.php';

if (!isset($_FILES['arquivo'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Nenhum arquivo enviado.']);
    exit;
}

$arquivoTmp = $_FILES['arquivo']['tmp_name'];
$nomeOriginal = pathinfo($_FILES['arquivo']['name'], PATHINFO_FILENAME);
$extensao = pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION);
$nomeFinal = uniqid('pdf_', true) . '.' . $extensao;
$caminhoFinal = 'upload/' . $nomeFinal;

if (!move_uploaded_file($arquivoTmp, $caminhoFinal)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao salvar o arquivo.']);
    exit;
}

// Detecta se é colorido
$ehColorido = isPdfColorido($caminhoFinal);

// Define perfis de compressão para teste
$perfis = $ehColorido ? ['/printer', '/ebook'] : ['/printer', '/ebook', '/screen'];

$tamanhoOriginalMB = filesize($caminhoFinal) / 1048576;
$melhorPerfil = '';
$menorTamanho = $tamanhoOriginalMB;
$tempDir = 'temp/';
if (!is_dir($tempDir)) mkdir($tempDir);

foreach ($perfis as $perfil) {
    $tempSaida = $tempDir . uniqid('teste_', true) . '.pdf';
    $comando = "\"C:\\Program Files\\gs\\gs10.05.1\\bin\\gswin64c.exe\" -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=$perfil -dNOPAUSE -dQUIET -dBATCH -sOutputFile=\"$tempSaida\" \"$caminhoFinal\"";
    exec($comando);

    if (file_exists($tempSaida)) {
        $tamanho = filesize($tempSaida) / 1048576;
        if ($tamanho < $menorTamanho) {
            $menorTamanho = $tamanho;
            $melhorPerfil = $perfil;
        }
        unlink($tempSaida);
    }
}

if (!$melhorPerfil) $melhorPerfil = '/printer';

echo json_encode([
    'sucesso' => true,
    'arquivo' => base64_encode(file_get_contents($caminhoFinal)),
    'arquivo_nome' => $nomeOriginal . '.' . $extensao,
    'tamanho_original' => $tamanhoOriginalMB,
    'tamanho_comprimido' => $menorTamanho,
    'perfil' => $melhorPerfil
]);
