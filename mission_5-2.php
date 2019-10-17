<?php
//https://qiita.com/KosukeQiita/items/b56b3004413c999b9858
//https://www.flatflag.nir87.com/select-932
//https://www.dbonline.jp/mysql/
//https://26gram.com/mysql MySQLの一通りの操作
//4-1 データベースに接続
//$dsn式の中にスペースを入れない
$dsn = 'データベース名';
$user = 'ユーザー名';
$password = 'パスワード';
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
//array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING)とは、データベース操作で発生したエラーを警告表示
//データベースに繋がっているか確認
if ($pdo) {//正常に接続→何もしない
}
else{
    "データベースに接続されていません";
}
//4-2 テーブル作成
$sql = "CREATE TABLE IF NOT EXISTS tbdata"//CREATE TABLE [データベース名.]テーブル名 (カラム1 型情報, カラム2 型情報, ...);
." ("
. "id INT AUTO_INCREMENT PRIMARY KEY,"//通し番号
. "name char(32) NOT NULL,"//CHAR型(制限文字数) 固定長文字列
. "comment TEXT NOT NULL,"//TEXT型 長い文字列型、65535文字まで入力可。更に長い文字列を格納する場合はLONGTEXT型（4294967295文字まで）がある
. "date char(32) ,"//DATETIME型が適当?
. "pass char(32) NOT NULL"
.");";
$stmt = $pdo->query($sql);
//IF NOT EXISTSを入れないと２回目以降にこのプログラムを呼び出した際に、SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'tbdata' already existsが発生。既に存在するテーブルを作成しようとした際に発生するエラー
//4-3 テーブル一覧表示
$sql ='SHOW TABLES';
$result = $pdo -> query($sql);
foreach ($result as $row){
    echo $row[0];
    echo '<br>';
}
echo "<hr>";
//4-4 テーブル内確認
$sql ='SHOW CREATE TABLE tbdata';
$result = $pdo -> query($sql);
foreach ($result as $row){
    echo $row[1];
}
echo "<hr>";
//編集 選択
//https://teratail.com/questions/69214
$edit_num = isset($_POST["edit_num"]) ? $_POST["edit_num"] : null;
$edit_button = isset($_POST["edit_button"]) ? $_POST["edit_button"] : null;
if(isset($_POST['edit_num']) && isset($_POST['edit_button']) && isset($_POST["edit_pass"])){ //編集ボタンが押されたら
   	$edit_num = $_POST["edit_num"];
    $edit_pass = $_POST["edit_pass"];
//編集フォームの入力内容と照合
    $sql = 'SELECT pass FROM tbdata WHERE id=:edit_num AND pass=:edit_pass';
    $stmt = $pdo->prepare($sql);
    $stmt -> bindParam(':edit_num', $edit_num, PDO::PARAM_STR);
    $stmt -> bindParam(':edit_pass', $edit_pass, PDO::PARAM_STR);
    $stmt -> execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!empty($row)){
        $sql = 'SELECT * FROM tbdata WHERE id=:id AND pass=pass';
        $stmt = $pdo->prepare($sql);
        $stmt -> bindParam(':id', $edit_num, PDO::PARAM_STR);
        $stmt -> execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $edit_flag = TRUE;
    }

