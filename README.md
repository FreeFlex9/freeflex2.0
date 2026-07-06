# How to use:
- A versão do PHP utilizada no Projeto é PHP 8.4
- No php.ini, configure `upload_max_filesize >= 8M` e `post_max_size >= 10M` (o padrão do PHP é 2M, insuficiente para o limite de 5MB por documento usado no cadastro de prestadores/empresas — valores abaixo disso causam falha silenciosa no upload de PDFs/imagens maiores)
- configure o banco de dados com .env
- Copie o arquivo **.env.example** para **.env** e configure as variáveis de ambiente relacionadas ao banco de dados
- execute os comandos:
```
composer install
```
```
npm install
```
- então execute o comando
```
php artisan key:generate
```
- abra o servidor E EXECUTE:
```
php artisan serve
```
```
npm run dev
```
- REVERB para chat em tempo real
```
php artisan reverb:start   
```
- execute as migrações: (seeder são opcionais)
```
php artisan db:seed --class=AdminSeeder
```
- e finalmente acessar a URL: http://127.0.0.1:8000/