<?php

$stringToProcess = '
vamos a ver cómo funciona<br>
<!-- ~#~HOLA~#~ --><br>
Hoy es <!-- ~#~TODAY_IS~#~ --><br>
Vamos a ver los colores:
<!-- %COLORES% -->
Color : <!-- ~#~COLOR~#~ --><br>
Y tiene estos valores:
<ul>
  <!-- %VALORES% -->
	<li><!-- ~#~VALOR~#~ --></li>
	<!-- %/VALORES% -->
</ul>
<!-- %/COLORES% -->';

$data = array(
	'hola' => 'Hola mundo',
	'today_is' => date('d/m/Y'),
	'colores' => array(
		0 => array(
			'color' => 'Verde',
			'valores' => array(
				array(
					'valor' => 1),
				array(
					'valor' => 2),
				array(
					'valor' => 3),
			)
		),
		1 => array(
			'color' => 'Blanco',
			'valores' => array(
				array(
					'valor' => 4),
				array(
					'valor' => 5),
				array(
					'valor' => 6),
				array(
					'valor' => 7),
			)
		),
	)
);

class Processor{
	private $initElementTag 	= '<!-- ~#~';
	private $endElementTag 		= '~#~ -->';
	private $initBlockTag 		= '<!-- %';
	private $endBlockTag 		= '% -->';
	private $closeEndBlockTag 	= '/';
	private $stringToProcess;
	private $data;
	private $initElementTagLength;
	private $endElementTagLength;
	private $initBlockTagLength;
	private $endBlockTagLength;
	private $closeBlockTagLength;

	public function __construct($stringToProcess, Array $data){
		$this->stringToProcess = $stringToProcess;
		$this->data = $data;

		$this->initElementTagLength = strlen($this->initElementTag);
		$this->endElementTagLength 	= strlen($this->endElementTag);
		$this->initBlockTagLength 	= strlen($this->initBlockTag);
		$this->endBlockTagLength	= strlen($this->endBlockTag);
		$this->closeBlockTagLength 	= strlen($this->closeEndBlockTag);
	}

	public function process(){
		$this->stringToProcess = $this->processBlocks($this->stringToProcess);
		$this->stringToProcess = $this->processElements($this->stringToProcess);

		return $this->stringToProcess;
	}

	private function processBlocks($stringToProcess, Array $data = null){
		if ($data == null){
			$data = $this->data;
		}

		do{
			$posIni = strpos($stringToProcess, $this->initBlockTag);
			if ($posIni){
				$posEnd = strpos($stringToProcess, $this->endBlockTag, $posIni);
				$varName = substr($stringToProcess, $posIni + $this->initBlockTagLength, $posEnd - ($posIni + $this->initBlockTagLength));

				$secondId = "{$this->initBlockTag}{$this->closeEndBlockTag}{$varName}{$this->endBlockTag}";
				$posSecondId = strpos($stringToProcess, $secondId);

				$strBlock = substr($stringToProcess, $posEnd + $this->endBlockTagLength, $posSecondId - ($posEnd + $this->endBlockTagLength));

				$varName = strtolower($varName);
				$tmpBlock = '';
				if (isset($data[$varName]) && is_array($data[$varName])){
					$countVars = count($data[$varName]);

					for ($i = 0; $i < $countVars; $i++){
						$internalBlock = $this->processBlocks($strBlock, $data[$varName][$i]);

						$tmpBlock .= $this->processElements($internalBlock, $data[$varName][$i]);
					}
				}

				$stringToProcess = substr_replace($stringToProcess, $tmpBlock, $posIni, $posSecondId + strlen($secondId) - $posIni);

				break;
			}


		}while ($posIni);

		return $stringToProcess;
	}

	private function processElements($stringToProcess, Array $data = null){
		if ($data == null){
			$data = $this->data;
		}

		do{
			$pos = strpos($stringToProcess, $this->initElementTag);

			if ($pos){
				$pos2 = strpos($stringToProcess, $this->endElementTag,$pos);

				if ($pos2){
					$varName = strtolower(substr($stringToProcess, $pos + $this->initElementTagLength, $pos2 - ($pos + $this->initElementTagLength)));
					if (isset($data[$varName])){
						$stringToProcess = substr_replace($stringToProcess, $data[$varName], $pos, $pos2 + $this->endElementTagLength - $pos);
					}else{
						$stringToProcess = substr_replace($stringToProcess, '', $pos, $pos2 + $this->endElementTagLength - $pos);
					}
				}
			}
		}while ($pos);

		return $stringToProcess;
	}

}

$processor = new Processor($stringToProcess, $data);

echo "INITIAL STRING: [{$stringToProcess}]<br>\n";
echo "PROCESSED STRING: [", $processor->process(), "]<br>\n";
