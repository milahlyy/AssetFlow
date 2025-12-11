<!DOCTYPE html>
<html>
<head>
<title>Buku Tamu</title>
</head>

<body>
<h3>ISI BUKU TAMU</h3>
<form action="proses_buku.php" method="POST">
    <table>
    <tr>
    <td>Name</td>
    <td></td>
    <td><input type="text" name="name"></td>
    </tr>
    <tr>
    <td>Jenis Kelamin</td>
    <td></td>
    <td>
    <input type="radio" name="jk" value="Laki-Laki">laki-laki<br/>
    <input type="radio" name="jk" value="Perempuan">perempuan<br/>
    </td>
    </tr>
    <tr>
    <td>Program Studi</td>
    <td></td>
    <td>
    <select name="prodi">
    <option value="Informatika">Informatika</option>
    <option value="SI">S1 Sistem Informasi</option>
    <option value="D3">D3 Sistem Informasi</option>
    </select>
    </td>
</tr>
<tr>
<td>Hobi</td>
<td></td>
<td>
    <input type="checkbox" name="hobi[]" value="ngoding">Ngoding<br/>
    <input type="checkbox" name="hobi[]" value="membaca">Membaca<br/>
    <input type="checkbox" name="hobi[]" value="tidur">Tidur<br/>
    </td>
</tr>
<tr>
<td>Pesan</td>
<td></td>
<td>
    <textarea name="pesan" rows="5" cols="30"></textarea>
    </td>
</tr>
<tr>
<td colspan="3" align="right">
    <input type="submit" value="kirim">
    </td>
</tr>
</table>
</form>
</body>
</html>