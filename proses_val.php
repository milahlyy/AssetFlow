<?php
// Variabel untuk menampung error
$nameErr = $emailErr = $genderErr = $websiteErr = "";

// Variabel untuk menampung nilai
$name = $email = $gender = $website = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // NAME
    if (empty($_POST["name"])) {
        $nameErr = "Name wajib diisi";
    } else {
        $name = bersihkan($_POST["name"]);
        if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
            $nameErr = "Hanya huruf dan spasi diperbolehkan";
        }
    }

    // EMAIL
    if (empty($_POST["email"])) {
        $emailErr = "Email wajib diisi";
    } else {
        $email = bersihkan($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Format email tidak valid";
        }
    }

    // WEBSITE (opsional)
    if (!empty($_POST["website"])) {
        $website = bersihkan($_POST["website"]);
        if (!filter_var($website, FILTER_VALIDATE_URL)) {
            $websiteErr = "URL tidak valid";
        }
    }

    // GENDER
    if (empty($_POST["gender"])) {
        $genderErr = "Gender wajib dipilih";
    } else {
        $gender = bersihkan($_POST["gender"]);
    }
}

// Fungsi sanitasi input
function bersihkan($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hasil Validasi</title>
    <style>.error{color:red;}</style>
</head>
<body>

<h2>Hasil Validasi</h2>

<?php
// Jika ada error
if ($nameErr || $emailErr || $websiteErr || $genderErr) {
    echo "<h3 class='error'>Terdapat kesalahan pada input:</h3>";
    echo "<p class='error'>Name: $nameErr</p>";
    echo "<p class='error'>Email: $emailErr</p>";
    echo "<p class='error'>Website: $websiteErr</p>";
    echo "<p class='error'>Gender: $genderErr</p>";
    echo "<br><a href='validasi.php'>Kembali ke Form</a>";
} else {
    // Jika semua valid
    echo "<h3>Data Anda:</h3>";
    echo "Name: $name <br>";
    echo "Email: $email <br>";
    echo "Website: $website <br>";
    echo "Gender: $gender <br>";
}
?>

</body>
</html>