//	}
}	
else{//編集しない
    $edit_flag = FALSE;
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"><!-- 文字コード指定。ここはこのままで。 -->
	<title>入力及び削除・編集フォーム</title>
</head>
<!--<h1><b>掲示板のテーマ：疲れた時のリフレッシュ方法<b></h1>-->
<h2>入力フォーム</h2>
<body>
<form method="post" action="mission_5-1.php">
	<p>名前：<br>
	<input type="text" name="name" size="20" value=<?php if($edit_flag!=FALSE){echo $row['name'];}?> ><br> <!--placeholder="入力必須"--><!--placeholder=” 表示させたい内容をグレーで記述 ”valueの方が優先される、黒字-->
	<p>コメント：<br>
	<textarea name="comment" name="comment" cols="30" rows="5"><?php if($edit_flag!=FALSE){echo $row['comment'];}?></textarea><br>
	<p>パスワード:<br>
	<input type="password" name="pass" size="20" required><br><!--placeholder="入力必須"-->
	<input type="submit" name="submit_button"value="送信" size=70><br>
		<input type="hidden" name="now_num" cols="5" rows="1" value=<?php if($edit_flag!=FALSE){ echo $edit_num ; } ?>><br>
	<hr>
</form>
</body>
<h2>削除フォーム</h2>
<body>
<form method="post" action="mission_5-1.php">
	<p>削除対象番号：<br>
	<input type="text" name="delete_num" size="20" placeholder="半角数字で入力"><br>
	<p>パスワード:<br>
	<input type="password" name="delete_pass" size="20" placeholder="入力必須" required><br>
	<input type="submit" name="delete_button"value="削除" size=70>
	<hr>
</form>
</body>
<h2>編集フォーム</h2>
<body>
<form method="post" action="mission_5-1.php">
	<p>編集対象番号：<br>
	<input type="text" name="edit_num" size="20" placeholder="半角数字で入力"><br>
	<p>パスワード:<br>
	<input type="password" name="edit_pass" size="20" placeholder="入力必須" required><br>
	<input type="submit" name="edit_button"value="編集" size=70>
	<hr>
</form>
</body>
</html>
<?php
if (isset($_POST["name"], $_POST["comment"], $_POST["pass"])){//ポストにデータがある
    // ポストのデータを変数に
    //$id = count + 1;
    $name = $_POST["name"];
    $comment = $_POST["comment"];
    date_default_timezone_set('Asia/Tokyo');
    $date = date('y年m月d日h:i:s');
    $pass = $_POST["pass"];
}
//4-5 テーブルへデータ入力insert
if(isset($_POST["submit_button"]) && empty($_POST["now_num"])){
    //bindParamの引数（:nameなど）は4-2でどんな名前のカラムを設定したかで変える必要がある。4-6にて確認できる。
    $sql = $pdo -> prepare("INSERT INTO tbdata (name, comment, date, pass) VALUES (:name, :comment, '$date', :pass)");
    $sql -> bindParam(':name', $name, PDO::PARAM_STR);
    $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
//    $sql -> bindParam(':date', $date, PDO::PARAM_STR);
    $sql -> bindParam(':pass', $pass, PDO::PARAM_STR);
    //データ入力実行
    $sql -> execute();

    //4-6 表示
    //$rowの添字（[ ]内）は4-2でどんな名前のカラムを設定したかで変える必要がある。
    $sql = 'SELECT * FROM tbdata';
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    echo "新規投稿完了しました";
    echo "<br>";    
    foreach ($results as $row){
        //$rowの中にはテーブルのカラム名が入る
        echo $row['id'].',';
        echo $row['name'].',';
        echo $row['comment'].',';
        echo $row['date'].'<br>';
        //echo "<hr>";
    }
}

///編集 上書き
elseif(isset($_POST["submit_button"]) && !empty($_POST["now_num"]) && isset($_POST["pass"])){
    
    $name = $_POST["name"];
    $comment = $_POST["comment"];
    date_default_timezone_set('Asia/Tokyo');
    $date = date('y年m月d日h:i:s');
    $pass=$_POST["pass"];
    $now_num=$_POST["now_num"]; 
    $sql = 'SELECT * FROM tbdata WHERE id=:now_num AND pass=:pass';//idと入力フォームのhidden(now_num)の照合
    $stmt = $pdo->prepare($sql);
    $stmt -> bindParam(':now_num', $now_num, PDO::PARAM_STR);
    $stmt -> bindParam(':pass', $pass, PDO::PARAM_STR);
    $stmt -> execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
//    var_dump($row);
    //4-7 編集
    //bindParamの引数（:nameなど）は4-2でどんな名前のカラムを設定したかで変える必要がある。
    if(!empty($row)){
        $sql = 'UPDATE tbdata SET name=:name,comment=:comment,date=:date WHERE id=:id AND pass=:pass';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $now_num, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
        $stmt->execute();
    }	
    //(4-6 表示)
    //$rowの添字（[ ]内）は4-2でどんな名前のカラムを設定したかで変える必要がある。
    $sql = 'SELECT * FROM tbdata';
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    echo "編集を実行しました";
    echo "<br>";
    foreach ($results as $row){
        //$rowの中にはテーブルのカラム名が入る
        echo $row['id'].',';
        echo $row['name'].',';
        echo $row['comment'].',';
        echo $row['date'].'<br>';
        //echo "<hr>";
    }
}
else{
	echo " ";
}
?>
<?php
//削除
if((isset($_POST["delete_num"])) && ($_POST["delete_num"] !="") && (isset($_POST["delete_pass"]))){
    $delete_num=$_POST["delete_num"];
    $delete_pass=$_POST["delete_pass"];
    if (isset($_POST["delete_num"]) ){//4-8 削除
        $id = $_POST["delete_num"];
        $sql = 'DELETE FROM tbdata WHERE id=:id AND pass=:pass';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $delete_num, PDO::PARAM_INT);
        $stmt->bindParam(':pass', $delete_pass, PDO::PARAM_STR);
        $stmt->execute();
        //(4-6 表示)
        //$rowの添字（[ ]内）は4-2でどんな名前のカラムを設定したかで変える必要がある。
        $sql = 'SELECT * FROM tbdata';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        echo "削除を実行しました";
        echo "<br>";
        foreach ($results as $row){
            //$rowの中にはテーブルのカラム名が入る         
            echo $row['id'].',';
            echo $row['name'].',';
            echo $row['comment'].',';
            echo $row['date'].'<br>';
            //echo "<hr>";
        }
    }
}
else{
	echo " ";
}
?>



