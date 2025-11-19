<?php
// index.php - Interface principal do sistema de compressão avançada de PDFs
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Compressor de PDF Avançado</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>📄 Compressor de PDF Avançado</h1>

    <form id="formulario" enctype="multipart/form-data">
        <label for="arquivos">Selecione um ou mais PDFs:</label>
        <input type="file" name="arquivos[]" id="arquivos" multiple accept="application/pdf" required>

        <label for="perfil">Nível de Compressão:</label>
        <select id="perfil" name="perfil">
            <option value="auto">Automático (Recomendado)</option>
            <option value="/printer">Alta Qualidade</option>
            <option value="/ebook">Equilíbrio</option>
            <option value="/screen">Máxima Compressão</option>
        </select>

        <button type="submit">🔽 Analisar e Comprimir</button>
    </form>

    <div id="resultado"></div>
    <div id="status"></div>

    <script>
    document.getElementById('formulario').addEventListener('submit', async function(e) {
        e.preventDefault();

        const arquivos = document.getElementById('arquivos').files;
        const perfilSelecionado = document.getElementById('perfil').value;
        const resultado = document.getElementById('resultado');
        const status = document.getElementById('status');

        resultado.innerHTML = '';
        status.innerHTML = '';

        if (arquivos.length === 0) {
            status.innerHTML = '⚠️ Nenhum arquivo selecionado.';
            return;
        }

        for (const arquivo of arquivos) {
            const formData = new FormData();
            formData.append('arquivo', arquivo);

            status.innerHTML = `📊 Analisando "${arquivo.name}"...`;

            const analise = await fetch('processar.php', {
                method: 'POST',
                body: formData
            }).then(r => r.json());

            if (!analise.sucesso) {
                status.innerHTML = `❌ Falha ao analisar "${arquivo.name}": ${analise.mensagem}`;
                continue;
            }

            status.innerHTML = `🛠️ Comprimindo "${arquivo.name}" com perfil ${analise.perfil}...`;

            const formDataComp = new FormData();
            formDataComp.append('arquivo', analise.arquivo);
            formDataComp.append('perfil', perfilSelecionado === 'auto' ? analise.perfil : perfilSelecionado);
            formDataComp.append('arquivo_nome', analise.arquivo_nome);

            const compressao = await fetch('comprimir.php', {
                method: 'POST',
                body: formDataComp
            }).then(r => r.json());

            if (!compressao.sucesso) {
                status.innerHTML = `❌ Erro ao comprimir "${arquivo.name}": ${compressao.mensagem}`;
                continue;
            }

            const tamanhoOriginal = analise.tamanho_original.toFixed(2);
            const tamanhoFinal = analise.tamanho_comprimido.toFixed(2);

            const div = document.createElement('div');
            div.innerHTML = `
                ✅ <strong>${arquivo.name}</strong><br>
                Tamanho original: ${tamanhoOriginal} MB<br>
                Tamanho comprimido: ${tamanhoFinal} MB<br>
                Perfil usado: ${perfilSelecionado === 'auto' ? analise.perfil : perfilSelecionado}<br>
                <a href="${compressao.link}" target="_blank">📥 Baixar PDF Comprimido</a>
                <hr>
            `;
            resultado.appendChild(div);

            status.innerHTML = `✔️ Concluído: "${arquivo.name}".`;
        }

        status.innerHTML += '<br>Todos os arquivos foram processados.';
    });
    </script>
</body>
</html>
