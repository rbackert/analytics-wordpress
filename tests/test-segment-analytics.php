<?php

class Segment_Analytics_Test extends WP_UnitTestCase {
	protected $object;

	public function setUp(): void {
		parent::setUp();
		$this->object = Segment_Analytics::get_instance();
	}

	public function tearDown(): void {
		parent::tearDown();
	}

	public function test_segment_instance() {
		$class = new ReflectionClass( 'Segment_Analytics' );

		$this->assertTrue( $class->hasProperty('instance') );
		$this->assertTrue( $class->getProperty('instance')->isStatic() );
	}

	/**
	 * @covers Segment_Analytics::setup_constants
	 */
	public function test_constants() {

		// Plugin File Path
		$path = str_replace( "/tests", '', dirname( __FILE__ ) );
		$this->assertSame( SEG_FILE_PATH, $path );

		// Plugin Folder
		$path = str_replace( "/tests", '', dirname( plugin_basename( __FILE__ ) ) );
		$this->assertSame( SEG_FOLDER, $path );

		// Plugin Root File
		$path = str_replace( "/tests", '', plugins_url( '', __FILE__ ) );
		$this->assertSame( SEG_URL, $path );

	}

}

