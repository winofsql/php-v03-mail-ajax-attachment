<?php
// ***************************
// メール送信
// ***************************
function db_mail(){

    global $server,$user,$dbname,$password,$message;

    // ***************************
    // DB 接続
    // ***************************
    $mysqli = @ new mysqli($server, $user, $password, $dbname);
    if ($mysqli->connect_error) {
        print "接続エラーです : ({$mysqli->connect_errno}) ({$mysqli->connect_error})";
        exit();
    }
    // ***************************
    // クライアントの文字セット
    // ***************************
    $mysqli->set_charset("utf8"); 

    // テキストの改行を整備
    $_POST["text"] = str_replace("\r\n", "\n", $_POST["text"]);
    $_POST["text"] = str_replace("\n", "\r\n", $_POST["text"]);

    // insert 用 SQL
$query = <<<QUERY
insert into maildb (
    to_address,
    subject,
    one_comment,
    create_date
) values(
    ?,
    ?,
    ?,
NOW())
QUERY;

    // 実行準備
    $stmt = $mysqli->prepare($query);
    if ( $stmt ) {
        // 入力データの埋め込み
        $stmt->bind_param("sss", $_POST["to"], $_POST["subject"], $_POST["text"]);
        $stmt->execute();
    }
    else {
        $create = "create table maildb ( mail_id serial, to_address varchar(100), subject varchar(100), one_comment varchar(100), create_date datetime, primary key(mail_id) )";
        $mysqli->query($create);
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sss", $_POST["to"], $_POST["subject"], $_POST["text"]);
        $stmt->execute();
    }

    // メール送信の準備
    $mail_address = "a@b.c.jp";
    $from_header = "From: " . mb_encode_mimeheader( mb_convert_encoding("差出人の名前","iso-2022-jp") );
    $from_header .= " <{$mail_address}>";

    $body = $_POST["text"];

    if ( isset( $_FILES["file"] ) ) {
        if ( $_FILES["file"]["error"] == 0 ) {

            $uniqid = uniqid();

            // このソースを置くサーバから使えるメールアドレス
            $from_header .= "\n";
            $from_header .= "Content-Type: multipart/mixed; boundary=\"{$uniqid}\"\n";

            $mime = $_FILES['file']['type'];
            $fname = $_FILES['file']['name'];

            $body  =<<< MAIL_DATA
--{$uniqid}
Content-Type: text/plain; charset="ISO-2022-JP"

{$_POST["text"]}
--{$uniqid}
Content-Type: {$mime}; name="{$fname}"
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="{$fname}"


MAIL_DATA;

            // アップロードされたファイル
            $path = $_FILES["file"]["tmp_name"];
            $data = file_get_contents($path);
            $encode = base64_encode($data);

            $body .= chunk_split($encode);
            $body .= "\n--{$uniqid}--\n";

        }
    }

    // 後でデータ確認用
    file_put_contents("mail.txt", $body );

    // メール送信
    $result = mb_send_mail($_POST["to"], $_POST["subject"], $body, $from_header);

    // 新しい投稿用のクラス作成
    $json = new stdClass;
    
    $json->to = $_POST["to"];
    $json->subject = $_POST["subject"];
    $json->text = $_POST["text"];
    $json->status = $result;

    print json_encode( $json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
}

// **************************
// デバック
// **************************
function debug_print(){

    print "<pre class=\"m-5\">";
    print_r( $_GET );
    print_r( $_POST );
    print_r( $_SESSION );
    print_r( $_FILES );
    print "</pre>";

}

