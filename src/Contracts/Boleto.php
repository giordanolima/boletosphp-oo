<?php

namespace GiordanoLima\BoletosPHP\Contracts;

interface Boleto
{
    /**
     * @return string
     */
    public function render();

    /**
     * Define o valor de um dado do boleto
     *
     * @param string $field
     * @param mixed $value
     *
     * @return void
     */
    public function setField($field, $value);

    /**
     * Atualiza valores dos dados
     *
     * @param array $data
     *
     * @return void
     */
    public function setFields(array $data);

    /**
     * Redefine todos os dados do boleto
     *
     * @param array $data
     *
     * @return void
     */
    public function setData(array $data);

    /**
     * @return array
     */
    public function getData();

    /**
     * Define a pasta de imagens
     *
     * @param string $path
     *
     * @return void
     */
    public function setImagePath($path);

    /**
     * Retorna campos obrigatórios
     *
     * @return array
     */
    public function getRequiredFields();

    /**
     * @return boolean
     */
    public function isValid();
}