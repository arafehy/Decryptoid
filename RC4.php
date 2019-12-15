<?php
    class RC4 {
        function rc4Cipher($key, $string) {
            $s = array();
            for ($i = 0; $i < 256; $i++) {
                $s[$i] = $i;
            }

            $t = array();
            for ($i = 0; $i < 256; $i++) {
                $t[$i] = ord($key[$i % strlen($key)]);
            }

            $j = 0;
            for ($i = 0; $i < 256; $i++) {
                $j = ($j + $s[$i] + $t[$i]) % 256;
                $temp = $s[$i];
                $s[$i] = $s[$j];
                $s[$j] = $temp;
            }

            $i = 0;
            $j = 0;
            $result_string = '';
            for ($y = 0; $y < strlen($string); $y++) {
                $i = ($i + 1) % 256;
                $j = ($j + $s[$i]) % 256;
                $x = $s[$i];
                $s[$i] = $s[$j];
                $s[$j] = $x;
                $result_string .= $string[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
            }
            return $result_string;
        }
    }
?>