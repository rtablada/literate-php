<?php namespace Rtablada\LiteratePhp;

class Parser
{
	public function parse($string)
	{
		$string = $this->breakParts($string);

		return "<?php\n\n".$string;
	}

	public function removeLists($string)
	{
		return preg_replace('/[\n]*\t*\*.*/', '', $string);
	}

	public function breakParts($string)
	{
		$parts = preg_split('/[\n]+(```|~~~).*[\n]/', $string);
		$string = '';

		foreach ($parts as $key => $part)
		{
			if ($key%2 === 0) {
				$part = $this->removeLists($part);
				$part = $this->parseComments($part);
			} else {
				$part = $this->parseCode($part);
			}

			if ($part != '') {
				$string .= "{$part}";
			}
		}

		return $string;
	}


	public function parseComments($string)
	{
		$return = '';
		$multiLineComment = false;
		$lines = preg_split('/[\r\n]/', $string);

		foreach ($lines as $key => $line) {
			if (preg_match('/#\s.*/', $line)) {
				$return .= str_replace('# ', "// ", $line) . "\n";
			} elseif ($line != '') {
				if (!$multiLineComment) {
					$multiLineComment = true;
					$return .= '/**';
				}
				$return .= "\n * {$line}";
			}
		}

		if ($multiLineComment) {
			$return .= "\n */\n";
		}

		return $return;
	}

	public function parseCode($string)
	{
		$string = preg_replace('/<\?php\s/', '', $string);

		return "{$string}\n\n";
	}
}
