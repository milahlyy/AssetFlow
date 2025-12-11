<!DOCTYPE html>
<html>
<head>
    <title>Form Validasi</title>
    <style>
        .error {color: red;}
    </style>
</head>
<body>

<h2>Form Validasi</h2>

<form method="POST" action="proses_val.php">

    Name: 
    <input type="text" name="name">
    <span class="error">*</span>
    <br><br>

    E-mail: 
    <input type="text" name="email">
    <span class="error">*</span>
    <br><br>

    Website: 
    <input type="text" name="website">
    <br><br>

    Gender:
    <input type="radio" name="gender" value="Female"> Female
    <input type="radio" name="gender" value="Male"> Male
    <span class="error">*</span>
    <br><br>

    <input type="submit" value="Submit">

</form>

</body>
</html>
