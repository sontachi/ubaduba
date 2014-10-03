<?php

function uploadArquivo($arquivo, $pasta, $largura, $altura) {
    if(!empty($arquivo["name"])) { //Se existe o arquivo
        $tamanhos = getimagesize($arquivo["tmp_name"]);
        
        if($tamanhos[0] > $largura || $tamanhos[1] > $altura)
            return -1;
        
        $dir="../".$pasta."/";
        if(!file_exists($dir))
            mkdir($dir, 0777, true);
        
        preg_match("/\.(gif|bmp|png|jpg|jpeg|pdf|doc|docx|odt|odp|rtf|ppt|pptx|pps|ppsx){1}$/i", $arquivo["name"], $ext);
        $imagem_nome = md5(uniqid(time())) . "." . $ext[1];
        
        $url = $dir . $imagem_nome;
        $return = $pasta."/".$imagem_nome;
        
        move_uploaded_file($arquivo["tmp_name"], $url);
        return $return;
    }
}


function removeArquivo($arquivo) {
    unlink("../".$arquivo);
}

?>