# 環境構築

## Dockerビルド<br>

・git clone git@github.com:sumeragi49/workrecordapplied3.git<br>
・docker compose up -d --build<br>

## Laravel環境構築<br>

・docker-compose exec php bash<br>
・composer install<br>
・cp .env .example.env<br>
・cp .env .testing.env<br>
・php artisan key:generate<br>
・php artisan key:generate --env=testing<br>
・php artisan migrate<br>
・php artisan migrate:fresh<br>
・php artisan db:seed<br>
・php artisan storage:link<br>

## 開発環境<br>

・会員登録ページ(一般ユーザー) http://localhost/register<br>
・ログイン画面(一般ユーザー) http://localhost/login<br>
・出勤登録画面(一般ユーザー) http://localhost/attendance<br>
・勤怠一覧画面(一般ユーザー) http://localhost/attendance/list<br>
・勤怠詳細画面(一般ユーザー) http://localhost/attendance/detail/{id}<br>
・申請一覧画面(一般ユーザー) http://localhost/stamp_correction_request/list<br>
・ログイン画面(管理者) http://localhost/admin/login<br>
・勤怠一覧画面(管理者) http://localhost/admin/attendance/list<br>
・勤怠詳細画面(管理者) http://localhost/admin/attendance/{id}<br>
・スタッフ一覧画面(管理者) http://localhost/admin/staff/list<br>
・スタッフ別勤怠一覧画面(管理者) http://localhost/admin/attendance/staff/{id}<br>
・申請一覧画面(管理者) http://localhost/stamp_correction_request/list<br>
・修正申請承認画面(管理者) http://localhost/stamp_correction_request/approve/{attendance_correct_request_id}<br>

## 使用技術(実行環境)<br>

・Composer version 2.9.3<br>
・laravel/laravel v8.6.12<br>
・laravel/fortify v1.19.1<br>
・PHPUnit 9.6.34<br>

## 連携外部サイト<br>

・mailhog<br>

## PHPUnitに関して<br>
テスト用DB<br>
・docker compose exec mysql bash<br>
・mysql -u root -p (password:root)<br>
MySQLログイン後<br>
・CREATE DATABASE demo_test;<br>
configファイルの変更<br>
<br>
database.phpのファイルを開き,mysqlの部分をコピーし,下にmysql_testを作成し貼り付ける。その際の変更点を以下の表とする

| 項目 | 変更前 | 変更後 |
| --- | --- | --- |
| 'database' | env('DB_DATABASE', 'forge') | 'demo_test' |
| 'username' | env('DB_USERNAME', 'forge') | 'root' |
| 'password' | env('DB_PASSWORD', '') | 'root' |

テスト用の.envファイルを作成<br>
・.envをコピーして「.env.testing」作成<br>
・PHPコンテナ上で コマンド cp .env .env.testing<br>

| 項目 | 変更前 | 変更後 |
| --- | --- | --- |
| APP_ENV | local | test |
| APP_KEY | ランダムなkey | (空欄にする) |
| DB_DATABASE | laravel_db | demo_test |
| DB_USERNAME | laravel_user | root |
| DB_PASSWORD | laravel_pass | root |

・APP_KEYに新たなテスト用のアプリケーション用キーを作成<br>
・PHPコンテナ上で コマンド php artisan key:generate --env=testing<br>
*必要であれば php artisan config:clear<br>
・php artisan migrate --env=testing<br>
<br>
phpunit.xmlの変更<br>

| 項目 | 変更前 | 変更後 |
| --- | --- | --- |
| DB_CONNECTION | "sqlite" | "mysql_test" |
| DB_DATABASE | "memory" | "demo_test" |

・2つの変更点とも共通して「<!-- -->」を削除する(中身は消さない)<br>

テスト実行コマンド<br>
・vendor/bin/phpunit tests/Feature/RegisterTest.php<br>
・vendor/bin/phpunit tests/Feature/LoginTest.php<br>
・vendor/bin/phpunit tests/Feature/AttendanceTest.php<br>
・vendor/bin/phpunit tests/Feature/AdminTest.php<br>
・vendor/bin/phpunit tests/Feature/EmailVerificationTest.php<br>

### test ユーザー<br>
staff<br>
・id:1 name:山田太郎 email:test1@example.com password:coachtech1001<br>
・id:2 name:西伶奈 email:test2@example.com password:coachtech1002<br>
・id:3 name:増田一世 email:test3@example.com password:coachtech1003<br>
・id:4 name:山本敬吉 email:test4@example.com password:coachtech1004<br>
・id:5 name:秋田朋美 email:test5@example.com password:coachtech1005<br>
・id:6 name:中西敦夫 email:test6@example.com password:coachtech1006<br>
*2026年1月に1か月分の勤怠のダミーデータを作成しています<br>

admin<br>
・id:7 name:山田花子 email:test7@example.com password:coachtech1007<br>

### ER図<br>
<img width="1555" height="1245" alt="スクリーンショット 2026-05-20 124747" src="https://github.com/user-attachments/assets/b3917f33-6315-4fed-94f7-be1300efeef2" />



