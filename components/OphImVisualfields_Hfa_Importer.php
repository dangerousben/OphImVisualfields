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

class OphImVisualfields_Hfa_Importer
{
	static public function get()
	{
		return new self(
			Yii::app()->db,
			Patient::model(),
			ProtectedFile::model(),
			OphImVisualFields_Hfa_Image::model(),
			Yii::app()->params['OphImVisualfields']['hfa']['dir_in']
		);
	}

	protected $db;
	protected $patient_model;
	protected $file_model;
	protected $image_model;
	protected $src_dir;

	public function __construct(
		CDbConnection $db,
		Patient $patient_model,
		ProtectedFile $file_model,
		OphImVisualfields_Hfa_Image $image_model,
		$src_dir
	)
	{
		$this->db = $db;
		$this->patient_model = $patient_model;
		$this->file_model = $file_model;
		$this->image_model = $image_model;
		$this->src_dir = $src_dir;
	}

	/**
	 * @return string[]
	 */
	public function listXmlFiles()
	{
		return glob($this->src_dir . DIRECTORY_SEPARATOR . '*.xml');
	}

	/**
	 * @param string $xml_path
	 */
	public function import($xml_path)
	{
		$xml = file_get_contents($xml_path);

		$data = OphImVisualfields_Hfa_XmlParser::parseString($xml);  // TODO: this shouldn't be static

		$patient = $this->patient_model->findByAttributes(array('hos_num' => $data['patient']['hos_num']));
		if (!$patient) {
			throw new Exception("Failed to find patient with hos_num '{$data['hos_num']}'");
		}

		// TODO: check patient data?

		$image_path = $this->src_dir . DIRECTORY_SEPARATOR . $data['image_file'];
		if (!file_exists($image_path)) {
			throw new Exception("Image file '{$image_path}' for scan '{$xml_path}' not found");
		}

		$tx = $this->db->beginTransaction();

		try {
			$file = $this->file_model->createFromFile($image_path);

			// TODO: set title and description?

			$file->save();

			$this->image_model->create($file, $patient, $data, $xml);

		} catch(Exception $e) {
			$tx->rollback();
			@unlink($protected_file->path);
			throw $e;
		}

		$tx->commit();
	}
}
