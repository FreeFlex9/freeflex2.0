# How to use:
- A versão do PHP utilizada no Projeto é PHP 8.4
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
- abra o servidor:
```
php artisan serve
```
```
- REVERB para chat em tempo real
```
php artisan reverb:start   
```
```
- deixe o npm em execução:
```
npm run dev
```
- execute as migrações: (seeder são opcionais)
```
php artisan migrate --seed
```
- e finalmente execute este comando:
```
php artisan storage:link
```
- acessar a URL: http://127.0.0.1:8000/