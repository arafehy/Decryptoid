<?php
	class DoubleTransposition{
		function splitKey($keyInput) {
			$key = array();
			for ($i=0; $i<strlen($keyInput); $i++) {
				$key[$i] = $keyInput[$i];
			}
			return $key;
		}
		
		function findKeyOrder($key) {
			$order = array();
			for ($i = 0; $i < count($key); $i++) {
				$order[$i] = -1;
			}
			
			$tempArr = $key;
			sort($tempArr);
			
			for ($i=0; $i<sizeof($tempArr); $i++) {
				for ($j=0; $j<sizeof($key); $j++) {
					if (($tempArr[$i] == $key[$j]) && ($order[$j] == -1)) {
						$order[$j] = $i;
						break;
					}
				}
			}
			return $order;
		}
		
		function encrypt($key1, $key2, $text) {
			$ciphertext = $this->columnarEncrypt($key1, $text);
			$ciphertext = $this->columnarEncrypt($key2, $ciphertext);
			return $ciphertext;
		}
		
		function columnarEncrypt($keyInput, $text) {
			$key = $this->splitKey($keyInput);
			$order = $this->findKeyOrder($key);
			$numColumns = count($key);
			$numRows = ceil(strlen($text)/$numColumns);
			
			$ciphertext = array();
			$textLocation = 0;
			for ($row = 0; $row<$numRows; $row++) {
				for ($column = 0; $column < $numColumns; $column++) {
					if ($textLocation<strlen($text)) {
						$ciphertext[$row][$column] = $text[$textLocation];
					}
					else {
						$ciphertext[$row][$column] = '|';
					}
					$textLocation++;
				}
			}
			
			$newCipherText = "";
			for ($i =0; $i<sizeof($order);$i++) {
				$j = 0;
				while ($order[$j] != $i) {
					$j++;
				}
				for ($row = 0; $row<$numRows; $row++) {
					$newCipherText = $newCipherText . $ciphertext[$row][$j];
				}
			}
			$newCipherText = str_replace('|','',$newCipherText);

			return $newCipherText;
		}
		
		function decrypt($key1, $key2, $ciphertext) {
			$text = $this->columnarDecrypt($key1, $ciphertext);
			$text = $this->columnarDecrypt($key2, $text);
			return $text;
		}
		
		function columnarDecrypt($keyInput, $encryptedText) {
			$key = $this->splitKey($keyInput);
			$order = $this->findKeyOrder($key);
			$numColumns = count($key);
			$numRows = ceil(strlen($encryptedText)/$numColumns);
		
			$ciphertext = array();
			for ($i = 0; $i<$numRows; $i++) {
				for($j =0; $j<$numColumns;$j++) {
					$ciphertext[$i][$j] = "-1";
				}
			}
					
			$emptySlots = ($numRows*$numColumns) - strlen($encryptedText);
			for($j=$numColumns-$emptySlots; $j<$numColumns; $j++) {
				$ciphertext[$numRows-1][$j] = '|';
			}
            
			$newCipherText = "";
			$textLocation = 0;

			for ($i =0; $i<sizeof($order);$i++) {
				$j = 0;
				while ($order[$j] != $i) {
					$j++;
				}
				for ($row = 0; $row<$numRows; $row++) {
					if ($ciphertext[$row][$j] == '|') {
						break;
					}
					else if ($textLocation<strlen($encryptedText)) {
						$ciphertext[$row][$j] = $encryptedText[$textLocation];
					}
					else {
						$ciphertext[$row][$j] = ' ';
					}
					$textLocation++;
				}
			}
			
			for ($row = 0; $row<$numRows; $row++) {
				for ($column = 0; $column < $numColumns; $column++) {
					$newCipherText = $newCipherText . $ciphertext[$row][$column];
				}
			}
			$newCipherText = str_replace('|','',$newCipherText);
			return $newCipherText;
		}
	}
?>