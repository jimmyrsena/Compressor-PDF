<?php
function isPdfColorido($arquivo) {
    $saida = [];
    $comando = "pdfimages -list \"$arquivo\"";
    exec($comando, $saida);

    foreach ($saida as $linha) {
        if (preg_match('/(rgb|icc|rgba)/i', $linha)) {
            return true;
        }
    }
    return false;
}
