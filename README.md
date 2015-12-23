# BoletosPHP Orientado à Objetos
[![Latest Stable Version](https://poser.pugx.org/giordanolima/boletosphp-oo/v/stable)](https://packagist.org/packages/giordanolima/boletosphp-oo) [![Total Downloads](https://poser.pugx.org/giordanolima/boletosphp-oo/downloads)](https://packagist.org/packages/giordanolima/boletosphp-oo) [![License](https://poser.pugx.org/giordanolima/boletosphp-oo/license)](https://packagist.org/packages/giordanolima/boletosphp-oo) [![StyleCI](https://styleci.io/repos/48493988/shield)](https://styleci.io/repos/48493988)

Esse pacote foi criado a partir do pacote BoletosPHP original ([link](http://boletophp.com.br/)) e fornece a mesma lógica, porém orientado a objeto, para uma melhor aplicação em frameworks e uso através do composer.

## Pacote em desenvolvimento
Este pacote ainda encontra-se em fase de adaptação e não tem suporte a todos os bancos disponíveis no pacote original. Aos poucos estes vão sendo adicionados. Dê um fork e contribua e ajude com correções de bugs e novas features. Atualmente o pacote possui suporte aos seguintes bancos:
* Itaú
* Sicredi

## Install
Instalação através do composer:

```bash
composer require giordanolima/boletosphp-oo
```

## Uso
```php
use GiordanoLima\BoletosPHP\BoletosPHP;
$boleto = new BoletosPHP(BoletosPHP::BOLETOSPHP_ITAU);
$boleto->setData([...]);
$boleto->setImageBasePath("path/to/images/");
echo $boleto->render();
```

## Constantes
Ao instanciar a classe, o banco que será gerado o boleto deverá ser passado como parâmetro. Segue abaixo a tabela dos bancos e suas respectivas constantes:

| Banco   | Constante          |
| ------- | ------------------ |
| Itaú    | BOLETOSPHP_ITAU    |
| Sicredi | BOLETOSPHP_SICREDI |

## Dados
Os dados dos boletos deverão ser passados através do método `setData`no formato de array, no estilo `campo => valor`. 
Ex.:
```php
$boleto->setData([
    "valor_boleto" => "99,00",
    "data_vencimento" => "01/04/2016",
    "nosso_numero" => 00000001,
    (...)
]);
```
Os dados padrão a todos os bancos são:

|        Campo       | Descrição                                                 | Obrigatório |
|:------------------:|-----------------------------------------------------------|:-----------:|
|    valor_boleto    | Valor do boleto no formato de moeda brasileira.           |     SIM     |
|   data_vencimento  | Data de vencimento do boleto no formato d/m/Y             |     SIM     |
|    nosso_numero    | Número que será usado como base para gerar o nosso número |     SIM     |
|       agencia      | Agência do cedente                                        |     SIM     |
|        conta       | Conta do cedente                                          |     SIM     |
|      conta_dv      | Dígito verificador da conta do cedente                    |     SIM     |
|      carteira      | Carteira do cedente                                       |     SIM     |
|    identificacao   | Nome do cedente                                           |     SIM     |
|      cpf_cnpj      | CPF ou CNPJ do cedente                                    |     SIM     |
|  numero_documento  | Campo "Número do Documento" do boleto                     |     NÃO     |
|   data_documento   | Data do documento. Formato: d/m/Y                         |     NÃO     |
| data_processamento | Data do processamento. Formato: d/m/Y                     |     NÃO     |
|       sacado       | Nome do sacado                                            |     NÃO     |
|      endereco      | Endereço completo do sacado a ser mostrado no Recibo      |     NÃO     |
|      endereco1     | Linha 1 do endereço no corpo do boleto                    |     NÃO     |
|      endereco2     | Linha 2 do endereço no corpo do boleto                    |     NÃO     |
|   demonstrativo1   | Linha 1 do demonstrativo                                  |     NÃO     |
|   demonstrativo2   | Linha 2 do demonstrativo                                  |     NÃO     |
|   demonstrativo3   | Linha 3 do demonstrativo                                  |     NÃO     |
|     instrucoes1    | Linha 1 das instruções                                    |     NÃO     |
|     instrucoes2    | Linha 2 das instruções                                    |     NÃO     |
|     instrucoes3    | Linha 3 das instruções                                    |     NÃO     |
|     instrucoes4    | Linha 4 das instruções                                    |     NÃO     |
|     quantidade     | Campo quantidade do boleto                                |     NÃO     |
|   valor_unitario   | Campo valor unitário do boleto                            |     NÃO     |
|       aceite       | Campo aceito do boleto                                    |     NÃO     |
|       especie      | Campo espécie do boleto                                   |     NÃO     |
|     especie_doc    | Campo especie_doc do boleto                               |     NÃO     |
|      cidade_uf     | Cidade/UF do cedente                                      |     NÃO     |
|       cedente      | Razão Social ou Nome Completo do cedente                  |     NÃO     |
Cada banco possui seus campos específicos, são eles, respectivamente:
### Itaú
Não existem campos específicos para esse banco.
### Sicredi

|   Campo  | Descrição                                                                          | Obrigatório |
|:--------:|------------------------------------------------------------------------------------|:-----------:|
|   posto  | Código do posto da cooperativa de crédito                                          |     SIM     |
| byte_idt | Byte de identificação do cedente do bloqueto utilizado para compor o nosso número. |     SIM     |
## Imagens
As imagens utilizadas no pacote estão na pasta `imagens` do pacote. Essas imagens deverão ser colocadas em uma pasta pública do projeto e o caminho deverá ser setada pelo método `setImageBasePath`. Ex.:
```php
$boleto->setImageBasePath("http://meusite.com.br/img/boletosphp/"); // Com "/" no final
```
## Render
O método `render` irá retornar um código HTML com o respectivo boleto gerado.
```php
echo $boleto->render();
/* Irá imprimir um código HTML
 * <html>
 * ...
 * </html>
*/
```