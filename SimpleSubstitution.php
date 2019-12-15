<?php
    class SimpleSubstitution {
        static $pair = array("a"=>"c", "b"=>"h", "c"=>"q", "d"=>"g", "e"=>"u","f"=>"r",
                            "g"=>"m", "h"=>"a","i"=>"l", "j"=>"y", "k"=>"e", "l"=>"n", "m"=>"p",
                            "n"=>"v", "o"=>"d", "p"=>"z", "q"=>"x", "r"=>"k", "s"=>"i", "t"=>"o",
                            "u"=>"f", "v"=>"s", "w"=>"t", "x"=>"j", "y"=>"w", "z"=>"b"," "=> " ");
                            
        public function Encrypt($s, $pair){
            $encrypt = strtolower($s);
            $encryptValue = " ";

            for($i=0; $i<strlen($encrypt); $i++){

                $temp = $encrypt[$i];
                $corresponding = $pair[$temp];

                $encryptValue = $corresponding . $encryptValue;
            }
            return $encryptValue;
        }

        public function Decrypt($s, $pair){

            $decrypt = strtolower($s);
            $decryptValue = " ";

            for($i=0; $i<strlen($decrypt); $i++){
                $temp = $decrypt[$i];

                for($j=0; $j<sizeof(array_keys($pair,$temp)); $j++){
                    $decryptValue = array_keys($pair,$temp)[$j] . $decryptValue;
                }
            }
            return $decryptValue;
        }
    }
?>