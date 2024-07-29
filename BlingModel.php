<?php


class BlingModel {


    #pega refresh token
    public static function getRefreshToken() {

        $sql = Mysql::conectar()->prepare("SELECT `refresh_token` FROM `Bling_Tokens`");

        $sql->execute();

        $refresh_token = $sql->fetch();

        return $refresh_token['refresh_token'];
    }


    #update no acess e refresh tokens
    public static function update($access_code, $refresh_code) {

        $sql = Mysql::conectar()->prepare("UPDATE `Bling_Tokens` SET `access_token` = ?, `refresh_token` = ?");
        
        if($sql->execute(array($access_code, $refresh_code))){
            return true;
        } else {
            return false;
        };
        
    }
}


?>