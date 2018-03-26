<?php

namespace DirectokiBundle;



/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ImportCSVLineResult
{


    protected $debugOutput;

    protected $entitiesToSave;

    function __construct( $debugOutput, $fieldValuesToSave ) {
        $this->debugOutput = $debugOutput;
        $this->entitiesToSave = $fieldValuesToSave;
    }


    /**
     * @return string
     */
    public function getDebugOutput() {
        return $this->debugOutput;
    }

    /**
     * @return array
     */
    public function getEntitiesToSave() {
        return $this->entitiesToSave;
    }



}

