Web part of the application to process olimpiads

### Setup on Windows

Install OpenServer:

- php (v7.2.34)
- mysql (v8.0)
- nginx/apache
- phpmyadmin
- add Domain "olimp-fmi -> olimp-fmi/www"

Install "Composer"

- php composer.phar update

Migrate DB:

- vendor/bin/phinx migrate
- add olimp/5rpgw5tt user
```
INSERT INTO `oi_users` (`username`, `password`, `password_salt`, `email`, `class`, `school`, `phone`, `name`, `surname`, `created_at`, `updated_at`, `score`, `mulct`, `old_score`, `is_admin`, `is_enabled`, `guid`, `live_update`)
VALUES ('olimp', 'e94234ff93abfce93b49bc08f0b792fab53c6d4e', '1187605225e4abfcd36c6b', 'olimp@rshu.edu.ua', 'Admin', 'Admin', '(000) 000-00-00', 'Admin', 'Admin', '2019-01-08 11:49:23', '2020-02-17 18:31:09', 0, 0, 0, 1, 1, '22DD176B-B8D9-4ACE-AF5A-80E532D835BF', 0)
```
- disable ONLY_FULL_GROUP_BY:
```
SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));
```