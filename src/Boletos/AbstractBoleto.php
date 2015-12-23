<?php

namespace GiordanoLima\BoletosPHP\Boletos;

use GiordanoLima\BoletosPHP\Contracts\Boleto;

abstract class AbstractBoleto implements Boleto
{

    /**
     * AbstractBoleto constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->setFields($data);
    }

    /**
     * Dados do boleto
     *
     * @var array
     */
    protected $data = [];

    /**
     *  Campos disponíveis
     * @var array
     */
    protected static $availableFields = [

    ];

    /**
     * Campos obrigaórios
     *
     * @var array
     */
    protected static $requiredFields = [];

    protected static $imagePath;

    /**
     * Define o valor de um dado do boleto
     *
     * @param string $field
     * @param mixed $value
     *
     * @return void
     */
    public function setField($field, $value)
    {
        // @TODO Validar campos ao serem adcionados
        $this->data[$field] = $value;
    }

    /**
     * Redefine todos os dados do boleto
     *
     * @param array $data
     *
     * @return void
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * Atualiza valores dos dados
     *
     * @param array $data
     *
     * @return void
     */
    public function setFields(array $data)
    {
        foreach($data as $field => $value) {
            $this->setField($field, $value);
        }
    }

    /**
     * Define a pasta de imagens
     *
     * @param string $path
     *
     * @return void
     */
    public function setImagePath($path)
    {
        self::$imagePath = $path;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    public function isValid()
    {
        
    }

    /**
     * Retorna campos obrigatórios
     *
     * @return array
     */
    public function getRequiredFields()
    {
        return self::$requiredFields;
    }

    public function render()
    {
        // @TODO
    }
}