<?php
/**
 * (C) OpenEyes Foundation, 2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (C) 2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

class OphImVisualfields_Hfa_XmlParser
{
	/**
	 * @param string $xml
	 * @return array
	 */
	static public function parseString($xml)
	{
		$doc = new DOMDocument;
		if (!$doc->loadXML($xml)) {
			throw XmlException::generate("Failed to parse XML string");
		}

		$xpath = new DOMXPath($doc);
		$xpath->registerNamespace('CZM', $doc->firstChild->namespaceURI);

		$parser = new self($doc, $xpath);
		return $parser->parse();
	}

	protected $doc;
	protected $xpath;

	/**
	 * @param DOMDocument $doc
	 * @param DOMXPath $xpath
	 */
	public function __construct(DOMDocument $doc, DOMXPath $xpath)
	{
		$this->doc = $doc;
		$this->xpath = $xpath;
	}

	/**
	 * @return array
	 */
	public function parse()
	{
		$iod = $this->getNode('CZM_HFA_EMR_IOD');

		$name = $this->getNode('patients_name', $iod);
		$series = $this->getNode('GeneralSeries_M', $iod);
		$hfa_series = $this->getNode('CZM_HFA_Series_M', $iod);
		$image = $this->getNode('ReferencedImage_M', $iod);

		return array(
			'patient' => array(
				'hos_num' => $this->getText('patient_id', $iod),
				'first_name' => $this->getText('given_name', $name),
				'last_name' => $this->getText('family_name', $name),
				'dob' => $this->getText('patients_birth_date', $iod),
				'gender' => $this->getText('patients_sex', $iod),
			),
			'test' => array(
				'date' => $this->getText('study_date', $iod) . ' ' . $this->getText('study_time', $iod),
				'eye_id' => ($this->getText('laterality', $series) == 'L') ? Eye::LEFT : Eye::RIGHT,
				'name' => $this->getText('test_name', $hfa_series),
				'strategy' => $this->getText('test_strategy', $hfa_series)
			),
			'image_file' => $this->getText('file_reference', $image),
		);
	}

	protected function getText($element_name, DOMNode $contextnode = null)
	{
		return $this->getNode($element_name, $contextnode)->textContent;
	}

	protected function getNode($element_name, DOMNode $contextnode = null)
	{
		$expression = ".//CZM:{$element_name}";
		$nodes = $this->xpath($expression, $contextnode);
		if ($nodes->length != 1) {
			for ($i = 0; $i < $nodes->length; $i++) {
				print $nodes->item($i)->textContent . "\n";
			}

			throw new Exception("Failed to find a single <{$element_name}> element");
		}
		return $nodes->item(0);
	}

	protected function xpath($expression, DOMNode $contextnode = null)
	{
		$nodes = $this->xpath->query($expression, $contextnode);
		if ($nodes === FALSE) {
			throw XmlException::generate("XPath query '{$expression}' failed");
		}
		return $nodes;
	}
}
