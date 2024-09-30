<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enkripsi dan Dekripsi</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        input, select { margin-bottom: 10px; }
    </style>
</head>
<body>
    <h1>Enkripsi dan Dekripsi</h1>
    <form method="post">
        <label for="plaintext">Plaintext:</label><br>
        <input type="text" id="plaintext" name="plaintext"><br>

        <label for="key">Kunci:</label><br>
        <input type="text" id="key" name="key"><br>

        <label for="cipher">Tipe Cipher:</label><br>
        <select id="cipher" name="cipher">
            <option value="vigenere">Vigenere Cipher</option>
            <option value="auto_key_vigenere">Auto-Key Vigenere Cipher</option>
            <option value="playfair">Playfair Cipher</option>
            <option value="hill">Hill Cipher</option>
            <option value="super_encryption">Super Enkripsi (Vigenere + Transposisi Kolom)</option>
        </select><br>

        <label for="action">Tindakan:</label><br>
        <select id="action" name="action">
            <option value="encrypt">Enkripsi</option>
            <option value="decrypt">Dekripsi</option>
        </select><br>

        <button type="submit">Proses</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $plaintext = $_POST['plaintext'];
        $key = $_POST['key'];
        $cipher = $_POST['cipher'];
        $action = $_POST['action'];

        // Fungsi Enkripsi dan Dekripsi Vigenere Cipher
        function vigenere_cipher($text, $key, $encrypt = true) {
            $result = "";
            $key = strtoupper($key);
            $keyLen = strlen($key);

            for ($i = 0, $j = 0; $i < strlen($text); $i++) {
                $char = strtoupper($text[$i]);

                if (ctype_alpha($char)) {
                    $shift = ord($key[$j % $keyLen]) - ord('A');
                    $shift = $encrypt ? $shift : -$shift;

                    $newChar = chr(((ord($char) - ord('A') + $shift + 26) % 26) + ord('A'));
                    $result .= $newChar;
                    $j++;
                } else {
                    $result .= $char;
                }
            }

            return $result;
        }

        // Fungsi untuk Auto-Key Vigenere Cipher
        function auto_key_vigenere_cipher($text, $key, $encrypt = true) {
            $result = "";
            $text = strtoupper($text);
            $key = strtoupper($key);

            if ($encrypt) {
                $key .= $text;
            }

            return vigenere_cipher($text, $key, $encrypt);
        }

        // Fungsi untuk Playfair Cipher
        function playfair_cipher($text, $key, $encrypt = true) {
            // Implementasi Playfair Cipher
            // Membuat matriks kunci 5x5
            $text = strtoupper($text);
            $key = strtoupper($key);

            $key = str_replace('J', 'I', $key);
            $text = str_replace('J', 'I', $text);

            $matrix = array();
            $key_string = '';

            // Menghilangkan huruf duplikat dalam kunci
            for ($i = 0; $i < strlen($key); $i++) {
                if (strpos($key_string, $key[$i]) === false && ctype_alpha($key[$i])) {
                    $key_string .= $key[$i];
                }
            }

            // Menambahkan huruf alfabet yang belum ada ke dalam kunci
            for ($i = ord('A'); $i <= ord('Z'); $i++) {
                $char = chr($i);
                if ($char == 'J') continue; // Menggabungkan I dan J
                if (strpos($key_string, $char) === false) {
                    $key_string .= $char;
                }
            }

            // Membuat matriks 5x5
            for ($i = 0; $i < 25; $i++) {
                $matrix[floor($i / 5)][($i % 5)] = $key_string[$i];
            }

            // Persiapan teks
            $prepared_text = '';
            for ($i = 0; $i < strlen($text); $i++) {
                if (ctype_alpha($text[$i])) {
                    $prepared_text .= $text[$i];
                }
            }

            // Memisahkan teks menjadi pasangan
            $digraphs = array();
            for ($i = 0; $i < strlen($prepared_text); $i += 2) {
                $char1 = $prepared_text[$i];
                $char2 = ($i + 1 < strlen($prepared_text)) ? $prepared_text[$i + 1] : 'X';

                if ($char1 == $char2) {
                    $char2 = 'X';
                    $i--;
                }

                $digraphs[] = array($char1, $char2);
            }

            $result = '';

            // Enkripsi/Dekripsi
            foreach ($digraphs as $pair) {
                $pos1 = $pos2 = array();

                // Mencari posisi pasangan huruf dalam matriks
                for ($row = 0; $row < 5; $row++) {
                    for ($col = 0; $col < 5; $col++) {
                        if ($matrix[$row][$col] == $pair[0]) {
                            $pos1 = array($row, $col);
                        }
                        if ($matrix[$row][$col] == $pair[1]) {
                            $pos2 = array($row, $col);
                        }
                    }
                }

                if ($pos1[0] == $pos2[0]) {
                    // Baris yang sama
                    $col1 = ($encrypt) ? ($pos1[1] + 1) % 5 : ($pos1[1] + 4) % 5;
                    $col2 = ($encrypt) ? ($pos2[1] + 1) % 5 : ($pos2[1] + 4) % 5;

                    $result .= $matrix[$pos1[0]][$col1] . $matrix[$pos2[0]][$col2];
                } elseif ($pos1[1] == $pos2[1]) {
                    // Kolom yang sama
                    $row1 = ($encrypt) ? ($pos1[0] + 1) % 5 : ($pos1[0] + 4) % 5;
                    $row2 = ($encrypt) ? ($pos2[0] + 1) % 5 : ($pos2[0] + 4) % 5;

                    $result .= $matrix[$row1][$pos1[1]] . $matrix[$row2][$pos2[1]];
                } else {
                    // Bentuk persegi
                    $result .= $matrix[$pos1[0]][$pos2[1]] . $matrix[$pos2[0]][$pos1[1]];
                }
            }

            return $result;
        }

        // Fungsi untuk Hill Cipher
        function hill_cipher($text, $keyMatrix, $encrypt = true) {
            $text = strtoupper($text);
            $text = str_replace(' ', '', $text);

            // Pastikan panjang teks kelipatan 2
            if (strlen($text) % 2 != 0) {
                $text .= 'X';
            }

            $result = '';

            // Hitung invers modular jika dekripsi
            if (!$encrypt) {
                $determinant = ($keyMatrix[0][0] * $keyMatrix[1][1] - $keyMatrix[0][1] * $keyMatrix[1][0]) % 26;
                $inverseDet = mod_inverse($determinant, 26);

                if ($inverseDet == -1) {
                    return "Kunci tidak memiliki invers, dekripsi tidak dapat dilakukan.";
                }

                $keyMatrix = [
                    [($keyMatrix[1][1] * $inverseDet) % 26, (-$keyMatrix[0][1] * $inverseDet) % 26],
                    [(-$keyMatrix[1][0] * $inverseDet) % 26, ($keyMatrix[0][0] * $inverseDet) % 26]
                ];

                // Normalisasi matriks
                for ($i = 0; $i < 2; $i++) {
                    for ($j = 0; $j < 2; $j++) {
                        $keyMatrix[$i][$j] = ($keyMatrix[$i][$j] + 26) % 26;
                    }
                }
            }

            // Proses enkripsi/dekripsi
            for ($i = 0; $i < strlen($text); $i += 2) {
                $char1 = ord($text[$i]) - ord('A');
                $char2 = ord($text[$i + 1]) - ord('A');

                $val1 = ($keyMatrix[0][0] * $char1 + $keyMatrix[0][1] * $char2) % 26;
                $val2 = ($keyMatrix[1][0] * $char1 + $keyMatrix[1][1] * $char2) % 26;

                $result .= chr($val1 + ord('A')) . chr($val2 + ord('A'));
            }

            return $result;
        }

        // Fungsi untuk menghitung invers modular
        function mod_inverse($a, $m) {
            $a = $a % $m;
            for ($x = 1; $x < $m; $x++) {
                if (($a * $x) % $m == 1) {
                    return $x;
                }
            }
            return -1;
        }

        // Fungsi untuk Super Enkripsi (Vigenere + Transposisi Kolom)
        function transposition_cipher($text, $key, $encrypt = true) {
            // Implementasi transposisi kolom sederhana
            $key_length = strlen($key);
            $text_length = strlen($text);
            $rows = ceil($text_length / $key_length);
            $grid = array();

            if ($encrypt) {
                // Mengisi grid dengan karakter teks
                $index = 0;
                for ($r = 0; $r < $rows; $r++) {
                    for ($c = 0; $c < $key_length; $c++) {
                        if ($index < $text_length) {
                            $grid[$r][$c] = $text[$index++];
                        } else {
                            $grid[$r][$c] = 'X'; // Padding
                        }
                    }
                }

                // Membaca grid berdasarkan urutan kunci
                $result = '';
                $key_order = str_split($key);
                asort($key_order);

                foreach ($key_order as $order => $char) {
                    for ($r = 0; $r < $rows; $r++) {
                        $result .= $grid[$r][$order];
                    }
                }
            } else {
                // Dekripsi
                $grid = array_fill(0, $rows, array_fill(0, $key_length, ''));
                $key_order = str_split($key);
                asort($key_order);

                $index = 0;
                foreach ($key_order as $order => $char) {
                    for ($r = 0; $r < $rows; $r++) {
                        if ($index < $text_length) {
                            $grid[$r][$order] = $text[$index++];
                        }
                    }
                }

                // Membaca grid untuk mendapatkan plaintext
                $result = '';
                for ($r = 0; $r < $rows; $r++) {
                    for ($c = 0; $c < $key_length; $c++) {
                        $result .= $grid[$r][$c];
                    }
                }
            }

            return $result;
        }

        // Proses Enkripsi/Dekripsi
        if ($cipher == "vigenere") {
            $result = vigenere_cipher($plaintext, $key, $action == "encrypt");
        } elseif ($cipher == "auto_key_vigenere") {
            $result = auto_key_vigenere_cipher($plaintext, $key, $action == "encrypt");
        } elseif ($cipher == "playfair") {
            $result = playfair_cipher($plaintext, $key, $action == "encrypt");
        } elseif ($cipher == "hill") {
            // Untuk Hill Cipher, kunci harus berupa matriks 2x2
            // Misalnya, masukkan kunci sebagai "3,3,2,5" untuk matriks [[3,3],[2,5]]
            $key_elements = explode(',', $key);
            if (count($key_elements) != 4) {
                echo "<p>Kunci untuk Hill Cipher harus 4 angka dipisahkan dengan koma.</p>";
                exit();
            }
            $keyMatrix = [
                [intval($key_elements[0]), intval($key_elements[1])],
                [intval($key_elements[2]), intval($key_elements[3])]
            ];

            $result = hill_cipher($plaintext, $keyMatrix, $action == "encrypt");
        } elseif ($cipher == "super_encryption") {
            $vigenere_result = vigenere_cipher($plaintext, $key, $action == "encrypt");
            $result = transposition_cipher($vigenere_result, $key, $action == "encrypt");
        } else {
            $result = "Cipher belum diimplementasikan.";
        }

        echo "<h3>Hasil:</h3>";
        echo "<p>$result</p>";
    }
    ?>
</body>
</html>
