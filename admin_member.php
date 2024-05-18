<?php
require('./phpsql.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save'])) {
    $no = $_POST['no'];
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $sql = "UPDATE member SET name = ?, username = ?, password = ?, role = ? WHERE no = ?";
    if ($stmt = mysqli_prepare($mysqli, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssssi", $name, $username, $password, $role, $no);
        if (mysqli_stmt_execute($stmt)) {
            echo "<p>資料已更新</p>";
        } else {
            echo "<p>更新資料時發生錯誤: " . mysqli_stmt_error($stmt) . "</p>";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "<p>無法準備 SQL 語句: " . mysqli_error($mysqli) . "</p>";
    }

    // 重定向回主頁面
    header("Location: " . $_SERVER["PHP_SELF"]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD 操作介面</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        h1 {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .box {
            padding-left: 50px;
            padding-right: 50px;
        }

        #search {
            margin-left: auto;
            margin-right: auto;
            width: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #form {
            display: flex;
            align-items: center;
        }

        #search input {
            width: 250px;
        }

        #search button {
            margin-bottom: 10px;
        }

        tr:hover {
            cursor: pointer;
            background-color: #f5f5f5;
        }

        .editable {
            display: none;
        }

        .editable input {
            width: 100%;
        }

        .edit-btn, .delete-btn {
            display: inline-block;
        }

        .save-btn {
            display: none;
        }

        .view {
            display: inline-block;
        }
    </style>
</head>

<body>
    <h1>使用者身份管理</h1>
    <div class="box">
        <div id="search">
            <form id="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="text" name="post_search" value="<?php echo isset($_POST['post_search']) ? htmlspecialchars($_POST['post_search']) : ''; ?>">
                <button type="submit" name="search" width="100px">搜尋</button>
            </form>
        </div>

        <?php
        $search = isset($_POST['post_search']) ? $_POST['post_search'] : '';

        echo "<table>
            <thead>
                <tr>
                    <th>no</th>
                    <th>姓名</th>
                    <th>使用者帳號</th>
                    <th>密碼</th>
                    <th>身份</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody id=\"tableBody\">";

        $sql = "SELECT * FROM member";
        $params = [];

        if (!empty(trim($search))) {
            $searchTerm = "%" . $search . "%";
            $sql .= " WHERE no LIKE ? OR name LIKE ? OR username LIKE ? OR role LIKE ?";
            $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        }

        if ($stmt = mysqli_prepare($mysqli, $sql)) {
            if (!empty($params)) {
                mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
            }

            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                    <form method='post' action='update.php'>
                        <td><input type='hidden' name='no' value='" . htmlspecialchars($row['no']) . "'>" . htmlspecialchars($row['no']) . "</td>
                        <td><span class='view'>" . htmlspecialchars($row['name']) . "</span><input type='text' class='editable' name='name' value='" . htmlspecialchars($row['name']) . "'></td>
                        <td><span class='view'>" . htmlspecialchars($row['username']) . "</span><input type='text' class='editable' name='username' value='" . htmlspecialchars($row['username']) . "'></td>
                        <td><span class='view'>" . htmlspecialchars($row['password']) . "</span><input type='text' class='editable' name='password' value='" . htmlspecialchars($row['password']) . "'></td>
                        <td><span class='view'>" . htmlspecialchars($row['role']) . "</span><input type='text' class='editable' name='role' value='" . htmlspecialchars($row['role']) . "'></td>
                        <td>
                            <button type='button' class='edit-btn' onclick='editRow(this)'>編輯</button>
                            <button type='submit' class='save-btn' name='save'>保存</button>
                            <button type='button' class='delete-btn' onclick='deleteRow(this, \"" . htmlspecialchars($row['no']) . "\")'>刪除</button>
                        </td>
                    </form>
                </tr>";
            }

            mysqli_stmt_close($stmt);
        } else {
            echo "<tr><td colspan='6'>無法執行查詢: " . mysqli_error($mysqli) . "</td></tr>";
        }

        echo "</tbody></table></div>";
        ?>
    </div>

    <script>
        function editRow(button) {
            const row = button.closest('tr');
            row.querySelectorAll('.view').forEach(element => element.style.display = 'none');
            row.querySelectorAll('.editable').forEach(element => element.style.display = 'inline-block');
            row.querySelector('.edit-btn').style.display = 'none';
            row.querySelector('.save-btn').style.display = 'inline-block';
        }

        function deleteRow(button, no) {
            if (confirm('確定要刪除此筆資料嗎？')) {
                const form = button.closest('form');
                form.action = 'delete.php';
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'no';
                input.value = no;
                form.appendChild(input);
                form.submit();
            }
        }
    </script>
</body>

</html>