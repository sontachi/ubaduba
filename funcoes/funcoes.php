<?php

function uploadArquivo($arquivo, $pasta) {

    if(!empty($arquivo["name"])) { //Se existe o arquivo
        $tamanhos = getimagesize($arquivo["tmp_name"]);
        
        $dir= $pasta."/";
        
        if(!file_exists($dir))
            mkdir($dir, 0777, true);
        
        preg_match("/\.(gif|bmp|png|jpg|jpeg|pdf|doc|docx|odt|odp|rtf|ppt|pptx|pps|ppsx|zip|rar){1}$/i", $arquivo["name"], $ext);
        $arquivo_nome = md5(uniqid(time())) . "." . $ext[1];
        
        $url = $dir . $arquivo_nome;
        $return = $pasta."/".$arquivo_nome;
        
        move_uploaded_file($arquivo["tmp_name"], $url);
        return $return;
    }
}


function removeArquivo($arquivo) {
    unlink("../".$arquivo);
}

?>
