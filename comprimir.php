<?php
if (!isset($_POST['arquivo'], $_POST['perfil'], $_POST['arquivo_nome'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Parâmetros incompletos.']);
    exit;
}

$dadosBinarios = base64_decode($_POST['arquivo']);
$nomeOriginal = pathinfo($_POST['arquivo_nome'], PATHINFO_FILENAME);
$extensao = pathinfo($_POST['arquivo_nome'], PATHINFO_EXTENSION);
$perfil = $_POST['perfil'];

$caminhoEntrada = 'upload/' . uniqid('original_', true) . '.' . $extensao;
file_put_contents($caminhoEntrada, $dadosBinarios);

$saidaNome = $nomeOriginal . '_comprimido.pdf';
$caminhoSaida = 'comprimidos/' . $saidaNome;

$comando = "\"C:\\Program Files\\gs\\gs10.05.1\\bin\\gswin64c.exe\" -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=$perfil -dNOPAUSE -dQUIET -dBATCH -sOutputFile=\"$caminhoSaida\" \"$caminhoEntrada\"";
exec($comando);

if (!file_exists($caminhoSaida)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao gerar o PDF comprimido.']);
    exit;
}

echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Arquivo comprimido com sucesso.',
    'link' => $caminhoSaida
]);
