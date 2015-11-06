<?php

namespace HM\BackUpWordPress;

class Backup_Engine_Get_Files extends \HM_Backup_UnitTestCase {

	public function setUp() {

		$this->backup = new Mock_File_Backup_Engine;
		$this->setup_test_data();
		$this->backup->set_root( $this->test_data );

	}

	public function tearDown() {
		hmbkp_rmdirtree( $this->test_data );
		hmbkp_rmdirtree( $this->test_data_symlink );
	}

	public function test_get_files() {

		$files = $this->backup->get_files();
		$this->assertEquals( count( $files ), 3 );

	}

	public function test_get_files_includes_hidden_files() {

		file_put_contents( $this->test_data . '/.hidden', '' );

		$files = $this->backup->get_files();
		$this->assertEquals( count( $files ), 4 );

	}

	public function test_vcs_ignored() {

		mkdir( $this->test_data . '/.git' );

		$files = $this->backup->get_files();
		$this->assertEquals( count( $files ), 3 );

	}

	public function test_default_excludes_ignored() {

		$default_excludes = $this->backup->get_default_excludes();

		foreach ( $default_excludes as $default_exclude ) {
			$default_exclude = str_replace( '*/', '', $default_exclude );
			mkdir( trailingslashit( $this->test_data ) . $default_exclude );
		}

		$files = $this->backup->get_files();
		$this->assertEquals( count( $files ), 3 );

	}

	/**
	 * The .gitignore file should be ignored because .git is a default exclude
	 */
	public function test_excluded_git_in_filename_is_ignored() {

		file_put_contents( $this->test_data . '/.gitignore', '' );

		$files = $this->backup->get_files();
		$this->assertEquals( count( $files ), 3 );

	}

	/**
	 * These folders shouln't be excluded just because `updraft` is an excluded directory
	 */
	public function test_excluded_dir_in_name_isnt_ignored() {

		mkdir( $this->test_data . '/updraft-plus' );
		file_put_contents( $this->test_data . '/updraft-plus/file.txt', 'The cake is a lie.' );
		mkdir( $this->test_data . '/plus-updraft' );
		file_put_contents( $this->test_data . '/plus-updraft/file.txt', 'The cake is a lie.' );

		$files = $this->backup->get_files();
		$this->assertEquals( count( $files ), 7 );

	}

}