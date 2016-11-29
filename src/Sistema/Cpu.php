<?php

/**
 * Classe utilizada para recuperar informações referentes a CPU da máquina
 * 
 * @package     WMI
 * @subpackage  bibliotecas
 * @access      public
 * @author      Fábio Jânio
 * @email       contato@fabiojanio.com
 * @homepage    http://www.fabiojanio.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3
 */

namespace Crphp\Wmic\Sistema;

use Crphp\Core\Sistema\Conector;
use Crphp\Core\Interfaces\Sistema\CpuInterface;

class Cpu implements CpuInterface
{
    /**
     * Armazena uma lista de informações referentes ao processador (CPU)
     *
     * @var object
     */
    private $cpu;

    /**
     * Consulta as informações da CPU(s) reconhecida(s) pelo host remoto
     * 
     * @param \Crphp\Wmi\Conectores\Conector $conexao
     * @return null
     */
    public function __construct(Conector $conexao)
    {
        $this->cpu = $conexao->executar(
                                          "SELECT
                                              Caption,
                                              DeviceID,
                                              LoadPercentage,
                                              CurrentClockSpeed,
                                              Name,
                                              NumberOfCores,
                                              DataWidth,
                                              NumberOfLogicalProcessors
                                          FROM Win32_Processor"
                                       );
    }
        
    /**
     * Retorna visão geral referente a CPU
     * 
     * @return array
     */
    public function detalhes()
    {
        foreach ($this->cpu as $c) {
            $cpu[$c->DeviceID] = [
                'nome' => $c->Name,
                'arquitetura' => $c->DataWidth,
                'mhz' => $c->CurrentClockSpeed,
                'nucleos' => $c->NumberOfCores,
                'processadoresLogicos' => $c->NumberOfLogicalProcessors,
                'cargaDoProcessador' => $c->LoadPercentage
            ];
        }
        
        return $cpu;
    }
}