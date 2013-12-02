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

class ImportHumphreyScansCommand extends CConsoleCommand
{
	/**
	 * @param array $args
	 */
	public function run($args)
	{
		// This prevents fatal errors from libxml, allowing us to throw exceptions instead
		libxml_use_internal_errors(true);

		$importer = OphImVisualfields_Hfa_Importer::get();

		$success = 0;
		$fail = 0;
		foreach ($importer->listXmlFiles() as $path) {
			try {
				$importer->import($path);
				print "Successfully imported '{$path}'\n";
				$success++;
			} catch(Exception $e) {
				print "Failed to import '{$path}':\n$e\n\n";
				$fail++;
			}
		}

		print "{$success} scans successfully imported, {$fail} failures\n";
	}
}
