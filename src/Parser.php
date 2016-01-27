<?php namespace Rtablada\LiteratePhp;

/**
 * This Parser can be used to parse Markdown syntax that contains blocks of PHP
 * code
 */
class Parser
{
    /**
     * Parses a string in Markdown syntax and returns PHP code
     * 
     * @param string $string
     * @return string
     */
	public function parse($string)
	{
		$string = $this->breakParts($string);

		return "<?php\n\n".$string;
	}

    /**
     * Removes lists that are created via Markdown * (star) syntax from a string
     * of Markdown.
     * 
     * Useful because in PHP comments, each line of a multi-line
     * comment begins with a star. To keep your Markdown lists intact within the
     * comments, you can use the - (minus) symbol instead of the star within
     * your Markdown code.
     * 
     * @param string $string
     * @return string
     */
	public function removeLists($string)
	{
		return preg_replace('/[\n]*\t*\*.*/', '', $string);
	}

    /**
     * Breaks a string in Markdown syntax into parts. Every other part is PHP
     * code, the rest is Markdown and will be turned into PHP comments
     * 
     * @param string $string
     * @return string
     */
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


    /**
     * Parses a string of Markdown and returns a PHP comment block containing
     * its text
     * 
     * @param string $string
     * @return string
     */
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

    /**
     * Parses a block of PHP code (inside a Markdown string), e. g. removes
     * the leading <?php (if any)
     * 
     * @param string $string
     * @return string
     */
	public function parseCode($string)
	{
		$string = preg_replace('/<\?php\s/', '', $string);

		return "{$string}\n\n";
	}
}