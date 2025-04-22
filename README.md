# my-commands/my-commands/README.md

# My Commands

Este projeto contém uma coleção de comandos úteis para serem utilizados no terminal.

## Estrutura do Projeto

- **bin/console**: Este arquivo é o ponto de entrada da aplicação. Ele carrega o autoload do Composer, cria uma instância da aplicação Symfony Console e registra os comandos definidos no projeto.
  
- **src/Command/HelloCommand.php**: Este arquivo define a classe HelloCommand, que estende a classe Command do Symfony. A classe possui os métodos configure e execute. O método configure define a ajuda do comando, enquanto o método execute exibe uma saudação personalizada no terminal.

- **composer.json**: Este arquivo é a configuração do Composer. Ele lista as dependências do projeto, incluindo a biblioteca symfony/console, e define o autoloading para a namespace MyCommands. Também especifica o arquivo bin/console como o comando executável.

## Instalação

Para instalar as dependências do projeto, execute o seguinte comando:

```
composer install
```

## Uso

Para executar o comando `hello`, utilize o seguinte comando no terminal:

```
php bin/console hello
```

Isso exibirá uma saudação personalizada no terminal.