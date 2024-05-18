<?php require('./phpsql.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $no = $_POST['no'];
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // 準備更新的 SQL 語句
    $sql = "UPDATE member SET name = ?, username = ?, password = ?, role = ? WHERE no = ?";
    if ($stmt = mysqli_prepare($mysqli, $sql)) {
        // 綁定參數
        mysqli_stmt_bind_param($stmt, "ssssi", $name, $username, $password, $role, $no);
        
        // 執行更新操作
        if (mysqli_stmt_execute($stmt)) {
            // 更新成功，重定向回主頁面
            header("Location: admin.php");
            exit();
        } else {
            echo "更新資料時發生錯誤: " . mysqli_stmt_error($stmt);
        }
        
        // 關閉語句
        mysqli_stmt_close($stmt);
    } else {
        echo "無法準備 SQL 語句: " . mysqli_error($mysqli);
    }

    // 關閉資料庫連接
    mysqli_close($mysqli);
}
