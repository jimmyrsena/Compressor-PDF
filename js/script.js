const form = document.getElementById('formulario');
const resultadoDiv = document.getElementById('resultado');

form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const arquivos = form.arquivos.files;
    const perfil = form.perfil.value;

    if (arquivos.length === 0) {
        alert('Selecione ao menos um arquivo PDF.');
        return;
    }

    resultadoDiv.innerHTML = '';
    
    for (let i = 0; i < arquivos.length; i++) {
        const arquivo = arquivos[i];
        resultadoDiv.innerHTML += `<p>Processando: <strong>${arquivo.name}</strong>...</p>`;
        await processarArquivo(arquivo, perfil);
    }
});

async function processarArquivo(arquivo, perfilSelecionado) {
    // FormData para enviar arquivo e perfil
    const formData = new FormData();
    formData.append('arquivo', arquivo);
    formData.append('perfil', perfilSelecionado);

    try {
        // Passo 1: Analisar arquivo (processar.php)
        const respAnalise = await fetch('processar.php', {
            method: 'POST',
            body: formData
        });
        const dadosAnalise = await respAnalise.json();

        if (!dadosAnalise.sucesso) {
            resultadoDiv.innerHTML += `<p style="color:red;">Erro na análise do arquivo ${arquivo.name}: ${dadosAnalise.mensagem}</p>`;
            return;
        }

        // Se perfil = auto, usar o perfil sugerido pelo backend
        let perfilParaComprimir = perfilSelecionado === 'auto' ? dadosAnalise.perfil : perfilSelecionado;

        // Passo 2: Compactar (comprimir.php)
        // Para compactar, precisamos enviar o arquivo base64 e o nome original para manter
        const formDataComprimir = new FormData();
        formDataComprimir.append('arquivo', dadosAnalise.arquivo);
        formDataComprimir.append('perfil', perfilParaComprimir);
        formDataComprimir.append('arquivo_nome', dadosAnalise.arquivo_nome);

        const respCompactar = await fetch('comprimir.php', {
            method: 'POST',
            body: formDataComprimir
        });

        const dadosCompactar = await respCompactar.json();

        if (dadosCompactar.sucesso) {
            resultadoDiv.innerHTML += `<p style="color:green;">Arquivo <strong>${arquivo.name}</strong> comprimido com sucesso! ` +
                `<a href="${dadosCompactar.link}" download>Baixar</a></p>`;
        } else {
            resultadoDiv.innerHTML += `<p style="color:red;">Erro ao comprimir ${arquivo.name}: ${dadosCompactar.mensagem}</p>`;
            if (dadosCompactar.erro) {
                resultadoDiv.innerHTML += `<pre>${dadosCompactar.erro}</pre>`;
            }
        }

    } catch (error) {
        resultadoDiv.innerHTML += `<p style="color:red;">Erro inesperado: ${error.message}</p>`;
    }
}
