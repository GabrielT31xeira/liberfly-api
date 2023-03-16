# Tutorial

- Criar o arquivo .env na raiz do projeto colocar o conteudo do .env.example dentro dele.
- Rodar o `docker compose up`
- Dentro do container do laravel rodar `chmod -R 777 storage`.
- Dentro do container do laravel rodar `php artisan key:generate`.
- Dentro do container do laravel rodar para criar o banco de dados `php artisan migrate`.
- Visite a url `localhost:8083/` parar verificar se o laravel está rodando.
- Usar o swagger com o comando "docker run -p 8081:8080 -e SWAGGER_JSON=/app/swagger.json -v `pwd`/public/docs/swagger/api-docs.json:/app/swagger.json swaggerapi/swagger-ui" fora do container na raiz do projeto
