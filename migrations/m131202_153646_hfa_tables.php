<?php

class m131202_153646_hfa_tables extends OEMigration
{
	public function up()
	{
		$this->createOETable(
			'ophimvisualfields_hfa_test_type',
			array(
				'id' => 'int unsigned not null auto_increment primary key',
				'name' => 'varchar(85) not null unique'
			)
		);

		$this->createOETable(
			'ophimvisualfields_hfa_test_strategy',
			array(
				'id' => 'int unsigned not null auto_increment primary key',
				'name' => 'varchar(85) not null unique'
			)
		);

		$this->createOETable(
			'ophimvisualfields_hfa_image',
			array(
				'file_id' =>  'int unsigned not null',
				'patient_id' => 'int unsigned not null',
				'eye_id' => 'int unsigned not null',
				'test_type_id' => 'int unsigned not null',
				'test_strategy_id' => 'int unsigned not null',
				'test_date' => 'datetime not null',
				'xml' => 'text',
				'constraint ophimvisualfields_hfa_image_file_id_fk foreign key (file_id) references protected_file (id)',
				'constraint ophimvisualfields_hfa_image_patient_id_fk foreign key (patient_id) references patient (id)',
				'constraint ophimvisualfields_hfa_image_eye_id_fk foreign key (eye_id) references eye (id)',
				'constraint ophimvisualfields_hfa_image_test_type_id_fk foreign key (test_type_id) references ophimvisualfields_hfa_test_type (id)',
				'constraint ophimvisualfields_hfa_image_test_strategy_id_fk foreign key (test_strategy_id) references ophimvisualfields_hfa_test_strategy (id)',
			)
		);

		$this->initialiseData(__DIR__);
	}

	public function down()
	{
		foreach (array('ophimvisualfields_hfa_image', 'ophimvisualfields_hfa_test_strategy', 'ophimvisualfields_hfa_test_type') as $table) {
			$this->dropTable($table);
		}
	}
}
