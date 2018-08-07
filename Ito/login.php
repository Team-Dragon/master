<?php
/*MySQLの設定とデータベースの作成*/
header("Content-Type: text/html; charset=UTF-8");

$dsn = "#";
$user = "#";
$password = "#";		
$pdo = new PDO($dsn,$user,$password);

//tableの作成：
$sql = "create table newac" 
." ("
."id INT AUTO_INCREMENT,"
."name char(32),"
."id_u char(32),"
."pass char(32),"
."mail varchar(256),"
."ky varchar(8),"
."ch1 INT,"
."time TIMESTAMP,"
."INDEX(id)"
.");";
$stmt = $pdo->query($sql);

$error_co; //errorの表示に使う
$error=0; //errorの個数

$id_u=$_POST["id_u"]; //ユーザーを識別するＩＤ
$pass=$_POST["pass"]; //パスワード

/*入力エラーの確認*/

if($id_u==""){
	$error_co="ユーザーIDが入力されていません";
	$error++;
}else if(!preg_match("/^[a-zA-Z0-9]+$/",$id_u)) //英数字に一致しない場合
{
    $error_co="ユーザーＩＤは半角英数字で入力してください";
    $error++;
}
if($pass==""){
	$error_co="パスワードが入力されていません";
	$error;
}else if(!preg_match("/^[a-zA-Z0-9]+$/",$pass)){
	$error_co="パスワードは半角英数字で入力してください";
	$error++;
}else if(mb_strlen($pass,"utf-8")<8){ //文字数が８文字より少ない場合
	$error_co="パスワードは8字以上で入力してください";
	$error++;
}else if(mb_strlen($pass,"utf-8")>32){
	$error_co="パスワードは32字以下で入力してください";
	$error++;
}

/*入力値が正しいかの確認*/

if($error==0){
	$sql="select*from newac where id_u=:id_u"; //databaseからUserIDが一致するものを検索
	$result=$pdo->prepare($sql);
    	$result->bindParam(':id_u',$id_u,PDO::PARAM_STR);
    	$result->execute();
	$row=$result->fetch(PDO::FETCH_ASSOC);
	$pass=crypt($pass,$row["pass"]); //入力パスワードをハッシュ化する
	$pass=mb_substr($pass,0,mb_strlen($row["pass"])); //文字数の調整を行う。（なぜかズレてしまうから）
	if($row==NULL){
		$error_co="ユーザーID　または　パスワードが間違っています";
		$error++;
	}elseif($row["pass"]!=$pass){
			$error_co="ユーザーＩＤ　または　パスワードが間違っています"; //不正アクセス者にばれてしまうので原因が分かっていてもぼかして知らせる
			$error++;
	}elseif($row["ch1"]==0){  //mailから本人確認をするとch1が１になる
			$error_co="本人確認が完了していません";
			$error++;
	}else{
		$name = $row["name"];
	}
}

/*ログイン情報をセッションに渡す*/
if($error==0){
	session_start();
	$_SESSION["user"] = array("name"=>$name, "id_u"=>$id_u, "pass"=>$pass);
	header('Location: http://co-153.99sv-coco.com/deliverable/php/top_account_login.php');　//login後の画面へ移動
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<link href="../css/menu1.css" rel="stylesheet" type="text/css">　//このページのcss
<link href="../css/background2.css" rel="stylesheet" type="text/css"> //背景画像のcss
</head>
<body>
<?php require('../html/tabs.html'); ?> //タブメニューのhtmlを呼び出す
<br><br><br>
<!--このページの内容を記述-->
<div class="menu">
<font size=5>
<p>
<h1 style="text-align:center;">LOGIN</h1>
</p>
<font size=3>
<div class="form1">
<form action="top_account_logout.php" method="POST">
<font color="white">
<input type="text" name="id_u"  placeholder="ID" class="id_u">
<input type="password" name="pass" placeholder="Password" class="pass">
<div align="center">
<?php echo $error_co ?><br><br>
</div>
<input type="submit" value="Login" class="login">
<form>
</div>
<form>
<input type="button" value="新規登録" onclick="location.href='newaccount.php'"  class="new"><br>
</form>
</div>

<body>
</html>